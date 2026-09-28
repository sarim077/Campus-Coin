@extends('layouts.app')

@section('title', 'Edit Transaction - Campus Coin')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item"><a href="{{ route('transactions.index') }}">Transactions</a></li>
<li class="breadcrumb-item active">Edit #{{ $transaction->id }}</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-dark text-white p-4 border-0">
                <h4 class="fw-bold mb-0"><i class="fas fa-edit text-warning me-2"></i> Edit Transaction Entry</h4>
                <p class="text-secondary small mb-0">Update transaction values while maintaining history integrity</p>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('transactions.update', $transaction->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Transaction Type</label>
                        <div>
                            <span class="badge {{ $transaction->type == 'income' ? 'badge-income' : 'badge-expense' }} fs-6 text-capitalize p-2">
                                <i class="{{ $transaction->type == 'income' ? 'fas fa-arrow-down' : 'fas fa-arrow-up' }} me-1"></i> {{ $transaction->type }}
                            </span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Description</label>
                        <input type="text" name="description" class="form-control" value="{{ old('description', $transaction->description) }}" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-secondary">Amount ({{ $currencySymbol }})</label>
                            <div class="input-group">
                                <span class="input-group-text">{{ $currencySymbol }}</span>
                                <input type="number" step="0.01" name="amount" class="form-control" value="{{ old('amount', $transaction->amount) }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-secondary">Transaction Date</label>
                            <input type="date" name="date" class="form-control" value="{{ old('date', $transaction->date->format('Y-m-d')) }}" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small text-secondary">Category</label>
                        <select name="category_id" class="form-select" required>
                            @if($transaction->type === 'expense')
                                <option value="unbudgeted" @selected($transaction->category_id === null)>Other / unbudgeted — deduct from available balance</option>
                            @endif
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}" @selected((string) old('category_id', $transaction->category_id) === (string) $c->id)>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                        @if($transaction->type === 'expense')
                            <div class="form-text">Budgeted categories reduce their budget. Other / unbudgeted spending reduces your available balance.</div>
                        @endif
                    </div>

                    <div class="bg-light p-3 rounded-3 border mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_recurring" value="1" id="editRecurringCheck" {{ $transaction->is_recurring ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold text-dark small" for="editRecurringCheck">
                                Mark as Recurring Entry
                            </label>
                        </div>
                        <div class="mt-2">
                            <label for="editRecurrenceFrequency" class="form-label small text-secondary">Repeat frequency</label>
                            <select id="editRecurrenceFrequency" name="recurrence_frequency" class="form-select form-select-sm">
                                <option value="monthly" @selected($transaction->recurrence_frequency !== 'weekly')>Monthly</option>
                                <option value="weekly" @selected($transaction->recurrence_frequency === 'weekly')>Weekly</option>
                            </select>
                            <div class="form-text">Due entries are created by the scheduled Laravel task.</div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('transactions.index') }}" class="btn btn-light rounded-pill px-4">Cancel</a>
                        <button type="submit" class="btn btn-success rounded-pill px-5 fw-bold shadow-sm">
                            <i class="fas fa-save me-2"></i> Update Transaction Record
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
