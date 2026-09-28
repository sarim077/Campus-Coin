@extends('layouts.app')

@section('title', 'Saving Tips Engine - Campus Coin')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item active">Saving Tips Engine</li>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="fas fa-lightbulb text-warning me-2"></i> Personalized Saving Tips Engine</h3>
        <p class="text-secondary small mb-0">Add your expenses and budgets, then generate tips to see suggestions based on your own spending. AI suggestions are optional.</p>
    </div>
    <form action="{{ route('saving-tips.generate') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-warning text-dark rounded-pill px-4 fw-bold shadow-sm">
            <i class="fas fa-lightbulb me-1"></i> Generate My Tips
        </button>
    </form>
</div>

<!-- Tips List -->
<div class="row g-4">
    @forelse($tips as $tip)
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 position-relative stat-card border-top border-4 {{ $tip->status == 'pinned' ? 'border-warning' : 'border-success' }}">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="icon-box bg-warning bg-opacity-10 text-warning">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark" data-no-translate>{{ $tip->tip_title }}</h5>
                                            <span class="badge bg-secondary bg-opacity-10 text-dark text-xs" data-no-translate>
                                {{ $tip->category?->name ?? 'General Allowance Goal' }}
                                                @if($tip->is_ai_generated)<span class="ms-1">· AI</span>@endif
                                            </span>
                        </div>
                    </div>
                    <div class="d-flex gap-1">
                        <form action="{{ route('saving-tips.bookmark', $tip->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-light btn-sm rounded-circle" title="Bookmark Tip">
                                <i class="{{ $tip->is_bookmarked ? 'fas fa-bookmark text-warning' : 'far fa-bookmark text-secondary' }}"></i>
                            </button>
                        </form>
                        <form action="{{ route('saving-tips.status', $tip->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" value="{{ $tip->status == 'pinned' ? 'active' : 'pinned' }}">
                            <button type="submit" class="btn btn-light btn-sm rounded-circle" title="{{ $tip->status == 'pinned' ? 'Unpin Tip' : 'Pin Tip to Dashboard' }}">
                                <i class="fas fa-thumbtack {{ $tip->status == 'pinned' ? 'text-warning' : 'text-secondary' }}"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <p class="text-secondary small mb-3 flex-grow-1" data-no-translate>{{ $tip->tip_body }}</p>

                <form action="{{ route('saving-tips.note', $tip->id) }}" method="POST" class="mb-3">
                    @csrf
                    <label class="form-label small fw-semibold" for="tip-note-{{ $tip->id }}">Your private note</label>
                    <textarea id="tip-note-{{ $tip->id }}" name="user_note" class="form-control form-control-sm" rows="2" maxlength="4000" placeholder="Add a reminder for yourself...">{{ $tip->user_note }}</textarea>
                    <button class="btn btn-sm btn-outline-secondary mt-2" type="submit">Save note</button>
                </form>

                <form action="{{ route('saving-tips.email', $tip->id) }}" method="POST" class="d-flex gap-2 mb-3">
                    @csrf
                    <input type="email" name="email" class="form-control form-control-sm" placeholder="Share by email" aria-label="Email address to share this tip with" required>
                    <button class="btn btn-sm btn-outline-primary" type="submit">Email</button>
                </form>

                <div class="d-flex align-items-center justify-content-between pt-3 border-top">
                    <div>
                        <span class="text-muted text-xs d-block">Estimated Savings Impact</span>
                        <strong class="text-success fs-5">+{{ $currencySymbol }}{{ number_format($tip->potential_savings, 2) }}/month</strong>
                    </div>
                    <form action="{{ route('saving-tips.status', $tip->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="status" value="dismissed">
                        <button type="submit" class="btn btn-outline-secondary btn-sm rounded-pill">Dismiss Tip</button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center text-muted py-5">
            <i class="fas fa-lightbulb fs-1 text-warning mb-3 opacity-50"></i>
            <h5>No Active Saving Tips Yet</h5>
            <p class="small">Add some expenses on the Transactions page. Then return here and click <strong>Generate My Tips</strong>. Your tips will appear below this button.</p>
        </div>
    @endforelse
</div>
@endsection
