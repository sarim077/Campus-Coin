@extends('layouts.app')

@section('title', 'Transactions Log - Campus Coin')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item active">Transactions Log</li>
@endsection

@section('content')
@if($errors->any())
    <div class="alert alert-danger rounded-4" role="alert">
        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif
<div class="page-intro d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1">Income & Expense Transactions</h3>
        <p class="text-secondary small mb-0">Record, edit, filter, and import historical transaction records.</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-success rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#csvImportModal">
            <i class="fas fa-file-csv me-1"></i> Bulk CSV Import
        </button>
        <a href="{{ route('transactions.create') }}" class="btn btn-success rounded-pill px-3 fw-bold shadow-sm">
            <i class="fas fa-plus me-1"></i> Add Transaction
        </a>
        <a href="{{ route('transactions.index', $showDeleted ? [] : ['show_deleted' => 1]) }}" class="btn btn-outline-secondary rounded-pill px-3">
            {{ $showDeleted ? 'View active transactions' : 'Deleted history' }}
        </a>
    </div>
</div>

<!-- Filter Bar -->
<div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-light">
    <form action="{{ route('transactions.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-2">
            <input type="text" name="search" class="form-control form-control-sm bg-white" placeholder="Search description..." value="{{ request('search') }}">
        </div>
        <div class="col-md-2">
            <select name="type" class="form-select form-select-sm bg-white">
                <option value="">All Types</option>
                <option value="income" {{ request('type') == 'income' ? 'selected' : '' }}>Income Only</option>
                <option value="expense" {{ request('type') == 'expense' ? 'selected' : '' }}>Expenses Only</option>
            </select>
        </div>
        <div class="col-md-2">
            <select name="category_id" class="form-select form-select-sm bg-white">
                <option value="">All Categories</option>
                @foreach($categories as $c)
                    <option value="{{ $c->id }}" {{ request('category_id') == $c->id ? 'selected' : '' }}>
                        [{{ ucfirst($c->type) }}] {{ $c->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <input type="date" name="start_date" class="form-control form-control-sm bg-white" value="{{ request('start_date') }}" title="Start Date">
        </div>
        <div class="col-md-2">
            <input type="date" name="end_date" class="form-control form-control-sm bg-white" value="{{ request('end_date') }}" title="End Date">
        </div>
        <div class="col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-success btn-sm w-100 rounded-pill"><i class="fas fa-filter me-1"></i> Filter</button>
            <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill"><i class="fas fa-undo"></i></a>
        </div>
    </form>
</div>

<!-- Transactions Table -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="table-responsive">
        <table class="table align-middle table-hover mb-0">
            <thead class="table-dark small">
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th>Recurring</th>
                    <th class="text-end">Amount</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody class="small">
                @forelse($transactions as $tx)
                    <tr>
                        <td class="text-muted fw-semibold">{{ $tx->date->format('Y-m-d') }}</td>
                        <td>
                            <span class="badge {{ $tx->type == 'income' ? 'badge-income' : 'badge-expense' }} text-capitalize px-2 py-1">
                                <i class="{{ $tx->type == 'income' ? 'fas fa-arrow-down' : 'fas fa-arrow-up' }} me-1"></i> {{ $tx->type }}
                            </span>
                        </td>
                        <td class="fw-bold">{{ $tx->description }}</td>
                        <td>
                            <span class="badge bg-secondary bg-opacity-10 text-dark border">
                                <i class="{{ $tx->category?->icon ?? 'fas fa-tag' }} me-1"></i> {{ $tx->category?->name ?? 'Uncategorized' }}
                            </span>
                        </td>
                        <td>
                            @if($tx->is_recurring)
                                <span class="badge bg-info bg-opacity-10 text-info border border-info">
                                    <i class="fas fa-sync-alt me-1"></i> {{ ucfirst($tx->recurrence_frequency ?? 'Monthly') }}
                                </span>
                            @else
                                <span class="text-muted text-xs">—</span>
                            @endif
                        </td>
                        <td class="text-end fw-bold fs-6 {{ $tx->type == 'income' ? 'text-success' : 'text-danger' }}">
                            {{ $tx->type == 'income' ? '+' : '-' }}{{ $currencySymbol }}{{ number_format($tx->amount, 2) }}
                        </td>
                        <td class="text-end">
                            @if($tx->trashed())
                                <form action="{{ route('transactions.restore', $tx->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-success btn-sm">Restore</button>
                                </form>
                            @else
                                <a href="{{ route('transactions.edit', $tx->id) }}" class="btn btn-light btn-sm text-primary rounded-circle" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('transactions.destroy', $tx->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Move this transaction to deleted history?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-light btn-sm text-danger rounded-circle" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="fas fa-folder-open fs-1 mb-2 d-block text-secondary opacity-50"></i>
                            No transactions found matching your criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($transactions->hasPages())
        <div class="card-footer bg-white p-3 border-0 d-flex justify-content-center">
            {{ $transactions->links() }}
        </div>
    @endif
</div>

<!-- Bulk CSV Import Modal -->
<div class="modal fade" id="csvImportModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-white p-3 border-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-file-csv text-success me-2"></i> Bulk Import CSV Transactions</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('transactions.import-csv') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <p class="text-secondary small mb-3">
                        Upload historical transactions in CSV format. Review suggested categories and make corrections before anything is saved.
                    </p>
                    <div class="bg-light p-3 rounded-3 border small mb-3">
                        <strong>Expected CSV Columns:</strong><br>
                        <code>Date, Type (income/expense), Amount, Description, Category</code><br>
                        <span class="text-muted">Example: <code>2026-09-20, expense, 15.50, Campus Canteen Lunch, Food</code></span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Select CSV File</label>
                        <input type="file" name="csv_file" class="form-control" accept=".csv, .txt" required>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm">Process CSV Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@if($errors->has('csv_file'))
<script>
    document.addEventListener('DOMContentLoaded', () => {
        bootstrap.Modal.getOrCreateInstance(document.getElementById('csvImportModal')).show();
    });
</script>
@endif
@endsection
