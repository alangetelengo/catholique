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
        <a href="{{ route('financial-reports.list') }}" class="adventiste-btn-secondary text-sm no-underline">
            <i class="fas fa-folder-open me-1.5" aria-hidden="true"></i>Historique
        </a>
        <a href="{{ route('financial-reports.revenues-by-category') }}" class="adventiste-btn-secondary text-sm no-underline">Nouveau calcul</a>
        <a href="{{ route('financial-reports.print', $financialReport) }}" class="adventiste-btn-primary text-sm no-underline inline-flex items-center">
            <i class="fas fa-print me-1.5" aria-hidden="true"></i>Imprimer
        </a>
    </div>
@endsection

@section('content')
    <div class="rounded-xl border border-sky-200/90 dark:border-sky-800/50 bg-sky-50/90 dark:bg-sky-950/25 px-4 py-3 mb-6 text-sm text-sky-950 dark:text-sky-100 leading-relaxed print:hidden">
        <p class="m-0 flex gap-2">
            <i class="fas fa-info-circle mt-0.5 shrink-0 text-sky-600 dark:text-sky-400" aria-hidden="true"></i>
            <span>Vue <strong class="font-semibold">figée</strong> du rapport enregistré : synthèse et liste des recettes validées sur la période.</span>
        </p>
    </div>

    <div id="report-content">
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
            <hr class="mt-4 border-slate-200">
        </div>

        @include('financial-reports.partials.revenues-by-category-report-body', [
            'report' => $report,
            'dateDebut' => $financialReport->date_debut->format('Y-m-d'),
            'dateFin' => $financialReport->date_fin->format('Y-m-d'),
            'selectedCategoryId' => $selectedCategoryId ?? $filteredCategoryId,
            'selectedTypeId' => $selectedTypeId ?? $filteredTypeId,
            'selectedParoisseId' => $financialReport->paroisse_id,
            'hideStoreForm' => true,
            'showWeeklyBreakdown' => $showWeeklyBreakdown ?? false,
            'showRptSemaine' => $showRptSemaine ?? true,
            'showRptDimanche' => $showRptDimanche ?? true,
            'rptTotalSubtitle' => $rptTotalSubtitle ?? 'Semaine + dimanche',
        ])

        <div class="adventiste-card-pro-static p-4 sm:p-5 border border-slate-200/80 dark:border-slate-600/60 bg-slate-50/50 dark:bg-slate-900/30 print-footer text-sm text-slate-600 dark:text-slate-400 mt-6">
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
                .adventiste-card-pro-static {
                    box-shadow: none !important;
                    border: 1px solid #e2e8f0 !important;
                    break-inside: avoid;
                }
            }
        </style>
    @endpush
@endsection
