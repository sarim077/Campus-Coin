<?php

namespace App\Providers;

use App\Models\SiteSetting;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('site.settings', function () {
            return SiteSetting::query()->find(1) ?? new SiteSetting(['currency_symbol' => '$']);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            $siteSettings = app('site.settings');
            $user = auth()->user();
            $currencySymbol = $siteSettings->currency_symbol ?: '$';
            if ($user?->isStudent()) {
                $currencySymbol = $user->currency_symbol ?: $currencySymbol;
            }
            $view->with('siteSettings', $siteSettings);
            $view->with('regions', config('regions', []));
            $view->with('currencySymbol', $currencySymbol);
            $view->with('accountDestination', $user
                ? route($user->isAdmin() ? 'admin.dashboard' : 'student.dashboard')
                : route('login'));
            $view->with('accountDestinationLabel', $user
                ? ($user->isAdmin() ? 'Admin overview' : 'Dashboard')
                : 'Log in');
        });
    }
}
