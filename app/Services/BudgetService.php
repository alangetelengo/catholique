<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\ExpenseFundingSource;
use App\Models\Revenue;
use App\Models\RevenueType;
use Illuminate\Support\Collection;

class BudgetService
{
    public function getSoldeDisponible(RevenueType $revenueType, ?Expense $excludeExpense = null): float
    {
        $totalRecettes = Revenue::where('revenue_type_id', $revenueType->id)
            ->where('statut', 'valide')
            ->sum('montant');

        $query = ExpenseFundingSource::where('revenue_type_id', $revenueType->id)
            ->whereHas('expense', function ($q) {
                $q->where('statut', 'valide');
            });

        if ($excludeExpense) {
            $query->where('expense_id', '!=', $excludeExpense->id);
        }

        $totalDepenses = $query->sum('montant_alloue');

        return (float) ($totalRecettes - $totalDepenses);
    }

    public function getSourcesAvecSolde(int $paroisseId, ?Expense $excludeExpense = null): Collection
    {
        $revenueTypes = RevenueType::where('paroisse_id', $paroisseId)
            ->where('actif', true)
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

            $revenueType = RevenueType::find($source['revenue_type_id']);
            if (! $revenueType) {
                $errors[] = "Type de recette invalide pour la source #{$index}";

                continue;
            }

            $soldeDisponible = $this->getSoldeDisponible($revenueType, $excludeExpense);
            $montantAlloue = (float) $source['montant_alloue'];

            if ($montantAlloue > $soldeDisponible) {
                $errors[] = sprintf(
                    '%s : montant demandé %s FCFA mais seulement %s FCFA disponible',
                    $revenueType->nom,
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
