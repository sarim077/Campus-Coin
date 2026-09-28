<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Campus Coin - Monthly Report Statement ({{ $month }})</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #333; margin: 30px; }
        .header { border-bottom: 2px solid #10b981; padding-bottom: 15px; margin-bottom: 20px; }
        .title { font-size: 24px; font-weight: bold; color: #047857; margin: 0; }
        .subtitle { font-size: 14px; color: #666; margin-top: 5px; }
        .summary-box { display: flex; justify-content: space-between; background: #f8fafc; padding: 15px; border-radius: 8px; margin-bottom: 25px; }
        .summary-item { text-align: center; }
        .summary-label { font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: bold; }
        .summary-val { font-size: 18px; font-weight: bold; margin-top: 3px; }
        .val-income { color: #059669; }
        .val-expense { color: #dc2626; }
        .val-net { color: #2563eb; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        th, td { padding: 10px; border-bottom: 1px solid #e2e8f0; text-align: left; }
        th { background: #0f172a; color: #fff; font-size: 12px; text-transform: uppercase; }
        .text-right { text-align: right; }
        .footer { font-size: 11px; color: #94a3b8; text-align: center; margin-top: 40px; border-top: 1px solid #e2e8f0; padding-top: 15px; }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <div class="title">Campus Coin — Student Financial Statement</div>
        <div class="subtitle">Generated for {{ $user->name }} ({{ $user->academic_year ?? 'Student' }}) • Period: {{ $periodStart }} to {{ $periodEnd }}</div>
    </div>

    <div class="summary-box">
        <div class="summary-item">
            <div class="summary-label">Total Income</div>
            <div class="summary-val val-income">{{ $currencySymbol }}{{ number_format($income, 2) }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Expenses</div>
            <div class="summary-val val-expense">{{ $currencySymbol }}{{ number_format($expense, 2) }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Net Saved Balance</div>
            <div class="summary-val val-net">{{ $currencySymbol }}{{ number_format($income - $expense, 2) }}</div>
        </div>
    </div>

    <h3>Category Breakdown Summary</h3>
    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th class="text-right">Total Amount</th>
                <th class="text-right">% of Expense</th>
            </tr>
        </thead>
        <tbody>
            @foreach($categoryBreakdown as $cb)
                <tr>
                    <td>{{ $cb->category?->name ?? 'Uncategorized' }}</td>
                    <td class="text-right">{{ $currencySymbol }}{{ number_format($cb->total, 2) }}</td>
                    <td class="text-right">{{ $expense > 0 ? round(($cb->total / $expense) * 100, 1) : 0 }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h3>Transaction Detail Log</h3>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Description</th>
                <th>Category</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $t)
                <tr>
                    <td>{{ $t->date->format('Y-m-d') }}</td>
                    <td style="text-transform: capitalize;">{{ $t->type }}</td>
                    <td>{{ $t->description }}</td>
                    <td>{{ $t->category?->name ?? 'Uncategorized' }}</td>
                    <td class="text-right" style="font-weight:bold; color: {{ $t->type == 'income' ? '#059669' : '#dc2626' }};">
                        {{ $t->type == 'income' ? '+' : '-' }}{{ $currencySymbol }}{{ number_format($t->amount, 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Campus Coin NextGen BudgetBee • Official Student Financial Summary Statement • Date Generated: {{ date('Y-m-d H:i:s') }}
    </div>
</body>
</html>
