<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PanelTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $student;

    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->student = User::create([
            'name' => 'Student User',
            'email' => 'student@test.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'monthly_allowance_baseline' => 500,
            'monthly_savings_goal' => 100,
        ]);

        $this->category = Category::create([
            'name' => 'Food',
            'type' => 'expense',
            'is_default' => true,
        ]);
    }

    public function test_student_dashboard_access(): void
    {
        $this->actingAs($this->student)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('quick-transaction-modal', false)
            ->assertSee('Add income or spending to your monthly activity.')
            ->assertSee('id="quickAmount"', false)
            ->assertSee('id="quickDate"', false)
            ->assertSee('id="typeExpense"', false)
            ->assertSee('id="typeIncome"', false)
            ->assertSee('quick-recurring-option', false)
            ->assertSee('.quick-transaction-modal form { display: flex;', false)
            ->assertSee('overflow-y: auto;', false)
            ->assertSee('height: calc(100% - 1.1rem); min-height: 0;', false);
    }

    public function test_student_financial_reports_hide_the_breadcrumb_bar(): void
    {
        $this->actingAs($this->student)
            ->get(route('reports.index'))
            ->assertOk()
            ->assertSee('Monthly Reports & Analytics', false)
            ->assertDontSee('aria-label="breadcrumb"', false);
    }

    public function test_admin_dashboard_access(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $response->assertOk()
            ->assertSee('admin-panel', false)
            ->assertSee('Admin workspace')
            ->assertSee('admin-sidebar', false)
            ->assertSee('aria-label="Admin workspace navigation"', false)
            ->assertDontSee('Administrator Portal')
            ->assertSee('portal-footer-main', false)
            ->assertSee('aria-label="Campus Coin social links"', false)
            ->assertDontSee('fas fa-font')
            ->assertSee(route('admin.users'))
            ->assertSee(route('admin.settings'));
    }

    public function test_admin_can_sign_in_through_the_login_page(): void
    {
        $response = $this->post(route('login'), [
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->admin);
    }

    public function test_students_and_admins_can_upload_view_and_remove_their_own_profile_photo(): void
    {
        Storage::fake('public');

        foreach ([$this->student, $this->admin] as $user) {
            $this->actingAs($user)
                ->from(route('profile'))
                ->put(route('profile.update'), [
                    'name' => $user->name,
                    'avatar' => $this->fakeProfilePhoto(),
                ])
                ->assertRedirect(route('profile'));

            $user->refresh();
            $this->assertNotNull($user->avatar_path);
            $this->assertStringStartsWith("avatars/{$user->id}/", $user->avatar_path);
            Storage::disk('public')->assertExists($user->avatar_path);

            $this->get(route('profile.avatar'))->assertOk();
            $this->get(route('profile'))
                ->assertOk()
                ->assertSee(route('profile.avatar'), false)
                ->assertSee('name="avatar"', false)
                ->assertSee('Remove profile photo');

            $previousAvatarPath = $user->avatar_path;
            $this->from(route('profile'))
                ->put(route('profile.update'), [
                    'name' => $user->name,
                    'avatar' => $this->fakeProfilePhoto(),
                ])
                ->assertRedirect(route('profile'));

            $user->refresh();
            $this->assertNotSame($previousAvatarPath, $user->avatar_path);
            Storage::disk('public')->assertMissing($previousAvatarPath);
            Storage::disk('public')->assertExists($user->avatar_path);

            $currentAvatarPath = $user->avatar_path;
            $this->from(route('profile'))
                ->delete(route('profile.avatar.delete'))
                ->assertRedirect(route('profile'));

            $user->refresh();
            $this->assertNull($user->avatar_path);
            Storage::disk('public')->assertMissing($currentAvatarPath);
        }
    }

    public function test_invalid_profile_photo_is_rejected_without_saving_profile_changes(): void
    {
        Storage::fake('public');

        $this->actingAs($this->student)
            ->from(route('profile'))
            ->put(route('profile.update'), [
                'name' => 'Name should not be changed',
                'avatar' => UploadedFile::fake()->create('notes.txt', 1, 'text/plain'),
            ])
            ->assertRedirect(route('profile'))
            ->assertSessionHasErrors('avatar');

        $this->assertDatabaseHas('users', [
            'id' => $this->student->id,
            'name' => 'Student User',
            'avatar_path' => null,
        ]);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    private function fakeProfilePhoto(): UploadedFile
    {
        $image = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/u/kAAAAASUVORK5CYII=');

        return UploadedFile::fake()->createWithContent('profile.png', $image);
    }

    public function test_guest_login_page_does_not_authenticate_or_preselect_remember_me(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('name="remember" id="remember" value="1"', false)
            ->assertDontSee('name="remember" id="remember" value="1" checked', false);

        $this->assertGuest();
    }

    public function test_admin_brand_and_homepage_account_links_open_admin_overview(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('class="navbar-brand d-flex align-items-center gap-3" href="'.route('admin.dashboard').'"', false);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Admin overview')
            ->assertSee('href="'.route('admin.dashboard').'"', false);
    }

    public function test_student_cannot_register_with_an_email_already_in_use(): void
    {
        $existingStudent = User::create([
            'name' => 'Existing Student',
            'email' => 'duplicate@test.com',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);

        $response = $this->from(route('register'))->post(route('register'), [
            'name' => 'Another Student',
            'email' => 'duplicate@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('register'))
            ->assertSessionHasErrors('email');
        $this->assertDatabaseCount('users', 3);
        $this->assertDatabaseHas('users', ['id' => $existingStudent->id, 'email' => 'duplicate@test.com']);
        $this->assertGuest();
    }

    public function test_student_cannot_access_admin_panel(): void
    {
        $response = $this->actingAs($this->student)->get(route('admin.dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_student_cannot_access_admin_site_settings(): void
    {
        $this->actingAs($this->student)
            ->get(route('admin.settings'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_update_site_currency_and_social_links(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.settings'))
            ->assertOk()
            ->assertSee('Site settings')
            ->assertSee('Currency symbol');

        $this->actingAs($this->admin)
            ->put(route('admin.settings.update'), [
                'currency_symbol' => '€',
                'facebook_url' => 'https://facebook.com/campuscoin',
                'instagram_url' => '',
                'x_url' => '',
                'linkedin_url' => '',
                'youtube_url' => '',
                'whatsapp_url' => '',
            ])
            ->assertRedirect(route('admin.settings'));

        $this->assertDatabaseHas('site_settings', [
            'id' => 1,
            'currency_symbol' => '€',
            'facebook_url' => 'https://facebook.com/campuscoin',
        ]);

        $this->actingAs($this->student)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('€');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('https://facebook.com/campuscoin');
    }

    public function test_admin_site_settings_reject_non_http_social_urls(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.settings'))
            ->put(route('admin.settings.update'), [
                'currency_symbol' => '$',
                'facebook_url' => 'javascript:alert(1)',
            ])
            ->assertRedirect(route('admin.settings'))
            ->assertSessionHasErrors('facebook_url');
    }

    public function test_student_can_create_transaction(): void
    {
        $response = $this->actingAs($this->student)->post(route('transactions.store'), [
            'type' => 'expense',
            'amount' => 25.50,
            'category_id' => $this->category->id,
            'description' => 'Campus Lunch',
            'date' => now()->toDateString(),
        ]);

        $response->assertRedirect(route('transactions.index'));
        $this->assertDatabaseHas('transactions', [
            'description' => 'Campus Lunch',
            'amount' => 25.50,
        ]);
    }

    public function test_profile_password_fields_are_empty_and_clear(): void
    {
        $this->actingAs($this->student)
            ->get(route('profile'))
            ->assertOk()
            ->assertSee('placeholder="Enter a new password"', false)
            ->assertSee('placeholder="Re-enter the new password"', false)
            ->assertSee('autocomplete="new-password"', false)
            ->assertDontSee('placeholder="••••••••"', false);
    }

    public function test_invalid_profile_password_does_not_partially_save_profile_changes(): void
    {
        $response = $this->actingAs($this->student)
            ->from(route('profile'))
            ->put(route('profile.update'), [
                'name' => 'Changed Name',
                'academic_year' => $this->student->academic_year,
                'monthly_allowance_baseline' => $this->student->monthly_allowance_baseline,
                'monthly_savings_goal' => $this->student->monthly_savings_goal,
                'savings_goal_due_date' => now()->addDays(10)->toDateString(),
                'password' => 'new-password',
                'password_confirmation' => 'different-password',
            ]);

        $response->assertRedirect(route('profile'))
            ->assertSessionHasErrors('password');
        $this->assertDatabaseHas('users', [
            'id' => $this->student->id,
            'name' => 'Student User',
        ]);
    }

    public function test_admin_can_add_category(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.categories'))
            ->assertOk()
            ->assertSee('System default categories')
            ->assertSee('Income categories')
            ->assertSee('Expense categories')
            ->assertSee('Food')
            ->assertSee('categoryMethod');

        $response = $this->post(route('admin.categories.store'), [
            'name' => 'Tuition',
            'type' => 'expense',
            'icon' => 'fas fa-book',
        ]);

        $response->assertRedirect(route('admin.categories'));
        $this->assertDatabaseHas('categories', [
            'name' => 'Tuition',
            'is_default' => true,
        ]);
    }

    public function test_admin_can_edit_a_system_category(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.categories.update', $this->category), [
                'name' => 'Campus Dining',
                'type' => 'expense',
                'icon' => 'fas fa-utensils',
            ])
            ->assertRedirect(route('admin.categories'));

        $this->assertDatabaseHas('categories', [
            'id' => $this->category->id,
            'name' => 'Campus Dining',
            'icon' => 'fas fa-utensils',
        ]);
    }

    public function test_admin_can_toggle_user_status(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.toggle', $this->student->id));
        $response->assertStatus(302);
        $this->assertTrue($this->student->fresh()->is_disabled);
    }

    public function test_admin_can_delete_a_student_and_their_transactions(): void
    {
        $transaction = Transaction::create([
            'user_id' => $this->student->id,
            'category_id' => $this->category->id,
            'amount' => 25,
            'type' => 'expense',
            'description' => 'Campus lunch',
            'date' => now()->toDateString(),
        ]);

        $this->actingAs($this->admin)
            ->delete(route('admin.users.destroy', $this->student))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $this->student->id]);
        $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
    }

    public function test_admin_cannot_delete_an_administrator_and_student_cannot_delete_users(): void
    {
        $this->actingAs($this->admin)
            ->delete(route('admin.users.destroy', $this->admin))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);

        $this->actingAs($this->student)
            ->delete(route('admin.users.destroy', $this->admin))
            ->assertRedirect(route('login'));

        $this->assertDatabaseHas('users', ['id' => $this->student->id]);
    }

    public function test_admin_user_directory_is_searchable_and_includes_account_controls(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.users', ['q' => 'student@test.com', 'role' => 'student']))
            ->assertOk()
            ->assertSee('Student accounts')
            ->assertSee('Academic year')
            ->assertSee('Download all reports')
            ->assertSee('Delete student account')
            ->assertSee('id="deleteUserModal"', false)
            ->assertSee('Delete permanently')
            ->assertDontSee('onsubmit="return confirm(', false)
            ->assertSee('student@test.com')
            ->assertSee('1 matching account');
    }

    public function test_admin_can_download_all_student_monthly_reports(): void
    {
        $month = now()->format('Y-m');
        $otherStudent = User::create([
            'name' => 'No Activity Student',
            'email' => 'quiet@test.com',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);

        Transaction::create([
            'user_id' => $this->student->id,
            'category_id' => $this->category->id,
            'amount' => 25.50,
            'type' => 'expense',
            'description' => '=SUM(A1:A2)',
            'date' => now()->startOfMonth()->addDays(2)->toDateString(),
        ]);

        Transaction::create([
            'user_id' => $this->student->id,
            'category_id' => $this->category->id,
            'amount' => 100,
            'type' => 'income',
            'description' => 'Monthly allowance',
            'date' => now()->startOfMonth()->addDays(3)->toDateString(),
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.reports.export', ['month' => $month]));

        $response->assertOk()
            ->assertDownload("campus-coin-all-student-reports-{$month}.csv");
        $csv = $response->streamedContent();

        $this->assertStringContainsString('STUDENT SUMMARIES', $csv);
        $this->assertStringContainsString('TRANSACTION DETAILS', $csv);
        $this->assertStringContainsString('Student User', $csv);
        $this->assertStringContainsString('No Activity Student', $csv);
        $this->assertStringContainsString("'=SUM(A1:A2)", $csv);
        $this->assertStringContainsString('100.00', $csv);
        $this->assertStringContainsString('25.50', $csv);
        $this->assertStringNotContainsString('Admin User', $csv);
        $this->assertDatabaseHas('users', ['id' => $otherStudent->id]);
    }

    public function test_student_cannot_download_all_student_reports(): void
    {
        $this->actingAs($this->student)
            ->get(route('admin.users.reports.export'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_announcements_page_shows_broadcast_status_and_create_form(): void
    {
        Announcement::create([
            'title' => 'Campus Update',
            'content' => 'A helpful update for students.',
            'is_active' => true,
            'created_by' => $this->admin->id,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.announcements'))
            ->assertOk()
            ->assertSee('System announcements')
            ->assertSee('Announcement library')
            ->assertSee('Live to students')
            ->assertSee('New announcement')
            ->assertSee('Campus Update')
            ->assertDontSee('Tip Templates');
    }
}
