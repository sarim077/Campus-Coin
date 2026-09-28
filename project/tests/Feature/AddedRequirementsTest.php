<?php

namespace Tests\Feature;

use App\Mail\AdminPasswordResetMail;
use App\Mail\ShareSummaryMail;
use App\Models\Category;
use App\Models\Insight;
use App\Models\SavingTip;
use App\Models\SavingTipTemplate;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class AddedRequirementsTest extends TestCase
{
    use RefreshDatabase;

    private User $student;

    private Category $expenseCategory;

    private Category $incomeCategory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->student = User::create([
            'name' => 'Campus Student',
            'email' => 'student@example.test',
            'password' => Hash::make('test-password'),
            'role' => 'student',
            'monthly_allowance_baseline' => 500,
            'monthly_savings_goal' => 100,
        ]);
        $this->expenseCategory = Category::create([
            'name' => 'Food',
            'type' => 'expense',
            'is_default' => true,
        ]);
        $this->incomeCategory = Category::create([
            'name' => 'Allowance',
            'type' => 'income',
            'is_default' => true,
        ]);
    }

    public function test_password_recovery_sends_a_one_time_code_notification(): void
    {
        Notification::fake();

        $this->post(route('password.email'), ['email' => $this->student->email])
            ->assertRedirect()
            ->assertSessionHas('status', 'If an account matches that email address, a password reset code has been sent.');

        Notification::assertSentTo($this->student, ResetPasswordNotification::class);

        $notification = Notification::sent($this->student, ResetPasswordNotification::class)->first();
        $this->assertMatchesRegularExpression('/^\d{6}$/', $notification->code);
        $this->assertNull($notification->toMail($this->student)->actionText);
    }

    public function test_two_factor_setup_shows_branded_qr_and_one_time_recovery_codes(): void
    {
        $this->actingAs($this->student)
            ->get(route('profile.two-factor'))
            ->assertOk()
            ->assertSee('Campus Coin ('.$this->student->email.')')
            ->assertSee('Confirm and enable');

        $secret = session('two_factor.setup_secret');
        $code = (new Google2FA)->getCurrentOtp($secret);
        $this->post(route('profile.two-factor.enable'), ['code' => $code])
            ->assertRedirect(route('profile.two-factor'))
            ->assertSessionHas('recovery_codes');

        $this->assertTrue($this->student->fresh()->hasTwoFactorEnabled());
        $this->assertCount(10, $this->student->fresh()->two_factor_recovery_codes);
    }

    public function test_two_factor_login_accepts_a_single_use_recovery_code(): void
    {
        $recoveryCode = 'AB123-CD456';
        $this->student->forceFill([
            'two_factor_secret' => (new Google2FA)->generateSecretKey(),
            'two_factor_recovery_codes' => [Hash::make('AB123CD456')],
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->post(route('login'), [
            'email' => $this->student->email,
            'password' => 'test-password',
        ])->assertRedirect(route('two-factor.challenge'));
        $this->assertGuest();

        $this->post(route('two-factor.verify'), ['code' => $recoveryCode])
            ->assertRedirect(route('student.dashboard'));
        $this->assertAuthenticatedAs($this->student);
        $this->assertSame([], $this->student->fresh()->two_factor_recovery_codes);
    }

    public function test_two_factor_can_be_disabled_with_a_recovery_code_and_current_password(): void
    {
        $this->student->forceFill([
            'two_factor_secret' => (new Google2FA)->generateSecretKey(),
            'two_factor_recovery_codes' => [Hash::make('AB123CD456')],
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->actingAs($this->student)
            ->post(route('profile.two-factor.disable'), [
                'password' => 'test-password',
                'code' => 'AB123-CD456',
            ])->assertRedirect(route('profile.two-factor'));

        $this->assertFalse($this->student->fresh()->hasTwoFactorEnabled());
    }

    public function test_admin_has_a_separate_login_page_that_rejects_student_accounts(): void
    {
        $this->get(route('admin.login'))->assertOk()->assertSee('Administrator sign in');

        $this->from(route('admin.login'))->post(route('admin.login.submit'), [
            'email' => $this->student->email,
            'password' => 'test-password',
        ])->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_deleted_transactions_are_retained_and_can_be_restored(): void
    {
        $transaction = $this->createTransaction();

        $this->actingAs($this->student)
            ->delete(route('transactions.destroy', $transaction))
            ->assertRedirect(route('transactions.index'));

        $this->assertSoftDeleted('transactions', ['id' => $transaction->id]);
        $this->actingAs($this->student)
            ->get(route('transactions.index', ['show_deleted' => 1]))
            ->assertOk()
            ->assertSee($transaction->description)
            ->assertSee('Restore');

        $this->post(route('transactions.restore', $transaction->id))
            ->assertRedirect(route('transactions.index'));
        $this->assertDatabaseHas('transactions', ['id' => $transaction->id, 'deleted_at' => null]);
    }

    public function test_due_recurring_transactions_are_generated_idempotently(): void
    {
        Carbon::setTestNow('2026-10-20 00:10:00');
        try {
            $template = Transaction::create([
                'user_id' => $this->student->id,
                'category_id' => $this->expenseCategory->id,
                'amount' => 25,
                'type' => 'expense',
                'description' => 'Monthly campus subscription',
                'date' => '2026-09-20',
                'is_recurring' => true,
                'recurrence_frequency' => 'monthly',
                'next_occurrence_at' => '2026-10-20',
            ]);

            $this->artisan('transactions:generate-recurring')->assertExitCode(0);
            $this->assertTrue(Transaction::where('recurrence_parent_id', $template->id)
                ->whereDate('date', '2026-10-20')
                ->where('description', 'Monthly campus subscription')
                ->exists());

            $this->artisan('transactions:generate-recurring')->assertExitCode(0);
            $this->assertSame(1, Transaction::where('recurrence_parent_id', $template->id)->count());
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_csv_import_requires_review_and_allows_manual_category_choice(): void
    {
        $this->actingAs($this->student);
        $file = UploadedFile::fake()->createWithContent(
            'transactions.csv',
            "Date,Type,Amount,Description,Category\n2026-09-01,expense,12.50,Campus Cafe,Food\n"
        );

        $preview = $this->post(route('transactions.import-csv'), ['csv_file' => $file])
            ->assertOk()
            ->assertViewIs('transactions.import-review')
            ->assertSee('Review imported transactions');
        $batchId = $preview->viewData('batchId');

        $this->assertDatabaseMissing('transactions', ['description' => 'Campus Cafe']);
        $this->post(route('transactions.import-csv.confirm'), [
            'batch_id' => $batchId,
            'categories' => [$this->expenseCategory->id],
        ])->assertRedirect(route('transactions.index'));
        $this->assertDatabaseHas('transactions', [
            'description' => 'Campus Cafe',
            'category_id' => $this->expenseCategory->id,
        ]);
    }

    public function test_report_email_and_tip_notes_are_available_to_the_owner(): void
    {
        Mail::fake();
        $transaction = $this->createTransaction();
        $tip = SavingTip::create([
            'user_id' => $this->student->id,
            'category_id' => $this->expenseCategory->id,
            'tip_title' => 'Plan campus meals',
            'tip_body' => 'Prepare meals in advance.',
            'potential_savings' => 15,
            'status' => 'active',
        ]);
        $insight = Insight::create([
            'user_id' => $this->student->id,
            'month' => now()->format('Y-m'),
            'summary_text' => 'Income exceeded expenses this month.',
            'tip_text' => 'Keep tracking your spending.',
            'generated_at' => now(),
        ]);

        $this->actingAs($this->student)
            ->post(route('reports.email'), [
                'email' => 'share@example.test',
                'month' => Carbon::parse($transaction->date)->format('Y-m'),
            ])->assertRedirect();
        Mail::assertSent(ShareSummaryMail::class);

        $this->post(route('saving-tips.note', $tip), ['user_note' => 'Try this next week.'])
            ->assertRedirect();
        $this->assertDatabaseHas('saving_tips', [
            'id' => $tip->id,
            'user_note' => 'Try this next week.',
        ]);
        $this->post(route('insights.note', $insight), ['user_note' => 'Review this at month end.'])
            ->assertRedirect();
        $this->post(route('insights.email', $insight), ['email' => 'share@example.test'])
            ->assertRedirect();
        Mail::assertSent(ShareSummaryMail::class);
    }

    public function test_outgoing_emails_share_responsive_campus_coin_branding(): void
    {
        $templates = [
            'emails.admin-password-reset' => ['user' => $this->student, 'adminName' => 'Admin User'],
            'emails.password-reset-code' => ['user' => $this->student, 'code' => '001234'],
            'emails.savings-goal-deadline' => [
                'user' => $this->student,
                'goalAmount' => 100,
                'savedAmount' => 80,
                'shortfall' => 20,
                'dueDate' => '2026-09-30',
                'notificationType' => 'reminder',
                'currencySymbol' => '$',
            ],
            'emails.share-summary' => ['title' => 'Monthly summary', 'summary' => 'Your financial summary.', 'details' => []],
            'emails.welcome-student' => ['user' => $this->student],
        ];

        foreach ($templates as $template => $data) {
            $html = view($template, $data)->render();
            $this->assertStringContainsString('Campus <span style="color:#60a5fa;">Coin</span>', $html);
            $this->assertStringContainsString('background-color:#071426', $html);
            $this->assertStringContainsString('max-width:600px', $html);
        }
    }

    public function test_report_filters_and_image_export_control_are_rendered(): void
    {
        $transaction = $this->createTransaction();
        Transaction::create([
            'user_id' => $this->student->id,
            'category_id' => $this->incomeCategory->id,
            'amount' => 50,
            'type' => 'income',
            'description' => 'Allowance',
            'date' => $transaction->date,
            'is_recurring' => false,
        ]);
        $this->actingAs($this->student)
            ->get(route('reports.index', [
                'month' => now()->format('Y-m'),
                'start_date' => now()->toDateString(),
                'end_date' => now()->toDateString(),
                'category_id' => $this->expenseCategory->id,
                'income_source_id' => $this->incomeCategory->id,
            ]))
            ->assertOk()
            ->assertSee('income_source_id', false)
            ->assertSee('report-filter-form', false)
            ->assertSee('report-filter-total', false)
            ->assertSee('id="export-report-image"', false)
            ->assertSee('50.00')
            ->assertSee('18.50');
    }

    public function test_admin_can_manage_templates_and_students_receive_active_templates(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.test',
            'password' => Hash::make('test-password'),
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.tip-templates.store'), [
                'title' => 'Plan affordable lunches',
                'body' => 'Prepare lunches at home to lower food spending.',
                'category_id' => $this->expenseCategory->id,
                'potential_savings' => 20,
            ])->assertRedirect(route('admin.tip-templates'));

        $this->assertDatabaseHas('saving_tip_templates', ['title' => 'Plan affordable lunches', 'is_active' => 1]);
        $this->actingAs($this->student)
            ->post(route('saving-tips.generate'))
            ->assertRedirect(route('saving-tips.index'));
        $this->assertDatabaseHas('saving_tips', [
            'user_id' => $this->student->id,
            'tip_title' => 'Plan affordable lunches',
            'source_template_id' => SavingTipTemplate::where('title', 'Plan affordable lunches')->value('id'),
        ]);
    }

    public function test_generating_tips_creates_a_personalized_suggestion_from_spending_without_an_ai_key(): void
    {
        config(['services.openrouter.key' => null]);
        Transaction::create([
            'user_id' => $this->student->id,
            'category_id' => $this->expenseCategory->id,
            'amount' => 200,
            'type' => 'expense',
            'description' => 'Campus groceries',
            'date' => now()->toDateString(),
            'is_recurring' => false,
        ]);

        $this->actingAs($this->student)
            ->post(route('saving-tips.generate'))
            ->assertRedirect(route('saving-tips.index'));

        $this->assertDatabaseHas('saving_tips', [
            'user_id' => $this->student->id,
            'category_id' => $this->expenseCategory->id,
            'tip_title' => 'Try a 10% reduction in Food',
            'potential_savings' => 20,
            'is_ai_generated' => false,
        ]);
        $this->get(route('saving-tips.index'))
            ->assertOk()
            ->assertSee('Generate My Tips')
            ->assertSee('Try a 10% reduction in Food')
            ->assertSee('You have spent 200.00 on Food this month');
    }

    public function test_ai_saving_tips_use_only_the_students_budget_totals_and_cap_savings(): void
    {
        $this->travelTo(Carbon::parse('2026-09-15 12:00:00'));
        config(['services.openrouter.key' => 'test-openrouter-key']);
        Http::fake([
            'https://openrouter.ai/api/v1/chat/completions' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => json_encode([
                            'tips' => [[
                                'category' => 'Food',
                                'title' => 'Plan meals',
                                'body' => 'Plan a few meals before shopping.',
                                'potential_savings' => 100,
                            ]],
                        ]),
                    ],
                ]],
            ]),
        ]);
        \App\Models\Budget::create([
            'user_id' => $this->student->id,
            'category_id' => $this->expenseCategory->id,
            'month' => now()->format('Y-m'),
            'limit_amount' => 100,
        ]);
        Transaction::create([
            'user_id' => $this->student->id,
            'category_id' => $this->expenseCategory->id,
            'amount' => 90,
            'type' => 'expense',
            'description' => 'Private cafe transaction description',
            'date' => now()->toDateString(),
            'is_recurring' => false,
        ]);
        $this->actingAs($this->student)
            ->post(route('saving-tips.generate'))
            ->assertRedirect(route('saving-tips.index'));

        Http::assertSent(function ($request) {
            return $request->url() === 'https://openrouter.ai/api/v1/chat/completions';
        });
        $tip = SavingTip::where('tip_title', 'Plan meals')->firstOrFail();
        $this->assertSame($this->student->id, $tip->user_id);
        $this->assertSame($this->expenseCategory->id, $tip->category_id);
        $this->assertTrue($tip->is_ai_generated);
        $this->assertSame('10.00', $tip->potential_savings);

        Http::assertSent(function ($request) {
            $payload = $request->data();
            $userData = json_decode($payload['messages'][1]['content'], true);

            return $request->url() === 'https://openrouter.ai/api/v1/chat/completions'
                && ($userData['categories'][0]['name'] ?? null) === 'Food'
                && ! isset($userData['name'])
                && ! isset($userData['email'])
                && ! str_contains($payload['messages'][1]['content'], 'Private cafe transaction description');
        });
    }

    public function test_ai_saving_tips_fall_back_to_deterministic_tips_when_provider_fails(): void
    {
        $this->travelTo(Carbon::parse('2026-09-15 12:00:00'));
        config(['services.openrouter.key' => 'test-openrouter-key']);
        Http::fake([
            'https://openrouter.ai/api/v1/chat/completions' => Http::response([], 503),
        ]);
        \App\Models\Budget::create([
            'user_id' => $this->student->id,
            'category_id' => $this->expenseCategory->id,
            'month' => now()->format('Y-m'),
            'limit_amount' => 100,
        ]);
        Transaction::create([
            'user_id' => $this->student->id,
            'category_id' => $this->expenseCategory->id,
            'amount' => 90,
            'type' => 'expense',
            'description' => 'Campus lunch',
            'date' => now()->toDateString(),
            'is_recurring' => false,
        ]);

        $this->actingAs($this->student)
            ->post(route('saving-tips.generate'))
            ->assertRedirect(route('saving-tips.index'))
            ->assertSessionHas('success', 'Your tips are below. They use your budgets, spending, savings goal, and available tips.');

        $this->assertDatabaseHas('saving_tips', [
            'user_id' => $this->student->id,
            'tip_title' => 'Review Food spending',
            'is_ai_generated' => false,
        ]);
    }

    public function test_admin_password_reset_sends_a_notice_without_emailing_the_new_password(): void
    {
        Mail::fake();
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.test',
            'password' => Hash::make('test-password'),
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.users.reset-password', $this->student), [
                'password' => 'new-test-password',
                'password_confirmation' => 'new-test-password',
            ])->assertRedirect();

        Mail::assertSent(AdminPasswordResetMail::class);
    }

    public function test_a_manual_csv_category_override_teaches_future_suggestions(): void
    {
        $alternativeCategory = Category::create([
            'name' => 'Campus Supplies',
            'type' => 'expense',
            'is_default' => true,
        ]);
        $this->actingAs($this->student);
        $file = UploadedFile::fake()->createWithContent(
            'transactions.csv',
            "Date,Type,Amount,Description,Category\n2026-09-01,expense,12.50,Campus Cafe,\n"
        );
        $preview = $this->post(route('transactions.import-csv'), ['csv_file' => $file])->assertOk();

        $this->post(route('transactions.import-csv.confirm'), [
            'batch_id' => $preview->viewData('batchId'),
            'categories' => [$alternativeCategory->id],
        ])->assertRedirect(route('transactions.index'));

        $this->post(route('ai.suggest-category'), [
            'description' => 'Campus Cafe charge',
            'type' => 'expense',
        ])->assertOk()->assertJson([
            'suggested_category_id' => $alternativeCategory->id,
            'source' => 'learned_correction',
        ]);
    }

    private function createTransaction(): Transaction
    {
        return Transaction::create([
            'user_id' => $this->student->id,
            'category_id' => $this->expenseCategory->id,
            'amount' => 18.50,
            'type' => 'expense',
            'description' => 'Campus lunch',
            'date' => now()->toDateString(),
            'is_recurring' => false,
        ]);
    }
}
