<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\Category;
use App\Models\HiddenBudgetCategory;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetCategoryVisibilityTest extends TestCase
{
    use RefreshDatabase;

    private User $student;

    private User $otherStudent;

    private Category $foodCategory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->student = User::create([
            'name' => 'Budget Student',
            'email' => 'budget-student@example.test',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);
        $this->otherStudent = User::create([
            'name' => 'Other Student',
            'email' => 'other-student@example.test',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);
        $this->foodCategory = Category::create([
            'name' => 'Food',
            'type' => 'expense',
            'is_default' => true,
        ]);
    }

    public function test_student_can_hide_and_restore_a_budget_category_without_changing_its_data(): void
    {
        $month = now()->format('Y-m');
        $budget = Budget::create([
            'user_id' => $this->student->id,
            'category_id' => $this->foodCategory->id,
            'month' => $month,
            'limit_amount' => 250,
        ]);
        $transaction = Transaction::create([
            'user_id' => $this->student->id,
            'category_id' => $this->foodCategory->id,
            'amount' => 25,
            'type' => 'expense',
            'description' => 'Lunch',
            'date' => now()->toDateString(),
            'is_recurring' => false,
        ]);

        $this->actingAs($this->student)
            ->post(route('budgets.categories.hide', $this->foodCategory), ['month' => $month])
            ->assertRedirect(route('budgets.index', ['month' => $month]));

        $hiddenPage = $this->get(route('budgets.index', ['month' => $month]))
            ->assertOk()
            ->assertSee('Hidden categories')
            ->assertSee('Food')
            ->assertSee(route('budgets.categories.show', $this->foodCategory));
        $this->assertSame(0, substr_count($hiddenPage->getContent(), 'aria-label="Hide Food from budget section"'));
        $this->assertDatabaseHas('budgets', ['id' => $budget->id, 'limit_amount' => 250]);
        $this->assertDatabaseHas('transactions', ['id' => $transaction->id, 'amount' => 25]);
        $this->assertDatabaseHas('hidden_budget_categories', [
            'user_id' => $this->student->id,
            'category_id' => $this->foodCategory->id,
        ]);

        $this->actingAs($this->otherStudent)
            ->get(route('budgets.index', ['month' => $month]))
            ->assertSee('aria-label="Hide Food from budget section"', false)
            ->assertDontSee('Hidden categories');

        $this->actingAs($this->student)
            ->post(route('budgets.categories.show', $this->foodCategory), ['month' => $month])
            ->assertRedirect(route('budgets.index', ['month' => $month]));

        $this->get(route('budgets.index', ['month' => $month]))
            ->assertSee('aria-label="Hide Food from budget section"', false)
            ->assertDontSee('Hidden categories');
        $this->assertDatabaseMissing('hidden_budget_categories', [
            'user_id' => $this->student->id,
            'category_id' => $this->foodCategory->id,
        ]);
    }

    public function test_student_cannot_hide_another_students_category_or_an_income_category(): void
    {
        $privateCategory = Category::create([
            'name' => "Other student's category",
            'type' => 'expense',
            'is_default' => false,
            'user_id' => $this->otherStudent->id,
        ]);
        $incomeCategory = Category::create([
            'name' => 'Allowance',
            'type' => 'income',
            'is_default' => true,
        ]);

        $this->actingAs($this->student)
            ->post(route('budgets.categories.hide', $privateCategory))
            ->assertNotFound();
        $this->post(route('budgets.categories.hide', $incomeCategory))
            ->assertNotFound();

        $this->assertSame(0, HiddenBudgetCategory::count());
    }
}
