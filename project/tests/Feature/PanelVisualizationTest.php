<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanelVisualizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_panel_keeps_trend_flat_and_only_donut_chart_three_dimensional(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('trendChart')
            ->assertSee('studentCategoryChart')
            ->assertSee('cc-chart-stage')
            ->assertSee('cc-donut-card')
            ->assertSee('.cc-chart-stage {', false)
            ->assertSee('.cc-chart-stage canvas { filter: none; }', false)
            ->assertSee('.cc-donut-stage {', false)
            ->assertSee('Your six-month trend will appear after you log a transaction.')
            ->assertSee('Your spending mix will appear as you record expenses this month.')
            ->assertSee('portal-menu-toggle')
            ->assertSee('.authenticated-panel .campus-navbar .portal-menu-toggle:hover', false)
            ->assertSee('.authenticated-panel .campus-footer .footer-link:hover', false)
            ->assertSee('navbar-expand-xl');
    }

    public function test_admin_dashboard_shows_account_health_without_student_financial_details(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student', 'name' => 'Private Student']);
        User::factory()->create(['role' => 'student', 'is_disabled' => true]);
        Transaction::create([
            'user_id' => $student->id,
            'amount' => 9876.54,
            'type' => 'expense',
            'description' => 'Private purchase description',
            'date' => now()->toDateString(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('REGISTERED STUDENTS')
            ->assertSee('ACTIVE ACCOUNTS')
            ->assertSee('DISABLED ACCOUNTS')
            ->assertSee('Manage students')
            ->assertSee(route('admin.users'))
            ->assertDontSee('SYSTEM INCOME')
            ->assertDontSee('SYSTEM EXPENSES')
            ->assertDontSee('Campus-wide money flow')
            ->assertDontSee('Private Student')
            ->assertDontSee('Private purchase description')
            ->assertDontSee('9,876.54')
            ->assertSee('portal-menu-toggle')
            ->assertSee('.authenticated-panel .campus-navbar .portal-menu-toggle:hover', false)
            ->assertSee('.authenticated-panel .campus-footer .footer-link:hover', false)
            ->assertSee('navbar-expand-xl');
    }
}
