@extends('layouts.app')

@section('title', 'Tableau de bord - Catholique')
@section('page-title', 'Tableau de bord')
@section('page-title-info', 'Vue synthétique de l’activité financière récente et accès rapides.')

@section('content')
    @php
        $fcfa = static fn (float $v): string => number_format($v, 0, ',', ' ') . ' FCFA';
        $t = $data['totals'];
        $p = $period;
        $expenseCats = [
            'charge_fixe' => 'Charge fixe',
            'charge_variable' => 'Charge variable',
            'charge_exceptionnelle' => 'Charge exceptionnelle',
            'alimentation_popote' => 'Alimentation popote',
        ];
        $queryBase = array_filter([
            'period' => $p['preset'],
            'date_from' => $p['preset'] === 'custom' ? $p['date_from'] : null,
            'date_to' => $p['preset'] === 'custom' ? $p['date_to'] : null,
            'paroisse_id' => $p['paroisse_id'],
        ]);
    @endphp

    <details class="adventiste-card-pro-static p-4 sm:p-5 mb-5 group">
        <summary class="cursor-pointer text-sm font-medium text-slate-800 dark:text-slate-200 list-none flex items-center gap-2">
            <span class="text-slate-500 group-open:rotate-90 transition-transform inline-block">▸</span>
            Règle du solde affiché
        </summary>
        <p class="mt-3 text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
            Le <strong>solde</strong> = recettes − dépenses <strong>Alimentation popote</strong> uniquement.
            Les autres dépenses figurent dans le total « information » et ne diminuent pas ce solde.
        </p>
    </details>

    <div class="adventiste-card-pro-static p-4 sm:p-5 mb-5">
        <form method="get" action="{{ route('home') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                <div>
                    <label for="period" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Période</label>
                    <select id="period" name="period" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm">
                        <option value="month" {{ $p['preset'] === 'month' ? 'selected' : '' }}>Mois en cours</option>
                        <option value="year" {{ $p['preset'] === 'year' ? 'selected' : '' }}>Année en cours</option>
                        <option value="30" {{ $p['preset'] === '30' ? 'selected' : '' }}>30 derniers jours</option>
                        <option value="custom" {{ $p['preset'] === 'custom' ? 'selected' : '' }}>Personnalisée</option>
                    </select>
                </div>
                <div>
                    <label for="date_from" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Date début (perso.)</label>
                    <input id="date_from" type="date" name="date_from" value="{{ $p['date_from'] }}"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label for="date_to" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Date fin (perso.)</label>
                    <input id="date_to" type="date" name="date_to" value="{{ $p['date_to'] }}"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm">
                </div>
                @if(auth()->user()?->hasRole('super_admin'))
                    <div>
                        <label for="paroisse_id" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Paroisse</label>
                        <select id="paroisse_id" name="paroisse_id"
                            class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm">
                            <option value="">Toutes</option>
                            @foreach ($paroisses as $par)
                                <option value="{{ $par->id }}" {{ (string) $p['paroisse_id'] === (string) $par->id ? 'selected' : '' }}>{{ $par->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>
            <button type="submit" class="adventiste-btn-primary">Actualiser</button>
        </form>
        <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">Période affichée : <strong>{{ $p['period_label'] }}</strong></p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3 mb-5">
        <div class="adventiste-card-pro-static p-4">
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Recettes</p>
            <p class="mt-1 text-lg font-bold text-emerald-700 dark:text-emerald-400">{{ $fcfa($t['total_revenues']) }}</p>
        </div>
        <div class="adventiste-card-pro-static p-4 border-l-4 border-rose-400/70">
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Popote (déduct.)</p>
            <p class="mt-1 text-lg font-bold text-rose-700 dark:text-rose-400">{{ $fcfa($t['total_expenses_popote']) }}</p>
        </div>
        <div class="adventiste-card-pro-static p-4 border-l-4 border-sky-500/70">
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Solde</p>
            <p class="mt-1 text-lg font-bold text-sky-800 dark:text-sky-300">{{ $fcfa($t['solde']) }}</p>
        </div>
        <div class="adventiste-card-pro-static p-4">
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Toutes dép. (info)</p>
            <p class="mt-1 text-base font-semibold text-slate-700 dark:text-slate-200">{{ $fcfa($t['total_expenses_all']) }}</p>
        </div>
        <div class="adventiste-card-pro-static p-4">
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Articles inventaire</p>
            <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">{{ $data['inventory_count'] }}</p>
            <a href="{{ route('inventories.index', array_filter(['paroisse_id' => $p['paroisse_id']])) }}" class="text-xs text-emerald-700 dark:text-emerald-400 hover:underline mt-1 inline-block">Voir</a>
        </div>
        @if($data['users_count'] !== null)
            <div class="adventiste-card-pro-static p-4">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Utilisateurs</p>
                <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">{{ $data['users_count'] }}</p>
                <a href="{{ route('users.index') }}" class="text-xs text-emerald-700 dark:text-emerald-400 hover:underline mt-1 inline-block">Gérer</a>
            </div>
        @endif
    </div>

    <div class="adventiste-card-pro-static p-4 sm:p-5 mb-5">
        <h2 class="text-base font-semibold text-slate-900 dark:text-white mb-2">Projection indicative</h2>
        <p class="text-sm text-slate-600 dark:text-slate-400 mb-2">Moyenne journalière des recettes sur la période × 365 (non saisonnalisée).</p>
        <p class="text-sm"><span class="text-slate-500 dark:text-slate-400">Moy. / jour</span> — <strong>{{ $fcfa($data['forecast']['daily_avg_revenue']) }}</strong>
            &nbsp;·&nbsp; <span class="text-slate-500 dark:text-slate-400">Extrapol. 365 j.</span> — <strong>{{ $fcfa($data['forecast']['projected_365']) }}</strong></p>
    </div>

    <div class="adventiste-card-pro-static p-4 sm:p-5 mb-5">
        <div class="h-72 w-full min-h-[18rem]">
            <canvas id="chart-dashboard-trend" aria-label="Tendance recettes et popote"></canvas>
        </div>
    </div>

    <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-3">Raccourcis</h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 mb-8">
        <a href="{{ route('revenues.create') }}" class="adventiste-card-pro-static p-4 text-center text-sm font-medium text-emerald-800 dark:text-emerald-300 hover:ring-2 hover:ring-emerald-500/30 transition-shadow">+ Recette</a>
        <a href="{{ route('expenses.create') }}" class="adventiste-card-pro-static p-4 text-center text-sm font-medium text-rose-800 dark:text-rose-300 hover:ring-2 hover:ring-rose-500/30 transition-shadow">+ Dépense</a>
        <a href="{{ route('financial-statistics.index') }}" class="adventiste-card-pro-static p-4 text-center text-sm font-medium text-sky-800 dark:text-sky-300 hover:ring-2 hover:ring-sky-500/30 transition-shadow">Statistiques</a>
        <a href="{{ route('revenue-reports.create') }}" class="adventiste-card-pro-static p-4 text-center text-sm font-medium text-slate-800 dark:text-slate-200 hover:ring-2 hover:ring-slate-400/30 transition-shadow">Rapport</a>
        <a href="{{ route('revenue-reports.index') }}" class="adventiste-card-pro-static p-4 text-center text-sm font-medium text-slate-800 dark:text-slate-200 hover:ring-2 hover:ring-slate-400/30 transition-shadow">Rapports enregistrés</a>
        <a href="{{ route('inventories.create') }}" class="adventiste-card-pro-static p-4 text-center text-sm font-medium text-slate-800 dark:text-slate-200 hover:ring-2 hover:ring-slate-400/30 transition-shadow">+ Inventaire</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-8">
        <div class="adventiste-card-pro-static p-4 sm:p-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-base font-semibold text-slate-900 dark:text-white">Dernières recettes</h2>
                <a href="{{ route('revenues.index') }}" class="text-xs text-emerald-700 dark:text-emerald-400 hover:underline">Tout voir</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-600 dark:text-slate-300 border-b border-slate-200 dark:border-slate-600">
                            <th class="py-2 pr-2">Date</th>
                            <th class="py-2 pr-2">Catégorie</th>
                            <th class="py-2 text-right">Montant</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data['recent_revenues'] as $rev)
                            <tr class="border-b border-slate-100 dark:border-slate-700/80">
                                <td class="py-2 pr-2">{{ optional($rev->date_recette)->format('d/m/Y') }}</td>
                                <td class="py-2 pr-2">{{ $rev->category?->nom ?? '—' }}</td>
                                <td class="py-2 text-right font-medium">{{ $fcfa((float) $rev->montant) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="py-4 text-slate-500">Aucune recette.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="adventiste-card-pro-static p-4 sm:p-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-base font-semibold text-slate-900 dark:text-white">Dernières dépenses</h2>
                <a href="{{ route('expenses.index') }}" class="text-xs text-emerald-700 dark:text-emerald-400 hover:underline">Tout voir</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-600 dark:text-slate-300 border-b border-slate-200 dark:border-slate-600">
                            <th class="py-2 pr-2">Date</th>
                            <th class="py-2 pr-2">Libellé</th>
                            <th class="py-2 text-right">Montant</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data['recent_expenses'] as $ex)
                            <tr class="border-b border-slate-100 dark:border-slate-700/80">
                                <td class="py-2 pr-2">{{ optional($ex->date_depense)->format('d/m/Y') }}</td>
                                <td class="py-2 pr-2">
                                    <span class="line-clamp-2">{{ $ex->libelle ?: ($expenseCats[$ex->categorie_charge] ?? $ex->categorie_charge) }}</span>
                                </td>
                                <td class="py-2 text-right font-medium">{{ $fcfa((float) $ex->montant) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="py-4 text-slate-500">Aucune dépense.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script type="application/json" id="dashboard-chart-data">{!! json_encode($data['chart']) !!}</script>
@endsection

@push('scripts')
    @vite(['resources/js/dashboard-charts.js'])
@endpush
