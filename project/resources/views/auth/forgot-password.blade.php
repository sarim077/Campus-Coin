@extends('layouts.app')

@section('title', 'Forgot Password - Campus Coin')

@section('content')
<div class="row justify-content-center py-4">
    <div class="col-md-6 col-lg-5">
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="card-header bg-dark text-white p-4 text-center border-0">
                <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width:48px; height:48px;">
                    <i class="fas fa-key fs-4"></i>
                </div>
                <h4 class="fw-bold mb-0">Password Recovery</h4>
                <p class="text-secondary small mb-0">Enter your email to receive a password reset code</p>
            </div>
            <div class="card-body p-4">
                @if(session('status'))
                    <div class="alert alert-success border-0 shadow-sm mb-4">
                        <div class="fw-bold"><i class="fas fa-check-circle me-1"></i> Check your inbox</div>
                        <div class="small mt-1">{{ session('status') }}</div>
                    </div>
                @endif

                <form action="{{ route('password.email') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-secondary">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                            <input type="email" name="email" class="form-control bg-light border-start-0 @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="you@example.com" autocomplete="email" required autofocus>
                        </div>
                        @error('email')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-success w-100 py-2.5 rounded-3 fw-bold shadow-sm mb-3">
                        <i class="fas fa-paper-plane me-2"></i> Send Verification Code
                    </button>
                </form>

                <div class="text-center small text-muted my-2 border-top pt-3">
                    Remember your password? <a href="{{ route('login') }}" class="text-success fw-bold text-decoration-none">Back to Log In</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
