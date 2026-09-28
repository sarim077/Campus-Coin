<?php

namespace App\Http\Controllers;

use App\Services\SavingsGoalService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SavingsGoalController extends Controller
{
    public function update(Request $request, SavingsGoalService $savingsGoals)
    {
        $validated = $request->validate([
            'monthly_savings_goal' => ['required', 'numeric', 'min:0'],
            'savings_goal_due_date' => [
                Rule::requiredIf(fn () => (float) $request->input('monthly_savings_goal', 0) > 0),
                'nullable',
                'date',
                'after_or_equal:today',
            ],
        ]);

        $savingsGoals->updateGoal(
            $request->user(),
            (float) $validated['monthly_savings_goal'],
            $validated['savings_goal_due_date'] ?? null
        );

        return back()->with('success', 'Savings goal updated successfully.');
    }
}
