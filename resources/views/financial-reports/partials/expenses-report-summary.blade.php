@php
    $fmt = static fn ($n) => \App\Helpers\ParoisseConfig::formatMontant($n);
    $selectedCaisse = ! empty($selectedCaisseId)
        ? \App\Models\Caisse::find($selectedCaisseId)
        : null;
    $selectedExpenseType = ! empty($selectedExpenseTypeId)
        ? \App\Models\ExpenseType::find($selectedExpenseTypeId)
        : null;
    $byExpenseType = collect($report['by_expense_type'] ?? []);
    $caisseSummary = collect($report['caisse_summary'] ?? []);
    $totalCaisseCredits = (float) $caisseSummary->sum('credits');
    $totalCaisseDepenses = (float) $caisseSummary->sum('depenses');
    $totalCaisseDepensesToutes = (float) $caisseSummary->sum(fn (array $row): float => (float) ($row['depenses_caisse'] ?? $row['depenses']));
    $totalCaisseSolde = (float) $caisseSummary->sum('solde');
    $isTypeFiltered = (bool) ($report['is_type_filtered'] ?? ! empty($selectedExpenseTypeId));
@endphp

<div class="rounded-xl border border-sky-200/80 dark:border-sky-800/60 bg-sky-50/90 dark:bg-sky-950/30 px-4 py-3 mb-5 text-sm text-sky-900 dark:text-sky-100">
    <strong class="font-semibold">Période :</strong>
    {{ \Illuminate\Support\Carbon::parse($dateDebut)->format('d/m/Y') }} → {{ \Illuminate\Support\Carbon::parse($dateFin)->format('d/m/Y') }}
    @if ($selectedCaisse || $selectedExpenseType)
        <span class="block mt-1 text-slate-600 dark:text-slate-300">
            @if ($selectedExpenseType)
                Type : <strong class="font-medium text-slate-800 dark:text-slate-100">{{ $selectedExpenseType->nom }}</strong>
            @endif
            @if ($selectedCaisse)
                @if ($selectedExpenseType) · @endif
                Caisse : <strong class="font-medium text-slate-800 dark:text-slate-100">{{ $selectedCaisse->nom }}</strong>
            @endif
        </span>
    @endif
</div>

@if ($byExpenseType->isNotEmpty() && ! $selectedExpenseTypeId)
    <div class="adventiste-card-pro-static overflow-hidden mb-6">
        <div class="px-4 py-3 border-b border-slate-200/80 dark:border-slate-600/80 bg-slate-50/80 dark:bg-slate-800/50">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white m-0">Répartition par type de dépense</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 m-0 mt-1">
                @if ($selectedCaisse)
                    Part payée par {{ $selectedCaisse->nom }} pour chaque type de dépense.
                @else
                    Montant total des dépenses de chaque type, quelle que soit la caisse qui les a payées.
                @endif
            </p>
        </div>
        <div class="overflow-x-auto p-2">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-700 dark:text-slate-200 border-b border-slate-200 dark:border-slate-600">
                        <th class="px-3 py-2 font-semibold">Type de dépense</th>
                        <th class="px-3 py-2 font-semibold text-center">Nb</th>
                        <th class="px-3 py-2 font-semibold text-right">Montant</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                    @foreach ($byExpenseType as $row)
                        <tr class="text-slate-700 dark:text-slate-200">
                            <td class="px-3 py-2">{{ $row['nom'] }}</td>
                            <td class="px-3 py-2 text-center tabular-nums">{{ $row['count'] }}</td>
                            <td class="px-3 py-2 text-right font-medium tabular-nums">{{ $fmt($row['montant']) }}</td>
                        </tr>
                    @endforeach
                    <tr class="bg-slate-50/90 dark:bg-slate-800/50 font-semibold">
                        <td class="px-3 py-2">Total</td>
                        <td class="px-3 py-2 text-center tabular-nums">{{ $report['expenses']->count() }}</td>
                        <td class="px-3 py-2 text-right tabular-nums">{{ $fmt($report['total_general']) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endif

@if ($caisseSummary->isNotEmpty())
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="adventiste-card-pro-static p-4 border-t-4 border-t-emerald-500">
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Crédits caisses (période)</p>
            <p class="mt-1 text-2xl font-bold text-emerald-700 dark:text-emerald-400 tabular-nums">{{ $fmt($totalCaisseCredits) }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $caisseSummary->count() }} caisse(s)</p>
        </div>
        <div class="adventiste-card-pro-static p-4 border-t-4 border-t-rose-500">
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
                @if ($isTypeFiltered && $selectedExpenseType)
                    Dépensé — {{ $selectedExpenseType->nom }}
                @else
                    Dépensé (période)
                @endif
            </p>
            <p class="mt-1 text-2xl font-bold text-rose-700 dark:text-rose-400 tabular-nums">{{ $fmt($totalCaisseDepenses) }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                {{ $report['expenses']->count() }} opération(s)
                @if ($isTypeFiltered)
                    · tous types : {{ $fmt($totalCaisseDepensesToutes) }}
                @endif
            </p>
        </div>
        <div class="adventiste-card-pro-static p-4 border-t-4 border-t-sky-500">
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Solde réel caisse (période)</p>
            <p class="mt-1 text-2xl font-bold text-sky-800 dark:text-sky-300 tabular-nums">{{ $fmt($totalCaisseSolde) }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">crédits − toutes les dépenses de la caisse</p>
        </div>
    </div>

    <div class="adventiste-card-pro-static overflow-hidden mb-6">
        <div class="px-4 py-3 border-b border-slate-200/80 dark:border-slate-600/80 bg-slate-50/80 dark:bg-slate-800/50">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white m-0">Répartition par caisse</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 m-0 mt-1">Crédits et dépenses de chaque caisse sur la période. Le solde réel tient compte de toutes les dépenses payées par la caisse.</p>
        </div>
        <div class="overflow-x-auto p-2">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-700 dark:text-slate-200 border-b border-slate-200 dark:border-slate-600">
                        <th class="px-3 py-2 font-semibold">Caisse</th>
                        <th class="px-3 py-2 font-semibold text-right">Crédits</th>
                        @if ($isTypeFiltered && $selectedExpenseType)
                            <th class="px-3 py-2 font-semibold text-right">Dépensé ({{ $selectedExpenseType->nom }})</th>
                        @endif
                        <th class="px-3 py-2 font-semibold text-right">Dépensé (tous types)</th>
                        <th class="px-3 py-2 font-semibold text-right">Solde réel</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                    @foreach ($caisseSummary as $row)
                        <tr class="text-slate-700 dark:text-slate-200">
                            <td class="px-3 py-2">{{ $row['nom'] }}</td>
                            <td class="px-3 py-2 text-right text-emerald-700 dark:text-emerald-400 tabular-nums">{{ $fmt($row['credits']) }}</td>
                            @if ($isTypeFiltered && $selectedExpenseType)
                                <td class="px-3 py-2 text-right text-rose-700 dark:text-rose-400 tabular-nums">{{ $fmt($row['depenses']) }}</td>
                            @endif
                            <td class="px-3 py-2 text-right text-rose-700 dark:text-rose-400 tabular-nums">{{ $fmt($row['depenses_caisse'] ?? $row['depenses']) }}</td>
                            <td class="px-3 py-2 text-right font-semibold tabular-nums">{{ $fmt($row['solde']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@else
    <div class="adventiste-card-pro-static p-4 border-t-4 border-t-rose-500 mb-6">
        <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Total des dépenses</p>
        <p class="mt-1 text-2xl font-bold text-rose-700 dark:text-rose-400 tabular-nums">{{ $fmt($report['total_general']) }}</p>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $report['expenses']->count() }} ligne(s) validée(s)</p>
    </div>

    @if (! $selectedCaisseId && count($report['by_category']) > 0)
        <div class="adventiste-card-pro-static overflow-hidden mb-6">
            <div class="px-4 py-3 border-b border-slate-200/80 dark:border-slate-600/80 bg-slate-50/80 dark:bg-slate-800/50">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white m-0">Par caisse</h3>
            </div>
            <div class="overflow-x-auto p-2">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-700 dark:text-slate-200 border-b border-slate-200 dark:border-slate-600">
                            <th class="px-3 py-2 font-semibold">Caisse</th>
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
@endif
