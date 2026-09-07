@extends('layouts.app')

@section('title', 'Rapport financier — Catholique')
@section('page-title', 'Rapport mensuel de justification')

@section('page-title-info')
    <span class="font-medium text-slate-800 dark:text-slate-100">{{ $financialReport->paroisse->nom ?? '—' }}</span>
    <span class="text-slate-500 dark:text-slate-400"> — </span>
    <span>{{ $financialReport->date_debut->copy()->locale(app()->getLocale())->translatedFormat('F Y') }}</span>
    <span class="block mt-1 text-slate-600 dark:text-slate-400">Période du {{ $financialReport->date_debut->format('d/m/Y') }} au {{ $financialReport->date_fin->format('d/m/Y') }} · Généré le {{ $financialReport->created_at->format('d/m/Y à H:i') }}@if ($financialReport->createdBy) par {{ $financialReport->createdBy->name }}@endif</span>
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
        <a href="{{ route('financial-reports.print', $financialReport) }}" class="adventiste-btn-primary text-sm no-underline inline-flex items-center">
            <i class="fas fa-print me-1.5" aria-hidden="true"></i>Imprimer
        </a>
    </div>
@endsection

@section('content')
    @php
        $fmt = static fn ($n) => \App\Helpers\ParoisseConfig::formatMontant($n);
        $payLabel = static fn ($v) => ucfirst(str_replace('_', ' ', (string) $v));
    @endphp

    <div class="rounded-xl border border-emerald-200/90 dark:border-emerald-900/40 bg-emerald-50/90 dark:bg-emerald-950/20 px-4 py-3 mb-6 text-sm text-emerald-950 dark:text-emerald-100 leading-relaxed print:hidden">
        <p class="m-0 flex gap-2">
            <i class="fas fa-file-invoice-dollar mt-0.5 shrink-0 text-emerald-600 dark:text-emerald-400" aria-hidden="true"></i>
            <span>Rapport <strong class="font-semibold">figé</strong> à la date d’enregistrement : recettes hors Procure (quêtes, locations, fête, Banque), dépenses toutes catégories et <strong class="font-semibold">solde</strong> pour la période affichée. Utilisez « Imprimer » pour l’aperçu PDF et le téléchargement.</span>
        </p>
    </div>

    <div id="report-content">
        {{-- En-tête pour impression --}}
        <div class="mb-6 text-center print-header hidden print:block">
            <h3 class="text-lg font-bold text-slate-900">{{ $financialReport->paroisse->nom ?? 'Paroisse' }}</h3>
            <p class="text-base font-semibold text-slate-800 mt-1">Rapport financier mensuel</p>
            <p class="text-sm text-slate-600 mt-2">
                Période : {{ $financialReport->date_debut->format('d/m/Y') }} au {{ $financialReport->date_fin->format('d/m/Y') }}
            </p>
            <p class="text-sm text-slate-600">
                Généré le : {{ $financialReport->created_at->format('d/m/Y à H:i') }}
                @if ($financialReport->createdBy)
                    par {{ $financialReport->createdBy->name }}
                @endif
            </p>
            <hr class="mt-4 border-slate-200">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="adventiste-card-pro-static p-4 sm:p-5 border-t-4 border-t-emerald-500">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400 flex items-center gap-2">
                    <i class="fas fa-coins text-emerald-600 dark:text-emerald-400" aria-hidden="true"></i>
                    Total recettes
                </p>
                <p class="mt-1 text-xl font-bold text-emerald-700 dark:text-emerald-400 tabular-nums">{{ $fmt($report['total_recettes']) }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Banque, quêtes, location, fête (hors Procure)</p>
            </div>
            <div class="adventiste-card-pro-static p-4 sm:p-5 border-t-4 border-t-rose-500">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400 flex items-center gap-2">
                    <i class="fas fa-receipt text-rose-600 dark:text-rose-400" aria-hidden="true"></i>
                    Total dépenses
                </p>
                <p class="mt-1 text-xl font-bold text-rose-700 dark:text-rose-400 tabular-nums">{{ $fmt($report['total_depenses']) }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Toutes catégories</p>
            </div>
            <div class="adventiste-card-pro-static p-4 sm:p-5 border-t-4 {{ $report['solde'] >= 0 ? 'border-t-sky-500' : 'border-t-amber-500' }}">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400 flex items-center gap-2">
                    <i class="fas fa-balance-scale text-slate-500 dark:text-slate-400" aria-hidden="true"></i>
                    Solde
                </p>
                <p class="mt-1 text-xl font-bold tabular-nums {{ $report['solde'] >= 0 ? 'text-sky-700 dark:text-sky-300' : 'text-amber-700 dark:text-amber-400' }}">{{ $fmt($report['solde']) }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $report['solde'] >= 0 ? 'Excédent' : 'Déficit' }} · recettes − dépenses</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="adventiste-card-pro-static overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-200/80 dark:border-slate-600/80 bg-slate-50/80 dark:bg-slate-800/50">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white m-0 flex items-center gap-2">
                        <i class="fas fa-layer-group text-emerald-600 dark:text-emerald-400" aria-hidden="true"></i>
                        Détails des recettes (hors Procure)
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 m-0 mt-1">Montants agrégés par catégorie de recette</p>
                </div>
                <div class="p-4 sm:p-5">
                    @if (count($report['details_recettes']) > 0)
                        <div class="adventiste-table-shell border-0 rounded-none shadow-none">
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead>
                                        <tr class="text-left text-slate-700 dark:text-slate-200">
                                            <th class="px-4 py-3 font-semibold">Catégorie</th>
                                            <th class="px-4 py-3 font-semibold text-right">Montant</th>
                                            <th class="px-4 py-3 font-semibold text-center">Nb</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                                        @foreach ($report['details_recettes'] as $detail)
                                            <tr class="text-slate-700 dark:text-slate-200">
                                                <td class="px-4 py-3">{{ $detail['nom'] }}</td>
                                                <td class="px-4 py-3 text-right font-medium text-emerald-700 dark:text-emerald-400 tabular-nums">{{ $fmt($detail['montant']) }}</td>
                                                <td class="px-4 py-3 text-center tabular-nums">{{ $detail['count'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-emerald-50/90 dark:bg-emerald-950/30 font-bold text-slate-900 dark:text-slate-100">
                                            <td class="px-4 py-3">Total</td>
                                            <td class="px-4 py-3 text-right tabular-nums">{{ $fmt($report['total_recettes']) }}</td>
                                            <td class="px-4 py-3 text-center tabular-nums">{{ $report['revenues']->count() }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    @else
                        <p class="text-sm text-slate-500 dark:text-slate-400 m-0">Aucune recette dans les catégories retenues pour cette période.</p>
                    @endif
                </div>
            </div>

            <div class="adventiste-card-pro-static overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-200/80 dark:border-slate-600/80 bg-slate-50/80 dark:bg-slate-800/50">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white m-0 flex items-center gap-2">
                        <i class="fas fa-chart-pie text-rose-600 dark:text-rose-400" aria-hidden="true"></i>
                        Dépenses par catégorie
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 m-0 mt-1">Ventilation sur la période du rapport</p>
                </div>
                <div class="p-4 sm:p-5">
                    <div class="adventiste-table-shell border-0 rounded-none shadow-none">
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="text-left text-slate-700 dark:text-slate-200">
                                        <th class="px-4 py-3 font-semibold">Source de financement</th>
                                        <th class="px-4 py-3 font-semibold text-right">Montant</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                                    @php
                                        $revenueCategories = \App\Models\RevenueCategory::where('paroisse_id', $financialReport->paroisse_id)
                                            ->where('actif', 1)
                                            ->orderBy('ordre')
                                            ->get();
                                    @endphp
                                    @forelse ($revenueCategories as $category)
                                        @php
                                            $montant = $report['details_depenses'][$category->code] ?? 0;
                                        @endphp
                                        @if ($montant > 0)
                                        <tr class="text-slate-700 dark:text-slate-200">
                                            <td class="px-4 py-3">{{ $category->nom }}</td>
                                            <td class="px-4 py-3 text-right font-medium tabular-nums">{{ $fmt($montant) }}</td>
                                        </tr>
                                        @endif
                                    @empty
                                        <tr class="text-slate-700 dark:text-slate-200">
                                            <td colspan="2" class="px-4 py-3 text-center text-slate-500 italic">Aucune dépense enregistrée</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr class="bg-rose-50/90 dark:bg-rose-950/30 font-bold text-slate-900 dark:text-slate-100">
                                        <td class="px-4 py-3">Total</td>
                                        <td class="px-4 py-3 text-right tabular-nums">{{ $fmt($report['total_depenses']) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if ($report['revenues']->count() > 0)
            <div class="adventiste-card-pro-static overflow-hidden mb-6">
                <div class="px-4 py-3 border-b border-slate-200/80 dark:border-slate-600/80 bg-slate-50/80 dark:bg-slate-800/50">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white m-0 flex items-center gap-2">
                        <i class="fas fa-list text-slate-500 dark:text-slate-400" aria-hidden="true"></i>
                        Liste détaillée des recettes
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
                                @foreach ($report['revenues'] as $revenue)
                                    <tr class="text-slate-700 dark:text-slate-200">
                                        <td class="px-4 py-3 whitespace-nowrap">{{ $revenue->date_recette?->format('d/m/Y') }}</td>
                                        <td class="px-4 py-3">{{ $revenue->category->nom ?? '—' }}</td>
                                        <td class="px-4 py-3">{{ $revenue->type->nom ?? '—' }}</td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex rounded-full bg-slate-100 dark:bg-slate-700 px-2.5 py-0.5 text-xs font-medium text-slate-700 dark:text-slate-200">{{ $payLabel($revenue->methode_paiement ?? '') ?: '—' }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-right font-semibold text-emerald-700 dark:text-emerald-400 tabular-nums">{{ $fmt($revenue->montant) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-slate-50/90 dark:bg-slate-800/50 font-bold text-slate-900 dark:text-slate-100">
                                    <td class="px-4 py-3 text-right" colspan="4">Total recettes</td>
                                    <td class="px-4 py-3 text-right tabular-nums">{{ $fmt($report['total_recettes']) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        @if ($report['expenses']->count() > 0)
            <div class="adventiste-card-pro-static overflow-hidden mb-6">
                <div class="px-4 py-3 border-b border-slate-200/80 dark:border-slate-600/80 bg-slate-50/80 dark:bg-slate-800/50">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white m-0 flex items-center gap-2">
                        <i class="fas fa-list text-slate-500 dark:text-slate-400" aria-hidden="true"></i>
                        Liste détaillée des dépenses
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 m-0 mt-1">{{ $report['expenses']->count() }} ligne(s)</p>
                </div>
                <div class="adventiste-table-shell border-0 rounded-none shadow-none">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left text-slate-700 dark:text-slate-200">
                                    <th class="px-4 py-3 font-semibold">Date</th>
                                    <th class="px-4 py-3 font-semibold">Source</th>
                                    <th class="px-4 py-3 font-semibold">Type</th>
                                    <th class="px-4 py-3 font-semibold">Fournisseur</th>
                                    <th class="px-4 py-3 font-semibold">Réf. facture</th>
                                    <th class="px-4 py-3 font-semibold text-right">Montant</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                                @foreach ($report['expenses'] as $expense)
                                    <tr class="text-slate-700 dark:text-slate-200">
                                        <td class="px-4 py-3 whitespace-nowrap">{{ $expense->date_depense?->format('d/m/Y') }}</td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium bg-emerald-50 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-200">{{ $expense->revenueCategory?->nom ?? '—' }}</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            @php
                                                $sourceLabels = $expense->fundingSources
                                                    ? $expense->fundingSources
                                                        ->map(fn ($source) => $source->caisse?->nom ?? $source->revenueType?->nom)
                                                        ->filter()
                                                        ->values()
                                                    : collect();
                                            @endphp
                                            {{ $sourceLabels->isNotEmpty() ? $sourceLabels->join(', ') : ($expense->revenueType?->nom ?? '—') }}
                                        </td>
                                        <td class="px-4 py-3">{{ $expense->fournisseur ?? '—' }}</td>
                                        <td class="px-4 py-3 font-mono text-xs">{{ $expense->facture_reference ?? '—' }}</td>
                                        <td class="px-4 py-3 text-right font-semibold text-rose-700 dark:text-rose-400 tabular-nums">{{ $fmt($expense->montant) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-slate-50/90 dark:bg-slate-800/50 font-bold text-slate-900 dark:text-slate-100">
                                    <td class="px-4 py-3 text-right" colspan="5">Total dépenses</td>
                                    <td class="px-4 py-3 text-right tabular-nums">{{ $fmt($report['total_depenses']) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <div class="adventiste-card-pro-static p-4 sm:p-5 border border-slate-200/80 dark:border-slate-600/60 bg-slate-50/50 dark:bg-slate-900/30 print-footer text-sm text-slate-600 dark:text-slate-400">
            <p class="mb-2 m-0 flex gap-2">
                <i class="fas fa-info-circle mt-0.5 shrink-0 text-slate-500" aria-hidden="true"></i>
                <span><strong class="text-slate-800 dark:text-slate-200">Note :</strong> le solde compare les recettes retenues (quête ordinaire et extraordinaire, location, fête, Banque ; hors Procure) à l’ensemble des dépenses validées sur la période.</span>
            </p>
            <p class="text-xs text-slate-500 dark:text-slate-500 m-0">Document consulté / imprimé le {{ now()->format('d/m/Y à H:i') }}</p>
        </div>
    </div>

    @push('styles')
        <style>
            @media print {
                @page {
                    margin: 1.5cm;
                }

                body {
                    background: #fff !important;
                }

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

                .ged-flash {
                    display: none !important;
                }

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

                table {
                    font-size: 11px;
                }
            }
        </style>
    @endpush
@endsection
