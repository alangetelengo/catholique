@extends('layouts.app')

@section('title', 'Statistiques financières — Catholique')
@section('page-title', 'Statistiques financières')
@section('page-title-info', 'Vue annuelle : mêmes règles que le rapport mensuel — recettes hors Procure, dépenses toutes catégories (validées), solde par mois.')

@section('btn-create')
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('financial-reports.list') }}" class="adventiste-btn-secondary text-sm">Rapports enregistrés</a>
        <a href="{{ route('financial-reports.expenses') }}" class="adventiste-btn-primary text-sm">Rapport dépenses</a>
    </div>
@endsection

@section('content')
    @php
        $fmt = static fn ($n) => \App\Helpers\ParoisseConfig::formatMontant($n);
    @endphp

    <div class="adventiste-card-pro-static p-4 sm:p-5 mb-6">
        <h2 class="text-base font-semibold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
            <i class="fas fa-sliders-h text-emerald-600 dark:text-emerald-400" aria-hidden="true"></i>
            Période analysée
        </h2>
        <form method="GET" action="{{ route('financial-reports.statistics') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
            @if (auth()->user()->hasRole('super_admin') && $paroisses->count() > 0)
                <div class="md:col-span-2 lg:col-span-1">
                    <label for="stat_paroisse" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Paroisse <span class="text-red-500">*</span></label>
                    <select id="stat_paroisse" name="paroisse_id" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm" required>
                        <option value="">Sélectionner…</option>
                        @foreach ($paroisses as $paroisse)
                            <option value="{{ $paroisse->id }}" @selected($selectedParoisseId == $paroisse->id)>{{ $paroisse->nom }}</option>
                        @endforeach
                    </select>
                </div>
            @else
                <input type="hidden" name="paroisse_id" value="{{ auth()->user()->paroisse_id }}">
            @endif
            <div>
                <label for="stat_year" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Année <span class="text-red-500">*</span></label>
                <select id="stat_year" name="year" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm" required>
                    @for ($y = now()->year; $y >= now()->year - 10; $y--)
                        <option value="{{ $y }}" @selected($selectedYear == $y)>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <button type="submit" class="adventiste-btn-primary w-full md:w-auto">Actualiser</button>
            </div>
        </form>
    </div>

    @if ($stats)
        <div class="rounded-xl border border-sky-200/90 dark:border-sky-800/50 bg-sky-50/90 dark:bg-sky-950/25 px-4 py-3 mb-6 text-sm text-sky-950 dark:text-sky-100 leading-relaxed">
            <p class="m-0 flex gap-2">
                <i class="fas fa-info-circle mt-0.5 shrink-0 text-sky-600 dark:text-sky-400" aria-hidden="true"></i>
                <span>
                    <strong class="font-semibold">Règle (alignée sur le rapport mensuel) :</strong>
                    les <strong>recettes</strong> comptabilisées excluent la catégorie <strong>Procure</strong> et regroupent toutes les recettes validées (y compris Banque) (recettes au statut « validé » uniquement).
                    Les <strong>dépenses</strong> incluent toutes les catégories de charges (statut « validé »). Le <strong>solde</strong> est recettes − dépenses.
                </span>
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="adventiste-card-pro-static p-4 border-t-4 border-t-emerald-500">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Total recettes {{ $selectedYear }}</p>
                <p class="mt-1 text-xl font-bold text-emerald-700 dark:text-emerald-400">{{ $fmt($stats['total_recettes']) }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Hors Procure</p>
            </div>
            <div class="adventiste-card-pro-static p-4 border-t-4 border-t-rose-500">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Total dépenses {{ $selectedYear }}</p>
                <p class="mt-1 text-xl font-bold text-rose-700 dark:text-rose-400">{{ $fmt($stats['total_depenses']) }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Toutes catégories</p>
            </div>
            <div class="adventiste-card-pro-static p-4 border-t-4 {{ $stats['solde'] >= 0 ? 'border-t-sky-500' : 'border-t-amber-500' }}">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Solde {{ $selectedYear }}</p>
                <p class="mt-1 text-xl font-bold {{ $stats['solde'] >= 0 ? 'text-sky-700 dark:text-sky-300' : 'text-amber-700 dark:text-amber-400' }}">{{ $fmt($stats['solde']) }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $stats['solde'] >= 0 ? 'Excédent' : 'Déficit' }} (recettes − dépenses)</p>
            </div>
        </div>

        <div class="adventiste-card-pro-static overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-200/80 dark:border-slate-600/80 bg-slate-50/80 dark:bg-slate-800/50">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white m-0">Répartition par mois</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 m-0 mt-1">Solde = recettes (hors Procure) − dépenses (toutes catégories)</p>
            </div>
            <div class="adventiste-table-shell border-0 rounded-none shadow-none">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-slate-700 dark:text-slate-200">
                                <th class="px-4 py-3 font-semibold">Mois</th>
                                <th class="px-4 py-3 font-semibold text-right">Recettes</th>
                                <th class="px-4 py-3 font-semibold text-right">Dépenses</th>
                                <th class="px-4 py-3 font-semibold text-right">Solde</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                            @foreach ($byMonth ?? [] as $m => $row)
                                <tr class="text-slate-700 dark:text-slate-200">
                                    <td class="px-4 py-3 font-medium">{{ $row['nom'] }}</td>
                                    <td class="px-4 py-3 text-right">{{ $fmt($row['recettes']) }}</td>
                                    <td class="px-4 py-3 text-right">{{ $fmt($row['depenses']) }}</td>
                                    <td class="px-4 py-3 text-right font-semibold {{ $row['solde'] >= 0 ? 'text-sky-700 dark:text-sky-300' : 'text-amber-700 dark:text-amber-400' }}">
                                        {{ $fmt($row['solde']) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        @if (! empty($byMonth))
                            <tfoot>
                                <tr class="bg-slate-100/90 dark:bg-slate-800/80 font-bold text-slate-900 dark:text-white">
                                    <td class="px-4 py-3">Total {{ $selectedYear }}</td>
                                    <td class="px-4 py-3 text-right">{{ $fmt($stats['total_recettes']) }}</td>
                                    <td class="px-4 py-3 text-right">{{ $fmt($stats['total_depenses']) }}</td>
                                    <td class="px-4 py-3 text-right {{ $stats['solde'] >= 0 ? 'text-sky-700 dark:text-sky-300' : 'text-amber-700 dark:text-amber-400' }}">
                                        {{ $fmt($stats['solde']) }}
                                    </td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="adventiste-card-pro-static p-12 text-center">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400">
                <i class="fas fa-chart-line text-2xl" aria-hidden="true"></i>
            </div>
            <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Sélectionnez une paroisse et une année</h3>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400 max-w-lg mx-auto">
                @if (auth()->user()->hasRole('super_admin'))
                    Choisissez une paroisse dans la liste, une année, puis cliquez sur « Actualiser » pour afficher les totaux et le détail mensuel.
                @else
                    Choisissez une année et cliquez sur « Actualiser » pour afficher les statistiques de votre paroisse.
                @endif
            </p>
        </div>
    @endif
@endsection
