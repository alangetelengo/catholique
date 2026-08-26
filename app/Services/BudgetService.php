<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\ExpenseFundingSource;
use App\Models\Revenue;
use App\Models\RevenueType;
use App\Support\SubventionMensuelle;
use Illuminate\Support\Collection;

/**
 * Soldes des types de recettes (hors caisses opérationnelles).
 * Les anciennes enveloppes mensuelles « subvention » ont été remplacées par les caisses.
 */
class BudgetService
{
    public function getSoldeDisponible(RevenueType $revenueType, ?Expense $excludeExpense = null): float
    {
        if (SubventionMensuelle::isSubventionType($revenueType)) {
            return 0.0;
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

        return max(0.0, round((float) $revenue->montant - $totalDepenses, 2));
    }

    private function hasSoldeDisponible(float $solde): bool
    {
        return round($solde, 2) > 0;
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
            return $this->hasSoldeDisponible($type->solde_disponible);
        })->values();
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
