<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use App\Models\HiddenBudgetCategory;
use App\Models\Transaction;
use App\Services\BudgetIncomeAllocator;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $month = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
        ])['month'] ?? Carbon::now()->format('Y-m');

        $expenseCategories = Category::where('type', 'expense')
            ->where(function ($q) use ($user) {
                $q->where('is_default', true)->orWhere('user_id', $user->id);
            })->get();
        $hiddenCategoryIds = HiddenBudgetCategory::where('user_id', $user->id)
            ->pluck('category_id');
        $hiddenCategories = Category::where('type', 'expense')
            ->whereIn('id', $hiddenCategoryIds)
            ->orderBy('name')
            ->get();
        $visibleCategories = $expenseCategories->whereNotIn('id', $hiddenCategoryIds);

        $budgetRecords = Budget::where('user_id', $user->id)
            ->where('month', $month)
            ->orderBy('id')
            ->get()
            ->keyBy('category_id');
        $funding = app(BudgetIncomeAllocator::class)->forMonth($user, Carbon::parse($month.'-01'));

        [$year, $mNum] = explode('-', $month);

        $budgetDetails = $visibleCategories->map(function ($cat) use ($budgetRecords, $funding, $user, $year, $mNum) {
            $budget = $budgetRecords->get($cat->id);
            $limit = $budget ? $budget->limit_amount : 0;

            $spent = Transaction::where('user_id', $user->id)
                ->where('category_id', $cat->id)
                ->where('type', 'expense')
                ->whereYear('date', $year)
                ->whereMonth('date', $mNum)
                ->sum('amount');

            $percentage = $limit > 0 ? round(($spent / $limit) * 100) : 0;

            $status = 'none';
            if ($limit > 0) {
                if ($percentage >= 100) {
                    $status = 'exceeded';
                } elseif ($percentage >= 80) {
                    $status = 'warning';
                } else {
                    $status = 'safe';
                }
            }

            return [
                'category_id' => $cat->id,
                'category_name' => $cat->name,
                'icon' => $cat->icon,
                'budget_id' => $budget ? $budget->id : null,
                'limit_amount' => $limit,
                'funded' => $funding['funded_by_category'][$cat->id] ?? 0,
                'spent' => $spent,
                'remaining' => max(0, $limit - $spent),
                'percentage' => min(100, $percentage),
                'raw_percentage' => $percentage,
                'status' => $status,
            ];
        });

        return view('budgets.index', compact('budgetDetails', 'month', 'funding', 'hiddenCategories'));
    }

    public function hideCategory(Request $request, Category $category)
    {
        $this->ensureCategoryCanBeManaged($category, $request->user()->id);
        $validated = $request->validate(['month' => ['nullable', 'date_format:Y-m']]);

        HiddenBudgetCategory::firstOrCreate([
            'user_id' => $request->user()->id,
            'category_id' => $category->id,
        ]);

        return redirect()->route('budgets.index', array_filter(['month' => $validated['month'] ?? null]))
            ->with('success', "{$category->name} was hidden from your budget list. Its budget and spending data are unchanged.");
    }

    public function showCategory(Request $request, Category $category)
    {
        $this->ensureCategoryCanBeManaged($category, $request->user()->id);
        $validated = $request->validate(['month' => ['nullable', 'date_format:Y-m']]);

        HiddenBudgetCategory::where('user_id', $request->user()->id)
            ->where('category_id', $category->id)
            ->delete();

        return redirect()->route('budgets.index', array_filter(['month' => $validated['month'] ?? null]))
            ->with('success', "{$category->name} is visible in your budget list again.");
    }

    public function storeOrUpdate(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'month' => 'required|date_format:Y-m',
            'limit_amount' => 'required|numeric|min:0',
        ]);

        Budget::updateOrCreate(
            [
                'user_id' => $user->id,
                'category_id' => $request->category_id,
                'month' => $request->month,
            ],
            [
                'limit_amount' => $request->limit_amount,
            ]
        );

        return redirect()->route('budgets.index', ['month' => $request->month])
            ->with('success', 'Monthly budget updated successfully!');
    }

    private function ensureCategoryCanBeManaged(Category $category, int $userId): void
    {
        abort_unless(
            $category->type === 'expense' && ($category->is_default || $category->user_id === $userId),
            404
        );
    }
}
