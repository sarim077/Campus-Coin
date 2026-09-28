@extends('layouts.app')

@section('title', 'Reset Password - Campus Coin')

@section('content')
<div class="row justify-content-center py-4">
    <div class="col-md-6 col-lg-5">
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="card-header bg-dark text-white p-4 text-center border-0">
                <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width:48px; height:48px;">
                    <i class="fas fa-lock-open fs-4"></i>
                </div>
                @if($codeVerified)
                    <h4 class="fw-bold mb-0">Set New Password</h4>
                    <p class="text-secondary small mb-0">Verification complete for {{ $email }}. Choose a new password.</p>
                @else
                    <h4 class="fw-bold mb-0">Verify Recovery Code</h4>
                    <p class="text-secondary small mb-0">Enter the code sent to your email address.</p>
                @endif
            </div>
            <div class="card-body p-4">
                @if(session('status'))
                    <div class="alert alert-success border-0 shadow-sm mb-4">{{ session('status') }}</div>
                @endif

                @if($codeVerified)
                    <form action="{{ route('password.update') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">New Password</label>
                            <input type="password" name="password" class="form-control bg-light @error('password') is-invalid @enderror" autocomplete="new-password" minlength="6" required autofocus>
                            @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold small text-secondary">Confirm New Password</label>
                            <input type="password" name="password_confirmation" class="form-control bg-light" autocomplete="new-password" minlength="6" required>
                        </div>

                        <button type="submit" class="btn btn-success w-100 py-2.5 rounded-3 fw-bold shadow-sm">
                            <i class="fas fa-check-circle me-2"></i> Update Password
                        </button>
                    </form>
                @else
                    @error('code')
                        <div class="alert alert-danger border-0 shadow-sm mb-4">{{ $message }}</div>
                    @enderror
                    <form action="{{ route('password.code.verify') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Email Address</label>
                            <input type="email" name="email" class="form-control bg-light @error('email') is-invalid @enderror" value="{{ old('email') }}" autocomplete="email" required autofocus>
                            @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-secondary">6-digit verification code</label>
                            <input type="text" name="code" class="form-control bg-light @error('code') is-invalid @enderror" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100 py-2.5 rounded-3 fw-bold shadow-sm">
                            <i class="fas fa-check-circle me-2"></i> Verify Code
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
