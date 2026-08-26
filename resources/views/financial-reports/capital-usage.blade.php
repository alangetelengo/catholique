@extends('layouts.app')

@section('title', 'Capital → dépenses — Catholique')
@section('page-title', 'Capital reçu → dépenses')
@section('page-title-info')
    Suivi du capital Banque (économat diocésain) : recettes → virements vers caisses → dépenses financées.
@endsection

@section('btn-create')
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('financial-reports.expenses-by-category') }}" class="adventiste-btn-secondary text-sm no-underline">
            <i class="fas fa-receipt me-1.5" aria-hidden="true"></i>Dépenses par catégorie
        </a>
        <a href="{{ route('financial-reports.index') }}" class="adventiste-btn-secondary text-sm no-underline">
            <i class="fas fa-chart-pie me-1.5" aria-hidden="true"></i>Hub rapports
        </a>
    </div>
@endsection

@section('content')
    @php
        $fmt = static fn ($n) => \App\Helpers\ParoisseConfig::formatMontant($n);
    @endphp

    <div class="rounded-xl border border-sky-200/90 dark:border-sky-800/50 bg-sky-50/90 dark:bg-sky-950/25 px-4 py-3 mb-6 text-sm text-sky-950 dark:text-sky-100 leading-relaxed">
        <p class="m-0 flex gap-2">
            <i class="fas fa-info-circle mt-0.5 shrink-0 text-sky-600 dark:text-sky-400" aria-hidden="true"></i>
            <span>
                Le capital est d’abord crédité en <strong class="font-semibold">trésorerie</strong>, puis alloué aux caisses par <strong class="font-semibold">virement</strong>.
                Les dépenses affichées sont celles financées par les caisses alimentées sur la période.
            </span>
        </p>
    </div>

    <div class="adventiste-card-pro-static p-4 sm:p-5 mb-6">
        <h2 class="text-base font-semibold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
            <i class="fas fa-filter text-emerald-600 dark:text-emerald-400" aria-hidden="true"></i>
            Filtres
        </h2>
        <form method="GET" action="{{ route('financial-reports.capital-usage') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
            @if (auth()->user()->hasRole('super_admin') && $paroisses->count() > 0)
                <div>
                    <label for="cu_paroisse" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Paroisse <span class="text-red-500">*</span></label>
                    <select id="cu_paroisse" name="paroisse_id" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm" required>
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
                <label for="cu_date_debut" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Date début</label>
                <input type="date" id="cu_date_debut" name="date_debut" value="{{ $dateDebut }}" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm" required>
            </div>
            <div>
                <label for="cu_date_fin" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Date fin</label>
                <input type="date" id="cu_date_fin" name="date_fin" value="{{ $dateFin }}" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm" required>
            </div>
            <div>
                <button type="submit" class="adventiste-btn-primary">
                    <i class="fas fa-calculator me-2" aria-hidden="true"></i>Calculer
                </button>
            </div>
        </form>
    </div>

    @if ($report)
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
            <div class="adventiste-card-pro-static p-4 border-t-4 border-t-emerald-500">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Capital Banque reçu</p>
                <p class="mt-1 text-xl font-bold text-emerald-700 dark:text-emerald-400 tabular-nums">{{ $fmt($report['total_capital']) }}</p>
            </div>
            <div class="adventiste-card-pro-static p-4 border-t-4 border-t-sky-500">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Alloué aux caisses</p>
                <p class="mt-1 text-xl font-bold text-sky-700 dark:text-sky-300 tabular-nums">{{ $fmt($report['total_virements']) }}</p>
            </div>
            <div class="adventiste-card-pro-static p-4 border-t-4 border-t-rose-500">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Dépensé</p>
                <p class="mt-1 text-xl font-bold text-rose-700 dark:text-rose-400 tabular-nums">{{ $fmt($report['total_depenses']) }}</p>
            </div>
            <div class="adventiste-card-pro-static p-4 border-t-4 border-t-amber-500">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Reste alloué</p>
                <p class="mt-1 text-xl font-bold text-amber-700 dark:text-amber-400 tabular-nums">{{ $fmt($report['reste_alloue']) }}</p>
            </div>
        </div>

        <div class="adventiste-card-pro-static overflow-hidden mb-6">
            <div class="px-4 py-3 border-b border-slate-200/80 dark:border-slate-600/80 bg-slate-50/80 dark:bg-slate-800/50">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white m-0">Synthèse par caisse</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-700 dark:text-slate-200">
                            <th class="px-4 py-3 font-semibold">Caisse</th>
                            <th class="px-4 py-3 font-semibold text-right">Alloué</th>
                            <th class="px-4 py-3 font-semibold text-right">Dépensé</th>
                            <th class="px-4 py-3 font-semibold text-right">Solde</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                        @forelse ($report['by_caisse'] as $row)
                            <tr class="text-slate-700 dark:text-slate-200">
                                <td class="px-4 py-3">{{ $row['nom'] }}</td>
                                <td class="px-4 py-3 text-right tabular-nums">{{ $fmt($row['alloue']) }}</td>
                                <td class="px-4 py-3 text-right tabular-nums text-rose-700 dark:text-rose-400">{{ $fmt($row['depense']) }}</td>
                                <td class="px-4 py-3 text-right tabular-nums font-semibold">{{ $fmt($row['solde']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-slate-500 dark:text-slate-400">Aucun mouvement caisse sur cette période.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-6">
            <div class="adventiste-card-pro-static overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-200/80 dark:border-slate-600/80 bg-slate-50/80 dark:bg-slate-800/50">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white m-0">Capital Banque reçu</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-slate-700 dark:text-slate-200">
                                <th class="px-4 py-3 font-semibold">Date</th>
                                <th class="px-4 py-3 font-semibold">Mois</th>
                                <th class="px-4 py-3 font-semibold text-right">Montant</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                            @forelse ($report['capitals'] as $revenue)
                                <tr class="text-slate-700 dark:text-slate-200">
                                    <td class="px-4 py-3 whitespace-nowrap">{{ $revenue->date_recette?->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3">{{ \App\Support\SubventionMensuelle::formatMoisCapital($revenue->mois_capital) }}</td>
                                    <td class="px-4 py-3 text-right font-semibold text-emerald-700 dark:text-emerald-400 tabular-nums">{{ $fmt($revenue->montant) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-6 text-center text-slate-500">Aucune recette Banque sur la période.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="adventiste-card-pro-static overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-200/80 dark:border-slate-600/80 bg-slate-50/80 dark:bg-slate-800/50">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white m-0">Virements trésorerie → caisses</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-slate-700 dark:text-slate-200">
                                <th class="px-4 py-3 font-semibold">Date</th>
                                <th class="px-4 py-3 font-semibold">Destination</th>
                                <th class="px-4 py-3 font-semibold text-right">Montant</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                            @forelse ($report['virements'] as $mouvement)
                                <tr class="text-slate-700 dark:text-slate-200">
                                    <td class="px-4 py-3 whitespace-nowrap">{{ $mouvement->date_mouvement?->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3">{{ $mouvement->contrepartieCaisse?->nom ?? '—' }}</td>
                                    <td class="px-4 py-3 text-right font-semibold text-sky-700 dark:text-sky-300 tabular-nums">{{ $fmt($mouvement->montant) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-6 text-center text-slate-500">Aucun virement sur la période.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="adventiste-card-pro-static overflow-hidden mb-6">
            <div class="px-4 py-3 border-b border-slate-200/80 dark:border-slate-600/80 bg-slate-50/80 dark:bg-slate-800/50">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white m-0">Dépenses financées par les caisses</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-700 dark:text-slate-200">
                            <th class="px-4 py-3 font-semibold">Date</th>
                            <th class="px-4 py-3 font-semibold">Libellé</th>
                            <th class="px-4 py-3 font-semibold">Type</th>
                            <th class="px-4 py-3 font-semibold">Caisse</th>
                            <th class="px-4 py-3 font-semibold text-right">Montant</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                        @forelse ($report['expenses'] as $expense)
                            @php
                                $sourceLabels = $expense->fundingSources
                                    ->map(fn ($s) => $s->caisse?->nom)
                                    ->filter()
                                    ->unique()
                                    ->values();
                            @endphp
                            <tr class="text-slate-700 dark:text-slate-200">
                                <td class="px-4 py-3 whitespace-nowrap">{{ $expense->date_depense?->format('d/m/Y') }}</td>
                                <td class="px-4 py-3">{{ $expense->libelle }}</td>
                                <td class="px-4 py-3">{{ $expense->expenseType?->nom ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $sourceLabels->isNotEmpty() ? $sourceLabels->join(', ') : '—' }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-rose-700 dark:text-rose-400 tabular-nums">{{ $fmt($expense->montant) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-slate-500">Aucune dépense financée par caisse sur la période.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @elseif (auth()->user()->hasRole('super_admin') && ! $selectedParoisseId)
        <div class="adventiste-card-pro-static p-10 text-center text-slate-500 dark:text-slate-400">
            Sélectionnez une paroisse et une période pour calculer le rapport.
        </div>
    @endif
@endsection
