@php
    $fmt = static fn ($n) => \App\Helpers\ParoisseConfig::formatMontant($n);
    $selectedCategory = null;
    if (!empty($selectedRevenueCategoryId)) {
        $selectedCategory = \App\Models\RevenueCategory::find($selectedRevenueCategoryId);
    }
    $selectedType = null;
    if (!empty($selectedRevenueTypeId)) {
        $selectedType = \App\Models\RevenueType::find($selectedRevenueTypeId);
    }
@endphp

<div class="rounded-xl border border-sky-200/80 dark:border-sky-800/60 bg-sky-50/90 dark:bg-sky-950/30 px-4 py-3 mb-6 text-sm text-sky-900 dark:text-sky-100">
    <strong class="font-semibold">Période :</strong>
    {{ \Illuminate\Support\Carbon::parse($dateDebut)->format('d/m/Y') }} au {{ \Illuminate\Support\Carbon::parse($dateFin)->format('d/m/Y') }}
    @if ($selectedCategory)
        <span class="block mt-1 text-slate-600 dark:text-slate-300">
            Filtre catégorie :
            <strong class="font-medium text-slate-800 dark:text-slate-100">{{ $selectedCategory->nom }}</strong>
            @if ($selectedType)
                — type : <strong class="font-medium text-slate-800 dark:text-slate-100">{{ $selectedType->nom }}</strong>
            @endif
        </span>
    @endif
</div>

<div class="adventiste-card-pro-static p-4 border-t-4 border-t-rose-500 mb-6">
    <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Total dépenses</p>
    <p class="mt-1 text-xl font-bold text-rose-700 dark:text-rose-400 tabular-nums">{{ $fmt($report['total_general']) }}</p>
    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Dépenses au statut « validé » sur la période</p>
</div>

@if (! $selectedRevenueCategoryId && count($report['by_category']) > 0)
    <div class="adventiste-card-pro-static overflow-hidden mb-6">
        <div class="px-4 py-3 border-b border-slate-200/80 dark:border-slate-600/80 bg-slate-50/80 dark:bg-slate-800/50">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white m-0">Répartition par catégorie (source des fonds)</h3>
        </div>
        <div class="overflow-x-auto p-2">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-700 dark:text-slate-200 border-b border-slate-200 dark:border-slate-600">
                        <th class="px-3 py-2 font-semibold">Catégorie</th>
                        <th class="px-3 py-2 font-semibold text-right">Montant</th>
                        <th class="px-3 py-2 font-semibold text-center">Nb</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                    @foreach ($report['by_category'] as $row)
                        <tr class="text-slate-700 dark:text-slate-200">
                            <td class="px-3 py-2">{{ $row['nom'] }}</td>
                            <td class="px-3 py-2 text-right font-medium tabular-nums">{{ $fmt($row['montant']) }}</td>
                            <td class="px-3 py-2 text-center">{{ $row['count'] }}</td>
                        </tr>
                    @endforeach
                    <tr class="bg-rose-50/80 dark:bg-rose-950/40 font-semibold text-slate-900 dark:text-white">
                        <td class="px-3 py-2">Total</td>
                        <td class="px-3 py-2 text-right tabular-nums">{{ $fmt($report['total_general']) }}</td>
                        <td class="px-3 py-2 text-center">{{ $report['expenses']->count() }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endif

@if ($selectedRevenueCategoryId && count($report['by_type']) > 0)
    <div class="adventiste-card-pro-static overflow-hidden mb-6">
        <div class="px-4 py-3 border-b border-slate-200/80 dark:border-slate-600/80 bg-slate-50/80 dark:bg-slate-800/50">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white m-0">Répartition par type (précision de la source)</h3>
        </div>
        <div class="overflow-x-auto p-2">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-700 dark:text-slate-200 border-b border-slate-200 dark:border-slate-600">
                        <th class="px-3 py-2 font-semibold">Type</th>
                        <th class="px-3 py-2 font-semibold text-right">Montant</th>
                        <th class="px-3 py-2 font-semibold text-center">Nb</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                    @foreach ($report['by_type'] as $row)
                        <tr class="text-slate-700 dark:text-slate-200">
                            <td class="px-3 py-2">{{ $row['nom'] }}</td>
                            <td class="px-3 py-2 text-right font-medium tabular-nums">{{ $fmt($row['montant']) }}</td>
                            <td class="px-3 py-2 text-center">{{ $row['count'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

@if ($report['expenses']->count() > 0)
    <div class="adventiste-card-pro-static overflow-hidden mb-6">
        <div class="px-4 py-3 border-b border-slate-200/80 dark:border-slate-600/80 bg-slate-50/80 dark:bg-slate-800/50">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white m-0">Liste détaillée</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 m-0 mt-1">{{ $report['expenses']->count() }} ligne(s)</p>
        </div>
        <div class="adventiste-table-shell border-0 rounded-none shadow-none">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-700 dark:text-slate-200">
                            <th class="px-4 py-3 font-semibold">Date</th>
                            <th class="px-4 py-3 font-semibold">Catégorie</th>
                            <th class="px-4 py-3 font-semibold">Type</th>
                            <th class="px-4 py-3 font-semibold">Libellé</th>
                            <th class="px-4 py-3 font-semibold">Fournisseur</th>
                            <th class="px-4 py-3 font-semibold text-right">Montant</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                        @foreach ($report['expenses'] as $ex)
                            <tr class="text-slate-700 dark:text-slate-200">
                                <td class="px-4 py-3 whitespace-nowrap">{{ $ex->date_depense?->format('d/m/Y') }}</td>
                                <td class="px-4 py-3">{{ $ex->revenueCategory?->nom ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    @php
                                        $sourceLabels = $ex->fundingSources
                                            ? $ex->fundingSources
                                                ->map(fn ($source) => $source->revenueType?->nom)
                                                ->filter()
                                                ->values()
                                            : collect();
                                    @endphp
                                    {{ $sourceLabels->isNotEmpty() ? $sourceLabels->join(', ') : ($ex->revenueType?->nom ?? '—') }}
                                </td>
                                <td class="px-4 py-3">{{ $ex->libelle ?: '—' }}</td>
                                <td class="px-4 py-3">{{ $ex->fournisseur ?? '—' }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-rose-700 dark:text-rose-400 tabular-nums">{{ $fmt($ex->montant) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-50/90 dark:bg-slate-800/50 font-bold text-slate-900 dark:text-slate-100">
                            <td class="px-4 py-3 text-right" colspan="5">Total</td>
                            <td class="px-4 py-3 text-right tabular-nums">{{ $fmt($report['total_general']) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@else
    <div class="adventiste-card-pro-static p-8 text-center text-sm text-slate-500 dark:text-slate-400 mb-6">
        Aucune dépense validée pour ces critères.
    </div>
@endif

@include('financial-reports.partials.report-signataires-block')
