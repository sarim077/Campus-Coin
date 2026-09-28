<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_landing_page_renders_the_campus_coin_sections(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('rel="icon" type="image/svg+xml"', false)
            ->assertSee('favicon.svg')
            ->assertSee('aria-label="Campus Coin social links"', false)
            ->assertSee('aria-label="Campus Coin on LinkedIn"', false)
            ->assertSee('Make every')
            ->assertSee('coin count.')
            ->assertDontSee('Student panel demo')
            ->assertDontSee('Admin panel demo')
            ->assertSee('home-scroll-progress')
            ->assertSee('coin-story-stage')
            ->assertSee('coin-marquee-track')
            ->assertSee('coin-glass')
            ->assertSee('helix-bead')
            ->assertSee('data-strand="front"', false)
            ->assertSee('href="'.route('login').'"', false)
            ->assertSee('href="'.route('register').'"', false)
            ->assertDontSee('class="scroll-cue"', false)
            ->assertSee('data-scroll-steps', false)
            ->assertSee('steps-sticky')
            ->assertSee('aria-current="step"', false)
            ->assertSee('A clearer picture of your')
            ->assertSee('Focus on a budget category')
            ->assertSee('hero-chart-backdrop')
            ->assertSee('story-line-glow')
            ->assertSee('story-line-tracer')
            ->assertSee('Money stuff, made a little clearer.');

        $this->assertSame(2, substr_count($response->getContent(), 'class="helix-bead"'));
    }

    public function test_login_page_only_offers_the_real_account_login(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('auth-shell-login')
            ->assertSee('class="site-footer auth-marketing-footer"', false)
            ->assertSee('class="auth-story-brand text-decoration-none"', false)
            ->assertSee('<svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="15" rx="3"/>', false)
            ->assertSee('favicon.svg')
            ->assertSee('data-password-toggle="password"', false)
            ->assertSee('name="email"', false)
            ->assertSee('Students and administrators can sign in here')
            ->assertDontSee('student123')
            ->assertDontSee('admin123')
            ->assertDontSee('demo-login');
    }

    public function test_registration_page_keeps_student_profile_fields_and_password_controls(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee('auth-shell-register')
            ->assertSee('class="site-footer auth-marketing-footer"', false)
            ->assertSee('class="auth-story-brand text-decoration-none"', false)
            ->assertSee('<svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="15" rx="3"/>', false)
            ->assertSee('favicon.svg')
            ->assertSee('name="academic_year"', false)
            ->assertSee('name="region"', false)
            ->assertDontSee('name="monthly_allowance_baseline"', false)
            ->assertDontSee('name="monthly_savings_goal"', false)
            ->assertDontSee('name="savings_goal_due_date"', false)
            ->assertSee('data-password-toggle="password_confirmation"', false)
            ->assertSee('name="password_confirmation"', false);
    }

    public function test_demo_login_endpoints_are_not_available(): void
    {
        $this->post('/demo-login/student')->assertNotFound();
        $this->post('/demo-login/admin')->assertNotFound();
    }
}
