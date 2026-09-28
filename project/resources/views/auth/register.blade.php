@extends('layouts.app')

@section('title', __('ui.auth.create_account') . ' - Campus Coin')

@section('content')
<div class="auth-page">
    <div class="auth-shell auth-shell-register">
        <aside class="auth-story">
            <a href="{{ route('home') }}" class="auth-story-brand text-decoration-none">
                <span class="brand-mark"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="15" rx="3"/><path d="M17 14h4M3 9h18M7 5V3h10v2"/><circle cx="17" cy="14" r=".5"/></svg></span>
                <span>Campus <span style="color:var(--theme-accent-soft)">Coin</span></span>
            </a>
            <div class="auth-story-copy">
                <p class="eyebrow">A fresh start, your way</p>
                <h1>Build a money plan that <span>fits you.</span></h1>
                <p>Start with a simple overview, set a few goals, and make adjustments as campus life changes. You’re always in control.</p>
                <ul class="auth-story-points">
                    <li><i class="fas fa-check"></i>Made for student life</li>
                    <li><i class="fas fa-check"></i>Your own budget and goals</li>
                    <li><i class="fas fa-check"></i>Start without linking a bank</li>
                </ul>
            </div>
            <span class="auth-story-note">Make every coin count, one step at a time.</span>
        </aside>

        <section class="auth-form-panel auth-form-panel-register" aria-labelledby="register-heading">
            <div class="auth-form-heading">
                <p class="eyebrow">Join Campus Coin</p>
                <h2 id="register-heading">{{ __('ui.auth.create_account') }}</h2>
                <p>{{ __('ui.auth.register_subtitle') }}</p>
            </div>

            <form action="{{ route('register') }}" method="POST" class="auth-form">
                @csrf
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">{{ __('ui.auth.full_name') }}</label>
                        <input id="name" type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name') }}" placeholder="e.g., Alex Morgan" autocomplete="name"
                            maxlength="255" required autofocus aria-describedby="name-error">
                        @error('name')<div id="name-error" class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">{{ __('ui.auth.email') }}</label>
                        <input id="email" type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email') }}" placeholder="student@university.edu" autocomplete="email"
                            maxlength="255" required aria-describedby="email-error">
                        @error('email')<div id="email-error" class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="password" class="form-label">{{ __('ui.auth.password') }}</label>
                        <div class="input-group">
                            <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                                placeholder="At least 6 characters" autocomplete="new-password" minlength="6"
                                required aria-describedby="password-error">
                            <button class="auth-password-toggle" type="button" data-password-toggle="password" aria-label="Show password" aria-pressed="false">
                                <i class="fas fa-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                        @error('password')<div id="password-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="password_confirmation" class="form-label">{{ __('ui.auth.confirm_password') }}</label>
                        <div class="input-group">
                            <input id="password_confirmation" type="password" name="password_confirmation" class="form-control"
                                placeholder="Enter it once more" autocomplete="new-password" minlength="6" required>
                            <button class="auth-password-toggle" type="button" data-password-toggle="password_confirmation" aria-label="Show password" aria-pressed="false">
                                <i class="fas fa-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="auth-profile-fields mb-4">
                    <h3 class="auth-section-label"><i class="fas fa-sliders-h"></i> Your starting point <span class="text-muted fw-normal">(you can change this later)</span></h3>
                    <div class="mb-3">
                        <label for="academic_year" class="form-label">{{ __('ui.auth.academic_year') }}</label>
                        <select id="academic_year" name="academic_year" class="form-select">
                            <option value="Freshman Year 2026" @selected(old('academic_year', 'Senior Year 2026') === 'Freshman Year 2026')>Freshman Year (1st Year)</option>
                            <option value="Sophomore Year 2026" @selected(old('academic_year', 'Senior Year 2026') === 'Sophomore Year 2026')>Sophomore Year (2nd Year)</option>
                            <option value="Junior Year 2026" @selected(old('academic_year', 'Senior Year 2026') === 'Junior Year 2026')>Junior Year (3rd Year)</option>
                            <option value="Senior Year 2026" @selected(old('academic_year', 'Senior Year 2026') === 'Senior Year 2026')>Senior Year (4th Year)</option>
                            <option value="Postgraduate / Master" @selected(old('academic_year') === 'Postgraduate / Master')>Postgraduate / Masters</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="region" class="form-label">Country/region</label>
                        <select id="region" name="region" class="form-select @error('region') is-invalid @enderror" required aria-describedby="region-help region-error">
                            <option value="">Choose your country or region</option>
                            @foreach($regions as $code => $regionOption)
                                <option value="{{ $code }}" @selected(old('region') === $code)>
                                    {{ $regionOption['name'] }} ({{ $regionOption['currency'] }})
                                </option>
                            @endforeach
                        </select>
                        <div id="region-help" class="form-text">Your currency display will match this choice. Site language stays English until you change it.</div>
                        @error('region')<div id="region-error" class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-glow-green auth-submit w-100 fw-bold">
                    {{ __('ui.auth.register_btn') }} <i class="fas fa-arrow-right ms-2"></i>
                </button>
            </form>

            <div class="auth-footnote">
                {{ __('ui.auth.have_account') }}
                <a href="{{ route('login') }}" class="text-decoration-none">{{ __('ui.auth.login_here') }}</a>
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
