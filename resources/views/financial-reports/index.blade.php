@extends('layouts.app')

@section('title', 'Génération de rapport mensuel — Catholique')
@section('page-title', 'Génération de rapport mensuel')
@section('page-title-info', 'Justification mensuelle : recettes validées hors Procure (quêtes, locations, popote/subvention), dépenses validées toutes catégories, solde = recettes − dépenses. Enregistrement possible selon les droits.')

@section('btn-create')
    <nav class="inline-flex min-w-0 max-w-full flex-wrap items-center justify-end gap-2" aria-label="Navigation rapports financiers">
        <button type="button" class="adventiste-btn-secondary text-sm inline-flex items-center gap-1.5" onclick="document.getElementById('financialReportHelpModal').showModal()" title="Aide sur ce hub">
            <i class="fas fa-question-circle text-slate-500 dark:text-slate-400" aria-hidden="true"></i>
            <span>Aide</span>
        </button>
        <span class="mx-0.5 hidden h-6 w-px shrink-0 self-center bg-slate-200 dark:bg-slate-600 sm:block" role="presentation" aria-hidden="true"></span>
        <a href="{{ route('financial-reports.list') }}" class="adventiste-btn-primary text-sm no-underline inline-flex items-center gap-1.5 shadow-sm">
            <i class="fas fa-folder-open" aria-hidden="true"></i>
            <span>Historique</span>
        </a>
        <span class="mx-0.5 hidden h-6 w-px shrink-0 self-center bg-slate-200 dark:bg-slate-600 sm:block" role="presentation" aria-hidden="true"></span>
        <a href="{{ route('financial-statistics.index') }}" class="adventiste-btn-secondary text-sm no-underline inline-flex items-center gap-1.5">
            <i class="fas fa-chart-line text-slate-500 dark:text-slate-400" aria-hidden="true"></i>
            <span>Statistiques</span>
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

    <div class="rounded-xl border border-sky-200/90 dark:border-sky-800/50 bg-sky-50/90 dark:bg-sky-950/25 px-4 py-3 mb-6 text-sm text-sky-950 dark:text-sky-100 leading-relaxed">
        <p class="m-0 flex gap-2">
            <i class="fas fa-info-circle mt-0.5 shrink-0 text-sky-600 dark:text-sky-400" aria-hidden="true"></i>
            <span>
                <strong class="font-semibold">Méthode de calcul :</strong>
                les <strong>recettes</strong> totalisent uniquement les catégories quête ordinaire, quête extraordinaire, location et popote/subvention (la <strong>Procure</strong> est exclue) pour les lignes au statut « validé ».
                Les <strong>dépenses</strong> additionnent toutes les catégories de charges sur la même période (statut « validé »).
                Le <strong>solde</strong> est la différence entre ces deux totaux.
            </span>
        </p>
    </div>

    @if ($report)
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="adventiste-card-pro-static p-4 border-t-4 border-t-emerald-500">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400 flex items-center gap-2">
                    <i class="fas fa-arrow-up text-emerald-600 dark:text-emerald-400" aria-hidden="true"></i>
                    Total recettes
                </p>
                <p class="mt-1 text-xl font-bold text-emerald-700 dark:text-emerald-400">{{ $fmt($report['total_recettes']) }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Quêtes, locations, popote (hors Procure)</p>
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
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $report['solde'] >= 0 ? 'Excédent' : 'Déficit' }} · recettes − dépenses</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="adventiste-card-pro-static overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-200/80 dark:border-slate-600/80 bg-slate-50/80 dark:bg-slate-800/50">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white m-0 flex items-center gap-2">
                        <i class="fas fa-coins text-amber-600 dark:text-amber-400" aria-hidden="true"></i>
                        Détails des recettes (hors Procure)
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 m-0 mt-1">Somme par catégorie de recette</p>
                </div>
                <div class="p-4">
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
                                                <td class="px-4 py-3 text-right font-semibold">{{ $fmt($detail['montant']) }}</td>
                                                <td class="px-4 py-3 text-center">
                                                    <span class="inline-flex rounded-full bg-sky-50 dark:bg-sky-900/30 px-2.5 py-0.5 text-xs font-medium text-sky-800 dark:text-sky-200">{{ $detail['count'] }}</span>
                                                </td>
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
                        <p class="text-sm text-slate-500 dark:text-slate-400 m-0">Aucune recette dans les catégories retenues (ou catégories non configurées) pour cette période.</p>
                    @endif
                </div>
            </div>

            <div class="adventiste-card-pro-static overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-200/80 dark:border-slate-600/80 bg-slate-50/80 dark:bg-slate-800/50">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white m-0 flex items-center gap-2">
                        <i class="fas fa-receipt text-slate-500 dark:text-slate-400" aria-hidden="true"></i>
                        Dépenses par source de financement
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 m-0 mt-1">Ventilation selon les catégories de revenus (somme = total dépenses)</p>
                </div>
                <div class="p-4">
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
                                        $revenueCategories = \App\Models\RevenueCategory::where('paroisse_id', $selectedParoisseId)
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
                                            <td class="px-4 py-3 text-right font-semibold">{{ $fmt($montant) }}</td>
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
            @php
                $payLabel = static fn ($v) => ucfirst(str_replace('_', ' ', (string) $v));
            @endphp
            <div class="adventiste-card-pro-static overflow-hidden mb-6">
                <div class="px-4 py-3 border-b border-slate-200/80 dark:border-slate-600/80 bg-slate-50/80 dark:bg-slate-800/50">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white m-0 flex items-center gap-2">
                        <i class="fas fa-list text-slate-500 dark:text-slate-400" aria-hidden="true"></i>
                        Liste détaillée des recettes
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 m-0 mt-1">{{ $report['revenues']->count() }} ligne(s) · mêmes catégories que le total « recettes »</p>
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
                                        <td class="px-4 py-3 text-right font-semibold text-emerald-700 dark:text-emerald-400">{{ $fmt($revenue->montant) }}</td>
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

        <div class="adventiste-card-pro-static overflow-hidden mb-6">
            <div class="px-4 py-3 border-b border-slate-200/80 dark:border-slate-600/80 bg-slate-50/80 dark:bg-slate-800/50">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white m-0 flex items-center gap-2">
                    <i class="fas fa-list text-slate-500 dark:text-slate-400" aria-hidden="true"></i>
                    Liste détaillée des dépenses
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 m-0 mt-1">Toutes les dépenses validées sur la période</p>
            </div>
            <div class="p-0 sm:p-0">
                @if ($report['expenses']->count() > 0)
                    <div class="adventiste-table-shell border-0 rounded-none shadow-none">
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="text-left text-slate-700 dark:text-slate-200">
                                        <th class="px-4 py-3 font-semibold">Date</th>
                                        <th class="px-4 py-3 font-semibold">Source</th>
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
                                                <span class="inline-flex rounded-full bg-emerald-50 dark:bg-emerald-900/30 px-2.5 py-0.5 text-xs font-medium text-emerald-800 dark:text-emerald-200">{{ $expense->revenueCategory?->nom ?? '—' }}</span>
                                            </td>
                                            <td class="px-4 py-3">
                                                @php
                                                    $sourceLabels = $expense->fundingSources
                                                        ? $expense->fundingSources
                                                            ->map(fn ($source) => $source->revenueType?->nom)
                                                            ->filter()
                                                            ->values()
                                                        : collect();
                                                @endphp
                                                {{ $sourceLabels->isNotEmpty() ? $sourceLabels->join(', ') : ($expense->revenueType?->nom ?? '—') }}
                                            </td>
                                            <td class="px-4 py-3">{{ $expense->fournisseur ?? '—' }}</td>
                                            <td class="px-4 py-3 text-right font-semibold text-rose-700 dark:text-rose-400">{{ $fmt($expense->montant) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="bg-slate-50/90 dark:bg-slate-800/50 font-bold text-slate-900 dark:text-slate-100">
                                        <td class="px-4 py-3 text-right" colspan="4">Total dépenses</td>
                                        <td class="px-4 py-3 text-right tabular-nums">{{ $fmt($report['total_depenses']) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                @else
                    <p class="text-sm text-slate-500 dark:text-slate-400 p-4 m-0">Aucune dépense validée pour cette période.</p>
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
                Choisissez le mois, l’année et la paroisse (si besoin), puis cliquez sur « Calculer le rapport » pour afficher le détail des recettes (hors Procure), des dépenses et le solde.
            </p>
        </div>
    @endif

    @include('financial-reports._help_modal')
@endsection
