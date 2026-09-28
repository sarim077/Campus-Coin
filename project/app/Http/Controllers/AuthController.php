<?php

namespace App\Http\Controllers;

use App\Mail\WelcomeStudentMail;
use App\Models\User;
use App\Services\PasswordResetCodeService;
use App\Services\SavingsGoalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use RuntimeException;

class AuthController extends Controller
{
    public function setLocale(Request $request, string $locale)
    {
        abort_unless($request->user()?->isStudent(), 403);

        $supported = array_keys(config('languages.supported', []));
        if (! in_array($locale, $supported, true)) {
            return back()->withErrors(['locale' => 'Choose one of the available languages.']);
        }

        session(['locale' => $locale]);

        return back();
    }

    public function showLogin()
    {
        if (Auth::check()) {
            return Auth::user()->isAdmin() ? redirect()->route('admin.dashboard') : redirect()->route('student.dashboard');
        }

        return view('auth.login', ['adminLogin' => false]);
    }

    public function showAdminLogin()
    {
        if (Auth::check()) {
            return Auth::user()->isAdmin() ? redirect()->route('admin.dashboard') : redirect()->route('student.dashboard');
        }

        return view('auth.login', ['adminLogin' => true]);
    }

    public function login(Request $request)
    {
        $adminLogin = $request->routeIs('admin.login.submit');
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->remember)) {
            $request->session()->regenerate();
            $user = Auth::user();

            if ($user->is_disabled) {
                Auth::logout();

                return back()->withErrors(['email' => 'Your account has been disabled by administrator oversight.']);
            }

            if ($adminLogin && ! $user->isAdmin()) {
                Auth::logout();

                return back()->withErrors(['email' => 'This sign-in page is for administrator accounts.']);
            }

            if ($user->hasTwoFactorEnabled()) {
                $remember = $request->boolean('remember');
                Auth::logout();
                $request->session()->put([
                    'two_factor.pending_user_id' => $user->id,
                    'two_factor.remember' => $remember,
                ]);
                $request->session()->regenerate();

                return redirect()->route('two-factor.challenge');
            }

            if ($user->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'))->with('success', 'Welcome back, Administrator!');
            }

            return redirect()->intended(route('student.dashboard'))->with('success', 'Welcome back to Campus Coin!');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return Auth::user()->isAdmin() ? redirect()->route('admin.dashboard') : redirect()->route('student.dashboard');
        }

        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'region' => ['required', 'string', 'size:2', Rule::in(array_keys(config('regions', [])))],
            'academic_year' => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'student',
            'region' => $request->region,
            'academic_year' => $request->academic_year ?? 'Freshman Year',
            'monthly_allowance_baseline' => 0.00,
            'monthly_savings_goal' => 0.00,
            'savings_goal_due_date' => null,
            'savings_goal_started_at' => null,
        ]);

        Mail::to($user->email)->send(new WelcomeStudentMail($user));
        Auth::login($user);

        return redirect()->route('student.dashboard')->with('success', 'Account created successfully! Welcome to Campus Coin.');
    }

    public function profile()
    {
        $user = Auth::user();

        return view('profile.index', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $previousSavingsGoal = (float) $user->monthly_savings_goal;
        $previousGoalDueDate = $user->savings_goal_due_date?->toDateString();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'academic_year' => ['nullable', 'string', 'max:100'],
            'monthly_allowance_baseline' => ['nullable', 'numeric', 'min:0'],
            'monthly_savings_goal' => ['nullable', 'numeric', 'min:0'],
            'savings_goal_due_date' => [
                Rule::requiredIf(fn () => (float) $request->input('monthly_savings_goal', $user->monthly_savings_goal) > 0
                    && ((float) $request->input('monthly_savings_goal', $user->monthly_savings_goal) !== $previousSavingsGoal
                        || $request->filled('savings_goal_due_date'))),
                'nullable',
                'date',
                'after_or_equal:today',
            ],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $previousAvatarPath = $user->avatar_path;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store("avatars/{$user->id}", 'public');
            if ($avatarPath === false) {
                throw new RuntimeException('The profile photo could not be saved.');
            }
            $validated['avatar_path'] = $avatarPath;
        }

        $newSavingsGoal = (float) ($validated['monthly_savings_goal'] ?? $previousSavingsGoal);
        $newGoalDueDate = $newSavingsGoal > 0
            ? ($validated['savings_goal_due_date'] ?? $previousGoalDueDate)
            : null;
        $goalChanged = $newSavingsGoal !== $previousSavingsGoal || $newGoalDueDate !== $previousGoalDueDate;

        $user->update([
            'name' => $validated['name'],
            'academic_year' => $validated['academic_year'] ?? null,
            'monthly_allowance_baseline' => $validated['monthly_allowance_baseline'] ?? 0.00,
            'avatar_path' => $validated['avatar_path'] ?? $previousAvatarPath,
        ]);

        if ($goalChanged) {
            app(SavingsGoalService::class)->updateGoal($user, $newSavingsGoal, $newGoalDueDate);
        }

        if (filled($validated['password'] ?? null)) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        if (isset($validated['avatar_path']) && $this->ownsAvatarPath($user, $previousAvatarPath)) {
            if (Storage::disk('public')->exists($previousAvatarPath) && ! Storage::disk('public')->delete($previousAvatarPath)) {
                throw new RuntimeException('The previous profile photo could not be removed.');
            }
        }

        return back()->with('success', 'Profile updated successfully!');
    }

    public function avatar()
    {
        $user = Auth::user();
        $path = $user->avatar_path;

        abort_unless($this->ownsAvatarPath($user, $path) && Storage::disk('public')->exists($path), 404);

        return response()->file(Storage::disk('public')->path($path));
    }

    public function deleteAvatar()
    {
        $user = Auth::user();
        $path = $user->avatar_path;

        if ($this->ownsAvatarPath($user, $path) && Storage::disk('public')->exists($path) && ! Storage::disk('public')->delete($path)) {
            throw new RuntimeException('The profile photo could not be removed.');
        }

        $user->update(['avatar_path' => null]);

        return back()->with('success', 'Profile photo removed.');
    }

    private function ownsAvatarPath(User $user, ?string $path): bool
    {
        return $path !== null
            && dirname($path) === "avatars/{$user->id}"
            && basename($path) !== '';
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request, PasswordResetCodeService $passwordResetCodes)
    {
        $validated = $request->validate(['email' => ['required', 'email', 'max:255']]);

        if (! $passwordResetCodes->sendCode($validated['email'])) {
            return back()
                ->withErrors(['email' => 'This email address is not registered.'])
                ->withInput(['email' => $validated['email']]);
        }

        return redirect()
            ->route('password.reset')
            ->with('status', 'If an account matches that email address, a password reset code has been sent.')
            ->withInput(['email' => $validated['email']]);
    }

    public function showResetPassword(Request $request)
    {
        $email = $request->session()->get('password_reset.email');
        $expiresAt = $request->session()->get('password_reset.expires_at');
        $codeVerified = is_string($email)
            && is_int($expiresAt)
            && now()->timestamp < $expiresAt
            && is_string($request->session()->get('password_reset.token'));

        if (! $codeVerified) {
            $request->session()->forget('password_reset');
            $email = null;
        }

        return view('auth.reset-password', compact('email', 'codeVerified'));
    }

    public function verifyResetCode(Request $request, PasswordResetCodeService $passwordResetCodes)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'code' => ['required', 'digits:6'],
        ]);

        $verification = $passwordResetCodes->verifyCode($validated['email'], $validated['code']);
        if (! $verification) {
            return back()->withErrors(['code' => 'The verification code is invalid or expired.'])->withInput($request->only('email'));
        }

        $request->session()->regenerate();
        $request->session()->put([
            'password_reset.email' => $validated['email'],
            'password_reset.token' => $verification['token'],
            'password_reset.expires_at' => $verification['expires_at'],
        ]);

        return redirect()->route('password.reset')->with('status', 'Code verified. You can now choose a new password.');
    }

    public function resetPassword(Request $request, PasswordResetCodeService $passwordResetCodes)
    {
        $validated = $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $email = $request->session()->get('password_reset.email');
        $token = $request->session()->get('password_reset.token');
        $expiresAt = $request->session()->get('password_reset.expires_at');

        if (! is_string($email) || ! is_string($token) || ! is_int($expiresAt) || now()->timestamp >= $expiresAt || ! $passwordResetCodes->resetPassword($email, $token, $validated['password'])) {
            $request->session()->forget('password_reset');

            return redirect()->route('password.reset')->withErrors(['code' => 'The verification has expired. Request a new code.']);
        }

        $request->session()->forget('password_reset');
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Password reset successful! You can now log in with your new password.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Logged out successfully.');
    }
}
