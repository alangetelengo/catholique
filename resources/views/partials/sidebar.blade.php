@php
    $navBase = 'flex items-center gap-3 px-5 py-3 rounded-xl text-white/85 hover:bg-[rgba(212,168,75,0.12)] hover:text-white transition-all border border-transparent hover:border-[rgba(212,168,75,0.15)]';
    $isFinanceRoute = request()->routeIs('revenues.*', 'revenue-reports.*', 'charges-fixes-reports.*', 'reports.quete.*', 'expenses.*');
    $isInventoryRoute = request()->routeIs('inventories.*', 'inventaire-magasin.*', 'inventaire-patrimoine.*');
    $isRevenueRoute = request()->routeIs('revenues.*', 'revenue-reports.*');
    $isExpenseRoute = request()->routeIs('expenses.*');
    $isSettingsRoute = request()->routeIs('revenue-categories.*', 'revenue-types.*');
    $isReportsRoute = request()->routeIs('revenue-reports.*', 'reports.quete.*', 'popote-reports.*', 'charges-fixes-reports.*');
    $isStatsRoute = request()->routeIs('financial-statistics.*');
    $isDashboardRoute = request()->routeIs('home');
    $isParoisseRoute = request()->routeIs('paroisses.*');
    $isEventsRoute = request()->routeIs('events.*');
    $isGroupsRoute = request()->routeIs('groups.*');
    $isConfigRoute = request()->routeIs('configurations.*');
    $isAdminAccessRoute = request()->routeIs('roles.*', 'permissions.*');
    $isMembersRoute = request()->routeIs('members.*');
    $isSacramentsRoute = request()->routeIs('sacraments.*');
    $canSacramentsNav = auth()->check() && (
        auth()->user()->can('view_baptisms')
        || auth()->user()->can('view_confirmations')
        || auth()->user()->can('view_communions')
        || auth()->user()->can('view_marriages')
        || auth()->user()->can('view_funerals')
    );
@endphp

<aside class="sidebar theme-sidebar fixed top-20 left-0 w-[250px] h-[calc(100vh-80px)] z-998 flex flex-col overflow-hidden transition-all duration-300">
    <nav class="flex-1 py-5 px-4 overflow-y-auto overflow-x-hidden sidebar-nav-scroll">
        <ul class="space-y-1">
            <li><a href="{{ route('home') }}" class="{{ $isDashboardRoute ? $navBase . ' nav-link-active' : $navBase }}"><span>📊</span><span class="nav-text">Tableau de bord</span></a></li>

            @can('view_members')
            <li><a href="{{ route('members.index') }}" class="{{ $isMembersRoute ? $navBase . ' nav-link-active' : $navBase }}"><span>👥</span><span class="nav-text">Membres</span></a></li>
            @endcan
            @if($canSacramentsNav)
            <li><a href="{{ route('sacraments.index', ['type' => 'bapteme']) }}" class="{{ $isSacramentsRoute ? $navBase . ' nav-link-active' : $navBase }}"><span>💒</span><span class="nav-text">Sacrements</span></a></li>
            @endif
            @can('view_events')
            <li><a href="{{ route('events.index') }}" class="{{ $isEventsRoute ? $navBase . ' nav-link-active' : $navBase }}"><span>📅</span><span class="nav-text">Événements</span></a></li>
            @endcan
            @can('view_groups')
            <li><a href="{{ route('groups.index') }}" class="{{ $isGroupsRoute ? $navBase . ' nav-link-active' : $navBase }}"><span>🙏</span><span class="nav-text">Groupes</span></a></li>
            @endcan

            <li class="pt-2">
                <details class="group sidebar-submenu" @if($isFinanceRoute) open @endif>
                    <summary class="{{ $isFinanceRoute ? $navBase . ' nav-link-active' : $navBase }} cursor-pointer list-none">
                        <span>💰</span>
                        <span class="nav-text">Finances</span>
                        <span class="sidebar-chevron" aria-hidden="true"></span>
                    </summary>
                    <ul class="sidebar-sub-menu mt-1" role="list" data-accordion-group="finances">
                        <li class="sidebar-sub-item">
                            <details class="sidebar-sub-nested" data-accordion-item="finances" @if($isRevenueRoute) open @endif>
                                <summary class="sidebar-sub-summary {{ $isRevenueRoute ? 'is-active' : '' }}">
                                    <span>Recettes</span>
                                    <span class="sidebar-chevron sidebar-chevron-nested" aria-hidden="true"></span>
                                </summary>
                                <ul class="sidebar-sub-menu sidebar-sub-menu-nested mt-0.5" role="list">
                                    <li class="sidebar-sub-item"><a href="{{ route('revenues.index') }}" class="sidebar-sub-link {{ request()->routeIs('revenues.index') ? 'is-active' : '' }}">Toutes les recettes</a></li>
                                    <li class="sidebar-sub-item"><a href="{{ route('revenues.create') }}" class="sidebar-sub-link {{ request()->routeIs('revenues.create') ? 'is-active' : '' }}">Ajouter une recette</a></li>
                                    <li class="sidebar-sub-item"><a href="{{ route('reports.quete.index') }}" class="sidebar-sub-link {{ request()->routeIs('reports.quete.*') ? 'is-active' : '' }}">Rapport quête ordinaire</a></li>
                                    <li class="sidebar-sub-item"><a href="{{ route('revenue-reports.index') }}" class="sidebar-sub-link {{ request()->routeIs('revenue-reports.*') ? 'is-active' : '' }}">Rapports recettes</a></li>
                                </ul>
                            </details>
                        </li>
                        <li class="sidebar-sub-item">
                            <details class="sidebar-sub-nested" data-accordion-item="finances" @if($isExpenseRoute) open @endif>
                                <summary class="sidebar-sub-summary {{ $isExpenseRoute ? 'is-active' : '' }}">
                                    <span>Dépenses</span>
                                    <span class="sidebar-chevron sidebar-chevron-nested" aria-hidden="true"></span>
                                </summary>
                                <ul class="sidebar-sub-menu sidebar-sub-menu-nested mt-0.5" role="list">
                                    <li class="sidebar-sub-item"><a href="{{ route('expenses.index') }}" class="sidebar-sub-link {{ request()->routeIs('expenses.index') ? 'is-active' : '' }}">Toutes les dépenses</a></li>
                                    <li class="sidebar-sub-item"><a href="{{ route('expenses.create') }}" class="sidebar-sub-link {{ request()->routeIs('expenses.create') ? 'is-active' : '' }}">Ajouter une dépense</a></li>
                                    <li class="sidebar-sub-item"><a href="{{ route('charges-fixes-reports.index') }}" class="sidebar-sub-link {{ request()->routeIs('charges-fixes-reports.*') ? 'is-active' : '' }}">Rapports charges fixes</a></li>
                                </ul>
                            </details>
                        </li>
                    </ul>
                </details>
            </li>

            {{-- Inventaires et paramètres recettes : hors du bloc Finances (Recettes / Dépenses uniquement) --}}
            <li>
                <details class="group" @if($isInventoryRoute) open @endif>
                    <summary class="{{ $isInventoryRoute ? $navBase . ' nav-link-active' : $navBase }} cursor-pointer list-none">
                        <span>📦</span>
                        <span class="nav-text">Inventaires</span>
                        <span class="sidebar-chevron" aria-hidden="true"></span>
                    </summary>
                    <ul class="sidebar-sub-menu mt-1" role="list">
                        <li class="sidebar-sub-item"><a href="{{ route('inventories.index') }}" class="sidebar-sub-link {{ request()->routeIs('inventories.index', 'inventories.edit') ? 'is-active' : '' }}">Articles (général)</a></li>
                        <li class="sidebar-sub-item"><a href="{{ route('inventories.create') }}" class="sidebar-sub-link {{ request()->routeIs('inventories.create') ? 'is-active' : '' }}">Nouvel article (général)</a></li>
                        <li class="sidebar-sub-item"><a href="{{ route('inventaire-magasin.index') }}" class="sidebar-sub-link {{ request()->routeIs('inventaire-magasin.*') ? 'is-active' : '' }}">Magasin (denrées)</a></li>
                        <li class="sidebar-sub-item"><a href="{{ route('inventaire-patrimoine.index') }}" class="sidebar-sub-link {{ request()->routeIs('inventaire-patrimoine.*') ? 'is-active' : '' }}">Patrimoine</a></li>
                    </ul>
                </details>
            </li>
            <li>
                <details class="group" @if($isSettingsRoute) open @endif>
                    <summary class="{{ $isSettingsRoute ? $navBase . ' nav-link-active' : $navBase }} cursor-pointer list-none">
                        <span>🏷️</span>
                        <span class="nav-text">Paramètres</span>
                        <span class="sidebar-chevron" aria-hidden="true"></span>
                    </summary>
                    <ul class="sidebar-sub-menu mt-1" role="list">
                        <li class="sidebar-sub-item"><a href="{{ route('revenue-categories.index') }}" class="sidebar-sub-link {{ request()->routeIs('revenue-categories.*') ? 'is-active' : '' }}">Catégories recettes</a></li>
                        <li class="sidebar-sub-item"><a href="{{ route('revenue-types.index') }}" class="sidebar-sub-link {{ request()->routeIs('revenue-types.*') ? 'is-active' : '' }}">Types recettes</a></li>
                    </ul>
                </details>
            </li>

            <li>
                <details class="group" @if($isReportsRoute) open @endif>
                    <summary class="{{ $isReportsRoute ? $navBase . ' nav-link-active' : $navBase }} cursor-pointer list-none">
                        <span>📑</span>
                        <span class="nav-text">Rapports</span>
                        <span class="sidebar-chevron" aria-hidden="true"></span>
                    </summary>
                    <ul class="sidebar-sub-menu mt-1">
                        @can('view_financial_reports')
                        <li class="sidebar-sub-item"><a href="{{ route('financial-reports.index') }}" class="sidebar-sub-link {{ request()->routeIs('financial-reports.index') ? 'is-active' : '' }}">Hub rapports financiers</a></li>
                        <li class="sidebar-sub-item"><a href="{{ route('financial-reports.list') }}" class="sidebar-sub-link {{ request()->routeIs('financial-reports.list') ? 'is-active' : '' }}">Liste des rapports</a></li>
                        <li class="sidebar-sub-item"><a href="{{ route('financial-reports.statistics') }}" class="sidebar-sub-link {{ request()->routeIs('financial-reports.statistics') ? 'is-active' : '' }}">Stats rapports</a></li>
                        @endcan
                        <li class="sidebar-sub-item"><a href="{{ route('revenue-reports.create') }}" class="sidebar-sub-link {{ request()->routeIs('revenue-reports.create') ? 'is-active' : '' }}">Générer un rapport</a></li>
                        <li class="sidebar-sub-item"><a href="{{ route('revenue-reports.index') }}" class="sidebar-sub-link {{ request()->routeIs('revenue-reports.index', 'revenue-reports.show', 'revenue-reports.edit') ? 'is-active' : '' }}">Rapports enregistrés</a></li>
                        <li class="sidebar-sub-item"><a href="{{ route('reports.quete.index') }}" class="sidebar-sub-link {{ request()->routeIs('reports.quete.*') ? 'is-active' : '' }}">Rapport Quête ordinaire</a></li>
                        <li class="sidebar-sub-item"><a href="{{ route('popote-reports.index') }}" class="sidebar-sub-link {{ request()->routeIs('popote-reports.*') ? 'is-active' : '' }}">Rapport Subvention Popote</a></li>
                        <li class="sidebar-sub-item"><a href="{{ route('charges-fixes-reports.index') }}" class="sidebar-sub-link {{ request()->routeIs('charges-fixes-reports.*') ? 'is-active' : '' }}">Rapport Charges fixes</a></li>
                    </ul>
                </details>
            </li>

            <li>
                <a href="{{ route('financial-statistics.index') }}" class="{{ $isStatsRoute ? $navBase . ' nav-link-active' : $navBase }}">
                    <span>📈</span>
                    <span class="nav-text">Statistiques</span>
                </a>
            </li>

            @can('manage_paroisses')
            <li><a href="{{ route('paroisses.index') }}" class="{{ $isParoisseRoute ? $navBase . ' nav-link-active' : $navBase }}"><span>⛪</span><span class="nav-text">Paroisses</span></a></li>
            @endcan
            @can('manage_users')
            <li><a href="{{ route('users.index') }}" class="{{ $navBase }}"><span>⚙️</span><span class="nav-text">Utilisateurs</span></a></li>
            @endcan
            @can('view_configuration')
            <li><a href="{{ route('configurations.index') }}" class="{{ $isConfigRoute ? $navBase . ' nav-link-active' : $navBase }}"><span>🔧</span><span class="nav-text">Configuration paroisse</span></a></li>
            @endcan
            @if(auth()->user()->can('manage_roles') || auth()->user()->can('manage_permissions'))
            <li>
                <details class="group" @if($isAdminAccessRoute) open @endif>
                    <summary class="{{ $isAdminAccessRoute ? $navBase . ' nav-link-active' : $navBase }} cursor-pointer list-none">
                        <span>🔐</span>
                        <span class="nav-text">Accès</span>
                        <span class="sidebar-chevron" aria-hidden="true"></span>
                    </summary>
                    <ul class="sidebar-sub-menu mt-1" role="list">
                        @can('manage_roles')
                        <li class="sidebar-sub-item"><a href="{{ route('roles.index') }}" class="sidebar-sub-link {{ request()->routeIs('roles.*') ? 'is-active' : '' }}">Rôles</a></li>
                        @endcan
                        @can('manage_permissions')
                        <li class="sidebar-sub-item"><a href="{{ route('permissions.index') }}" class="sidebar-sub-link {{ request()->routeIs('permissions.*') ? 'is-active' : '' }}">Permissions</a></li>
                        @endcan
                    </ul>
                </details>
            </li>
            @endif
        </ul>
    </nav>

    <div class="shrink-0 p-4 border-t border-[rgba(212,168,75,0.28)] bg-[rgba(0,0,0,0.22)]">
        <p class="px-2 text-center leading-snug nav-text">
            <span class="block text-xs font-bold uppercase tracking-wide text-white drop-shadow-[0_1px_2px_rgba(0,0,0,0.5)]">{{ config('app.name') }}</span>
            @auth
                @php
                    $sidebarUser = auth()->user();
                    $sidebarParoisse = $sidebarUser->paroisse?->nom;
                    if ($sidebarParoisse === null && $sidebarUser->hasRole('super_admin')) {
                        $sidebarParoisse = 'Toutes les paroisses';
                    }
                @endphp
                @if($sidebarParoisse)
                    <span class="block mt-2 text-sm font-bold text-white leading-tight drop-shadow-[0_1px_2px_rgba(0,0,0,0.45)]">{{ $sidebarParoisse }}</span>
                @endif
            @endauth
        </p>
    </div>
</aside>

<style>
.sidebar details > summary::-webkit-details-marker { display: none; }
.sidebar .sidebar-chevron {
    margin-left: auto;
    width: 1.15rem;
    height: 1.15rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
    line-height: 1;
    opacity: 0.95;
    color: #f2c86b;
    border: 1px solid rgba(212, 168, 75, 0.5);
    border-radius: 9999px;
    background: rgba(212, 168, 75, 0.12);
    transition: background-color 0.2s ease, border-color 0.2s ease;
}
.sidebar .sidebar-chevron::before {
    content: '+';
    font-weight: 700;
    transform: translateY(-0.5px);
}
.sidebar details[open] > summary .sidebar-chevron {
    color: #fff3d1;
}
.sidebar details[open] > summary .sidebar-chevron::before {
    content: '-';
}
.sidebar details > summary:hover .sidebar-chevron {
    background: rgba(212, 168, 75, 0.2);
    border-color: rgba(212, 168, 75, 0.7);
}
.sidebar .sidebar-submenu > .sidebar-sub-menu {
    margin-left: 0.5rem;
    border-left: 1px solid rgba(212, 168, 75, 0.25);
    padding-left: 0.5rem;
}
.sidebar .sidebar-sub-nested > summary::-webkit-details-marker {
    display: none;
}
.sidebar .sidebar-sub-summary {
    list-style: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: rgba(255, 255, 255, 0.78);
    font-size: 0.84rem;
    padding: 0.35rem 0.75rem;
    border-radius: 0.5rem;
}
.sidebar .sidebar-chevron-nested {
    width: 1rem;
    height: 1rem;
    font-size: 0.8rem;
}
.sidebar .sidebar-sub-summary:hover {
    color: #ffffff;
    background: rgba(212, 168, 75, 0.1);
}
.sidebar .sidebar-sub-summary.is-active {
    color: #ffffff;
    background: rgba(212, 168, 75, 0.16);
    border: 1px solid rgba(212, 168, 75, 0.35);
}
.sidebar .sidebar-sub-menu-nested {
    margin-left: 0.25rem;
    border-left: 1px dashed rgba(212, 168, 75, 0.2);
    padding-left: 0.35rem;
}
.sidebar .sidebar-sub-item + .sidebar-sub-item {
    margin-top: 0.125rem;
}
.sidebar .sidebar-sub-link {
    display: flex !important;
    align-items: center !important;
    gap: 0.45rem !important;
    color: rgba(255, 255, 255, 0.78) !important;
    font-size: 0.85rem;
    line-height: 1.3;
    padding: 0.42rem 0.75rem 0.42rem 1rem;
    border-radius: 0.5rem;
    text-decoration: none !important;
}
.sidebar .sidebar-sub-link::before {
    content: '';
    width: 0.35rem;
    height: 0.35rem;
    border-radius: 9999px;
    background: rgba(212, 168, 75, 0.7);
    box-shadow: 0 0 0 1px rgba(212, 168, 75, 0.25);
}
.sidebar .sidebar-sub-link:hover {
    color: #ffffff !important;
    background: rgba(212, 168, 75, 0.12);
}
.sidebar .sidebar-sub-link.is-active {
    color: #ffffff !important;
    background: linear-gradient(105deg, rgba(6, 162, 105, 0.55) 0%, rgba(28, 77, 59, 0.55) 100%) !important;
    border: 1px solid rgba(212, 168, 75, 0.3);
}
#main-wrapper.menu-toggle .sidebar details > ul {
    display: none !important;
}
#main-wrapper.menu-toggle .sidebar .sidebar-chevron {
    display: none !important;
}
</style>

<script>
    (function () {
        const group = document.querySelector('[data-accordion-group="finances"]');
        if (!group) return;

        const items = Array.from(group.querySelectorAll('details[data-accordion-item="finances"]'));
        if (!items.length) return;

        items.forEach((item) => {
            item.addEventListener('toggle', function () {
                if (!item.open) return;
                items.forEach((other) => {
                    if (other !== item) {
                        other.open = false;
                    }
                });
            });
        });
    })();
</script>
