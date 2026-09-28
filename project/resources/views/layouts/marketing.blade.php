<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#071426">
    <meta name="description" content="Campus Coin helps students track spending, plan monthly budgets, and build savings without linking a bank account.">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v=4">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v=4">
    <title>@yield('title', 'Campus Coin')</title>
    @include('layouts.partials.theme-init')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .goog-te-banner-frame.skiptranslate { display: none !important; }
        body { top: 0 !important; }
        .goog-logo-link, .goog-te-gadget span, .goog-te-gadget img { display: none !important; }
        .goog-te-gadget { font-size: 0 !important; }
        #google_translate_element { min-width: 0; }
        #google_translate_element .goog-te-gadget { display: flex; align-items: center; }
        #google_translate_element select {
            width: 138px; height: 38px; padding: .42rem 1.8rem .42rem .75rem;
            color: #fff; background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.22); border-radius: 999px;
            font: 600 .78rem/1.2 "Plus Jakarta Sans", sans-serif;
            cursor: pointer; appearance: auto;
        }
        #google_translate_element select option { color: #17212b; background: #fff; }
        .skiptranslate iframe { visibility: hidden !important; }
    </style>
    @yield('styles')
</head>
<body class="fx-site">
    <svg class="icon-library" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <symbol id="icon-arrow" viewBox="0 0 24 24"><path d="M7 17 17 7M7 7h10v10"/></symbol>
        <symbol id="icon-arrow-down" viewBox="0 0 24 24"><path d="M12 4v15m-6-6 6 6 6-6"/></symbol>
        <symbol id="icon-wallet" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="15" rx="3"/><path d="M17 14h4M3 9h18M7 5V3h10v2"/><circle cx="17" cy="14" r=".5"/></symbol>
        <symbol id="icon-spark" viewBox="0 0 24 24"><path d="m12 2-1.7 7.1L4 12l6.3 2.9L12 22l1.7-7.1L20 12l-6.3-2.9L12 2Z"/><path d="m19 2-.8 2.2L16 5l2.2.8L19 8l.8-2.2L22 5l-2.2-.8L19 2ZM4 16l-.8 2.2L1 19l2.2.8L4 22l.8-2.2L7 19l-2.2-.8L4 16Z"/></symbol>
        <symbol id="icon-clock" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></symbol>
        <symbol id="icon-shield" viewBox="0 0 24 24"><path d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11Z"/><path d="m9 12 2 2 4-4"/></symbol>
        <symbol id="icon-chart" viewBox="0 0 24 24"><path d="M3 3v18h18M7 14l4-4 3 3 6-7"/></symbol>
        <symbol id="icon-bolt" viewBox="0 0 24 24"><path d="m13 2-3 8h7L9 22l2-9H5l8-11Z"/></symbol>
        <symbol id="icon-diamond" viewBox="0 0 24 24"><path d="m3 9 4-6h10l4 6-9 12L3 9Z"/><path d="M3 9h18M7 3l5 18 5-18M3 9l4-6m14 6-4-6"/></symbol>
        <symbol id="icon-globe" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a15 15 0 0 1 0 18M12 3a15 15 0 0 0 0 18"/></symbol>
        <symbol id="icon-plus" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></symbol>
        <symbol id="icon-check" viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"/></symbol>
        <symbol id="icon-star" viewBox="0 0 24 24"><path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-3-5.6 3 1.1-6.2L3 9.6l6.2-.9L12 3Z"/></symbol>
        <symbol id="icon-play" viewBox="0 0 24 24"><path d="m9 6 10 6-10 6V6Z"/></symbol>
    </svg>

    <header class="site-header">
        <div class="header-inner">
            <a class="brand" href="{{ auth()->check() ? $accountDestination : route('home') }}" aria-label="Campus Coin home">
                <span class="brand-mark"><svg class="icon"><use href="#icon-wallet"/></svg></span><span class="brand-name">Campus <span>Coin</span></span>
            </a>

            <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="primary-nav" aria-label="Open navigation">
                <span></span><span></span>
            </button>

            <nav class="primary-nav" id="primary-nav" aria-label="Main navigation">
                <a class="nav-link is-active" href="#home">Home</a>
                <a class="nav-link" href="#how-it-works">How it works</a>
                <a class="nav-link" href="#budgets">Budgets</a>
                <a class="nav-link" href="#features">Features</a>
                <a class="nav-link" href="#insights">Saving tips</a>
                <a class="nav-link nav-login" href="{{ $accountDestination }}">{{ $accountDestinationLabel }}</a>
                <a class="nav-link nav-register" href="{{ route('register') }}">Create account</a>
            </nav>

            <div class="header-actions">
                @guest
                    <div id="google_translate_element" title="Select language"></div>
                @endguest
                <a class="button button-light button-small" href="{{ auth()->check() ? $accountDestination : route('register') }}">{{ auth()->check() ? $accountDestinationLabel : 'Get started' }} <span class="button-spark"><svg class="icon"><use href="#icon-arrow"/></svg></span></a>
            </div>
            @include('layouts.partials.theme-toggle', ['themeToggleClass' => 'theme-toggle-public'])
        </div>
    </header>

    <main>@yield('content')</main>

    <footer class="site-footer">
        <div class="footer-community">
            <p class="eyebrow">Start where you are</p>
            <h2>Small choices add up.<br><span>Make every coin count.</span></h2>
            <a class="button button-light" href="{{ route('register') }}">Create your free account <svg class="icon"><use href="#icon-arrow"/></svg></a>
        </div>
        <div class="footer-main">
            <div class="footer-brand-column">
                <a class="brand" href="{{ auth()->check() ? $accountDestination : route('home') }}"><span class="brand-mark"><svg class="icon"><use href="#icon-wallet"/></svg></span><span class="brand-name">Campus <span>Coin</span></span></a>
                <p>Know where it goes. Make room for what matters.</p>
                @include('layouts.partials.social-links')
            </div>
            <div class="footer-column">
                <h3>Your money</h3>
                <a href="{{ route('student.dashboard') }}">Dashboard</a><a href="{{ route('transactions.index') }}">Transactions</a><a href="{{ route('budgets.index') }}">Monthly budgets</a><a href="{{ route('reports.index') }}">Reports</a><a href="{{ route('saving-tips.index') }}">Saving tips</a>
            </div>
            <div class="footer-column">
                <h3>For students</h3>
                <a href="#about">About Campus Coin</a><a href="#how-it-works">How it works</a><a href="#features">Features</a><a href="#faq">FAQ</a><a href="{{ route('register') }}">Create an account</a>
            </div>
            <div class="footer-column">
                <h3>Account</h3>
                <a href="{{ $accountDestination }}">{{ $accountDestinationLabel }}</a><a href="{{ route('register') }}">Sign up</a><a href="{{ route('password.request') }}">Reset password</a>
            </div>
            <div class="footer-trust">
                <div class="footer-trust-mark"><svg class="icon"><use href="#icon-shield"/></svg>Student-first money tools</div>
                <small>No bank connection required</small>
                <a class="button button-quiet button-small" href="{{ route('register') }}">Join Campus Coin <svg class="icon"><use href="#icon-arrow"/></svg></a>
            </div>
        </div>
        <div class="footer-bottom">
            <span>© {{ date('Y') }} Campus Coin. All rights reserved.</span>
            <span class="risk-note">A simple way to build healthy money habits.</span>
            <a href="#home">Back to top <svg class="icon"><use href="#icon-arrow"/></svg></a>
        </div>
    </footer>

    <div class="cookie-banner" role="dialog" aria-label="Cookie preferences" hidden>
        <span class="cookie-icon"><svg class="icon"><use href="#icon-globe"/></svg></span>
        <p>Campus Coin uses essential cookies to keep your account working and remember your preferences.</p>
        <div class="cookie-actions"><button type="button" data-cookie-choice="declined">Decline</button><button class="cookie-accept" type="button" data-cookie-choice="accepted">Accept</button></div>
    </div>

    @guest
        <script>
            function googleTranslateElementInit() {
                new google.translate.TranslateElement({
                    pageLanguage: 'en',
                    autoDisplay: false
                }, 'google_translate_element');
            }
        </script>
        <script src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
    @endguest
    @yield('scripts')
</body>
</html>
