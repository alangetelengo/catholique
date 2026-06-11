<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\ExpenseFundingSource;
use App\Models\Revenue;
use App\Models\RevenueType;
use App\Support\SubventionMensuelle;
use Illuminate\Support\Collection;

class BudgetService
{
    public function getSoldeDisponible(RevenueType $revenueType, ?Expense $excludeExpense = null): float
    {
        if (SubventionMensuelle::isSubventionType($revenueType)) {
            return (float) $this->getSubventionEnvelopesAvecSolde(
                (int) $revenueType->paroisse_id,
                $excludeExpense,
                false,
                $revenueType->id
            )->sum('solde_disponible');
        }

        $totalRecettes = Revenue::where('revenue_type_id', $revenueType->id)
            ->where('statut', 'valide')
            ->sum('montant');

        $query = ExpenseFundingSource::where('revenue_type_id', $revenueType->id)
            ->whereNull('revenue_id')
            ->whereHas('expense', function ($q) {
                $q->where('statut', 'valide');
            });

        if ($excludeExpense) {
            $query->where('expense_id', '!=', $excludeExpense->id);
        }

        $totalDepenses = $query->sum('montant_alloue');

        return (float) ($totalRecettes - $totalDepenses);
    }

    public function getSoldeDisponibleForRevenue(Revenue $revenue, ?Expense $excludeExpense = null): float
    {
        if ($revenue->statut !== 'valide') {
            return 0.0;
        }

        $query = ExpenseFundingSource::query()
            ->where('revenue_id', $revenue->id)
            ->whereHas('expense', function ($q) {
                $q->where('statut', 'valide');
            });

        if ($excludeExpense) {
            $query->where('expense_id', '!=', $excludeExpense->id);
        }

        $totalDepenses = (float) $query->sum('montant_alloue');

        return (float) $revenue->montant - $totalDepenses;
    }

    /**
     * Enveloppes mensuelles de toutes les subventions avec solde restant.
     *
     * @return Collection<int, Revenue>
     */
    public function getSubventionEnvelopesAvecSolde(
        int $paroisseId,
        ?Expense $excludeExpense = null,
        bool $onlyWithSolde = true,
        ?int $revenueTypeId = null
    ): Collection {
        $envelopes = Revenue::query()
            ->where('paroisse_id', $paroisseId)
            ->where('statut', 'valide')
            ->whereNotNull('mois_subvention')
            ->when($revenueTypeId !== null, fn ($q) => $q->where('revenue_type_id', $revenueTypeId))
            ->whereHas('type.category', fn ($q) => $q->where('code', SubventionMensuelle::CATEGORY_CODE))
            ->with(['type'])
            ->orderByDesc('mois_subvention')
            ->orderBy('revenue_type_id')
            ->get()
            ->map(function (Revenue $revenue) use ($excludeExpense) {
                $revenue->solde_disponible = $this->getSoldeDisponibleForRevenue($revenue, $excludeExpense);
                $revenue->mois_label = SubventionMensuelle::formatMoisLabel($revenue->mois_subvention);
                $revenue->envelope_label = SubventionMensuelle::envelopeLabel($revenue->type, $revenue->mois_subvention);

                return $revenue;
            });

        if ($onlyWithSolde) {
            return $envelopes->filter(fn (Revenue $revenue) => $revenue->solde_disponible > 0)->values();
        }

        return $envelopes->values();
    }

    /**
     * @return Collection<int, Revenue>
     */
    public function getSubventionEnvelopesForExpenseForm(int $paroisseId, ?Expense $expense = null): Collection
    {
        $envelopes = $this->getSubventionEnvelopesAvecSolde($paroisseId, $expense, false);
        $usedRevenueIds = $expense
            ? $expense->fundingSources()->whereNotNull('revenue_id')->pluck('revenue_id')
            : collect();

        return $envelopes
            ->filter(fn (Revenue $revenue) => $revenue->solde_disponible > 0 || $usedRevenueIds->contains($revenue->id))
            ->values();
    }

    /**
     * @deprecated Utiliser getSubventionEnvelopesAvecSolde
     *
     * @return Collection<int, Revenue>
     */
    public function getPopoteEnvelopesAvecSolde(int $paroisseId, ?Expense $excludeExpense = null, bool $onlyWithSolde = true): Collection
    {
        $popoteType = RevenueType::query()
            ->where('paroisse_id', $paroisseId)
            ->where('code', SubventionMensuelle::POPOTE_TYPE_CODE)
            ->value('id');

        return $this->getSubventionEnvelopesAvecSolde($paroisseId, $excludeExpense, $onlyWithSolde, $popoteType);
    }

    /**
     * @deprecated Utiliser getSubventionEnvelopesForExpenseForm
     *
     * @return Collection<int, Revenue>
     */
    public function getPopoteEnvelopesForExpenseForm(int $paroisseId, ?Expense $expense = null): Collection
    {
        return $this->getSubventionEnvelopesForExpenseForm($paroisseId, $expense);
    }

    public function getSourcesAvecSolde(int $paroisseId, ?Expense $excludeExpense = null): Collection
    {
        $revenueTypes = RevenueType::where('paroisse_id', $paroisseId)
            ->where('actif', true)
            ->whereDoesntHave('category', fn ($q) => $q->where('code', SubventionMensuelle::CATEGORY_CODE))
            ->with('category')
            ->orderBy('ordre')
            ->orderBy('nom')
            ->get();

        return $revenueTypes->map(function ($type) use ($excludeExpense) {
            $type->solde_disponible = $this->getSoldeDisponible($type, $excludeExpense);

            return $type;
        })->filter(function ($type) {
            return $type->solde_disponible > 0;
        });
    }

    public function validateFundingSources(array $fundingSources, ?Expense $excludeExpense = null): array
    {
        $errors = [];
        $totalAlloue = 0;

        foreach ($fundingSources as $index => $source) {
            if (empty($source['revenue_type_id']) || empty($source['montant_alloue'])) {
                $errors[] = "La source #{$index} est incomplète";

                continue;
            }

            $revenueType = RevenueType::with('category')->find($source['revenue_type_id']);
            if (! $revenueType) {
                $errors[] = "Type de recette invalide pour la source #{$index}";

                continue;
            }

            $montantAlloue = (float) $source['montant_alloue'];
            $revenueId = ! empty($source['revenue_id']) ? (int) $source['revenue_id'] : null;

            if (SubventionMensuelle::isSubventionType($revenueType)) {
                if ($revenueId === null) {
                    $errors[] = sprintf(
                        'Sélectionnez le mois de %s concerné pour la source #%d.',
                        $revenueType->nom,
                        $index + 1
                    );

                    continue;
                }

                $revenue = Revenue::query()->with('type')->find($revenueId);
                if (! $revenue || $revenue->revenue_type_id !== $revenueType->id || $revenue->mois_subvention === null) {
                    $errors[] = sprintf('Enveloppe mensuelle invalide pour %s (source #%d).', $revenueType->nom, $index + 1);

                    continue;
                }

                $soldeDisponible = $this->getSoldeDisponibleForRevenue($revenue, $excludeExpense);
                $label = SubventionMensuelle::envelopeLabel($revenue->type, $revenue->mois_subvention);
            } else {
                if ($revenueId !== null) {
                    $errors[] = 'La source #'.($index + 1).' ne doit pas être liée à une enveloppe mensuelle.';

                    continue;
                }

                $soldeDisponible = $this->getSoldeDisponible($revenueType, $excludeExpense);
                $label = $revenueType->nom;
            }

            if ($montantAlloue > $soldeDisponible) {
                $errors[] = sprintf(
                    '%s : montant demandé %s FCFA mais seulement %s FCFA disponible',
                    $label,
                    number_format($montantAlloue, 0, ',', ' '),
                    number_format($soldeDisponible, 0, ',', ' ')
                );
            }

            $totalAlloue += $montantAlloue;
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'total_alloue' => $totalAlloue,
        ];
    }

    public function getAllocatedAmountForType(int $revenueTypeId): float
    {
        return (float) ExpenseFundingSource::where('revenue_type_id', $revenueTypeId)
            ->whereHas('expense', function ($q) {
                $q->where('statut', 'valide');
            })
            ->sum('montant_alloue');
    }
}
