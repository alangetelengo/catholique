@extends('layouts.app')

@section('title', 'Rapport charges fixes — Catholique')
@section('page-title', 'Rapport des charges fixes')
@section('page-title-info', 'Les charges fixes ne sont déduites d’aucune recette. Rapport à destination de la hiérarchie sur la période choisie (mensuelle ou annuelle).')

@section('btn-create')
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('financial-reports.expenses-by-category') }}" class="adventiste-btn-secondary text-sm no-underline">
            <i class="fas fa-receipt me-1.5" aria-hidden="true"></i>Dépenses par catégorie
        </a>
        <a href="{{ route('financial-reports.index') }}" class="adventiste-btn-secondary text-sm no-underline">
            <i class="fas fa-chart-pie me-1.5" aria-hidden="true"></i>Hub rapports
        </a>
        <a href="{{ route('financial-reports.list') }}" class="adventiste-btn-secondary text-sm no-underline">Rapports enregistrés</a>
        <a href="{{ route('charges-fixes-reports.index') }}" class="adventiste-btn-secondary text-sm no-underline">Rapports charges fixes (CRUD)</a>
    </div>
@endsection

@section('content')
    @php
        $fmt = static fn ($n) => \App\Helpers\ParoisseConfig::formatMontant($n);
    @endphp

    <div class="rounded-xl border border-rose-200/90 dark:border-rose-900/40 bg-rose-50/90 dark:bg-rose-950/20 px-4 py-3 mb-6 text-sm text-rose-950 dark:text-rose-100 leading-relaxed">
        <p class="m-0 flex gap-2">
            <i class="fas fa-info-circle mt-0.5 shrink-0 text-rose-600 dark:text-rose-400" aria-hidden="true"></i>
            <span>Ce rapport liste uniquement les <strong class="font-semibold">charges fixes</strong> enregistrées sur la période. Elles ne sont pas soustraites des recettes dans les autres écrans.</span>
        </p>
    </div>

    <div class="adventiste-card-pro-static p-4 sm:p-5 mb-6">
        <h2 class="text-base font-semibold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
            <i class="fas fa-filter text-emerald-600 dark:text-emerald-400" aria-hidden="true"></i>
            Période et paroisse
        </h2>
        <form method="GET" action="{{ route('financial-reports.charges-fixes') }}">
            <input type="hidden" name="period_type" id="period_type" value="{{ $periodType }}">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-4 items-end">
                @if (auth()->user()->hasRole('super_admin') && $paroisses->count() > 0)
                    <div class="lg:col-span-3">
                        <label for="cf_paroisse" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Paroisse <span class="text-red-500">*</span></label>
                        <select id="cf_paroisse" name="paroisse_id" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm text-slate-900 dark:text-slate-100" required>
                            <option value="">Sélectionner…</option>
                            @foreach ($paroisses as $paroisse)
                                <option value="{{ $paroisse->id }}" @selected($selectedParoisseId == $paroisse->id)>{{ $paroisse->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <input type="hidden" name="paroisse_id" value="{{ auth()->user()->paroisse_id }}">
                @endif

                <div class="lg:col-span-3">
                    <span class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Type de période</span>
                    <div class="inline-flex rounded-lg border border-slate-300 dark:border-slate-600 p-0.5 bg-slate-100/80 dark:bg-slate-800/80 w-full sm:w-auto">
                        <button type="button" class="period-btn flex-1 sm:flex-none px-3 py-2 text-sm rounded-md transition-colors {{ $periodType === 'month' ? 'bg-white dark:bg-slate-700 text-emerald-700 dark:text-emerald-300 font-semibold shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200' }}" data-period="month">Mensuel</button>
                        <button type="button" class="period-btn flex-1 sm:flex-none px-3 py-2 text-sm rounded-md transition-colors {{ $periodType === 'year' ? 'bg-white dark:bg-slate-700 text-emerald-700 dark:text-emerald-300 font-semibold shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200' }}" data-period="year">Annuel</button>
                    </div>
                </div>

                <div class="mois-field lg:col-span-2 {{ $periodType === 'year' ? 'hidden' : '' }}">
                    <label for="cf_month" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Mois</label>
                    <select id="cf_month" name="month" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm text-slate-900 dark:text-slate-100">
                        @foreach ([1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril', 5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août', 9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'] as $num => $nom)
                            <option value="{{ $num }}" @selected($selectedMonth == $num)>{{ $nom }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <label for="cf_year" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Année</label>
                    <select id="cf_year" name="year" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm text-slate-900 dark:text-slate-100">
                        @for ($y = now()->year; $y >= now()->year - 5; $y--)
                            <option value="{{ $y }}" @selected($selectedYear == $y)>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <div class="lg:col-span-12">
                    <button type="submit" class="adventiste-btn-primary">
                        <i class="fas fa-list me-2" aria-hidden="true"></i>Voir le rapport
                    </button>
                </div>
            </div>
        </form>
    </div>

    @if ($report)
        <div class="adventiste-card-pro-static p-4 sm:p-5 mb-6 border-t-4 border-t-rose-500">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="text-base font-semibold text-slate-900 dark:text-white m-0 flex items-center gap-2">
                        <i class="fas fa-file-invoice-dollar text-rose-600 dark:text-rose-400" aria-hidden="true"></i>
                        Total charges fixes
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 m-0">
                        {{ $report['date_debut']->format('d/m/Y') }} — {{ $report['date_fin']->format('d/m/Y') }}
                        · {{ $report['expenses']->count() }} enregistrement(s)
                    </p>
                </div>
                <p class="text-2xl font-bold text-rose-700 dark:text-rose-400 m-0 tabular-nums">{{ $fmt($report['total']) }}</p>
            </div>
        </div>

        <div class="adventiste-card-pro-static overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-200/80 dark:border-slate-600/80 bg-slate-50/80 dark:bg-slate-800/50">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white m-0 flex items-center gap-2">
                    <i class="fas fa-receipt text-slate-500 dark:text-slate-400" aria-hidden="true"></i>
                    Liste des charges fixes
                </h3>
            </div>
            @if ($report['expenses']->count() > 0)
                @php $typeLabels = $report['type_labels'] ?? []; @endphp
                <div class="adventiste-table-shell border-0 rounded-none shadow-none">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left text-slate-700 dark:text-slate-200">
                                    <th class="px-4 py-3 font-semibold">Date</th>
                                    <th class="px-4 py-3 font-semibold">Type de charge</th>
                                    <th class="px-4 py-3 font-semibold">Réf. facture</th>
                                    <th class="px-4 py-3 font-semibold">Fournisseur</th>
                                    <th class="px-4 py-3 font-semibold text-right">Montant</th>
                                    <th class="px-4 py-3 font-semibold">Méthode</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                                @foreach ($report['expenses'] as $exp)
                                    <tr class="text-slate-700 dark:text-slate-200">
                                        <td class="px-4 py-3 whitespace-nowrap">{{ $exp->date_depense?->format('d/m/Y') }}</td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex rounded-full bg-rose-500/10 dark:bg-rose-500/20 px-2.5 py-0.5 text-xs font-medium text-rose-800 dark:text-rose-200">{{ $typeLabels[$exp->type_charge] ?? $exp->type_charge }}</span>
                                        </td>
                                        <td class="px-4 py-3">{{ $exp->facture_reference ?? '—' }}</td>
                                        <td class="px-4 py-3">{{ $exp->fournisseur ?? '—' }}</td>
                                        <td class="px-4 py-3 text-right font-semibold text-rose-700 dark:text-rose-400">{{ $fmt($exp->montant) }}</td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex rounded-full bg-slate-100 dark:bg-slate-700 px-2.5 py-0.5 text-xs font-medium text-slate-700 dark:text-slate-200">{{ ucfirst(str_replace('_', ' ', (string) $exp->methode_paiement)) }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-slate-50/90 dark:bg-slate-800/50 font-bold text-slate-900 dark:text-slate-100">
                                    <td class="px-4 py-3 text-right" colspan="4">Total</td>
                                    <td class="px-4 py-3 text-right">{{ $fmt($report['total']) }}</td>
                                    <td class="px-4 py-3"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @else
                <p class="text-sm text-slate-500 dark:text-slate-400 p-6 m-0">Aucune charge fixe enregistrée pour cette période.</p>
            @endif
        </div>
    @else
        <div class="adventiste-card-pro-static p-10 text-center">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400">
                <i class="fas fa-file-invoice-dollar text-xl" aria-hidden="true"></i>
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-400 m-0 max-w-md mx-auto">
                @if (auth()->user()->hasRole('super_admin'))
                    Sélectionnez une paroisse et une période, puis cliquez sur « Voir le rapport ».
                @else
                    Choisissez la période et cliquez sur « Voir le rapport ».
                @endif
            </p>
        </div>
    @endif
@endsection

@push('scripts')
<script>
document.querySelectorAll('.period-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var period = this.dataset.period;
        var hidden = document.getElementById('period_type');
        if (hidden) hidden.value = period;
        document.querySelectorAll('.period-btn').forEach(function (b) {
            b.classList.remove('bg-white', 'dark:bg-slate-700', 'text-emerald-700', 'dark:text-emerald-300', 'font-semibold', 'shadow-sm');
            b.classList.add('text-slate-600', 'dark:text-slate-400');
        });
        this.classList.add('bg-white', 'dark:bg-slate-700', 'text-emerald-700', 'dark:text-emerald-300', 'font-semibold', 'shadow-sm');
        this.classList.remove('text-slate-600', 'dark:text-slate-400');
        document.querySelectorAll('.mois-field').forEach(function (el) {
            if (period === 'year') el.classList.add('hidden');
            else el.classList.remove('hidden');
        });
    });
});
</script>
@endpush
