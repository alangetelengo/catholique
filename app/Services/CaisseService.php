<?php

namespace App\Services;

use App\Models\Caisse;
use App\Models\CaisseMouvement;
use App\Models\Expense;
use App\Models\ExpenseFundingSource;
use App\Models\Revenue;
use App\Models\RevenueType;
use App\Support\SubventionMensuelle;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CaisseService
{
    public function getSolde(Caisse $caisse, ?Expense $excludeExpense = null): float
    {
        $credits = (float) CaisseMouvement::query()
            ->where('caisse_id', $caisse->id)
            ->where('sens', CaisseMouvement::SENS_CREDIT)
            ->sum('montant');

        $debitsQuery = CaisseMouvement::query()
            ->where('caisse_id', $caisse->id)
            ->where('sens', CaisseMouvement::SENS_DEBIT);

        if ($excludeExpense) {
            $debitsQuery->where(function ($q) use ($excludeExpense): void {
                $q->whereNull('expense_id')
                    ->orWhere('expense_id', '!=', $excludeExpense->id);
            });
        }

        $debits = (float) $debitsQuery->sum('montant');

        return max(0.0, round($credits - $debits, 2));
    }

    /**
     * @return Collection<int, Caisse>
     */
    public function getCaissesAvecSolde(int $paroisseId, ?Expense $excludeExpense = null, bool $onlyOperatives = false, bool $onlyWithSolde = false): Collection
    {
        $query = Caisse::query()
            ->where('paroisse_id', $paroisseId)
            ->where('actif', true)
            ->orderBy('ordre')
            ->orderBy('nom');

        if ($onlyOperatives) {
            $query->where('est_tresorerie', false);
        }

        return $query->get()->map(function (Caisse $caisse) use ($excludeExpense) {
            $caisse->solde_disponible = $this->getSolde($caisse, $excludeExpense);

            return $caisse;
        })->when($onlyWithSolde, fn (Collection $items) => $items->filter(
            fn (Caisse $caisse) => round((float) $caisse->solde_disponible, 2) > 0
        )->values());
    }

    public function getTresorerie(int $paroisseId): Caisse
    {
        $caisse = Caisse::query()
            ->where('paroisse_id', $paroisseId)
            ->where('code', Caisse::CODE_TRESORERIE)
            ->first();

        if (! $caisse) {
            throw new InvalidArgumentException('La caisse de trésorerie générale est introuvable pour cette paroisse.');
        }

        return $caisse;
    }

    public function creditDirect(
        Caisse $caisse,
        float $montant,
        string $dateMouvement,
        string $libelle,
        ?string $notes = null,
        ?int $createdBy = null
    ): CaisseMouvement {
        if ($caisse->isTresorerie()) {
            throw new InvalidArgumentException('Utilisez une recette Banque pour créditer la trésorerie générale.');
        }

        if ($montant <= 0) {
            throw new InvalidArgumentException('Le montant du crédit doit être positif.');
        }

        return CaisseMouvement::query()->create([
            'paroisse_id' => $caisse->paroisse_id,
            'caisse_id' => $caisse->id,
            'type' => CaisseMouvement::TYPE_CREDIT_DIRECT,
            'sens' => CaisseMouvement::SENS_CREDIT,
            'montant' => $montant,
            'date_mouvement' => $dateMouvement,
            'libelle' => $libelle,
            'notes' => $notes,
            'created_by' => $createdBy,
        ]);
    }

    public function syncCreditFromBanqueRevenue(Revenue $revenue): void
    {
        $revenue->loadMissing('category', 'type');

        if ($revenue->category?->code !== 'banque') {
            $this->removeCreditsLinkedToRevenue($revenue);

            return;
        }

        if (($revenue->statut ?? 'valide') !== 'valide') {
            $this->removeCreditsLinkedToRevenue($revenue);

            return;
        }

        $tresorerie = $this->getTresorerie((int) $revenue->paroisse_id);
        $existing = CaisseMouvement::query()
            ->where('revenue_id', $revenue->id)
            ->where('type', CaisseMouvement::TYPE_CREDIT_RECETTE)
            ->first();

        $payload = [
            'paroisse_id' => $revenue->paroisse_id,
            'caisse_id' => $tresorerie->id,
            'type' => CaisseMouvement::TYPE_CREDIT_RECETTE,
            'sens' => CaisseMouvement::SENS_CREDIT,
            'montant' => $revenue->montant,
            'date_mouvement' => $revenue->date_recette?->format('Y-m-d') ?? now()->toDateString(),
            'libelle' => 'Recette Banque — '.($revenue->type?->nom ?? 'Revenu principal')
                .($revenue->mois_capital ? ' ('.SubventionMensuelle::formatMoisCapital($revenue->mois_capital).')' : ''),
            'revenue_id' => $revenue->id,
            'revenue_type_id' => $revenue->revenue_type_id,
            'created_by' => $revenue->created_by,
        ];

        if ($existing) {
            $existing->update($payload);

            return;
        }

        CaisseMouvement::query()->create($payload);
    }

    public function removeCreditsLinkedToRevenue(Revenue $revenue): void
    {
        CaisseMouvement::query()
            ->where('revenue_id', $revenue->id)
            ->where('type', CaisseMouvement::TYPE_CREDIT_RECETTE)
            ->delete();
    }

    /**
     * @return array{debit: CaisseMouvement, credit: CaisseMouvement}
     */
    public function virementTresorerieVersCaisse(
        Caisse $destination,
        float $montant,
        string $dateMouvement,
        string $libelle,
        ?string $notes = null,
        ?int $createdBy = null
    ): array {
        if ($destination->isTresorerie()) {
            throw new InvalidArgumentException('La destination doit être une caisse opérationnelle.');
        }

        if ((int) $destination->paroisse_id < 1) {
            throw new InvalidArgumentException('Paroisse invalide pour le virement.');
        }

        $tresorerie = $this->getTresorerie((int) $destination->paroisse_id);
        $solde = $this->getSolde($tresorerie);

        if ($montant <= 0) {
            throw new InvalidArgumentException('Le montant du virement doit être positif.');
        }

        if ($montant > $solde) {
            throw new InvalidArgumentException(sprintf(
                'Trésorerie insuffisante : %s FCFA demandé, %s FCFA disponible.',
                number_format($montant, 0, ',', ' '),
                number_format($solde, 0, ',', ' ')
            ));
        }

        return DB::transaction(function () use ($tresorerie, $destination, $montant, $dateMouvement, $libelle, $notes, $createdBy) {
            $debit = CaisseMouvement::query()->create([
                'paroisse_id' => $tresorerie->paroisse_id,
                'caisse_id' => $tresorerie->id,
                'type' => CaisseMouvement::TYPE_VIREMENT,
                'sens' => CaisseMouvement::SENS_DEBIT,
                'montant' => $montant,
                'date_mouvement' => $dateMouvement,
                'libelle' => $libelle ?: 'Virement vers '.$destination->nom,
                'notes' => $notes,
                'contrepartie_caisse_id' => $destination->id,
                'created_by' => $createdBy,
            ]);

            $credit = CaisseMouvement::query()->create([
                'paroisse_id' => $destination->paroisse_id,
                'caisse_id' => $destination->id,
                'type' => CaisseMouvement::TYPE_VIREMENT,
                'sens' => CaisseMouvement::SENS_CREDIT,
                'montant' => $montant,
                'date_mouvement' => $dateMouvement,
                'libelle' => $libelle ?: 'Virement depuis '.$tresorerie->nom,
                'notes' => $notes,
                'contrepartie_caisse_id' => $tresorerie->id,
                'contrepartie_mouvement_id' => $debit->id,
                'created_by' => $createdBy,
            ]);

            $debit->update(['contrepartie_mouvement_id' => $credit->id]);

            return ['debit' => $debit, 'credit' => $credit];
        });
    }

    public function alimentationDepuisRecette(
        Caisse $destination,
        RevenueType $revenueType,
        float $montant,
        string $dateMouvement,
        string $libelle,
        ?string $notes = null,
        ?int $createdBy = null
    ): CaisseMouvement {
        if ($destination->isTresorerie()) {
            throw new InvalidArgumentException('Alimentez la trésorerie via une recette Banque.');
        }

        if ((int) $destination->paroisse_id !== (int) $revenueType->paroisse_id) {
            throw new InvalidArgumentException('La caisse et le type de recette doivent appartenir à la même paroisse.');
        }

        $revenueType->loadMissing('category');
        if ($revenueType->category?->code === 'banque' || SubventionMensuelle::isSubventionType($revenueType)) {
            throw new InvalidArgumentException('Utilisez un virement depuis la trésorerie ou un crédit direct de caisse.');
        }

        $solde = $this->getSoldeDisponibleRevenueType($revenueType);
        if ($montant <= 0) {
            throw new InvalidArgumentException('Le montant doit être positif.');
        }

        if ($montant > $solde) {
            throw new InvalidArgumentException(sprintf(
                '%s : %s FCFA demandé, %s FCFA disponible.',
                $revenueType->nom,
                number_format($montant, 0, ',', ' '),
                number_format($solde, 0, ',', ' ')
            ));
        }

        return CaisseMouvement::query()->create([
            'paroisse_id' => $destination->paroisse_id,
            'caisse_id' => $destination->id,
            'type' => CaisseMouvement::TYPE_ALIMENTATION_RECETTE,
            'sens' => CaisseMouvement::SENS_CREDIT,
            'montant' => $montant,
            'date_mouvement' => $dateMouvement,
            'libelle' => $libelle ?: 'Alimentation depuis '.$revenueType->nom,
            'notes' => $notes,
            'revenue_type_id' => $revenueType->id,
            'created_by' => $createdBy,
        ]);
    }

    public function getSoldeDisponibleRevenueType(RevenueType $revenueType, ?Expense $excludeExpense = null): float
    {
        $totalRecettes = (float) Revenue::query()
            ->where('revenue_type_id', $revenueType->id)
            ->where('statut', 'valide')
            ->sum('montant');

        $legacyDepensesQuery = ExpenseFundingSource::query()
            ->where('revenue_type_id', $revenueType->id)
            ->whereNull('caisse_id')
            ->whereHas('expense', fn ($q) => $q->where('statut', 'valide'));

        if ($excludeExpense) {
            $legacyDepensesQuery->where('expense_id', '!=', $excludeExpense->id);
        }

        $legacyDepenses = (float) $legacyDepensesQuery->sum('montant_alloue');

        $alimentations = (float) CaisseMouvement::query()
            ->where('revenue_type_id', $revenueType->id)
            ->where('type', CaisseMouvement::TYPE_ALIMENTATION_RECETTE)
            ->where('sens', CaisseMouvement::SENS_CREDIT)
            ->sum('montant');

        return max(0.0, round($totalRecettes - $legacyDepenses - $alimentations, 2));
    }

    /**
     * @param  list<array{caisse_id: int|string, montant_alloue: float|int|string}>  $fundingSources
     * @return array{valid: bool, errors: list<string>, total_alloue: float}
     */
    public function validateFundingSources(array $fundingSources, ?Expense $excludeExpense = null, ?int $paroisseId = null): array
    {
        $errors = [];
        $totalAlloue = 0.0;
        $totalsByCaisse = [];
        $resolvedParoisseId = $paroisseId ?? ($excludeExpense?->paroisse_id !== null ? (int) $excludeExpense->paroisse_id : null);

        foreach ($fundingSources as $index => $source) {
            if (empty($source['caisse_id']) || ! isset($source['montant_alloue'])) {
                $errors[] = 'La source #'.($index + 1).' est incomplète.';

                continue;
            }

            $caisse = Caisse::query()->find($source['caisse_id']);
            if (! $caisse || ! $caisse->actif) {
                $errors[] = 'Caisse invalide pour la source #'.($index + 1).'.';

                continue;
            }

            if ($resolvedParoisseId !== null && (int) $caisse->paroisse_id !== $resolvedParoisseId) {
                $errors[] = 'La caisse de la source #'.($index + 1).' n\'appartient pas à votre paroisse.';

                continue;
            }

            if ($caisse->isTresorerie()) {
                $errors[] = 'La trésorerie générale ne peut pas financer directement une dépense (source #'.($index + 1).').';

                continue;
            }

            $montantAlloue = (float) $source['montant_alloue'];
            if ($montantAlloue <= 0) {
                $errors[] = 'Le montant alloué doit être positif (source #'.($index + 1).').';

                continue;
            }

            $caisseId = (int) $caisse->id;
            $totalsByCaisse[$caisseId] = ($totalsByCaisse[$caisseId] ?? [
                'caisse' => $caisse,
                'montant' => 0.0,
                'indexes' => [],
            ]);
            $totalsByCaisse[$caisseId]['montant'] += $montantAlloue;
            $totalsByCaisse[$caisseId]['indexes'][] = $index + 1;
            $totalAlloue += $montantAlloue;
        }

        foreach ($totalsByCaisse as $entry) {
            /** @var Caisse $caisse */
            $caisse = $entry['caisse'];
            $montantDemande = round((float) $entry['montant'], 2);
            $solde = $this->getSolde($caisse, $excludeExpense);

            if ($montantDemande > $solde) {
                $errors[] = sprintf(
                    '%s : montant demandé %s FCFA mais seulement %s FCFA disponible. Alimentez d\'abord cette caisse.',
                    $caisse->nom,
                    number_format($montantDemande, 0, ',', ' '),
                    number_format($solde, 0, ',', ' ')
                );
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'total_alloue' => round($totalAlloue, 2),
        ];
    }

    /**
     * Crée les mouvements de trésorerie manquants pour les recettes Banque déjà enregistrées.
     */
    public function backfillBanqueCredits(?int $paroisseId = null): int
    {
        $query = Revenue::query()
            ->with(['category', 'type'])
            ->where('statut', 'valide')
            ->whereHas('category', fn ($q) => $q->where('code', 'banque'));

        if ($paroisseId !== null) {
            $query->where('paroisse_id', $paroisseId);
        }

        $count = 0;
        foreach ($query->cursor() as $revenue) {
            $alreadySynced = CaisseMouvement::query()
                ->where('revenue_id', $revenue->id)
                ->where('type', CaisseMouvement::TYPE_CREDIT_RECETTE)
                ->exists();

            if ($alreadySynced) {
                continue;
            }

            $this->syncCreditFromBanqueRevenue($revenue);
            $count++;
        }

        return $count;
    }

    /**
     * @param  list<array{caisse_id: int|string, montant_alloue: float|int|string}>  $fundingSources
     */
    public function syncDepenseMouvements(Expense $expense, array $fundingSources): void
    {
        CaisseMouvement::query()
            ->where('expense_id', $expense->id)
            ->where('type', CaisseMouvement::TYPE_DEPENSE)
            ->delete();

        foreach ($expense->fundingSources as $index => $source) {
            if (! $source->caisse_id) {
                continue;
            }

            CaisseMouvement::query()->create([
                'paroisse_id' => $expense->paroisse_id,
                'caisse_id' => $source->caisse_id,
                'type' => CaisseMouvement::TYPE_DEPENSE,
                'sens' => CaisseMouvement::SENS_DEBIT,
                'montant' => $source->montant_alloue,
                'date_mouvement' => $expense->date_depense?->format('Y-m-d') ?? now()->toDateString(),
                'libelle' => $expense->libelle,
                'expense_id' => $expense->id,
                'expense_funding_source_id' => $source->id,
                'created_by' => $expense->created_by,
            ]);
        }
    }

    public function removeDepenseMouvements(Expense $expense): void
    {
        CaisseMouvement::query()
            ->where('expense_id', $expense->id)
            ->where('type', CaisseMouvement::TYPE_DEPENSE)
            ->delete();
    }
}
