<div class="footer theme-footer-bar" style="
    padding-left: 250px;
    width: 100%;
    box-sizing: border-box;
    transition: padding-left 0.3s ease;
    border-top: 1px solid rgba(212, 168, 75, 0.2);
">
    <div class="copyright" style="padding: 1rem 1.5rem;">
        <p style="text-align: center; margin: 0; font-size: 0.875rem; color: #2d1f4a;">
            © {{ date('Y') }} — {{ config('app.name') }} — <span style="color: #8b6cb8;">Paroisse catholique</span>
        </p>
    </div>
</div>
<style>
#main-wrapper.menu-toggle .footer { padding-left: 80px !important; }
body.dark-mode .footer p { color: #f5f0ff !important; }
body.dark-mode .footer span { color: #e8d5a3 !important; }
</style>
