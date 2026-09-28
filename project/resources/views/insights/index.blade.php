@extends('layouts.app')

@section('title', 'AI Monthly Insights - Campus Coin')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item active">AI Monthly Insights</li>
@endsection

@section('content')
<div class="page-intro d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="fas fa-robot text-success me-2"></i> AI Monthly Spending Insights</h3>
        <p class="text-secondary small mb-0">Plain-language narrative summaries analyzing monthly trends, high-growth categories, and actionable advice.</p>
    </div>
    <form action="{{ route('insights.generate') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm">
            <i class="fas fa-sparkles me-1"></i> Generate Monthly Insight
        </button>
    </form>
</div>

<!-- Current Month Insight Card -->
@if($currentInsight)
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-gradient position-relative border-start border-5 border-success">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-success bg-opacity-25 text-success border border-success px-3 py-1">
                    <i class="fas fa-calendar-alt me-1"></i> {{ date('F Y', strtotime($currentInsight->month . '-01')) }} (Current Month)
                </span>
                <span class="text-muted text-xs">Generated {{ $currentInsight->generated_at->diffForHumans() }}</span>
            </div>
            <form action="{{ route('insights.bookmark', $currentInsight->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-light btn-sm rounded-pill" title="Bookmark Insight">
                    <i class="{{ $currentInsight->is_bookmarked ? 'fas fa-bookmark text-warning' : 'far fa-bookmark text-secondary' }}"></i>
                </button>
            </form>
        </div>

        <h5 class="fw-bold text-dark mb-2"><i class="fas fa-comment-alt me-2 text-success"></i> Monthly Narrative Summary</h5>
        <p class="text-secondary mb-3 fs-6 leading-relaxed" data-no-translate>{{ $currentInsight->summary_text }}</p>

        <div class="p-3 bg-white rounded-3 border border-warning">
            <h6 class="fw-bold text-dark mb-1"><i class="fas fa-flag text-warning me-2"></i> Flagged Spending Pattern & Actionable Guidance</h6>
            <p class="text-secondary small mb-0" data-no-translate>{{ $currentInsight->tip_text }}</p>
        </div>
        <form action="{{ route('insights.note', $currentInsight->id) }}" method="POST" class="mt-3">
            @csrf
            <label class="form-label small fw-semibold" for="current-insight-note">Your private note</label>
            <textarea id="current-insight-note" name="user_note" class="form-control form-control-sm" rows="2" maxlength="4000" placeholder="Add a reminder for yourself...">{{ $currentInsight->user_note }}</textarea>
            <button class="btn btn-sm btn-outline-secondary mt-2">Save note</button>
        </form>
        <form action="{{ route('insights.email', $currentInsight->id) }}" method="POST" class="d-flex gap-2 mt-3">
            @csrf
            <input type="email" name="email" class="form-control form-control-sm" placeholder="Share this summary by email" aria-label="Email address to share this summary with" required>
            <button class="btn btn-sm btn-outline-primary">Email summary</button>
        </form>
    </div>
@else
    <div class="card border-0 shadow-sm rounded-4 p-5 text-center mb-4 bg-light">
        <i class="fas fa-robot fs-1 text-success mb-3 opacity-50"></i>
        <h5 class="fw-bold" data-no-translate>No AI Insight Generated for {{ date('F Y') }} Yet</h5>
        <p class="text-secondary small mb-3">Click below to trigger the AI spending pattern analyzer on your current month transactions.</p>
        <div>
            <form action="{{ route('insights.generate') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold" data-no-translate>Generate {{ date('F Y') }} Insight</button>
            </form>
        </div>
    </div>
@endif

<!-- Insight History -->
<h5 class="fw-bold mb-3"><i class="fas fa-history text-secondary me-2"></i> Past Insights History</h5>
<div class="row g-4">
    @forelse($insights as $ins)
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fw-bold text-dark"><i class="fas fa-calendar me-1 text-success"></i> {{ date('F Y', strtotime($ins->month . '-01')) }}</span>
                    <form action="{{ route('insights.bookmark', $ins->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-link btn-sm text-secondary p-0">
                            <i class="{{ $ins->is_bookmarked ? 'fas fa-bookmark text-warning' : 'far fa-bookmark' }}"></i>
                        </button>
                    </form>
                </div>
                <p class="text-secondary small mb-3" data-no-translate>{{ Str::limit($ins->summary_text, 140) }}</p>
                <div class="p-2 bg-light rounded small text-muted" data-no-translate>
                    <strong>Advice:</strong> {{ Str::limit($ins->tip_text, 120) }}
                </div>
                <form action="{{ route('insights.note', $ins->id) }}" method="POST" class="mt-3">
                    @csrf
                    <label class="form-label small fw-semibold" for="insight-note-{{ $ins->id }}">Your private note</label>
                    <textarea id="insight-note-{{ $ins->id }}" name="user_note" class="form-control form-control-sm" rows="2" maxlength="4000" placeholder="Add a reminder for yourself...">{{ $ins->user_note }}</textarea>
                    <button class="btn btn-sm btn-outline-secondary mt-2">Save note</button>
                </form>
                <form action="{{ route('insights.email', $ins->id) }}" method="POST" class="d-flex gap-2 mt-3">
                    @csrf
                    <input type="email" name="email" class="form-control form-control-sm" placeholder="Share by email" aria-label="Email address to share this insight with" required>
                    <button class="btn btn-sm btn-outline-primary">Email</button>
                </form>
            </div>
        </div>
    @empty
        <div class="col-12 text-center text-muted py-4">No historical insights recorded yet.</div>
    @endforelse
</div>
@endsection
