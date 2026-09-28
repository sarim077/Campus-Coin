@extends('layouts.app')

@section('title', __('ui.auth.welcome_back') . ' - Campus Coin')

@section('content')
<div class="auth-page">
    <div class="auth-shell auth-shell-login">
        <aside class="auth-story">
            <a href="{{ route('home') }}" class="auth-story-brand text-decoration-none">
                <span class="brand-mark"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="15" rx="3"/><path d="M17 14h4M3 9h18M7 5V3h10v2"/><circle cx="17" cy="14" r=".5"/></svg></span>
                <span>Campus <span style="color:var(--theme-accent-soft)">Coin</span></span>
            </a>
            <div class="auth-story-copy">
                <p class="eyebrow">Your money, in focus</p>
                <h1>Make room for what <span>matters.</span></h1>
                <p>Pick up right where you left off. Your student budget, recent activity, and savings progress are ready when you are.</p>
                <ul class="auth-story-points">
                    <li><i class="fas fa-check"></i>See your monthly picture</li>
                    <li><i class="fas fa-check"></i>Keep your plan flexible</li>
                    <li><i class="fas fa-check"></i>No bank link required</li>
                </ul>
            </div>
            <span class="auth-story-note">Small choices add up. You’ve got this.</span>
        </aside>

        <section class="auth-form-panel" aria-labelledby="login-heading">
            <div class="auth-form-heading">
                <p class="eyebrow">{{ $adminLogin ? 'Administrator access' : 'Welcome back' }}</p>
                <h2 id="login-heading">{{ $adminLogin ? 'Administrator sign in' : __('ui.auth.welcome_back') }}</h2>
                <p>{{ $adminLogin ? 'Administrator access. Only admin accounts may sign in here.' : __('ui.auth.sign_in_subtitle') . ' Students and administrators can sign in here.' }}</p>
            </div>

            <form action="{{ $adminLogin ? route('admin.login.submit') : route('login') }}" method="POST" class="auth-form">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label">{{ __('ui.auth.email') }}</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input id="email" type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email') }}" placeholder="you@university.edu" autocomplete="username"
                            required autofocus aria-describedby="email-error">
                    </div>
                    @error('email')<div id="email-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label for="password" class="form-label mb-0">{{ __('ui.auth.password') }}</label>
                        <a href="{{ route('password.request') }}" class="auth-inline-link text-decoration-none small">{{ __('ui.auth.forgot') }}</a>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                            placeholder="Enter your password" autocomplete="current-password" required aria-describedby="password-error">
                        <button class="auth-password-toggle" type="button" data-password-toggle="password" aria-label="Show password" aria-pressed="false">
                            <i class="fas fa-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                    @error('password')<div id="password-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="d-flex align-items-center justify-content-between gap-3 mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                        <label class="form-check-label" for="remember">{{ __('ui.auth.remember') }}</label>
                    </div>
                    <span class="text-muted small">Private by design</span>
                </div>

                <button type="submit" class="btn btn-glow-green auth-submit w-100 fw-bold">
                    {{ __('ui.auth.login_btn') }} <i class="fas fa-arrow-right ms-2"></i>
                </button>
            </form>

            <div class="auth-footnote">
                @if($adminLogin)
                    Student account? <a href="{{ route('login') }}" class="text-decoration-none">Student sign in</a>
                @else
                    {{ __('ui.auth.no_account') }}
                    <a href="{{ route('register') }}" class="text-decoration-none">{{ __('ui.auth.signup_here') }}</a>
                    <span class="d-block mt-2">Administrator? <a href="{{ route('admin.login') }}" class="text-decoration-none">Direct admin sign in</a></span>
                @endif
            </div>
        </section>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.passwordToggle);
            if (!input) return;
            const visible = input.type === 'password';
            input.type = visible ? 'text' : 'password';
            button.setAttribute('aria-pressed', String(visible));
            button.setAttribute('aria-label', visible ? 'Hide password' : 'Show password');
            button.innerHTML = `<i class="fas fa-eye${visible ? '-slash' : ''}" aria-hidden="true"></i>`;
        });
    });
</script>
@endsection
