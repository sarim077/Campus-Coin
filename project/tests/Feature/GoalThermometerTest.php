<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoalThermometerTest extends TestCase
{
    use RefreshDatabase;

    public function test_goal_thermometer_formats_values_and_caps_visual_progress(): void
    {
        $this->blade(
            '<x-goal-thermometer :current-value="$currentValue" :goal-value="$goalValue" label="Savings goal" color="#16a34a" />',
            ['currentValue' => 60, 'goalValue' => 50]
        )
            ->assertSee('role="meter"', false)
            ->assertSee('aria-valuenow="100.0"', false)
            ->assertSee('--goal-thermometer-level: 100%;', false)
            ->assertSee('--goal-thermometer-color: #16a34a;', false)
            ->assertSee('Savings goal')
            ->assertSee('$60.00')
            ->assertSee('$50.00');
    }

    public function test_goal_thermometer_shows_a_neutral_prompt_for_zero_or_missing_goals(): void
    {
        foreach ([
            '<x-goal-thermometer :current-value="45" :goal-value="$goalValue" />',
            '<x-goal-thermometer :current-value="45" />',
        ] as $template) {
            $this->blade($template, ['goalValue' => 0])
                ->assertSee('aria-valuenow="0.0"', false)
                ->assertSee('--goal-thermometer-level: 0%;', false)
                ->assertSee('$45.00')
                ->assertSee('Set a goal to track progress')
                ->assertDontSee(' of ');
        }
    }

    public function test_student_dashboard_uses_the_thermometer_for_monthly_savings_progress(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'monthly_savings_goal' => 50,
            'savings_goal_started_at' => now()->toDateString(),
            'savings_goal_due_date' => now()->addDays(10)->toDateString(),
        ]);

        Transaction::create([
            'user_id' => $student->id,
            'amount' => 45,
            'type' => 'income',
            'description' => 'Monthly savings progress',
            'date' => now()->toDateString(),
        ]);

        $this->actingAs($student)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('aria-valuenow="90.0"', false)
            ->assertSee('$45.00')
            ->assertSee('$50.00')
            ->assertSee('action="'.route('student.savings-goal.update').'"', false)
            ->assertSee('name="monthly_savings_goal"', false)
            ->assertSee('name="savings_goal_due_date"', false)
            ->assertSee('value="unbudgeted" data-type="expense"', false)
            ->assertSee('Other / unbudgeted');
    }
}
