@extends('layouts.app')

@section('title', 'Détail rapport charges fixes - Catholique')
@section('page-title', 'Détail rapport charges fixes')
@section('page-title-info', 'Rapport hiérarchique des charges fixes (sans déduction des recettes).')

@section('header-back')
    <x-back-link :href="route('charges-fixes-reports.index')" />
@endsection

@section('btn-create')
    <a target="_blank" href="{{ route('charges-fixes-reports.print', $report) }}" class="adventiste-btn-secondary">Imprimer</a>
    <a href="{{ route('charges-fixes-reports.pdf', $report) }}" class="adventiste-btn-secondary">Exporter PDF</a>
@endsection

@section('content')
    @php
        $fcfa = static fn (float $v): string => number_format($v, 0, ',', ' ') . ' fcfa';
        $period = ($details['period_kind'] ?? 'mensuel') === 'annuel'
            ? 'Année ' . ($details['year'] ?? optional($report->date_debut)->format('Y'))
            : sprintf('%02d/%s', (int) ($details['month'] ?? optional($report->date_debut)->format('m')), $details['year'] ?? optional($report->date_debut)->format('Y'));
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
        <div class="adventiste-card-pro-static p-4"><p class="text-xs uppercase text-slate-500">Période</p><p class="mt-1 text-xl font-bold">{{ $period }}</p></div>
        <div class="adventiste-card-pro-static p-4"><p class="text-xs uppercase text-slate-500">Paroisse</p><p class="mt-1 text-xl font-bold">{{ $report->paroisse?->nom ?? '-' }}</p></div>
        <div class="adventiste-card-pro-static p-4"><p class="text-xs uppercase text-slate-500">Total charges fixes</p><p class="mt-1 text-xl font-bold text-rose-700 dark:text-rose-400">{{ $fcfa((float) $report->total_depenses) }}</p></div>
    </div>

    <div class="adventiste-table-shell mb-5">
        <table class="min-w-full text-sm">
            <thead><tr><th class="px-4 py-3 font-semibold">Type</th><th class="px-4 py-3 font-semibold">Occurrences</th><th class="px-4 py-3 font-semibold">Montant</th></tr></thead>
            <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                @forelse($byType as $item)
                    <tr><td class="px-4 py-3">{{ str_replace('_', ' ', ucfirst($item['type'] ?? '-')) }}</td><td class="px-4 py-3">{{ $item['count'] ?? 0 }}</td><td class="px-4 py-3 font-semibold">{{ $fcfa((float) ($item['montant'] ?? 0)) }}</td></tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-8 text-center text-slate-500">Aucune donnée.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="adventiste-table-shell">
        <table class="min-w-full text-sm">
            <thead><tr><th class="px-4 py-3 font-semibold">Date</th><th class="px-4 py-3 font-semibold">Type</th><th class="px-4 py-3 font-semibold">Fournisseur</th><th class="px-4 py-3 font-semibold">Référence</th><th class="px-4 py-3 font-semibold">Montant</th></tr></thead>
            <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                @forelse($rows as $row)
                    <tr>
                        <td class="px-4 py-3">{{ \Illuminate\Support\Carbon::parse($row['date'])->format('d/m/Y') }}</td>
                        <td class="px-4 py-3">{{ str_replace('_', ' ', ucfirst($row['type'] ?? '-')) }}</td>
                        <td class="px-4 py-3">{{ $row['fournisseur'] ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $row['reference'] ?? '-' }}</td>
                        <td class="px-4 py-3 font-semibold">{{ $fcfa((float) ($row['montant'] ?? 0)) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">Aucune charge fixe sur la période.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

