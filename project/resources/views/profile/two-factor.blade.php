@extends('layouts.app')

@section('title', 'Two-factor authentication - Campus Coin')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('profile') }}">Profile</a></li>
<li class="breadcrumb-item active">Two-factor authentication</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <h1 class="h4 fw-bold">Two-factor authentication</h1>
                <p class="text-secondary">Add a second sign-in check using Google Authenticator, Microsoft Authenticator, 1Password, or another TOTP-compatible app. This is optional for both student and administrator accounts.</p>

                @if(session('success'))
                    <div class="alert alert-success" role="status">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif

                @if(session('recovery_codes'))
                    <div class="alert alert-warning" role="alert">
                        <h2 class="h6 fw-bold">Save these recovery codes now</h2>
                        <p>Each code works once if you lose access to your authenticator. They will not be shown again.</p>
                        <pre class="bg-white p-3 rounded user-select-all" id="recovery-codes">{{ implode("\n", session('recovery_codes')) }}</pre>
                        <button class="btn btn-sm btn-outline-dark" type="button" onclick="navigator.clipboard.writeText(document.getElementById('recovery-codes').textContent)">Copy recovery codes</button>
                    </div>
                @endif

                @if($user->hasTwoFactorEnabled())
                    <div class="alert alert-success">Authenticator protection is enabled since {{ $user->two_factor_confirmed_at->format('M j, Y') }}.</div>
                    <h2 class="h6 fw-bold mt-4">Replace recovery codes</h2>
                    <form action="{{ route('profile.two-factor.recovery-codes') }}" method="POST" class="row g-2">
                        @csrf
                        <div class="col-md-5"><input type="password" name="password" class="form-control" placeholder="Current password" autocomplete="current-password" required></div>
                        <div class="col-md-5"><input name="code" class="form-control" placeholder="Authenticator or recovery code" required></div>
                        <div class="col-md-2"><button class="btn btn-outline-success w-100">Replace</button></div>
                    </form>
                    <h2 class="h6 fw-bold mt-4">Turn off two-factor authentication</h2>
                    <form action="{{ route('profile.two-factor.disable') }}" method="POST" class="row g-2">
                        @csrf
                        <div class="col-md-5"><input type="password" name="password" class="form-control" placeholder="Current password" autocomplete="current-password" required></div>
                        <div class="col-md-5"><input name="code" class="form-control" placeholder="Authenticator or recovery code" required></div>
                        <div class="col-md-2"><button class="btn btn-outline-danger w-100">Disable</button></div>
                    </form>
                @elseif($secret)
                    <h2 class="h6 fw-bold mt-4">1. Scan this QR code</h2>
                    <div class="border rounded p-3 d-inline-block bg-white two-factor-qr">{!! $qrCode !!}</div>
                    <p class="small text-secondary mt-2">If scanning is unavailable, enter this setup key manually:</p>
                    <code class="user-select-all">{{ $secret }}</code>
                    <p class="small text-secondary mt-3">The account will appear in your authenticator as <strong>Campus Coin ({{ $user->email }})</strong>.</p>
                    <h2 class="h6 fw-bold mt-4">2. Confirm the 6-digit code</h2>
                    <form action="{{ route('profile.two-factor.enable') }}" method="POST" class="row g-2">
                        @csrf
                        <div class="col-md-8"><input name="code" class="form-control" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" placeholder="123456" required></div>
                        <div class="col-md-4"><button class="btn btn-success w-100">Confirm and enable</button></div>
                    </form>
                @else
                    <div class="alert alert-secondary">Two-factor authentication is currently off.</div>
                    <form action="{{ route('profile.two-factor.setup') }}" method="POST">
                        @csrf
                        <button class="btn btn-success">Set up authenticator</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
