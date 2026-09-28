@extends('layouts.app')

@section('title', ucfirst($user->role) . ' Profile - Campus Coin')

@section('breadcrumb')
<li class="breadcrumb-item active">User Profile</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-10 col-xxl-9">
        <div class="card profile-card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header profile-card-header text-white p-4 d-flex align-items-center justify-content-between border-0">
                <div class="d-flex align-items-center gap-3">
                    <div class="profile-avatar text-white rounded-circle d-flex align-items-center justify-content-center">
                        @if($user->avatar_path)
                            <img src="{{ route('profile.avatar') }}" alt="{{ $user->name }} profile photo">
                        @else
                            <i class="fas {{ $user->isAdmin() ? 'fa-user-shield' : 'fa-user-graduate' }}" aria-hidden="true"></i>
                        @endif
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0" data-no-translate>{{ $user->name }}</h4>
                        <div class="text-secondary small">{{ $user->email }} • <span class="badge bg-success bg-opacity-25 text-success">{{ ucfirst($user->role) }}</span></div>
                    </div>
                </div>
                @if($user->avatar_path)
                    <form action="{{ route('profile.avatar.delete') }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-light btn-sm rounded-pill profile-remove-avatar" aria-label="Remove profile photo">
                            <i class="fas fa-trash-alt me-1" aria-hidden="true"></i> Remove photo
                        </button>
                    </form>
                @endif
            </div>
            <div class="card-body p-4">
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <h6 class="profile-section-title fw-bold mb-3"><i class="fas fa-camera me-2" aria-hidden="true"></i> Profile Photo</h6>
                    <div class="profile-photo-upload mb-4">
                        <div class="flex-grow-1">
                            <label for="profile-avatar" class="form-label fw-bold small text-secondary">Upload a photo</label>
                            <input id="profile-avatar" type="file" name="avatar" accept="image/jpeg,image/png,image/webp" class="form-control @error('avatar') is-invalid @enderror" aria-describedby="profile-avatar-help">
                            <div id="profile-avatar-help" class="form-text">JPG, PNG, or WebP; maximum file size 5 MB. Saving your profile applies the photo.</div>
                            @error('avatar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <h6 class="profile-section-title fw-bold mb-3"><i class="fas fa-id-card me-2" aria-hidden="true"></i> Account Information</h6>
                    <div class="alert alert-info d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <span>Authenticator-app two-factor security is {{ $user->hasTwoFactorEnabled() ? 'enabled' : 'available to enable' }}.</span>
                        <a href="{{ route('profile.two-factor') }}" class="btn btn-sm btn-outline-primary">Manage two-factor security</a>
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="profile-name" class="form-label fw-bold small text-secondary">Full Name</label>
                            <input id="profile-name" type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" autocomplete="name" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="profile-email" class="form-label fw-bold small text-secondary">Email Address (Primary Login)</label>
                            <input id="profile-email" type="email" class="form-control profile-readonly" value="{{ $user->email }}" autocomplete="email" disabled>
                            <div class="form-text">Email address cannot be modified.</div>
                        </div>
                    </div>

                    <h6 class="profile-section-title fw-bold mb-3"><i class="fas fa-sliders-h me-2" aria-hidden="true"></i> Academic & Financial Goals Profile</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label for="academic-year" class="form-label fw-bold small text-secondary">Academic Year</label>
                            <input id="academic-year" type="text" name="academic_year" class="form-control @error('academic_year') is-invalid @enderror" value="{{ old('academic_year', $user->academic_year) }}" placeholder="Senior Year 2026">
                            @error('academic_year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        @if($user->isStudent())
                            <div class="col-md-4">
                                <label for="profile-region" class="form-label fw-bold small text-secondary">Country/region</label>
                                <input id="profile-region" type="text" class="form-control profile-readonly" value="{{ config('regions.'.$user->region.'.name', 'Not set') }} ({{ config('regions.'.$user->region.'.currency', $currencySymbol) }})" disabled>
                                <div class="form-text">Currency is set from your registration choice. Existing amounts are not converted.</div>
                            </div>
                        @endif
                        <div class="col-md-4">
                            <label for="monthly-allowance" class="form-label fw-bold small text-secondary">Monthly Allowance Baseline ({{ $currencySymbol }})</label>
                            <div class="input-group">
                                <span class="input-group-text">{{ $currencySymbol }}</span>
                                <input id="monthly-allowance" type="number" step="0.01" min="0" name="monthly_allowance_baseline" class="form-control @error('monthly_allowance_baseline') is-invalid @enderror" value="{{ old('monthly_allowance_baseline', $user->monthly_allowance_baseline) }}">
                            </div>
                            @error('monthly_allowance_baseline')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label for="monthly-savings-goal" class="form-label fw-bold small text-secondary">Savings Goal Amount ({{ $currencySymbol }})</label>
                            <div class="input-group">
                                <span class="input-group-text">{{ $currencySymbol }}</span>
                                <input id="monthly-savings-goal" type="number" step="0.01" min="0" name="monthly_savings_goal" class="form-control @error('monthly_savings_goal') is-invalid @enderror" value="{{ old('monthly_savings_goal', $user->monthly_savings_goal) }}">
                            </div>
                            @error('monthly_savings_goal')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label for="savings-goal-due-date" class="form-label fw-bold small text-secondary">Savings Goal Date</label>
                            <input id="savings-goal-due-date" type="date" name="savings_goal_due_date" class="form-control @error('savings_goal_due_date') is-invalid @enderror" value="{{ old('savings_goal_due_date', $user->savings_goal_due_date?->format('Y-m-d')) }}" min="{{ now()->toDateString() }}" @if((float) $user->monthly_savings_goal > 0) required @endif>
                            <div class="form-text">Choose the day you want to have saved this amount. We’ll email you the day before if you’re short, and on the due date if you reach it.</div>
                            @error('savings_goal_due_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="profile-security-heading">
                        <h6 class="profile-section-title profile-security-title fw-bold mb-1"><i class="fas fa-lock me-2" aria-hidden="true"></i> Change Password</h6>
                        <p class="profile-security-help mb-3">Optional. Leave both fields empty to keep your current password.</p>
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="new-password" class="form-label fw-bold small text-secondary">New Password</label>
                            <input id="new-password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Enter a new password" autocomplete="new-password">
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="new-password-confirmation" class="form-label fw-bold small text-secondary">Confirm New Password</label>
                            <input id="new-password-confirmation" type="password" name="password_confirmation" class="form-control" placeholder="Re-enter the new password" autocomplete="new-password">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-success profile-save-button rounded-pill px-4 fw-bold shadow-sm">
                            <i class="fas fa-save me-2"></i> Save Profile Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
