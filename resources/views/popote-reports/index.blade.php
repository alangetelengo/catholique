@extends('layouts.app')

@section('title', 'Rapports Subvention Popote - Catholique')
@section('page-title', 'Rapport Subvention Popote')
@section('page-title-info', 'Comparaison subvention reçue vs dépenses alimentation.')

@section('btn-create')
    <a href="{{ route('popote-reports.create') }}" class="adventiste-btn-primary">+ Nouveau rapport</a>
@endsection

@section('content')
    @php $fcfa = static fn (float $v): string => number_format($v, 0, ',', ' ') . ' fcfa'; @endphp
    <div class="adventiste-card-pro-static p-4 sm:p-5 mb-5">
        <form method="get" class="grid grid-cols-1 md:grid-cols-3 gap-3">
            @if($paroisses->isNotEmpty())
                <select name="paroisse_id" class="rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm">
                    <option value="">Toutes paroisses</option>
                    @foreach($paroisses as $p)
                        <option value="{{ $p->id }}" {{ (int) request('paroisse_id') === (int) $p->id ? 'selected' : '' }}>{{ $p->nom }}</option>
                    @endforeach
                </select>
            @endif
            <input type="number" min="2000" max="2100" name="year" value="{{ request('year') }}" placeholder="Année" class="rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm">
            <div class="flex items-center gap-2">
                <button class="adventiste-btn-primary" type="submit">Filtrer</button>
                <a href="{{ route('popote-reports.index') }}" class="adventiste-btn-secondary">Réinitialiser</a>
            </div>
        </form>
    </div>

    <div class="adventiste-table-shell">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead><tr><th class="px-4 py-3 font-semibold">Période</th><th class="px-4 py-3 font-semibold">Paroisse</th><th class="px-4 py-3 font-semibold">Subvention reçue</th><th class="px-4 py-3 font-semibold">Dépenses alimentation</th><th class="px-4 py-3 font-semibold">Solde</th><th class="px-4 py-3 font-semibold text-right">Actions</th></tr></thead>
                <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                    @forelse($reports as $report)
                        @php
                            $details = (array) ($report->details_recettes ?? []);
                            $period = ($details['period_kind'] ?? 'mensuel') === 'annuel'
                                ? 'Année ' . ($details['year'] ?? optional($report->date_debut)->format('Y'))
                                : sprintf('%02d/%s', (int) ($details['month'] ?? optional($report->date_debut)->format('m')), $details['year'] ?? optional($report->date_debut)->format('Y'));
                        @endphp
                        <tr>
                            <td class="px-4 py-3">{{ $period }}</td>
                            <td class="px-4 py-3">{{ $report->paroisse?->nom ?? '-' }}</td>
                            <td class="px-4 py-3 font-semibold">{{ $fcfa((float) $report->total_recettes) }}</td>
                            <td class="px-4 py-3 font-semibold">{{ $fcfa((float) $report->total_depenses) }}</td>
                            <td class="px-4 py-3 font-semibold">{{ $fcfa((float) $report->solde) }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <x-action-button variant="view" href="{{ route('popote-reports.show', $report) }}" />
                                    <x-action-button variant="edit" href="{{ route('popote-reports.edit', $report) }}" />
                                    <x-action-button variant="delete" action="{{ route('popote-reports.destroy', $report) }}" method="DELETE" confirm-message="Supprimer ce rapport Subvention Popote ?" />
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
    @include('partials.pagination-fr', ['paginator' => $reports, 'itemLabel' => 'rapports'])
@endsection

