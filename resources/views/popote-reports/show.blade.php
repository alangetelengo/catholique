@extends('layouts.app')

@section('title', 'Détail rapport Caisse Popote - Catholique')
@section('page-title', 'Détail rapport Caisse Popote')
@section('page-title-info', 'Crédits de la caisse Popote comparés aux dépenses alimentation.')

@section('header-back')
    <x-back-link :href="route('popote-reports.index')" />
@endsection

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
        <div class="adventiste-card-pro-static p-4"><p class="text-xs uppercase text-slate-500">Crédits caisse</p><p class="mt-1 text-xl font-bold text-emerald-700">{{ $fcfa((float) $report->total_recettes) }}</p></div>
        <div class="adventiste-card-pro-static p-4"><p class="text-xs uppercase text-slate-500">Dépenses alimentation</p><p class="mt-1 text-xl font-bold text-rose-700">{{ $fcfa((float) $report->total_depenses) }}</p></div>
        <div class="adventiste-card-pro-static p-4"><p class="text-xs uppercase text-slate-500">Solde popote</p><p class="mt-1 text-xl font-bold">{{ $fcfa((float) $report->solde) }}</p></div>
    </div>

    @php $monthlySummary = collect($detailsRecettes['monthly_summary'] ?? []); @endphp
    @if($monthlySummary->isNotEmpty())
        <div class="adventiste-card-pro-static p-4 sm:p-5 mb-5">
            <h2 class="text-sm font-semibold text-slate-900 dark:text-white mb-3">Synthèse par mois</h2>
            <div class="adventiste-table-shell">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 font-semibold text-left">Mois concerné</th>
                            <th class="px-4 py-3 font-semibold text-left">Crédits caisse</th>
                            <th class="px-4 py-3 font-semibold text-left">Dépenses</th>
                            <th class="px-4 py-3 font-semibold text-left">Solde restant</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                        @foreach($monthlySummary as $row)
                            <tr>
                                <td class="px-4 py-3 font-medium">{{ $row['mois_label'] ?? '—' }}</td>
                                <td class="px-4 py-3 text-emerald-700 font-semibold">{{ $fcfa((float) ($row['subvention_recue'] ?? 0)) }}</td>
                                <td class="px-4 py-3 text-rose-700 font-semibold">{{ $fcfa((float) ($row['depenses'] ?? 0)) }}</td>
                                <td class="px-4 py-3 font-semibold">{{ $fcfa((float) ($row['solde'] ?? 0)) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="adventiste-table-shell mb-5">
        <table class="min-w-full text-sm">
            <thead><tr><th class="px-4 py-3 font-semibold">Mois concerné</th><th class="px-4 py-3 font-semibold">Date recette</th><th class="px-4 py-3 font-semibold">Référence</th><th class="px-4 py-3 font-semibold">Montant subvention</th></tr></thead>
            <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                @forelse($rowsRecettes as $row)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $row['mois_label'] ?? '—' }}</td>
                        <td class="px-4 py-3">{{ \Illuminate\Support\Carbon::parse($row['date'])->format('d/m/Y') }}</td>
                        <td class="px-4 py-3">{{ $row['reference'] ?? '-' }}</td>
                        <td class="px-4 py-3 font-semibold">{{ $fcfa((float) ($row['montant'] ?? 0)) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">Aucune subvention reçue.</td></tr>
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

