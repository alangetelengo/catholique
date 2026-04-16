@extends('layouts.app')

@section('title', 'Rapport des revenus - Semaine / dimanche')
@section('page-title', 'Rapport des revenus — Quête ordinaire')
@section('page-title-info', 'Recettes de quête ordinaire par semaine (lundi–dimanche) ou par mois, avec impression et export PDF.')

@section('btn-create')
    @if($report ?? null)
        <div class="flex flex-wrap items-center gap-2">
            <button type="button"
                    id="btnOpenPrintModal"
                    class="adventiste-btn-secondary"
                    data-print-url="{{ route('financial-reports.revenues-weekly-print', [
                        'paroisse_id' => $selectedParoisseId,
                        'period_type' => $periodType,
                        'week_start' => $selectedWeekStart,
                        'month' => $selectedMonth,
                        'year' => $selectedYear,
                    ]) }}">
                <i class="fas fa-print me-2" aria-hidden="true"></i>Imprimer
            </button>
            <form action="{{ route('financial-reports.revenues-weekly-pdf') }}" method="POST" class="inline-flex">
                @csrf
                <input type="hidden" name="paroisse_id" value="{{ $selectedParoisseId }}">
                <input type="hidden" name="period_type" value="{{ $periodType }}">
                <input type="hidden" name="week_start" value="{{ $selectedWeekStart }}">
                <input type="hidden" name="month" value="{{ $selectedMonth }}">
                <input type="hidden" name="year" value="{{ $selectedYear }}">
                <button type="submit" class="adventiste-btn-primary">
                    <i class="fas fa-download me-2" aria-hidden="true"></i>Télécharger PDF
                </button>
            </form>
        </div>
    @endif
@endsection

@section('content')
    <div class="adventiste-card-pro-static p-4 sm:p-5 mb-6">
        <h2 class="text-base font-semibold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
            <i class="fas fa-sliders-h text-emerald-600 dark:text-emerald-400" aria-hidden="true"></i>
            Paramètres du rapport
        </h2>
        <form method="GET" action="{{ route('financial-reports.revenues-weekly') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
            @if(auth()->user()->hasRole('super_admin') && $paroisses->count() > 0)
                <div class="md:col-span-2 lg:col-span-1">
                    <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Paroisse <span class="text-red-500">*</span></label>
                    <select name="paroisse_id" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm" required>
                        <option value="">Sélectionner…</option>
                        @foreach($paroisses as $paroisse)
                            <option value="{{ $paroisse->id }}" @selected($selectedParoisseId == $paroisse->id)>{{ $paroisse->nom }}</option>
                        @endforeach
                    </select>
                </div>
            @else
                <input type="hidden" name="paroisse_id" value="{{ auth()->user()->paroisse_id }}">
            @endif

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Période</label>
                <select name="period_type" id="period-type" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm" required>
                    <option value="week" @selected($periodType === 'week')>Semaine</option>
                    <option value="month" @selected($periodType === 'month')>Mois</option>
                </select>
            </div>

            <div id="week-container">
                <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Semaine <span class="text-red-500">*</span></label>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">Date du lundi de la semaine</p>
                <input type="date"
                       name="week_start"
                       id="week-start"
                       class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm"
                       value="{{ $selectedWeekStart ?? now()->startOfWeek()->format('Y-m-d') }}"
                       required>
            </div>

            <div id="month-container" class="hidden">
                <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Mois <span class="text-red-500">*</span></label>
                <select name="month" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm">
                    @foreach([
                        1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
                        5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
                        9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre',
                    ] as $num => $nom)
                        <option value="{{ $num }}" @selected($selectedMonth == $num)>{{ $nom }}</option>
                    @endforeach
                </select>
            </div>

            <div id="year-container" class="hidden">
                <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Année <span class="text-red-500">*</span></label>
                <select name="year" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm">
                    @for($y = now()->year; $y >= now()->year - 2; $y--)
                        <option value="{{ $y }}" @selected($selectedYear == $y)>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <div class="md:col-span-2 lg:col-span-4 flex flex-wrap gap-2">
                <button type="submit" class="adventiste-btn-primary">Générer le rapport</button>
            </div>
        </form>
    </div>

    @if($report)
        @php
            if ($periodType === 'week') {
                $dateDebut = \Carbon\Carbon::parse($selectedWeekStart)->startOfWeek();
                $dateFin = $dateDebut->copy()->endOfWeek();
            } else {
                $dateDebut = \Carbon\Carbon::create($selectedYear, $selectedMonth, 1)->startOfMonth();
                $dateFin = $dateDebut->copy()->endOfMonth();
            }
        @endphp

        <div class="rounded-xl border border-sky-200/80 dark:border-sky-800/60 bg-sky-50/90 dark:bg-sky-950/30 px-4 py-3 mb-6 text-sm text-sky-900 dark:text-sky-100">
            <strong class="font-semibold">Période :</strong>
            {{ $dateDebut->format('d/m/Y') }} au {{ $dateFin->format('d/m/Y') }}
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="adventiste-card-pro-static p-4 border-t-4 border-t-sky-500">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Total semaine</p>
                <p class="mt-1 text-xl font-bold text-slate-900 dark:text-white">{{ \App\Helpers\ParoisseConfig::formatMontant($report['total_semaine']) }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Lundi – samedi</p>
            </div>
            <div class="adventiste-card-pro-static p-4 border-t-4 border-t-emerald-500">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Total dimanche</p>
                <p class="mt-1 text-xl font-bold text-emerald-700 dark:text-emerald-400">{{ \App\Helpers\ParoisseConfig::formatMontant($report['total_dimanche']) }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Messe du dimanche</p>
            </div>
            <div class="adventiste-card-pro-static p-4 border-t-4 border-t-amber-500">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Total général</p>
                <p class="mt-1 text-xl font-bold text-amber-800 dark:text-amber-300">{{ \App\Helpers\ParoisseConfig::formatMontant($report['total_general']) }}</p>
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
                            @php
                                $joursLabels = [
                                    'lundi' => 'Lundi',
                                    'mardi' => 'Mardi',
                                    'mercredi' => 'Mercredi',
                                    'jeudi' => 'Jeudi',
                                    'vendredi' => 'Vendredi',
                                    'samedi' => 'Samedi',
                                ];
                            @endphp
                            @foreach(['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'] as $jour)
                                <tr class="text-slate-700 dark:text-slate-200">
                                    <td class="px-3 py-2">{{ $joursLabels[$jour] }}</td>
                                    <td class="px-3 py-2 text-right font-medium">{{ \App\Helpers\ParoisseConfig::formatMontant($report['details_semaine'][$jour]['montant'] ?? 0) }}</td>
                                    <td class="px-3 py-2 text-center">{{ $report['details_semaine'][$jour]['count'] ?? 0 }}</td>
                                </tr>
                            @endforeach
                            <tr class="bg-sky-50/80 dark:bg-sky-950/40 font-semibold text-slate-900 dark:text-white">
                                <td class="px-3 py-2">Total semaine</td>
                                <td class="px-3 py-2 text-right">{{ \App\Helpers\ParoisseConfig::formatMontant($report['total_semaine']) }}</td>
                                <td class="px-3 py-2 text-center">{{ $report['revenues_semaine']->count() }}</td>
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
                                <td class="px-3 py-2 text-right font-medium">{{ \App\Helpers\ParoisseConfig::formatMontant($report['total_dimanche']) }}</td>
                                <td class="px-3 py-2 text-center">{{ $report['details_dimanche']['count'] }}</td>
                            </tr>
                            <tr class="bg-emerald-50/80 dark:bg-emerald-950/40 font-semibold text-slate-900 dark:text-white">
                                <td class="px-3 py-2">Total dimanche</td>
                                <td class="px-3 py-2 text-right">{{ \App\Helpers\ParoisseConfig::formatMontant($report['total_dimanche']) }}</td>
                                <td class="px-3 py-2 text-center">{{ $report['details_dimanche']['count'] }}</td>
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
                                <th class="px-4 py-3 font-semibold">Méthode</th>
                                <th class="px-4 py-3 font-semibold text-right">Montant</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                            @foreach($report['revenues_all'] as $revenue)
                                @php
                                    $joursLabelsFull = [
                                        'lundi' => 'Lundi',
                                        'mardi' => 'Mardi',
                                        'mercredi' => 'Mercredi',
                                        'jeudi' => 'Jeudi',
                                        'vendredi' => 'Vendredi',
                                        'samedi' => 'Samedi',
                                        'dimanche' => 'Dimanche',
                                    ];
                                @endphp
                                <tr class="text-slate-700 dark:text-slate-200">
                                    <td class="px-4 py-3">{{ $revenue->date_recette?->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3">{{ $joursLabelsFull[$revenue->jour_semaine] ?? '—' }}</td>
                                    <td class="px-4 py-3">
                                        @if($revenue->periode_messe === 'semaine' || in_array($revenue->jour_semaine, ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi']))
                                            <span class="inline-flex items-center rounded-full bg-sky-100 dark:bg-sky-900/40 px-2.5 py-1 text-xs font-semibold text-sky-800 dark:text-sky-200">Semaine</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-emerald-100 dark:bg-emerald-900/40 px-2.5 py-1 text-xs font-semibold text-emerald-800 dark:text-emerald-200">Dimanche</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">{{ $revenue->methode_paiement ?? '—' }}</td>
                                    <td class="px-4 py-3 text-right font-semibold">{{ \App\Helpers\ParoisseConfig::formatMontant($revenue->montant) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-slate-100/90 dark:bg-slate-800/80 font-bold text-slate-900 dark:text-white">
                                <td colspan="4" class="px-4 py-3">Total général</td>
                                <td class="px-4 py-3 text-right">{{ \App\Helpers\ParoisseConfig::formatMontant($report['total_general']) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="adventiste-card-pro-static p-10 text-center">
            <div class="inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 mb-4" aria-hidden="true">
                <i class="fas fa-calculator text-2xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-100 mb-2">Sélectionnez une période</h3>
            <p class="text-sm text-slate-600 dark:text-slate-400 max-w-md mx-auto mb-0">
                Choisissez la paroisse, la période (semaine ou mois), puis cliquez sur « Générer le rapport » pour afficher les montants de quête ordinaire.
            </p>
        </div>
    @endif

    @if($report ?? false)
        <dialog id="modalPrintReport" class="max-w-[min(96vw,72rem)] w-full rounded-2xl border border-slate-200/80 dark:border-slate-600 bg-white dark:bg-slate-800 shadow-2xl p-0 backdrop:bg-slate-900/60">
            <div class="flex max-h-[min(92vh,900px)] flex-col">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 dark:border-slate-600 px-4 py-3 sm:px-5">
                    <h2 class="text-base font-semibold text-slate-900 dark:text-white m-0 flex items-center gap-2" id="modalPrintReportLabel">
                        <i class="fas fa-print text-emerald-600 dark:text-emerald-400" aria-hidden="true"></i>
                        Rapport des revenus — Quête ordinaire
                    </h2>
                    <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700" data-print-dialog-close aria-label="Fermer">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <div class="min-h-0 flex-1 overflow-hidden bg-slate-50 dark:bg-slate-900/50">
                    <iframe id="printReportIframe" title="Document à imprimer" class="h-[min(75vh,640px)] w-full border-0"></iframe>
                </div>
                <div class="flex flex-wrap justify-end gap-2 border-t border-slate-200 dark:border-slate-600 px-4 py-3 sm:px-5">
                    <button type="button" class="adventiste-btn-secondary" data-print-dialog-close>Fermer</button>
                    <button type="button" class="adventiste-btn-primary" id="btnPrintFromModal">
                        <i class="fas fa-print me-2" aria-hidden="true"></i>Imprimer
                    </button>
                </div>
            </div>
        </dialog>
    @endif
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const periodType = document.getElementById('period-type');
        const weekContainer = document.getElementById('week-container');
        const monthContainer = document.getElementById('month-container');
        const yearContainer = document.getElementById('year-container');
        const weekInput = document.getElementById('week-start');

        if (!periodType || !weekContainer || !monthContainer || !yearContainer) return;

        const monthSelect = monthContainer.querySelector('select');
        const yearSelect = yearContainer.querySelector('select');

        function periodIsWeek() {
            return periodType.value === 'week';
        }

        function updatePeriodFields() {
            const week = periodIsWeek();
            weekContainer.classList.toggle('hidden', !week);
            monthContainer.classList.toggle('hidden', week);
            yearContainer.classList.toggle('hidden', week);
            if (weekInput) {
                weekInput.required = week;
            }
            if (monthSelect) monthSelect.required = !week;
            if (yearSelect) yearSelect.required = !week;
        }

        if (weekInput) {
            weekInput.addEventListener('change', function() {
                const selectedDate = new Date(this.value);
                const dayOfWeek = selectedDate.getDay();
                const diff = selectedDate.getDate() - dayOfWeek + (dayOfWeek === 0 ? -6 : 1);
                const monday = new Date(selectedDate.setDate(diff));
                this.value = monday.toISOString().split('T')[0];
            });
        }

        periodType.addEventListener('change', updatePeriodFields);
        updatePeriodFields();

        const btnOpenPrint = document.getElementById('btnOpenPrintModal');
        const modalPrint = document.getElementById('modalPrintReport');
        const printIframe = document.getElementById('printReportIframe');
        const btnPrintFromModal = document.getElementById('btnPrintFromModal');

        function closePrintDialog() {
            if (modalPrint && typeof modalPrint.close === 'function') {
                modalPrint.close();
            }
        }

        if (modalPrint && printIframe && btnOpenPrint && typeof modalPrint.showModal === 'function') {
            btnOpenPrint.addEventListener('click', function() {
                const url = btnOpenPrint.getAttribute('data-print-url');
                if (url) printIframe.src = url;
                modalPrint.showModal();
            });
            modalPrint.addEventListener('close', function() {
                printIframe.src = 'about:blank';
            });
            modalPrint.querySelectorAll('[data-print-dialog-close]').forEach(function(btn) {
                btn.addEventListener('click', closePrintDialog);
            });
        }
        if (btnPrintFromModal && printIframe) {
            btnPrintFromModal.addEventListener('click', function() {
                try {
                    if (printIframe.contentWindow && printIframe.contentWindow.print) {
                        printIframe.contentWindow.print();
                    }
                } catch (e) {
                    console.error(e);
                }
            });
        }
    });
</script>
@endpush
