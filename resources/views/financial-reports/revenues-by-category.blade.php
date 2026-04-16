@extends('layouts.app')

@section('title', 'Rapport par catégories de recettes — Catholique')
@section('page-title', 'Rapport par catégories de recettes')

@section('page-title-info')
    Filtrez par paroisse, période, catégorie, puis éventuellement un <strong class="font-semibold">type</strong> (liste dépendante de la catégorie) : totaux <strong class="font-semibold">semaine (lun.–sam.)</strong> et <strong class="font-semibold">dimanche</strong>, détail par jour, liste avec période ; la <strong class="font-semibold">répartition par catégorie</strong> n’apparaît que si vous n’avez pas choisi de catégorie (sinon le filtre suffit).
    <span id="rbc-period-display" class="hidden block mt-1 text-slate-500 dark:text-slate-400"></span>
@endsection

@section('btn-create')
    <div class="flex flex-wrap items-center gap-2">
        <a id="rbc-pdf-link" href="#" target="_blank" rel="noopener noreferrer" class="hidden inline-flex items-center gap-2 rounded-lg border border-rose-200 dark:border-rose-800/60 bg-rose-50 dark:bg-rose-950/40 px-3 py-2 text-sm font-semibold text-rose-800 dark:text-rose-200 hover:bg-rose-100 dark:hover:bg-rose-900/50 transition-colors no-underline">
            <i class="fas fa-file-pdf" aria-hidden="true"></i>Exporter PDF
        </a>
        <a href="{{ route('financial-reports.expenses-by-category') }}" class="adventiste-btn-secondary text-sm no-underline">
            <i class="fas fa-receipt me-1.5" aria-hidden="true"></i>Dépenses par catégorie
        </a>
        <a href="{{ route('financial-reports.index') }}" class="adventiste-btn-secondary text-sm no-underline">
            <i class="fas fa-chart-pie me-1.5" aria-hidden="true"></i>Hub rapports
        </a>
        <a href="{{ route('financial-reports.list') }}" class="adventiste-btn-secondary text-sm no-underline">Rapports enregistrés</a>
    </div>
@endsection

@section('content')
    @php
        $today = now()->format('Y-m-d');
        $debMois = now()->startOfMonth()->format('Y-m-d');
        $finMois = now()->endOfMonth()->format('Y-m-d');
        $debSem = now()->startOfWeek()->format('Y-m-d');
        $finSem = now()->endOfWeek()->format('Y-m-d');
        $ajaxRoutes = $ajaxRoutes ?? [
            'categories' => route('financial-reports.revenues-by-category.revenue-categories'),
            'types' => route('financial-reports.revenues-by-category.revenue-types'),
            'calculate' => route('financial-reports.revenues-by-category.calculate'),
        ];
    @endphp

    <div class="rounded-xl border border-sky-200/90 dark:border-sky-800/50 bg-sky-50/90 dark:bg-sky-950/25 px-4 py-3 mb-6 text-sm text-sky-950 dark:text-sky-100 leading-relaxed">
        <p class="m-0 flex gap-2">
            <i class="fas fa-info-circle mt-0.5 shrink-0 text-sky-600 dark:text-sky-400" aria-hidden="true"></i>
            <span>Les montants regroupent les recettes de la paroisse sur l’intervalle choisi. Le calcul du rapport se fait <strong class="font-semibold">sans recharger la page</strong> (pas d’URL longue). Les raccourcis de dates remplissent les champs puis vous pouvez cliquer sur <strong class="font-semibold">Calculer</strong>.</span>
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
                    <button type="button" class="adventiste-btn-secondary text-xs py-2 px-3 rbc-shortcut" data-debut="{{ $today }}" data-fin="{{ $today }}">Aujourd’hui</button>
                    <button type="button" class="adventiste-btn-secondary text-xs py-2 px-3 rbc-shortcut" data-debut="{{ $debSem }}" data-fin="{{ $finSem }}">Semaine</button>
                    <button type="button" class="adventiste-btn-secondary text-xs py-2 px-3 rbc-shortcut" data-debut="{{ $debMois }}" data-fin="{{ $finMois }}">Mois en cours</button>
                </div>
            </div>
        </div>

        <form id="filterForm" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-3 lg:gap-4 lg:items-start" onsubmit="return false;">
            @if (auth()->user()->hasRole('super_admin') && $paroisses->count() > 0)
                <div class="lg:col-span-3">
                    <label for="rbc_paroisse" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Paroisse <span class="text-red-500">*</span></label>
                    <select id="rbc_paroisse" name="paroisse_id" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm" required>
                        <option value="">Sélectionner…</option>
                        @foreach ($paroisses as $p)
                            <option value="{{ $p->id }}" @selected($selectedParoisseId == $p->id)>{{ $p->nom }}</option>
                        @endforeach
                    </select>
                </div>
            @else
                <input type="hidden" id="rbc_paroisse_hidden" name="paroisse_id" value="{{ auth()->user()->paroisse_id }}">
            @endif

            <div class="lg:col-span-2">
                <label for="rbc_date_debut" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Date début</label>
                <input type="date" id="rbc_date_debut" name="date_debut" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm" value="{{ $dateDebut ?? $debMois }}" required>
            </div>
            <div class="lg:col-span-2">
                <label for="rbc_date_fin" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Date fin</label>
                <input type="date" id="rbc_date_fin" name="date_fin" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm" value="{{ $dateFin ?? $finMois }}" required>
            </div>
            <div class="lg:col-span-2">
                <label for="rbc_category" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Catégorie</label>
                <select id="rbc_category" name="revenue_category_id" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm" @if (auth()->user()->hasRole('super_admin') && ! $selectedParoisseId) disabled @endif>
                    <option value="">Toutes les catégories</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="lg:col-span-3">
                <label for="rbc_type" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300" title="Activé dès qu’une catégorie précise est choisie (pas pour « Toutes les catégories »).">Type</label>
                <select id="rbc_type" name="revenue_type_id" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-slate-100 dark:bg-slate-900/50 text-slate-500 dark:text-slate-400 px-3 py-2.5 text-sm cursor-not-allowed border-dashed" disabled aria-describedby="rbc-filter-hint">
                    <option value="">Choisir une catégorie…</option>
                </select>
            </div>
            <div id="rbc-filter-hint" class="lg:col-span-12 rounded-lg border border-slate-200/90 bg-slate-50/80 px-3 py-2 text-xs leading-relaxed text-slate-600 dark:border-slate-600/60 dark:bg-slate-800/40 dark:text-slate-400">
                @if (auth()->user()->hasRole('super_admin'))
                    <span class="block sm:inline">Choisissez d’abord une <strong class="font-medium text-slate-700 dark:text-slate-300">paroisse</strong> pour activer catégorie et type.</span>
                    <span class="hidden sm:inline text-slate-400 dark:text-slate-500" aria-hidden="true"> · </span>
                @endif
                <span class="block sm:inline">Le filtre <strong class="font-medium text-slate-700 dark:text-slate-300">Type</strong> s’active lorsqu’une <strong class="font-medium text-slate-700 dark:text-slate-300">catégorie</strong> précise est choisie (liste chargée automatiquement).</span>
            </div>
            <div class="lg:col-span-12 flex flex-wrap gap-2 justify-end border-t border-slate-200/80 pt-3 dark:border-slate-600/60 lg:pt-4">
                <button type="button" id="rbc-btn-calculate" class="adventiste-btn-primary">
                    <i class="fas fa-calculator me-2" aria-hidden="true"></i>Calculer
                </button>
                <a href="{{ route('financial-reports.revenues-by-category') }}" class="adventiste-btn-secondary no-underline inline-flex items-center">Réinitialiser</a>
            </div>
        </form>
    </div>

    <div id="rbc-report-root">
        <div id="rbc-report-placeholder" class="adventiste-card-pro-static p-10 text-center">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400">
                <i class="fas fa-chart-pie text-xl" aria-hidden="true"></i>
            </div>
            <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-100 m-0">Paramétrez le rapport</h3>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400 max-w-lg mx-auto m-0">
                @if (auth()->user()->hasRole('super_admin'))
                    Choisissez une <strong class="font-medium">paroisse</strong>, les dates, puis cliquez sur <strong class="font-medium">Calculer</strong>.
                @else
                    Choisissez la période, éventuellement une <strong class="font-medium">catégorie</strong> et un <strong class="font-medium">type</strong>, puis <strong class="font-medium">Calculer</strong>.
                @endif
            </p>
        </div>
    </div>

    @push('scripts')
        <script>
            (function () {
                var routes = @json($ajaxRoutes);
                var token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                var isSuperAdmin = @json(auth()->user()->hasRole('super_admin'));

                function paroisseId() {
                    var el = document.getElementById('rbc_paroisse');
                    if (el) {
                        return el.value ? parseInt(el.value, 10) : null;
                    }
                    var h = document.getElementById('rbc_paroisse_hidden');
                    return h && h.value ? parseInt(h.value, 10) : null;
                }

                function setTypeDisabled(disabled) {
                    var t = document.getElementById('rbc_type');
                    if (!t) return;
                    t.disabled = !!disabled;
                    t.classList.toggle('bg-slate-100', !!disabled);
                    t.classList.toggle('dark:bg-slate-900/50', !!disabled);
                    t.classList.toggle('text-slate-500', !!disabled);
                    t.classList.toggle('cursor-not-allowed', !!disabled);
                    t.classList.toggle('border-dashed', !!disabled);
                    t.classList.toggle('bg-white', !disabled);
                    t.classList.toggle('dark:bg-slate-800', !disabled);
                }

                function fillTypes(types, selectedId) {
                    var t = document.getElementById('rbc_type');
                    if (!t) return;
                    t.innerHTML = '';
                    var o0 = document.createElement('option');
                    o0.value = '';
                    o0.textContent = 'Tous les types';
                    t.appendChild(o0);
                    (types || []).forEach(function (row) {
                        var o = document.createElement('option');
                        o.value = row.id;
                        o.textContent = row.nom;
                        if (selectedId && String(selectedId) === String(row.id)) {
                            o.selected = true;
                        }
                        t.appendChild(o);
                    });
                    setTypeDisabled(false);
                    t.setAttribute('name', 'revenue_type_id');
                }

                function resetTypesPlaceholder() {
                    var t = document.getElementById('rbc_type');
                    if (!t) return;
                    t.innerHTML = '';
                    var o = document.createElement('option');
                    o.value = '';
                    o.textContent = 'Choisir une catégorie…';
                    t.appendChild(o);
                    t.value = '';
                    t.removeAttribute('name');
                    setTypeDisabled(true);
                }

                function fetchJson(url, options) {
                    return fetch(url, Object.assign({
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                    }, options || {})).then(function (r) {
                        return r.json().then(function (data) {
                            if (!r.ok) {
                                throw new Error(data.message || ('Erreur ' + r.status));
                            }
                            return data;
                        });
                    });
                }

                function loadCategories(pid) {
                    var cat = document.getElementById('rbc_category');
                    if (!cat) return Promise.resolve();
                    if (!pid) {
                        cat.innerHTML = '<option value="">Toutes les catégories</option>';
                        cat.disabled = true;
                        resetTypesPlaceholder();
                        return Promise.resolve();
                    }
                    var url = routes.categories + '?paroisse_id=' + encodeURIComponent(pid);
                    return fetchJson(url).then(function (data) {
                        cat.innerHTML = '<option value="">Toutes les catégories</option>';
                        (data.categories || []).forEach(function (c) {
                            var o = document.createElement('option');
                            o.value = c.id;
                            o.textContent = c.nom;
                            cat.appendChild(o);
                        });
                        cat.disabled = false;
                        resetTypesPlaceholder();
                    });
                }

                function loadTypes(pid, categoryId) {
                    if (!categoryId) {
                        resetTypesPlaceholder();
                        return Promise.resolve();
                    }
                    var url = routes.types + '?paroisse_id=' + encodeURIComponent(pid) + '&revenue_category_id=' + encodeURIComponent(categoryId);
                    return fetchJson(url).then(function (data) {
                        fillTypes(data.types, null);
                    });
                }

                document.querySelectorAll('.rbc-shortcut').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        var d0 = btn.getAttribute('data-debut');
                        var d1 = btn.getAttribute('data-fin');
                        var i0 = document.getElementById('rbc_date_debut');
                        var i1 = document.getElementById('rbc_date_fin');
                        if (i0 && d0) i0.value = d0;
                        if (i1 && d1) i1.value = d1;
                    });
                });

                if (isSuperAdmin) {
                    document.getElementById('rbc_paroisse')?.addEventListener('change', function () {
                        var pid = paroisseId();
                        var cat = document.getElementById('rbc_category');
                        if (cat) cat.value = '';
                        loadCategories(pid);
                    });
                }

                document.getElementById('rbc_category')?.addEventListener('change', function () {
                    var pid = paroisseId();
                    var cid = this.value;
                    if (!pid) return;
                    loadTypes(pid, cid);
                });

                document.getElementById('rbc-btn-calculate')?.addEventListener('click', function () {
                    var pid = paroisseId();
                    var d0 = document.getElementById('rbc_date_debut')?.value;
                    var d1 = document.getElementById('rbc_date_fin')?.value;
                    var cat = document.getElementById('rbc_category');
                    var cid = cat && !cat.disabled ? cat.value : '';
                    var tidEl = document.getElementById('rbc_type');
                    var tid = (tidEl && !tidEl.disabled && tidEl.getAttribute('name')) ? tidEl.value : '';

                    if (!pid) {
                        alert('Veuillez sélectionner une paroisse.');
                        return;
                    }
                    if (!d0 || !d1) {
                        alert('Veuillez renseigner les dates de début et fin.');
                        return;
                    }

                    var btn = document.getElementById('rbc-btn-calculate');
                    var prev = btn.innerHTML;
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2" aria-hidden="true"></i>Calcul…';

                    var payload = {
                        paroisse_id: pid,
                        date_debut: d0,
                        date_fin: d1
                    };
                    if (cid) payload.revenue_category_id = parseInt(cid, 10);
                    if (tid) payload.revenue_type_id = parseInt(tid, 10);

                    fetch(routes.calculate, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify(payload)
                    }).then(function (r) {
                        return r.json().then(function (data) {
                            if (!r.ok) {
                                throw new Error(data.message || (data.errors ? JSON.stringify(data.errors) : ('Erreur ' + r.status)));
                            }
                            return data;
                        });
                    }).then(function (data) {
                        var root = document.getElementById('rbc-report-root');
                        if (root) {
                            root.innerHTML = data.html;
                        }
                        var pdf = document.getElementById('rbc-pdf-link');
                        if (pdf && data.pdf_url) {
                            pdf.href = data.pdf_url;
                            pdf.classList.remove('hidden');
                        }
                        var per = document.getElementById('rbc-period-display');
                        if (per && data.period_label) {
                            per.textContent = 'Période affichée : ' + data.period_label + ' · Édité le ' + new Date().toLocaleString('fr-FR');
                            per.classList.remove('hidden');
                        }
                    }).catch(function (e) {
                        alert(e.message || 'Erreur lors du calcul du rapport.');
                    }).finally(function () {
                        btn.disabled = false;
                        btn.innerHTML = prev;
                    });
                });
            })();
        </script>
    @endpush
@endsection
