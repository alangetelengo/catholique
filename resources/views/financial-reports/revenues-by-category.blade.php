@extends('layouts.app')

@section('title', 'Rapport par catégories de recettes — Catholique')
@section('page-title', 'Rapport par catégories de recettes')

@section('page-title-info')
    Filtrez par paroisse, période et catégorie : totaux <strong class="font-semibold">semaine (lun.–sam.)</strong> et <strong class="font-semibold">dimanche</strong>, détail par jour, liste avec période, puis répartition par catégorie.
    @if ($dateDebut && $dateFin)
        <span class="block mt-1 text-slate-500 dark:text-slate-400">
            Période affichée : {{ \Illuminate\Support\Carbon::parse($dateDebut)->format('d/m/Y') }} → {{ \Illuminate\Support\Carbon::parse($dateFin)->format('d/m/Y') }}
            · Édité le {{ now()->format('d/m/Y à H:i') }}
        </span>
    @endif
@endsection

@section('btn-create')
    <div class="flex flex-wrap items-center gap-2">
        @if ($report)
            <a href="{{ route('financial-reports.revenues-by-category.pdf', array_filter([
                'paroisse_id' => $selectedParoisseId,
                'date_debut' => $dateDebut,
                'date_fin' => $dateFin,
                'revenue_category_id' => $selectedCategoryId ?: null,
            ])) }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-lg border border-rose-200 dark:border-rose-800/60 bg-rose-50 dark:bg-rose-950/40 px-3 py-2 text-sm font-semibold text-rose-800 dark:text-rose-200 hover:bg-rose-100 dark:hover:bg-rose-900/50 transition-colors no-underline">
                <i class="fas fa-file-pdf" aria-hidden="true"></i>Exporter PDF
            </a>
        @endif
        <a href="{{ route('financial-reports.index') }}" class="adventiste-btn-secondary text-sm no-underline">
            <i class="fas fa-chart-pie me-1.5" aria-hidden="true"></i>Hub rapports
        </a>
        <a href="{{ route('financial-reports.list') }}" class="adventiste-btn-secondary text-sm no-underline">Rapports enregistrés</a>
    </div>
@endsection

@section('content')
    @php
        $fmt = static fn ($n) => \App\Helpers\ParoisseConfig::formatMontant($n);
        $today = now()->format('Y-m-d');
        $debMois = now()->startOfMonth()->format('Y-m-d');
        $finMois = now()->endOfMonth()->format('Y-m-d');
        $debSem = now()->startOfWeek()->format('Y-m-d');
        $finSem = now()->endOfWeek()->format('Y-m-d');
        $baseParams = array_filter(request()->only(['revenue_category_id', 'paroisse_id']), fn ($v) => $v !== null && $v !== '');
    @endphp

    <div class="rounded-xl border border-sky-200/90 dark:border-sky-800/50 bg-sky-50/90 dark:bg-sky-950/25 px-4 py-3 mb-6 text-sm text-sky-950 dark:text-sky-100 leading-relaxed">
        <p class="m-0 flex gap-2">
            <i class="fas fa-info-circle mt-0.5 shrink-0 text-sky-600 dark:text-sky-400" aria-hidden="true"></i>
            <span>Les montants regroupent les recettes de la paroisse sur l’intervalle choisi. Utilisez les <strong class="font-semibold">raccourcis</strong> (aujourd’hui, semaine, mois) pour remplir rapidement les dates.</span>
        </p>
    </div>

    <div class="adventiste-card-pro-static p-4 sm:p-5 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4 pb-4 border-b border-slate-200/80 dark:border-slate-600/80">
            <h2 class="text-base font-semibold text-slate-900 dark:text-white m-0 flex items-center gap-2">
                <i class="fas fa-filter text-emerald-600 dark:text-emerald-400" aria-hidden="true"></i>
                Filtres
            </h2>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('financial-reports.revenues-by-category', array_merge($baseParams, ['date_debut' => $today, 'date_fin' => $today])) }}" class="adventiste-btn-secondary text-xs py-2 px-3 no-underline">Aujourd’hui</a>
                <a href="{{ route('financial-reports.revenues-by-category', array_merge($baseParams, ['date_debut' => $debSem, 'date_fin' => $finSem])) }}" class="adventiste-btn-secondary text-xs py-2 px-3 no-underline">Semaine</a>
                <a href="{{ route('financial-reports.revenues-by-category', array_merge($baseParams, ['date_debut' => $debMois, 'date_fin' => $finMois])) }}" class="adventiste-btn-secondary text-xs py-2 px-3 no-underline">Mois en cours</a>
            </div>
        </div>

        <form method="GET" action="{{ route('financial-reports.revenues-by-category') }}" id="filterForm" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-4 items-end">
            @if (auth()->user()->hasRole('super_admin') && $paroisses->count() > 0)
                <div class="lg:col-span-3">
                    <label for="rbc_paroisse" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Paroisse <span class="text-red-500">*</span></label>
                    <select id="rbc_paroisse" name="paroisse_id" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm" required>
                        <option value="">Sélectionner…</option>
                        @foreach ($paroisses as $p)
                            <option value="{{ $p->id }}" @selected($selectedParoisseId == $p->id)>{{ $p->nom }}</option>
                        @endforeach
                    </select>
                </div>
            @else
                <input type="hidden" name="paroisse_id" value="{{ auth()->user()->paroisse_id }}">
            @endif

            <div class="lg:col-span-2">
                <label for="rbc_date_debut" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Date début</label>
                <input type="date" id="rbc_date_debut" name="date_debut" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm" value="{{ $dateDebut ?? $debMois }}">
            </div>
            <div class="lg:col-span-2">
                <label for="rbc_date_fin" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Date fin</label>
                <input type="date" id="rbc_date_fin" name="date_fin" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm" value="{{ $dateFin ?? $finMois }}">
            </div>
            <div class="lg:col-span-3">
                <label for="rbc_category" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Catégorie</label>
                <select id="rbc_category" name="revenue_category_id" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm">
                    <option value="">Toutes les catégories</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" @selected($selectedCategoryId == $cat->id)>{{ $cat->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="lg:col-span-2 flex flex-wrap gap-2">
                <button type="submit" class="adventiste-btn-primary">
                    <i class="fas fa-calculator me-2" aria-hidden="true"></i>Calculer
                </button>
                <a href="{{ route('financial-reports.revenues-by-category') }}" class="adventiste-btn-secondary no-underline inline-flex items-center">Réinitialiser</a>
            </div>
        </form>
    </div>

    @if ($report)
        @php
            $w = $report['weekly'] ?? null;
            $joursLabels = [
                'lundi' => 'Lundi', 'mardi' => 'Mardi', 'mercredi' => 'Mercredi', 'jeudi' => 'Jeudi',
                'vendredi' => 'Vendredi', 'samedi' => 'Samedi', 'dimanche' => 'Dimanche',
            ];
        @endphp

        @if ($w)
            <div class="rounded-xl border border-sky-200/80 dark:border-sky-800/60 bg-sky-50/90 dark:bg-sky-950/30 px-4 py-3 mb-6 text-sm text-sky-900 dark:text-sky-100">
                <strong class="font-semibold">Période :</strong>
                {{ \Illuminate\Support\Carbon::parse($dateDebut)->format('d/m/Y') }} au {{ \Illuminate\Support\Carbon::parse($dateFin)->format('d/m/Y') }}
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="adventiste-card-pro-static p-4 border-t-4 border-t-sky-500">
                    <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Total semaine</p>
                    <p class="mt-1 text-xl font-bold text-slate-900 dark:text-white">{{ $fmt($w['total_semaine']) }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Lundi – samedi</p>
                </div>
                <div class="adventiste-card-pro-static p-4 border-t-4 border-t-emerald-500">
                    <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Total dimanche</p>
                    <p class="mt-1 text-xl font-bold text-emerald-700 dark:text-emerald-400">{{ $fmt($w['total_dimanche']) }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Messe du dimanche</p>
                </div>
                <div class="adventiste-card-pro-static p-4 border-t-4 border-t-amber-500">
                    <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Total général</p>
                    <p class="mt-1 text-xl font-bold text-amber-800 dark:text-amber-300">{{ $fmt($w['total_general']) }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Semaine + dimanche</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <div class="adventiste-card-pro-static overflow-hidden">
                    <div class="px-4 py-3 border-b border-slate-200/80 dark:border-slate-600/80 bg-slate-50/80 dark:bg-slate-800/50">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white m-0">Détails de la semaine (lundi – samedi)</h3>
                    </div>
                    <div class="overflow-x-auto p-2">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left text-slate-700 dark:text-slate-200 border-b border-slate-200 dark:border-slate-600">
                                    <th class="px-3 py-2 font-semibold">Jour</th>
                                    <th class="px-3 py-2 font-semibold text-right">Montant</th>
                                    <th class="px-3 py-2 font-semibold text-center">Nb</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                                @foreach (['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'] as $jour)
                                    <tr class="text-slate-700 dark:text-slate-200">
                                        <td class="px-3 py-2">{{ $joursLabels[$jour] }}</td>
                                        <td class="px-3 py-2 text-right font-medium">{{ $fmt($w['details_semaine'][$jour]['montant'] ?? 0) }}</td>
                                        <td class="px-3 py-2 text-center">{{ $w['details_semaine'][$jour]['count'] ?? 0 }}</td>
                                    </tr>
                                @endforeach
                                <tr class="bg-sky-50/80 dark:bg-sky-950/40 font-semibold text-slate-900 dark:text-white">
                                    <td class="px-3 py-2">Total semaine</td>
                                    <td class="px-3 py-2 text-right">{{ $fmt($w['total_semaine']) }}</td>
                                    <td class="px-3 py-2 text-center">{{ $w['revenues_semaine']->count() }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="adventiste-card-pro-static overflow-hidden">
                    <div class="px-4 py-3 border-b border-slate-200/80 dark:border-slate-600/80 bg-slate-50/80 dark:bg-slate-800/50">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white m-0">Détails du dimanche</h3>
                    </div>
                    <div class="overflow-x-auto p-2">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left text-slate-700 dark:text-slate-200 border-b border-slate-200 dark:border-slate-600">
                                    <th class="px-3 py-2 font-semibold">Jour</th>
                                    <th class="px-3 py-2 font-semibold text-right">Montant</th>
                                    <th class="px-3 py-2 font-semibold text-center">Nb</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                                <tr class="text-slate-700 dark:text-slate-200">
                                    <td class="px-3 py-2">Dimanche</td>
                                    <td class="px-3 py-2 text-right font-medium">{{ $fmt($w['total_dimanche']) }}</td>
                                    <td class="px-3 py-2 text-center">{{ $w['details_dimanche']['count'] }}</td>
                                </tr>
                                <tr class="bg-emerald-50/80 dark:bg-emerald-950/40 font-semibold text-slate-900 dark:text-white">
                                    <td class="px-3 py-2">Total dimanche</td>
                                    <td class="px-3 py-2 text-right">{{ $fmt($w['total_dimanche']) }}</td>
                                    <td class="px-3 py-2 text-center">{{ $w['details_dimanche']['count'] }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

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
                                    <th class="px-4 py-3 font-semibold">Jour</th>
                                    <th class="px-4 py-3 font-semibold">Période</th>
                                    @if (! $selectedCategoryId)
                                        <th class="px-4 py-3 font-semibold">Catégorie</th>
                                    @endif
                                    <th class="px-4 py-3 font-semibold">Méthode</th>
                                    <th class="px-4 py-3 font-semibold">Référence</th>
                                    <th class="px-4 py-3 font-semibold text-right">Montant</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                                @forelse ($w['revenues_all'] as $revenue)
                                    @php
                                        $isSemaine = $revenue->periode_messe === 'semaine' || ($revenue->jour_semaine && in_array($revenue->jour_semaine, ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'], true));
                                        $isDimanche = $revenue->periode_messe === 'dimanche' || $revenue->jour_semaine === 'dimanche';
                                    @endphp
                                    <tr class="text-slate-700 dark:text-slate-200">
                                        <td class="px-4 py-3 whitespace-nowrap">{{ $revenue->date_recette?->format('d/m/Y') }}</td>
                                        <td class="px-4 py-3">{{ $joursLabels[$revenue->jour_semaine] ?? '—' }}</td>
                                        <td class="px-4 py-3">
                                            @if ($isSemaine)
                                                <span class="inline-flex items-center rounded-full bg-sky-100 dark:bg-sky-900/40 px-2.5 py-1 text-xs font-semibold text-sky-800 dark:text-sky-200">Semaine</span>
                                            @elseif ($isDimanche)
                                                <span class="inline-flex items-center rounded-full bg-emerald-100 dark:bg-emerald-900/40 px-2.5 py-1 text-xs font-semibold text-emerald-800 dark:text-emerald-200">Dimanche</span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-700 px-2.5 py-1 text-xs font-semibold text-slate-700 dark:text-slate-200">Autre</span>
                                            @endif
                                        </td>
                                        @if (! $selectedCategoryId)
                                            <td class="px-4 py-3">{{ $revenue->category?->nom ?? '—' }}</td>
                                        @endif
                                        <td class="px-4 py-3">{{ $revenue->methode_paiement ? ucfirst(str_replace('_', ' ', (string) $revenue->methode_paiement)) : '—' }}</td>
                                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300 break-all max-w-[12rem]">{{ $revenue->reference_paiement ?? '—' }}</td>
                                        <td class="px-4 py-3 text-right font-semibold text-emerald-700 dark:text-emerald-400">{{ $fmt($revenue->montant) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $selectedCategoryId ? 6 : 7 }}" class="px-4 py-12 text-center text-slate-500 dark:text-slate-400">Aucune recette sur cette période.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if ($w['revenues_all']->count() > 0)
                                <tfoot>
                                    <tr class="bg-slate-100/90 dark:bg-slate-800/80 font-bold text-slate-900 dark:text-white">
                                        <td class="px-4 py-3" colspan="{{ $selectedCategoryId ? 5 : 6 }}">Total général</td>
                                        <td class="px-4 py-3 text-right">{{ $fmt($report['total_general']) }}</td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        @endif

        @if (count($report['by_category']) > 0)
            <div class="adventiste-card-pro-static overflow-hidden mb-6">
                <div class="px-4 py-3 border-b border-slate-200/80 dark:border-slate-600/80 bg-slate-50/80 dark:bg-slate-800/50">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white m-0 flex items-center gap-2">
                        <i class="fas fa-layer-group text-slate-500 dark:text-slate-400" aria-hidden="true"></i>
                        Répartition par catégorie
                    </h3>
                </div>
                <div class="adventiste-table-shell border-0 rounded-none shadow-none">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left text-slate-700 dark:text-slate-200">
                                    <th class="px-4 py-3 font-semibold">Catégorie</th>
                                    <th class="px-4 py-3 font-semibold text-center">Nb recettes</th>
                                    <th class="px-4 py-3 font-semibold text-right">Montant</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                                @foreach ($report['by_category'] as $cat)
                                    <tr class="text-slate-700 dark:text-slate-200">
                                        <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">{{ $cat['nom'] }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="inline-flex rounded-full bg-slate-100 dark:bg-slate-700 px-2.5 py-0.5 text-xs font-medium">{{ $cat['count'] }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-right font-semibold text-emerald-700 dark:text-emerald-400">{{ $fmt($cat['montant']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-slate-50/90 dark:bg-slate-800/50 font-bold text-slate-900 dark:text-slate-100">
                                    <td class="px-4 py-3">TOTAL</td>
                                    <td class="px-4 py-3 text-center">{{ $report['revenues']->count() }}</td>
                                    <td class="px-4 py-3 text-right">{{ $fmt($report['total_general']) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        @can('generate_financial_reports')
            <div class="flex justify-end">
                <form action="{{ route('financial-reports.revenues-by-category.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="paroisse_id" value="{{ $selectedParoisseId }}">
                    <input type="hidden" name="date_debut" value="{{ $dateDebut }}">
                    <input type="hidden" name="date_fin" value="{{ $dateFin }}">
                    @if ($selectedCategoryId)
                        <input type="hidden" name="revenue_category_id" value="{{ $selectedCategoryId }}">
                    @endif
                    <button type="submit" class="adventiste-btn-primary">
                        <i class="fas fa-save me-2" aria-hidden="true"></i>Enregistrer ce rapport
                    </button>
                </form>
            </div>
        @endcan
    @else
        <div class="adventiste-card-pro-static p-10 text-center">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400">
                <i class="fas fa-chart-pie text-xl" aria-hidden="true"></i>
            </div>
            <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-100 m-0">Paramétrez le rapport</h3>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400 max-w-lg mx-auto m-0">
                @if (auth()->user()->hasRole('super_admin'))
                    Choisissez une <strong class="font-medium">paroisse</strong>, des dates de début et fin, puis cliquez sur <strong class="font-medium">Calculer</strong>.
                @else
                    Choisissez la période (et éventuellement une catégorie), puis cliquez sur <strong class="font-medium">Calculer</strong>.
                @endif
            </p>
        </div>
    @endif
@endsection
