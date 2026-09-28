<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Transaction;
use App\Models\User;
use Carbon\CarbonInterface;

class BudgetIncomeAllocator
{
    public function forMonth(User $user, CarbonInterface $date): array
    {
        $month = $date->format('Y-m');
        $income = (float) Transaction::query()
            ->where('user_id', $user->id)
            ->where('type', 'income')
            ->whereYear('date', substr($month, 0, 4))
            ->whereMonth('date', substr($month, 5, 2))
            ->sum('amount');
        $remainingIncome = round($income, 2);
        $fundedByCategory = [];
        $allocated = 0.0;

        $budgets = Budget::query()
            ->where('user_id', $user->id)
            ->where('month', $month)
            ->whereHas('category', fn ($query) => $query->where('type', 'expense'))
            ->orderBy('id')
            ->get(['id', 'category_id', 'limit_amount']);

        foreach ($budgets as $budget) {
            $funded = min($remainingIncome, (float) $budget->limit_amount);
            $fundedByCategory[$budget->category_id] = round($funded, 2);
            $remainingIncome = round($remainingIncome - $funded, 2);
            $allocated = round($allocated + $funded, 2);
        }

        $expensesByCategory = Transaction::query()
            ->where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereDate('date', '>=', $date->copy()->startOfMonth()->toDateString())
            ->whereDate('date', '<=', $date->copy()->endOfMonth()->toDateString())
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->get();
        $remainingByCategory = [];
        $remainingInBudgets = 0.0;
        $unbudgetedExpenses = 0.0;
        $overBudgetExpenses = 0.0;

        foreach ($fundedByCategory as $categoryId => $funded) {
            $spent = (float) $expensesByCategory
                ->firstWhere('category_id', $categoryId)?->total;
            $remaining = max(0.0, round($funded - $spent, 2));
            $remainingByCategory[$categoryId] = $remaining;
            $remainingInBudgets = round($remainingInBudgets + $remaining, 2);
            $overBudgetExpenses = round($overBudgetExpenses + max(0.0, $spent - $funded), 2);
        }

        foreach ($expensesByCategory as $expenses) {
            $categoryId = $expenses->category_id;
            if ($categoryId === null || ! array_key_exists($categoryId, $fundedByCategory)) {
                $unbudgetedExpenses = round($unbudgetedExpenses + (float) $expenses->total, 2);
            }
        }

        $remainingForGoal = max(0.0, round(
            $remainingIncome - $unbudgetedExpenses - $overBudgetExpenses,
            2
        ));

        return [
            'income' => round($income, 2),
            'funded_by_category' => $fundedByCategory,
            'allocated_to_budgets' => $allocated,
            'remaining_by_category' => $remainingByCategory,
            'remaining_in_budgets' => $remainingInBudgets,
            'income_after_budgets' => max(0.0, $remainingIncome),
            'unbudgeted_expenses' => $unbudgetedExpenses,
            'over_budget_expenses' => $overBudgetExpenses,
            'remaining_for_goal' => $remainingForGoal,
        ];
    }
}
