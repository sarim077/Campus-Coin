@extends('layouts.app')

@section('title', 'Admin Control Panel - Campus Coin')

@section('breadcrumb')
<li class="breadcrumb-item active">Administrator Portal</li>
@endsection

@section('content')
<div class="card border-0 shadow-lg rounded-4 p-4 mb-4 position-relative overflow-hidden portal-hero-card">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
            <span class="badge mb-2 px-3 py-1 rounded-pill portal-role-badge"><i class="fas fa-user-shield me-1"></i> ADMIN WORKSPACE</span>
            <h2 class="fw-bold mb-1 text-white">Good day, {{ auth()->user()->name }}</h2>
            <p class="text-white-50 small mb-0">Manage student accounts and keep the platform running smoothly.</p>
        </div>
        <a href="{{ route('admin.users') }}" class="btn btn-glow-outline rounded-pill px-4">Manage students <i class="fas fa-arrow-right ms-1"></i></a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-lg rounded-4 p-4 stat-card">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-white-50 small fw-bold">REGISTERED STUDENTS</span>
                <span class="admin-stat-icon admin-stat-green"><i class="fas fa-user-graduate"></i></span>
            </div>
            <h3 class="fw-bold text-white mb-1 fs-3">{{ number_format($totalUsers) }}</h3>
            <span class="text-success small fw-semibold"><i class="fas fa-check-circle me-1"></i>{{ number_format($activeUsers) }} active accounts</span>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-lg rounded-4 p-4 stat-card">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-white-50 small fw-bold">ACTIVE ACCOUNTS</span>
                <span class="admin-stat-icon admin-stat-green"><i class="fas fa-user-check"></i></span>
            </div>
            <h3 class="fw-bold text-success mb-1 fs-3">{{ number_format($activeUsers) }}</h3>
            <span class="text-white-50 small">Students able to sign in</span>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-lg rounded-4 p-4 stat-card">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-white-50 small fw-bold">DISABLED ACCOUNTS</span>
                <span class="admin-stat-icon admin-stat-rose"><i class="fas fa-user-slash"></i></span>
            </div>
            <h3 class="fw-bold text-danger mb-1 fs-3">{{ number_format($disabledUsers) }}</h3>
            <span class="text-white-50 small">Accounts currently disabled</span>
        </div>
    </div>
</div>
@endsection
