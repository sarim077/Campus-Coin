@extends('layouts.app')

@section('title', __('ui.nav.dashboard') . ' - ' . __('ui.app_name'))

@section('breadcrumb')
<li class="breadcrumb-item active">{{ __('ui.nav.dashboard') }}</li>
@endsection

@section('content')
<!-- Personalized Student Header Banner -->
<div class="card border-0 shadow-lg rounded-4 p-4 mb-4 position-relative overflow-hidden reveal-on-scroll portal-hero-card">
    <div class="position-absolute end-0 top-50 translate-middle-y me-4 pe-none" style="opacity: 0.08; font-size: 7.5rem; transform: rotate(12deg); color: var(--theme-accent-soft);">
        <i class="fas fa-coins"></i>
    </div>
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 position-relative z-1">
        <div>
            <div class="mb-2">
                <span class="badge bg-success bg-opacity-25 text-success border border-success px-3 py-1 fw-medium rounded-pill">
                    <i class="fas fa-graduation-cap me-1"></i> <span>{{ $user->academic_year ?? 'University Student' }}</span>
                </span>
            </div>
            <h2 class="fw-bold mb-1 text-white">{{ __('ui.auth.welcome_back') }}, {{ $user->name }}!</h2>
            <p class="text-white-50 small mb-0">{{ __('ui.dashboard.summary') }} {{ date('F Y') }}.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-success rounded-pill px-4 py-2 fw-bold shadow-lg" data-bs-toggle="modal" data-bs-target="#quickAddModal">
                <i class="fas fa-plus-circle me-1"></i> {{ __('ui.dashboard.quick_add') }}
            </button>
            <a href="{{ route('transactions.index') }}" class="btn btn-glow-outline rounded-pill px-4 py-2">
                <i class="fas fa-list me-1"></i> {{ __('ui.nav.transactions') }}
            </a>
        </div>
    </div>
</div>

<!-- Announcements Banner -->
@if(count($announcements) > 0)
    @foreach($announcements as $ann)
        <div class="alert border-0 shadow-lg rounded-4 d-flex align-items-center justify-content-between mb-4 reveal-on-scroll student-announcement">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center student-announcement-icon" style="width:44px; height:44px;">
                    <i class="fas fa-bullhorn fs-5"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0 text-white">{{ $ann->title }}</h6>
                    <small class="text-white-50">{{ $ann->content }}</small>
                </div>
            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        </div>
    @endforeach
@endif

<!-- Summary Cards Grid -->
<div class="row g-3 mb-4 reveal-on-scroll student-summary-grid">
    <!-- Monthly Income Card -->
    <div class="col-12 col-sm-6 col-xl">
        <div class="card border-0 shadow-lg rounded-4 p-4 stat-card position-relative overflow-hidden">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-white-50 small fw-bold text-uppercase">{{ __('ui.dashboard.income') }}</span>
                <div class="icon-box dashboard-income-icon shadow">
                    <i class="fas fa-arrow-down"></i>
                </div>
            </div>
            <h3 class="fw-bold text-success mb-1 fs-3">{{ $currencySymbol }}{{ number_format($monthlyIncome, 2) }}</h3>
            <span class="text-white-50 small">{{ __('ui.dashboard.baseline') }}: {{ $currencySymbol }}{{ number_format($user->monthly_allowance_baseline, 2) }}</span>
        </div>
    </div>

    <!-- Monthly Expenses Card -->
    <div class="col-12 col-sm-6 col-xl">
        <div class="card border-0 shadow-lg rounded-4 p-4 stat-card position-relative overflow-hidden">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-white-50 small fw-bold text-uppercase">{{ __('ui.dashboard.expenses') }}</span>
                <div class="icon-box dashboard-expense-icon shadow">
                    <i class="fas fa-arrow-up"></i>
                </div>
            </div>
            <h3 class="fw-bold text-danger mb-1 fs-3">{{ $currencySymbol }}{{ number_format($monthlyExpense, 2) }}</h3>
            <span class="text-white-50 small">{{ date('F') }} {{ __('ui.dashboard.total_spending') }}</span>
        </div>
    </div>

    <!-- Net Balance Card -->
    <div class="col-12 col-sm-6 col-xl">
        <div class="card border-0 shadow-lg rounded-4 p-4 stat-card position-relative overflow-hidden">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-white-50 small fw-bold text-uppercase">{{ __('ui.dashboard.balance') }}</span>
                <div class="icon-box text-white shadow" style="background: rgba(142, 164, 182, 0.16); color: var(--theme-accent-soft) !important;">
                    <i class="fas fa-wallet"></i>
                </div>
            </div>
            <h3 class="fw-bold text-glow-green mb-1 fs-3">
                {{ $currencySymbol }}{{ number_format($netBalance, 2) }}
            </h3>
            <span class="text-white-50 small">After budgets and out-of-budget spending</span>
        </div>
    </div>

    <!-- Remaining Monthly Budget Card -->
    <div class="col-12 col-sm-6 col-xl">
        <div class="card border-0 shadow-lg rounded-4 p-4 stat-card position-relative overflow-hidden">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-white-50 small fw-bold text-uppercase">Budget remaining</span>
                <div class="icon-box text-white shadow">
                    <i class="fas fa-list-check" aria-hidden="true"></i>
                </div>
            </div>
            <h3 class="fw-bold text-glow-green mb-1 fs-3">
                {{ $currencySymbol }}{{ number_format($budgetFunding['remaining_in_budgets'], 2) }}
            </h3>
            <span class="text-white-50 small">
                {{ $currencySymbol }}{{ number_format($budgetFunding['allocated_to_budgets'], 2) }} allocated this month
            </span>
            <a href="{{ route('budgets.index') }}" class="stretched-link" aria-label="Manage monthly budgets"></a>
        </div>
    </div>

    <!-- Savings Goal Thermometer Card -->
    <div class="col-12 col-sm-6 col-xl">
        <div class="card border-0 shadow-lg rounded-4 p-4 stat-card stat-card--goal">
            <div class="goal-card-content">
                <x-goal-thermometer
                    :current-value="$savingsGoalProgress"
                    :goal-value="$savingsGoal"
                    label="Savings goal"
                    :currency-symbol="$currencySymbol"
                />
                <div class="goal-card-actions">
                    @if($savingsGoalDueDate)
                        <small class="text-secondary">Target date: {{ $savingsGoalDueDate->format('M j, Y') }}</small>
                    @endif
                    <details class="goal-thermometer-editor" @if($errors->hasAny(['monthly_savings_goal', 'savings_goal_due_date'])) open @endif>
                        <summary class="goal-thermometer-editor__summary">Edit goal</summary>
                        <form action="{{ route('student.savings-goal.update') }}" method="POST" class="goal-thermometer-editor__form">
                    @csrf
                    @method('PUT')
                    <div>
                        <label for="dashboard-savings-goal" class="form-label">Goal amount</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">{{ $currencySymbol }}</span>
                            <input
                                id="dashboard-savings-goal"
                                type="number"
                                name="monthly_savings_goal"
                                min="0"
                                step="0.01"
                                value="{{ old('monthly_savings_goal', $savingsGoal) }}"
                                class="form-control @error('monthly_savings_goal') is-invalid @enderror"
                                required
                            >
                        </div>
                        @error('monthly_savings_goal')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label for="dashboard-savings-goal-date" class="form-label">Goal date</label>
                        <input
                            id="dashboard-savings-goal-date"
                            type="date"
                            name="savings_goal_due_date"
                            min="{{ now()->toDateString() }}"
                            value="{{ old('savings_goal_due_date', $savingsGoalDueDate?->format('Y-m-d')) }}"
                            class="form-control form-control-sm @error('savings_goal_due_date') is-invalid @enderror"
                            @if((float) $savingsGoal > 0) required @endif
                        >
                        @error('savings_goal_due_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                            <button type="submit" class="btn btn-success btn-sm w-100">Save goal</button>
                        </form>
                    </details>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Dynamic Widgets Row -->
<div class="row g-4 mb-4">
    <!-- Widget: 6-Month Income vs Expense Chart -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-lg rounded-4 p-4 h-100 cc-chart-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h5 class="fw-bold mb-0">{{ __('ui.dashboard.income_expense_trend') }}</h5>
                    <small class="text-muted">{{ __('ui.dashboard.six_months') }}</small>
                </div>
                <a href="{{ route('reports.index') }}" class="btn btn-outline-success btn-sm rounded-pill px-3">
                    <i class="fas fa-external-link-alt me-1"></i> {{ __('ui.dashboard.full_report') }}
                </a>
            </div>
            <div class="cc-chart-stage cc-bar-stage" style="height: 300px;">
                <canvas id="trendChart" aria-label="Six month income and expense trend" role="img"></canvas>
                @if($chartPeakTotal <= 0)<p class="cc-chart-empty">Your six-month trend will appear after you log a transaction.</p>@endif
                <p class="chart-load-fallback" hidden>Charts are temporarily unavailable. Your financial records are still safe.</p>
            </div>
        </div>
    </div>

    <!-- Widgets: Top Category & Budget Consumption -->
    <div class="col-lg-4">
        <div class="d-flex flex-column gap-4 h-100">
            <!-- Widget: Top Category -->
            <div class="card border-0 shadow-lg rounded-4 p-4 position-relative overflow-hidden text-white student-spend-card">
                <div class="position-absolute end-0 top-0 text-white pe-none" style="opacity: 0.15; font-size: 5rem; margin-right: 0.5rem; margin-top: -0.5rem;">
                    <i class="fas fa-fire"></i>
                </div>
                <span class="text-white-75 small fw-bold text-uppercase mb-2 d-block">{{ __('ui.dashboard.top_category') }}</span>
                @if($topCategory && $topCategory->category)
                    <div class="d-flex align-items-center gap-3">
                        <div class="icon-box bg-white bg-opacity-25 text-white fs-4 rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="{{ $topCategory->category?->icon ?? 'fas fa-tag' }}"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-white">{{ $topCategory->category?->name ?? 'Uncategorized' }}</h5>
                            <div class="fw-semibold text-white-75">{{ $currencySymbol }}{{ number_format($topCategory->total, 2) }} {{ __('ui.dashboard.logged') }}</div>
                        </div>
                    </div>
                @else
                    <div class="text-white-75 small">{{ __('ui.dashboard.no_expenses') }}</div>
                @endif
            </div>

            <div class="card border-0 shadow-lg rounded-4 p-4 cc-chart-card cc-donut-card">
                <div class="mb-2"><h6 class="fw-bold mb-0">This month's spending mix</h6><small class="text-muted">See where your student budget is going</small></div>
                <div class="cc-chart-stage cc-donut-stage" style="height: 210px;">
                    <canvas id="studentCategoryChart" aria-label="Monthly expenses grouped by category" role="img"></canvas>
                    @if($categoryBreakdown->isEmpty())<p class="cc-chart-empty">Your spending mix will appear as you record expenses this month.</p>@endif
                    <p class="chart-load-fallback" hidden>Charts are temporarily unavailable. Your financial records are still safe.</p>
                </div>
            </div>

            <!-- Widget: Budget vs Actual Progress Bars -->
            <div class="card border-0 shadow-lg rounded-4 p-4 flex-grow-1 cc-budget-depth">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-bullseye text-success me-1"></i> {{ __('ui.dashboard.budget_actual') }}</h6>
                    <a href="{{ route('budgets.index') }}" class="small text-success text-decoration-none fw-bold">{{ __('ui.dashboard.manage_budgets') }}</a>
                </div>

                @if(count($budgets) > 0)
                    <div class="d-flex flex-column gap-3">
                        @foreach($budgets as $b)
                            <div>
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="fw-bold">{{ $b['category'] }}</span>
                                    <span class="text-muted">{{ $currencySymbol }}{{ number_format($b['spent'], 2) }} / {{ $currencySymbol }}{{ number_format($b['limit'], 2) }}</span>
                                </div>
                                <small class="text-muted d-block mb-1">Funded from income: {{ $currencySymbol }}{{ number_format($b['funded'], 2) }} / {{ $currencySymbol }}{{ number_format($b['limit'], 2) }}</small>
                                <div class="progress rounded-pill" style="height: 10px;">
                                    <div class="progress-bar {{ $b['status'] == 'exceeded' ? 'bg-danger' : ($b['status'] == 'warning' ? 'bg-warning' : 'bg-success') }} shadow"
                                         role="progressbar" style="width: {{ $b['percentage'] }}%;"></div>
                                </div>
                                @if($b['status'] == 'exceeded')
                                    <span class="badge bg-danger bg-opacity-10 text-danger text-xs mt-1"><i class="fas fa-exclamation-triangle"></i> {{ __('ui.dashboard.budget_exceeded') }}</span>
                                @elseif($b['status'] == 'warning')
                                    <span class="badge bg-warning bg-opacity-10 text-warning text-dark text-xs mt-1"><i class="fas fa-exclamation-circle"></i> {{ __('ui.dashboard.nearing_limit') }} ({{ $b['raw_percentage'] }}%)</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-3 text-muted small">
                        {{ __('ui.dashboard.no_budgets') }}<br>
                        <a href="{{ route('budgets.index') }}" class="btn btn-outline-success btn-sm mt-2 rounded-pill">{{ __('ui.dashboard.set_budgets') }}</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Saving Tips & Recent Transactions Row -->
<div class="row g-4">
    <!-- Saving Tips Engine Highlights -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-lg rounded-4 p-4 h-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h5 class="fw-bold mb-0"><i class="fas fa-lightbulb text-warning me-2"></i> {{ __('ui.dashboard.saving_tips') }}</h5>
                <a href="{{ route('saving-tips.index') }}" class="btn btn-outline-warning btn-sm rounded-pill px-3">{{ __('ui.dashboard.view_all') }}</a>
            </div>

            @if(count($savingTips) > 0)
                <div class="d-flex flex-column gap-3">
                    @foreach($savingTips as $tip)
                        <div class="p-3 bg-light rounded-3 border border-light position-relative student-tip-card">
                            @if($tip->status == 'pinned')
                                <span class="badge bg-warning text-dark position-absolute end-0 top-0 m-2"><i class="fas fa-thumbtack"></i> {{ __('ui.dashboard.pinned') }}</span>
                            @endif
                            <h6 class="fw-bold text-dark mb-1" data-no-translate>{{ $tip->tip_title }}</h6>
                            <p class="text-secondary small mb-2">{{ $tip->tip_body }}</p>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="badge bg-success bg-opacity-10 text-success border border-success">
                                    {{ __('ui.dashboard.potential_savings') }}: {{ $currencySymbol }}{{ number_format($tip->potential_savings, 2) }}/mo
                                </span>
                                <form action="{{ route('saving-tips.status', $tip->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="status" value="dismissed">
                                    <button type="submit" class="btn btn-link btn-sm text-muted text-decoration-none p-0">{{ __('ui.dashboard.dismiss') }}</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-4 text-muted small">
                    {{ __('ui.dashboard.generate_tips') }}
                </div>
            @endif
        </div>
    </div>

    <!-- Recent Transactions & AI Insight Summary -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-lg rounded-4 p-4 h-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h5 class="fw-bold mb-0"><i class="fas fa-history text-primary me-2"></i> {{ __('ui.dashboard.recent_transactions') }}</h5>
                        <a href="{{ route('transactions.create') }}" class="btn btn-success btn-sm rounded-pill px-3">+ {{ __('ui.dashboard.add_new') }}</a>
            </div>

            <div class="table-responsive">
                <table class="table align-middle table-hover mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Category</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @forelse($recentTransactions as $tx)
                            <tr>
                                <td class="text-muted">{{ $tx->date->format('M d') }}</td>
                                <td class="fw-semibold">{{ $tx->description }}</td>
                                <td>
                                    <span class="badge bg-secondary bg-opacity-10 text-dark">
                                        {{ $tx->category?->name ?? __('ui.dashboard.uncategorized') }}
                                    </span>
                                </td>
                                <td class="text-end fw-bold {{ $tx->type == 'income' ? 'text-success' : 'text-danger' }}">
                                    {{ $tx->type == 'income' ? '+' : '-' }}{{ $currencySymbol }}{{ number_format($tx->amount, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">{{ __('ui.dashboard.no_transactions') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Quick Add Entry Modal with AI Categorization -->
<div class="modal fade quick-transaction-modal" id="quickAddModal" tabindex="-1" aria-labelledby="quickAddModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="{{ route('transactions.store') }}" method="POST">
                @csrf
                <div class="modal-header quick-transaction-header p-4 border-0">
                    <span class="quick-transaction-mark" aria-hidden="true"><i class="fas fa-bolt"></i></span>
                    <div class="flex-grow-1">
                        <h2 class="modal-title h5 fw-bold mb-1" id="quickAddModalTitle">{{ __('ui.dashboard.quick_log') }}</h2>
                        <p class="small mb-0">Add income or spending to your monthly activity.</p>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('ui.dashboard.cancel') }}"></button>
                </div>
                <div class="modal-body p-4">
                    <fieldset class="mb-4">
                        <legend class="form-label fw-semibold small mb-2">{{ __('ui.dashboard.transaction_type') }}</legend>
                        <div class="quick-transaction-type" role="group" aria-label="{{ __('ui.dashboard.transaction_type') }}">
                            <input type="radio" name="type" id="typeExpense" value="expense" checked onchange="toggleCategorySelect('expense')">
                            <label for="typeExpense"><i class="fas fa-arrow-up me-2" aria-hidden="true"></i>{{ __('ui.dashboard.expense') }}</label>
                            <input type="radio" name="type" id="typeIncome" value="income" onchange="toggleCategorySelect('income')">
                            <label for="typeIncome"><i class="fas fa-arrow-down me-2" aria-hidden="true"></i>{{ __('ui.dashboard.income') }}</label>
                        </div>
                    </fieldset>

                    <div class="mb-3">
                        <label for="quickDescription" class="form-label fw-semibold small">{{ __('ui.dashboard.description') }}</label>
                        <input type="text" name="description" id="quickDescription" class="form-control" placeholder="{{ __('ui.dashboard.description_placeholder') }}" maxlength="255" autocomplete="off" required oninput="liveAiSuggest(this.value)">
                        <input type="hidden" name="ai_suggested_category_id" id="quickAiSuggestedCategoryId">
                        <div id="aiSuggestBadge" class="quick-ai-suggestion mt-2 d-none" role="status">
                            <i class="fas fa-robot me-1" aria-hidden="true"></i> {{ __('ui.dashboard.ai_suggested') }}: <strong id="aiSuggestName"></strong>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-sm-6">
                            <label for="quickAmount" class="form-label fw-semibold small">{{ __('ui.dashboard.amount') }}</label>
                            <div class="input-group quick-money-input">
                                <span class="input-group-text">{{ $currencySymbol }}</span>
                                <input id="quickAmount" type="number" step="0.01" min="0.01" name="amount" class="form-control" placeholder="0.00" inputmode="decimal" required>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label for="quickDate" class="form-label fw-semibold small">{{ __('ui.dashboard.date') }}</label>
                            <input id="quickDate" type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="quickCategorySelect" class="form-label fw-semibold small">{{ __('ui.dashboard.category') }}</label>
                        <select name="category_id" id="quickCategorySelect" class="form-select" required>
                            <option value="" disabled selected>{{ __('ui.dashboard.select_category') }}</option>
                            @foreach($expenseCategories as $c)
                                <option value="{{ $c->id }}" data-type="expense">{{ $c->name }}</option>
                            @endforeach
                            <option value="unbudgeted" data-type="expense">Other / unbudgeted</option>
                            @foreach($incomeCategories as $c)
                                <option value="{{ $c->id }}" data-type="income">{{ $c->name }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Budgeted categories reduce their budget. Other / unbudgeted spending reduces your available balance.</div>
                    </div>

                    <label class="quick-recurring-option" for="quickRecurring">
                        <input class="form-check-input" type="checkbox" name="is_recurring" value="1" id="quickRecurring">
                        <span class="quick-recurring-icon"><i class="fas fa-sync-alt" aria-hidden="true"></i></span>
                        <span><strong>{{ __('ui.dashboard.recurring') }}</strong><small>Use for regular monthly income or expenses.</small></span>
                    </label>
                </div>
                <div class="modal-footer quick-transaction-footer border-0 p-3 px-4">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">{{ __('ui.dashboard.cancel') }}</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold"><i class="fas fa-check me-2" aria-hidden="true"></i>{{ __('ui.dashboard.save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .quick-transaction-modal .modal-dialog { max-width: 560px; }
    .quick-transaction-modal .modal-content { overflow: hidden; color: #E8EDF0; background: #15181A; }
    .quick-transaction-modal form { display: flex; min-height: 0; flex: 1 1 auto; flex-direction: column; }
    .quick-transaction-modal .modal-body { min-height: 0; flex: 1 1 auto; overflow-y: auto; overscroll-behavior: contain; -webkit-overflow-scrolling: touch; }
    .quick-transaction-modal .modal-header, .quick-transaction-modal .modal-footer { flex: 0 0 auto; }
    .quick-transaction-header { display: flex; align-items: center; gap: 13px; color: #F2F7FA; background: radial-gradient(circle at 90% 0, rgba(70, 160, 228,.17), transparent 42%), linear-gradient(135deg,#182025,#15181A); }
    .quick-transaction-header p { color: #AAB1B7; }
    .quick-transaction-mark { display: grid; width: 42px; height: 42px; flex: 0 0 42px; place-items: center; border: 1px solid rgba(70, 160, 228,.22); border-radius: 13px; background: rgba(70, 160, 228,.1); color: #82BFEF; }
    .quick-transaction-modal .form-label, .quick-transaction-modal legend { color: #DCE2E7; }
    .quick-transaction-modal .form-control, .quick-transaction-modal .form-select, .quick-transaction-modal .input-group-text { min-height: 44px; border-color: rgba(235, 241, 245,.14); background-color: #101315; color: #EDF1F5; }
    .quick-transaction-modal .form-control:focus, .quick-transaction-modal .form-select:focus { border-color: rgba(70, 160, 228,.55); box-shadow: 0 0 0 .2rem rgba(70, 160, 228,.12); }
    .quick-transaction-modal .form-control::placeholder { color: #787F84; }
    .quick-transaction-modal .form-select option { color: #EDF1F5; background: #15181A; }
    .quick-transaction-modal .quick-money-input .input-group-text { min-width: 48px; justify-content: center; color: #82BFEF; font-weight: 700; }
    .quick-transaction-type { display: grid; grid-template-columns: 1fr 1fr; padding: 4px; gap: 4px; border: 1px solid rgba(235, 241, 245,.12); border-radius: 13px; background: #101315; }
    .quick-transaction-type input { position: absolute; width: 1px; height: 1px; opacity: 0; }
    .quick-transaction-type label { display: flex; min-height: 42px; align-items: center; justify-content: center; padding: 8px 12px; border: 1px solid transparent; border-radius: 9px; color: #AAB1B7; font-size: .88rem; font-weight: 600; cursor: pointer; transition: background .16s ease, color .16s ease, border-color .16s ease; }
    .quick-transaction-type input:focus-visible + label { outline: 2px solid #82BFEF; outline-offset: 2px; }
    .quick-transaction-type input#typeExpense:checked + label { border-color: rgba(251,113,133,.3); background: rgba(251,113,133,.13); color: #ffb2bd; }
    .quick-transaction-type input#typeIncome:checked + label { border-color: rgba(70, 160, 228,.3); background: rgba(70, 160, 228,.13); color: #9AC9F0; }
    .quick-ai-suggestion { width: fit-content; padding: 6px 10px; border: 1px solid rgba(70, 160, 228,.2); border-radius: 9px; background: rgba(70, 160, 228,.08); color: #AECEE9; font-size: .77rem; }
    .quick-recurring-option { display: flex; align-items: center; gap: 11px; padding: 12px; border: 1px solid rgba(235, 241, 245,.1); border-radius: 12px; background: rgba(255,255,255,.02); cursor: pointer; }
    .quick-recurring-option .form-check-input { flex: 0 0 auto; margin: 0; }
    .quick-recurring-icon { display: grid; width: 34px; height: 34px; flex: 0 0 34px; place-items: center; border-radius: 10px; background: rgba(70, 160, 228,.1); color: #82BFEF; font-size: .8rem; }
    .quick-recurring-option strong, .quick-recurring-option small { display: block; }
    .quick-recurring-option strong { color: #DCE2E7; font-size: .78rem; font-weight: 600; }
    .quick-recurring-option small { margin-top: 3px; color: #899096; font-size: .7rem; }
    .quick-transaction-footer { background: #111517; }
    @media (max-width: 575.98px) {
        .quick-transaction-modal .modal-dialog { height: calc(100% - 1.1rem); min-height: 0; margin: .55rem; }
        .quick-transaction-modal .modal-body { padding: 1.1rem !important; }
        .quick-transaction-modal .modal-header { padding: 1rem 1.1rem !important; }
        .quick-transaction-modal .modal-footer { background: #111517; }
    }
    @media (prefers-reduced-motion: reduce) {
        .quick-transaction-type label { transition: none; }
    }
</style>
@endsection

@section('scripts')
<script>
    // 6-Month Income vs Expense Chart (Chart.js)
    const currencySymbol = @json($currencySymbol);
    const trendCanvas = document.getElementById('trendChart');
    if (trendCanvas && window.Chart) {
    const ctx = trendCanvas.getContext('2d');
    const gradientIncome = ctx.createLinearGradient(0, 0, 0, 300);
    gradientIncome.addColorStop(0, 'rgba(16, 124, 185, 0.8)');
    gradientIncome.addColorStop(1, 'rgba(16, 124, 185, 0.2)');
    
    const gradientExpense = ctx.createLinearGradient(0, 0, 0, 300);
    gradientExpense.addColorStop(0, 'rgba(239, 68, 68, 0.8)');
    gradientExpense.addColorStop(1, 'rgba(239, 68, 68, 0.2)');
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartMonths) !!},
            datasets: [
                {
                    label: '{{ __('ui.dashboard.income') }} ({{ $currencySymbol }})',
                    data: {!! json_encode($chartIncome) !!},
                    backgroundColor: gradientIncome,
                    borderColor: '#107CB9',
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false,
                },
                {
                    label: '{{ __('ui.dashboard.expenses') }} ({{ $currencySymbol }})',
                    data: {!! json_encode($chartExpenses) !!},
                    backgroundColor: gradientExpense,
                    borderColor: '#ef4444',
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { 
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: {
                            size: 12,
                            weight: 'bold'
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    cornerRadius: 8,
                    titleFont: { size: 14, weight: 'bold' },
                    bodyFont: { size: 13 },
                    callbacks: {
                        label: (context) => `${context.dataset.label}: ${currencySymbol}${Number(context.parsed.y).toLocaleString()}`,
                    }
                }
            },
            scales: {
                y: { 
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        font: { size: 11 }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: { size: 11, weight: 'bold' }
                    }
                }
            },
            animation: { duration: 1200, easing: 'easeOutQuart' },
        }
    });
    }

    const studentCategoryCanvas = document.getElementById('studentCategoryChart');
    if (studentCategoryCanvas && window.Chart && @json($categoryBreakdown->isNotEmpty())) {
        new Chart(studentCategoryCanvas, {
            type: 'doughnut',
            data: {
                labels: @json($categoryBreakdown->map(fn ($item) => $item->category?->name ?? 'Uncategorized')->values()),
                datasets: [{
                    data: @json($categoryBreakdown->map(fn ($item) => (float) $item->total)->values()),
                    backgroundColor: ['#3498D3', '#38bdf8', '#fbbf24', '#fb7185', '#1487B8', '#84cc16'],
                    borderColor: '#151719',
                    borderWidth: 4,
                    hoverOffset: 10,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '66%',
                animation: { duration: 1200, easing: 'easeOutQuart' },
                plugins: { legend: { position: 'bottom', labels: { color: '#cbd5e1', usePointStyle: true, padding: 12, boxWidth: 8 } } },
            },
        });
    }

    // Toggle Income / Expense Categories in Modal
    function toggleCategorySelect(type) {
        const select = document.getElementById('quickCategorySelect');
        Array.from(select.options).forEach(opt => {
            if (opt.value === "") return;
            if (opt.getAttribute('data-type') === type) {
                opt.hidden = false;
                opt.disabled = false;
            } else {
                opt.hidden = true;
                opt.disabled = true;
            }
        });
        select.value = "";
    }

    toggleCategorySelect(document.querySelector('input[name="type"]:checked').value);
    document.getElementById('quickAddModal').addEventListener('shown.bs.modal', () => {
        document.getElementById('quickDescription').focus();
    });

    // Live AI Categorization Suggestion
    let aiTimeout;
    function liveAiSuggest(query) {
        clearTimeout(aiTimeout);
        if (query.length < 3) return;

        aiTimeout = setTimeout(() => {
            fetch("{{ route('ai.suggest-category') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    description: query,
                    type: document.querySelector('#quickAddModal input[name="type"]:checked')?.value
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.suggested_category_id) {
                    const badge = document.getElementById('aiSuggestBadge');
                    const badgeName = document.getElementById('aiSuggestName');
                    badgeName.innerText = data.category_name;
                    badge.classList.remove('d-none');

                    const select = document.getElementById('quickCategorySelect');
                    select.value = data.suggested_category_id;
                    document.getElementById('quickAiSuggestedCategoryId').value = data.suggested_category_id;
                }
            });
        }, 300);
    }
</script>
@endsection
