<div id="preloader" class="theme-preloader-bg">
    <div class="preloader-bubble-wrapper">
        <div class="bubble-glow"></div>
        <div class="bubble-ring"></div>
        <img src="{{ asset('images/logo-catholique.png') }}" class="logo-in-bubble" alt="Logo Paroisse Saint-Esprit" width="100" height="100">
    </div>
</div>

<style>
#preloader {
    position: fixed;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2147483647;
    overflow: hidden;
}
.preloader-bubble-wrapper {
    position: relative;
    width: 160px;
    height: 160px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: bounceBubble 2s ease-in-out infinite;
}
.bubble-glow {
    position: absolute;
    width: 180px;
    height: 180px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(0,255,136,0.4), transparent 70%);
    filter: blur(35px);
    z-index: 0;
    animation: pulseBubbleGlow 2s ease-in-out infinite alternate;
}
.bubble-ring {
    position: absolute;
    width: 170px;
    height: 170px;
    border-radius: 50%;
    border: 2px solid rgba(0,255,136,0.6);
    box-shadow:
        0 0 15px rgba(0,255,136,0.8),
        0 0 30px rgba(0,180,100,0.4);
    z-index: 1;
    animation: rotateBubble 5s linear infinite;
}
.logo-in-bubble {
    position: relative;
    width: 100px;
    height: 100px;
    z-index: 2;
    border-radius: 50%;
    border: 2px solid rgba(0, 180, 100, 0.3);
    object-fit: cover;
    object-position: center;
    padding: 1px;
    background: #ffffff;
    filter: drop-shadow(0 10px 20px rgba(0,0,0,0.25)) drop-shadow(0 0 15px rgba(0,255,136,0.35));
}
@keyframes pulseBubbleGlow {
    0% { transform: scale(1); opacity: 0.65; }
    50% { transform: scale(1.15); opacity: 1; }
    100% { transform: scale(1); opacity: 0.65; }
}
@keyframes rotateBubble {
    to { transform: rotate(360deg); }
}
@keyframes bounceBubble {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-36px); }
}
#preloader.fade-out {
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.8s ease, visibility 0.8s ease;
}
</style>
