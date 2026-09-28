@extends('layouts.app')

@section('title', 'Manage Categories - Campus Coin')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item active">Category Management</li>
@endsection

@section('content')
<div class="page-intro d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1">Category Management</h3>
        <p class="text-secondary small mb-0">View default system categories and add custom personal income/expense categories.</p>
    </div>
    <button type="button" class="btn btn-success rounded-pill px-3 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
        <i class="fas fa-plus me-1"></i> Add Custom Category
    </button>
</div>

<div class="row g-4">
    <!-- Income Categories Column -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
            <div class="card-header bg-success text-white p-3 border-0 d-flex align-items-center justify-content-between">
                <h5 class="fw-bold mb-0"><i class="fas fa-hand-holding-usd me-2"></i> Income Categories</h5>
                <span class="badge bg-white text-success">System & Custom</span>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @foreach($defaultIncomeCategories as $c)
                        <li class="list-group-item p-3 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <div class="icon-box bg-success bg-opacity-10 text-success">
                                    <i class="{{ $c->icon ?? 'fas fa-coins' }}"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark" data-no-translate>{{ $c->name }}</h6>
                                    <span class="badge bg-secondary bg-opacity-25 text-dark text-xs">System Default</span>
                                </div>
                            </div>
                        </li>
                    @endforeach

                    @foreach($userIncomeCategories as $c)
                        <li class="list-group-item p-3 d-flex align-items-center justify-content-between bg-light bg-opacity-50">
                            <div class="d-flex align-items-center gap-3">
                                <div class="icon-box bg-primary bg-opacity-10 text-primary">
                                    <i class="{{ $c->icon ?? 'fas fa-tag' }}"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark" data-no-translate>{{ $c->name }}</h6>
                                    <span class="badge bg-primary bg-opacity-25 text-primary text-xs">Custom Personal</span>
                                </div>
                            </div>
                            <form action="{{ route('categories.destroy', $c->id) }}" method="POST" onsubmit="return confirm('Delete this custom category?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-light btn-sm text-danger rounded-circle"><i class="fas fa-trash"></i></button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    <!-- Expense Categories Column -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
            <div class="card-header bg-danger text-white p-3 border-0 d-flex align-items-center justify-content-between">
                <h5 class="fw-bold mb-0"><i class="fas fa-shopping-cart me-2"></i> Expense Categories</h5>
                <span class="badge bg-white text-danger">System & Custom</span>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @foreach($defaultExpenseCategories as $c)
                        <li class="list-group-item p-3 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <div class="icon-box bg-danger bg-opacity-10 text-danger">
                                    <i class="{{ $c->icon ?? 'fas fa-box-open' }}"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark" data-no-translate>{{ $c->name }}</h6>
                                    <span class="badge bg-secondary bg-opacity-25 text-dark text-xs">System Default</span>
                                </div>
                            </div>
                        </li>
                    @endforeach

                    @foreach($userExpenseCategories as $c)
                        <li class="list-group-item p-3 d-flex align-items-center justify-content-between bg-light bg-opacity-50">
                            <div class="d-flex align-items-center gap-3">
                                <div class="icon-box bg-danger bg-opacity-10 text-danger">
                                    <i class="{{ $c->icon ?? 'fas fa-tag' }}"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark" data-no-translate>{{ $c->name }}</h6>
                                    <span class="badge bg-primary bg-opacity-25 text-primary text-xs">Custom Personal</span>
                                </div>
                            </div>
                            <form action="{{ route('categories.destroy', $c->id) }}" method="POST" onsubmit="return confirm('Delete this custom category?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-light btn-sm text-danger rounded-circle"><i class="fas fa-trash"></i></button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Create Custom Category Modal -->
<div class="modal fade" id="createCategoryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-white p-3 border-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-folder-plus text-success me-2"></i> Add Personal Category</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('categories.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Category Type</label>
                        <select name="type" class="form-select" required>
                            <option value="expense">Expense Category</option>
                            <option value="income">Income Category</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Category Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Lab Fees, Freelance Design, Gaming" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Icon Class (FontAwesome)</label>
                        <input type="text" name="icon" class="form-control" value="fas fa-tag" placeholder="fas fa-tag">
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm">Save Custom Category</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
