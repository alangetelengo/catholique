@extends('layouts.app')

@section('title', 'Configuration de l\'application — Catholique')
@section('page-title', 'Configuration de l\'application')
@section('page-title-info', 'Paroisses, utilisateurs, rôles Spatie, permissions, référentiels recettes et raccourci vers les paramètres paroisse — navigation par onglets sans recharger la page lors du changement d’onglet ou de pagination.')

@section('btn-create')
@endsection

@push('styles')
<style>
    .config-tab-btn { border-radius: 0.5rem 0.5rem 0 0; border: 1px solid transparent; margin-bottom: -1px; }
    .config-tab-btn.is-active {
        background: rgb(255 255 255);
        border-color: rgb(226 232 240);
        border-bottom-color: rgb(255 255 255);
        color: rgb(6 95 70);
        box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    }
    .dark .config-tab-btn.is-active {
        background: rgb(30 41 59);
        border-color: rgb(51 65 85);
        border-bottom-color: rgb(30 41 59);
        color: rgb(167 243 208);
    }
    .config-tab-btn:not(.is-active) {
        color: rgb(71 85 105);
    }
    .dark .config-tab-btn:not(.is-active) { color: rgb(148 163 184); }
    .config-tab-btn:not(.is-active):hover {
        background: rgb(248 250 252);
        color: rgb(15 23 42);
    }
    .dark .config-tab-btn:not(.is-active):hover {
        background: rgb(30 41 59 / 0.8);
        color: rgb(241 245 249);
    }
</style>
@endpush

@section('content')
    @php
        $configIndexUrl = route('application-configuration.index');
    @endphp

    <div
        id="application-configuration-root"
        class="space-y-6"
        data-config-url="{{ $configIndexUrl }}"
        data-active-tab="{{ $activeTab }}"
    >
        <div class="flex flex-wrap gap-2 border-b border-slate-200 dark:border-slate-700 pb-1" role="tablist" aria-label="Sections configuration">
            @foreach ($tabs as $tabKey => $tabLabel)
                <button
                    type="button"
                    role="tab"
                    aria-selected="{{ $activeTab === $tabKey ? 'true' : 'false' }}"
                    data-config-tab-button="{{ $tabKey }}"
                    class="config-tab-btn px-4 py-2.5 text-sm font-semibold transition-colors {{ $activeTab === $tabKey ? 'is-active' : '' }}"
                >
                    {{ $tabLabel }}
                </button>
            @endforeach
        </div>

        <div
            id="app-config-panel"
            class="min-h-[200px]"
            role="tabpanel"
            aria-live="polite"
        >
            {!! $panelHtml !!}
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    var root = document.getElementById('application-configuration-root');
    if (!root) return;

    var baseUrl = root.getAttribute('data-config-url');
    var panelEl = document.getElementById('app-config-panel');

    function setActiveTabButton(tab) {
        document.querySelectorAll('[data-config-tab-button]').forEach(function (b) {
            var on = b.getAttribute('data-config-tab-button') === tab;
            b.setAttribute('aria-selected', on ? 'true' : 'false');
            b.classList.toggle('is-active', on);
        });
    }

    function loadPanel(url, pushState) {
        var u = url instanceof URL ? new URL(url.toString()) : new URL(url, window.location.origin);
        u.searchParams.set('panel_only', '1');
        fetch(u.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html',
            },
            credentials: 'same-origin',
        })
            .then(function (r) {
                if (!r.ok) throw new Error('Erreur ' + r.status);
                return r.text();
            })
            .then(function (html) {
                panelEl.innerHTML = html;
                var clean = new URL(u.toString());
                clean.searchParams.delete('panel_only');
                var t = clean.searchParams.get('tab');
                if (t) setActiveTabButton(t);
                if (pushState) {
                    window.history.pushState({ appConfig: true }, '', clean.pathname + clean.search + clean.hash);
                }
            })
            .catch(function () {
                panelEl.innerHTML = '<div class="adventiste-card-pro-static p-6 text-red-600 dark:text-red-400 text-sm">Impossible de charger ce panneau. Réessayez ou rechargez la page.</div>';
            });
    }

    root.addEventListener('click', function (e) {
        var pag = e.target.closest('a.app-config-page-link');
        if (pag && panelEl.contains(pag)) {
            e.preventDefault();
            loadPanel(pag.href, true);
        }
    });

    root.addEventListener('change', function (e) {
        var sel = e.target.closest('select.app-config-per-page');
        if (!sel || !panelEl.contains(sel)) return;
        var u = new URL(window.location.href);
        u.searchParams.set('per_page', sel.value);
        u.searchParams.set('page', '1');
        loadPanel(u, true);
    });

    root.addEventListener('submit', function (e) {
        var form = e.target.closest('form.app-config-filter-form');
        if (!form || !panelEl.contains(form)) return;
        e.preventDefault();
        var fd = new FormData(form);
        var u = new URL(baseUrl, window.location.origin);
        fd.forEach(function (v, k) {
            if (v !== '' && v != null) u.searchParams.set(k, v);
        });
        u.searchParams.set('page', '1');
        loadPanel(u, true);
    });

    document.querySelectorAll('[data-config-tab-button]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var tab = btn.getAttribute('data-config-tab-button');
            setActiveTabButton(tab);
            var u = new URL(baseUrl, window.location.origin);
            u.searchParams.set('tab', tab);
            u.searchParams.set('page', '1');
            loadPanel(u, true);
        });
    });

    window.addEventListener('popstate', function () {
        if (window.location.pathname.indexOf('application-configuration') === -1) return;
        var u = new URL(window.location.href);
        loadPanel(u, false);
        var tab = u.searchParams.get('tab');
        if (tab) setActiveTabButton(tab);
    });
})();
</script>
@endpush
