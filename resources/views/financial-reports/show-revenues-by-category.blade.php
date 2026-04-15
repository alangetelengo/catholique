@extends('layouts.app')

@php
    $detailsCat = (array) ($financialReport->details_recettes ?? []);
    $filteredCategoryId = $detailsCat['revenue_category_id'] ?? null;
    $filteredCategoryNom = $filteredCategoryId
        ? (\App\Models\RevenueCategory::query()->whereKey($filteredCategoryId)->value('nom'))
        : null;
    $filteredTypeId = $detailsCat['revenue_type_id'] ?? null;
    $filteredTypeNom = $filteredTypeId
        ? (\App\Models\RevenueType::query()->whereKey($filteredTypeId)->value('nom'))
        : null;
@endphp

@section('title', 'Rapport par catégories de recettes — Catholique')
@section('page-title', 'Rapport par catégories de recettes')

@section('page-title-info')
    <span class="font-medium text-slate-800 dark:text-slate-100">{{ $financialReport->paroisse->nom ?? '—' }}</span>
    <span class="text-slate-500 dark:text-slate-400"> — </span>
    <span>Période du {{ $financialReport->date_debut->format('d/m/Y') }} au {{ $financialReport->date_fin->format('d/m/Y') }}</span>
    @if ($filteredCategoryNom)
        <span class="block mt-1 text-slate-600 dark:text-slate-400">Filtre catégorie : <strong class="font-semibold text-slate-800 dark:text-slate-200">{{ $filteredCategoryNom }}</strong></span>
    @endif
    @if ($filteredTypeNom)
        <span class="block mt-1 text-slate-600 dark:text-slate-400">Filtre type : <strong class="font-semibold text-slate-800 dark:text-slate-200">{{ $filteredTypeNom }}</strong></span>
    @endif
    <span class="block mt-1 text-slate-600 dark:text-slate-400">Enregistré le {{ $financialReport->created_at->format('d/m/Y à H:i') }}@if ($financialReport->createdBy) par {{ $financialReport->createdBy->name }}@endif</span>
@endsection

@section('content-container-class', 'w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8')

@section('header-back')
    <x-back-link :href="route('financial-reports.list')" label="Retour à la liste" />
@endsection

@section('btn-create')
    <div class="flex flex-wrap items-center gap-2 print:hidden">
        <a href="{{ route('financial-reports.index') }}" class="adventiste-btn-secondary text-sm no-underline">
            <i class="fas fa-chart-pie me-1.5" aria-hidden="true"></i>Hub rapports
        </a>
        <a href="{{ route('financial-reports.revenues-by-category') }}" class="adventiste-btn-secondary text-sm no-underline">Nouveau calcul</a>
        <a href="{{ route('financial-reports.download-pdf', $financialReport) }}" class="inline-flex items-center gap-2 rounded-lg border border-rose-200 dark:border-rose-800/60 bg-rose-50 dark:bg-rose-950/40 px-3 py-2 text-sm font-semibold text-rose-800 dark:text-rose-200 hover:bg-rose-100 dark:hover:bg-rose-900/50 transition-colors no-underline" target="_blank" rel="noopener noreferrer">
            <i class="fas fa-download" aria-hidden="true"></i>Télécharger PDF
        </a>
        <button type="button" onclick="window.print()" class="adventiste-btn-primary text-sm">
            <i class="fas fa-print me-1.5" aria-hidden="true"></i>Imprimer
        </button>
    </div>
@endsection

@section('content')
    @php
        $fmt = static fn ($n) => \App\Helpers\ParoisseConfig::formatMontant($n);
        $payLabel = static fn ($v) => ucfirst(str_replace('_', ' ', (string) $v));
    @endphp

    <div class="rounded-xl border border-sky-200/90 dark:border-sky-800/50 bg-sky-50/90 dark:bg-sky-950/25 px-4 py-3 mb-6 text-sm text-sky-950 dark:text-sky-100 leading-relaxed print:hidden">
        <p class="m-0 flex gap-2">
            <i class="fas fa-info-circle mt-0.5 shrink-0 text-sky-600 dark:text-sky-400" aria-hidden="true"></i>
            <span>Vue <strong class="font-semibold">figée</strong> du rapport enregistré : répartition par catégorie et liste des recettes validées sur la période.</span>
        </p>
    </div>

    <div id="report-content">
        {{-- Visible uniquement à l’impression (évite le doublon visuel avec l’en-tête de page) --}}
        <div class="mb-6 text-center print-header hidden print:block">
            <h3 class="text-lg font-bold text-slate-900">{{ $financialReport->paroisse->nom ?? 'Paroisse' }}</h3>
            <p class="text-base font-semibold text-slate-800 mt-1">Rapport par catégories de recettes</p>
            <p class="text-sm text-slate-600 mt-2">
                Période : {{ $financialReport->date_debut->format('d/m/Y') }} au {{ $financialReport->date_fin->format('d/m/Y') }}
            </p>
            @if ($filteredCategoryNom)
                <p class="text-sm text-slate-600">Catégorie : {{ $filteredCategoryNom }}</p>
            @endif
            @if ($filteredTypeNom)
                <p class="text-sm text-slate-600">Type : {{ $filteredTypeNom }}</p>
            @endif
            <p class="text-sm text-slate-600">
                Généré le {{ $financialReport->created_at->format('d/m/Y à H:i') }}
                @if ($financialReport->createdBy)
                    par {{ $financialReport->createdBy->name }}
                @endif
            </p>
            <hr class="mt-4 border-slate-200">
        </div>

        <div class="grid grid-cols-1 gap-4 mb-6 @if ($filteredCategoryId) md:grid-cols-2 @else md:grid-cols-3 @endif">
            <div class="adventiste-card-pro-static p-4 sm:p-5 border-t-4 border-t-emerald-500">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400 flex items-center gap-2">
                    <i class="fas fa-coins text-emerald-600 dark:text-emerald-400" aria-hidden="true"></i>
                    Total recettes
                </p>
                <p class="mt-1 text-xl font-bold text-emerald-700 dark:text-emerald-400 tabular-nums">{{ $fmt($report['total_general']) }}</p>
            </div>
            <div class="adventiste-card-pro-static p-4 sm:p-5 border-t-4 border-t-slate-500">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400 flex items-center gap-2">
                    <i class="fas fa-list-ol text-slate-500 dark:text-slate-400" aria-hidden="true"></i>
                    Nombre de recettes
                </p>
                <p class="mt-1 text-xl font-bold text-slate-900 dark:text-white tabular-nums">{{ $report['revenues']->count() }}</p>
            </div>
            @if (! $filteredCategoryId)
                <div class="adventiste-card-pro-static p-4 sm:p-5 border-t-4 border-t-violet-500">
                    <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400 flex items-center gap-2">
                        <i class="fas fa-folder text-violet-600 dark:text-violet-400" aria-hidden="true"></i>
                        Catégories
                    </p>
                    <p class="mt-1 text-xl font-bold text-violet-800 dark:text-violet-300 tabular-nums">{{ count($report['by_category']) }}</p>
                </div>
            @endif
        </div>

        @if (! $filteredCategoryId && count($report['by_category']) > 0)
            <div class="adventiste-card-pro-static overflow-hidden mb-6">
                <div class="px-4 py-3 border-b border-slate-200/80 dark:border-slate-600/80 bg-slate-50/80 dark:bg-slate-800/50">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white m-0 flex items-center gap-2">
                        <i class="fas fa-chart-pie text-emerald-600 dark:text-emerald-400" aria-hidden="true"></i>
                        Répartition par catégorie
                    </h3>
                </div>
                <div class="adventiste-table-shell border-0 rounded-none shadow-none">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left text-slate-700 dark:text-slate-200">
                                    <th class="px-4 py-3 font-semibold">Catégorie</th>
                                    <th class="px-4 py-3 font-semibold text-center">Nb</th>
                                    <th class="px-4 py-3 font-semibold text-right">Montant</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                                @foreach ($report['by_category'] as $cat)
                                    <tr class="text-slate-700 dark:text-slate-200">
                                        <td class="px-4 py-3">{{ $cat['nom'] }}</td>
                                        <td class="px-4 py-3 text-center tabular-nums">{{ $cat['count'] }}</td>
                                        <td class="px-4 py-3 text-right font-medium text-emerald-700 dark:text-emerald-400 tabular-nums">{{ $fmt($cat['montant']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-emerald-50/90 dark:bg-emerald-950/30 font-bold text-slate-900 dark:text-slate-100">
                                    <td class="px-4 py-3">Total</td>
                                    <td class="px-4 py-3 text-center tabular-nums">{{ $report['revenues']->count() }}</td>
                                    <td class="px-4 py-3 text-right tabular-nums">{{ $fmt($report['total_general']) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        @if ($report['revenues']->count() > 0)
            <div class="adventiste-card-pro-static overflow-hidden mb-6">
                <div class="px-4 py-3 border-b border-slate-200/80 dark:border-slate-600/80 bg-slate-50/80 dark:bg-slate-800/50">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white m-0 flex items-center gap-2">
                        <i class="fas fa-list text-slate-500 dark:text-slate-400" aria-hidden="true"></i>
                        Liste des recettes
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 m-0 mt-1">{{ $report['revenues']->count() }} ligne(s)</p>
                </div>
                <div class="adventiste-table-shell border-0 rounded-none shadow-none">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left text-slate-700 dark:text-slate-200">
                                    <th class="px-4 py-3 font-semibold">Date</th>
                                    <th class="px-4 py-3 font-semibold">Catégorie</th>
                                    <th class="px-4 py-3 font-semibold">Type</th>
                                    <th class="px-4 py-3 font-semibold">Méthode</th>
                                    <th class="px-4 py-3 font-semibold text-right">Montant</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                                @foreach ($report['revenues'] as $r)
                                    <tr class="text-slate-700 dark:text-slate-200">
                                        <td class="px-4 py-3 whitespace-nowrap">{{ $r->date_recette?->format('d/m/Y') }}</td>
                                        <td class="px-4 py-3">{{ $r->category?->nom ?? '—' }}</td>
                                        <td class="px-4 py-3">{{ $r->type?->nom ?? '—' }}</td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex rounded-full bg-slate-100 dark:bg-slate-700 px-2.5 py-0.5 text-xs font-medium text-slate-700 dark:text-slate-200">{{ $payLabel($r->methode_paiement ?? '') ?: '—' }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-right font-semibold text-emerald-700 dark:text-emerald-400 tabular-nums">{{ $fmt($r->montant) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-slate-50/90 dark:bg-slate-800/50 font-bold text-slate-900 dark:text-slate-100">
                                    <td class="px-4 py-3 text-right" colspan="4">Total</td>
                                    <td class="px-4 py-3 text-right tabular-nums">{{ $fmt($report['total_general']) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <div class="adventiste-card-pro-static p-4 sm:p-5 border border-slate-200/80 dark:border-slate-600/60 bg-slate-50/50 dark:bg-slate-900/30 print-footer text-sm text-slate-600 dark:text-slate-400">
            <p class="text-xs text-slate-500 dark:text-slate-500 m-0 text-center">Document consulté / généré le {{ now()->format('d/m/Y à H:i') }}</p>
        </div>
    </div>

    @push('styles')
        <style>
            @media print {
                @page { margin: 1.5cm; }
                body { background: #fff !important; }
                #navHeader,
                #mainHeader,
                aside.sidebar,
                #preloader,
                .footer.theme-footer-bar,
                #flashAlertModal,
                dialog {
                    display: none !important;
                }
                #mainContent > header {
                    display: none !important;
                }
                .ged-flash { display: none !important; }
                .main-content,
                #main-wrapper.menu-toggle .main-content {
                    margin-left: 0 !important;
                    margin-top: 0 !important;
                }
                #mainContent main {
                    padding-top: 0 !important;
                    padding-bottom: 0 !important;
                }
                #report-content {
                    position: static !important;
                    width: 100% !important;
                    max-width: none !important;
                }
                .print\:hidden {
                    display: none !important;
                }
                html.dark #report-content {
                    color: #0f172a !important;
                }
                html.dark #report-content .text-white,
                html.dark #report-content [class*='dark:text-white'] {
                    color: #0f172a !important;
                }
                .adventiste-card-pro-static,
                .adventiste-table-shell {
                    box-shadow: none !important;
                    border: 1px solid #e2e8f0 !important;
                    break-inside: avoid;
                }
                table { font-size: 11px; }
            }
        </style>
    @endpush
@endsection
