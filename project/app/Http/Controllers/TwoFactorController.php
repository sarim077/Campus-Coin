<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TwoFactorController extends Controller
{
    public function challenge(Request $request)
    {
        $user = User::find($request->session()->get('two_factor.pending_user_id'));
        abort_unless($user && $user->hasTwoFactorEnabled() && !$user->is_disabled, 403);

        return view('auth.two-factor-challenge');
    }

    public function verifyChallenge(Request $request)
    {
        $request->validate(['code' => ['required', 'string', 'max:32']]);
        $user = User::find($request->session()->get('two_factor.pending_user_id'));
        abort_unless($user && $user->hasTwoFactorEnabled() && !$user->is_disabled, 403);

        if (!$this->verifyCodeOrConsumeRecoveryCode($user, $request->string('code')->toString())) {
            return back()->withErrors(['code' => 'The authenticator code or recovery code is invalid.']);
        }

        $remember = (bool) $request->session()->pull('two_factor.remember', false);
        $request->session()->forget('two_factor.pending_user_id');
        Auth::login($user, $remember);
        $request->session()->regenerate();

        return redirect()->intended($user->isAdmin()
            ? route('admin.dashboard')
            : route('student.dashboard'));
    }

    public function setup(Request $request)
    {
        $user = $request->user();

        if ($user->hasTwoFactorEnabled()) {
            return view('profile.two-factor', ['user' => $user, 'secret' => null, 'qrCode' => null]);
        }

        $secret = $request->session()->get('two_factor.setup_secret');
        if (!$secret) {
            $secret = (new Google2FA())->generateSecretKey();
            $request->session()->put('two_factor.setup_secret', $secret);
        }

        $uri = (new Google2FA())->getQRCodeUrl('Campus Coin', $user->email, $secret);
        $qrCode = QrCode::format('svg')->size(220)->margin(1)->generate($uri);

        return view('profile.two-factor', compact('user', 'secret', 'qrCode'));
    }

    public function enable(Request $request)
    {
        $request->validate(['code' => ['required', 'digits:6']]);
        $secret = $request->session()->get('two_factor.setup_secret');
        abort_unless($secret, 419, 'Your authenticator setup expired. Start setup again.');

        if (!(new Google2FA())->verifyKey($secret, $request->input('code'), 1)) {
            return back()->withErrors(['code' => 'That code did not verify. Check the authenticator and try again.']);
        }

        $recoveryCodes = $this->generateRecoveryCodes();
        $request->user()->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => array_map(fn (string $code) => Hash::make($code), $recoveryCodes),
            'two_factor_confirmed_at' => now(),
        ])->save();
        $request->session()->forget('two_factor.setup_secret');

        return redirect()->route('profile.two-factor')
            ->with('recovery_codes', $recoveryCodes)
            ->with('success', 'Two-factor authentication is enabled. Save your recovery codes now.');
    }

    public function disable(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
            'code' => ['required', 'string', 'max:32'],
        ]);

        $user = $request->user();
        abort_unless($user->hasTwoFactorEnabled(), 404);

        if (!$this->verifyCodeOrConsumeRecoveryCode($user, $request->string('code')->toString())) {
            return back()->withErrors(['code' => 'The authenticator code or recovery code is invalid.']);
        }

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        return redirect()->route('profile.two-factor')->with('success', 'Two-factor authentication has been disabled.');
    }

    public function regenerateRecoveryCodes(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
            'code' => ['required', 'string', 'max:32'],
        ]);

        $user = $request->user();
        abort_unless($user->hasTwoFactorEnabled(), 404);

        if (!$this->verifyCodeOrConsumeRecoveryCode($user, $request->string('code')->toString())) {
            return back()->withErrors(['code' => 'The authenticator code or recovery code is invalid.']);
        }

        $recoveryCodes = $this->generateRecoveryCodes();
        $user->forceFill([
            'two_factor_recovery_codes' => array_map(fn (string $code) => Hash::make($code), $recoveryCodes),
        ])->save();

        return redirect()->route('profile.two-factor')
            ->with('recovery_codes', $recoveryCodes)
            ->with('success', 'New recovery codes generated. Previously issued codes no longer work.');
    }

    private function verifyCodeOrConsumeRecoveryCode(User $user, string $code): bool
    {
        if ($user->two_factor_secret && (new Google2FA())->verifyKey($user->two_factor_secret, $code, 1)) {
            return true;
        }

        $normalized = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $code) ?? '');
        return DB::transaction(function () use ($user, $normalized): bool {
            $lockedUser = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $recoveryCodes = $lockedUser->two_factor_recovery_codes ?? [];

            foreach ($recoveryCodes as $index => $hashedCode) {
                if (Hash::check($normalized, $hashedCode)) {
                    unset($recoveryCodes[$index]);
                    $lockedUser->forceFill(['two_factor_recovery_codes' => array_values($recoveryCodes)])->save();

                    return true;
                }
            }

            return false;
        });
    }

    private function generateRecoveryCodes(): array
    {
        return array_map(
            fn () => strtoupper(Str::random(5) . '-' . Str::random(5)),
            range(1, 10)
        );
    }
}
