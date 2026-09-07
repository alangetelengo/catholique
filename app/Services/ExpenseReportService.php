<?php

namespace App\Services;

use App\Models\Caisse;
use App\Models\CaisseMouvement;
use App\Models\Expense;
use App\Models\ExpenseFundingSource;
use App\Models\Revenue;
use App\Support\SubventionMensuelle;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ExpenseReportService
{
    /**
     * @return array{
     *     expenses: Collection<int, Expense>,
     *     by_category: array<string, array{id: int, nom: string, montant: float, count: int}>,
     *     by_expense_type: array<int|string, array{id: int|null, nom: string, montant: float, count: int}>,
     *     caisse_summary: list<array{key: string, caisse_id: int, nom: string, credits: float, depenses: float, solde: float, count: int}>,
     *     total_general: float,
     *     date_debut: Carbon,
     *     date_fin: Carbon
     * }
     */
    public function calculateSummaryReport(
        int $paroisseId,
        Carbon $dateDebut,
        Carbon $dateFin,
        ?int $caisseId = null,
        ?int $expenseTypeId = null
    ): array {
        $query = Expense::query()
            ->with(['expenseType', 'fundingSources.caisse'])
            ->where('paroisse_id', $paroisseId)
            ->where('statut', 'valide')
            ->whereDate('date_depense', '>=', $dateDebut)
            ->whereDate('date_depense', '<=', $dateFin);

        if ($expenseTypeId) {
            $query->where('expense_type_id', $expenseTypeId);
        }

        if ($caisseId) {
            $query->whereHas('fundingSources', function ($fundingQuery) use ($caisseId): void {
                $fundingQuery->where('caisse_id', $caisseId);
            });
        }

        $expenses = $query->orderBy('date_depense')->orderBy('id')->get();

        $byCategory = [];
        $byExpenseType = [];
        foreach ($expenses->groupBy('expense_type_id') as $typeId => $items) {
            $expenseType = $items->first()?->expenseType;
            $key = $typeId ?: 'sans_type';
            $byExpenseType[$key] = [
                'id' => $typeId ? (int) $typeId : null,
                'nom' => $expenseType?->nom ?? 'Sans type',
                'montant' => (float) $items->sum('montant'),
                'count' => $items->count(),
            ];
        }

        uasort($byExpenseType, fn (array $a, array $b): int => $b['montant'] <=> $a['montant']);

        $caisseBuckets = [];
        $totalGeneral = 0.0;

        foreach ($expenses as $expense) {
            foreach ($expense->fundingSources as $fundingSource) {
                if ($caisseId && (int) $fundingSource->caisse_id !== (int) $caisseId) {
                    continue;
                }

                $caisse = $fundingSource->caisse;
                if (! $caisse) {
                    continue;
                }

                $allocated = (float) $fundingSource->montant_alloue;
                $totalGeneral += $allocated;

                $groupKey = 'caisse-'.$caisse->id;

                if (! isset($byCategory[$groupKey])) {
                    $byCategory[$groupKey] = [
                        'id' => (int) $caisse->id,
                        'nom' => $caisse->nom,
                        'montant' => 0.0,
                        'count' => 0,
                    ];
                }
                $byCategory[$groupKey]['montant'] += $allocated;
                $byCategory[$groupKey]['count']++;

                if (! isset($caisseBuckets[$caisse->id])) {
                    $caisseBuckets[$caisse->id] = [
                        'key' => $groupKey,
                        'caisse_id' => (int) $caisse->id,
                        'nom' => $caisse->nom,
                        'depenses' => 0.0,
                        'count' => 0,
                    ];
                }
                $caisseBuckets[$caisse->id]['depenses'] += $allocated;
                $caisseBuckets[$caisse->id]['count']++;
            }
        }

        uasort($byCategory, fn (array $a, array $b): int => $b['montant'] <=> $a['montant']);

        $caisseSummary = $this->finalizeCaisseSummary($paroisseId, $dateDebut, $dateFin, $caisseBuckets);

        return [
            'expenses' => $expenses,
            'by_category' => $byCategory,
            'by_expense_type' => $byExpenseType,
            'caisse_summary' => $caisseSummary,
            'total_general' => round($totalGeneral, 2),
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
        ];
    }

    /**
     * @return array{
     *     total_capital: float,
     *     total_virements: float,
     *     total_depenses: float,
     *     reste_alloue: float,
     *     capitals: Collection<int, Revenue>,
     *     virements: Collection<int, CaisseMouvement>,
     *     expenses: Collection<int, Expense>,
     *     by_caisse: list<array{id: int, nom: string, alloue: float, depense: float, solde: float}>
     * }
     */
    public function calculateCapitalUsageReport(int $paroisseId, Carbon $dateDebut, Carbon $dateFin): array
    {
        $capitals = Revenue::query()
            ->with(['category', 'type'])
            ->where('paroisse_id', $paroisseId)
            ->where('statut', 'valide')
            ->whereHas('category', fn ($q) => $q->where('code', 'banque'))
            ->whereDate('date_recette', '>=', $dateDebut)
            ->whereDate('date_recette', '<=', $dateFin)
            ->orderBy('date_recette')
            ->orderBy('id')
            ->get();

        $tresorerie = Caisse::query()
            ->where('paroisse_id', $paroisseId)
            ->where('code', Caisse::CODE_TRESORERIE)
            ->first();

        $virements = collect();
        if ($tresorerie) {
            $virements = CaisseMouvement::query()
                ->with(['caisse', 'contrepartieCaisse'])
                ->where('paroisse_id', $paroisseId)
                ->where('caisse_id', $tresorerie->id)
                ->where('type', CaisseMouvement::TYPE_VIREMENT)
                ->where('sens', CaisseMouvement::SENS_DEBIT)
                ->whereDate('date_mouvement', '>=', $dateDebut)
                ->whereDate('date_mouvement', '<=', $dateFin)
                ->orderBy('date_mouvement')
                ->orderBy('id')
                ->get();
        }

        $destinationCaisseIds = $virements
            ->pluck('contrepartie_caisse_id')
            ->filter()
            ->unique()
            ->values();

        $expensesQuery = Expense::query()
            ->with(['expenseType', 'fundingSources.caisse'])
            ->where('paroisse_id', $paroisseId)
            ->where('statut', 'valide')
            ->whereDate('date_depense', '>=', $dateDebut)
            ->whereDate('date_depense', '<=', $dateFin)
            ->whereHas('fundingSources', function ($q) use ($destinationCaisseIds): void {
                $q->whereNotNull('caisse_id');
                if ($destinationCaisseIds->isNotEmpty()) {
                    $q->whereIn('caisse_id', $destinationCaisseIds);
                }
            });

        if ($destinationCaisseIds->isEmpty()) {
            $expensesQuery = Expense::query()
                ->with(['expenseType', 'fundingSources.caisse'])
                ->where('paroisse_id', $paroisseId)
                ->where('statut', 'valide')
                ->whereDate('date_depense', '>=', $dateDebut)
                ->whereDate('date_depense', '<=', $dateFin)
                ->whereHas('fundingSources', function ($q): void {
                    $q->whereNotNull('caisse_id')
                        ->whereHas('caisse', fn ($caisse) => $caisse->where('est_tresorerie', false));
                });
        }

        $expenses = $expensesQuery->orderBy('date_depense')->orderBy('id')->get();

        $byCaisse = [];
        foreach ($virements as $virement) {
            $caisseId = (int) $virement->contrepartie_caisse_id;
            if ($caisseId < 1) {
                continue;
            }
            if (! isset($byCaisse[$caisseId])) {
                $byCaisse[$caisseId] = [
                    'id' => $caisseId,
                    'nom' => $virement->contrepartieCaisse?->nom ?? 'Caisse #'.$caisseId,
                    'alloue' => 0.0,
                    'depense' => 0.0,
                    'solde' => 0.0,
                ];
            }
            $byCaisse[$caisseId]['alloue'] += (float) $virement->montant;
        }

        $totalDepenses = 0.0;
        foreach ($expenses as $expense) {
            foreach ($expense->fundingSources as $source) {
                if (! $source->caisse_id) {
                    continue;
                }
                if ($destinationCaisseIds->isNotEmpty() && ! $destinationCaisseIds->contains((int) $source->caisse_id)) {
                    continue;
                }
                $allocated = (float) $source->montant_alloue;
                $totalDepenses += $allocated;
                $sourceCaisseId = (int) $source->caisse_id;
                if (! isset($byCaisse[$sourceCaisseId])) {
                    $byCaisse[$sourceCaisseId] = [
                        'id' => $sourceCaisseId,
                        'nom' => $source->caisse?->nom ?? 'Caisse #'.$sourceCaisseId,
                        'alloue' => 0.0,
                        'depense' => 0.0,
                        'solde' => 0.0,
                    ];
                }
                $byCaisse[$sourceCaisseId]['depense'] += $allocated;
            }
        }

        foreach ($byCaisse as &$row) {
            $row['solde'] = round($row['alloue'] - $row['depense'], 2);
        }
        unset($row);

        usort($byCaisse, fn (array $a, array $b): int => strcmp($a['nom'], $b['nom']));

        $totalCapital = (float) $capitals->sum('montant');
        $totalVirements = (float) $virements->sum('montant');

        return [
            'total_capital' => $totalCapital,
            'total_virements' => $totalVirements,
            'total_depenses' => $totalDepenses,
            'reste_alloue' => round($totalVirements - $totalDepenses, 2),
            'capitals' => $capitals,
            'virements' => $virements,
            'expenses' => $expenses,
            'by_caisse' => array_values($byCaisse),
        ];
    }

    /**
     * @return array{
     *     date_debut: Carbon,
     *     date_fin: Carbon,
     *     subvention_recue: float,
     *     total_depenses_alimentation: float,
     *     solde: float,
     *     credits: Collection<int, CaisseMouvement>,
     *     depenses: Collection<int, Expense>,
     *     monthly_summary: list<array{mois_subvention: string, mois_label: string, subvention_recue: float, depenses: float, solde: float}>
     * }
     */
    public function calculatePopoteReport(int $paroisseId, Carbon $dateDebut, Carbon $dateFin): array
    {
        $popoteCaisse = Caisse::query()
            ->where('paroisse_id', $paroisseId)
            ->where('code', FinancialStatisticsService::POPOTE_CAISSE_CODE)
            ->first();

        $credits = collect();
        $depensesAlimentation = collect();
        $subventionRecue = 0.0;
        $totalDepensesAlimentation = 0.0;
        $monthlySummary = [];

        if ($popoteCaisse) {
            $credits = CaisseMouvement::query()
                ->where('caisse_id', $popoteCaisse->id)
                ->where('sens', CaisseMouvement::SENS_CREDIT)
                ->whereDate('date_mouvement', '>=', $dateDebut)
                ->whereDate('date_mouvement', '<=', $dateFin)
                ->orderBy('date_mouvement')
                ->orderBy('id')
                ->get();

            $subventionRecue = (float) $credits->sum('montant');

            $depensesAlimentation = Expense::query()
                ->where('paroisse_id', $paroisseId)
                ->where('statut', 'valide')
                ->whereDate('date_depense', '>=', $dateDebut)
                ->whereDate('date_depense', '<=', $dateFin)
                ->whereHas('fundingSources', fn ($q) => $q->where('caisse_id', $popoteCaisse->id))
                ->with(['expenseType', 'fundingSources' => fn ($q) => $q->where('caisse_id', $popoteCaisse->id)])
                ->orderBy('date_depense')
                ->orderBy('id')
                ->get();

            $totalDepensesAlimentation = (float) ExpenseFundingSource::query()
                ->where('caisse_id', $popoteCaisse->id)
                ->whereHas('expense', function ($q) use ($paroisseId, $dateDebut, $dateFin): void {
                    $q->where('paroisse_id', $paroisseId)
                        ->where('statut', 'valide')
                        ->whereDate('date_depense', '>=', $dateDebut)
                        ->whereDate('date_depense', '<=', $dateFin);
                })
                ->sum('montant_alloue');

            $monthlySummary = $this->buildPopoteMonthlySummary(
                $paroisseId,
                $dateDebut,
                $dateFin,
                (int) $popoteCaisse->id
            );
        }

        return [
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
            'subvention_recue' => $subventionRecue,
            'total_depenses_alimentation' => $totalDepensesAlimentation,
            'solde' => $subventionRecue - $totalDepensesAlimentation,
            'credits' => $credits,
            'depenses' => $depensesAlimentation,
            'monthly_summary' => $monthlySummary,
        ];
    }

    /**
     * @param  array<int, array{key: string, caisse_id: int, nom: string, depenses: float, count: int}>  $buckets
     * @return list<array{key: string, caisse_id: int, nom: string, credits: float, depenses: float, solde: float, count: int}>
     */
    private function finalizeCaisseSummary(int $paroisseId, Carbon $dateDebut, Carbon $dateFin, array $buckets): array
    {
        $summary = [];

        foreach ($buckets as $bucket) {
            $credits = (float) CaisseMouvement::query()
                ->where('caisse_id', $bucket['caisse_id'])
                ->where('paroisse_id', $paroisseId)
                ->where('sens', CaisseMouvement::SENS_CREDIT)
                ->whereDate('date_mouvement', '>=', $dateDebut)
                ->whereDate('date_mouvement', '<=', $dateFin)
                ->sum('montant');

            $depenses = (float) $bucket['depenses'];

            $summary[] = [
                ...$bucket,
                'credits' => $credits,
                'solde' => $credits - $depenses,
            ];
        }

        usort($summary, fn (array $a, array $b): int => strcmp($a['nom'], $b['nom']));

        return $summary;
    }

    /**
     * @return list<array{mois_subvention: string, mois_label: string, subvention_recue: float, depenses: float, solde: float}>
     */
    private function buildPopoteMonthlySummary(int $paroisseId, Carbon $dateDebut, Carbon $dateFin, int $popoteCaisseId): array
    {
        $summary = [];
        $cursor = $dateDebut->copy()->startOfMonth();
        $endMonth = $dateFin->copy()->startOfMonth();

        while ($cursor->lte($endMonth)) {
            $monthStart = $cursor->copy()->startOfMonth();
            $monthEnd = $cursor->copy()->endOfMonth();
            $start = $monthStart->lt($dateDebut) ? $dateDebut->copy()->startOfDay() : $monthStart->copy()->startOfDay();
            $end = $monthEnd->gt($dateFin) ? $dateFin->copy()->endOfDay() : $monthEnd->copy()->endOfDay();

            $subventionRecue = (float) CaisseMouvement::query()
                ->where('caisse_id', $popoteCaisseId)
                ->where('sens', CaisseMouvement::SENS_CREDIT)
                ->whereDate('date_mouvement', '>=', $start)
                ->whereDate('date_mouvement', '<=', $end)
                ->sum('montant');

            $depenses = (float) ExpenseFundingSource::query()
                ->where('caisse_id', $popoteCaisseId)
                ->whereHas('expense', function ($q) use ($paroisseId, $start, $end): void {
                    $q->where('paroisse_id', $paroisseId)
                        ->where('statut', 'valide')
                        ->whereDate('date_depense', '>=', $start)
                        ->whereDate('date_depense', '<=', $end);
                })
                ->sum('montant_alloue');

            if ($subventionRecue === 0.0 && $depenses === 0.0) {
                $cursor->addMonth();

                continue;
            }

            $moisKey = SubventionMensuelle::moisSubventionFromParts((int) $cursor->year, (int) $cursor->month);
            $summary[] = [
                'mois_subvention' => $moisKey,
                'mois_label' => SubventionMensuelle::formatMoisLabel($moisKey),
                'subvention_recue' => $subventionRecue,
                'depenses' => $depenses,
                'solde' => $subventionRecue - $depenses,
            ];

            $cursor->addMonth();
        }

        return $summary;
    }
}
