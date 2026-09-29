import $ from 'jquery';

/**
 * Bureau : menu réduit (80 px). Téléphone : tiroir ouvert / fermé.
 */
function estNavigationMobile() {
    return window.matchMedia('(max-width: 1023px)').matches;
}

function closeMobileSidebar() {
    const wrapper = document.getElementById('main-wrapper');
    if (!wrapper) {
        return;
    }
    const tiroirOuvert = wrapper.classList.contains('sidebar-open');
    wrapper.classList.remove('sidebar-open');
    document.body.style.overflow = '';
    const hamburger = document.querySelector('#navControl .hamburger');
    if (hamburger && (estNavigationMobile() || tiroirOuvert)) {
        hamburger.classList.remove('is-active');
    }
}

window.closeMobileSidebar = closeMobileSidebar;

function toggleSidebar() {
    const wrapper = document.getElementById('main-wrapper');
    if (!wrapper) {
        return;
    }
    const hamburger = document.querySelector('#navControl .hamburger');
    if (estNavigationMobile()) {
        wrapper.classList.toggle('sidebar-open');
        const ouvert = wrapper.classList.contains('sidebar-open');
        if (hamburger) {
            hamburger.classList.toggle('is-active', ouvert);
        }
        document.body.style.overflow = ouvert ? 'hidden' : '';

        return;
    }
    wrapper.classList.toggle('menu-toggle');
    if (hamburger) {
        hamburger.classList.toggle('is-active');
    }
    if (wrapper.classList.contains('menu-toggle')) {
        localStorage.setItem('sidebar-collapsed', '1');
    } else {
        localStorage.removeItem('sidebar-collapsed');
    }
}

window.toggleSidebar = toggleSidebar;

function appliquerEtatMenuBureau() {
    const wrapper = document.getElementById('main-wrapper');
    const hamburger = document.querySelector('#navControl .hamburger');
    if (!wrapper || estNavigationMobile()) {
        return;
    }
    if (localStorage.getItem('sidebar-collapsed') === '1') {
        wrapper.classList.add('menu-toggle');
        if (hamburger) {
            hamburger.classList.add('is-active');
        }
    } else {
        wrapper.classList.remove('menu-toggle');
        if (hamburger) {
            hamburger.classList.remove('is-active');
        }
    }
}

function initSidebarFromStorage() {
    const wrapper = document.getElementById('main-wrapper');
    if (!wrapper) {
        return;
    }
    if (estNavigationMobile()) {
        wrapper.classList.remove('menu-toggle');
        closeMobileSidebar();
    } else {
        appliquerEtatMenuBureau();
    }

    const sidebar = document.querySelector('.sidebar');
    if (sidebar) {
        sidebar.querySelectorAll('a').forEach(function (lien) {
            lien.addEventListener('click', function () {
                if (estNavigationMobile()) {
                    closeMobileSidebar();
                }
            });
        });
    }

    window.addEventListener('resize', function () {
        const hamburger = document.querySelector('#navControl .hamburger');
        if (estNavigationMobile()) {
            wrapper.classList.remove('menu-toggle');
            if (hamburger && !wrapper.classList.contains('sidebar-open')) {
                hamburger.classList.remove('is-active');
            }

            return;
        }
        closeMobileSidebar();
        appliquerEtatMenuBureau();
    });
}

function initThemeToggle() {
    const $btn = $('#themeToggle');
    if (!$btn.length) {
        return;
    }
    if (localStorage.getItem('theme') === 'dark') {
        $('body').addClass('dark-mode');
        $('html').addClass('dark');
        $btn.text('☀️');
    } else {
        $btn.text('🌙');
    }
    $btn.on('click', function () {
        $('body').toggleClass('dark-mode');
        $('html').toggleClass('dark');
        if ($('body').hasClass('dark-mode')) {
            localStorage.setItem('theme', 'dark');
            $btn.text('☀️');
        } else {
            localStorage.setItem('theme', 'light');
            $btn.text('🌙');
        }
    });
}

/**
 * Préchargeur plein écran (#preloader + .fade-out dans preload.blade.php).
 *
 * Comportement :
 * - Dès le chargement complet de la page (événement window `load`), attente
 *   de PRELOADER_DELAY_AFTER_LOAD_MS puis disparition.
 * - Filet de sécurité : au plus tard après PRELOADER_MAX_MS depuis l’init JS,
 *   le préchargeur est masqué même si `load` tarde (connexion lente, ressource bloquée).
 * - La classe CSS .fade-out applique une transition d’opacité d’environ 0,8 s
 *   (voir partial preload) : la page devient pleinement visible après ce fondu.
 */
function initPreloader() {
    const PRELOADER_DELAY_AFTER_LOAD_MS = 800;
    const PRELOADER_MAX_MS = 3500;

    const $preloader = $('#preloader');
    if (!$preloader.length) {
        return;
    }
    let hidden = false;
    function hide() {
        if (hidden) {
            return;
        }
        hidden = true;
        $preloader.addClass('fade-out');
    }
    const maxTimer = window.setTimeout(hide, PRELOADER_MAX_MS);
    function scheduleHideAfterLoad() {
        window.clearTimeout(maxTimer);
        window.setTimeout(hide, PRELOADER_DELAY_AFTER_LOAD_MS);
    }
    if (document.readyState === 'complete') {
        scheduleHideAfterLoad();
    } else {
        $(window).on('load', scheduleHideAfterLoad);
    }
}

$(function () {
    initPreloader();
    initSidebarFromStorage();
    initThemeToggle();

    $('#navControl').on('click', function () {
        toggleSidebar();
    });

    $(document).on('click', '.js-flash-dismiss', function () {
        $(this).closest('.ged-flash').fadeOut(200, function () {
            $(this).remove();
        });
    });
});
