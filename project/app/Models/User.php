<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'region',
        'academic_year',
        'monthly_allowance_baseline',
        'monthly_savings_goal',
        'savings_goal_due_date',
        'savings_goal_started_at',
        'savings_goal_revision',
        'is_disabled',
        'avatar_path',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'monthly_allowance_baseline' => 'decimal:2',
        'monthly_savings_goal' => 'decimal:2',
        'savings_goal_due_date' => 'date',
        'savings_goal_started_at' => 'date',
        'is_disabled' => 'boolean',
        'two_factor_secret' => 'encrypted',
        'two_factor_recovery_codes' => 'encrypted:array',
        'two_factor_confirmed_at' => 'datetime',
    ];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function getCurrencySymbolAttribute(): ?string
    {
        return $this->region
            ? config("regions.{$this->region}.symbol")
            : null;
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_secret !== null && $this->two_factor_confirmed_at !== null;
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new \App\Notifications\ResetPasswordNotification($token));
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function budgets()
    {
        return $this->hasMany(Budget::class);
    }

    public function insights()
    {
        return $this->hasMany(Insight::class);
    }

    public function savingTips()
    {
        return $this->hasMany(SavingTip::class);
    }
}
