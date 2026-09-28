<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentLanguagePreferenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_select_a_google_translate_language(): void
    {
        $student = $this->makeUser('student');

        $this->actingAs($student)
            ->post(route('locale.switch', 'tr'))
            ->assertRedirect();

        $this->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('Türkçe')
            ->assertSee('google_translate_element')
            ->assertSee('https://translate.google.com/translate_a/element.js', false)
            ->assertSee('googtrans=/en/tr', false);
    }

    public function test_language_selector_and_translation_are_student_only(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('language-menu', false)
            ->assertDontSee('google_translate_element', false);

        $admin = $this->makeUser('admin');
        $this->actingAs($admin)
            ->post(route('locale.switch', 'es'))
            ->assertForbidden();

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('id="google_translate_element"', false)
            ->assertDontSee('language-menu', false);
    }

    public function test_invalid_student_language_is_rejected(): void
    {
        $student = $this->makeUser('student');

        $this->actingAs($student)
            ->from(route('student.dashboard'))
            ->post(route('locale.switch', 'invalid-language'))
            ->assertRedirect(route('student.dashboard'))
            ->assertSessionHasErrors('locale');
    }

    private function makeUser(string $role): User
    {
        static $nextId = 1;
        $email = $role.$nextId.'@example.test';
        $nextId++;

        return User::create([
            'name' => ucfirst($role).' Test',
            'email' => $email,
            'password' => Hash::make('test-password'),
            'role' => $role,
        ]);
    }
}
