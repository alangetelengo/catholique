@extends('layouts.app')

@section('title', 'Rapport dépenses — Catholique')
@section('page-title', 'Rapport dépenses')

@section('page-title-info')
    Dépenses validées ventilées par caisse et type. L'onglet Capital suit le flux Banque → caisses → dépenses.
    <span id="er-period-display" class="{{ (($summaryReport ?? null) || ($capitalReport ?? null)) ? 'block' : 'hidden' }} mt-1 text-slate-500 dark:text-slate-400">
        @if ($summaryReport ?? null)
            Période : {{ \Illuminate\Support\Carbon::parse($dateDebut)->format('d/m/Y') }} - {{ \Illuminate\Support\Carbon::parse($dateFin)->format('d/m/Y') }}
        @elseif ($capitalReport ?? null)
            Période : {{ \Illuminate\Support\Carbon::parse($dateDebut)->format('d/m/Y') }} - {{ \Illuminate\Support\Carbon::parse($dateFin)->format('d/m/Y') }}
        @endif
    </span>
@endsection

@section('btn-create')
    @php
        $initialPrintUrl = ($activeTab ?? 'synthese') === 'capital' && ($capitalPrintUrl ?? null)
            ? $capitalPrintUrl
            : ($summaryPrintUrl ?? null);
    @endphp
    <div class="flex flex-wrap items-center gap-2">
        @if ($initialPrintUrl)
            <a id="er-print-link" href="{{ $initialPrintUrl }}" class="adventiste-btn-secondary text-sm no-underline inline-flex items-center">
                <i class="fas fa-print me-1.5" aria-hidden="true"></i>Imprimer
            </a>
        @else
            <a id="er-print-link" href="#" class="hidden adventiste-btn-secondary text-sm no-underline inline-flex items-center">
                <i class="fas fa-print me-1.5" aria-hidden="true"></i>Imprimer
            </a>
        @endif
        <a href="{{ route('financial-reports.revenues-by-category') }}" class="adventiste-btn-secondary text-sm no-underline">
            <i class="fas fa-layer-group me-1.5" aria-hidden="true"></i>Recettes par catégorie
        </a>
        <a href="{{ route('financial-reports.list') }}" class="adventiste-btn-secondary text-sm no-underline">Historique</a>
    </div>
@endsection

@section('content')
    @php
        $today = now()->format('Y-m-d');
        $debMois = now()->startOfMonth()->format('Y-m-d');
        $finMois = now()->endOfMonth()->format('Y-m-d');
        $debSem = now()->startOfWeek()->format('Y-m-d');
        $finSem = now()->endOfWeek()->format('Y-m-d');
        $activeTab = $activeTab ?? 'synthese';
        $tabs = [
            'synthese' => ['label' => 'Synthèse', 'icon' => 'fa-wallet'],
            'detail' => ['label' => 'Détail', 'icon' => 'fa-list'],
            'capital' => ['label' => 'Capital', 'icon' => 'fa-university'],
        ];
    @endphp

    <div class="rounded-xl border border-sky-200/90 dark:border-sky-800/50 bg-sky-50/90 dark:bg-sky-950/25 px-4 py-3 mb-6 text-sm text-sky-950 dark:text-sky-100 leading-relaxed">
        <p class="m-0 flex gap-2">
            <i class="fas fa-info-circle mt-0.5 shrink-0 text-sky-600 dark:text-sky-400" aria-hidden="true"></i>
            <span>Les montants regroupent les <strong class="font-semibold">dépenses validées</strong>, ventilées par <strong class="font-semibold">caisse</strong> de financement. L'onglet <strong class="font-semibold">Capital</strong> suit le flux trésorerie Banque → virements → dépenses financées.</span>
        </p>
    </div>

    <div class="adventiste-card-pro-static p-4 sm:p-5 mb-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-4 pb-4 border-b border-slate-200/80 dark:border-slate-600/80">
            <h2 class="text-base font-semibold text-slate-900 dark:text-white m-0 flex items-center gap-2">
                <i class="fas fa-filter text-emerald-600 dark:text-emerald-400" aria-hidden="true"></i>
                Filtres
            </h2>
            <div class="flex flex-col items-stretch gap-1.5 sm:items-end">
                <span class="text-[10px] font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Période rapide</span>
                <div class="flex flex-wrap gap-2" role="group" aria-label="Raccourcis de période">
                    <button type="button" class="adventiste-btn-secondary text-xs py-2 px-3 er-shortcut" data-debut="{{ $today }}" data-fin="{{ $today }}">Aujourd'hui</button>
                    <button type="button" class="adventiste-btn-secondary text-xs py-2 px-3 er-shortcut" data-debut="{{ $debSem }}" data-fin="{{ $finSem }}">Semaine</button>
                    <button type="button" class="adventiste-btn-secondary text-xs py-2 px-3 er-shortcut" data-debut="{{ $debMois }}" data-fin="{{ $finMois }}">Mois en cours</button>
                </div>
            </div>
        </div>

        <form id="er-filterForm" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-3 lg:gap-4 lg:items-start" onsubmit="return false;">
            <input type="hidden" id="er_active_tab" name="tab" value="{{ $activeTab }}">

            @if (auth()->user()->hasRole('super_admin') && $paroisses->count() > 0)
                <div class="lg:col-span-3">
                    <label for="er_paroisse" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Paroisse <span class="text-red-500">*</span></label>
                    <select id="er_paroisse" name="paroisse_id" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm" required>
                        <option value="">Sélectionner…</option>
                        @foreach ($paroisses as $p)
                            <option value="{{ $p->id }}" @selected($selectedParoisseId == $p->id)>{{ $p->nom }}</option>
                        @endforeach
                    </select>
                </div>
            @else
                <input type="hidden" id="er_paroisse_hidden" name="paroisse_id" value="{{ auth()->user()->paroisse_id }}">
            @endif

            <div class="lg:col-span-2">
                <label for="er_date_debut" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Date début</label>
                <input type="date" id="er_date_debut" name="date_debut" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm" value="{{ $dateDebut }}" required>
            </div>
            <div class="lg:col-span-2">
                <label for="er_date_fin" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Date fin</label>
                <input type="date" id="er_date_fin" name="date_fin" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm" value="{{ $dateFin }}" required>
            </div>

            <div id="er-filter-advanced" class="lg:col-span-5 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3 lg:gap-4 lg:col-start-auto {{ $activeTab === 'capital' ? 'hidden' : '' }}">
                <div class="lg:col-span-2">
                    <label for="er_expense_type" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Type de dépense</label>
                    <select id="er_expense_type" name="expense_type_id" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm">
                        <option value="">Tous les types</option>
                        @foreach ($expenseTypes as $expenseType)
                            <option value="{{ $expenseType->id }}" @selected(($selectedExpenseTypeId ?? null) == $expenseType->id)>{{ $expenseType->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="lg:col-span-3">
                    <label for="er_caisse" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Caisse de financement</label>
                    <select id="er_caisse" name="caisse_id" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm" @if (auth()->user()->hasRole('super_admin') && ! $selectedParoisseId) disabled @endif>
                        <option value="">Toutes les caisses</option>
                        @foreach ($caisses as $caisse)
                            <option value="{{ $caisse->id }}" data-paroisse-id="{{ $caisse->paroisse_id }}" @selected(($selectedCaisseId ?? null) == $caisse->id)>{{ $caisse->nom }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="lg:col-span-12 flex flex-wrap gap-2 justify-end border-t border-slate-200/80 pt-3 dark:border-slate-600/60 lg:pt-4">
                <button type="button" id="er-btn-calculate" class="adventiste-btn-primary">
                    <i class="fas fa-calculator me-2" aria-hidden="true"></i>Calculer
                </button>
                <a href="{{ route('financial-reports.expenses') }}" class="adventiste-btn-secondary no-underline inline-flex items-center">Réinitialiser</a>
            </div>
        </form>
    </div>

    <div class="mb-6 border-b border-slate-200 dark:border-slate-600">
        <nav class="flex flex-wrap gap-1 -mb-px" aria-label="Onglets rapport dépenses">
            @foreach ($tabs as $key => $tab)
                <button type="button"
                    class="er-tab-btn inline-flex items-center gap-2 px-4 py-3 text-sm font-medium border-b-2 transition-colors {{ $activeTab === $key ? 'border-emerald-500 text-emerald-700 dark:text-emerald-400' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200' }}"
                    data-tab="{{ $key }}">
                    <i class="fas {{ $tab['icon'] }}" aria-hidden="true"></i>{{ $tab['label'] }}
                </button>
            @endforeach
        </nav>
    </div>

    <div id="er-panel-synthese" class="er-tab-panel {{ $activeTab !== 'synthese' ? 'hidden' : '' }}">
        <div id="er-synthese-root">
            @if ($summaryReport ?? null)
                @include('financial-reports.partials.expenses-report-summary', [
                    'report' => $summaryReport,
                    'dateDebut' => $dateDebut,
                    'dateFin' => $dateFin,
                    'selectedCaisseId' => $selectedCaisseId ?? null,
                    'selectedExpenseTypeId' => $selectedExpenseTypeId ?? null,
                ])
            @else
                <div id="er-synthese-placeholder" class="adventiste-card-pro-static p-10 text-center">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400">
                        <i class="fas fa-wallet text-xl" aria-hidden="true"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-100 m-0">Paramétrez le rapport</h3>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400 max-w-lg mx-auto m-0">Choisissez la période puis cliquez sur <strong class="font-medium">Calculer</strong>.</p>
                </div>
            @endif
        </div>
        @can('generate_financial_reports')
            <form id="er-store-form" method="POST" action="{{ route('financial-reports.expenses.store') }}" class="{{ ($summaryReport ?? null) && $activeTab !== 'capital' ? 'flex' : 'hidden' }} mt-6 justify-end">
                @csrf
                <input type="hidden" name="paroisse_id" id="er-store-paroisse" value="{{ $selectedParoisseId }}">
                <input type="hidden" name="date_debut" id="er-store-debut" value="{{ $dateDebut }}">
                <input type="hidden" name="date_fin" id="er-store-fin" value="{{ $dateFin }}">
                <input type="hidden" name="caisse_id" id="er-store-caisse" value="{{ $selectedCaisseId ?? '' }}">
                <input type="hidden" name="expense_type_id" id="er-store-type" value="{{ $selectedExpenseTypeId ?? '' }}">
                <button type="submit" class="adventiste-btn-primary">
                    <i class="fas fa-save me-2" aria-hidden="true"></i>Enregistrer le rapport
                </button>
            </form>
        @endcan
    </div>

    <div id="er-panel-detail" class="er-tab-panel {{ $activeTab !== 'detail' ? 'hidden' : '' }}">
        <div id="er-detail-root">
            @if ($summaryReport ?? null)
                @include('financial-reports.partials.expenses-report-detail', ['report' => $summaryReport])
            @else
                <div class="adventiste-card-pro-static p-10 text-center text-slate-500 dark:text-slate-400">
                    Lancez un calcul depuis l'onglet Synthèse ou cliquez sur Calculer.
                </div>
            @endif
        </div>
    </div>

    <div id="er-panel-capital" class="er-tab-panel {{ $activeTab !== 'capital' ? 'hidden' : '' }}">
        @include('financial-reports.partials.expenses-report-capital', ['capitalReport' => $capitalReport ?? null])
    </div>

    @push('scripts')
        <script>
            (function () {
                var calculateUrl = @json(route('financial-reports.expenses.calculate'));
                var expensesRoute = @json(route('financial-reports.expenses'));
                var printRoute = @json(route('financial-reports.expenses.print'));
                var capitalPrintRoute = @json(route('financial-reports.capital-usage.print'));
                var token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                var isSuperAdmin = @json(auth()->user()->hasRole('super_admin'));
                var activeTab = @json($activeTab);
                var summaryPrintUrl = @json($summaryPrintUrl);
                var capitalPrintUrl = @json($capitalPrintUrl);
                var hasSummaryReport = @json((bool) ($summaryReport ?? null));

                function paroisseId() {
                    var el = document.getElementById('er_paroisse');
                    if (el) return el.value ? parseInt(el.value, 10) : null;
                    var h = document.getElementById('er_paroisse_hidden');
                    return h && h.value ? parseInt(h.value, 10) : null;
                }

                function filterPayload() {
                    var pid = paroisseId();
                    var d0 = document.getElementById('er_date_debut')?.value;
                    var d1 = document.getElementById('er_date_fin')?.value;
                    var caisseEl = document.getElementById('er_caisse');
                    var caisseId = caisseEl && !caisseEl.disabled ? caisseEl.value : '';
                    var etid = document.getElementById('er_expense_type')?.value || '';
                    return { pid: pid, d0: d0, d1: d1, caisseId: caisseId, etid: etid };
                }

                function buildSummaryPrintUrl(payload) {
                    var params = new URLSearchParams({
                        paroisse_id: String(payload.pid),
                        date_debut: payload.d0,
                        date_fin: payload.d1
                    });
                    if (payload.caisseId) params.set('caisse_id', payload.caisseId);
                    if (payload.etid) params.set('expense_type_id', payload.etid);
                    return printRoute + '?' + params.toString();
                }

                function buildCapitalPrintUrl(payload) {
                    return capitalPrintRoute + '?' + new URLSearchParams({
                        paroisse_id: String(payload.pid),
                        date_debut: payload.d0,
                        date_fin: payload.d1
                    }).toString();
                }

                function updatePrintLink() {
                    var printLink = document.getElementById('er-print-link');
                    if (!printLink) return;
                    var f = filterPayload();
                    var url = null;

                    if (activeTab === 'capital' && f.pid && f.d0 && f.d1) {
                        url = capitalPrintUrl || buildCapitalPrintUrl(f);
                    } else if ((activeTab === 'synthese' || activeTab === 'detail') && hasSummaryReport && f.pid) {
                        url = summaryPrintUrl || buildSummaryPrintUrl(f);
                    }

                    if (url) {
                        printLink.href = url;
                        printLink.classList.remove('hidden');
                    } else {
                        printLink.classList.add('hidden');
                    }
                }

                function toggleAdvancedFilters(show) {
                    var block = document.getElementById('er-filter-advanced');
                    if (block) block.classList.toggle('hidden', !show);
                }

                function toggleStoreForm(show) {
                    var form = document.getElementById('er-store-form');
                    if (form) form.classList.toggle('hidden', !show);
                    if (form) form.classList.toggle('flex', show);
                }

                function switchTab(tab) {
                    activeTab = tab;
                    document.getElementById('er_active_tab').value = tab;
                    document.querySelectorAll('.er-tab-btn').forEach(function (btn) {
                        var isActive = btn.getAttribute('data-tab') === tab;
                        btn.classList.toggle('border-emerald-500', isActive);
                        btn.classList.toggle('text-emerald-700', isActive);
                        btn.classList.toggle('dark:text-emerald-400', isActive);
                        btn.classList.toggle('border-transparent', !isActive);
                        btn.classList.toggle('text-slate-500', !isActive);
                    });
                    document.querySelectorAll('.er-tab-panel').forEach(function (panel) {
                        panel.classList.add('hidden');
                    });
                    document.getElementById('er-panel-' + tab)?.classList.remove('hidden');
                    toggleAdvancedFilters(tab !== 'capital');
                    toggleStoreForm(tab === 'synthese' && hasSummaryReport);
                    updatePrintLink();
                }

                document.querySelectorAll('.er-tab-btn').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        switchTab(btn.getAttribute('data-tab'));
                    });
                });

                document.querySelectorAll('.er-shortcut').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        document.getElementById('er_date_debut').value = btn.getAttribute('data-debut');
                        document.getElementById('er_date_fin').value = btn.getAttribute('data-fin');
                    });
                });

                if (isSuperAdmin) {
                    document.getElementById('er_paroisse')?.addEventListener('change', function () {
                        var pid = paroisseId();
                        var caisse = document.getElementById('er_caisse');
                        if (!caisse) return;
                        caisse.disabled = !pid;
                        Array.prototype.forEach.call(caisse.options, function (opt) {
                            if (!opt.value) { opt.hidden = false; return; }
                            var optPid = opt.getAttribute('data-paroisse-id');
                            opt.hidden = !!(pid && optPid && String(optPid) !== String(pid));
                        });
                    });
                }

                function updateStoreForm(payload) {
                    var form = document.getElementById('er-store-form');
                    if (!form) return;
                    document.getElementById('er-store-paroisse').value = payload.pid;
                    document.getElementById('er-store-debut').value = payload.d0;
                    document.getElementById('er-store-fin').value = payload.d1;
                    document.getElementById('er-store-caisse').value = payload.caisseId || '';
                    document.getElementById('er-store-type').value = payload.etid || '';
                    hasSummaryReport = true;
                    summaryPrintUrl = buildSummaryPrintUrl(payload);
                    toggleStoreForm(activeTab === 'synthese');
                    updatePrintLink();
                }

                document.getElementById('er-btn-calculate')?.addEventListener('click', function () {
                    var f = filterPayload();
                    if (!f.pid) { alert('Veuillez sélectionner une paroisse.'); return; }
                    if (!f.d0 || !f.d1) { alert('Veuillez renseigner les dates.'); return; }

                    if (activeTab === 'capital') {
                        var params = new URLSearchParams({
                            tab: 'capital',
                            paroisse_id: String(f.pid),
                            date_debut: f.d0,
                            date_fin: f.d1
                        });
                        window.location = expensesRoute + '?' + params.toString();
                        return;
                    }

                    var btn = document.getElementById('er-btn-calculate');
                    var prev = btn.innerHTML;
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Calcul…';

                    var body = { paroisse_id: f.pid, date_debut: f.d0, date_fin: f.d1 };
                    if (f.caisseId) body.caisse_id = parseInt(f.caisseId, 10);
                    if (f.etid) body.expense_type_id = parseInt(f.etid, 10);

                    fetch(calculateUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify(body)
                    }).then(function (r) {
                        return r.json().then(function (data) {
                            if (!r.ok) throw new Error(data.message || 'Erreur ' + r.status);
                            return data;
                        });
                    }).then(function (data) {
                        var synthRoot = document.getElementById('er-synthese-root');
                        if (synthRoot) synthRoot.innerHTML = data.html;
                        var detailRoot = document.getElementById('er-detail-root');
                        if (detailRoot) detailRoot.innerHTML = data.detail_html;
                        var per = document.getElementById('er-period-display');
                        if (per && data.period_label) {
                            per.textContent = 'Période : ' + data.period_label;
                            per.classList.remove('hidden');
                        }
                        updateStoreForm(f);
                    }).catch(function (e) {
                        alert(e.message || 'Erreur lors du calcul.');
                    }).finally(function () {
                        btn.disabled = false;
                        btn.innerHTML = prev;
                    });
                });

                switchTab(activeTab);
            })();
        </script>
    @endpush
@endsection
