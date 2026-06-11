@php
    $fmt = static fn ($n) => \App\Helpers\ParoisseConfig::formatMontant($n);
    $selectedCategory = ! empty($selectedRevenueCategoryId)
        ? \App\Models\RevenueCategory::find($selectedRevenueCategoryId)
        : null;
    $selectedType = ! empty($selectedRevenueTypeId)
        ? \App\Models\RevenueType::find($selectedRevenueTypeId)
        : null;
    $isSubventionCategory = $selectedCategory && $selectedCategory->code === \App\Support\SubventionMensuelle::CATEGORY_CODE;
    $envelopes = collect($report['subvention_envelopes'] ?? []);
    $totalSubventionRecue = (float) $envelopes->sum('subvention_recue');
    $totalSubventionDepenses = (float) $envelopes->sum('depenses');
    $totalSubventionSolde = (float) $envelopes->sum('solde');
    $fundingSourceLabel = static function ($source): string {
        $type = $source->revenueType;
        if (! $type) {
            return '—';
        }
        if ($source->revenue?->mois_subvention) {
            return \App\Support\SubventionMensuelle::envelopeLabel($type, $source->revenue->mois_subvention);
        }

        return $type->nom;
    };
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
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Subvention reçue</p>
            <p class="mt-1 text-2xl font-bold text-emerald-700 dark:text-emerald-400 tabular-nums">{{ $fmt($totalSubventionRecue) }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $envelopes->count() }} enveloppe(s)</p>
        </div>
        <div class="adventiste-card-pro-static p-4 border-t-4 border-t-rose-500">
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Dépensé (période)</p>
            <p class="mt-1 text-2xl font-bold text-rose-700 dark:text-rose-400 tabular-nums">{{ $fmt($totalSubventionDepenses) }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $report['expenses']->count() }} opération(s)</p>
        </div>
        <div class="adventiste-card-pro-static p-4 border-t-4 border-t-sky-500">
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Solde restant</p>
            <p class="mt-1 text-2xl font-bold text-sky-800 dark:text-sky-300 tabular-nums">{{ $fmt($totalSubventionSolde) }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">recettes − dépenses</p>
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
                            <th class="px-3 py-2 font-semibold text-right">Reçu</th>
                            <th class="px-3 py-2 font-semibold text-right">Dépensé</th>
                            <th class="px-3 py-2 font-semibold text-right">Solde</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                        @foreach ($envelopes as $row)
                            <tr class="text-slate-700 dark:text-slate-200">
                                <td class="px-3 py-2">{{ $row['type_nom'] }}</td>
                                <td class="px-3 py-2 font-medium">{{ $row['mois_label'] }}</td>
                                <td class="px-3 py-2 text-right text-emerald-700 dark:text-emerald-400 tabular-nums">{{ $fmt($row['subvention_recue']) }}</td>
                                <td class="px-3 py-2 text-right text-rose-700 dark:text-rose-400 tabular-nums">{{ $fmt($row['depenses']) }}</td>
                                <td class="px-3 py-2 text-right font-semibold tabular-nums">{{ $fmt($row['solde']) }}</td>
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
@else
    <div class="adventiste-card-pro-static p-4 border-t-4 border-t-rose-500 mb-6">
        <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Total des dépenses</p>
        <p class="mt-1 text-2xl font-bold text-rose-700 dark:text-rose-400 tabular-nums">{{ $fmt($report['total_general']) }}</p>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $report['expenses']->count() }} ligne(s) validée(s)</p>
    </div>

    @if (! $selectedRevenueCategoryId && count($report['by_category']) > 0)
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

    @if ($selectedRevenueCategoryId && count($report['by_type']) > 0)
        <div class="adventiste-card-pro-static overflow-hidden mb-6">
            <div class="px-4 py-3 border-b border-slate-200/80 dark:border-slate-600/80 bg-slate-50/80 dark:bg-slate-800/50">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white m-0">Par type de source</h3>
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

@if ($report['expenses']->count() > 0)
    <div class="mb-6">
        <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-3">
            Détail des opérations
            <span class="font-normal text-slate-500 dark:text-slate-400">({{ $report['expenses']->count() }})</span>
        </h3>

        <div class="space-y-3">
            @foreach ($report['expenses'] as $ex)
                <div class="adventiste-card-pro-static p-4">
                    <div class="flex items-start justify-between gap-3 mb-2">
                        <span class="text-sm font-semibold text-slate-800 dark:text-slate-100 whitespace-nowrap">
                            {{ $ex->date_depense?->format('d/m/Y') }}
                        </span>
                        <span class="text-base font-bold text-rose-700 dark:text-rose-400 tabular-nums whitespace-nowrap">
                            {{ $fmt((float) $ex->montant) }}
                        </span>
                    </div>
                    @if ($ex->fundingSources->isNotEmpty())
                        @foreach ($ex->fundingSources as $source)
                            <p class="text-xs text-emerald-700 dark:text-emerald-400 mb-0.5">{{ $fundingSourceLabel($source) }}</p>
                        @endforeach
                    @elseif ($ex->revenueType)
                        <p class="text-xs text-emerald-700 dark:text-emerald-400 mb-0.5">{{ $ex->revenueType->nom }}</p>
                    @endif
                    <p class="text-sm text-slate-700 dark:text-slate-200 mt-1">{{ $ex->libelle ?: '—' }}</p>
                    @if ($ex->fournisseur)
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Fournisseur : {{ $ex->fournisseur }}</p>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="adventiste-card-pro-static p-4 mt-4 bg-slate-50/90 dark:bg-slate-800/50">
            <div class="flex items-center justify-between font-bold text-slate-900 dark:text-white">
                <span>Total des dépenses</span>
                <span class="text-rose-700 dark:text-rose-400 tabular-nums">{{ $fmt($report['total_general']) }}</span>
            </div>
        </div>
    </div>
@else
    <div class="adventiste-card-pro-static p-8 text-center text-sm text-slate-500 dark:text-slate-400 mb-6">
        Aucune dépense validée pour ces critères.
    </div>
@endif
