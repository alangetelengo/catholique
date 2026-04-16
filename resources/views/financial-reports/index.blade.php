@extends('layouts.app')

@section('title', 'Rapports financiers — Catholique')
@section('page-title', 'Rapports financiers')
@section('page-title-info', 'Choisissez paroisse, mois et année, puis calculez le rapport de justification (recettes popote/subvention, dépenses, solde). Vous pouvez l’enregistrer si votre rôle le permet.')

@section('btn-create')
    <nav class="inline-flex min-w-0 max-w-full flex-wrap items-center justify-end gap-2" aria-label="Navigation rapports financiers">
        <button type="button" class="adventiste-btn-secondary text-sm inline-flex items-center gap-1.5" onclick="document.getElementById('financialReportHelpModal').showModal()" title="Aide sur ce hub">
            <i class="fas fa-question-circle text-slate-500 dark:text-slate-400" aria-hidden="true"></i>
            <span>Aide</span>
        </button>
        <span class="mx-0.5 hidden h-6 w-px shrink-0 self-center bg-slate-200 dark:bg-slate-600 sm:block" role="presentation" aria-hidden="true"></span>
        <a href="{{ route('financial-reports.statistics') }}" class="adventiste-btn-secondary text-sm no-underline inline-flex items-center gap-1.5">
            <i class="fas fa-chart-line text-slate-500 dark:text-slate-400" aria-hidden="true"></i>
            <span>Stats rapports</span>
        </a>
        <a href="{{ route('financial-reports.list') }}" class="adventiste-btn-primary text-sm no-underline inline-flex items-center gap-1.5 shadow-sm">
            <i class="fas fa-folder-open" aria-hidden="true"></i>
            <span>Rapports enregistrés</span>
        </a>
        <span class="mx-0.5 hidden h-6 w-px shrink-0 self-center bg-slate-200 dark:bg-slate-600 sm:block" role="presentation" aria-hidden="true"></span>
        <a href="{{ route('financial-reports.revenues-by-category') }}" class="adventiste-btn-secondary text-sm no-underline inline-flex items-center gap-1.5">
            <i class="fas fa-layer-group text-slate-500 dark:text-slate-400" aria-hidden="true"></i>
            <span>Par catégories</span>
        </a>
        <a href="{{ route('financial-reports.popote') }}" class="adventiste-btn-secondary text-sm no-underline inline-flex items-center gap-1.5">
            <i class="fas fa-utensils text-slate-500 dark:text-slate-400" aria-hidden="true"></i>
            <span>Subvention Popote</span>
        </a>
        <a href="{{ route('financial-reports.charges-fixes') }}" class="adventiste-btn-secondary text-sm no-underline inline-flex items-center gap-1.5">
            <i class="fas fa-file-invoice-dollar text-slate-500 dark:text-slate-400" aria-hidden="true"></i>
            <span>Charges fixes</span>
        </a>
    </nav>
@endsection

@section('content')
    @php
        $fmt = static fn ($n) => \App\Helpers\ParoisseConfig::formatMontant($n);
    @endphp

    <div class="adventiste-card-pro-static p-4 sm:p-5 mb-6">
        <h2 class="text-base font-semibold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
            <i class="fas fa-filter text-emerald-600 dark:text-emerald-400" aria-hidden="true"></i>
            Période et paroisse
        </h2>
        <form method="GET" action="{{ route('financial-reports.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
            @if (auth()->user()->hasRole('super_admin') && $paroisses->count() > 0)
                <div class="md:col-span-2 lg:col-span-1">
                    <label for="hub_paroisse" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Paroisse <span class="text-red-500">*</span></label>
                    <select id="hub_paroisse" name="paroisse_id" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm" required>
                        <option value="">Sélectionner…</option>
                        @foreach ($paroisses as $paroisse)
                            <option value="{{ $paroisse->id }}" @selected($selectedParoisseId == $paroisse->id)>{{ $paroisse->nom }}</option>
                        @endforeach
                    </select>
                </div>
            @else
                <input type="hidden" name="paroisse_id" value="{{ auth()->user()->paroisse_id }}">
            @endif

            <div>
                <label for="hub_month" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Mois <span class="text-red-500">*</span></label>
                <select id="hub_month" name="month" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm" required>
                    @foreach ([
                        1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
                        5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
                        9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre',
                    ] as $num => $nom)
                        <option value="{{ $num }}" @selected($selectedMonth == $num)>{{ $nom }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="hub_year" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Année <span class="text-red-500">*</span></label>
                <select id="hub_year" name="year" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm" required>
                    @for ($y = now()->year; $y >= now()->year - 5; $y--)
                        <option value="{{ $y }}" @selected($selectedYear == $y)>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <div class="lg:col-span-4">
                <button type="submit" class="adventiste-btn-primary">
                    <i class="fas fa-calculator me-2" aria-hidden="true"></i>Calculer le rapport
                </button>
            </div>
        </form>
    </div>

    @if ($report)
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="adventiste-card-pro-static p-4 border-t-4 border-t-emerald-500">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400 flex items-center gap-2">
                    <i class="fas fa-arrow-up text-emerald-600 dark:text-emerald-400" aria-hidden="true"></i>
                    Total recettes
                </p>
                <p class="mt-1 text-xl font-bold text-emerald-700 dark:text-emerald-400">{{ $fmt($report['total_recettes']) }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Popote / Subvention</p>
            </div>
            <div class="adventiste-card-pro-static p-4 border-t-4 border-t-rose-500">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400 flex items-center gap-2">
                    <i class="fas fa-arrow-down text-rose-600 dark:text-rose-400" aria-hidden="true"></i>
                    Total dépenses
                </p>
                <p class="mt-1 text-xl font-bold text-rose-700 dark:text-rose-400">{{ $fmt($report['total_depenses']) }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Toutes catégories</p>
            </div>
            <div class="adventiste-card-pro-static p-4 border-t-4 {{ $report['solde'] >= 0 ? 'border-t-sky-500' : 'border-t-amber-500' }}">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400 flex items-center gap-2">
                    <i class="fas fa-balance-scale text-slate-500 dark:text-slate-400" aria-hidden="true"></i>
                    Solde
                </p>
                <p class="mt-1 text-xl font-bold {{ $report['solde'] >= 0 ? 'text-sky-700 dark:text-sky-300' : 'text-amber-700 dark:text-amber-400' }}">{{ $fmt($report['solde']) }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $report['solde'] >= 0 ? 'Excédent' : 'Déficit' }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="adventiste-card-pro-static overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-200/80 dark:border-slate-600/80 bg-slate-50/80 dark:bg-slate-800/50">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white m-0 flex items-center gap-2">
                        <i class="fas fa-coins text-amber-600 dark:text-amber-400" aria-hidden="true"></i>
                        Détails des recettes (Popote/Subvention)
                    </h3>
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
                                                <td class="px-4 py-3 text-right font-semibold">{{ $fmt($detail['montant']) }}</td>
                                                <td class="px-4 py-3 text-center">
                                                    <span class="inline-flex rounded-full bg-sky-50 dark:bg-sky-900/30 px-2.5 py-0.5 text-xs font-medium text-sky-800 dark:text-sky-200">{{ $detail['count'] }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
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
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white m-0 flex items-center gap-2">
                        <i class="fas fa-receipt text-slate-500 dark:text-slate-400" aria-hidden="true"></i>
                        Dépenses par catégorie
                    </h3>
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
                                        <td class="px-4 py-3 text-right font-semibold">{{ $fmt($report['details_depenses']['charge_fixe']) }}</td>
                                    </tr>
                                    <tr class="text-slate-700 dark:text-slate-200">
                                        <td class="px-4 py-3">Charges variables</td>
                                        <td class="px-4 py-3 text-right font-semibold">{{ $fmt($report['details_depenses']['charge_variable']) }}</td>
                                    </tr>
                                    <tr class="text-slate-700 dark:text-slate-200">
                                        <td class="px-4 py-3">Charges exceptionnelles</td>
                                        <td class="px-4 py-3 text-right font-semibold">{{ $fmt($report['details_depenses']['charge_exceptionnelle']) }}</td>
                                    </tr>
                                    <tr class="text-slate-700 dark:text-slate-200">
                                        <td class="px-4 py-3">Alimentation popote</td>
                                        <td class="px-4 py-3 text-right font-semibold">{{ $fmt($report['details_depenses']['alimentation_popote'] ?? 0) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="adventiste-card-pro-static overflow-hidden mb-6">
            <div class="px-4 py-3 border-b border-slate-200/80 dark:border-slate-600/80 bg-slate-50/80 dark:bg-slate-800/50">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white m-0 flex items-center gap-2">
                    <i class="fas fa-list text-slate-500 dark:text-slate-400" aria-hidden="true"></i>
                    Liste détaillée des dépenses
                </h3>
            </div>
            <div class="p-0 sm:p-0">
                @if ($report['expenses']->count() > 0)
                    @php
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
                    <div class="adventiste-table-shell border-0 rounded-none shadow-none">
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="text-left text-slate-700 dark:text-slate-200">
                                        <th class="px-4 py-3 font-semibold">Date</th>
                                        <th class="px-4 py-3 font-semibold">Catégorie</th>
                                        <th class="px-4 py-3 font-semibold">Type</th>
                                        <th class="px-4 py-3 font-semibold">Fournisseur</th>
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
                                            <td class="px-4 py-3 text-right font-semibold text-rose-700 dark:text-rose-400">{{ $fmt($expense->montant) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <p class="text-sm text-slate-500 dark:text-slate-400 p-4 m-0">Aucune dépense enregistrée pour cette période.</p>
                @endif
            </div>
        </div>

        @can('generate_financial_reports')
            <div class="flex justify-end">
                <form action="{{ route('financial-reports.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="paroisse_id" value="{{ $selectedParoisseId }}">
                    <input type="hidden" name="month" value="{{ $selectedMonth }}">
                    <input type="hidden" name="year" value="{{ $selectedYear }}">
                    <button type="submit" class="adventiste-btn-primary">
                        <i class="fas fa-save me-2" aria-hidden="true"></i>Enregistrer ce rapport
                    </button>
                </form>
            </div>
        @endcan
    @else
        <div class="adventiste-card-pro-static p-12 text-center">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400">
                <i class="fas fa-chart-pie text-2xl" aria-hidden="true"></i>
            </div>
            <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Sélectionnez une période</h3>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400 max-w-md mx-auto">
                Choisissez le mois, l’année et la paroisse (si besoin), puis cliquez sur « Calculer le rapport » pour afficher recettes, dépenses et solde.
            </p>
        </div>
    @endif

    @include('financial-reports._help_modal')
@endsection
