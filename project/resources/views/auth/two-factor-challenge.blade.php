@extends('layouts.app')

@section('title', 'Two-step verification - Campus Coin')

@section('content')
<div class="row justify-content-center py-5">
    <div class="col-md-6 col-lg-5">
        <div class="card border-0 shadow-lg rounded-4">
            <div class="card-body p-4">
                <h1 class="h4 fw-bold">Two-step verification</h1>
                <p class="text-secondary">Enter the 6-digit code from your authenticator app, or use one of your single-use recovery codes.</p>
                <form action="{{ route('two-factor.verify') }}" method="POST">
                    @csrf
                    <label for="two-factor-code" class="form-label">Authenticator or recovery code</label>
                    <input id="two-factor-code" name="code" class="form-control @error('code') is-invalid @enderror" autocomplete="one-time-code" inputmode="text" required autofocus>
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <button class="btn btn-success w-100 mt-3" type="submit">Verify and continue</button>
                </form>
                <a class="btn btn-link w-100 mt-2" href="{{ route('login') }}">Cancel sign in</a>
            </div>
        </div>
    </div>
</div>
@endsection
