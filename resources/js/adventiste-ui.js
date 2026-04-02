import $ from 'jquery';

/**
 * Barre latérale : replier / déplier (persisté dans localStorage).
 */
function toggleSidebar() {
    const $wrapper = $('#main-wrapper');
    if (!$wrapper.length) {
        return;
    }
    $wrapper.toggleClass('menu-toggle');
    $('#navControl .hamburger').toggleClass('is-active');
    if ($wrapper.hasClass('menu-toggle')) {
        localStorage.setItem('sidebar-collapsed', '1');
    } else {
        localStorage.removeItem('sidebar-collapsed');
    }
}

window.toggleSidebar = toggleSidebar;

function initSidebarFromStorage() {
    const $wrapper = $('#main-wrapper');
    if (!$wrapper.length) {
        return;
    }
    if (localStorage.getItem('sidebar-collapsed') === '1') {
        $wrapper.addClass('menu-toggle');
        $('#navControl .hamburger').addClass('is-active');
    }
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
