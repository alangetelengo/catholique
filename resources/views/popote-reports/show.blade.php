@extends('layouts.app')

@section('title', 'Détail rapport Subvention Popote - Catholique')
@section('page-title', 'Détail rapport Subvention Popote')
@section('page-title-info', 'Subvention reçue comparée aux dépenses alimentation.')

@section('btn-create')
    <a target="_blank" href="{{ route('popote-reports.print', $report) }}" class="adventiste-btn-secondary">Imprimer</a>
    <a href="{{ route('popote-reports.pdf', $report) }}" class="adventiste-btn-secondary">Exporter PDF</a>
@endsection

@section('content')
    @php
        $fcfa = static fn (float $v): string => number_format($v, 0, ',', ' ') . ' fcfa';
        $period = ($detailsRecettes['period_kind'] ?? 'mensuel') === 'annuel'
            ? 'Année ' . ($detailsRecettes['year'] ?? optional($report->date_debut)->format('Y'))
            : sprintf('%02d/%s', (int) ($detailsRecettes['month'] ?? optional($report->date_debut)->format('m')), $detailsRecettes['year'] ?? optional($report->date_debut)->format('Y'));
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-5">
        <div class="adventiste-card-pro-static p-4"><p class="text-xs uppercase text-slate-500">Période</p><p class="mt-1 text-xl font-bold">{{ $period }}</p></div>
        <div class="adventiste-card-pro-static p-4"><p class="text-xs uppercase text-slate-500">Subvention reçue</p><p class="mt-1 text-xl font-bold text-emerald-700">{{ $fcfa((float) $report->total_recettes) }}</p></div>
        <div class="adventiste-card-pro-static p-4"><p class="text-xs uppercase text-slate-500">Dépenses alimentation</p><p class="mt-1 text-xl font-bold text-rose-700">{{ $fcfa((float) $report->total_depenses) }}</p></div>
        <div class="adventiste-card-pro-static p-4"><p class="text-xs uppercase text-slate-500">Solde popote</p><p class="mt-1 text-xl font-bold">{{ $fcfa((float) $report->solde) }}</p></div>
    </div>

    <div class="adventiste-table-shell mb-5">
        <table class="min-w-full text-sm">
            <thead><tr><th class="px-4 py-3 font-semibold">Date recette</th><th class="px-4 py-3 font-semibold">Référence</th><th class="px-4 py-3 font-semibold">Montant subvention</th></tr></thead>
            <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                @forelse($rowsRecettes as $row)
                    <tr><td class="px-4 py-3">{{ \Illuminate\Support\Carbon::parse($row['date'])->format('d/m/Y') }}</td><td class="px-4 py-3">{{ $row['reference'] ?? '-' }}</td><td class="px-4 py-3 font-semibold">{{ $fcfa((float) ($row['montant'] ?? 0)) }}</td></tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-8 text-center text-slate-500">Aucune subvention reçue.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="adventiste-table-shell">
        <table class="min-w-full text-sm">
            <thead><tr><th class="px-4 py-3 font-semibold">Date dépense</th><th class="px-4 py-3 font-semibold">Libellé</th><th class="px-4 py-3 font-semibold">Fournisseur</th><th class="px-4 py-3 font-semibold">Référence</th><th class="px-4 py-3 font-semibold">Montant dépense</th></tr></thead>
            <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                @forelse($rowsDepenses as $row)
                    <tr>
                        <td class="px-4 py-3">{{ \Illuminate\Support\Carbon::parse($row['date'])->format('d/m/Y') }}</td>
                        <td class="px-4 py-3">{{ $row['libelle'] ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $row['fournisseur'] ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $row['reference'] ?? '-' }}</td>
                        <td class="px-4 py-3 font-semibold">{{ $fcfa((float) ($row['montant'] ?? 0)) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">Aucune dépense alimentation.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

