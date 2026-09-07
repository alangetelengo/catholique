@php $fmt = static fn ($n) => \App\Helpers\ParoisseConfig::formatMontant($n); @endphp

@if ($popoteReport)
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="adventiste-card-pro-static p-4 border-t-4 border-t-emerald-500">
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Crédits caisse popote</p>
            <p class="mt-1 text-2xl font-bold text-emerald-700 dark:text-emerald-400 tabular-nums">{{ $fmt($popoteReport['subvention_recue']) }}</p>
        </div>
        <div class="adventiste-card-pro-static p-4 border-t-4 border-t-rose-500">
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Dépenses alimentation</p>
            <p class="mt-1 text-2xl font-bold text-rose-700 dark:text-rose-400 tabular-nums">{{ $fmt($popoteReport['total_depenses_alimentation']) }}</p>
        </div>
        <div class="adventiste-card-pro-static p-4 border-t-4 border-t-sky-500">
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Écart</p>
            <p class="mt-1 text-2xl font-bold {{ $popoteReport['solde'] >= 0 ? 'text-sky-800 dark:text-sky-300' : 'text-amber-700 dark:text-amber-400' }} tabular-nums">{{ $fmt($popoteReport['solde']) }}</p>
        </div>
    </div>

    @if (count($popoteReport['monthly_summary']) > 0)
        <div class="adventiste-card-pro-static overflow-hidden mb-6">
            <div class="px-4 py-3 border-b border-slate-200/80 dark:border-slate-600/80 bg-slate-50/80 dark:bg-slate-800/50">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white m-0">Synthèse mensuelle</h3>
            </div>
            <div class="overflow-x-auto p-2">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-700 dark:text-slate-200 border-b border-slate-200 dark:border-slate-600">
                            <th class="px-3 py-2 font-semibold">Mois</th>
                            <th class="px-3 py-2 font-semibold text-right">Crédits</th>
                            <th class="px-3 py-2 font-semibold text-right">Dépenses</th>
                            <th class="px-3 py-2 font-semibold text-right">Solde</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                        @foreach ($popoteReport['monthly_summary'] as $row)
                            <tr class="text-slate-700 dark:text-slate-200">
                                <td class="px-3 py-2">{{ $row['mois_label'] }}</td>
                                <td class="px-3 py-2 text-right text-emerald-700 dark:text-emerald-400 tabular-nums">{{ $fmt($row['subvention_recue']) }}</td>
                                <td class="px-3 py-2 text-right text-rose-700 dark:text-rose-400 tabular-nums">{{ $fmt($row['depenses']) }}</td>
                                <td class="px-3 py-2 text-right font-semibold tabular-nums">{{ $fmt($row['solde']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-6">
        <div class="adventiste-card-pro-static overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-200/80 dark:border-slate-600/80 bg-slate-50/80 dark:bg-slate-800/50">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white m-0">Crédits caisse</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                        @forelse ($popoteReport['credits'] as $credit)
                            <tr class="text-slate-700 dark:text-slate-200">
                                <td class="px-4 py-3 whitespace-nowrap">{{ $credit->date_mouvement?->format('d/m/Y') }}</td>
                                <td class="px-4 py-3">{{ $credit->libelle ?: '—' }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-emerald-700 dark:text-emerald-400 tabular-nums">{{ $fmt($credit->montant) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-6 text-center text-slate-500">Aucun crédit sur la période.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="adventiste-card-pro-static overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-200/80 dark:border-slate-600/80 bg-slate-50/80 dark:bg-slate-800/50">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white m-0">Dépenses alimentation</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                        @forelse ($popoteReport['depenses'] as $expense)
                            <tr class="text-slate-700 dark:text-slate-200">
                                <td class="px-4 py-3 whitespace-nowrap">{{ $expense->date_depense?->format('d/m/Y') }}</td>
                                <td class="px-4 py-3">{{ $expense->libelle ?: '—' }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-rose-700 dark:text-rose-400 tabular-nums">{{ $fmt($expense->fundingSources->sum('montant_alloue')) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-6 text-center text-slate-500">Aucune dépense popote sur la période.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @can('generate_financial_reports')
        <form method="POST" action="{{ route('financial-reports.expenses.store-popote') }}" class="flex justify-end">
            @csrf
            <input type="hidden" name="paroisse_id" value="{{ $selectedParoisseId }}">
            <input type="hidden" name="date_debut" value="{{ $dateDebut }}">
            <input type="hidden" name="date_fin" value="{{ $dateFin }}">
            <button type="submit" class="adventiste-btn-primary">
                <i class="fas fa-save me-2" aria-hidden="true"></i>Enregistrer le rapport Popote
            </button>
        </form>
    @endcan
@else
    <div class="adventiste-card-pro-static p-10 text-center text-slate-500 dark:text-slate-400">
        Sélectionnez une paroisse et une période, puis cliquez sur <strong class="font-medium">Calculer</strong>.
    </div>
@endif
