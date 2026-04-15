@php
    $navBase = 'flex items-center gap-3 px-5 py-3 rounded-xl text-white/85 hover:bg-[rgba(212,168,75,0.12)] hover:text-white transition-all border border-transparent hover:border-[rgba(212,168,75,0.15)]';
    $isInventoryRoute = request()->routeIs('inventories.*', 'inventaire-magasin.*', 'inventaire-patrimoine.*');
    // Recettes : saisie uniquement ; les rapports recettes (resource) ouvrent le menu « Rapports ».
    $isRevenueRoute = request()->routeIs('revenues.*');
    $isExpenseRoute = request()->routeIs('expenses.*', 'charges-fixes-reports.*');
    $isReportsRoute = request()->routeIs('financial-reports.*', 'revenue-reports.*', 'popote-reports.*', 'charges-fixes-reports.*');
    $isFinancialHub = request()->routeIs(
        'financial-reports.index',
        'financial-reports.popote',
        'financial-reports.popote-print',
        'financial-reports.popote-pdf',
        'financial-reports.charges-fixes',
        'financial-reports.revenues-by-category',
        'financial-reports.revenues-by-category.store',
        'financial-reports.revenues-by-category.pdf',
    );
    $isFinancialList = request()->routeIs('financial-reports.list', 'financial-reports.show', 'financial-reports.download-pdf');
    $isFinancialStats = request()->routeIs('financial-reports.statistics');
    $isFinancialQueteWeekly = request()->routeIs('financial-reports.revenues-weekly', 'financial-reports.revenues-weekly-print', 'financial-reports.revenues-weekly-pdf');
    $isRevenueReportsResource = request()->routeIs('revenue-reports.index', 'revenue-reports.show', 'revenue-reports.edit', 'revenue-reports.print');
    $isRevenueReportCreate = request()->routeIs('revenue-reports.create');
    $isStatsRoute = request()->routeIs('financial-statistics.*');
    $isDashboardRoute = request()->routeIs('home');
    $isAppConfigRoute = request()->routeIs('application-configuration.*');
    $isConfigWorkspaceRoute = request()->routeIs('configurations.workspace');
    $isEventsRoute = request()->routeIs('events.*');
    $isGroupsRoute = request()->routeIs('groups.*');
    $isLegacyAdminRoute = request()->routeIs('paroisses.*', 'users.*', 'roles.*', 'permissions.*', 'revenue-categories.*', 'revenue-types.*', 'configurations.*');
    $isConfigurationNavOpen = $isAppConfigRoute || $isConfigWorkspaceRoute || $isLegacyAdminRoute;
    $isConfigParoissesTab = request()->routeIs('application-configuration.index') && request()->query('tab') === 'paroisses';
    $isConfigOverviewSubActive = $isAppConfigRoute && ! $isConfigParoissesTab;
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
                <details class="group" @if($isRevenueRoute) open @endif>
                    <summary class="{{ $isRevenueRoute ? $navBase . ' nav-link-active' : $navBase }} cursor-pointer list-none">
                        <span>💵</span>
                        <span class="nav-text">Recettes</span>
                        <span class="sidebar-chevron" aria-hidden="true"></span>
                    </summary>
                    <ul class="sidebar-sub-menu mt-1" role="list">
                        <li class="sidebar-sub-item"><a href="{{ route('revenues.index') }}" class="sidebar-sub-link {{ request()->routeIs('revenues.index') ? 'is-active' : '' }}">Toutes les recettes</a></li>
                        <li class="sidebar-sub-item"><a href="{{ route('revenues.create') }}" class="sidebar-sub-link {{ request()->routeIs('revenues.create') ? 'is-active' : '' }}">Ajouter une recette</a></li>
                        {{-- <li class="sidebar-sub-item"><a href="{{ route('revenue-reports.index') }}" class="sidebar-sub-link {{ $isRevenueReportsResource || $isRevenueReportCreate ? 'is-active' : '' }}">Rapports recettes</a></li> --}}
                    </ul>
                </details>
            </li>
            <li>
                <details class="group" @if($isExpenseRoute) open @endif>
                    <summary class="{{ $isExpenseRoute ? $navBase . ' nav-link-active' : $navBase }} cursor-pointer list-none">
                        <span>🧾</span>
                        <span class="nav-text">Dépenses</span>
                        <span class="sidebar-chevron" aria-hidden="true"></span>
                    </summary>
                    <ul class="sidebar-sub-menu mt-1" role="list">
                        <li class="sidebar-sub-item"><a href="{{ route('expenses.index') }}" class="sidebar-sub-link {{ request()->routeIs('expenses.index') ? 'is-active' : '' }}">Toutes les dépenses</a></li>
                        <li class="sidebar-sub-item"><a href="{{ route('expenses.create') }}" class="sidebar-sub-link {{ request()->routeIs('expenses.create') ? 'is-active' : '' }}">Ajouter une dépense</a></li>
                        {{-- <li class="sidebar-sub-item"><a href="{{ route('charges-fixes-reports.index') }}" class="sidebar-sub-link {{ request()->routeIs('charges-fixes-reports.*') ? 'is-active' : '' }}">Rapports charges fixes</a></li> --}}
                    </ul>
                </details>
            </li>

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
                <details class="group" @if($isReportsRoute) open @endif>
                    <summary class="{{ $isReportsRoute ? $navBase . ' nav-link-active' : $navBase }} cursor-pointer list-none">
                        <span>📑</span>
                        <span class="nav-text">Rapports</span>
                        <span class="sidebar-chevron" aria-hidden="true"></span>
                    </summary>
                    <ul class="sidebar-sub-menu mt-1">
                        @can('view_financial_reports')
                        <li class="sidebar-sub-item"><a href="{{ route('financial-reports.index') }}" class="sidebar-sub-link {{ $isFinancialHub ? 'is-active' : '' }}">Hub rapports financiers</a></li>
                        <li class="sidebar-sub-item"><a href="{{ route('financial-reports.list') }}" class="sidebar-sub-link {{ $isFinancialList ? 'is-active' : '' }}">Liste des rapports</a></li>
                        <li class="sidebar-sub-item"><a href="{{ route('financial-reports.statistics') }}" class="sidebar-sub-link {{ $isFinancialStats ? 'is-active' : '' }}">Stats rapports</a></li>
                        {{-- <li class="sidebar-sub-item"><a href="{{ route('financial-reports.revenues-weekly') }}" class="sidebar-sub-link {{ $isFinancialQueteWeekly ? 'is-active' : '' }}">Rapport quête ordinaire</a></li> --}}
                        @endcan
                        {{-- <li class="sidebar-sub-item"><a href="{{ route('revenue-reports.create') }}" class="sidebar-sub-link {{ $isRevenueReportCreate ? 'is-active' : '' }}">Générer un rapport</a></li>
                        <li class="sidebar-sub-item"><a href="{{ route('revenue-reports.index') }}" class="sidebar-sub-link {{ $isRevenueReportsResource ? 'is-active' : '' }}">Rapports enregistrés</a></li>
                        <li class="sidebar-sub-item"><a href="{{ route('popote-reports.index') }}" class="sidebar-sub-link {{ request()->routeIs('popote-reports.*') ? 'is-active' : '' }}">Rapport Subvention Popote</a></li>
                        <li class="sidebar-sub-item"><a href="{{ route('charges-fixes-reports.index') }}" class="sidebar-sub-link {{ request()->routeIs('charges-fixes-reports.*') ? 'is-active' : '' }}">Rapport Charges fixes</a></li> --}}
                    </ul>
                </details>
            </li>

            <li>
                <a href="{{ route('financial-statistics.index') }}" class="{{ $isStatsRoute ? $navBase . ' nav-link-active' : $navBase }}">
                    <span>📈</span>
                    <span class="nav-text">Statistiques</span>
                </a>
            </li>

            @auth
            <li>
                <details class="group" @if($isConfigurationNavOpen) open @endif>
                    <summary class="{{ $isConfigurationNavOpen ? $navBase . ' nav-link-active' : $navBase }} cursor-pointer list-none">
                        <span>⚙️</span>
                        <span class="nav-text">Configuration</span>
                        <span class="sidebar-chevron" aria-hidden="true"></span>
                    </summary>
                    <ul class="sidebar-sub-menu mt-1" role="list">
                        <li class="sidebar-sub-item"><a href="{{ route('application-configuration.index') }}" class="sidebar-sub-link {{ $isConfigOverviewSubActive ? 'is-active' : '' }}">Vue d'ensemble</a></li>
                        @if(auth()->user()->can('view_configuration') || auth()->user()->can('manage_paroisses'))
                        <li class="sidebar-sub-item"><a href="{{ route('application-configuration.index', ['tab' => 'paroisses']) }}" class="sidebar-sub-link {{ $isConfigParoissesTab ? 'is-active' : '' }}">Liste des paroisses</a></li>
                        @endif
                        @if(auth()->user()->can('view_configuration'))
                        <li class="sidebar-sub-item"><a href="{{ route('configurations.workspace') }}" class="sidebar-sub-link {{ $isConfigWorkspaceRoute ? 'is-active' : '' }}">Identité &amp; logo</a></li>
                        @endif
                    </ul>
                </details>
            </li>
            <li class="pt-2 mt-1 border-t border-[rgba(212,168,75,0.28)]">
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="{{ $navBase }} w-full text-left font-sans cursor-pointer appearance-none bg-transparent">
                        <span aria-hidden="true">🔑</span>
                        <span class="nav-text">Déconnexion</span>
                    </button>
                </form>
            </li>
            @endauth
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
