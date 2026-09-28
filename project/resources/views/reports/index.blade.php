@extends('layouts.app')

@section('title', 'Monthly Financial Reports - Campus Coin')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item active">Financial Reports</li>
@endsection

@section('content')
<div class="page-intro d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1">Monthly Reports & Analytics</h3>
        <p class="text-secondary small mb-0">Review category breakdowns, 6-month trend analysis, and daily/weekly summaries.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.pdf', request()->only('month', 'start_date', 'end_date', 'category_id', 'income_source_id')) }}" target="_blank" class="btn btn-dark rounded-pill px-3 shadow-sm">
            <i class="fas fa-print me-1"></i> Print / Export PDF
        </a>
        <button type="button" id="export-report-image" class="btn btn-outline-dark rounded-pill px-3">Save PNG</button>
    </div>
</div>

<!-- Report Filters -->
<div class="card border-0 shadow-sm rounded-4 p-3 mb-4">
    <form action="{{ route('reports.index') }}" method="GET" class="row g-2 align-items-center report-filter-form">
        <div class="col-sm-6 col-lg-2">
            <label class="small fw-bold text-muted mb-1">Target Month</label>
            <input type="month" name="month" class="form-control form-control-sm bg-white" value="{{ $month }}" onchange="this.form.submit()">
        </div>
        <div class="col-sm-6 col-lg-2">
            <label class="small fw-bold text-muted mb-1">From</label>
            <input type="date" name="start_date" class="form-control form-control-sm bg-white" value="{{ request('start_date', $periodStart) }}">
        </div>
        <div class="col-sm-6 col-lg-2">
            <label class="small fw-bold text-muted mb-1">To</label>
            <input type="date" name="end_date" class="form-control form-control-sm bg-white" value="{{ request('end_date', $periodEnd) }}">
        </div>
        <div class="col-sm-6 col-lg-2">
            <label class="small fw-bold text-muted mb-1">Category Filter</label>
            <select name="category_id" class="form-select form-select-sm bg-white" onchange="this.form.submit()">
                <option value="">All Categories</option>
                @foreach($categories as $c)
                    <option value="{{ $c->id }}" {{ $categoryId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-6 col-lg-2">
            <label class="small fw-bold text-muted mb-1">Income source</label>
            <select name="income_source_id" class="form-select form-select-sm bg-white" onchange="this.form.submit()">
                <option value="">All income</option>
                @foreach($incomeSources as $source)
                    <option value="{{ $source->id }}" @selected($incomeSourceId == $source->id)>{{ $source->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-lg-2 pt-lg-3">
            <span class="badge bg-success fs-6 p-2 report-filter-total">Total Expense: {{ $currencySymbol }}{{ number_format($totalMonthlyExpense, 2) }}</span>
        </div>
        <div class="col-12"><button class="btn btn-success btn-sm">Apply report filters</button></div>
    </form>
</div>

<form action="{{ route('reports.email') }}" method="POST" class="card border-0 shadow-sm rounded-4 p-3 mb-4">
    @csrf
    <input type="hidden" name="month" value="{{ $month }}">
    <input type="hidden" name="start_date" value="{{ $periodStart }}">
    <input type="hidden" name="end_date" value="{{ $periodEnd }}">
    <input type="hidden" name="category_id" value="{{ $categoryId }}">
    <input type="hidden" name="income_source_id" value="{{ $incomeSourceId }}">
    <label for="report-email" class="form-label fw-semibold">Share this report by email</label>
    <div class="input-group"><input id="report-email" type="email" name="email" class="form-control" placeholder="recipient@example.com" required><button class="btn btn-outline-primary">Email report summary</button></div>
</form>

<p id="report-export-status" class="visually-hidden" aria-live="polite"></p>
<div id="report-export-area">
<!-- Charts Row -->
<div class="row g-4 mb-4">
    <!-- Category Spending Breakdown Pie Chart -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
            <h5 class="fw-bold mb-3"><i class="fas fa-chart-pie text-danger me-2"></i> Category Spending Distribution</h5>
            <div style="height: 280px;" class="cc-chart-stage cc-donut-stage d-flex justify-content-center">
                <canvas id="categoryPieChart"></canvas>
            </div>
        </div>
    </div>

    <!-- 6-Month Income vs Expense Bar Chart -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
            <h5 class="fw-bold mb-3"><i class="fas fa-chart-line text-primary me-2"></i> 6-Month Financial Overview</h5>
            <div class="cc-chart-stage" style="height: 280px;">
                <canvas id="sixMonthChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Breakdown Tables Row -->
<div class="row g-4">
    <!-- Daily Summaries -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
            <h5 class="fw-bold mb-3" data-no-translate><i class="fas fa-calendar-day text-info me-2"></i> Daily Spending Breakdown ({{ $periodStart }} – {{ $periodEnd }})</h5>
            <div class="table-responsive" style="max-height: 320px; overflow-y: auto;">
                <table class="table align-middle table-hover mb-0">
                    <thead class="table-light sticky-top small">
                        <tr>
                            <th>Date</th>
                            <th class="text-end">Daily Income</th>
                            <th class="text-end">Daily Expense</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @forelse($dailySummaries as $d)
                            <tr>
                                <td class="fw-semibold text-muted">{{ $d->date }}</td>
                                <td class="text-end text-success">{{ $currencySymbol }}{{ number_format($d->daily_income, 2) }}</td>
                                <td class="text-end text-danger">{{ $currencySymbol }}{{ number_format($d->daily_expense, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted py-3">No daily records for selected month.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Weekly Summaries -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
            <h5 class="fw-bold mb-3"><i class="fas fa-calendar-week text-warning me-2"></i> Weekly Summaries</h5>
            <div class="table-responsive">
                <table class="table align-middle table-hover mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>Week</th>
                            <th class="text-end">Weekly Income</th>
                            <th class="text-end">Weekly Expense</th>
                            <th class="text-end">Net Savings</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @forelse($weeklySummaries as $idx => $w)
                            <tr>
                                <td class="fw-bold">Week {{ $idx + 1 }}</td>
                                <td class="text-end text-success">{{ $currencySymbol }}{{ number_format($w->weekly_income, 2) }}</td>
                                <td class="text-end text-danger">{{ $currencySymbol }}{{ number_format($w->weekly_expense, 2) }}</td>
                                <td class="text-end fw-bold {{ ($w->weekly_income - $w->weekly_expense) >= 0 ? 'text-primary' : 'text-danger' }}">
                                    {{ $currencySymbol }}{{ number_format($w->weekly_income - $w->weekly_expense, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">No weekly records for selected month.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>
@endsection

@section('scripts')
@vite('resources/js/report-export.js')
<script>
    const currencySymbol = @json($currencySymbol);
    // Category Breakdown Pie Chart
    const pieLabels = {!! json_encode($categoryBreakdown->pluck('category.name')) !!};
    const pieData = {!! json_encode($categoryBreakdown->map(fn ($item) => (float) $item->total)->values()) !!};

    const ctxPie = document.getElementById('categoryPieChart').getContext('2d');
    new Chart(ctxPie, {
        type: 'doughnut',
        data: {
            labels: pieLabels,
            datasets: [{
                data: pieData,
                backgroundColor: [
                    '#3498D3', '#38bdf8', '#fbbf24', '#fb7185', '#1487B8', '#84cc16', '#64748b'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 1200, easing: 'easeOutQuart' },
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: (context) => `${context.label}: ${currencySymbol}${Number(context.parsed).toLocaleString()}`,
                    },
                },
            }
        }
    });

    // 6-Month Income vs Expense Chart
    const sixMonthsLabels = {!! json_encode(collect($sixMonthsData)->pluck('month')) !!};
    const sixMonthsIncome = {!! json_encode(collect($sixMonthsData)->pluck('income')) !!};
    const sixMonthsExpense = {!! json_encode(collect($sixMonthsData)->pluck('expense')) !!};

    const ctx6 = document.getElementById('sixMonthChart').getContext('2d');
    new Chart(ctx6, {
        type: 'line',
        data: {
            labels: sixMonthsLabels,
            datasets: [
                {
                    label: 'Income',
                    data: sixMonthsIncome,
                    borderColor: '#107CB9',
                    backgroundColor: 'rgba(16, 124, 185, 0.1)',
                    fill: true,
                    tension: 0.3
                },
                {
                    label: 'Expense',
                    data: sixMonthsExpense,
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    fill: true,
                    tension: 0.3
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 1400, easing: 'easeOutQuart' },
            scales: { y: { beginAtZero: true } },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: (context) => `${context.dataset.label}: ${currencySymbol}${Number(context.parsed.y).toLocaleString()}`,
                    },
                },
            }
        }
    });
</script>
@endsection
