@php $fmt = static fn ($n) => \App\Helpers\ParoisseConfig::formatMontant($n); @endphp

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
