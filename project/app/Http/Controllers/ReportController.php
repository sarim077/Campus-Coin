<?php

namespace App\Http\Controllers;

use App\Mail\ShareSummaryMail;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $data = $this->reportData($request);

        return view('reports.index', $data);
    }

    public function exportPdf(Request $request)
    {
        $data = $this->reportData($request);

        return view('reports.pdf', $data);
    }

    public function email(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'month' => ['nullable', 'date_format:Y-m'],
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'category_id' => ['nullable', 'integer'],
            'income_source_id' => ['nullable', 'integer'],
        ]);

        $data = $this->reportData($request);
        $details = [
            'Period' => $data['periodStart'] . ' to ' . $data['periodEnd'],
            'Income' => $data['currencySymbol'] . number_format($data['income'], 2),
            'Expenses' => $data['currencySymbol'] . number_format($data['expense'], 2),
            'Net balance' => $data['currencySymbol'] . number_format($data['income'] - $data['expense'], 2),
            'Transactions included' => (string) $data['transactions']->count(),
        ];
        foreach ($data['categoryBreakdown']->take(5) as $breakdown) {
            $details[$breakdown->category?->name ?? 'Uncategorized'] =
                $data['currencySymbol'] . number_format((float) $breakdown->total, 2);
        }

        Mail::to($validated['email'])->send(new ShareSummaryMail(
            'Financial report — ' . $data['month'],
            'Here is your Campus Coin financial summary for ' . $data['periodStart'] . ' through ' . $data['periodEnd'] . '.',
            $details
        ));

        return redirect()->route('reports.index', $request->except('email'))
            ->with('success', 'Financial report emailed successfully.');
    }

    private function reportData(Request $request): array
    {
        $filters = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'category_id' => ['nullable', 'integer'],
            'income_source_id' => ['nullable', 'integer'],
        ]);

        $user = $request->user();
        $month = $filters['month'] ?? now()->format('Y-m');
        $monthStart = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $periodStart = $filters['start_date'] ?? $monthStart->toDateString();
        $periodEnd = $filters['end_date'] ?? $monthStart->copy()->endOfMonth()->toDateString();
        $categoryId = $filters['category_id'] ?? null;
        $incomeSourceId = $filters['income_source_id'] ?? null;

        $availableCategories = Category::where(function ($query) use ($user) {
            $query->where('is_default', true)->orWhere('user_id', $user->id);
        })->get();

        if ($categoryId) {
            abort_unless($availableCategories->contains('id', (int) $categoryId), 403);
        }
        if ($incomeSourceId) {
            abort_unless(
                $availableCategories->contains(fn (Category $category) => $category->id === (int) $incomeSourceId && $category->type === 'income'),
                403
            );
        }

        $applyPeriod = static function ($query) use ($periodStart, $periodEnd) {
            return $query->whereDate('date', '>=', $periodStart)
                ->whereDate('date', '<=', $periodEnd);
        };

        $categoryBreakdown = $applyPeriod(
            Transaction::where('user_id', $user->id)->where('type', 'expense')
        )
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->select('category_id', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('category_id')
            ->with('category')
            ->orderByDesc('total')
            ->get();

        $incomeQuery = $applyPeriod(Transaction::where('user_id', $user->id)->where('type', 'income'));
        if ($incomeSourceId) {
            $incomeQuery->where('category_id', $incomeSourceId);
        }
        $income = (float) $incomeQuery->sum('amount');

        $expenseQuery = $applyPeriod(Transaction::where('user_id', $user->id)->where('type', 'expense'));
        if ($categoryId) {
            $expenseQuery->where('category_id', $categoryId);
        }
        $expense = (float) $expenseQuery->sum('amount');

        $sixMonthsData = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonthsNoOverflow($i);
            $incomeQuery = Transaction::where('user_id', $user->id)
                ->where('type', 'income')
                ->whereYear('date', $date->year)
                ->whereMonth('date', $date->month);
            if ($incomeSourceId) {
                $incomeQuery->where('category_id', $incomeSourceId);
            }
            $expenseQuery = Transaction::where('user_id', $user->id)
                ->where('type', 'expense')
                ->whereYear('date', $date->year)
                ->whereMonth('date', $date->month);
            if ($categoryId) {
                $expenseQuery->where('category_id', $categoryId);
            }
            $monthIncome = (float) $incomeQuery->sum('amount');
            $monthExpense = (float) $expenseQuery->sum('amount');
            $sixMonthsData[] = [
                'month' => $date->format('M Y'),
                'income' => $monthIncome,
                'expense' => $monthExpense,
                'net' => $monthIncome - $monthExpense,
            ];
        }

        $dailySummaries = $applyPeriod(Transaction::where('user_id', $user->id))
            ->select('date')
            ->selectRaw(
                'SUM(CASE WHEN type = ? AND (? IS NULL OR category_id = ?) THEN amount ELSE 0 END) as daily_income',
                ['income', $incomeSourceId, $incomeSourceId]
            )
            ->selectRaw(
                'SUM(CASE WHEN type = ? AND (? IS NULL OR category_id = ?) THEN amount ELSE 0 END) as daily_expense',
                ['expense', $categoryId, $categoryId]
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $weeklySummaries = $dailySummaries
            ->groupBy(fn ($day) => Carbon::parse($day->date)->startOfWeek()->toDateString())
            ->map(fn ($days) => (object) [
                'weekly_income' => $days->sum('daily_income'),
                'weekly_expense' => $days->sum('daily_expense'),
            ])->values();

        $transactionsQuery = $applyPeriod(Transaction::where('user_id', $user->id));
        if ($categoryId || $incomeSourceId) {
            $transactionsQuery->where(function ($query) use ($categoryId, $incomeSourceId) {
                if ($categoryId) {
                    $query->where(function ($query) use ($categoryId) {
                        $query->where('type', 'expense')->where('category_id', $categoryId);
                    });
                }
                if ($incomeSourceId) {
                    $method = $categoryId ? 'orWhere' : 'where';
                    $query->{$method}(function ($query) use ($incomeSourceId) {
                        $query->where('type', 'income')->where('category_id', $incomeSourceId);
                    });
                }
            });
        }

        return [
            'user' => $user,
            'month' => $month,
            'periodStart' => $periodStart,
            'periodEnd' => $periodEnd,
            'categoryId' => $categoryId,
            'incomeSourceId' => $incomeSourceId,
            'categoryBreakdown' => $categoryBreakdown,
            'totalMonthlyExpense' => $expense,
            'income' => $income,
            'expense' => $expense,
            'sixMonthsData' => $sixMonthsData,
            'dailySummaries' => $dailySummaries,
            'weeklySummaries' => $weeklySummaries,
            'categories' => $availableCategories,
            'incomeSources' => $availableCategories->where('type', 'income'),
            'transactions' => $transactionsQuery->with('category')->orderBy('date')->get(),
            'currencySymbol' => $user->currency_symbol ?: (app('site.settings')->currency_symbol ?: '$'),
        ];
    }
}
