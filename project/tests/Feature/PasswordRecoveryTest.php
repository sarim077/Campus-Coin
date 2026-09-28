<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use App\Services\PasswordResetCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordRecoveryTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Recovery User',
            'email' => 'recovery@example.test',
            'password' => Hash::make('old-password'),
            'role' => 'student',
        ]);
    }

    public function test_unregistered_email_is_identified_and_registered_email_receives_a_reset_code(): void
    {
        Notification::fake();
        $message = 'If an account matches that email address, a password reset code has been sent.';

        $this->post(route('password.email'), ['email' => 'missing@example.test'])
            ->assertRedirect()
            ->assertSessionHasErrors(['email' => 'This email address is not registered.']);

        $this->post(route('password.email'), ['email' => $this->user->email])
            ->assertRedirect(route('password.reset'))
            ->assertSessionHas('status', $message);

        $this->get(route('password.reset'))
            ->assertOk()
            ->assertSee('Verify Recovery Code')
            ->assertSee('name="code"', false)
            ->assertSee('value="'.$this->user->email.'"', false);

        Notification::assertSentTo($this->user, ResetPasswordNotification::class);
        $notification = Notification::sent($this->user, ResetPasswordNotification::class)->first();
        $this->assertSame('emails.password-reset-code', $notification->toMail($this->user)->view);
    }

    public function test_code_can_be_verified_once_then_used_to_set_a_password_and_login(): void
    {
        Notification::fake();
        $this->requestCode();
        $code = Notification::sent($this->user, ResetPasswordNotification::class)->first()->code;
        $this->assertDatabaseMissing('password_reset_codes', [
            'email' => $this->user->email,
            'code_hash' => $code,
        ]);

        $this->post(route('password.code.verify'), ['email' => $this->user->email, 'code' => $code])
            ->assertRedirect(route('password.reset'));
        $this->get(route('password.reset'))
            ->assertOk()
            ->assertSee('Verification complete for '.$this->user->email)
            ->assertDontSee('name="code"', false);
        $this->assertGuest();

        $this->post(route('password.update'), [
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect(route('login'));

        $this->assertGuest();
        $this->assertTrue(Hash::check('new-password', $this->user->fresh()->password));

        $this->post(route('password.code.verify'), ['email' => $this->user->email, 'code' => $code])
            ->assertSessionHasErrors('code');
        $this->post(route('login'), ['email' => $this->user->email, 'password' => 'new-password'])
            ->assertRedirect(route('student.dashboard'));
        $this->assertAuthenticatedAs($this->user);
    }

    public function test_five_invalid_attempts_invalidate_the_code(): void
    {
        Notification::fake();
        $this->requestCode();
        $code = Notification::sent($this->user, ResetPasswordNotification::class)->first()->code;

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('password.code.verify'), [
                'email' => $this->user->email,
                'code' => $code === '000000' ? '000001' : '000000',
            ])->assertSessionHasErrors('code');
        }

        $this->assertDatabaseHas('password_reset_codes', [
            'email' => $this->user->email,
            'attempts' => 5,
            'code_hash' => null,
        ]);

        $this->assertNull(app(PasswordResetCodeService::class)->verifyCode($this->user->email, $code));
    }

    public function test_code_expires_after_ten_minutes(): void
    {
        Notification::fake();
        $this->requestCode();
        $code = Notification::sent($this->user, ResetPasswordNotification::class)->first()->code;
        $this->travel(11)->minutes();

        $this->post(route('password.code.verify'), ['email' => $this->user->email, 'code' => $code])
            ->assertSessionHasErrors('code');
    }

    public function test_repeated_code_requests_are_throttled_per_account(): void
    {
        Notification::fake();

        $this->requestCode();
        $this->requestCode();

        Notification::assertSentToTimes($this->user, ResetPasswordNotification::class, 1);
    }

    public function test_invalid_code_input_is_not_flashed_back_into_the_session(): void
    {
        $this->post(route('password.code.verify'), [
            'email' => $this->user->email,
            'code' => '123',
        ])
            ->assertSessionHasErrors('code')
            ->assertSessionMissing('_old_input.code');
    }

    private function requestCode(): void
    {
        $this->post(route('password.email'), ['email' => $this->user->email])
            ->assertRedirect(route('password.reset'))
            ->assertSessionHas('status');
    }
}
