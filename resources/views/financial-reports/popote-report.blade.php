@extends('layouts.app')

@section('title', 'Rapport Subvention Popote — Catholique')
@section('page-title', 'Rapport Subvention Popote')
@section('page-title-info', 'Compare la subvention popote reçue aux dépenses enregistrées en catégorie « alimentation popote » sur la période choisie (mensuelle ou annuelle).')

@section('btn-create')
    <div class="flex flex-wrap items-center gap-2">
        @if ($report)
            <button type="button" id="btnOpenPrintPopote" class="adventiste-btn-secondary text-sm"
                data-print-url="{{ route('financial-reports.popote-print', [
                    'paroisse_id' => $selectedParoisseId,
                    'period_type' => $periodType,
                    'month' => $selectedMonth,
                    'year' => $selectedYear,
                ]) }}">
                <i class="fas fa-print me-1.5" aria-hidden="true"></i>Imprimer
            </button>
            <form action="{{ route('financial-reports.popote-pdf') }}" method="POST" class="inline-flex m-0">
                @csrf
                <input type="hidden" name="paroisse_id" value="{{ $selectedParoisseId }}">
                <input type="hidden" name="period_type" value="{{ $periodType }}">
                <input type="hidden" name="month" value="{{ $selectedMonth }}">
                <input type="hidden" name="year" value="{{ $selectedYear }}">
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg border border-rose-200 dark:border-rose-800/60 bg-rose-50 dark:bg-rose-950/40 px-3 py-2 text-sm font-semibold text-rose-800 dark:text-rose-200 hover:bg-rose-100 dark:hover:bg-rose-900/50 transition-colors cursor-pointer">
                    <i class="fas fa-download" aria-hidden="true"></i>PDF
                </button>
            </form>
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
        $joursLabels = ['lundi' => 'Lundi', 'mardi' => 'Mardi', 'mercredi' => 'Mercredi', 'jeudi' => 'Jeudi', 'vendredi' => 'Vendredi', 'samedi' => 'Samedi', 'dimanche' => 'Dimanche'];
    @endphp

    <div class="rounded-xl border border-sky-200/90 dark:border-sky-800/50 bg-sky-50/90 dark:bg-sky-950/25 px-4 py-3 mb-6 text-sm text-sky-950 dark:text-sky-100 leading-relaxed">
        <p class="m-0 flex gap-2">
            <i class="fas fa-info-circle mt-0.5 shrink-0 text-sky-600 dark:text-sky-400" aria-hidden="true"></i>
            <span>La subvention est réservée aux <strong class="font-semibold">dépenses d’alimentation</strong> (catégorie <strong class="font-semibold">alimentation popote</strong>). Seules ces dépenses sont comparées à la subvention reçue.</span>
        </p>
    </div>

    <div class="adventiste-card-pro-static p-4 sm:p-5 mb-6">
        <h2 class="text-base font-semibold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
            <i class="fas fa-filter text-emerald-600 dark:text-emerald-400" aria-hidden="true"></i>
            Période et paroisse
        </h2>
        <form method="GET" action="{{ route('financial-reports.popote') }}">
            <input type="hidden" name="period_type" id="period_type" value="{{ $periodType }}">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-4 items-end">
                @if (auth()->user()->hasRole('super_admin') && $paroisses->count() > 0)
                    <div class="lg:col-span-3">
                        <label for="popote_paroisse" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Paroisse <span class="text-red-500">*</span></label>
                        <select id="popote_paroisse" name="paroisse_id" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm" required>
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
                    <label for="popote_month" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Mois</label>
                    <select id="popote_month" name="month" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm">
                        @foreach ([1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril', 5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août', 9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'] as $num => $nom)
                            <option value="{{ $num }}" @selected($selectedMonth == $num)>{{ $nom }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <label for="popote_year" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Année</label>
                    <select id="popote_year" name="year" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm">
                        @for ($y = now()->year; $y >= now()->year - 5; $y--)
                            <option value="{{ $y }}" @selected($selectedYear == $y)>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <button type="submit" class="adventiste-btn-primary w-full sm:w-auto">
                        <i class="fas fa-calculator me-2" aria-hidden="true"></i>Voir le rapport
                    </button>
                </div>
            </div>
        </form>
    </div>

    @if ($report)
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="adventiste-card-pro-static p-4 border-t-4 border-t-emerald-500">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400 flex items-center gap-2">
                    <i class="fas fa-coins text-emerald-600 dark:text-emerald-400" aria-hidden="true"></i>
                    Subvention reçue
                </p>
                <p class="mt-1 text-xl font-bold text-emerald-700 dark:text-emerald-400">{{ $fmt($report['subvention_recue']) }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $report['date_debut']->format('d/m/Y') }} — {{ $report['date_fin']->format('d/m/Y') }}</p>
            </div>
            <div class="adventiste-card-pro-static p-4 border-t-4 border-t-rose-500">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400 flex items-center gap-2">
                    <i class="fas fa-shopping-cart text-rose-600 dark:text-rose-400" aria-hidden="true"></i>
                    Dépenses alimentation
                </p>
                <p class="mt-1 text-xl font-bold text-rose-700 dark:text-rose-400">{{ $fmt($report['total_depenses_alimentation']) }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $report['depenses_alimentation']->count() }} ligne(s)</p>
            </div>
            <div class="adventiste-card-pro-static p-4 border-t-4 {{ $report['solde'] >= 0 ? 'border-t-sky-500' : 'border-t-amber-500' }}">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400 flex items-center gap-2">
                    <i class="fas fa-balance-scale text-slate-500 dark:text-slate-400" aria-hidden="true"></i>
                    Solde
                </p>
                <p class="mt-1 text-xl font-bold {{ $report['solde'] >= 0 ? 'text-sky-700 dark:text-sky-300' : 'text-amber-700 dark:text-amber-400' }}">{{ $fmt($report['solde']) }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $report['solde'] >= 0 ? 'Reste subvention' : 'Dépassement' }}</p>
            </div>
        </div>

        <div class="adventiste-card-pro-static overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-200/80 dark:border-slate-600/80 bg-slate-50/80 dark:bg-slate-800/50">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white m-0 flex items-center gap-2">
                    <i class="fas fa-list text-slate-500 dark:text-slate-400" aria-hidden="true"></i>
                    Détail des dépenses alimentation
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 m-0 mt-1">Libellé, jour et montant</p>
            </div>
            @if ($report['depenses_alimentation']->count() > 0)
                <div class="adventiste-table-shell border-0 rounded-none shadow-none">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left text-slate-700 dark:text-slate-200">
                                    <th class="px-4 py-3 font-semibold">Date</th>
                                    <th class="px-4 py-3 font-semibold">Jour</th>
                                    <th class="px-4 py-3 font-semibold">Libellé</th>
                                    <th class="px-4 py-3 font-semibold text-right">Montant</th>
                                    <th class="px-4 py-3 font-semibold">Méthode</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                                @foreach ($report['depenses_alimentation'] as $dep)
                                    <tr class="text-slate-700 dark:text-slate-200">
                                        <td class="px-4 py-3 whitespace-nowrap">{{ $dep->date_depense?->format('d/m/Y') }}</td>
                                        <td class="px-4 py-3">{{ $joursLabels[$dep->jour_semaine] ?? $dep->jour_semaine ?? '—' }}</td>
                                        <td class="px-4 py-3">{{ $dep->libelle ?? '—' }}</td>
                                        <td class="px-4 py-3 text-right font-semibold text-rose-700 dark:text-rose-400">{{ $fmt($dep->montant) }}</td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex rounded-full bg-slate-100 dark:bg-slate-700 px-2.5 py-0.5 text-xs font-medium text-slate-700 dark:text-slate-200">{{ ucfirst(str_replace('_', ' ', (string) $dep->methode_paiement)) }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-slate-50/90 dark:bg-slate-800/50 font-bold text-slate-900 dark:text-slate-100">
                                    <td class="px-4 py-3 text-right" colspan="3">Total dépenses alimentation</td>
                                    <td class="px-4 py-3 text-right">{{ $fmt($report['total_depenses_alimentation']) }}</td>
                                    <td class="px-4 py-3"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @else
                <p class="text-sm text-slate-500 dark:text-slate-400 p-6 m-0">Aucune dépense alimentation enregistrée pour cette période.</p>
            @endif
        </div>
    @else
        <div class="adventiste-card-pro-static p-10 text-center">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400">
                <i class="fas fa-utensils text-xl" aria-hidden="true"></i>
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

    @if ($report ?? false)
        <dialog id="modalPrintPopote" class="max-w-6xl w-[calc(100%-2rem)] rounded-2xl border border-slate-200/80 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-2xl p-0 backdrop:bg-slate-900/50">
            <div class="px-4 sm:px-6 py-3 border-b border-slate-200 dark:border-slate-600 flex items-start justify-between gap-4">
                <h2 class="text-base font-semibold text-slate-900 dark:text-white m-0 flex items-center gap-2" id="modalPrintPopoteLabel">
                    <i class="fas fa-print text-emerald-600 dark:text-emerald-400" aria-hidden="true"></i>
                    Aperçu avant impression
                </h2>
                <button type="button" class="shrink-0 rounded-lg p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800" onclick="document.getElementById('modalPrintPopote').close()" aria-label="Fermer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="p-0 bg-slate-100 dark:bg-slate-950">
                <iframe id="printPopoteIframe" title="Document à imprimer" class="w-full h-[min(75vh,560px)] border-0 bg-white block"></iframe>
            </div>
            <div class="px-4 sm:px-6 py-3 border-t border-slate-200 dark:border-slate-600 flex flex-wrap justify-end gap-2">
                <button type="button" class="adventiste-btn-secondary" onclick="document.getElementById('modalPrintPopote').close()">Fermer</button>
                <button type="button" class="adventiste-btn-primary" id="btnPrintPopoteFromModal">
                    <i class="fas fa-print me-2" aria-hidden="true"></i>Imprimer
                </button>
            </div>
        </dialog>
    @endif
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('.period-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var period = this.dataset.period;
                document.getElementById('period_type').value = period;
                document.querySelectorAll('.period-btn').forEach(function (b) {
                    b.classList.remove('bg-white', 'dark:bg-slate-700', 'text-emerald-700', 'dark:text-emerald-300', 'font-semibold', 'shadow-sm');
                    b.classList.add('text-slate-600', 'dark:text-slate-400');
                });
                this.classList.add('bg-white', 'dark:bg-slate-700', 'text-emerald-700', 'dark:text-emerald-300', 'font-semibold', 'shadow-sm');
                this.classList.remove('text-slate-600', 'dark:text-slate-400');
                document.querySelectorAll('.mois-field').forEach(function (el) {
                    if (period === 'month') {
                        el.classList.remove('hidden');
                    } else {
                        el.classList.add('hidden');
                    }
                });
            });
        });

        (function () {
            var dlg = document.getElementById('modalPrintPopote');
            var iframe = document.getElementById('printPopoteIframe');
            var openBtn = document.getElementById('btnOpenPrintPopote');
            var printBtn = document.getElementById('btnPrintPopoteFromModal');
            if (!dlg || !iframe) {
                return;
            }
            if (openBtn) {
                openBtn.addEventListener('click', function () {
                    var url = this.getAttribute('data-print-url');
                    if (url) {
                        iframe.src = url;
                    }
                    dlg.showModal();
                });
            }
            dlg.addEventListener('close', function () {
                iframe.src = 'about:blank';
            });
            if (printBtn) {
                printBtn.addEventListener('click', function () {
                    try {
                        if (iframe.contentWindow && iframe.contentWindow.print) {
                            iframe.contentWindow.print();
                        }
                    } catch (e) {
                        console.error(e);
                    }
                });
            }
        })();
    </script>
@endpush
