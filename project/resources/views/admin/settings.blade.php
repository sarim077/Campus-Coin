@extends('layouts.app')

@section('title', 'Site Settings - Campus Coin')

@section('content')
<section class="card border-0 shadow-sm rounded-4 p-4">
    <div class="d-flex align-items-start gap-3 mb-4">
        <span class="admin-stat-icon admin-stat-green"><i class="fas fa-sliders-h" aria-hidden="true"></i></span>
        <div>
            <h1 class="h3 fw-bold mb-1">Site settings</h1>
            <p class="text-muted mb-0">Manage the currency symbol and social links shown across Campus Coin.</p>
        </div>
    </div>

    <form action="{{ route('admin.settings.update') }}" method="POST">
        @csrf
        @method('PUT')

        <h2 class="h6 fw-bold text-success mb-3">Currency</h2>
        <div class="mb-4" style="max-width: 420px;">
            <label for="currency-symbol" class="form-label">Currency symbol</label>
            <input id="currency-symbol" name="currency_symbol" class="form-control @error('currency_symbol') is-invalid @enderror" value="{{ old('currency_symbol', $settings->currency_symbol) }}" maxlength="8" required>
            <div class="form-text">Examples: $, €, £, ¥, ₹, or AED.</div>
            <div class="form-text">This changes the displayed symbol only; existing amounts are not converted.</div>
            @error('currency_symbol')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <h2 class="h6 fw-bold text-success mb-2">Social links</h2>
        <p class="small text-muted mb-3">Add full HTTPS profile or channel URLs. Blank Facebook, X, and LinkedIn fields keep their share links; other blank fields are hidden.</p>
        <div class="row g-3 mb-4">
            @foreach([
                'facebook_url' => ['Facebook', 'https://facebook.com/your-page', 'fab fa-facebook-f'],
                'instagram_url' => ['Instagram', 'https://instagram.com/your-account', 'fab fa-instagram'],
                'x_url' => ['X', 'https://x.com/your-account', 'fab fa-x-twitter'],
                'linkedin_url' => ['LinkedIn', 'https://linkedin.com/company/your-page', 'fab fa-linkedin-in'],
                'youtube_url' => ['YouTube', 'https://youtube.com/@your-channel', 'fab fa-youtube'],
                'whatsapp_url' => ['WhatsApp', 'https://wa.me/15551234567', 'fab fa-whatsapp'],
            ] as $field => [$label, $placeholder, $icon])
                <div class="col-md-6">
                    <label for="{{ $field }}" class="form-label">{{ $label }}</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="{{ $icon }}" aria-hidden="true"></i></span>
                        <input id="{{ $field }}" type="url" name="{{ $field }}" class="form-control @error($field) is-invalid @enderror" value="{{ old($field, $settings->{$field}) }}" placeholder="{{ $placeholder }}" autocomplete="url">
                    </div>
                    @error($field)<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
            @endforeach
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold">
                <i class="fas fa-save me-2" aria-hidden="true"></i>Save site settings
            </button>
        </div>
    </form>
</section>
@endsection
