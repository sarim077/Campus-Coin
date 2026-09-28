<?php

namespace Tests\Feature;

use App\Mail\SavingsGoalDeadlineMail;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Services\BudgetIncomeAllocator;
use App\Services\SavingsGoalService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SavingsGoalDeadlineTest extends TestCase
{
    use RefreshDatabase;

    public function test_day_before_email_reports_the_amount_still_needed_and_is_sent_once(): void
    {
        Mail::fake();
        Carbon::setTestNow('2026-10-14 10:00:00');

        try {
            $student = $this->studentWithGoal(500, '2026-10-15');
            $this->income($student, 400, '2026-10-14');

            $this->assertSame(1, app(SavingsGoalService::class)->sendDueNotifications());
            $this->assertSame(0, app(SavingsGoalService::class)->sendDueNotifications());

            Mail::assertSent(SavingsGoalDeadlineMail::class, 1);
            Mail::assertSent(SavingsGoalDeadlineMail::class, function (SavingsGoalDeadlineMail $mail) use ($student): bool {
                return $mail->hasTo($student->email)
                    && $mail->notificationType === 'reminder'
                    && $mail->savedAmount === 400.0
                    && $mail->shortfall === 100.0
                    && $mail->dueDate === 'October 15, 2026';
            });
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_goal_day_email_is_sent_only_when_the_goal_is_met_on_that_date(): void
    {
        Mail::fake();
        Carbon::setTestNow('2026-10-15 10:00:00');

        try {
            $student = $this->studentWithGoal(500, '2026-10-15');
            $this->income($student, 700, '2026-10-15');
            $category = $this->expenseCategory($student);
            Budget::create([
                'user_id' => $student->id,
                'category_id' => $category->id,
                'month' => '2026-10',
                'limit_amount' => 200,
            ]);
            $this->expense($student, $category, 100, '2026-10-15');

            $this->assertSame(1, app(SavingsGoalService::class)->sendDueNotifications());
            Mail::assertSent(SavingsGoalDeadlineMail::class, function (SavingsGoalDeadlineMail $mail) use ($student): bool {
                return $mail->hasTo($student->email)
                    && $mail->notificationType === 'achieved'
                    && $mail->savedAmount === 500.0
                    && $mail->shortfall === 0.0;
            });
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_goal_day_email_is_not_sent_if_the_target_is_not_maintained(): void
    {
        Mail::fake();
        Carbon::setTestNow('2026-10-15 10:00:00');

        try {
            $student = $this->studentWithGoal(500, '2026-10-15');
            $this->income($student, 600, '2026-10-15');
            $category = $this->expenseCategory($student);
            $this->expense($student, $category, 150, '2026-10-15');

            $this->assertSame(0, app(SavingsGoalService::class)->sendDueNotifications());
            Mail::assertNothingSent();
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_income_funds_category_budgets_before_contributing_to_the_goal(): void
    {
        Carbon::setTestNow('2026-10-10 10:00:00');

        try {
            $student = $this->studentWithGoal(300, '2026-10-31');
            $transport = $this->expenseCategory($student, 'Transport');
            $food = $this->expenseCategory($student, 'Food');
            Budget::create([
                'user_id' => $student->id,
                'category_id' => $transport->id,
                'month' => '2026-10',
                'limit_amount' => 700,
            ]);
            Budget::create([
                'user_id' => $student->id,
                'category_id' => $food->id,
                'month' => '2026-10',
                'limit_amount' => 400,
            ]);
            $this->income($student, 1000, '2026-10-10');

            $allocation = app(BudgetIncomeAllocator::class)->forMonth($student, now());
            $this->assertSame(700.0, $allocation['funded_by_category'][$transport->id]);
            $this->assertSame(300.0, $allocation['funded_by_category'][$food->id]);
            $this->assertSame(0.0, $allocation['remaining_for_goal']);
            $this->assertSame(0.0, app(SavingsGoalService::class)->savedAmount($student, now()));
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_income_left_after_budget_funding_contributes_to_the_goal(): void
    {
        Carbon::setTestNow('2026-10-10 10:00:00');

        try {
            $student = $this->studentWithGoal(300, '2026-10-31');
            $transport = $this->expenseCategory($student, 'Transport');
            Budget::create([
                'user_id' => $student->id,
                'category_id' => $transport->id,
                'month' => '2026-10',
                'limit_amount' => 700,
            ]);
            $this->income($student, 1000, '2026-10-10');
            $this->expense($student, $transport, 200, '2026-10-10');

            $this->assertSame(300.0, app(SavingsGoalService::class)->savedAmount($student, now()));
            $allocation = app(BudgetIncomeAllocator::class)->forMonth($student, now());
            $this->assertSame(300.0, $allocation['remaining_for_goal']);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_remaining_budgets_and_unbudgeted_spending_update_dashboard_balance_and_goal_progress(): void
    {
        Carbon::setTestNow('2026-10-10 10:00:00');

        try {
            $student = $this->studentWithGoal(5000, '2026-10-31');
            $transport = $this->expenseCategory($student, 'Transport');
            $food = $this->expenseCategory($student, 'Food');
            $other = $this->expenseCategory($student, 'Other');
            Budget::create([
                'user_id' => $student->id,
                'category_id' => $transport->id,
                'month' => '2026-10',
                'limit_amount' => 700,
            ]);
            Budget::create([
                'user_id' => $student->id,
                'category_id' => $food->id,
                'month' => '2026-10',
                'limit_amount' => 1600,
            ]);
            $this->income($student, 5000, '2026-10-10');

            $initialAllocation = app(BudgetIncomeAllocator::class)->forMonth($student, now());
            $this->assertSame(2300.0, $initialAllocation['remaining_in_budgets']);
            $this->assertSame(2700.0, $initialAllocation['remaining_for_goal']);

            $this->expense($student, $food, 300, '2026-10-10');
            $afterBudgetExpense = app(BudgetIncomeAllocator::class)->forMonth($student, now());
            $this->assertSame(2000.0, $afterBudgetExpense['remaining_in_budgets']);
            $this->assertSame(2700.0, $afterBudgetExpense['remaining_for_goal']);

            $this->expense($student, $other, 100, '2026-10-10');

            $allocation = app(BudgetIncomeAllocator::class)->forMonth($student, now());
            $this->assertSame(2300.0, $allocation['allocated_to_budgets']);
            $this->assertSame(2000.0, $allocation['remaining_in_budgets']);
            $this->assertSame(100.0, $allocation['unbudgeted_expenses']);
            $this->assertSame(2600.0, $allocation['remaining_for_goal']);
            $this->assertSame(2600.0, app(SavingsGoalService::class)->savedAmount($student, now()));

            $this->actingAs($student)
                ->get(route('student.dashboard'))
                ->assertOk()
                ->assertSee('$2,000.00')
                ->assertSee('$2,300.00 allocated this month')
                ->assertSee('$2,600.00')
                ->assertSee('Budget remaining');
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_unbudgeted_expense_can_be_logged_and_reduces_available_balance(): void
    {
        Carbon::setTestNow('2026-10-10 10:00:00');

        try {
            $student = $this->studentWithGoal(500, '2026-10-31');
            $food = $this->expenseCategory($student, 'Food');
            Budget::create([
                'user_id' => $student->id,
                'category_id' => $food->id,
                'month' => '2026-10',
                'limit_amount' => 500,
            ]);
            $this->income($student, 1000, '2026-10-10');

            $this->actingAs($student)
                ->get(route('transactions.create'))
                ->assertOk()
                ->assertSee('Other / unbudgeted — deduct from available balance');

            $this->post(route('transactions.store'), [
                'type' => 'expense',
                'amount' => 250,
                'category_id' => 'unbudgeted',
                'description' => 'Unbudgeted personal purchase',
                'date' => '2026-10-10',
            ])->assertRedirect(route('transactions.index'));

            $this->assertDatabaseHas('transactions', [
                'user_id' => $student->id,
                'type' => 'expense',
                'category_id' => null,
                'description' => 'Unbudgeted personal purchase',
            ]);

            $allocation = app(BudgetIncomeAllocator::class)->forMonth($student, now());
            $this->assertSame(500.0, $allocation['remaining_in_budgets']);
            $this->assertSame(250.0, $allocation['unbudgeted_expenses']);
            $this->assertSame(250.0, $allocation['remaining_for_goal']);

            $this->from(route('transactions.create'))
                ->post(route('transactions.store'), [
                    'type' => 'income',
                    'amount' => 50,
                    'category_id' => 'unbudgeted',
                    'description' => 'Invalid unbudgeted income',
                    'date' => '2026-10-10',
                ])
                ->assertSessionHasErrors('category_id');

            $this->actingAs($student)
                ->get(route('student.dashboard'))
                ->assertOk()
                ->assertSee('$250.00');

            $this->get(route('budgets.index'))
                ->assertOk()
                ->assertDontSee('income allocation:');
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_profile_saves_goal_amount_and_due_date_as_a_new_goal_period(): void
    {
        Carbon::setTestNow('2026-10-10 10:00:00');

        try {
            $student = User::factory()->create([
                'role' => 'student',
                'monthly_savings_goal' => 0,
            ]);

            $this->actingAs($student)
                ->put(route('profile.update'), [
                    'name' => $student->name,
                    'academic_year' => $student->academic_year,
                    'monthly_allowance_baseline' => $student->monthly_allowance_baseline,
                    'monthly_savings_goal' => 500,
                    'savings_goal_due_date' => '2026-10-25',
                ])
                ->assertSessionHas('success');

            $student->refresh();
            $this->assertSame('2026-10-10', $student->savings_goal_started_at->toDateString());
            $this->assertSame('2026-10-25', $student->savings_goal_due_date->toDateString());
            $this->assertSame(1, $student->savings_goal_revision);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_goal_amount_and_date_can_be_updated_from_the_dashboard(): void
    {
        Carbon::setTestNow('2026-10-10 10:00:00');

        try {
            $student = $this->studentWithGoal(300, '2026-10-31');

            $this->actingAs($student)
                ->put(route('student.savings-goal.update'), [
                    'monthly_savings_goal' => 600,
                    'savings_goal_due_date' => '2026-10-25',
                ])
                ->assertRedirect()
                ->assertSessionHas('success');

            $student->refresh();
            $this->assertSame('600.00', $student->monthly_savings_goal);
            $this->assertSame('2026-10-10', $student->savings_goal_started_at->toDateString());
            $this->assertSame('2026-10-25', $student->savings_goal_due_date->toDateString());
            $this->assertSame(2, $student->savings_goal_revision);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_income_is_reserved_for_budgets_before_the_goal_period_starts(): void
    {
        Carbon::setTestNow('2026-10-10 10:00:00');

        try {
            $student = $this->studentWithGoal(300, '2026-10-31');
            $student->update(['savings_goal_started_at' => '2026-10-10']);
            $transport = $this->expenseCategory($student);
            Budget::create([
                'user_id' => $student->id,
                'category_id' => $transport->id,
                'month' => '2026-10',
                'limit_amount' => 700,
            ]);
            $this->income($student, 700, '2026-10-05');
            $this->income($student, 300, '2026-10-10');

            $this->assertSame(300.0, app(SavingsGoalService::class)->savedAmount($student, now()));
        } finally {
            Carbon::setTestNow();
        }
    }

    private function studentWithGoal(float $goal, string $dueDate): User
    {
        return User::factory()->create([
            'role' => 'student',
            'monthly_savings_goal' => $goal,
            'savings_goal_due_date' => $dueDate,
            'savings_goal_started_at' => now()->toDateString(),
            'savings_goal_revision' => 1,
        ]);
    }

    private function income(User $student, float $amount, string $date): void
    {
        Transaction::create([
            'user_id' => $student->id,
            'amount' => $amount,
            'type' => 'income',
            'description' => 'Income',
            'date' => $date,
        ]);
    }

    private function expenseCategory(User $student, string $name = 'Transport'): Category
    {
        return Category::create([
            'user_id' => $student->id,
            'name' => $name,
            'type' => 'expense',
            'is_default' => false,
        ]);
    }

    private function expense(User $student, Category $category, float $amount, string $date): void
    {
        Transaction::create([
            'user_id' => $student->id,
            'category_id' => $category->id,
            'amount' => $amount,
            'type' => 'expense',
            'description' => 'Expense',
            'date' => $date,
        ]);
    }
}
