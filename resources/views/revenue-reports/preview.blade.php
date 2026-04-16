@extends('layouts.app')

@section('title', 'Aperçu rapport recettes - Catholique')
@section('page-title', 'Aperçu du rapport de recettes')
@section('page-title-info', 'Rapport généré sans enregistrement.')

@section('btn-create')
    <a href="{{ route('revenue-reports.create') }}" class="adventiste-btn-secondary">Nouveau paramétrage</a>
@endsection

@section('content')
    @php
        $formatFcfa = static fn (float $v): string => number_format($v, 0, ',', ' ') . ' fcfa';
        $details = (array) ($reportData['details_recettes'] ?? []);
        $rows = collect($details['revenues'] ?? []);
        $byType = collect($details['by_type'] ?? []);
        $periodLabel = ($details['period_kind'] ?? 'mensuel') === 'annuel'
            ? 'Année ' . ($details['year'] ?? optional($reportData['date_debut'] ?? null)->format('Y'))
            : sprintf('%02d/%s', (int) ($details['month'] ?? optional($reportData['date_debut'] ?? null)->format('m')), $details['year'] ?? optional($reportData['date_debut'] ?? null)->format('Y'));
        $targetLabel = ($details['report_target'] ?? 'global') === 'categorie' ? 'Par catégorie' : 'Global';
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
        <div class="adventiste-card-pro-static p-4">
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Type</p>
            <p class="mt-1 text-xl font-bold text-slate-900 dark:text-slate-100">{{ $targetLabel }}</p>
        </div>
        <div class="adventiste-card-pro-static p-4">
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Période</p>
            <p class="mt-1 text-xl font-bold text-slate-900 dark:text-slate-100">{{ $periodLabel }}</p>
        </div>
        <div class="adventiste-card-pro-static p-4">
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Total recettes</p>
            <p class="mt-1 text-xl font-bold text-emerald-700 dark:text-emerald-400">{{ $formatFcfa((float) ($reportData['total_recettes'] ?? 0)) }}</p>
        </div>
    </div>

    <div class="adventiste-card-pro-static p-4 sm:p-5 mb-5">
        <p class="text-sm text-slate-600 dark:text-slate-300">
            Paroisse: <span class="font-semibold">{{ $paroisse?->nom ?? '-' }}</span>
            @if (!empty($details['revenue_category_nom']))
                | Catégorie: <span class="font-semibold">{{ $details['revenue_category_nom'] }}</span>
            @endif
            | Statut: <span class="font-semibold text-amber-600">Non enregistré</span>
        </p>
    </div>

    <div class="adventiste-table-shell mb-5">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-700 dark:text-slate-200">
                        <th class="px-4 py-3 font-semibold">Type recette</th>
                        <th class="px-4 py-3 font-semibold">Occurrences</th>
                        <th class="px-4 py-3 font-semibold">Montant</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                    @forelse ($byType as $item)
                        <tr class="text-slate-700 dark:text-slate-200">
                            <td class="px-4 py-3">{{ $item['type_nom'] ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $item['count'] ?? 0 }}</td>
                            <td class="px-4 py-3 font-semibold">{{ $formatFcfa((float) ($item['montant'] ?? 0)) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-4 py-8 text-center text-slate-500">Aucune donnée par type.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="adventiste-table-shell">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-700 dark:text-slate-200">
                        <th class="px-4 py-3 font-semibold">Date</th>
                        <th class="px-4 py-3 font-semibold">Catégorie</th>
                        <th class="px-4 py-3 font-semibold">Type</th>
                        <th class="px-4 py-3 font-semibold">Montant</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                    @forelse ($rows as $row)
                        <tr class="text-slate-700 dark:text-slate-200">
                            <td class="px-4 py-3">{{ \Illuminate\Support\Carbon::parse($row['date'])->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">{{ $row['category'] ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $row['type'] ?? '-' }}</td>
                            <td class="px-4 py-3 font-semibold">{{ $formatFcfa((float) ($row['montant'] ?? 0)) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">Aucune recette sur la période.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

