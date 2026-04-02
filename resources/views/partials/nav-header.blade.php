{{-- Nav header : logo église + déclinaison violet / or / vert --}}
<style>
.nav-header {
    width: 250px;
    height: 80px;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1100;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 16px;
    transition: width 0.3s ease;
}
.nav-header .brand-logo {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1.25rem;
    font-weight: 700;
    color: #fff;
    text-decoration: none;
    letter-spacing: 0.04em;
    transition: all 0.3s ease;
    max-width: 100%;
}
.nav-header .brand-logo:hover {
    color: #d4a84b;
    transform: translateX(2px);
}
.nav-header .brand-logo img {
    height: 48px;
    width: 48px;
    flex-shrink: 0;
    border-radius: 50%;
    border: 2px solid color-mix(in srgb, #d4a84b 55%, #00a86b 45%);
    padding: 4px;
    background: #ffffff;
    box-shadow:
        0 10px 20px rgba(0, 0, 0, 0.25),
        0 0 0 1px rgba(255, 255, 255, 0.55) inset;
    transition: all 0.3s ease;
    object-fit: cover;
    object-position: center;
}
.nav-header .brand-logo img:hover {
    transform: scale(1.05);
    box-shadow:
        0 0 26px rgba(240, 200, 92, 0.35),
        0 0 20px rgba(0, 168, 107, 0.3);
}
.nav-header .brand-logo .sidebar-label {
    background: linear-gradient(90deg, #f0e6ff, #f0c85c);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
}
#main-wrapper.menu-toggle .nav-header {
    width: 80px;
}
#main-wrapper.menu-toggle .nav-header .brand-logo span {
    display: none;
}
#main-wrapper.menu-toggle .nav-header .brand-logo {
    justify-content: center;
}
</style>

<div id="navHeader" class="nav-header theme-nav-header">
    <a href="/" class="brand-logo">
        <img src="{{ asset('images/logo-catholique.png') }}" alt="Logo Paroisse Saint-Esprit" width="48" height="48">
        <span class="sidebar-label">Catholique</span>
    </a>
</div>
