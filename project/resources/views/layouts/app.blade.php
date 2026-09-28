<!DOCTYPE html>
<html lang="{{ $appLocale ?? 'en' }}" dir="{{ $appDir ?? 'ltr' }}" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#071426">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v=3">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v=3">
    <title>@yield('title', __('ui.app_name') . ' - ' . __('ui.tagline'))</title>
    @include('layouts.partials.theme-init')
    @if(($appDir ?? 'ltr') === 'rtl')
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    @else
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    @endif
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Noto+Nastaliq+Urdu:wght@400;600;700&family=Noto+Sans+Arabic:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        html, body {
            background-color: var(--cc-bg) !important;
            color: var(--cc-text) !important;
            overflow-x: hidden;
            max-width: 100%;
        }
        html[data-bs-theme="dark"], html[data-bs-theme="dark"] body {
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(85, 172, 247, 0.25) 0%, transparent 45%),
                radial-gradient(circle at 90% 60%, rgba(99, 155, 241, 0.2) 0%, transparent 45%),
                radial-gradient(circle at 50% 90%, rgba(132, 196, 252, 0.15) 0%, transparent 50%);
            background-attachment: fixed;
        }
        html[data-bs-theme="light"], html[data-bs-theme="light"] body {
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(85, 172, 247, 0.12) 0%, transparent 45%),
                radial-gradient(circle at 90% 60%, rgba(99, 155, 241, 0.08) 0%, transparent 40%);
            background-attachment: fixed;
        }
        body {
            font-family: 'Plus Jakarta Sans', 'Noto Sans Arabic', 'Noto Nastaliq Urdu', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: background-color 0.25s ease, color 0.25s ease;
            overflow-x: hidden;
        }
        body.font-sm { font-size: 0.875rem; }
        body.font-md { font-size: 1rem; }
        body.font-lg { font-size: 1.125rem; }
        
        /* Text glow helper classes */
        .text-glow-purple {
            color: #84C4FC !important;
            text-shadow: 0 0 12px rgba(132, 196, 252, 0.6), 0 0 24px rgba(85, 172, 247, 0.4);
        }
        .text-glow-green {
            color: #3498D3 !important;
            text-shadow: 0 0 12px rgba(52, 152, 211, 0.6), 0 0 24px rgba(16, 124, 185, 0.4);
        }
        .text-glow-white {
            color: #ffffff !important;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
        }

        /* Navbar & Glowing Elements */
        .navbar-brand-logo {
            font-weight: 800;
            letter-spacing: -0.4px;
            color: var(--cc-text) !important;
        }
        html[data-bs-theme="dark"] .campus-navbar {
            background: rgba(16, 18, 20, 0.92);
            border-bottom: 1px solid rgba(126, 192, 244, 0.12);
            backdrop-filter: blur(16px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.24), 0 1px 16px rgba(70, 160, 228, 0.06);
        }
        html[data-bs-theme="light"] .campus-navbar {
            background: rgba(255, 255, 255, 0.95);
            border-bottom: 1px solid rgba(32, 96, 145, 0.14);
            backdrop-filter: blur(16px);
            box-shadow: 0 4px 20px rgba(27, 70, 105, 0.08);
        }
        .campus-navbar {
            max-width: 100vw;
        }
        .campus-navbar .container { overflow: visible; gap: 0.6rem; }
        .campus-navbar .navbar-brand { flex-shrink: 0; }
        .brand-mark { 
            width: 40px; height: 40px; 
            background: linear-gradient(135deg, #61B6F4, #2078B7) !important;
            box-shadow: 0 0 18px rgba(70, 160, 228, 0.28);
        }
        .campus-navbar .navbar-nav { gap: 0.2rem; }
        
        html[data-bs-theme="dark"] .campus-navbar .nav-link { color: #E8F4FF; }
        html[data-bs-theme="light"] .campus-navbar .nav-link { color: #424C53; }

        .campus-navbar .nav-link {
            font-size: 0.85rem;
            font-weight: 600;
            white-space: nowrap;
            transition: all 0.3s ease;
            border-radius: 9999px;
            padding: 0.45rem 0.85rem !important;
        }
        .campus-navbar .nav-link:hover { 
            color: #155D94 !important; 
            background: rgba(70, 160, 228, 0.09); 
            box-shadow: none;
        }
        .campus-navbar .nav-link.active { 
            color: #B5DAF6 !important; 
            background: rgba(70, 160, 228, 0.12) !important; 
            box-shadow: none;
        }
        .campus-navbar .navbar-toggler:focus { box-shadow: 0 0 0 0.2rem rgba(70, 160, 228, 0.28); }
        .campus-navbar .navbar-collapse { min-width: 0; }
        .campus-navbar .dropdown-menu {
            min-width: 13rem;
            padding: 0.45rem;
            background: var(--cc-surface);
            border: 1px solid var(--cc-border);
            box-shadow: 0 16px 36px rgba(0, 0, 0, 0.3);
        }
        .campus-navbar .dropdown-item { 
            border-radius: 0.55rem; 
            padding: 0.6rem 0.75rem; 
            color: var(--cc-text);
            transition: all 0.2s ease;
        }
        .campus-navbar .dropdown-item:hover {
            background-color: rgba(70, 160, 228, 0.1);
            color: var(--cc-text);
            box-shadow: none;
        }
        .navbar-tools { flex-shrink: 0; }

        /* Buttons with Glowing Purple Pill Effects */
        .btn-purple-glow, .btn-primary-purple {
            background: linear-gradient(135deg, #3396EA 0%, #3A93ED 100%) !important;
            border: 1px solid #84C4FC !important;
            color: #ffffff !important;
            box-shadow: 0 0 18px rgba(85, 172, 247, 0.6), inset 0 1px 0 rgba(255, 255, 255, 0.2) !important;
            transition: all 0.3s ease !important;
            border-radius: 9999px !important;
        }
        .btn-purple-glow:hover, .btn-primary-purple:hover {
            background: linear-gradient(135deg, #55ACF7 0%, #3396EA 100%) !important;
            box-shadow: 0 0 28px rgba(132, 196, 252, 0.85), 0 0 12px rgba(255, 255, 255, 0.4) !important;
            transform: translateY(-2px);
            color: #ffffff !important;
        }

        .btn-glow-green, .btn-success {
            background: linear-gradient(135deg, #107CB9 0%, #056396 100%) !important;
            border: 1px solid #3498D3 !important;
            color: #ffffff !important;
            box-shadow: 0 0 15px rgba(16, 124, 185, 0.5), inset 0 1px 0 rgba(255, 255, 255, 0.2) !important;
            transition: all 0.3s ease !important;
        }
        .btn-glow-green:hover, .btn-success:hover {
            background: linear-gradient(135deg, #3498D3 0%, #107CB9 100%) !important;
            box-shadow: 0 0 25px rgba(52, 152, 211, 0.8), 0 0 10px rgba(255, 255, 255, 0.4) !important;
            transform: translateY(-2px);
            color: #ffffff !important;
        }

        .btn-glow-outline, .btn-outline-dark, .btn-outline-light {
            background: rgba(85, 172, 247, 0.08) !important;
            border: 1px solid rgba(85, 172, 247, 0.5) !important;
            color: var(--cc-text) !important;
            transition: all 0.3s ease !important;
        }
        .btn-glow-outline:hover, .btn-outline-dark:hover, .btn-outline-light:hover {
            background: rgba(85, 172, 247, 0.25) !important;
            border-color: #84C4FC !important;
            box-shadow: 0 0 18px rgba(85, 172, 247, 0.5) !important;
            transform: translateY(-2px);
            color: var(--cc-text) !important;
        }

        /* Footer */
        html[data-bs-theme="dark"] .campus-footer {
            background: #0D0F10;
            border-top: 1px solid rgba(126, 192, 244, 0.12);
            box-shadow: 0 -10px 30px rgba(0, 0, 0, 0.7);
            color: #ffffff !important;
        }
        html[data-bs-theme="light"] .campus-footer {
            background: #ffffff;
            border-top: 1px solid rgba(32, 96, 145, 0.14);
            box-shadow: 0 -10px 30px rgba(27, 70, 105, 0.06);
            color: #171D21 !important;
        }
        .campus-footer .footer-link {
            color: var(--cc-muted);
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .campus-footer .footer-link:hover { 
            color: #155D94; 
            text-shadow: none;
            padding-left: 0.3rem; 
        }
        .footer-demo-card { 
            background: rgba(85, 172, 247, 0.06); 
            border: 1px solid rgba(85, 172, 247, 0.25); 
            box-shadow: 0 0 15px rgba(85, 172, 247, 0.15);
        }

        .page-intro { 
            padding: 1.5rem; 
            border: 1px solid rgba(85, 172, 247, 0.35); 
            border-radius: 1.25rem; 
            background: linear-gradient(135deg, rgba(13, 24, 36, 0.9), rgba(85, 172, 247, 0.15)); 
            box-shadow: 0 0 25px rgba(85, 172, 247, 0.2);
        }
        .page-intro h1, .page-intro h2, .page-intro h3 { letter-spacing: -0.03em; }

        /* Card & Table Styling */
        .card, .stat-card {
            background-color: var(--cc-surface) !important;
            border: 1px solid var(--cc-border) !important;
            backdrop-filter: blur(12px);
            box-shadow: var(--cc-shadow);
            color: var(--cc-text) !important;
            transition: all 0.3s ease;
        }
        .card:hover, .stat-card:hover {
            border-color: rgba(52, 152, 211, 0.6) !important;
            box-shadow: 0 10px 35px rgba(16, 124, 185, 0.25) !important;
        }
        .stat-card { border-radius: 16px; }
        
        /* Glowing Metric Cards */
        html[data-bs-theme="dark"] .metric-glow-card {
            background: linear-gradient(135deg, rgba(20, 36, 46, 0.9), rgba(10, 19, 25, 0.95)) !important;
            border: 1px solid rgba(52, 152, 211, 0.35) !important;
            box-shadow: 0 0 20px rgba(16, 124, 185, 0.2);
            border-radius: 16px;
            color: #ffffff !important;
        }
        html[data-bs-theme="light"] .metric-glow-card {
            background: linear-gradient(135deg, #ffffff, #ECF6FD) !important;
            border: 1px solid rgba(16, 124, 185, 0.35) !important;
            box-shadow: 0 10px 25px rgba(16, 124, 185, 0.15);
            border-radius: 16px;
            color: #06354E !important;
        }
        .metric-glow-card:hover {
            box-shadow: 0 0 30px rgba(52, 152, 211, 0.4);
            border-color: #3498D3 !important;
        }

        .icon-box {
            width: 48px; height: 48px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center; font-size: 1.25rem;
            background: rgba(52, 152, 211, 0.15) !important;
            color: #056396 !important;
            box-shadow: 0 0 12px rgba(52, 152, 211, 0.3);
        }

        html[data-bs-theme="dark"] .text-white-50 { color: rgba(255, 255, 255, 0.65) !important; }
        html[data-bs-theme="light"] .text-white-50 { color: #374151 !important; }
        html[data-bs-theme="light"] .text-white { color: #06354E !important; }

        .text-muted, .text-secondary { color: var(--cc-muted) !important; }

        /* Form elements */
        .form-control, .form-select { 
            background-color: var(--cc-surface) !important;
            color: var(--cc-text) !important;
            border-color: var(--cc-border) !important; 
            border-radius: 0.7rem; 
        }
        .form-control:focus, .form-select:focus { 
            border-color: #3498D3 !important; 
            box-shadow: 0 0 15px rgba(52, 152, 211, 0.4) !important; 
            background-color: var(--cc-surface) !important;
            color: var(--cc-text) !important;
        }

        .table {
            color: var(--cc-text) !important;
            --bs-table-bg: transparent;
            --bs-table-border-color: var(--cc-border);
        }

        @media (max-width: 1199.98px) {
            .campus-navbar .navbar-collapse { padding: 1rem 0 0.5rem; }
            .campus-navbar .navbar-nav { align-items: stretch; gap: 0.4rem; }
            .navbar-tools { padding-top: 0.75rem; border-top: 1px solid rgba(255, 255, 255, 0.1); width: 100%; justify-content: flex-start; }
            .campus-navbar .nav-link { padding-top: 0.6rem !important; padding-bottom: 0.6rem !important; }
            .campus-navbar .dropdown-menu { margin-top: 0.35rem; }
        }
        @media (max-width: 575.98px) {
            .campus-navbar { padding-top: 0.65rem !important; padding-bottom: 0.65rem !important; }
            .navbar-brand { gap: 0.5rem !important; }
            .navbar-brand-logo { font-size: 1.1rem !important; }
            .navbar-brand .brand-tagline { font-size: 0.65rem; }
            .footer-demo-actions > * { flex: 1 1 100%; }
        }

        /* Hardware-Accelerated Smooth Glowing Animations */
        @keyframes pulseGlow {
            0% {
                box-shadow: 0 0 12px rgba(16, 124, 185, 0.4);
                border-color: rgba(52, 152, 211, 0.4);
            }
            50% {
                box-shadow: 0 0 24px rgba(52, 152, 211, 0.7);
                border-color: rgba(52, 152, 211, 0.7);
            }
            100% {
                box-shadow: 0 0 12px rgba(16, 124, 185, 0.4);
                border-color: rgba(52, 152, 211, 0.4);
            }
        }

        @keyframes textGlowPulse {
            0% {
                text-shadow: 0 0 8px rgba(52, 152, 211, 0.4);
            }
            50% {
                text-shadow: 0 0 18px rgba(52, 152, 211, 0.8);
            }
            100% {
                text-shadow: 0 0 8px rgba(52, 152, 211, 0.4);
            }
        }

        .btn-glow-green, .btn-success {
            animation: pulseGlow 4s infinite ease-in-out;
            will-change: box-shadow, border-color;
            transform: translateZ(0);
        }

        .text-glow-green {
            animation: textGlowPulse 4s infinite ease-in-out;
            will-change: text-shadow;
        }

        /* Responsive & UI Helpers */
        .badge-income { background-color: rgba(16, 124, 185, 0.2); color: #3498D3; border: 1px solid rgba(52, 152, 211, 0.4); }
        .badge-expense { background-color: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.4); }
        
        .goog-te-banner-frame.skiptranslate { display: none !important; }
        body { top: 0 !important; }
        .goog-logo-link, .goog-te-gadget span, .goog-te-gadget img { display: none !important; }
        .goog-te-gadget { font-size: 0 !important; }
        #google_translate_element { min-width: 0; }
        #google_translate_element .goog-te-gadget { display: flex; align-items: center; }
        #google_translate_element select {
            width: 138px; height: 38px; padding: .42rem 1.8rem .42rem .75rem;
            color: #fff; background-color: rgba(255,255,255,.1);
            border: 1px solid rgba(255,255,255,.18); border-radius: .65rem;
            font-size: .78rem; font-weight: 600; cursor: pointer;
            appearance: auto;
        }
        #google_translate_element select option { color: #17212b; background: #fff; }
        .skiptranslate iframe { visibility: hidden !important; }
        .breadcrumb-item + .breadcrumb-item::before { content: "›"; color: #A3B0B8; }
        .hover-text-white:hover { color: #ffffff !important; }
        .hover-bg-white-20:hover { background-color: rgba(255, 255, 255, 0.2) !important; }

        /* Match the public Campus Coin landing page across both authenticated portals. */
        html[data-bs-theme="dark"] .campus-navbar {
            background: rgba(16, 18, 20, .9);
            border-bottom-color: rgba(235, 241, 245, .08);
            box-shadow: 0 8px 30px rgba(0, 0, 0, .2);
        }
        html[data-bs-theme="dark"], html[data-bs-theme="dark"] body {
            background-image:
                radial-gradient(circle at 10% 20%, rgba(30, 78, 112, .15) 0%, transparent 45%),
                radial-gradient(circle at 90% 60%, rgba(41, 85, 115, .09) 0%, transparent 45%),
                radial-gradient(circle at 50% 90%, rgba(30, 78, 112, .06) 0%, transparent 50%);
        }
        html[data-bs-theme="light"], html[data-bs-theme="light"] body {
            background-image:
                radial-gradient(circle at 10% 20%, rgba(48, 104, 148, .08) 0%, transparent 45%),
                radial-gradient(circle at 90% 60%, rgba(69, 109, 140, .05) 0%, transparent 40%);
        }
        html[data-bs-theme="dark"] .campus-navbar .nav-link { color: #C6C9CB; }
        .campus-navbar .nav-link:hover, .campus-navbar .nav-link.active {
            color: var(--cc-primary-bright) !important;
            background: rgba(70, 160, 228, .1) !important;
            box-shadow: none !important;
        }
        .brand-mark { background: linear-gradient(135deg, #61B6F4, #2078B7) !important; box-shadow: 0 0 20px rgba(70, 160, 228, .28); }
        .text-glow-purple { color: var(--cc-primary-bright) !important; text-shadow: 0 0 16px rgba(70, 160, 228, .22) !important; }
        .btn-purple-glow, .btn-primary-purple {
            background: linear-gradient(135deg, #46A0E4, #1D6DA6) !important;
            border-color: rgba(97, 182, 244, .6) !important;
            color: #0C1217 !important;
            box-shadow: 0 8px 24px rgba(39, 118, 177, .18) !important;
        }
        .btn-glow-green, .btn-success {
            background: linear-gradient(135deg, #2B86C7, #145989) !important;
            border-color: rgba(97, 182, 244, .55) !important;
            box-shadow: 0 8px 24px rgba(39, 118, 177, .16) !important;
        }
        .btn-glow-outline, .btn-outline-dark, .btn-outline-light {
            background: rgba(70, 160, 228, .055) !important;
            border-color: rgba(70, 160, 228, .28) !important;
        }
        .page-intro { border-color: var(--cc-border); background: linear-gradient(145deg, var(--cc-surface), rgba(70, 160, 228, .035)); box-shadow: var(--cc-shadow); }
        .card, .stat-card { border-color: var(--cc-border) !important; box-shadow: var(--cc-shadow); }
        .stat-card { background: linear-gradient(145deg, rgba(24, 28, 31, .97), rgba(19, 21, 23, .97)) !important; }
        html[data-bs-theme="light"] .stat-card { background: linear-gradient(145deg, #ffffff, #F3F7FA) !important; }
        .portal-hero-card { background: radial-gradient(ellipse at 84% 25%, rgba(70, 160, 228, .13), transparent 34%), linear-gradient(145deg, #111517, #15191A) !important; border-color: rgba(70, 160, 228, .24) !important; box-shadow: 0 16px 50px rgba(0,0,0,.24), 0 0 34px rgba(70, 160, 228,.07) !important; }
        .portal-role-badge { color: #82BFEF; background: rgba(70, 160, 228, .1); border: 1px solid rgba(70, 160, 228, .24); }
        .authenticated-panel main > .container { max-width: 1440px; }
        .authenticated-panel .campus-footer { background: #0D0F10; border-top-color: rgba(235, 241, 245, .08); }
        .authenticated-panel .campus-navbar .dropdown-item.active,
        .authenticated-panel .campus-navbar .dropdown-item:active { color: #0C1217; background: #61B6F4; }
        .authenticated-panel .campus-navbar .dropdown-item:hover,
        .authenticated-panel .campus-navbar .dropdown-item:focus { background: rgba(70, 160, 228, .11) !important; color: #E8EFF5 !important; box-shadow: none !important; }
        .authenticated-panel .campus-navbar .dropdown-item.active:hover { color: #0C1217 !important; background: #82BFEF !important; }
        .authenticated-panel .campus-navbar .nav-link:hover,
        .authenticated-panel .campus-navbar .nav-link:focus,
        .authenticated-panel .campus-navbar .nav-link.active { color: #82BFEF !important; background: rgba(70, 160, 228, .1) !important; box-shadow: none !important; }
        .authenticated-panel .campus-navbar .nav-link.active:hover { color: #B5DAF6 !important; background: rgba(70, 160, 228, .16) !important; }
        .authenticated-panel .campus-navbar .navbar-toggler,
        .authenticated-panel .campus-navbar .navbar-toggler:hover,
        .authenticated-panel .campus-navbar .navbar-toggler:active,
        .authenticated-panel .campus-navbar .navbar-toggler[aria-expanded="true"] {
            background: linear-gradient(135deg, #46A0E4, #1D6DA6) !important;
            border-color: rgba(97, 182, 244, .6) !important;
            box-shadow: 0 8px 24px rgba(39, 118, 177, .18) !important;
            color: #0C1217 !important;
        }
        .authenticated-panel .campus-navbar .navbar-toggler:focus,
        .authenticated-panel .campus-navbar .navbar-toggler:focus-visible { box-shadow: 0 0 0 .2rem rgba(70, 160, 228, .28) !important; }
        .authenticated-panel .btn-purple-glow:hover,
        .authenticated-panel .btn-purple-glow:focus,
        .authenticated-panel .btn-purple-glow:active,
        .authenticated-panel .btn-primary-purple:hover,
        .authenticated-panel .btn-primary-purple:focus,
        .authenticated-panel .btn-primary-purple:active {
            background: linear-gradient(135deg, #61B6F4, #2078B7) !important;
            border-color: rgba(97, 182, 244, .7) !important;
            color: #0C1217 !important;
            box-shadow: 0 8px 24px rgba(39, 118, 177, .22) !important;
        }
        .authenticated-panel .btn-glow-outline:hover,
        .authenticated-panel .btn-glow-outline:focus,
        .authenticated-panel .btn-glow-outline:focus-visible,
        .authenticated-panel .btn-glow-outline:active,
        .authenticated-panel .btn-outline-dark:hover,
        .authenticated-panel .btn-outline-dark:focus,
        .authenticated-panel .btn-outline-dark:focus-visible,
        .authenticated-panel .btn-outline-dark:active,
        .authenticated-panel .btn-outline-light:hover,
        .authenticated-panel .btn-outline-light:focus,
        .authenticated-panel .btn-outline-light:focus-visible,
        .authenticated-panel .btn-outline-light:active {
            border-color: rgba(97, 182, 244, .55) !important;
            background: rgba(70, 160, 228, .12) !important;
            color: #B5DAF6 !important;
            box-shadow: 0 0 18px rgba(39, 118, 177, .18) !important;
        }
        .authenticated-panel .campus-navbar {
            background: rgba(16, 18, 20, .88) !important;
            border-bottom: 1px solid rgba(235, 241, 245, .09);
            box-shadow: 0 10px 34px rgba(0, 0, 0, .24);
            backdrop-filter: blur(18px) saturate(145%);
        }
        .admin-panel .campus-navbar {
            background: rgba(16, 18, 20, .94) !important;
            border-bottom-color: rgba(70, 160, 228, .2);
            box-shadow: 0 10px 34px rgba(0, 0, 0, .3), 0 1px 18px rgba(70, 160, 228, .06);
        }
        .admin-panel .admin-layout { display: grid; grid-template-columns: 240px minmax(0, 1fr); align-items: start; gap: 24px; }
        .admin-panel .admin-sidebar { grid-column: 1; grid-row: 1; }
        .admin-panel .admin-content { grid-column: 2; grid-row: 1; }
        .authenticated-panel .profile-card { overflow: hidden; border: 1px solid rgba(235, 241, 245,.1) !important; background: #111315; box-shadow: 0 18px 48px rgba(0,0,0,.24) !important; }
        .authenticated-panel .profile-card-header { min-height: 104px; background: linear-gradient(120deg,#1A1E21,#202426) !important; }
        .authenticated-panel .profile-avatar { width: 56px; height: 56px; flex: 0 0 56px; border: 1px solid rgba(126, 192, 244,.27); background: linear-gradient(145deg,#246898,#124363); box-shadow: 0 6px 20px rgba(39, 118, 177,.17); font-size: 1.25rem; }
        .authenticated-panel .profile-avatar img { width: 100%; height: 100%; border-radius: inherit; object-fit: cover; }
        .authenticated-panel .profile-photo-upload { display: flex; max-width: 620px; align-items: flex-end; gap: 16px; padding: 16px; border: 1px solid rgba(235, 241, 245,.09); border-radius: 14px; background: rgba(255,255,255,.02); }
        .authenticated-panel .profile-photo-upload > div { min-width: 0; }
        .authenticated-panel .profile-remove-avatar { flex: 0 0 auto; }
        .authenticated-panel .profile-card .card-body { padding: clamp(20px,3vw,32px) !important; }
        .authenticated-panel .profile-section-title { color: #74B5E9; font-size: .95rem; }
        .authenticated-panel .profile-security-heading { padding-top: 4px; }
        .authenticated-panel .profile-security-title { color: #e6a0a6; }
        .authenticated-panel .profile-security-help { color: #8F969B; font-size: .8rem; }
        .authenticated-panel .profile-card .form-label { color: #AAB1B7 !important; }
        .authenticated-panel .profile-card .form-control,
        .authenticated-panel .profile-card .input-group-text {
            min-height: 42px;
            border-color: rgba(235, 241, 245,.12) !important;
            background-color: #15181A !important;
            color: #E8EDF0 !important;
        }
        .authenticated-panel .profile-card .form-control:focus { border-color: rgba(70, 160, 228,.5) !important; box-shadow: 0 0 0 .2rem rgba(70, 160, 228,.1) !important; }
        .authenticated-panel .profile-card .form-control::placeholder { color: #787F84; opacity: 1; }
        .authenticated-panel .profile-card .form-control:disabled,
        .authenticated-panel .profile-card .profile-readonly { background: #1A1E20 !important; color: #BAC0C5 !important; opacity: 1; }
        .authenticated-panel .profile-card .form-text { color: #899096; }
        .authenticated-panel .profile-save-button { min-height: 42px; padding-inline: 22px; background: linear-gradient(120deg,#3582B8,#165987); border-color: rgba(97, 182, 244,.35); }
        .authenticated-panel .profile-save-button:hover { background: linear-gradient(120deg,#4695CE,#1C689B); box-shadow: 0 8px 24px rgba(39, 118, 177,.2); }
        @media (min-width: 1200px) {
            .admin-panel .campus-navbar .navbar-collapse { justify-content: flex-end; }
            .admin-panel .campus-navbar .navbar-tools { margin-left: auto; }
        }
        .admin-sidebar {
            position: sticky;
            top: 82px;
            display: flex;
            min-height: 420px;
            flex-direction: column;
            padding: 16px 12px 12px;
            border: 1px solid rgba(235, 241, 245,.09);
            border-radius: 16px;
            background: linear-gradient(160deg, rgba(20, 24, 26,.98), rgba(13, 15, 17,.98));
            box-shadow: 0 14px 36px rgba(0,0,0,.2);
        }
        .admin-sidebar-heading { display: flex; align-items: center; gap: 11px; padding: 5px 7px 18px; border-bottom: 1px solid rgba(235, 241, 245,.08); }
        .admin-sidebar-icon { display: grid; width: 36px; height: 36px; flex: 0 0 36px; place-items: center; border: 1px solid rgba(70, 160, 228,.2); border-radius: 11px; background: rgba(70, 160, 228,.09); color: #82BFEF; }
        .admin-sidebar-heading strong, .admin-sidebar-heading small { display: block; }
        .admin-sidebar-heading strong { color: #EDF2F7; font-size: .82rem; }
        .admin-sidebar-heading small { margin-top: 2px; color: #828A90; font-size: .68rem; }
        .admin-sidebar-nav { display: grid; gap: 5px; padding-top: 16px; }
        .admin-sidebar-nav a { display: flex; min-height: 42px; align-items: center; gap: 11px; padding: 0 11px; border: 1px solid transparent; border-radius: 10px; color: #AAB1B7; font-size: .82rem; font-weight: 600; text-decoration: none; transition: background .18s ease, border-color .18s ease, color .18s ease, transform .18s ease; }
        .admin-sidebar-nav a > i { display: grid; width: 42px; height: 42px; flex: 0 0 42px; place-items: center; border: 1px solid rgba(70, 160, 228,.13); border-radius: 12px; background: rgba(70, 160, 228,.06); color: #818A91; font-size: 16px; line-height: 1; text-align: center; }
        .admin-sidebar-nav a:hover, .admin-sidebar-nav a:focus-visible { transform: translateX(2px); border-color: rgba(70, 160, 228,.13); background: rgba(70, 160, 228,.07); color: #E0ECF6; }
        .admin-sidebar-nav a:hover > i, .admin-sidebar-nav a:focus-visible > i { border-color: rgba(70, 160, 228,.25); color: #82BFEF; }
        .admin-sidebar-nav a.active { border-color: rgba(70, 160, 228,.2); background: linear-gradient(100deg, rgba(70, 160, 228,.15), rgba(70, 160, 228,.06)); color: #B5DAF6; }
        .admin-sidebar-nav a.active > i { border-color: rgba(70, 160, 228,.24); background: rgba(70, 160, 228,.12); color: #82BFEF; }
        .admin-sidebar-footer { display: flex; align-items: center; gap: 8px; margin-top: auto; padding: 16px 8px 4px; border-top: 1px solid rgba(235, 241, 245,.08); color: #858D93; font-size: .68rem; }
        .admin-sidebar-status { width: 7px; height: 7px; border-radius: 50%; background: #46A0E4; box-shadow: 0 0 10px rgba(70, 160, 228,.55); }
        .admin-content { min-width: 0; }
        @media (max-width: 991.98px) {
            .admin-panel .admin-layout { grid-template-columns: 1fr; gap: 16px; }
            .admin-panel .admin-sidebar, .admin-panel .admin-content { grid-column: 1; grid-row: auto; }
            .admin-panel .admin-sidebar { grid-row: 1; }
            .admin-panel .admin-content { grid-row: 2; }
            .admin-sidebar { position: static; min-height: auto; padding: 12px; }
            .admin-sidebar-heading { display: none; }
            .admin-sidebar-nav { grid-template-columns: repeat(4, minmax(0,1fr)); gap: 7px; padding-top: 0; }
            .admin-sidebar-nav a { min-height: 54px; justify-content: center; gap: 9px; padding: 5px 8px; font-size: .75rem; }
            .admin-sidebar-footer { display: none; }
        }
        @media (max-width: 575.98px) {
            .admin-sidebar-nav { grid-template-columns: repeat(2, minmax(0,1fr)); }
            .admin-sidebar-nav a { justify-content: flex-start; }
            .admin-sidebar-nav a > i { width: 42px; height: 42px; flex-basis: 42px; }
        }
        .authenticated-panel .campus-navbar > .container { min-height: 58px; }
        .authenticated-panel .campus-navbar .navbar-brand { gap: .65rem !important; }
        .authenticated-panel .campus-navbar .brand-mark {
            width: 38px;
            height: 38px;
            border: 1px solid rgba(126, 192, 244, .32);
            border-radius: 12px !important;
            background: linear-gradient(145deg, #54A8E8, #165A8B) !important;
            box-shadow: 0 5px 18px rgba(45, 132, 198, .22);
        }
        .authenticated-panel .campus-navbar .brand-mark .icon { width: 19px; height: 19px; fill: none; stroke: currentColor; stroke-width: 1.6; stroke-linecap: round; stroke-linejoin: round; }
        .authenticated-panel .navbar-brand-logo { font-size: 1.13rem !important; letter-spacing: -.055em; animation: none !important; text-shadow: 0 0 16px rgba(70, 160, 228, .18) !important; }
        .authenticated-panel .campus-navbar .brand-tagline { font-size: .62rem !important; color: #99A3AA !important; }
        .authenticated-panel .campus-navbar .navbar-nav { align-items: center; gap: .2rem; }
        .authenticated-panel .campus-navbar .nav-link {
            padding: .56rem .72rem !important;
            border: 1px solid transparent;
            border-radius: 10px !important;
            color: #B9BFC4 !important;
            font-size: .77rem;
            font-weight: 600;
            transition: color .18s ease, background-color .18s ease, border-color .18s ease;
        }
        .authenticated-panel .campus-navbar .nav-link i { color: var(--theme-text-secondary); transition: color .18s ease; }
        .authenticated-panel .campus-navbar .nav-link:hover,
        .authenticated-panel .campus-navbar .nav-link:focus-visible {
            color: #DCECF8 !important;
            background: rgba(70, 160, 228, .08) !important;
            border-color: rgba(70, 160, 228, .14);
            box-shadow: none !important;
        }
        .authenticated-panel .campus-navbar .nav-link:hover i,
        .authenticated-panel .campus-navbar .nav-link:focus-visible i { color: var(--theme-accent-soft); }
        .authenticated-panel .campus-navbar .nav-link.active {
            color: #B5DAF6 !important;
            background: rgba(70, 160, 228, .12) !important;
            border-color: rgba(70, 160, 228, .18);
            box-shadow: inset 0 0 16px rgba(70, 160, 228, .035) !important;
        }
        .authenticated-panel .campus-navbar .nav-link.active i { color: var(--theme-on-accent); }
        .authenticated-panel .campus-navbar .navbar-tools { gap: .45rem !important; }
        .authenticated-panel .profile-menu-avatar { display: inline-grid; width: 28px; height: 28px; flex: 0 0 28px; place-items: center; overflow: hidden; border: 1px solid rgba(126, 192, 244,.28); border-radius: 50%; background: rgba(70, 160, 228,.1); color: #82BFEF; }
        .authenticated-panel .profile-menu-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .authenticated-panel .profile-menu-avatar > i { font-size: 14px; }
        .authenticated-panel .campus-navbar .navbar-tools > .dropdown > .btn {
            min-height: 36px;
            padding-inline: .7rem !important;
            border: 1px solid rgba(235, 241, 245, .09) !important;
            border-radius: 10px !important;
            background: rgba(255, 255, 255, .035) !important;
            color: #BBC2C7 !important;
            transition: background-color .18s ease, border-color .18s ease, color .18s ease;
        }
        .authenticated-panel .campus-navbar .navbar-tools > .dropdown > .btn:hover,
        .authenticated-panel .campus-navbar .navbar-tools > .dropdown > .btn:focus-visible {
            border-color: rgba(70, 160, 228, .28) !important;
            background: rgba(70, 160, 228, .08) !important;
            color: #E2EEF8 !important;
        }
        .authenticated-panel .campus-navbar .dropdown-menu {
            padding: .42rem;
            border: 1px solid rgba(235, 241, 245, .1);
            border-radius: 14px;
            background: rgba(21, 23, 25, .98);
            box-shadow: 0 18px 42px rgba(0, 0, 0, .36);
            backdrop-filter: blur(16px);
        }
        .authenticated-panel .campus-navbar .dropdown-item {
            padding: .62rem .75rem;
            border-radius: 9px;
            color: #C3C9CE;
            font-size: .82rem;
            transition: color .18s ease, background-color .18s ease;
        }
        .authenticated-panel .campus-navbar .dropdown-item:hover,
        .authenticated-panel .campus-navbar .dropdown-item:focus-visible {
            background: rgba(70, 160, 228, .1) !important;
            color: #E5F0F9 !important;
            box-shadow: none !important;
        }
        .authenticated-panel .campus-navbar .dropdown-item.active,
        .authenticated-panel .campus-navbar .dropdown-item:active {
            background: rgba(70, 160, 228, .16) !important;
            color: #B5DAF6 !important;
            box-shadow: none !important;
        }
        .authenticated-panel .campus-navbar .navbar-toggler.portal-menu-toggle {
            display: grid;
            width: 42px;
            height: 38px;
            place-content: center;
            gap: 4px;
            border: 1px solid rgba(126, 192, 244, .24) !important;
            border-radius: 11px !important;
            background: rgba(70, 160, 228, .06) !important;
            box-shadow: none !important;
            transition: background-color .18s ease, border-color .18s ease;
        }
        .authenticated-panel .campus-navbar .portal-menu-toggle:hover,
        .authenticated-panel .campus-navbar .portal-menu-toggle:active,
        .authenticated-panel .campus-navbar .portal-menu-toggle[aria-expanded="true"] {
            border-color: rgba(126, 192, 244, .48) !important;
            background: rgba(70, 160, 228, .13) !important;
            box-shadow: none !important;
            transform: none !important;
        }
        .authenticated-panel .campus-navbar .portal-menu-toggle:focus-visible {
            outline: 2px solid rgba(126, 192, 244, .72);
            outline-offset: 2px;
        }
        .portal-menu-icon, .portal-menu-icon::before, .portal-menu-icon::after {
            display: block;
            width: 18px;
            height: 2px;
            border-radius: 2px;
            background: #C8E0F4;
            transition: transform .2s ease, opacity .2s ease, background-color .2s ease;
        }
        .portal-menu-icon { position: relative; }
        .portal-menu-icon::before, .portal-menu-icon::after { position: absolute; left: 0; content: ""; }
        .portal-menu-icon::before { transform: translateY(-6px); }
        .portal-menu-icon::after { transform: translateY(6px); }
        .portal-menu-toggle[aria-expanded="true"] .portal-menu-icon { background: transparent; }
        .portal-menu-toggle[aria-expanded="true"] .portal-menu-icon::before { transform: rotate(45deg); }
        .portal-menu-toggle[aria-expanded="true"] .portal-menu-icon::after { transform: rotate(-45deg); }
        @media (min-width: 1200px) {
            .authenticated-panel .campus-navbar.navbar-expand-xl .navbar-toggler.portal-menu-toggle { display: none; }
        }
        .authenticated-panel .campus-navbar .dropdown-divider { border-color: rgba(235, 241, 245, .1); }
        .authenticated-panel .campus-navbar .dropdown-header,
        .authenticated-panel .campus-navbar .dropdown-header .text-glow-green { color: #D9E9F6 !important; text-shadow: none !important; }
        .authenticated-panel .campus-navbar .navbar-tools .text-primary,
        .authenticated-panel .campus-navbar .navbar-tools .text-info { color: #82BFEF !important; }
        .authenticated-panel .campus-navbar .navbar-tools .bg-primary { background: rgba(70, 160, 228, .16) !important; }
        .authenticated-panel .campus-navbar .navbar-tools .border-primary { border-color: rgba(70, 160, 228, .32) !important; }
        .authenticated-panel .campus-navbar .text-glow-green,
        .authenticated-panel .campus-footer .text-glow-green { animation: none !important; }
        .authenticated-panel .campus-footer {
            background: #0D0F10;
            border-top: 1px solid rgba(235, 241, 245, .08);
            box-shadow: 0 -12px 34px rgba(0, 0, 0, .16);
        }
        .authenticated-panel .campus-footer .footer-link {
            display: inline-flex;
            align-items: center;
            color: #AAB1B7;
            transition: color .18s ease, transform .18s ease;
        }
        .authenticated-panel .campus-footer .footer-link:hover,
        .authenticated-panel .campus-footer .footer-link:focus-visible {
            color: #9AC9F0 !important;
            text-shadow: none !important;
            transform: translateX(3px);
        }
        .authenticated-panel .campus-footer .footer-link i,
        .authenticated-panel .campus-footer .text-glow-green { color: #74B5E9 !important; text-shadow: none !important; }
        .portal-footer-main { display: grid; grid-template-columns: minmax(240px, 1.5fr) repeat(2, minmax(150px, .7fr)); gap: 42px; padding-block: 38px; }
        .portal-footer-brand { display: inline-flex; align-items: center; gap: 12px; color: #EEF3F7; text-decoration: none; }
        .portal-footer-brand .brand-mark { width: 38px; height: 38px; border-radius: 12px !important; background: linear-gradient(145deg,#54A8E8,#165A8B) !important; color: #0C1217 !important; }
        .portal-footer-brand .brand-mark .icon { display: block; width: 19px; height: 19px; fill: none; stroke: currentColor; stroke-width: 1.6; stroke-linecap: round; stroke-linejoin: round; }
        .portal-footer-description { max-width: 32rem; margin: 16px 0 0; color: #9AA1A7; font-size: .85rem; line-height: 1.7; }
        .portal-footer-column h2 { margin: 0 0 14px; color: #D9E9F6; font-size: .75rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; }
        .portal-footer-column a { display: block; width: fit-content; margin: 10px 0; color: #AAB1B7; font-size: .85rem; text-decoration: none; transition: color .18s ease, transform .18s ease; }
        .portal-footer-column a:hover, .portal-footer-column a:focus-visible { transform: translateX(3px); color: #9AC9F0; }
        .portal-footer-bottom { display: flex; justify-content: space-between; gap: 16px; padding: 18px 0; border-top: 1px solid rgba(235, 241, 245,.08); color: #899096; font-size: .75rem; }
        .authenticated-panel .campus-footer .social-links { display: flex; align-items: center; gap: 8px; margin-top: 18px; }
        .authenticated-panel .campus-footer .social-links a {
            display: grid;
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            place-items: center;
            border: 1px solid rgba(235, 241, 245,.12);
            border-radius: 50%;
            color: #AAB1B7;
            text-decoration: none;
            transition: color .18s ease, border-color .18s ease, background-color .18s ease, transform .18s ease;
        }
        .authenticated-panel .campus-footer .social-links a:hover, .authenticated-panel .campus-footer .social-links a:focus-visible { border-color: rgba(70, 160, 228,.42); background: rgba(70, 160, 228,.08); color: #9AC9F0; }
        .authenticated-panel .campus-footer .social-links a:hover { transform: translateY(-2px); }
        .authenticated-panel .campus-footer .social-links svg { display: block; width: 15px; height: 15px; fill: currentColor; }
        @media (max-width: 767.98px) {
            .portal-footer-main { grid-template-columns: repeat(2, minmax(0,1fr)); gap: 30px 22px; }
            .portal-footer-brand-column { grid-column: 1 / -1; }
            .portal-footer-bottom { flex-direction: column; gap: 6px; }
        }
        @media (max-width: 1199.98px) {
            .authenticated-panel .campus-navbar .navbar-collapse {
                width: 100%;
                padding: .85rem 0 .3rem;
                border-top: 1px solid rgba(235, 241, 245, .07);
                text-align: left;
            }
            .authenticated-panel .campus-navbar .navbar-nav { width: 100%; align-items: stretch; gap: .18rem; }
            .authenticated-panel .campus-navbar .nav-item { width: 100%; }
            .authenticated-panel .campus-navbar .nav-link {
                width: 100%;
                padding: .65rem .78rem !important;
                text-align: left;
            }
            .authenticated-panel .campus-navbar .navbar-tools {
                width: 100%;
                padding-top: .8rem;
                border-top: 1px solid rgba(235, 241, 245, .07);
                justify-content: flex-start;
            }
        }
        @media (max-width: 575.98px) {
            .authenticated-panel .campus-navbar > .container { min-height: 52px; }
            .authenticated-panel .campus-navbar .brand-tagline { max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            .authenticated-panel .campus-navbar .navbar-tools { align-items: stretch !important; }
            .authenticated-panel .campus-navbar .navbar-tools > .dropdown { flex: 1 1 auto; }
            .authenticated-panel .profile-menu-name { max-width: 100px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        }
        @media (max-width: 380px) {
            .authenticated-panel .campus-navbar .brand-tagline { display: none !important; }
            .authenticated-panel .campus-navbar .navbar-tools { gap: .35rem !important; }
            .authenticated-panel .campus-navbar .navbar-tools .dropdown > .btn { padding-inline: .5rem !important; }
            .authenticated-panel .profile-menu-name { max-width: 70px; }
            .authenticated-panel .profile-photo-upload { padding: 12px; }
            .authenticated-panel .profile-remove-avatar { font-size: .72rem; }
        }
        .authenticated-panel .campus-footer .footer-link:hover { color: #9AC9F0; text-shadow: none; }
        .authenticated-panel .list-group-item { color: var(--cc-text); background: transparent; border-color: var(--cc-border); }
        .authenticated-panel .table-light { --bs-table-bg: rgba(70, 160, 228, .055); --bs-table-color: #D5DBE0; --bs-table-border-color: var(--cc-border); }
        .authenticated-panel .text-primary { color: #61B6F4 !important; }
        .authenticated-panel .bg-light { background-color: #1B1F21 !important; }
        .authenticated-panel .border-light { border-color: var(--cc-border) !important; }
        .authenticated-panel .btn-outline-success { color: #82BFEF; border-color: rgba(70, 160, 228, .38); }
        .authenticated-panel .btn-outline-success:hover { color: #0C1217; background: #61B6F4; border-color: #61B6F4; }
        .authenticated-panel .portal-hero-card { min-height: 156px; }
        .authenticated-panel .stat-card { min-height: 145px; }
        .authenticated-panel .student-summary-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .authenticated-panel .student-summary-grid > [class*="col-"] { width: auto; }
        .authenticated-panel .student-summary-grid > :last-child { grid-column: 1 / -1; }
        .authenticated-panel .student-summary-grid .stat-card { height: 100%; }
        @media (max-width: 1199.98px) {
            .authenticated-panel .student-summary-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .authenticated-panel .student-summary-grid > :last-child { grid-column: 1 / -1; }
        }
        @media (max-width: 575.98px) {
            .authenticated-panel .student-summary-grid { grid-template-columns: minmax(0, 1fr); }
        }
        .admin-stat-icon { display: grid; width: 38px; height: 38px; place-items: center; border: 1px solid currentColor; border-radius: 12px; font-size: .95rem; }
        .admin-stat-green { color: #82BFEF; background: rgba(70, 160, 228, .1); }
        .admin-stat-blue { color: #7dd3fc; background: rgba(56, 189, 248, .1); }
        .admin-stat-rose { color: #fda4af; background: rgba(251, 113, 133, .1); }
        .authenticated-panel .cc-chart-card { border-radius: 18px !important; }
        .authenticated-panel .cc-chart-empty { position: absolute; inset: 0; display: grid; place-items: center; margin: 0; padding: 1rem; color: var(--cc-muted); font-size: .9rem; text-align: center; }
        .authenticated-panel .chart-load-fallback { position: absolute; inset: 0; display: grid; place-items: center; margin: 0; padding: 1rem; color: var(--cc-muted); text-align: center; }
        .authenticated-panel .chart-load-fallback[hidden] { display: none; }
        .authenticated-panel .cc-activity-empty { min-height: 190px; display: grid; place-items: center; color: var(--cc-muted); text-align: center; }
        .authenticated-panel .cc-activity-bar i { background: linear-gradient(180deg,#61B6F4,#155D94); }
        .authenticated-panel .cc-activity-bar b { background: linear-gradient(180deg,#ff9b9b,#db5363); opacity: .8; }
        .authenticated-panel .student-announcement { background: linear-gradient(115deg, rgba(70, 160, 228,.09), rgba(21,23,25,.96)) !important; border: 1px solid rgba(70, 160, 228,.2) !important; }
        .authenticated-panel .student-announcement-icon { background: rgba(70, 160, 228,.13); color: #82BFEF; }
        .authenticated-panel .student-spend-card { background: radial-gradient(ellipse at 92% 12%, rgba(70, 160, 228,.17), transparent 38%), linear-gradient(145deg,#162027,#111517) !important; }
        .authenticated-panel .student-tip-card { background: linear-gradient(145deg,#1B1F21,#17191A) !important; }
        .authenticated-panel .student-tip-card h6 { color: #E8EDF1 !important; }
        .authenticated-panel .student-tip-card .text-secondary { color: #AAB1B7 !important; }
        html[data-bs-theme="dark"] .card:not(.modal-content) .text-dark { color: #E3E7EB !important; }
        .cc-motion-ready .scroll-reveal {
            --cc-reveal-y: 28px;
            opacity: 0;
            transform: translate3d(0, var(--cc-reveal-y), 0);
            transition: opacity .72s cubic-bezier(.2,.75,.2,1), transform .82s cubic-bezier(.2,.75,.2,1);
            transition-delay: var(--cc-reveal-delay, 0ms);
            will-change: opacity, transform;
        }
        .cc-motion-ready .scroll-reveal.revealed { --cc-reveal-y: 0px; opacity: 1; }
        .cc-chart-card { transform-style: flat; perspective: none; }
        .cc-donut-card { transform-style: preserve-3d; perspective: 1100px; }
        .cc-chart-stage {
            position: relative;
            transform: none;
            transform-style: flat;
        }
        .cc-chart-stage canvas { filter: none; }
        .cc-donut-stage {
            max-width: 360px;
            width: 100%;
            margin-inline: auto;
            transform: perspective(1100px) rotateX(8deg) translateZ(8px);
            transform-origin: center center;
            transform-style: preserve-3d;
            transition: transform .3s ease, filter .3s ease;
            filter: drop-shadow(0 13px 11px rgba(0, 0, 0, .25));
        }
        .cc-donut-card:hover .cc-donut-stage { transform: perspective(1100px) rotateX(3deg) translateZ(14px); }
        .cc-donut-stage canvas { filter: drop-shadow(0 4px 10px rgba(70, 160, 228, .08)); }
        .cc-budget-depth .progress { overflow: hidden; transform: none; box-shadow: none; }
        .cc-budget-depth .progress-bar { border-radius: inherit; box-shadow: 0 0 12px rgba(70, 160, 228, .32); }
        .cc-activity-bars { display: grid; min-height: 190px; grid-template-columns: repeat(6, minmax(0, 1fr)); align-items: end; gap: clamp(10px, 3vw, 38px); padding: 14px clamp(8px, 4vw, 42px) 0; border-bottom: 1px solid var(--cc-border); }
        .cc-activity-column { display: grid; min-width: 0; justify-items: center; gap: 8px; }
        .cc-activity-value { color: var(--cc-muted); font-size: clamp(8px, .8vw, 11px); white-space: nowrap; }
        .cc-activity-bar { display: flex; width: min(54px, 75%); height: 140px; align-items: end; justify-content: center; gap: 3px; overflow: hidden; transform: none; border-radius: 7px 7px 2px 2px; background: rgba(148, 163, 184, .08); box-shadow: inset 0 -2px rgba(70, 160, 228, .12); }
        .cc-activity-bar i, .cc-activity-bar b { display: block; width: 42%; height: var(--bar-height); background: linear-gradient(180deg,#61B6F4,#155D94); }
        .cc-activity-bar b { background: linear-gradient(180deg,#B5DAF6,#276897); opacity: .72; }
        .cc-activity-label { overflow: hidden; max-width: 100%; color: var(--cc-muted); font-size: clamp(8px, .85vw, 11px); text-overflow: ellipsis; white-space: nowrap; }
        .portal-scroll-progress { position: fixed; z-index: 1200; inset: 0 auto auto 0; width: 100%; height: 3px; transform: scaleX(0); transform-origin: left; background: linear-gradient(90deg,#46A0E4,#B1D9F8); box-shadow: 0 0 14px rgba(70, 160, 228,.58); pointer-events: none; }
        .authenticated-panel main > .container { width: 100%; padding-inline: clamp(12px, 2.2vw, 32px); }
        .authenticated-panel main .row > [class*="col-"], .authenticated-panel .admin-content, .authenticated-panel .card-body { min-width: 0; }
        .authenticated-panel img, .authenticated-panel video, .authenticated-panel canvas { max-width: 100%; }
        .authenticated-panel .table-responsive { max-width: 100%; overscroll-behavior-x: contain; }
        .authenticated-panel .form-control, .authenticated-panel .form-select { min-width: 0; }
        .authenticated-panel .modal-dialog { width: auto; max-width: calc(100% - 1rem); margin-inline: auto; }
        @media (max-width: 767.98px) {
            .authenticated-panel main.py-4 { padding-block: 1rem !important; }
            .authenticated-panel .admin-layout { gap: 12px; }
            .authenticated-panel .admin-sidebar-nav { grid-template-columns: repeat(2, minmax(0,1fr)); }
            .authenticated-panel .admin-sidebar-nav a { justify-content: flex-start; }
            .authenticated-panel .card-body { overflow-wrap: anywhere; }
            .authenticated-panel .cc-chart-stage { max-width: 100%; }
            .authenticated-panel .profile-card-header { flex-wrap: wrap; gap: 14px; }
            .authenticated-panel .profile-photo-upload { max-width: none; }
        }
        @media (max-width: 575.98px) {
            .authenticated-panel main > .container { padding-inline: 12px; }
            .authenticated-panel .card > .p-4 { padding: 1rem !important; }
            .authenticated-panel .admin-sidebar-nav a { min-width: 0; }
            .authenticated-panel .admin-sidebar-nav a span { overflow-wrap: anywhere; }
            .authenticated-panel .profile-photo-upload { align-items: stretch; }
            .authenticated-panel .modal-dialog { max-width: calc(100% - .5rem); margin-block: .5rem; }
            .authenticated-panel .modal-content { max-height: calc(100dvh - 1rem); overflow-y: auto; }
        }
        @media (max-width: 380px) {
            .authenticated-panel main > .container { padding-inline: 8px; }
            .authenticated-panel .admin-sidebar-nav { grid-template-columns: 1fr; }
            .authenticated-panel .admin-sidebar-nav a { justify-content: flex-start; }
        }
        @media (max-width: 575.98px) {
            .cc-activity-bars { gap: 6px; padding-inline: 0; }
            .cc-activity-bar { width: 80%; }
        }
        @media (prefers-reduced-motion: reduce) {
            .cc-motion-ready .scroll-reveal { opacity: 1; transform: none; transition: none; }
            .cc-donut-stage { transform: none; transition: none; }
            .coin-story, .coin-story-stage { position: relative; top: auto; min-height: 100svh; }
        }
    </style>
    <script>if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) document.documentElement.classList.add('cc-motion-ready');</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @yield('styles')
</head>
<body class="font-md @auth authenticated-panel @if(auth()->user()->isAdmin()) admin-panel @endif @else public-app-shell @endauth">
    <div class="portal-scroll-progress" aria-hidden="true"></div>
    @guest
    <header class="site-header">
        <div class="header-inner">
            <a class="brand" href="{{ route('home') }}" aria-label="Campus Coin home">
                <span class="brand-mark"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="15" rx="3"/><path d="M17 14h4M3 9h18M7 5V3h10v2"/><circle cx="17" cy="14" r=".5"/></svg></span>
                <span class="brand-name">Campus <span>Coin</span></span>
            </a>
            <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="primary-nav" aria-label="Open navigation">
                <span></span><span></span>
            </button>
            <nav class="primary-nav" id="primary-nav" aria-label="Main navigation">
                <a class="nav-link" href="{{ route('home') }}#home">Home</a>
                <a class="nav-link" href="{{ route('home') }}#how-it-works">How it works</a>
                <a class="nav-link" href="{{ route('home') }}#budgets">Budgets</a>
                <a class="nav-link" href="{{ route('home') }}#features">Features</a>
                <a class="nav-link" href="{{ route('home') }}#insights">Saving tips</a>
                <a class="nav-link nav-login {{ request()->routeIs('login') ? 'is-active' : '' }}" href="{{ route('login') }}">Log in</a>
                <a class="nav-link nav-register {{ request()->routeIs('register') ? 'is-active' : '' }}" href="{{ route('register') }}">Create account</a>
            </nav>
            <div class="header-actions">
                <div id="google_translate_element" title="Select language"></div>
                <a class="button button-light button-small" href="{{ route('register') }}">Get started <span class="button-spark"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 17 17 7M7 7h10v10"/></svg></span></a>
            </div>
            @include('layouts.partials.theme-toggle', ['themeToggleClass' => 'theme-toggle-public'])
        </div>
    </header>
    @else
    <nav class="navbar navbar-expand-xl navbar-dark sticky-top campus-navbar">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-3" href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('student.dashboard') }}">
                <div class="brand-mark text-white rounded-3 d-flex align-items-center justify-content-center shadow-sm">
                    <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="15" rx="3"/><path d="M17 14h4M3 9h18M7 5V3h10v2"/><circle cx="17" cy="14" r=".5"/></svg>
                </div>
                <div>
                    <span class="fs-4 navbar-brand-logo d-block lh-sm mb-0 text-glow-green">{{ __('ui.app_name') }}</span>
                    <span class="brand-tagline text-white-50 fw-normal d-block" style="font-size: 0.72rem; margin-top: 2px;">@if(auth()->user()->isAdmin()) Admin workspace @else {{ __('ui.tagline') }} @endif</span>
                </div>
            </a>

            <button class="navbar-toggler portal-menu-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#navContent" aria-controls="navContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="portal-menu-icon" aria-hidden="true"></span>
            </button>

            <div class="collapse navbar-collapse" id="navContent">
                @auth
                    @if(auth()->check() && auth()->user()->isStudent())
                        <ul class="navbar-nav me-auto mb-2 mb-xxl-0 ms-xxl-3">
                            <li class="nav-item"><a class="nav-link px-3 rounded-3 {{ request()->routeIs('student.dashboard') ? 'active' : '' }}" href="{{ route('student.dashboard') }}"><i class="fas fa-chart-pie me-2"></i> {{ __('ui.nav.dashboard') }}</a></li>
                            <li class="nav-item"><a class="nav-link px-3 rounded-3 {{ request()->routeIs('transactions.*') ? 'active' : '' }}" href="{{ route('transactions.index') }}"><i class="fas fa-list-alt me-2"></i> {{ __('ui.nav.transactions') }}</a></li>
                            <li class="nav-item"><a class="nav-link px-3 rounded-3 {{ request()->routeIs('budgets.*') ? 'active' : '' }}" href="{{ route('budgets.index') }}"><i class="fas fa-bullseye me-2"></i> {{ __('ui.nav.budgets') }}</a></li>
                            <li class="nav-item"><a class="nav-link px-3 rounded-3 {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}"><i class="fas fa-chart-bar me-2"></i> {{ __('ui.nav.reports') }}</a></li>
                            <li class="nav-item dropdown">
                                <button class="nav-link dropdown-toggle px-3 rounded-3 border-0 bg-transparent {{ request()->routeIs('insights.*', 'saving-tips.*', 'categories.*') ? 'active' : '' }}" id="studentToolsDropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">{{ __('ui.nav.more') }}</button>
                                <ul class="dropdown-menu" aria-labelledby="studentToolsDropdown">
                                    <li><a class="dropdown-item {{ request()->routeIs('insights.*') ? 'active' : '' }}" href="{{ route('insights.index') }}"><i class="fas fa-robot me-2 text-primary"></i>{{ __('ui.nav.insights') }}</a></li>
                                    <li><a class="dropdown-item {{ request()->routeIs('saving-tips.*') ? 'active' : '' }}" href="{{ route('saving-tips.index') }}"><i class="fas fa-lightbulb me-2 text-warning"></i>{{ __('ui.nav.saving_tips') }}</a></li>
                                    <li><a class="dropdown-item {{ request()->routeIs('categories.*') ? 'active' : '' }}" href="{{ route('categories.index') }}"><i class="fas fa-tags me-2 text-info"></i>{{ __('ui.nav.categories') }}</a></li>
                                </ul>
                            </li>
                        </ul>
                    @else
                    @endif
                @else
                        <ul class="navbar-nav me-auto mb-2 mb-xxl-0 ms-xxl-3">
                            <li class="nav-item"><a class="nav-link px-3 rounded-3" href="{{ route('home') }}#features">{{ __('ui.nav.features') }}</a></li>
                        <li class="nav-item"><a class="nav-link px-3 rounded-3" href="{{ route('home') }}#sitemap">{{ __('ui.nav.sitemap') }}</a></li>
                    </ul>
                @endauth

                <div class="navbar-tools d-flex align-items-center gap-2 flex-wrap">
                    @include('layouts.partials.theme-toggle')
                    @if(auth()->check() && auth()->user()->isStudent())
                    <div id="google_translate_element" title="{{ __('ui.theme.language') }}"></div>
                    @elseif(auth()->guest())
                    <div id="google_translate_element" title="Select language"></div>
                    @endif

                    @auth
                        <div class="dropdown">
                            <button class="btn btn-sm bg-white bg-opacity-10 text-white border border-white border-opacity-25 rounded-pill dropdown-toggle d-flex align-items-center gap-2 px-3 py-2 shadow-sm" type="button" data-bs-toggle="dropdown" aria-label="Account menu for {{ auth()->user()->name }}" data-no-translate>
                                <span class="profile-menu-avatar" aria-hidden="true">
                                    @if(auth()->user()->avatar_path)
                                        <img src="{{ route('profile.avatar') }}" alt="">
                                    @else
                                        <i class="fas {{ auth()->user()->isAdmin() ? 'fa-user-shield' : 'fa-user-graduate' }}"></i>
                                    @endif
                                </span>
                                <span class="profile-menu-name fw-semibold">{{ auth()->user()->name }}</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4" style="background: var(--cc-surface);">
                                <li>
                                    <div class="dropdown-header">
                                        <div class="fw-bold text-glow-green">{{ auth()->user()->name }}</div>
                                        <div class="text-muted small">{{ auth()->user()->email }}</div>
                                        <span class="badge bg-primary bg-opacity-25 text-primary border border-primary mt-1">{{ ucfirst(auth()->user()->role) }}</span>
                                    </div>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item rounded-3" href="{{ route('profile') }}"><i class="fas fa-id-card me-2 text-primary"></i> {{ __('ui.nav.profile') }}</a></li>
                                @if(auth()->user()->isAdmin())
                                    <li><a class="dropdown-item rounded-3" href="{{ route('admin.dashboard') }}"><i class="fas fa-user-shield me-2 text-warning"></i> {{ __('ui.nav.admin_panel') }}</a></li>
                                @else
                                    <li><a class="dropdown-item rounded-3" href="{{ route('student.dashboard') }}"><i class="fas fa-chart-pie me-2 text-success"></i> {{ __('ui.nav.dashboard') }}</a></li>
                                @endif
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger rounded-3"><i class="fas fa-sign-out-alt me-2"></i> {{ __('ui.nav.sign_out') }}</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-sm btn-purple-glow text-white px-4 py-2 fw-bold shadow-lg rounded-pill me-1 d-inline-flex align-items-center gap-2">
                            <i class="fas fa-sign-in-alt"></i> Web Login
                        </a>
                        <a href="{{ route('register') }}" class="btn btn-sm btn-glow-outline text-white px-3 py-2 fw-semibold rounded-pill d-inline-flex align-items-center gap-1">
                            <i class="fas fa-user-plus me-1"></i> {{ __('ui.nav.signup') }}
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>
    @endguest

    <main class="flex-grow-1 py-4">
        <div class="container">
            @if(auth()->check() && auth()->user()->isAdmin())
                <div class="admin-layout">
                    @include('layouts.partials.admin-sidebar')
                    <div class="admin-content">
            @endif
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3 border-0 d-flex align-items-center gap-2 mb-4" role="alert">
                    <i class="fas fa-check-circle fs-5"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-3 border-0 d-flex align-items-center gap-2 mb-4" role="alert">
                    <i class="fas fa-exclamation-triangle fs-5"></i>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-warning alert-dismissible fade show shadow-sm rounded-3 border-0 mb-4" role="alert">
                    <div class="fw-bold mb-1"><i class="fas fa-exclamation-circle me-1"></i> {{ __('ui.fix_inputs') }}</div>
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @guest
                @hasSection('breadcrumb')
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb p-2 px-3 rounded-3 shadow-sm" style="background: var(--cc-surface); border: 1px solid var(--cc-border);">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted"><i class="fas fa-home"></i> {{ __('ui.nav.home') }}</a></li>
                            @yield('breadcrumb')
                        </ol>
                    </nav>
                @endif
            @endguest

            @yield('content')
            @if(auth()->check() && auth()->user()->isAdmin())
                    </div>
                </div>
            @endif
        </div>
    </main>

    @guest
    <footer class="site-footer auth-marketing-footer">
        <div class="footer-community">
            <p class="eyebrow">Start where you are</p>
            <h2>Small choices add up.<br><span>Make every coin count.</span></h2>
            <a class="button button-light" href="{{ route('register') }}">Create your free account <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 17 17 7M7 7h10v10"/></svg></a>
        </div>
        <div class="footer-main">
            <div class="footer-brand-column">
                <a class="brand" href="{{ route('home') }}" aria-label="Campus Coin home">
                    <span class="brand-mark"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="15" rx="3"/><path d="M17 14h4M3 9h18M7 5V3h10v2"/><circle cx="17" cy="14" r=".5"/></svg></span>
                    <span class="brand-name">Campus <span>Coin</span></span>
                </a>
                <p>Know where it goes. Make room for what matters.</p>
                @include('layouts.partials.social-links')
            </div>
            <div class="footer-column">
                <h3>Your money</h3>
                <a href="{{ route('student.dashboard') }}">Dashboard</a>
                <a href="{{ route('transactions.index') }}">Transactions</a>
                <a href="{{ route('budgets.index') }}">Monthly budgets</a>
                <a href="{{ route('reports.index') }}">Reports</a>
                <a href="{{ route('saving-tips.index') }}">Saving tips</a>
            </div>
            <div class="footer-column">
                <h3>For students</h3>
                <a href="{{ route('home') }}#about">About Campus Coin</a>
                <a href="{{ route('home') }}#how-it-works">How it works</a>
                <a href="{{ route('home') }}#features">Features</a>
                <a href="{{ route('home') }}#faq">FAQ</a>
                <a href="{{ route('register') }}">Create an account</a>
            </div>
            <div class="footer-column">
                <h3>Account</h3>
                <a href="{{ route('login') }}">Log in</a>
                <a href="{{ route('register') }}">Sign up</a>
                <a href="{{ route('password.request') }}">Reset password</a>
            </div>
            <div class="footer-trust">
                <div class="footer-trust-mark"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 20 6v5c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6l8-3Z"/><path d="m8.5 12 2.2 2.2 4.8-5"/></svg>Student-first money tools</div>
                <small>No bank connection required</small>
                <a class="button button-quiet button-small" href="{{ route('register') }}">Join Campus Coin <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 17 17 7M7 7h10v10"/></svg></a>
            </div>
        </div>
        <div class="footer-bottom">
            <span>© {{ date('Y') }} Campus Coin. All rights reserved.</span>
            <span class="risk-note">A simple way to build healthy money habits.</span>
            <a href="{{ route('home') }}#home">Back to top <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 17 17 7M7 7h10v10"/></svg></a>
        </div>
    </footer>
    @else
    <footer class="campus-footer portal-footer mt-auto">
        <div class="container">
            <div class="portal-footer-main">
                <div class="portal-footer-brand-column">
                    <a class="portal-footer-brand" href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('student.dashboard') }}">
                        <span class="brand-mark d-flex align-items-center justify-content-center"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="15" rx="3"/><path d="M17 14h4M3 9h18M7 5V3h10v2"/><circle cx="17" cy="14" r=".5"/></svg></span>
                        <span><span class="fs-5 fw-bold text-glow-green d-block lh-1">{{ __('ui.app_name') }}</span><span class="text-white-50 small d-block mt-1">{{ __('ui.tagline') }}</span></span>
                    </a>
                    <p class="portal-footer-description">{{ __('ui.footer.about') }}</p>
                    @include('layouts.partials.social-links')
                </div>
                <nav class="portal-footer-column" aria-label="Workspace links">
                    <h2>{{ auth()->user()->isAdmin() ? 'Admin workspace' : 'Your money' }}</h2>
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}">{{ __('ui.nav.admin_overview') }}</a>
                        <a href="{{ route('admin.categories') }}">{{ __('ui.nav.admin_categories') }}</a>
                        <a href="{{ route('admin.announcements') }}">{{ __('ui.nav.admin_announcements') }}</a>
                        <a href="{{ route('admin.users') }}">{{ __('ui.nav.admin_users') }}</a>
                    @else
                        <a href="{{ route('student.dashboard') }}">{{ __('ui.nav.dashboard') }}</a>
                        <a href="{{ route('transactions.index') }}">{{ __('ui.nav.transactions') }}</a>
                        <a href="{{ route('budgets.index') }}">{{ __('ui.nav.budgets') }}</a>
                        <a href="{{ route('reports.index') }}">{{ __('ui.nav.reports') }}</a>
                    @endif
                </nav>
                <nav class="portal-footer-column" aria-label="Campus Coin links">
                    <h2>Campus Coin</h2>
                    <a href="{{ route('home') }}#about">About Campus Coin</a>
                    <a href="{{ route('home') }}#how-it-works">How it works</a>
                    <a href="{{ route('home') }}#features">Features</a>
                    <a href="{{ route('profile') }}">{{ __('ui.nav.profile') }}</a>
                </nav>
            </div>
            <div class="portal-footer-bottom">
                <span>© {{ date('Y') }} Campus Coin. All rights reserved.</span>
                <a href="{{ route('home') }}#home" class="footer-link">Back to Campus Coin</a>
            </div>
        </div>
    </footer>
    @endguest

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        if (window.Chart) {
            Chart.defaults.color = getComputedStyle(document.documentElement).getPropertyValue('--theme-text-secondary').trim();
            Chart.defaults.borderColor = getComputedStyle(document.documentElement).getPropertyValue('--theme-chart-grid').trim();
            Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
        } else {
            document.querySelectorAll('.chart-load-fallback').forEach((fallback) => { fallback.hidden = false; });
        }

        /* Scroll reveals, chart depth, and reading progress shared by both portals. */
        document.addEventListener('DOMContentLoaded', function () {
            const motionReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            const revealElements = [...document.querySelectorAll(
                'main .container > :not(nav):not(.admin-layout), main .container .card:not(.modal .card), main .container .alert, main .container .table-responsive'
            )];
            const progressBar = document.querySelector('.portal-scroll-progress');
            let revealIndex = 0;

            revealElements.forEach((element) => {
                element.classList.add('scroll-reveal');
                element.style.setProperty('--cc-reveal-delay', `${(revealIndex++ % 4) * 65}ms`);
                if (element.matches('.cc-chart-card') || element.querySelector('canvas')) {
                    element.classList.add('cc-chart-card');
                }
            });

            if (!motionReduced && 'IntersectionObserver' in window) {
                const revealObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('revealed');
                            observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.12, rootMargin: '0px 0px -35px 0px' });

                revealElements.forEach((element) => revealObserver.observe(element));
            } else {
                revealElements.forEach((element) => element.classList.add('revealed'));
            }

            const updateScrollProgress = () => {
                const scrollableHeight = document.documentElement.scrollHeight - window.innerHeight;
                const progress = scrollableHeight > 0 ? window.scrollY / scrollableHeight : 0;
                if (progressBar) progressBar.style.transform = `scaleX(${Math.min(1, Math.max(0, progress))})`;
            };
            let scrollFrame = 0;
            window.addEventListener('scroll', () => {
                if (scrollFrame) return;
                scrollFrame = window.requestAnimationFrame(() => {
                    updateScrollProgress();
                    scrollFrame = 0;
                });
            }, { passive: true });
            updateScrollProgress();

            if (!motionReduced && window.matchMedia('(pointer: fine)').matches) {
                document.querySelectorAll('.cc-donut-card').forEach((card) => {
                    const stage = card.querySelector('.cc-donut-stage');
                    if (!stage) return;
                    card.addEventListener('pointermove', (event) => {
                        const bounds = card.getBoundingClientRect();
                        const x = (event.clientX - bounds.left) / bounds.width - 0.5;
                        const y = (event.clientY - bounds.top) / bounds.height - 0.5;
                        stage.style.transform = `perspective(1100px) rotateX(${(8 - y * 5).toFixed(2)}deg) rotateY(${(x * 6).toFixed(2)}deg) translateZ(10px)`;
                    });
                    card.addEventListener('pointerleave', () => { stage.style.transform = ''; });
                });
            }
        });

    </script>
    @if(auth()->guest() || (auth()->check() && auth()->user()->isStudent()))
        <script>
            function googleTranslateElementInit() {
                new google.translate.TranslateElement({
                    pageLanguage: 'en',
                    autoDisplay: false
                }, 'google_translate_element');
            }
        </script>
        <script src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
    @endif
    @yield('scripts')
</body>
</html>
