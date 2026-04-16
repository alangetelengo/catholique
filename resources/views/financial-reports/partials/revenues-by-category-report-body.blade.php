@php
    $fmt = static fn ($n) => \App\Helpers\ParoisseConfig::formatMontant($n);
    $w = $report['weekly'] ?? null;
    $joursLabels = [
        'lundi' => 'Lundi', 'mardi' => 'Mardi', 'mercredi' => 'Mercredi', 'jeudi' => 'Jeudi',
        'vendredi' => 'Vendredi', 'samedi' => 'Samedi', 'dimanche' => 'Dimanche',
    ];
    $showRptSemaine = $showRptSemaine ?? true;
    $showRptDimanche = $showRptDimanche ?? true;
    $rptTotalSubtitle = $rptTotalSubtitle ?? 'Semaine + dimanche';
    $rptSummaryCols = 1 + (int) $showRptSemaine + (int) $showRptDimanche;
@endphp

@if ($w)
    <div class="rounded-xl border border-sky-200/80 dark:border-sky-800/60 bg-sky-50/90 dark:bg-sky-950/30 px-4 py-3 mb-6 text-sm text-sky-900 dark:text-sky-100">
        <strong class="font-semibold">Période :</strong>
        {{ \Illuminate\Support\Carbon::parse($dateDebut)->format('d/m/Y') }} au {{ \Illuminate\Support\Carbon::parse($dateFin)->format('d/m/Y') }}
    </div>

    <div class="grid grid-cols-1 gap-4 mb-6 @if ($rptSummaryCols === 3) md:grid-cols-3 @else md:grid-cols-2 @endif">
        @if ($showRptSemaine)
            <div class="adventiste-card-pro-static p-4 border-t-4 border-t-sky-500">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Total semaine</p>
                <p class="mt-1 text-xl font-bold text-slate-900 dark:text-white">{{ $fmt($w['total_semaine']) }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Lundi – samedi</p>
            </div>
        @endif
        @if ($showRptDimanche)
            <div class="adventiste-card-pro-static p-4 border-t-4 border-t-emerald-500">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Total dimanche</p>
                <p class="mt-1 text-xl font-bold text-emerald-700 dark:text-emerald-400">{{ $fmt($w['total_dimanche']) }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Messe du dimanche</p>
            </div>
        @endif
        <div class="adventiste-card-pro-static p-4 border-t-4 border-t-amber-500">
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Total général</p>
            <p class="mt-1 text-xl font-bold text-amber-800 dark:text-amber-300">{{ $fmt($w['total_general']) }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $rptTotalSubtitle }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 mb-6 @if ($showRptSemaine && $showRptDimanche) lg:grid-cols-2 @endif">
        @if ($showRptSemaine)
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
        @endif

        @if ($showRptDimanche)
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
        @endif
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
                                <td class="px-4 py-3 text-right font-semibold text-emerald-700 dark:text-emerald-400">{{ $fmt($revenue->montant) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $selectedCategoryId ? 5 : 6 }}" class="px-4 py-12 text-center text-slate-500 dark:text-slate-400">Aucune recette sur cette période.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($w['revenues_all']->count() > 0)
                        <tfoot>
                            <tr class="bg-slate-100/90 dark:bg-slate-800/80 font-bold text-slate-900 dark:text-white">
                                <td class="px-4 py-3" colspan="{{ $selectedCategoryId ? 4 : 5 }}">Total général</td>
                                <td class="px-4 py-3 text-right">{{ $fmt($report['total_general']) }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
@endif

@if (! ($selectedCategoryId ?? null) && count($report['by_category']) > 0)
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
            @if ($selectedTypeId ?? null)
                <input type="hidden" name="revenue_type_id" value="{{ $selectedTypeId }}">
            @endif
            <button type="submit" class="adventiste-btn-primary">
                <i class="fas fa-save me-2" aria-hidden="true"></i>Enregistrer ce rapport
            </button>
        </form>
    </div>
@endcan
