@php
    $fmt = static fn ($n) => \App\Helpers\ParoisseConfig::formatMontant($n);
    $payLabel = static fn ($v) => ucfirst(str_replace('_', ' ', (string) $v));
    $selectedCategory = ! empty($selectedCategoryId)
        ? \App\Models\RevenueCategory::find($selectedCategoryId)
        : null;
    $selectedType = ! empty($selectedTypeId)
        ? \App\Models\RevenueType::find($selectedTypeId)
        : null;
    $isSubventionCategory = false;
    $envelopes = collect();
    $w = $report['weekly'] ?? null;
    $showWeeklyBreakdown = ($showWeeklyBreakdown ?? false) && $w;
    $showRptSemaine = $showRptSemaine ?? true;
    $showRptDimanche = $showRptDimanche ?? true;
    $rptTotalSubtitle = $rptTotalSubtitle ?? 'Semaine + dimanche';
    $joursLabels = [
        'lundi' => 'Lundi', 'mardi' => 'Mardi', 'mercredi' => 'Mercredi', 'jeudi' => 'Jeudi',
        'vendredi' => 'Vendredi', 'samedi' => 'Samedi', 'dimanche' => 'Dimanche',
    ];
    $hideStoreForm = $hideStoreForm ?? false;
@endphp

<div class="rounded-xl border border-sky-200/80 dark:border-sky-800/60 bg-sky-50/90 dark:bg-sky-950/30 px-4 py-3 mb-5 text-sm text-sky-900 dark:text-sky-100">
    <strong class="font-semibold">Période :</strong>
    {{ \Illuminate\Support\Carbon::parse($dateDebut)->format('d/m/Y') }} → {{ \Illuminate\Support\Carbon::parse($dateFin)->format('d/m/Y') }}
    @if ($selectedCategory)
        <span class="block mt-1 text-slate-600 dark:text-slate-300">
            {{ $selectedCategory->nom }}
            @if ($selectedType)
                · <strong class="font-medium text-slate-800 dark:text-slate-100">{{ $selectedType->nom }}</strong>
            @endif
        </span>
    @endif
</div>

@if ($isSubventionCategory && $envelopes->isNotEmpty())
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="adventiste-card-pro-static p-4 border-t-4 border-t-emerald-500">
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Recettes subvention (historique)</p>
            <p class="mt-1 text-2xl font-bold text-emerald-700 dark:text-emerald-400 tabular-nums">{{ $fmt($envelopes->sum('montant')) }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $envelopes->count() }} enveloppe(s)</p>
        </div>
        <div class="adventiste-card-pro-static p-4 border-t-4 border-t-sky-500">
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Recettes (période)</p>
            <p class="mt-1 text-2xl font-bold text-sky-800 dark:text-sky-300 tabular-nums">{{ $fmt($report['total_general']) }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $report['revenues']->count() }} ligne(s)</p>
        </div>
        <div class="adventiste-card-pro-static p-4 border-t-4 border-t-violet-500">
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Types distincts</p>
            <p class="mt-1 text-2xl font-bold text-violet-800 dark:text-violet-300 tabular-nums">{{ $envelopes->pluck('type_id')->unique()->count() }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">ventilation mensuelle</p>
        </div>
    </div>

    @if ($envelopes->count() > 1)
        <div class="adventiste-card-pro-static overflow-hidden mb-6">
            <div class="px-4 py-3 border-b border-slate-200/80 dark:border-slate-600/80 bg-slate-50/80 dark:bg-slate-800/50">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white m-0">Détail par mois concerné</h3>
            </div>
            <div class="overflow-x-auto p-2">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-700 dark:text-slate-200 border-b border-slate-200 dark:border-slate-600">
                            <th class="px-3 py-2 font-semibold">Type</th>
                            <th class="px-3 py-2 font-semibold">Mois</th>
                            <th class="px-3 py-2 font-semibold text-right">Montant</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                        @foreach ($envelopes as $row)
                            <tr class="text-slate-700 dark:text-slate-200">
                                <td class="px-3 py-2">{{ $row['type_nom'] }}</td>
                                <td class="px-3 py-2 font-medium">{{ $row['mois_label'] }}</td>
                                <td class="px-3 py-2 text-right font-semibold text-emerald-700 dark:text-emerald-400 tabular-nums">{{ $fmt($row['montant']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @elseif ($envelopes->count() === 1)
        @php $env = $envelopes->first(); @endphp
        <p class="text-sm text-slate-600 dark:text-slate-400 mb-6">
            Enveloppe : <strong class="text-slate-800 dark:text-slate-200">{{ $env['label'] }}</strong>
        </p>
    @endif
@elseif ($showWeeklyBreakdown)
    @php
        $rptSummaryCols = 1 + (int) $showRptSemaine + (int) $showRptDimanche;
    @endphp
    <div class="grid grid-cols-1 gap-4 mb-6 @if ($rptSummaryCols === 3) sm:grid-cols-3 @else sm:grid-cols-2 @endif">
        @if ($showRptSemaine)
            <div class="adventiste-card-pro-static p-4 border-t-4 border-t-sky-500">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Total semaine</p>
                <p class="mt-1 text-2xl font-bold text-sky-800 dark:text-sky-300 tabular-nums">{{ $fmt($w['total_semaine']) }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Lundi – samedi</p>
            </div>
        @endif
        @if ($showRptDimanche)
            <div class="adventiste-card-pro-static p-4 border-t-4 border-t-emerald-500">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Total dimanche</p>
                <p class="mt-1 text-2xl font-bold text-emerald-700 dark:text-emerald-400 tabular-nums">{{ $fmt($w['total_dimanche']) }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Messe du dimanche</p>
            </div>
        @endif
        <div class="adventiste-card-pro-static p-4 border-t-4 border-t-amber-500">
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Total recettes</p>
            <p class="mt-1 text-2xl font-bold text-amber-800 dark:text-amber-300 tabular-nums">{{ $fmt($report['total_general']) }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $rptTotalSubtitle }}</p>
        </div>
    </div>

    @if ($showRptSemaine)
        <div class="adventiste-card-pro-static overflow-hidden mb-6">
            <div class="px-4 py-3 border-b border-slate-200/80 dark:border-slate-600/80 bg-slate-50/80 dark:bg-slate-800/50">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white m-0">Semaine (lundi – samedi)</h3>
            </div>
            <div class="overflow-x-auto p-2">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-700 dark:text-slate-200 border-b border-slate-200 dark:border-slate-600">
                            <th class="px-3 py-2 font-semibold">Jour</th>
                            <th class="px-3 py-2 font-semibold text-right">Montant</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                        @foreach (['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'] as $jour)
                            <tr class="text-slate-700 dark:text-slate-200">
                                <td class="px-3 py-2">{{ $joursLabels[$jour] }}</td>
                                <td class="px-3 py-2 text-right font-medium tabular-nums">{{ $fmt($w['details_semaine'][$jour]['montant'] ?? 0) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@else
    <div class="adventiste-card-pro-static p-4 border-t-4 border-t-emerald-500 mb-6">
        <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Total des recettes</p>
        <p class="mt-1 text-2xl font-bold text-emerald-700 dark:text-emerald-400 tabular-nums">{{ $fmt($report['total_general']) }}</p>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $report['revenues']->count() }} ligne(s) validée(s)</p>
    </div>

    @if (! $selectedCategoryId && count($report['by_category']) > 0)
        <div class="adventiste-card-pro-static overflow-hidden mb-6">
            <div class="px-4 py-3 border-b border-slate-200/80 dark:border-slate-600/80 bg-slate-50/80 dark:bg-slate-800/50">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white m-0">Par catégorie</h3>
            </div>
            <div class="overflow-x-auto p-2">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-700 dark:text-slate-200 border-b border-slate-200 dark:border-slate-600">
                            <th class="px-3 py-2 font-semibold">Catégorie</th>
                            <th class="px-3 py-2 font-semibold text-right">Montant</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                        @foreach ($report['by_category'] as $row)
                            <tr class="text-slate-700 dark:text-slate-200">
                                <td class="px-3 py-2">{{ $row['nom'] }}</td>
                                <td class="px-3 py-2 text-right font-medium tabular-nums">{{ $fmt($row['montant']) }}</td>
                            </tr>
                        @endforeach
                        <tr class="bg-slate-50/90 dark:bg-slate-800/50 font-semibold">
                            <td class="px-3 py-2">Total</td>
                            <td class="px-3 py-2 text-right tabular-nums">{{ $fmt($report['total_general']) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($selectedCategoryId && count($report['by_type']) > 0)
        <div class="adventiste-card-pro-static overflow-hidden mb-6">
            <div class="px-4 py-3 border-b border-slate-200/80 dark:border-slate-600/80 bg-slate-50/80 dark:bg-slate-800/50">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white m-0">Par type</h3>
            </div>
            <div class="overflow-x-auto p-2">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-700 dark:text-slate-200 border-b border-slate-200 dark:border-slate-600">
                            <th class="px-3 py-2 font-semibold">Type</th>
                            <th class="px-3 py-2 font-semibold text-right">Montant</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                        @foreach ($report['by_type'] as $row)
                            <tr class="text-slate-700 dark:text-slate-200">
                                <td class="px-3 py-2">{{ $row['nom'] }}</td>
                                <td class="px-3 py-2 text-right font-medium tabular-nums">{{ $fmt($row['montant']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endif

@if ($report['revenues']->count() > 0)
    <div class="mb-6">
        <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-3">
            Détail des recettes
            <span class="font-normal text-slate-500 dark:text-slate-400">({{ $report['revenues']->count() }})</span>
        </h3>

        <div class="space-y-3">
            @foreach ($report['revenues'] as $revenue)
                <div class="adventiste-card-pro-static p-4">
                    <div class="flex items-start justify-between gap-3 mb-2">
                        <span class="text-sm font-semibold text-slate-800 dark:text-slate-100 whitespace-nowrap">
                            {{ $revenue->date_recette?->format('d/m/Y') }}
                        </span>
                        <span class="text-base font-bold text-emerald-700 dark:text-emerald-400 tabular-nums whitespace-nowrap">
                            {{ $fmt((float) $revenue->montant) }}
                        </span>
                    </div>
                    @if ($revenue->type)
                        <p class="text-xs text-emerald-700 dark:text-emerald-400 mb-0.5">
                            @if ($revenue->mois_capital)
                                {{ $revenue->type->nom }} — {{ \App\Support\SubventionMensuelle::formatMoisCapital($revenue->mois_capital) }}
                            @else
                                {{ $revenue->type->nom }}
                            @endif
                        </p>
                    @endif
                    @if (! $selectedCategoryId && $revenue->category)
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $revenue->category->nom }}</p>
                    @endif
                    @if ($revenue->jour_semaine)
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $joursLabels[$revenue->jour_semaine] ?? $revenue->jour_semaine }}</p>
                    @endif
                    @if ($revenue->methode_paiement)
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $payLabel($revenue->methode_paiement) }}</p>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="adventiste-card-pro-static p-4 mt-4 bg-slate-50/90 dark:bg-slate-800/50">
            <div class="flex items-center justify-between font-bold text-slate-900 dark:text-white">
                <span>Total des recettes</span>
                <span class="text-emerald-700 dark:text-emerald-400 tabular-nums">{{ $fmt($report['total_general']) }}</span>
            </div>
        </div>
    </div>
@else
    <div class="adventiste-card-pro-static p-8 text-center text-sm text-slate-500 dark:text-slate-400 mb-6">
        Aucune recette validée pour ces critères.
    </div>
@endif

@if (! $hideStoreForm)
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
@endif
