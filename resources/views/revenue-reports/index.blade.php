@extends('layouts.app')

@section('title', 'Rapports recettes - Catholique')
@section('page-title', 'Rapports de recettes')
@section('page-title-info', 'Historique des rapports globaux et par catégorie.')

@section('btn-create')
    <a href="{{ route('revenue-reports.create') }}" class="adventiste-btn-primary">+ Nouveau rapport</a>
@endsection

@section('content')
    @php
        $formatFcfa = static fn (float $v): string => number_format($v, 0, ',', ' ') . ' fcfa';
    @endphp

    <div class="adventiste-card-pro-static p-4 sm:p-5 mb-5">
        <form method="get" class="grid grid-cols-1 md:grid-cols-4 gap-3">
            @if ($paroisses->isNotEmpty())
                <select name="paroisse_id" class="rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm">
                    <option value="">Toutes paroisses</option>
                    @foreach ($paroisses as $paroisse)
                        <option value="{{ $paroisse->id }}" {{ (int) request('paroisse_id') === (int) $paroisse->id ? 'selected' : '' }}>{{ $paroisse->nom }}</option>
                    @endforeach
                </select>
            @endif
            <select name="report_target" class="rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm">
                <option value="">Tous types</option>
                <option value="global" {{ request('report_target') === 'global' ? 'selected' : '' }}>Global</option>
                <option value="categorie" {{ request('report_target') === 'categorie' ? 'selected' : '' }}>Par catégorie</option>
            </select>
            <input type="number" name="year" min="2000" max="2100" value="{{ request('year') }}" placeholder="Année" class="rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm">
            <div class="flex items-center gap-2">
                <button class="adventiste-btn-primary" type="submit">Filtrer</button>
                <a href="{{ route('revenue-reports.index') }}" class="adventiste-btn-secondary">Réinitialiser</a>
            </div>
        </form>
    </div>

    <div class="adventiste-table-shell">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-700 dark:text-slate-200">
                        <th class="px-4 py-3 font-semibold">Période</th>
                        <th class="px-4 py-3 font-semibold">Type</th>
                        <th class="px-4 py-3 font-semibold">Paroisse</th>
                        <th class="px-4 py-3 font-semibold">Total recettes</th>
                        <th class="px-4 py-3 font-semibold">Créé par</th>
                        <th class="px-4 py-3 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                    @forelse ($reports as $report)
                        @php
                            $details = (array) ($report->details_recettes ?? []);
                            $target = ($details['report_target'] ?? 'global') === 'categorie' ? 'Par catégorie' : 'Global';
                            $periodLabel = ($details['period_kind'] ?? 'mensuel') === 'annuel'
                                ? 'Année ' . ($details['year'] ?? optional($report->date_debut)->format('Y'))
                                : sprintf('%02d/%s', (int) ($details['month'] ?? optional($report->date_debut)->format('m')), $details['year'] ?? optional($report->date_debut)->format('Y'));
                        @endphp
                        <tr class="text-slate-700 dark:text-slate-200">
                            <td class="px-4 py-3">{{ $periodLabel }}</td>
                            <td class="px-4 py-3">{{ $target }}</td>
                            <td class="px-4 py-3">{{ $report->paroisse?->nom ?? '-' }}</td>
                            <td class="px-4 py-3 font-semibold">{{ $formatFcfa((float) $report->total_recettes) }}</td>
                            <td class="px-4 py-3">{{ $report->createdBy?->name ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <x-action-button variant="view" href="{{ route('revenue-reports.show', $report) }}" />
                                    <x-action-button variant="edit" href="{{ route('revenue-reports.edit', $report) }}" />
                                    <x-action-button variant="delete" action="{{ route('revenue-reports.destroy', $report) }}" method="DELETE" confirm-message="Supprimer ce rapport ?" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">Aucun rapport trouvé.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $reports->links() }}</div>
@endsection

