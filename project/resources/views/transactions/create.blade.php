@extends('layouts.app')

@section('title', 'Log New Transaction - Campus Coin')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item"><a href="{{ route('transactions.index') }}">Transactions</a></li>
<li class="breadcrumb-item active">New Entry</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-dark text-white p-4 border-0">
                <h4 class="fw-bold mb-0"><i class="fas fa-plus-circle text-success me-2"></i> Log Income or Expense</h4>
                <p class="text-secondary small mb-0">Record single or recurring transaction entries with AI category suggestion</p>
            </div>
            <div class="card-body p-4">
                <section class="rounded-4 border bg-light p-3 mb-4" aria-labelledby="receipt-scan-heading" data-receipt-scanner data-category-suggestion-url="{{ route('ai.suggest-category') }}">
                    <div class="d-flex align-items-start gap-3">
                        <span class="admin-stat-icon admin-stat-green flex-shrink-0"><i class="fas fa-camera" aria-hidden="true"></i></span>
                        <div class="flex-grow-1">
                            <h5 id="receipt-scan-heading" class="fw-bold mb-1">Scan a receipt or award</h5>
                            <p class="small text-secondary mb-3">Choose a clear photo of a store receipt, prize notice, check, or other money document. We’ll read it on this device and fill in the form for you to review.</p>
                            <label for="receiptImage" class="form-label fw-bold small text-secondary">Document photo</label>
                            <input id="receiptImage" type="file" class="form-control" accept="image/jpeg,image/png,image/webp" capture="environment" aria-describedby="receiptScanHelp">
                            <div id="receiptScanHelp" class="form-text">JPEG, PNG, or WebP image up to 10 MB. The image is processed in your browser and is not uploaded.</div>
                            <button type="button" id="scanReceiptButton" class="btn btn-outline-success rounded-pill px-4 mt-3" disabled>
                                <i class="fas fa-wand-magic-sparkles me-1" aria-hidden="true"></i> Read document
                            </button>
                            <div id="receiptScanProgress" class="small text-secondary mt-2" role="status" aria-live="polite"></div>
                            <div id="receiptScanResult" class="alert alert-info mt-3 mb-0 d-none" role="status"></div>
                        </div>
                    </div>
                </section>

                <form action="{{ route('transactions.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-secondary">Transaction Type</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="type" id="typeExpense" value="expense" checked onchange="switchCategories('expense')">
                            <label class="btn btn-outline-danger py-2 fw-bold" for="typeExpense"><i class="fas fa-arrow-up me-1"></i> Expense</label>

                            <input type="radio" class="btn-check" name="type" id="typeIncome" value="income" onchange="switchCategories('income')">
                            <label class="btn btn-outline-success py-2 fw-bold" for="typeIncome"><i class="fas fa-arrow-down me-1"></i> Income</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Description / Vendor Note</label>
                        <input type="text" name="description" id="createDescription" class="form-control" value="{{ old('description') }}" placeholder="e.g., Campus Cafe Lunch, Library Printing, Spotify Subscription" required onkeyup="checkAiSuggestion(this.value)">
                        <input type="hidden" name="ai_suggested_category_id" id="aiSuggestedCategoryId">
                        
                        <div id="aiBanner" class="mt-2 p-2 bg-primary bg-opacity-10 border border-primary rounded d-none">
                            <div class="d-flex align-items-center justify-content-between small text-primary">
                                <span><i class="fas fa-robot me-1"></i> AI Assistant Suggested Category: <strong id="aiCatName"></strong></span>
                                <span class="badge bg-primary">AI Match</span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-secondary">Amount ({{ $currencySymbol }})</label>
                            <div class="input-group">
                                <span class="input-group-text">{{ $currencySymbol }}</span>
                                <input type="number" step="0.01" name="amount" class="form-control" value="{{ old('amount') }}" placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-secondary">Transaction Date</label>
                            <input type="date" name="date" class="form-control" value="{{ old('date', date('Y-m-d')) }}" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small text-secondary">Category</label>
                        <select name="category_id" id="categorySelect" class="form-select" required>
                            <option value="" disabled @selected(old('category_id') === null)>Select category...</option>
                            @foreach($expenseCategories as $c)
                                <option value="{{ $c->id }}" data-type="expense" @selected((string) old('category_id') === (string) $c->id)>{{ $c->name }}</option>
                            @endforeach
                            <option value="unbudgeted" data-type="expense" @selected(old('category_id') === 'unbudgeted')>Other / unbudgeted — deduct from available balance</option>
                            @foreach($incomeCategories as $c)
                                <option value="{{ $c->id }}" data-type="income" class="d-none" @selected((string) old('category_id') === (string) $c->id)>{{ $c->name }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Choose a budgeted category to use its budget. Choose Other / unbudgeted for spending that should reduce your available balance instead.</div>
                    </div>

                    <div class="bg-light p-3 rounded-3 border mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_recurring" value="1" id="isRecurringCheck" onchange="toggleRecurringOptions(this.checked)">
                            <label class="form-check-label fw-bold text-dark small" for="isRecurringCheck">
                                Mark as Recurring Entry
                            </label>
                        </div>
                        <div id="recurringDiv" class="mt-2 d-none">
                            <label class="form-label small text-secondary mb-1">Recurrence Cycle</label>
                            <select name="recurrence_frequency" class="form-select form-select-sm">
                                <option value="monthly" selected>Monthly (e.g., allowance, rent, streaming)</option>
                                <option value="weekly">Weekly</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('transactions.index') }}" class="btn btn-light rounded-pill px-4">Cancel</a>
                        <button type="submit" class="btn btn-success rounded-pill px-5 fw-bold shadow-sm">
                            <i class="fas fa-save me-2"></i> Save Transaction Record
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@vite('resources/js/receipt-scanner.js')
<script>
    function switchCategories(type) {
        const select = document.getElementById('categorySelect');
        Array.from(select.options).forEach(opt => {
            if (opt.value === "") return;
            if (opt.getAttribute('data-type') === type) {
                opt.classList.remove('d-none');
            } else {
                opt.classList.add('d-none');
            }
        });
        select.value = "";
    }

    function toggleRecurringOptions(checked) {
        const div = document.getElementById('recurringDiv');
        if (checked) div.classList.remove('d-none');
        else div.classList.add('d-none');
    }

    let aiTimer;
    function checkAiSuggestion(val) {
        clearTimeout(aiTimer);
        if (val.length < 3) return;

        aiTimer = setTimeout(() => {
            fetch("{{ route('ai.suggest-category') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    description: val,
                    type: document.querySelector('input[name="type"]:checked')?.value
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.suggested_category_id) {
                    const banner = document.getElementById('aiBanner');
                    const nameSpan = document.getElementById('aiCatName');
                    nameSpan.innerText = data.category_name;
                    banner.classList.remove('d-none');

                    const select = document.getElementById('categorySelect');
                    select.value = data.suggested_category_id;
                    document.getElementById('aiSuggestedCategoryId').value = data.suggested_category_id;
                }
            });
        }, 300);
    }
</script>
@endsection
