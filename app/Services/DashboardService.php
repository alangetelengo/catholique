<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Inventory;
use App\Models\Revenue;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Données agrégées pour la page d’accueil / tableau de bord.
 * S’appuie sur FinancialStatisticsService pour les totaux et le graphique (règle du solde identique aux statistiques).
 */
class DashboardService
{
    public function __construct(
        private readonly FinancialStatisticsService $statistics
    ) {}

    /**
     * @return array{
     *     preset: string,
     *     date_from: string,
     *     date_to: string,
     *     paroisse_id: int|null,
     *     period_label: string
     * }
     */
    public function resolvePeriod(Request $request): array
    {
        $preset = $request->string('period')->value() ?: 'month';
        $allowed = ['month', 'year', '30', 'custom'];
        if (! in_array($preset, $allowed, true)) {
            $preset = 'month';
        }

        $now = now();

        if ($preset === 'custom' && $request->filled('date_from') && $request->filled('date_to')) {
            $from = Carbon::parse($request->string('date_from')->value())->startOfDay();
            $to = Carbon::parse($request->string('date_to')->value())->endOfDay();
            if ($from->gt($to)) {
                [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
            }
        } else {
            $from = match ($preset) {
                'year' => $now->copy()->startOfYear(),
                '30' => $now->copy()->subDays(29)->startOfDay(),
                default => $now->copy()->startOfMonth(),
            };
            $to = $now->copy()->endOfDay();
        }

        $paroisseId = $this->resolveParoisseId($request);

        return [
            'preset' => $preset,
            'date_from' => $from->toDateString(),
            'date_to' => $to->toDateString(),
            'paroisse_id' => $paroisseId,
            'period_label' => $from->format('d/m/Y').' — '.$to->format('d/m/Y'),
        ];
    }

    /**
     * @param  array{preset: string, date_from: string, date_to: string, paroisse_id: int|null, period_label: string}  $period
     * @return array<string, mixed>
     */
    public function build(Request $request, array $period): array
    {
        $from = Carbon::parse($period['date_from'])->startOfDay();
        $to = Carbon::parse($period['date_to'])->endOfDay();
        $paroisseId = $period['paroisse_id'];

        $totals = $this->statistics->quickFinancialTotals($from, $to, $paroisseId);
        $chart = $this->statistics->chartSeriesForDashboard($from, $to, $paroisseId);

        $days = max(1, $from->diffInDays($to) + 1);
        $forecast = [
            'daily_avg_revenue' => round($totals['total_revenues'] / $days, 2),
            'projected_365' => round(($totals['total_revenues'] / $days) * 365, 2),
        ];

        return [
            'period' => $period,
            'totals' => $totals,
            'chart' => $chart,
            'forecast' => $forecast,
            'recent_revenues' => $this->recentRevenues($paroisseId),
            'recent_expenses' => $this->recentExpenses($paroisseId),
            'inventory_count' => $this->inventoryCount($paroisseId),
            'users_count' => $request->user()?->hasRole('super_admin') ? User::query()->count() : null,
        ];
    }

    private function resolveParoisseId(Request $request): ?int
    {
        $user = $request->user();
        if ($user?->hasRole('super_admin')) {
            return $request->filled('paroisse_id') ? (int) $request->integer('paroisse_id') : null;
        }

        return $user?->paroisse_id ? (int) $user->paroisse_id : null;
    }

    /**
     * @return Collection<int, Revenue>
     */
    private function recentRevenues(?int $paroisseId): Collection
    {
        return Revenue::query()
            ->with(['category', 'type'])
            ->when($paroisseId !== null, fn (Builder $q) => $q->where('paroisse_id', $paroisseId))
            ->orderByDesc('date_recette')
            ->orderByDesc('id')
            ->limit(5)
            ->get();
    }

    /**
     * @return Collection<int, Expense>
     */
    private function recentExpenses(?int $paroisseId): Collection
    {
        return Expense::query()
            ->when($paroisseId !== null, fn (Builder $q) => $q->where('paroisse_id', $paroisseId))
            ->orderByDesc('date_depense')
            ->orderByDesc('id')
            ->limit(5)
            ->get();
    }

    private function inventoryCount(?int $paroisseId): int
    {
        return Inventory::query()
            ->when($paroisseId !== null, fn (Builder $q) => $q->where('paroisse_id', $paroisseId))
            ->count();
    }
}
