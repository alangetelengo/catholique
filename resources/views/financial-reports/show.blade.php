@extends('layouts.app')

@section('title', 'Rapport financier — Catholique')
@section('page-title', 'Rapport mensuel de justification')

@section('page-title-info')
    <span class="font-medium text-slate-800 dark:text-slate-100">{{ $financialReport->paroisse->nom ?? '—' }}</span>
    <span class="text-slate-500 dark:text-slate-400"> — </span>
    <span>{{ $financialReport->date_debut->copy()->locale(app()->getLocale())->translatedFormat('F Y') }}</span>
    <span class="block mt-1 text-slate-600 dark:text-slate-400">Période du {{ $financialReport->date_debut->format('d/m/Y') }} au {{ $financialReport->date_fin->format('d/m/Y') }} · Généré le {{ $financialReport->created_at->format('d/m/Y à H:i') }}@if ($financialReport->createdBy) par {{ $financialReport->createdBy->name }}@endif</span>
@endsection

@section('btn-create')
    <div class="flex flex-wrap items-center gap-2 print:hidden">
        <a href="{{ route('financial-reports.list') }}" class="adventiste-btn-secondary text-sm no-underline">
            <i class="fas fa-arrow-left me-1.5" aria-hidden="true"></i>Retour à la liste
        </a>
        <a href="{{ route('financial-reports.index') }}" class="adventiste-btn-secondary text-sm no-underline">
            <i class="fas fa-chart-pie me-1.5" aria-hidden="true"></i>Hub rapports
        </a>
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
        $cats = [
            'charge_fixe' => 'Charge fixe',
            'charge_variable' => 'Charge variable',
            'charge_exceptionnelle' => 'Charge exceptionnelle',
            'alimentation_popote' => 'Alimentation popote',
        ];
        $types = [
            'carburant' => 'Carburant',
            'hosties' => 'Hosties',
            'internet' => 'Internet',
            'maintenance_materiel' => 'Maintenance matériel',
            'gaz' => 'Gaz',
            'eau' => 'Eau',
            'electricite' => 'Électricité',
            'jardinage' => 'Jardinage',
            'salaire_ouvrier' => 'Salaire ouvrier',
            'autre' => 'Autre',
            'alimentation' => 'Alimentation',
        ];
    @endphp

    <div id="report-content">
        {{-- En-tête pour impression --}}
        <div class="mb-6 text-center print-header hidden print:block">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ $financialReport->paroisse->nom ?? 'Paroisse' }}</h3>
            <p class="text-base font-semibold text-slate-800 dark:text-slate-200 mt-1">Rapport financier mensuel</p>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-2">
                Période : {{ $financialReport->date_debut->format('d/m/Y') }} au {{ $financialReport->date_fin->format('d/m/Y') }}
            </p>
            <p class="text-sm text-slate-600 dark:text-slate-400">
                Généré le : {{ $financialReport->created_at->format('d/m/Y à H:i') }}
                @if ($financialReport->createdBy)
                    par {{ $financialReport->createdBy->name }}
                @endif
            </p>
            <hr class="mt-4 border-slate-200 dark:border-slate-600">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="adventiste-card-pro-static p-4 border-t-4 border-t-emerald-500">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Total recettes</p>
                <p class="mt-1 text-xl font-bold text-emerald-700 dark:text-emerald-400">{{ $fmt($report['total_recettes']) }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Popote / Subvention</p>
            </div>
            <div class="adventiste-card-pro-static p-4 border-t-4 border-t-rose-500">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Total dépenses</p>
                <p class="mt-1 text-xl font-bold text-rose-700 dark:text-rose-400">{{ $fmt($report['total_depenses']) }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Toutes catégories</p>
            </div>
            <div class="adventiste-card-pro-static p-4 border-t-4 {{ $report['solde'] >= 0 ? 'border-t-sky-500' : 'border-t-amber-500' }}">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Solde</p>
                <p class="mt-1 text-xl font-bold {{ $report['solde'] >= 0 ? 'text-sky-700 dark:text-sky-300' : 'text-amber-700 dark:text-amber-400' }}">{{ $fmt($report['solde']) }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $report['solde'] >= 0 ? 'Excédent' : 'Déficit' }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="adventiste-card-pro-static overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-200/80 dark:border-slate-600/80 bg-slate-50/80 dark:bg-slate-800/50">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white m-0">Détails des recettes (Popote/Subvention)</h3>
                </div>
                <div class="p-4">
                    @if (count($report['details_recettes']) > 0)
                        <div class="adventiste-table-shell border-0 rounded-none shadow-none">
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead>
                                        <tr class="text-left text-slate-700 dark:text-slate-200">
                                            <th class="px-4 py-3 font-semibold">Type</th>
                                            <th class="px-4 py-3 font-semibold text-right">Montant</th>
                                            <th class="px-4 py-3 font-semibold text-center">Nb</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                                        @foreach ($report['details_recettes'] as $detail)
                                            <tr class="text-slate-700 dark:text-slate-200">
                                                <td class="px-4 py-3">{{ $detail['nom'] }}</td>
                                                <td class="px-4 py-3 text-right font-medium">{{ $fmt($detail['montant']) }}</td>
                                                <td class="px-4 py-3 text-center">{{ $detail['count'] }}</td>
                                            </tr>
                                        @endforeach
                                        <tr class="bg-emerald-50/90 dark:bg-emerald-950/30 font-bold text-slate-900 dark:text-slate-100">
                                            <td class="px-4 py-3">TOTAL</td>
                                            <td class="px-4 py-3 text-right">{{ $fmt($report['total_recettes']) }}</td>
                                            <td class="px-4 py-3 text-center">{{ $report['revenues']->count() }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        <p class="text-sm text-slate-500 dark:text-slate-400 m-0">Aucune recette popote/subvention pour cette période.</p>
                    @endif
                </div>
            </div>

            <div class="adventiste-card-pro-static overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-200/80 dark:border-slate-600/80 bg-slate-50/80 dark:bg-slate-800/50">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white m-0">Dépenses par catégorie</h3>
                </div>
                <div class="p-4">
                    <div class="adventiste-table-shell border-0 rounded-none shadow-none">
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="text-left text-slate-700 dark:text-slate-200">
                                        <th class="px-4 py-3 font-semibold">Catégorie</th>
                                        <th class="px-4 py-3 font-semibold text-right">Montant</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                                    <tr class="text-slate-700 dark:text-slate-200">
                                        <td class="px-4 py-3">Charges fixes</td>
                                        <td class="px-4 py-3 text-right font-medium">{{ $fmt($report['details_depenses']['charge_fixe']) }}</td>
                                    </tr>
                                    <tr class="text-slate-700 dark:text-slate-200">
                                        <td class="px-4 py-3">Charges variables</td>
                                        <td class="px-4 py-3 text-right font-medium">{{ $fmt($report['details_depenses']['charge_variable']) }}</td>
                                    </tr>
                                    <tr class="text-slate-700 dark:text-slate-200">
                                        <td class="px-4 py-3">Charges exceptionnelles</td>
                                        <td class="px-4 py-3 text-right font-medium">{{ $fmt($report['details_depenses']['charge_exceptionnelle']) }}</td>
                                    </tr>
                                    <tr class="text-slate-700 dark:text-slate-200">
                                        <td class="px-4 py-3">Alimentation popote</td>
                                        <td class="px-4 py-3 text-right font-medium">{{ $fmt($report['details_depenses']['alimentation_popote'] ?? 0) }}</td>
                                    </tr>
                                    <tr class="bg-rose-50/90 dark:bg-rose-950/30 font-bold text-slate-900 dark:text-slate-100">
                                        <td class="px-4 py-3">TOTAL</td>
                                        <td class="px-4 py-3 text-right">{{ $fmt($report['total_depenses']) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if ($report['revenues']->count() > 0)
            <div class="adventiste-card-pro-static overflow-hidden mb-6">
                <div class="px-4 py-3 border-b border-slate-200/80 dark:border-slate-600/80 bg-slate-50/80 dark:bg-slate-800/50">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white m-0">Liste détaillée des recettes</h3>
                </div>
                <div class="adventiste-table-shell border-0 rounded-none shadow-none">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left text-slate-700 dark:text-slate-200">
                                    <th class="px-4 py-3 font-semibold">Date</th>
                                    <th class="px-4 py-3 font-semibold">Type</th>
                                    <th class="px-4 py-3 font-semibold">Méthode</th>
                                    <th class="px-4 py-3 font-semibold">Référence</th>
                                    <th class="px-4 py-3 font-semibold text-right">Montant</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                                @foreach ($report['revenues'] as $revenue)
                                    <tr class="text-slate-700 dark:text-slate-200">
                                        <td class="px-4 py-3 whitespace-nowrap">{{ $revenue->date_recette?->format('d/m/Y') }}</td>
                                        <td class="px-4 py-3">{{ $revenue->type->nom ?? '—' }}</td>
                                        <td class="px-4 py-3">{{ $revenue->methode_paiement ?? '—' }}</td>
                                        <td class="px-4 py-3">{{ $revenue->reference_paiement ?? '—' }}</td>
                                        <td class="px-4 py-3 text-right font-semibold text-emerald-700 dark:text-emerald-400">{{ $fmt($revenue->montant) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        @if ($report['expenses']->count() > 0)
            <div class="adventiste-card-pro-static overflow-hidden mb-6">
                <div class="px-4 py-3 border-b border-slate-200/80 dark:border-slate-600/80 bg-slate-50/80 dark:bg-slate-800/50">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white m-0">Liste détaillée des dépenses</h3>
                </div>
                <div class="adventiste-table-shell border-0 rounded-none shadow-none">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left text-slate-700 dark:text-slate-200">
                                    <th class="px-4 py-3 font-semibold">Date</th>
                                    <th class="px-4 py-3 font-semibold">Catégorie</th>
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
                                            <span class="inline-flex rounded-full bg-emerald-50 dark:bg-emerald-900/30 px-2.5 py-0.5 text-xs font-medium text-emerald-800 dark:text-emerald-200">{{ $cats[$expense->categorie_charge] ?? $expense->categorie_charge }}</span>
                                        </td>
                                        <td class="px-4 py-3">{{ $types[$expense->type_charge] ?? $expense->type_charge }}</td>
                                        <td class="px-4 py-3">{{ $expense->fournisseur ?? '—' }}</td>
                                        <td class="px-4 py-3">{{ $expense->facture_reference ?? '—' }}</td>
                                        <td class="px-4 py-3 text-right font-semibold text-rose-700 dark:text-rose-400">{{ $fmt($expense->montant) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <div class="mt-8 pt-4 border-t border-slate-200 dark:border-slate-600 print-footer text-sm text-slate-600 dark:text-slate-400">
            <p class="mb-2">
                <strong class="text-slate-800 dark:text-slate-200">Note :</strong>
                Ce rapport justifie les dépenses effectuées contre les recettes popote/subvention reçues pour la période indiquée.
            </p>
            <p class="text-xs text-slate-500 dark:text-slate-500 m-0">Document consulté / imprimé le {{ now()->format('d/m/Y à H:i') }}</p>
        </div>
    </div>

    @push('styles')
        <style>
            /* Impression : ne pas utiliser visibility sur body * (aperçu Chrome souvent vide). */
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
