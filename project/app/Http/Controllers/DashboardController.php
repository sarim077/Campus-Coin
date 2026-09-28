<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Insight;
use App\Models\RecentlyViewed;
use App\Models\SavingTip;
use App\Models\Transaction;
use App\Services\BudgetIncomeAllocator;
use App\Services\SavingsGoalService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $currentMonthStr = Carbon::now()->format('Y-m');
        [$year, $mNum] = explode('-', $currentMonthStr);

        // Current month totals
        $monthlyIncome = Transaction::where('user_id', $user->id)
            ->where('type', 'income')
            ->whereYear('date', $year)
            ->whereMonth('date', $mNum)
            ->sum('amount');

        $monthlyExpense = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereYear('date', $year)
            ->whereMonth('date', $mNum)
            ->sum('amount');

        $budgetFunding = app(BudgetIncomeAllocator::class)->forMonth($user, Carbon::now());
        $netBalance = $budgetFunding['remaining_for_goal'];

        // Savings goal progress
        $savingsGoal = $user->monthly_savings_goal ?? 0;
        $savingsGoalProgress = app(SavingsGoalService::class)->savedAmount($user, Carbon::now());
        $savingsGoalDueDate = $user->savings_goal_due_date;

        // Top Spending Category this month
        $topCategory = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereYear('date', $year)
            ->whereMonth('date', $mNum)
            ->select('category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->with('category')
            ->first();

        $categoryBreakdown = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereYear('date', $year)
            ->whereMonth('date', $mNum)
            ->select('category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('category_id')
            ->with('category')
            ->orderByDesc('total')
            ->take(6)
            ->get();

        // Budgets vs Actual for current month
        $budgets = Budget::where('user_id', $user->id)
            ->where('month', $currentMonthStr)
            ->with('category')
            ->get()
            ->map(function ($budget) use ($user, $year, $mNum, $budgetFunding) {
                $spent = Transaction::where('user_id', $user->id)
                    ->where('category_id', $budget->category_id)
                    ->where('type', 'expense')
                    ->whereYear('date', $year)
                    ->whereMonth('date', $mNum)
                    ->sum('amount');

                $percentage = $budget->limit_amount > 0 ? round(($spent / $budget->limit_amount) * 100) : 0;
                $status = 'safe';
                if ($percentage >= 100) {
                    $status = 'exceeded';
                } elseif ($percentage >= 80) {
                    $status = 'warning';
                }

                return [
                    'category' => $budget->category->name ?? 'Uncategorized',
                    'limit' => $budget->limit_amount,
                    'funded' => $budgetFunding['funded_by_category'][$budget->category_id] ?? 0,
                    'spent' => $spent,
                    'percentage' => min(100, $percentage),
                    'raw_percentage' => $percentage,
                    'status' => $status,
                ];
            });

        // 6-Month Income vs Expense Trend Chart Data
        $chartMonths = [];
        $chartIncome = [];
        $chartExpenses = [];

        for ($i = 5; $i >= 0; $i--) {
            $m = Carbon::now()->subMonths($i);
            $mYear = $m->format('Y');
            $mMonth = $m->format('m');
            $mLabel = $m->format('M Y');

            $inc = Transaction::where('user_id', $user->id)
                ->where('type', 'income')
                ->whereYear('date', $mYear)
                ->whereMonth('date', $mMonth)
                ->sum('amount');

            $exp = Transaction::where('user_id', $user->id)
                ->where('type', 'expense')
                ->whereYear('date', $mYear)
                ->whereMonth('date', $mMonth)
                ->sum('amount');

            $chartMonths[] = $mLabel;
            $chartIncome[] = (float) $inc;
            $chartExpenses[] = (float) $exp;
        }
        $chartPeakTotal = max(array_map(
            fn (float $income, float $expense) => $income + $expense,
            $chartIncome,
            $chartExpenses
        )) ?: 0;

        // Active Saving Tips ranked by potential savings
        $savingTips = SavingTip::where('user_id', $user->id)
            ->where('status', '!=', 'dismissed')
            ->orderByRaw("CASE WHEN status = 'pinned' THEN 0 ELSE 1 END")
            ->orderByDesc('potential_savings')
            ->take(4)
            ->get();

        // Recent Transactions
        $recentTransactions = Transaction::where('user_id', $user->id)
            ->with('category')
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->take(7)
            ->get();

        // System Announcements
        $announcements = Announcement::where('is_active', true)
            ->orderByDesc('created_at')
            ->take(3)
            ->get();

        // User Categories for Quick Add
        $incomeCategories = Category::where('type', 'income')
            ->where(function ($q) use ($user) {
                $q->where('is_default', true)->orWhere('user_id', $user->id);
            })->get();

        $expenseCategories = Category::where('type', 'expense')
            ->where(function ($q) use ($user) {
                $q->where('is_default', true)->orWhere('user_id', $user->id);
            })->get();

        // Latest Monthly AI Insight
        $latestInsight = Insight::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->first();

        // Recently Viewed Transactions across sessions
        $recentlyViewed = RecentlyViewed::where('user_id', $user->id)
            ->with(['transaction.category'])
            ->orderByDesc('viewed_at')
            ->take(5)
            ->get();

        return view('student.dashboard', compact(
            'user',
            'monthlyIncome',
            'monthlyExpense',
            'netBalance',
            'savingsGoal',
            'savingsGoalProgress',
            'savingsGoalDueDate',
            'budgetFunding',
            'topCategory',
            'categoryBreakdown',
            'budgets',
            'chartMonths',
            'chartIncome',
            'chartExpenses',
            'chartPeakTotal',
            'savingTips',
            'recentTransactions',
            'announcements',
            'incomeCategories',
            'expenseCategories',
            'latestInsight',
            'recentlyViewed'
        ));
    }
}
