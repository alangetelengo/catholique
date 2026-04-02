@extends('layouts.app')

@section('title', 'Statistiques financières - Catholique')
@section('page-title', 'Statistiques financières')
@section('page-title-info')
    <p class="text-slate-600 dark:text-slate-400">
        Le <strong>solde</strong> affiché correspond aux <strong>recettes moins les dépenses « Alimentation popote »</strong> uniquement.
        Les charges fixes, variables et exceptionnelles sont comptabilisées à titre <strong>informatif</strong> pour la hiérarchie et ne réduisent pas ce solde.
    </p>
@endsection

@section('content')
    @php
        $fcfa = static fn (float $v): string => number_format($v, 0, ',', ' ') . ' FCFA';
        $c = $snapshot['current'];
        $prev = $snapshot['previous'];
        $exportQuery = array_filter([
            'date_from' => $filters['date_from'],
            'date_to' => $filters['date_to'],
            'paroisse_id' => $filters['paroisse_id'],
        ]);
        if ($filters['compare_previous_year']) {
            $exportQuery['compare_previous_year'] = 1;
        }
    @endphp

    <div class="adventiste-card-pro-static p-4 sm:p-5 mb-5">
        <form method="get" action="{{ route('financial-statistics.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                <div>
                    <label for="date_from" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Date début</label>
                    <input id="date_from" type="date" name="date_from" value="{{ $filters['date_from'] }}"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label for="date_to" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Date fin</label>
                    <input id="date_to" type="date" name="date_to" value="{{ $filters['date_to'] }}"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm">
                </div>
                @if(auth()->user()?->hasRole('super_admin'))
                    <div>
                        <label for="paroisse_id" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Paroisse</label>
                        <select id="paroisse_id" name="paroisse_id"
                            class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm">
                            <option value="">Toutes les paroisses</option>
                            @foreach ($paroisses as $p)
                                <option value="{{ $p->id }}" {{ (string) $filters['paroisse_id'] === (string) $p->id ? 'selected' : '' }}>{{ $p->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="flex items-end">
                    <label class="flex items-center gap-2 cursor-pointer text-sm text-slate-700 dark:text-slate-300">
                        <input type="checkbox" name="compare_previous_year" value="1" class="rounded border-slate-300 dark:border-slate-600"
                            {{ $filters['compare_previous_year'] ? 'checked' : '' }}>
                        Comparer à N−1 (même plage, année précédente)
                    </label>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button type="submit" class="adventiste-btn-primary">Actualiser</button>
                <a href="{{ route('financial-statistics.index') }}" class="adventiste-btn-secondary">Réinitialiser</a>
                <span class="text-slate-400 dark:text-slate-500">|</span>
                <a href="{{ route('financial-statistics.pdf', $exportQuery) }}" class="adventiste-btn-secondary">Export PDF</a>
                <a href="{{ route('financial-statistics.excel', $exportQuery) }}" class="adventiste-btn-secondary">Export Excel</a>
            </div>
        </form>
    </div>

    <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">Période : <strong>{{ $snapshot['period_label'] }}</strong>
        @if($snapshot['paroisse'])
            — Paroisse : <strong>{{ $snapshot['paroisse']->nom }}</strong>
        @elseif(auth()->user()?->hasRole('super_admin') && !$filters['paroisse_id'])
            — <strong>Toutes les paroisses</strong>
        @endif
    </p>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
        <div class="adventiste-card-pro-static p-4">
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Total recettes</p>
            <p class="mt-1 text-xl font-bold text-emerald-700 dark:text-emerald-400">{{ $fcfa($c['total_revenues']) }}</p>
        </div>
        <div class="adventiste-card-pro-static p-4 border-l-4 border-rose-400/80">
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Dépenses popote (déductibles)</p>
            <p class="mt-1 text-xl font-bold text-rose-700 dark:text-rose-400">{{ $fcfa($c['total_expenses_popote']) }}</p>
        </div>
        <div class="adventiste-card-pro-static p-4 border-l-4 border-sky-500/80">
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Solde (recettes − popote)</p>
            <p class="mt-1 text-xl font-bold text-sky-800 dark:text-sky-300">{{ $fcfa($c['solde']) }}</p>
        </div>
        <div class="adventiste-card-pro-static p-4 opacity-95">
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Toutes dépenses (information)</p>
            <p class="mt-1 text-lg font-semibold text-slate-700 dark:text-slate-200">{{ $fcfa($c['total_expenses_all']) }}</p>
            <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">Non déduites du solde ci-dessus.</p>
        </div>
    </div>

    @if($prev)
        <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-3">Période N−1 (référence)</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="adventiste-card-pro-static p-4">
                <p class="text-xs text-slate-500 dark:text-slate-400">Recettes N−1</p>
                <p class="text-lg font-bold text-emerald-700 dark:text-emerald-400">{{ $fcfa($prev['total_revenues']) }}</p>
                @php $dRev = $c['total_revenues'] - $prev['total_revenues']; @endphp
                <p class="text-xs mt-1 {{ $dRev >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">Δ {{ $dRev >= 0 ? '+' : '' }}{{ $fcfa($dRev) }}</p>
            </div>
            <div class="adventiste-card-pro-static p-4">
                <p class="text-xs text-slate-500 dark:text-slate-400">Popote N−1</p>
                <p class="text-lg font-bold text-rose-700 dark:text-rose-400">{{ $fcfa($prev['total_expenses_popote']) }}</p>
            </div>
            <div class="adventiste-card-pro-static p-4">
                <p class="text-xs text-slate-500 dark:text-slate-400">Solde N−1</p>
                <p class="text-lg font-bold text-sky-800 dark:text-sky-300">{{ $fcfa($prev['solde']) }}</p>
                @php $dSol = $c['solde'] - $prev['solde']; @endphp
                <p class="text-xs mt-1 {{ $dSol >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">Δ {{ $dSol >= 0 ? '+' : '' }}{{ $fcfa($dSol) }}</p>
            </div>
        </div>
    @endif

    <div class="adventiste-card-pro-static p-4 sm:p-5 mb-6">
        <h2 class="text-base font-semibold text-slate-900 dark:text-white mb-2">Projection (indicatif)</h2>
        <p class="text-sm text-slate-600 dark:text-slate-400 mb-3">
            Moyenne journalière des recettes sur la période filtrée, extrapolée sur 365 jours — ne tient pas compte de la saisonnalité.
        </p>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
            <div><span class="text-slate-500 dark:text-slate-400">Jours couverts</span> — <strong>{{ $snapshot['forecast']['days_in_period'] }}</strong></div>
            <div><span class="text-slate-500 dark:text-slate-400">Moy. journalière recettes</span> — <strong>{{ $fcfa($snapshot['forecast']['daily_avg_revenue']) }}</strong></div>
            <div><span class="text-slate-500 dark:text-slate-400">Extrapolation 365 j.</span> — <strong>{{ $fcfa($snapshot['forecast']['projected_annual_revenue']) }}</strong></div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">
        <div class="adventiste-card-pro-static p-4 sm:p-5">
            <h2 class="text-base font-semibold text-slate-900 dark:text-white mb-3">Recettes par catégorie</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-600 dark:text-slate-300 border-b border-slate-200 dark:border-slate-600">
                            <th class="py-2 pr-3">Catégorie</th>
                            <th class="py-2 text-right">Montant</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($c['revenue_by_category'] as $row)
                            <tr class="border-b border-slate-100 dark:border-slate-700/80">
                                <td class="py-2 pr-3">{{ $row['label'] }}</td>
                                <td class="py-2 text-right font-medium">{{ $fcfa($row['total']) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="py-4 text-slate-500">Aucune recette sur la période.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="adventiste-card-pro-static p-4 sm:p-5">
            <h2 class="text-base font-semibold text-slate-900 dark:text-white mb-3">Dépenses par catégorie</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-600 dark:text-slate-300 border-b border-slate-200 dark:border-slate-600">
                            <th class="py-2 pr-3">Catégorie</th>
                            <th class="py-2 text-right">Montant</th>
                            <th class="py-2 text-center">Solde</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($c['expense_by_category'] as $row)
                            <tr class="border-b border-slate-100 dark:border-slate-700/80">
                                <td class="py-2 pr-3">{{ $row['label'] }}</td>
                                <td class="py-2 text-right font-medium">{{ $fcfa($row['total']) }}</td>
                                <td class="py-2 text-center text-xs">
                                    @if($row['deductible'])
                                        <span class="rounded-full bg-rose-100 dark:bg-rose-900/40 px-2 py-0.5 text-rose-800 dark:text-rose-200">Déduit</span>
                                    @else
                                        <span class="text-slate-500">Info</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="py-4 text-slate-500">Aucune dépense sur la période.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-3">Graphiques</h2>
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-5 mb-5">
        <div class="adventiste-card-pro-static p-4 sm:p-5">
            <div class="h-72 w-full min-h-[18rem]">
                <canvas id="chart-financial-monthly" aria-label="Graphique mensuel"></canvas>
            </div>
        </div>
        @if($filters['compare_previous_year'])
            <div class="adventiste-card-pro-static p-4 sm:p-5">
                <div class="h-72 w-full min-h-[18rem]">
                    <canvas id="chart-financial-compare" aria-label="Comparaison année précédente"></canvas>
                </div>
            </div>
        @else
            <div class="adventiste-card-pro-static p-4 sm:p-5 flex items-center justify-center text-sm text-slate-500 dark:text-slate-400">
                Cochez « Comparer à N−1 » pour afficher le graphique d’évolution des recettes par rapport à l’année précédente.
            </div>
        @endif
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-8">
        <div class="adventiste-card-pro-static p-4 sm:p-5">
            <div class="h-64 w-full min-h-[16rem]">
                <canvas id="chart-pie-revenues" aria-label="Répartition recettes"></canvas>
            </div>
        </div>
        <div class="adventiste-card-pro-static p-4 sm:p-5">
            <div class="h-64 w-full min-h-[16rem]">
                <canvas id="chart-pie-expenses" aria-label="Répartition dépenses"></canvas>
            </div>
        </div>
    </div>

    <script type="application/json" id="financial-stats-chart-data">{!! json_encode($snapshot['chart']) !!}</script>
@endsection

@push('scripts')
    @vite(['resources/js/financial-statistics-charts.js'])
@endpush
