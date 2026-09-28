<?php

namespace Tests\Feature;

use App\Mail\WelcomeStudentMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class StudentRegionPreferenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_requires_a_supported_country_and_saves_its_currency_region(): void
    {
        Mail::fake();

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Country/region')
            ->assertSee('value="PK"', false)
            ->assertDontSee('name="monthly_allowance_baseline"', false)
            ->assertDontSee('name="monthly_savings_goal"', false)
            ->assertDontSee('name="savings_goal_due_date"', false)
            ->assertSee('Site language stays English until you change it.');

        $this->post(route('register'), [
            'name' => 'Pakistan Student',
            'email' => 'pakistan@example.test',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'region' => 'PK',
        ])->assertRedirect(route('student.dashboard'));

        $student = User::where('email', 'pakistan@example.test')->firstOrFail();
        $this->assertSame('PK', $student->region);
        $this->assertSame('Rs', $student->currency_symbol);
        $this->assertSame('0.00', $student->monthly_allowance_baseline);
        $this->assertSame('0.00', $student->monthly_savings_goal);
        Mail::assertSent(WelcomeStudentMail::class, fn (WelcomeStudentMail $mail): bool => $mail->hasTo($student->email)
            && $mail->user->is($student));

        $this->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('Rs0.00')
            ->assertSee('<html lang="en"', false);
    }

    public function test_registration_rejects_missing_or_unsupported_country_codes(): void
    {
        $payload = [
            'name' => 'New Student',
            'email' => 'newstudent@example.test',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ];

        $this->from(route('register'))
            ->post(route('register'), $payload + ['region' => ''])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('region');

        $this->from(route('register'))
            ->post(route('register'), $payload + ['region' => 'ZZ'])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('region');

        $this->assertDatabaseMissing('users', ['email' => 'newstudent@example.test']);
    }

    public function test_student_region_currency_overrides_the_global_currency_setting_without_changing_language(): void
    {
        $student = User::create([
            'name' => 'India Student',
            'email' => 'india@example.test',
            'password' => 'secret123',
            'role' => 'student',
            'region' => 'IN',
        ]);

        $this->actingAs($student)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('₹')
            ->assertSee('<html lang="en"', false);
    }
}
