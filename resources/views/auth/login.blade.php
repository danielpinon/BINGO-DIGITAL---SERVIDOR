@extends('layouts.app', ['class' => 'off-canvas-sidebar', 'activePage' => 'login', 'title' => __('BINGO DIGITAL - Acesso')])

@push('css')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    html, body {
        min-height: 100%;
        height: 100%;
        margin: 0;
        overflow: hidden;
    }
    .off-canvas-sidebar .navbar .navbar-collapse .navbar-nav .nav-item .nav-link { color: rgba(255,255,255,0.7); }

    .guest-navbar {
        display: none !important;
    }

    .bingo-login-page {
        background: #070518;
        min-height: 100vh;
        height: 100vh;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        position: fixed;
        inset: 0;
        font-family: 'Outfit', sans-serif;
        overflow: hidden;
        padding: 0 clamp(24px, 4vw, 72px);
    }

    /* Animated gradient orbs */
    .bingo-login-page::before,
    .bingo-login-page::after {
        content: '';
        position: absolute;
        border-radius: 50%;
        filter: blur(100px);
        opacity: 0.45;
        animation: floatOrb 12s ease-in-out infinite alternate;
        pointer-events: none;
        z-index: 0;
    }
    .bingo-login-page::before {
        width: 500px; height: 500px;
        background: radial-gradient(circle, #5b21b6 0%, #3b0764 60%, transparent 100%);
        top: -120px; left: -80px;
    }
    .bingo-login-page::after {
        width: 420px; height: 420px;
        background: radial-gradient(circle, #6d28d9 0%, #4c1d95 60%, transparent 100%);
        bottom: -100px; right: -60px;
        animation-delay: -5s;
        animation-duration: 15s;
    }

    .bingo-orb {
        position: absolute;
        border-radius: 50%;
        filter: blur(80px);
        opacity: 0.35;
        pointer-events: none;
        z-index: 0;
    }
    .bingo-orb.orb-1 {
        width: 300px; height: 300px;
        background: #a855f7;
        top: 40%; left: 15%;
        animation: floatOrb 18s ease-in-out infinite alternate;
    }
    .bingo-orb.orb-2 {
        width: 240px; height: 240px;
        background: #7c3aed;
        top: 15%; right: 20%;
        animation: floatOrb 14s ease-in-out infinite alternate;
        animation-delay: -7s;
    }
    .bingo-orb.orb-3 {
        width: 180px; height: 180px;
        background: #c084fc;
        bottom: 25%; left: 35%;
        animation: floatOrb 10s ease-in-out infinite alternate;
        animation-delay: -3s;
    }

    @keyframes floatOrb {
        0%   { transform: translate(0, 0) scale(1); }
        50%  { transform: translate(30px, -40px) scale(1.08); }
        100% { transform: translate(-20px, 20px) scale(0.95); }
    }

    /* Grid overlay for texture */
    .bingo-grid-overlay {
        position: absolute;
        inset: 0;
        background-image:
            linear-gradient(rgba(255,255,255,0.015) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255,255,255,0.015) 1px, transparent 1px);
        background-size: 60px 60px;
        pointer-events: none;
        z-index: 1;
        mask-image: radial-gradient(ellipse at center, black 30%, transparent 80%);
        -webkit-mask-image: radial-gradient(ellipse at center, black 30%, transparent 80%);
    }

    /* Main container */
    .bingo-login-container {
        position: relative;
        z-index: 2;
        width: 100%;
        max-width: 1360px;
        margin: 0 auto;
        min-height: 100%;
        height: 100%;
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(420px, 500px);
        gap: clamp(52px, 6vw, 110px);
        align-items: center;
    }

    /* Left branding panel */
    .bingo-brand-panel {
        color: #fff;
        padding: clamp(24px, 4vw, 56px) 0;
        animation: fadeSlideInLeft 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    .bingo-brand-badge {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        background: rgba(139, 92, 246, 0.12);
        border: 1px solid rgba(139, 92, 246, 0.25);
        padding: 8px 18px;
        border-radius: 999px;
        font-size: 0.8rem;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #c4b5fd;
        margin-bottom: clamp(24px, 3vw, 40px);
    }
    .bingo-brand-badge .dot {
        width: 8px; height: 8px;
        border-radius: 50%;
        background: #8b5cf6;
        box-shadow: 0 0 10px #8b5cf6;
        animation: pulseDot 2s ease-in-out infinite;
    }
    @keyframes pulseDot {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.5; transform: scale(0.8); }
    }
    .bingo-brand-title {
        font-family: 'Outfit', sans-serif;
        font-weight: 900;
        font-size: clamp(3.2rem, 5vw, 5.5rem);
        line-height: 1.05;
        letter-spacing: -0.03em;
        margin-bottom: clamp(18px, 2vw, 28px);
        color: #fafafa;
    }
    .bingo-brand-title .accent {
        background: linear-gradient(135deg, #a78bfa 0%, #7c3aed 50%, #c084fc 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .bingo-brand-subtitle {
        font-family: 'Space Grotesk', sans-serif;
        font-size: 1.1rem;
        font-weight: 400;
        color: rgba(255,255,255,0.55);
        line-height: 1.6;
        max-width: 560px;
        margin-bottom: clamp(34px, 4vw, 58px);
    }
    .bingo-brand-stats {
        display: flex;
        gap: clamp(28px, 4vw, 56px);
        flex-wrap: wrap;
    }
    .bingo-stat {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .bingo-stat-value {
        font-size: 1.6rem;
        font-weight: 800;
        color: #fafafa;
        line-height: 1;
    }
    .bingo-stat-label {
        font-family: 'Space Grotesk', sans-serif;
        font-size: 0.75rem;
        font-weight: 500;
        color: rgba(255,255,255,0.4);
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    /* Right login card */
    .bingo-login-card {
        background: rgba(255, 255, 255, 0.03);
        backdrop-filter: blur(24px);
        -webkit-backdrop-filter: blur(24px);
        border: 1px solid rgba(255, 255, 255, 0.07);
        border-radius: 24px;
        width: 100%;
        padding: clamp(38px, 4vw, 56px) clamp(34px, 3.5vw, 48px);
        box-shadow:
            0 0 0 1px rgba(139, 92, 246, 0.05),
            0 40px 80px -20px rgba(0, 0, 0, 0.6),
            inset 0 1px 0 rgba(255,255,255,0.04);
        animation: fadeSlideInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.15s forwards;
        opacity: 0;
    }
    .bingo-login-card-header {
        margin-bottom: clamp(28px, 3vw, 38px);
    }
    .bingo-login-card-title {
        font-weight: 700;
        font-size: clamp(1.55rem, 1.6vw, 1.9rem);
        color: #fff;
        margin-bottom: 6px;
        letter-spacing: -0.01em;
    }
    .bingo-login-card-subtitle {
        font-family: 'Space Grotesk', sans-serif;
        font-size: 0.9rem;
        color: rgba(255,255,255,0.4);
        font-weight: 400;
    }

    /* Custom inputs */
    .bingo-input-group {
        position: relative;
        margin-bottom: 24px;
    }
    .bingo-input-group input {
        width: 100%;
        background: rgba(0, 0, 0, 0.25);
        border: 1.5px solid rgba(255, 255, 255, 0.08);
        border-radius: 14px;
        padding: 18px 18px 18px 52px;
        color: #fff;
        font-family: 'Outfit', sans-serif;
        font-size: 0.95rem;
        font-weight: 400;
        outline: none;
        transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .bingo-input-group input::placeholder {
        color: rgba(255,255,255,0.25);
    }
    .bingo-input-group input:focus {
        border-color: rgba(139, 92, 246, 0.5);
        background: rgba(0, 0, 0, 0.35);
        box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1), 0 0 20px rgba(139, 92, 246, 0.08);
    }
    .bingo-input-group .input-icon {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        color: rgba(255,255,255,0.25);
        font-size: 1.1rem;
        transition: color 0.35s ease;
        pointer-events: none;
    }
    .bingo-input-group input:focus + .input-icon,
    .bingo-input-group input:not(:placeholder-shown) + .input-icon {
        color: #a78bfa;
    }
    .bingo-input-group.has-error input {
        border-color: rgba(239, 68, 68, 0.5);
        box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
    }
    .bingo-input-error {
        color: #f87171;
        font-size: 0.78rem;
        font-weight: 500;
        margin-top: 6px;
        margin-left: 4px;
        display: flex;
        align-items: center;
        gap: 5px;
        animation: shakeIn 0.4s ease;
    }
    @keyframes shakeIn {
        0% { transform: translateX(-6px); opacity: 0; }
        100% { transform: translateX(0); opacity: 1; }
    }

    /* Checkbox */
    .bingo-remember {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 30px;
        cursor: pointer;
        user-select: none;
    }
    .bingo-remember input {
        appearance: none;
        -webkit-appearance: none;
        width: 20px; height: 20px;
        border: 1.5px solid rgba(255,255,255,0.15);
        border-radius: 6px;
        background: rgba(0,0,0,0.2);
        cursor: pointer;
        position: relative;
        transition: all 0.25s ease;
    }
    .bingo-remember input:checked {
        background: linear-gradient(135deg, #7c3aed, #a855f7);
        border-color: transparent;
    }
    .bingo-remember input:checked::after {
        content: '';
        position: absolute;
        left: 6px; top: 3px;
        width: 5px; height: 9px;
        border: solid #fff;
        border-width: 0 2px 2px 0;
        transform: rotate(45deg);
    }
    .bingo-remember span {
        color: rgba(255,255,255,0.5);
        font-size: 0.88rem;
        font-weight: 400;
    }

    /* Submit button */
    .bingo-submit-btn {
        width: 100%;
        padding: 18px 24px;
        border: none;
        border-radius: 14px;
        background: linear-gradient(135deg, #6d28d9 0%, #7c3aed 40%, #a855f7 100%);
        color: #fff;
        font-family: 'Outfit', sans-serif;
        font-size: 1rem;
        font-weight: 700;
        letter-spacing: 0.02em;
        cursor: pointer;
        position: relative;
        overflow: hidden;
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        box-shadow: 0 4px 24px rgba(124, 58, 237, 0.3), inset 0 1px 0 rgba(255,255,255,0.15);
    }
    .bingo-submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(124, 58, 237, 0.45), inset 0 1px 0 rgba(255,255,255,0.2);
    }
    .bingo-submit-btn:active {
        transform: translateY(0);
    }
    .bingo-submit-btn::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(105deg, transparent 30%, rgba(255,255,255,0.18) 50%, transparent 70%);
        transform: translateX(-100%);
        transition: transform 0.6s ease;
    }
    .bingo-submit-btn:hover::before {
        transform: translateX(100%);
    }

    /* Footer links */
    .bingo-login-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 28px;
        padding-top: 22px;
        border-top: 1px solid rgba(255,255,255,0.06);
    }
    .bingo-login-footer a {
        font-family: 'Space Grotesk', sans-serif;
        color: rgba(255,255,255,0.4);
        font-size: 0.82rem;
        font-weight: 500;
        text-decoration: none;
        transition: color 0.3s ease;
    }
    .bingo-login-footer a:hover {
        color: #c4b5fd;
    }

    /* Animations */
    @keyframes fadeSlideInLeft {
        from { opacity: 0; transform: translateX(-40px); }
        to   { opacity: 1; transform: translateX(0); }
    }
    @keyframes fadeSlideInUp {
        from { opacity: 0; transform: translateY(30px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* Responsive */
    @media (max-width: 1100px) {
        .bingo-login-container {
            grid-template-columns: 1fr;
            gap: 32px;
            max-width: 560px;
            min-height: auto;
        }
        .bingo-brand-panel {
            text-align: center;
            padding: 16px 0 0;
        }
        .bingo-brand-stats {
            justify-content: center;
        }
        .bingo-brand-badge { margin: 0 auto 24px; }
        .bingo-brand-subtitle { margin: 0 auto 32px; }
    }
    @media (max-width: 480px) {
        .bingo-login-page { padding: 0 16px; }
        .bingo-login-container { gap: 20px; }
        .bingo-login-card { padding: 32px 24px; border-radius: 20px; }
        .bingo-brand-title { font-size: 2rem; }
        .bingo-brand-stats { gap: 24px; }
    }

    @media (max-height: 780px) and (min-width: 961px) {
        .bingo-login-page {
            min-height: 100vh;
            padding-top: 0;
            padding-bottom: 0;
        }

        .bingo-login-container {
            min-height: auto;
        }

        .bingo-brand-badge {
            margin-bottom: 20px;
        }

        .bingo-brand-title {
            font-size: clamp(3rem, 4.7vw, 4.2rem);
            margin-bottom: 18px;
        }

        .bingo-brand-subtitle {
            margin-bottom: 28px;
            line-height: 1.45;
        }

        .bingo-login-card {
            padding: 30px 36px;
            border-radius: 22px;
        }

        .bingo-login-card-header {
            margin-bottom: 22px;
        }

        .bingo-input-group {
            margin-bottom: 16px;
        }

        .bingo-input-group input {
            padding-top: 14px;
            padding-bottom: 14px;
        }

        .bingo-remember {
            margin-bottom: 20px;
        }

        .bingo-submit-btn {
            padding-top: 15px;
            padding-bottom: 15px;
        }

        .bingo-login-footer {
            margin-top: 18px;
            padding-top: 16px;
        }
    }

    /* Hide old material dashboard background on login page specifically */
    .page-header.login-page {
        background: transparent !important;
        position: static !important;
        min-height: 100vh !important;
        height: 100vh !important;
        padding: 0 !important;
        overflow: hidden !important;
    }
    .page-header.login-page::before,
    .page-header.login-page::after {
        content: none !important;
    }
    .wrapper-full-page .page-header {
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .wrapper-full-page {
        background: #070518 !important;
        min-height: 100vh;
        height: 100vh;
        padding-top: 0;
        overflow: hidden;
    }

    .page-header.login-page > .footer {
        display: none !important;
    }
</style>
@endpush

@section('content')
<div class="bingo-login-page">
    <div class="bingo-grid-overlay"></div>
    <div class="bingo-orb orb-1"></div>
    <div class="bingo-orb orb-2"></div>
    <div class="bingo-orb orb-3"></div>

    <div class="bingo-login-container">
        <!-- Brand Panel -->
        <div class="bingo-brand-panel">
            <div class="bingo-brand-badge">
                <span class="dot"></span>
                Plataforma Online
            </div>
            <h1 class="bingo-brand-title">
                BINGO<br>
                <span class="accent">DIGITAL</span>
            </h1>
            <p class="bingo-brand-subtitle">
                Gerencie seus bingos com elegância. Sorteios em tempo real, geração automática de cartelas e validação inteligente de ganhadores.
            </p>
            <div class="bingo-brand-stats">
                <div class="bingo-stat">
                    <span class="bingo-stat-value">5&times;5</span>
                    <span class="bingo-stat-label">Cartelas</span>
                </div>
                <div class="bingo-stat">
                    <span class="bingo-stat-value">01-75</span>
                    <span class="bingo-stat-label">Números</span>
                </div>
                <div class="bingo-stat">
                    <span class="bingo-stat-value">Real-time</span>
                    <span class="bingo-stat-label">Sorteio</span>
                </div>
            </div>
        </div>

        <!-- Login Card -->
        <div class="bingo-login-card">
            <div class="bingo-login-card-header">
                <h2 class="bingo-login-card-title">Acesso ao Sistema</h2>
                <p class="bingo-login-card-subtitle">Digite suas credenciais para continuar</p>
            </div>

            <form method="POST" action="{{ route('login') }}" autocomplete="off">
                @csrf

                <div class="bingo-input-group {{ $errors->has('email') ? 'has-error' : '' }}">
                    <input type="email" name="email" id="email" placeholder="Email"
                           value="{{ old('email') }}" required autocomplete="off">
                    <span class="input-icon material-icons">mail</span>
                    @if ($errors->has('email'))
                        <div class="bingo-input-error">
                            <span class="material-icons" style="font-size: 14px;">error_outline</span>
                            {{ $errors->first('email') }}
                        </div>
                    @endif
                </div>

                <div class="bingo-input-group {{ $errors->has('password') ? 'has-error' : '' }}">
                    <input type="password" name="password" id="password" placeholder="Senha"
                           value="" required autocomplete="off">
                    <span class="input-icon material-icons">lock</span>
                    @if ($errors->has('password'))
                        <div class="bingo-input-error">
                            <span class="material-icons" style="font-size: 14px;">error_outline</span>
                            {{ $errors->first('password') }}
                        </div>
                    @endif
                </div>

                <label class="bingo-remember">
                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    <span>Lembrar-me neste dispositivo</span>
                </label>

                <button type="submit" class="bingo-submit-btn">
                    Acessar Painel
                </button>
            </form>

            <div class="bingo-login-footer">
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}">Esqueceu a senha?</a>
                @else
                    <span></span>
                @endif
                <a href="#" onclick="funcionalidadeNaoHabilitada(); return false;">Precisa de ajuda?</a>
            </div>
        </div>
    </div>
</div>
@endsection
