<div id="mainHeader" class="header theme-header-bar fixed top-0 left-[250px] right-0 h-20 z-1099 flex items-center text-white transition-all duration-300"
     style="position: fixed; top: 0; left: 250px; right: 0; height: 80px; display: flex; z-index: 2147482000; background:
        radial-gradient(ellipse 80% 80% at 20% 80%, rgba(0, 180, 100, 0.25), rgba(0, 180, 100, 0.12) 25%, transparent 50%),
        linear-gradient(135deg, #0a0f15 0%, #0d1a1a 25%, #0f2520 50%, #0d1a1a 75%, #0a0f15 100%); border: 0 !important; border-bottom: 0 !important; box-shadow: none !important; outline: 0 !important;">
    <button type="button" id="navControl" class="nav-control shrink-0 h-full w-14 flex items-center justify-center hover:bg-slate-100/80 dark:hover:bg-white/5 focus:bg-transparent focus:outline-none focus:ring-0 active:bg-slate-100/80 dark:active:bg-white/5 cursor-pointer transition-all duration-300" title="Afficher / masquer le menu">
        <div class="hamburger flex flex-col gap-1.5 w-6 items-center justify-center">
            <span class="line block w-full h-0.5 rounded bg-linear-to-r from-church-gold via-[#00c978] to-[#8b6cb8] transition-all duration-300"></span>
            <span class="line block w-full h-0.5 rounded bg-linear-to-r from-church-gold via-[#00c978] to-[#8b6cb8] transition-all duration-300"></span>
            <span class="line block w-full h-0.5 rounded bg-linear-to-r from-church-gold via-[#00c978] to-[#8b6cb8] transition-all duration-300"></span>
        </div>
    </button>
    <div class="flex-1 flex justify-between items-center px-6 min-w-0">
        <span class="text-sm font-semibold system-label truncate text-white/90">
            <span class="text-emerald-100/90">
                @auth
                    @php
                        $headerUser = auth()->user();
                        $headerParoisseNom = $headerUser->paroisse?->nom;
                        if ($headerParoisseNom === null && $headerUser->hasRole('super_admin')) {
                            $headerParoisseNom = 'Toutes les paroisses';
                        }
                    @endphp
                    {{ config('app.name') }}@if($headerParoisseNom)<span class="text-white/60 font-normal"> — </span>{{ $headerParoisseNom }}@endif
                @else
                    {{ config('app.name') }}
                @endauth
            </span>
        </span>
        <ul class="header-right flex items-center gap-1 shrink-0">
            <li class="mr-2">
                <button id="themeToggle" type="button" class="px-3 py-1.5 rounded bg-white/10 text-lg hover:bg-white/20 transition-colors" title="Mode clair / mode sombre">🌙</button>
            </li>
            <li class="flex items-center user-box">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name ?? 'Admin') }}&background=4a3570&color=f0c85c" alt="Avatar" class="w-10 h-10 rounded-full avatar ring-2 ring-church-gold/40" width="40" height="40">
                <div class="user-details ml-2">
                    <p class="user-name text-sm font-semibold text-white">{{ auth()->user()->name ?? 'administrateur' }}</p>
                </div>
                <button id="profileToggle" type="button" class="ml-2 text-white/80 hover:text-white cursor-pointer text-sm" aria-haspopup="true" aria-expanded="false">▼</button>
            </li>
        </ul>
    </div>
</div>

<div id="profileMenu" class="menu-hidden header-dropdown w-48 bg-white rounded-lg shadow-xl py-1 border border-slate-200 min-w-48">
    <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50">👤 Mon compte</a>
    @can('manage_users')
    <a href="{{ route('users.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50">⚙️ Utilisateurs</a>
    @endcan
    <form method="post" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="w-full text-left flex items-center gap-2 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 font-medium">🔑 Déconnexion</button>
    </form>
</div>

<style>
.header-right { list-style: none; margin: 0; padding: 0; }
.user-box {
    display: flex; align-items: center; gap: 8px;
    background: rgba(0,0,0,0.2); padding: 6px 12px; border-radius: 8px;
}
.user-details .user-name { font-size: 0.95rem; font-weight: 600; color: #fff; line-height: 1.2; }
.header-dropdown a { transition: background 0.15s; text-decoration: none; }
.menu-hidden { display: none !important; }

#profileMenu {
    position: fixed !important;
    z-index: 2147483000 !important;
    opacity: 1 !important;
    filter: none !important;
    isolation: isolate;
}

.header .nav-control .hamburger.is-active .line:nth-child(1) { transform: rotate(45deg) translate(5px, 5px); }
.header .nav-control .hamburger.is-active .line:nth-child(2) { opacity: 0; }
.header .nav-control .hamburger.is-active .line:nth-child(3) { transform: rotate(-45deg) translate(6px, -6px); }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const profileToggle = document.getElementById('profileToggle');
    const profileMenu = document.getElementById('profileMenu');

    function placeMenu(trigger, menu, widthHint) {
        if (!trigger || !menu) return;
        menu.classList.remove('menu-hidden');
        const rect = trigger.getBoundingClientRect();
        const width = widthHint || menu.offsetWidth || 320;
        const top = rect.bottom + 8;
        let left = rect.right - width;
        left = Math.max(8, left);
        if (left + width > window.innerWidth - 8) {
            left = window.innerWidth - width - 8;
        }
        menu.style.top = top + 'px';
        menu.style.left = left + 'px';
    }

    function hideMenu(menu, trigger) {
        if (!menu) return;
        menu.classList.add('menu-hidden');
        if (trigger) trigger.setAttribute('aria-expanded', 'false');
    }

    function hideAll() {
        hideMenu(profileMenu, profileToggle);
    }

    if (profileToggle && profileMenu) {
        profileToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            const willOpen = profileMenu.classList.contains('menu-hidden');
            hideAll();
            if (willOpen) {
                placeMenu(profileToggle, profileMenu, 192);
                profileToggle.setAttribute('aria-expanded', 'true');
            }
        });
    }

    [profileMenu].forEach(function (menu) {
        if (!menu) return;
        menu.addEventListener('click', function (e) { e.stopPropagation(); });
    });

    window.addEventListener('resize', function () {
        if (profileMenu && !profileMenu.classList.contains('menu-hidden')) {
            placeMenu(profileToggle, profileMenu, 192);
        }
    });

    document.addEventListener('click', hideAll);
});
</script>
