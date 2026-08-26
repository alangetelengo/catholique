<?php

namespace App\Services;

use App\Models\Caisse;
use App\Models\Expense;
use App\Models\Paroisse;
use App\Models\Revenue;
use App\Models\RevenueCategory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Agrégations recettes / dépenses pour la page Statistiques financières.
 *
 * Règle comptable : le solde affiché = total recettes − dépenses financées par la caisse Popote.
 * Les autres dépenses sont suivies à part (information hiérarchie) sans impacter ce solde.
 */
class FinancialStatisticsService
{
    /** Code caisse Popote (dépenses déductibles du solde). */
    public const POPOTE_CAISSE_CODE = 'alimentation_popote';

    /**
     * @deprecated Conservé pour lecture d'anciennes allocations sans caisse_id
     */
    public const POPOTE_TYPE_CODE = 'subvention_popote';

    /**
     * @return array{
     *     date_from: string,
     *     date_to: string,
     *     paroisse_id: int|null,
     *     compare_previous_year: bool
     * }
     */
    public function resolveFilters(Request $request): array
    {
        $user = $request->user();
        $defaultFrom = now()->startOfYear()->toDateString();
        $defaultTo = now()->toDateString();

        $dateFrom = $request->filled('date_from')
            ? Carbon::parse($request->string('date_from')->value())->toDateString()
            : $defaultFrom;
        $dateTo = $request->filled('date_to')
            ? Carbon::parse($request->string('date_to')->value())->toDateString()
            : $defaultTo;

        if ($dateFrom > $dateTo) {
            [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
        }

        $paroisseId = null;
        if ($user?->hasRole('super_admin')) {
            $paroisseId = $request->filled('paroisse_id') ? (int) $request->integer('paroisse_id') : null;
        } else {
            $paroisseId = $user?->paroisse_id ? (int) $user->paroisse_id : null;
        }

        return [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'paroisse_id' => $paroisseId,
            'compare_previous_year' => $request->boolean('compare_previous_year'),
        ];
    }

    /**
     * @param  array{date_from: string, date_to: string, paroisse_id: int|null, compare_previous_year: bool}  $filters
     * @return array<string, mixed>
     */
    public function buildSnapshot(array $filters): array
    {
        $from = Carbon::parse($filters['date_from'])->startOfDay();
        $to = Carbon::parse($filters['date_to'])->endOfDay();
        $paroisseId = $filters['paroisse_id'];

        $current = $this->aggregatePeriod($from, $to, $paroisseId);

        $previous = null;
        if ($filters['compare_previous_year']) {
            $fromPrev = $from->copy()->subYear();
            $toPrev = $to->copy()->subYear();
            $previous = $this->aggregatePeriod($fromPrev, $toPrev, $paroisseId);
        }

        $paroisse = $paroisseId ? Paroisse::query()->find($paroisseId) : null;

        $days = max(1, $from->diffInDays($to) + 1);
        $dailyAvgRevenue = $current['total_revenues'] / $days;
        $projected365 = $dailyAvgRevenue * 365;

        return [
            'filters' => $filters,
            'period_label' => $from->format('d/m/Y').' — '.$to->format('d/m/Y'),
            'paroisse' => $paroisse,
            'current' => $current,
            'previous' => $previous,
            'forecast' => [
                'days_in_period' => $days,
                'daily_avg_revenue' => round($dailyAvgRevenue, 2),
                'projected_annual_revenue' => round($projected365, 2),
            ],
            'chart' => $this->buildChartPayload($from, $to, $paroisseId, $filters['compare_previous_year']),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function aggregatePeriod(Carbon $from, Carbon $to, ?int $paroisseId): array
    {
        $fromStr = $from->toDateString();
        $toStr = $to->toDateString();

        $revQ = $this->scopedRevenues($fromStr, $toStr, $paroisseId);
        $expQ = $this->scopedExpenses($fromStr, $toStr, $paroisseId);

        $totalRevenues = (float) (clone $revQ)->sum('montant');
        $totalExpensesAll = (float) (clone $expQ)->sum('montant');
        $totalPopote = $this->sumPopoteAllocated($fromStr, $toStr, $paroisseId);
        $solde = $totalRevenues - $totalPopote;

        return [
            'total_revenues' => $totalRevenues,
            'total_expenses_all' => $totalExpensesAll,
            'total_expenses_popote' => $totalPopote,
            'solde' => $solde,
            'revenue_by_category' => $this->revenueBreakdown($fromStr, $toStr, $paroisseId),
            'expense_by_category' => $this->expenseBreakdown($fromStr, $toStr, $paroisseId),
            'monthly_revenues' => $this->monthlyTotals('revenues', 'date_recette', $fromStr, $toStr, $paroisseId),
            'monthly_popote' => $this->monthlyPopoteExpenses($fromStr, $toStr, $paroisseId),
            'monthly_other_expenses' => $this->monthlyNonPopoteExpenses($fromStr, $toStr, $paroisseId),
        ];
    }

    private function scopedRevenues(string $from, string $to, ?int $paroisseId): Builder
    {
        $q = Revenue::query()
            ->where('statut', 'valide')
            ->whereBetween('date_recette', [$from, $to]);
        if ($paroisseId !== null) {
            $q->where('paroisse_id', $paroisseId);
        }

        return $q;
    }

    private function scopedExpenses(string $from, string $to, ?int $paroisseId): Builder
    {
        $q = Expense::query()
            ->where('statut', 'valide')
            ->whereBetween('date_depense', [$from, $to]);
        if ($paroisseId !== null) {
            $q->where('paroisse_id', $paroisseId);
        }

        return $q;
    }

    /**
     * @return Collection<int, array{label: string, total: float, category_id: int|null}>
     */
    private function revenueBreakdown(string $from, string $to, ?int $paroisseId): Collection
    {
        $rows = Revenue::query()
            ->where('statut', 'valide')
            ->whereBetween('date_recette', [$from, $to])
            ->when($paroisseId !== null, fn (Builder $q) => $q->where('paroisse_id', $paroisseId))
            ->selectRaw('revenue_category_id, SUM(montant) as total')
            ->groupBy('revenue_category_id')
            ->get();

        $names = RevenueCategory::query()->pluck('nom', 'id');

        return $rows->map(function ($row) use ($names) {
            $id = $row->revenue_category_id;

            return [
                'category_id' => $id,
                'label' => $names[$id] ?? 'Sans catégorie',
                'total' => (float) $row->total,
            ];
        })->sortByDesc('total')->values();
    }

    /**
     * @return Collection<int, array{key: string, label: string, total: float, deductible: bool}>
     */
    private function expenseBreakdown(string $from, string $to, ?int $paroisseId): Collection
    {
        $rows = DB::table('expense_funding_sources as efs')
            ->join('expenses as e', 'e.id', '=', 'efs.expense_id')
            ->leftJoin('caisses as c', 'c.id', '=', 'efs.caisse_id')
            ->where('e.statut', 'valide')
            ->whereBetween('e.date_depense', [$from, $to])
            ->whereNull('e.deleted_at')
            ->when($paroisseId !== null, fn ($q) => $q->where('e.paroisse_id', $paroisseId))
            ->selectRaw("COALESCE(c.code, 'legacy') as caisse_code, COALESCE(c.nom, 'Anciennes sources') as caisse_nom, SUM(efs.montant_alloue) as total")
            ->groupByRaw('COALESCE(c.code, \'legacy\'), COALESCE(c.nom, \'Anciennes sources\')')
            ->get();

        return $rows->map(function ($row) {
            return [
                'key' => (string) $row->caisse_code,
                'label' => (string) $row->caisse_nom,
                'total' => (float) $row->total,
                'deductible' => $row->caisse_code === self::POPOTE_CAISSE_CODE,
            ];
        })->sortByDesc('total')->values();
    }

    /**
     * @return array<string, float>
     */
    private function monthlyTotals(string $table, string $dateColumn, string $from, string $to, ?int $paroisseId): array
    {
        $expr = $this->monthSqlExpression($dateColumn);
        $model = $table === 'revenues' ? Revenue::query() : Expense::query();
        $rows = $model
            ->where('statut', 'valide')
            ->whereBetween($dateColumn, [$from, $to])
            ->when($paroisseId !== null, fn (Builder $q) => $q->where('paroisse_id', $paroisseId))
            ->selectRaw("{$expr} as ym, SUM(montant) as total")
            ->groupByRaw($expr)
            ->orderBy('ym')
            ->get();

        return $this->fillMonthRange($from, $to, $rows->pluck('total', 'ym'));
    }

    /**
     * @return array<string, float>
     */
    private function monthlyPopoteExpenses(string $from, string $to, ?int $paroisseId): array
    {
        $expr = $this->monthSqlExpression('date_depense');
        $popoteCaisseIds = $this->popoteCaisseIds($paroisseId);

        $rows = DB::table('expense_funding_sources as efs')
            ->join('expenses as e', 'e.id', '=', 'efs.expense_id')
            ->where('e.statut', 'valide')
            ->whereBetween('e.date_depense', [$from, $to])
            ->whereNull('e.deleted_at')
            ->when($paroisseId !== null, fn ($q) => $q->where('e.paroisse_id', $paroisseId))
            ->where(function ($q) use ($popoteCaisseIds): void {
                if ($popoteCaisseIds->isNotEmpty()) {
                    $q->whereIn('efs.caisse_id', $popoteCaisseIds);
                } else {
                    $q->whereRaw('0 = 1');
                }
                $q->orWhere(function ($legacy): void {
                    $legacy->whereNull('efs.caisse_id')
                        ->whereExists(function ($sub): void {
                            $sub->select(DB::raw(1))
                                ->from('revenue_types as rt')
                                ->whereColumn('rt.id', 'efs.revenue_type_id')
                                ->where('rt.code', self::POPOTE_TYPE_CODE);
                        });
                });
            })
            ->selectRaw("{$expr} as ym, SUM(efs.montant_alloue) as total")
            ->groupByRaw($expr)
            ->orderBy('ym')
            ->get();

        return $this->fillMonthRange($from, $to, $rows->pluck('total', 'ym'));
    }

    /**
     * @return array<string, float>
     */
    private function monthlyNonPopoteExpenses(string $from, string $to, ?int $paroisseId): array
    {
        $expr = $this->monthSqlExpression('date_depense');
        $popoteCaisseIds = $this->popoteCaisseIds($paroisseId);

        $rows = DB::table('expense_funding_sources as efs')
            ->join('expenses as e', 'e.id', '=', 'efs.expense_id')
            ->where('e.statut', 'valide')
            ->whereBetween('e.date_depense', [$from, $to])
            ->whereNull('e.deleted_at')
            ->when($paroisseId !== null, fn ($q) => $q->where('e.paroisse_id', $paroisseId))
            ->where(function ($q) use ($popoteCaisseIds): void {
                $q->where(function ($notPopote) use ($popoteCaisseIds): void {
                    if ($popoteCaisseIds->isNotEmpty()) {
                        $notPopote->whereNull('efs.caisse_id')
                            ->orWhereNotIn('efs.caisse_id', $popoteCaisseIds);
                    } else {
                        $notPopote->whereNotNull('efs.caisse_id')
                            ->orWhereNull('efs.caisse_id');
                    }
                })->where(function ($notLegacy): void {
                    $notLegacy->whereNotNull('efs.caisse_id')
                        ->orWhereNotExists(function ($sub): void {
                            $sub->select(DB::raw(1))
                                ->from('revenue_types as rt')
                                ->whereColumn('rt.id', 'efs.revenue_type_id')
                                ->where('rt.code', self::POPOTE_TYPE_CODE);
                        });
                });
            })
            ->selectRaw("{$expr} as ym, SUM(efs.montant_alloue) as total")
            ->groupByRaw($expr)
            ->orderBy('ym')
            ->get();

        return $this->fillMonthRange($from, $to, $rows->pluck('total', 'ym'));
    }

    private function monthSqlExpression(string $column): string
    {
        $safe = preg_match('/^[a-z_]+$/i', $column) ? $column : 'date_recette';

        return match (DB::connection()->getDriverName()) {
            'sqlite' => "strftime('%Y-%m', {$safe})",
            'pgsql' => "to_char({$safe}, 'YYYY-MM')",
            default => "DATE_FORMAT({$safe}, '%Y-%m')",
        };
    }

    /**
     * @param  Collection<string, mixed>  $ymTotals
     * @return array<string, float>
     */
    private function fillMonthRange(string $from, string $to, Collection $ymTotals): array
    {
        $start = Carbon::parse($from)->startOfMonth();
        $end = Carbon::parse($to)->startOfMonth();
        $out = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $key = $cursor->format('Y-m');
            $out[$key] = (float) ($ymTotals[$key] ?? 0);
            $cursor->addMonth();
        }

        return $out;
    }

    private function buildChartPayload(Carbon $from, Carbon $to, ?int $paroisseId, bool $compare): array
    {
        $fromStr = $from->toDateString();
        $toStr = $to->toDateString();
        $monthlyRev = $this->monthlyTotals('revenues', 'date_recette', $fromStr, $toStr, $paroisseId);
        $monthlyPop = $this->monthlyPopoteExpenses($fromStr, $toStr, $paroisseId);
        $monthlyOther = $this->monthlyNonPopoteExpenses($fromStr, $toStr, $paroisseId);
        $labels = array_keys($monthlyRev);

        $payload = [
            'labels' => $labels,
            'datasets' => [
                'revenues' => array_values($monthlyRev),
                'popote_deductible' => array_values($monthlyPop),
                'other_expenses_info' => array_values($monthlyOther),
            ],
            'compare' => $compare,
        ];

        if ($compare) {
            $pFrom = $from->copy()->subYear()->toDateString();
            $pTo = $to->copy()->subYear()->toDateString();
            $prevRev = $this->monthlyTotals('revenues', 'date_recette', $pFrom, $pTo, $paroisseId);
            $prevPop = $this->monthlyPopoteExpenses($pFrom, $pTo, $paroisseId);
            $payload['datasets']['revenues_previous_year'] = [];
            $payload['datasets']['popote_previous_year'] = [];
            foreach ($labels as $ym) {
                $prevYm = Carbon::createFromFormat('Y-m', $ym)->subYear()->format('Y-m');
                $payload['datasets']['revenues_previous_year'][] = (float) ($prevRev[$prevYm] ?? 0);
                $payload['datasets']['popote_previous_year'][] = (float) ($prevPop[$prevYm] ?? 0);
            }
        }

        return $payload;
    }

    /**
     * @return array{total_revenues: float, total_expenses_all: float, total_expenses_popote: float, solde: float}
     */
    public function quickFinancialTotals(Carbon $from, Carbon $to, ?int $paroisseId): array
    {
        $fromStr = $from->toDateString();
        $toStr = $to->toDateString();
        $totalRevenues = (float) (clone $this->scopedRevenues($fromStr, $toStr, $paroisseId))->sum('montant');
        $totalExpensesAll = (float) (clone $this->scopedExpenses($fromStr, $toStr, $paroisseId))->sum('montant');
        $totalPopote = $this->sumPopoteAllocated($fromStr, $toStr, $paroisseId);

        return [
            'total_revenues' => $totalRevenues,
            'total_expenses_all' => $totalExpensesAll,
            'total_expenses_popote' => $totalPopote,
            'solde' => $totalRevenues - $totalPopote,
        ];
    }

    /**
     * @return array{granularity: string, labels: array<int, string>, revenues: float[], popote: float[]}
     */
    public function chartSeriesForDashboard(Carbon $from, Carbon $to, ?int $paroisseId): array
    {
        $days = $from->copy()->startOfDay()->diffInDays($to->copy()->startOfDay()) + 1;
        if ($days <= 90) {
            $daily = $this->dailyChartSeries($from, $to, $paroisseId);

            return [
                'granularity' => 'day',
                'labels' => $daily['labels'],
                'revenues' => $daily['revenues'],
                'popote' => $daily['popote'],
            ];
        }

        $fromStr = $from->toDateString();
        $toStr = $to->toDateString();
        $monthlyRev = $this->monthlyTotals('revenues', 'date_recette', $fromStr, $toStr, $paroisseId);
        $monthlyPop = $this->monthlyPopoteExpenses($fromStr, $toStr, $paroisseId);
        $keys = array_keys($monthlyRev);

        return [
            'granularity' => 'month',
            'labels' => $keys,
            'revenues' => array_values($monthlyRev),
            'popote' => array_map(fn (string $k): float => (float) ($monthlyPop[$k] ?? 0), $keys),
        ];
    }

    /**
     * @return array{labels: string[], revenues: float[], popote: float[]}
     */
    private function dailyChartSeries(Carbon $from, Carbon $to, ?int $paroisseId): array
    {
        $fromStr = $from->toDateString();
        $toStr = $to->toDateString();
        $dayExprRev = $this->daySqlExpression('date_recette');
        $dayExprExp = $this->daySqlExpression('date_depense');

        $revRows = Revenue::query()
            ->where('statut', 'valide')
            ->whereBetween('date_recette', [$fromStr, $toStr])
            ->when($paroisseId !== null, fn (Builder $q) => $q->where('paroisse_id', $paroisseId))
            ->selectRaw("{$dayExprRev} as d, SUM(montant) as total")
            ->groupByRaw($dayExprRev)
            ->pluck('total', 'd');

        $popoteCaisseIds = $this->popoteCaisseIds($paroisseId);
        $popRows = DB::table('expense_funding_sources as efs')
            ->join('expenses as e', 'e.id', '=', 'efs.expense_id')
            ->where('e.statut', 'valide')
            ->whereBetween('e.date_depense', [$fromStr, $toStr])
            ->whereNull('e.deleted_at')
            ->when($paroisseId !== null, fn ($q) => $q->where('e.paroisse_id', $paroisseId))
            ->where(function ($q) use ($popoteCaisseIds): void {
                if ($popoteCaisseIds->isNotEmpty()) {
                    $q->whereIn('efs.caisse_id', $popoteCaisseIds);
                } else {
                    $q->whereRaw('0 = 1');
                }
                $q->orWhere(function ($legacy): void {
                    $legacy->whereNull('efs.caisse_id')
                        ->whereExists(function ($sub): void {
                            $sub->select(DB::raw(1))
                                ->from('revenue_types as rt')
                                ->whereColumn('rt.id', 'efs.revenue_type_id')
                                ->where('rt.code', self::POPOTE_TYPE_CODE);
                        });
                });
            })
            ->selectRaw("{$dayExprExp} as d, SUM(efs.montant_alloue) as total")
            ->groupByRaw($dayExprExp)
            ->pluck('total', 'd');

        $labels = [];
        $revenues = [];
        $popote = [];
        $cursor = $from->copy()->startOfDay();
        $end = $to->copy()->startOfDay();
        while ($cursor->lte($end)) {
            $key = $cursor->toDateString();
            $labels[] = $key;
            $revenues[] = (float) ($revRows[$key] ?? 0);
            $popote[] = (float) ($popRows[$key] ?? 0);
            $cursor->addDay();
        }

        return [
            'labels' => $labels,
            'revenues' => $revenues,
            'popote' => $popote,
        ];
    }

    /**
     * @return Collection<int, int>
     */
    private function popoteCaisseIds(?int $paroisseId): Collection
    {
        return Caisse::query()
            ->where('code', self::POPOTE_CAISSE_CODE)
            ->when($paroisseId !== null, fn ($q) => $q->where('paroisse_id', $paroisseId))
            ->pluck('id');
    }

    private function sumPopoteAllocated(string $from, string $to, ?int $paroisseId): float
    {
        $popoteCaisseIds = $this->popoteCaisseIds($paroisseId);

        return (float) DB::table('expense_funding_sources as efs')
            ->join('expenses as e', 'e.id', '=', 'efs.expense_id')
            ->where('e.statut', 'valide')
            ->whereBetween('e.date_depense', [$from, $to])
            ->whereNull('e.deleted_at')
            ->when($paroisseId !== null, fn ($q) => $q->where('e.paroisse_id', $paroisseId))
            ->where(function ($q) use ($popoteCaisseIds): void {
                if ($popoteCaisseIds->isNotEmpty()) {
                    $q->whereIn('efs.caisse_id', $popoteCaisseIds);
                } else {
                    $q->whereRaw('0 = 1');
                }
                $q->orWhere(function ($legacy): void {
                    $legacy->whereNull('efs.caisse_id')
                        ->whereExists(function ($sub): void {
                            $sub->select(DB::raw(1))
                                ->from('revenue_types as rt')
                                ->whereColumn('rt.id', 'efs.revenue_type_id')
                                ->where('rt.code', self::POPOTE_TYPE_CODE);
                        });
                });
            })
            ->sum('efs.montant_alloue');
    }

    private function daySqlExpression(string $column): string
    {
        $safe = preg_match('/^[a-z_]+$/i', $column) ? $column : 'date_recette';

        return match (DB::connection()->getDriverName()) {
            'sqlite' => "date({$safe})",
            'pgsql' => "to_char({$safe}::date, 'YYYY-MM-DD')",
            default => "DATE({$safe})",
        };
    }
}
