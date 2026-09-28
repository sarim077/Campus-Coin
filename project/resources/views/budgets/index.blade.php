@extends('layouts.app')

@section('title', 'Category Budget Goals - Campus Coin')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item active">Budget Goals</li>
@endsection

@section('content')
<div class="page-intro d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1">Budget Goals & Real-Time Alerts</h3>
        <p class="text-secondary small mb-0">Set monthly caps per spending category and monitor consumption progress bars.</p>
    </div>
    <div>
        <form action="{{ route('budgets.index') }}" method="GET" class="d-flex align-items-center gap-2">
            <label class="small fw-bold text-muted me-1">Select Month:</label>
            <input type="month" name="month" class="form-control form-control-sm" value="{{ $month }}" onchange="this.form.submit()">
        </form>
    </div>
</div>

<!-- Active Alerts Banner -->
@php
    $exceededCount = $budgetDetails->where('status', 'exceeded')->count();
    $warningCount = $budgetDetails->where('status', 'warning')->count();
@endphp

@if($exceededCount > 0 || $warningCount > 0)
    <div class="alert alert-warning border-0 shadow-sm rounded-4 mb-4 p-3 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-warning bg-opacity-25 text-dark rounded-circle d-flex align-items-center justify-content-center" style="width:44px; height:44px;">
                <i class="fas fa-exclamation-triangle fs-5"></i>
            </div>
            <div>
                <h6 class="fw-bold text-dark mb-0">Budget Consumption Alert</h6>
                <div class="small text-secondary">
                    @if($exceededCount > 0)
                        <strong class="text-danger">{{ $exceededCount }} category budget(s) exceeded!</strong>
                    @endif
                    @if($warningCount > 0)
                        <span class="text-warning text-dark">{{ $warningCount }} category budget(s) nearing limit (&ge;80%).</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif

@if($hiddenCategories->isNotEmpty())
    <section class="card border-0 shadow-sm rounded-4 p-3 p-md-4 mb-4" aria-labelledby="hidden-budget-categories-title">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h5 id="hidden-budget-categories-title" class="fw-bold mb-1">Hidden categories</h5>
                <p class="text-secondary small mb-0">These categories are hidden only from this budget page. Their budgets and spending are unchanged.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($hiddenCategories as $category)
                    <form action="{{ route('budgets.categories.show', $category) }}" method="POST" class="d-flex align-items-center gap-2">
                        @csrf
                        <input type="hidden" name="month" value="{{ $month }}">
                        <span class="small fw-semibold">{{ $category->name }}</span>
                        <button type="submit" class="btn btn-outline-primary btn-sm rounded-pill">
                            <i class="fas fa-eye me-1" aria-hidden="true"></i>Show
                            <span class="visually-hidden">{{ $category->name }} in budget section</span>
                        </button>
                    </form>
                @endforeach
            </div>
        </div>
    </section>
@endif

<!-- Budgets Grid -->
<div class="row g-4">
    @foreach($budgetDetails as $b)
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 stat-card budget-card h-100 position-relative overflow-hidden">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="icon-box bg-danger bg-opacity-10 text-danger">
                            <i class="{{ $b['icon'] ?? 'fas fa-box-open' }}"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark" data-no-translate>{{ $b['category_name'] }}</h5>
                            <span class="text-muted text-xs">{{ date('F Y', strtotime($month . '-01')) }}</span>
                        </div>
                    </div>
                    @if($b['status'] == 'exceeded')
                        <span class="badge bg-danger"><i class="fas fa-exclamation-triangle"></i> Exceeded</span>
                    @elseif($b['status'] == 'warning')
                        <span class="badge bg-warning text-dark"><i class="fas fa-exclamation-circle"></i> Nearing Limit</span>
                    @elseif($b['limit_amount'] > 0)
                        <span class="badge bg-success"><i class="fas fa-check"></i> Safe</span>
                    @else
                        <span class="badge bg-secondary">No Limit</span>
                    @endif
                </div>

                <div class="d-flex align-items-baseline justify-content-between mb-1">
                    <span class="text-muted small">Spent: <strong class="budget-card-amount text-dark">{{ $currencySymbol }}{{ number_format($b['spent'], 2) }}</strong></span>
                    <span class="text-muted small">Limit: <strong class="budget-card-amount text-dark">{{ $currencySymbol }}{{ number_format($b['limit_amount'], 2) }}</strong></span>
                </div>
                <div class="small text-secondary mb-1">Funded from income: <strong class="budget-card-amount text-dark">{{ $currencySymbol }}{{ number_format($b['funded'], 2) }}</strong> / {{ $currencySymbol }}{{ number_format($b['limit_amount'], 2) }}</div>

                <!-- Real-time Progress Bar -->
                <div class="progress rounded-pill mb-3" style="height: 10px;">
                    <div class="progress-bar {{ $b['status'] == 'exceeded' ? 'bg-danger' : ($b['status'] == 'warning' ? 'bg-warning' : 'bg-success') }}"
                         role="progressbar" style="width: {{ $b['percentage'] }}%;"></div>
                </div>

                <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                    <span class="text-muted text-xs">Remaining: {{ $currencySymbol }}{{ number_format($b['remaining'], 2) }}</span>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3"
                                onclick="openBudgetModal({{ $b['category_id'] }}, {{ Js::from($b['category_name']) }}, {{ $b['limit_amount'] }})">
                            <i class="fas fa-edit me-1"></i> {{ $b['limit_amount'] > 0 ? 'Edit Limit' : 'Set Limit' }}
                        </button>
                        <form action="{{ route('budgets.categories.hide', $b['category_id']) }}" method="POST">
                            @csrf
                            <input type="hidden" name="month" value="{{ $month }}">
                            <button type="submit" class="btn btn-outline-secondary btn-sm rounded-pill" aria-label="Hide {{ $b['category_name'] }} from budget section">
                                <i class="fas fa-eye-slash me-1" aria-hidden="true"></i>Hide
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
    @if($budgetDetails->isEmpty())
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 p-4 text-center">
                <h5 class="fw-bold">No budget categories to show</h5>
                <p class="text-secondary small mb-0">
                    @if($hiddenCategories->isNotEmpty())
                        Show a hidden category above to add it back to this page.
                    @else
                        There are no expense categories available for budgeting.
                    @endif
                </p>
            </div>
        </div>
    @endif
</div>

<!-- Set / Edit Budget Limit Modal -->
<div class="modal fade" id="budgetModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-white p-3 border-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-bullseye text-success me-2"></i> Set Category Monthly Limit</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('budgets.store') }}" method="POST">
                @csrf
                <input type="hidden" name="month" value="{{ $month }}">
                <input type="hidden" name="category_id" id="modalCategoryId">

                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Target Category</label>
                        <input type="text" id="modalCategoryName" class="form-control bg-light" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Monthly Limit ({{ $currencySymbol }})</label>
                        <div class="input-group">
                            <span class="input-group-text">{{ $currencySymbol }}</span>
                            <input type="number" step="0.01" name="limit_amount" id="modalLimitAmount" class="form-control" placeholder="e.g. 50.00" required min="0">
                        </div>
                        <div class="form-text">Set to 0 to remove monthly budget constraint for this category.</div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm">Save Budget Limit</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function openBudgetModal(catId, catName, limit) {
        document.getElementById('modalCategoryId').value = catId;
        document.getElementById('modalCategoryName').value = catName;
        document.getElementById('modalLimitAmount').value = limit;

        const modal = new bootstrap.Modal(document.getElementById('budgetModal'));
        modal.show();
    }
</script>
@endsection
