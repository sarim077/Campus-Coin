<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordResetCodeService
{
    private const CODE_EXPIRATION_MINUTES = 10;

    private const SEND_THROTTLE_SECONDS = 60;

    private const MAX_ATTEMPTS = 5;

    public function sendCode(string $email): bool
    {
        $issued = DB::transaction(function () use ($email): array {
            $user = User::query()->where('email', $email)->lockForUpdate()->first();

            if (! $user) {
                return ['registered' => false, 'user' => null, 'code' => null];
            }

            $record = DB::table('password_reset_codes')->where('email', $email)->lockForUpdate()->first();
            if ($record && $record->sent_at && Carbon::parse($record->sent_at)->gt(now()->subSeconds(self::SEND_THROTTLE_SECONDS))) {
                return ['registered' => true, 'user' => null, 'code' => null];
            }

            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $expiresAt = now()->addMinutes(self::CODE_EXPIRATION_MINUTES);

            DB::table('password_reset_codes')->updateOrInsert(
                ['email' => $email],
                [
                    'code_hash' => $this->hashCode($code),
                    'expires_at' => $expiresAt,
                    'attempts' => 0,
                    'sent_at' => now(),
                    'reset_token_hash' => null,
                    'reset_expires_at' => null,
                ]
            );

            return ['registered' => true, 'user' => $user, 'code' => $code];
        });

        if ($issued['user'] && $issued['code']) {
            $issued['user']->notify(new ResetPasswordNotification($issued['code']));
        }

        return $issued['registered'];
    }

    /**
     * @return array{token: string, expires_at: int}|null
     */
    public function verifyCode(string $email, string $code): ?array
    {
        return DB::transaction(function () use ($email, $code): ?array {
            $record = DB::table('password_reset_codes')->where('email', $email)->lockForUpdate()->first();

            if (! $record || ! $record->code_hash || ! $record->expires_at || now()->gte(Carbon::parse($record->expires_at)) || $record->attempts >= self::MAX_ATTEMPTS) {
                return null;
            }

            if (! hash_equals($record->code_hash, $this->hashCode($code))) {
                $attempts = $record->attempts + 1;
                DB::table('password_reset_codes')->where('email', $email)->update([
                    'attempts' => $attempts,
                    'code_hash' => $attempts >= self::MAX_ATTEMPTS ? null : $record->code_hash,
                ]);

                return null;
            }

            $token = Str::random(64);
            $expiresAt = now()->addMinutes(self::CODE_EXPIRATION_MINUTES);

            DB::table('password_reset_codes')->where('email', $email)->update([
                'code_hash' => null,
                'expires_at' => null,
                'reset_token_hash' => Hash::make($token),
                'reset_expires_at' => $expiresAt,
            ]);

            return ['token' => $token, 'expires_at' => $expiresAt->timestamp];
        });
    }

    public function resetPassword(string $email, string $token, string $password): bool
    {
        return DB::transaction(function () use ($email, $token, $password): bool {
            $record = DB::table('password_reset_codes')->where('email', $email)->lockForUpdate()->first();

            if (! $record || ! $record->reset_token_hash || ! $record->reset_expires_at || now()->gte(Carbon::parse($record->reset_expires_at)) || ! Hash::check($token, $record->reset_token_hash)) {
                return false;
            }

            $user = User::query()->where('email', $email)->lockForUpdate()->first();
            if (! $user) {
                return false;
            }

            $user->forceFill([
                'password' => Hash::make($password),
                'remember_token' => Str::random(60),
            ])->save();

            DB::table('password_reset_codes')->where('email', $email)->delete();

            return true;
        });
    }

    private function hashCode(string $code): string
    {
        return hash_hmac('sha256', $code, (string) config('app.key'));
    }
}
