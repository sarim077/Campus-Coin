<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InsightController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SavingsGoalController;
use App\Http\Controllers\SavingTipController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TwoFactorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Campus Coin Application
|--------------------------------------------------------------------------
*/

// Public Landing Page & Sitemap
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/admin/login', [AuthController::class, 'showAdminLogin'])->name('admin.login');
    Route::post('/admin/login', [AuthController::class, 'login'])->middleware('throttle:6,1')->name('admin.login.submit');
    Route::get('/two-factor-challenge', [TwoFactorController::class, 'challenge'])->name('two-factor.challenge');
    Route::post('/two-factor-challenge', [TwoFactorController::class, 'verifyChallenge'])->middleware('throttle:6,1')->name('two-factor.verify');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->middleware('throttle:3,1')->name('password.email');

    Route::get('/reset-password', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/verify-reset-code', [AuthController::class, 'verifyResetCode'])->middleware('throttle:6,1')->name('password.code.verify');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:6,1')->name('password.update');
});

// Authenticated User Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Profile Management
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::get('/profile/avatar', [AuthController::class, 'avatar'])->name('profile.avatar');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::delete('/profile/avatar', [AuthController::class, 'deleteAvatar'])->name('profile.avatar.delete');
    Route::get('/profile/two-factor', [TwoFactorController::class, 'setup'])->name('profile.two-factor');
    Route::post('/profile/two-factor/setup', [TwoFactorController::class, 'setup'])->name('profile.two-factor.setup');
    Route::post('/profile/two-factor/enable', [TwoFactorController::class, 'enable'])->name('profile.two-factor.enable');
    Route::post('/profile/two-factor/disable', [TwoFactorController::class, 'disable'])->name('profile.two-factor.disable');
    Route::post('/profile/two-factor/recovery-codes', [TwoFactorController::class, 'regenerateRecoveryCodes'])->name('profile.two-factor.recovery-codes');

    // AI Categorizer API (Web Authenticated AJAX)
    Route::post('/api/ai/suggest-category', [TransactionController::class, 'aiSuggestCategory'])->name('ai.suggest-category');

    // Student Dashboard & Panel Routes
    Route::middleware('student')->group(function () {
        Route::post('/locale/{locale}', [AuthController::class, 'setLocale'])->name('locale.switch');
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('student.dashboard');
        Route::put('/savings-goal', [SavingsGoalController::class, 'update'])->name('student.savings-goal.update');

        // Transactions CRUD & Import
        Route::resource('transactions', TransactionController::class);
        Route::post('/transactions/import-csv', [TransactionController::class, 'importCsv'])->name('transactions.import-csv');
        Route::post('/transactions/import-csv/confirm', [TransactionController::class, 'confirmImportCsv'])->name('transactions.import-csv.confirm');
        Route::post('/transactions/{transaction}/restore', [TransactionController::class, 'restore'])->name('transactions.restore');

        // Personal Categories Management
        Route::resource('categories', CategoryController::class)->except(['create', 'show', 'edit']);

        // Budget Goals & Alerts
        Route::get('/budgets', [BudgetController::class, 'index'])->name('budgets.index');
        Route::post('/budgets', [BudgetController::class, 'storeOrUpdate'])->name('budgets.store');
        Route::post('/budgets/categories/{category}/hide', [BudgetController::class, 'hideCategory'])->name('budgets.categories.hide');
        Route::post('/budgets/categories/{category}/show', [BudgetController::class, 'showCategory'])->name('budgets.categories.show');

        // Monthly Reports & Analytics
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/pdf', [ReportController::class, 'exportPdf'])->name('reports.pdf');
        Route::post('/reports/email', [ReportController::class, 'email'])->name('reports.email');

        // AI Spending Insights
        Route::get('/insights', [InsightController::class, 'index'])->name('insights.index');
        Route::post('/insights/generate', [InsightController::class, 'generate'])->name('insights.generate');
        Route::post('/insights/{insight}/bookmark', [InsightController::class, 'toggleBookmark'])->name('insights.bookmark');
        Route::post('/insights/{insight}/note', [InsightController::class, 'saveNote'])->name('insights.note');
        Route::post('/insights/{insight}/email', [InsightController::class, 'email'])->name('insights.email');

        // Personalized Saving Tips Engine
        Route::get('/saving-tips', [SavingTipController::class, 'index'])->name('saving-tips.index');
        Route::post('/saving-tips/generate', [SavingTipController::class, 'generateTips'])->name('saving-tips.generate');
        Route::post('/saving-tips/{tip}/status', [SavingTipController::class, 'updateStatus'])->name('saving-tips.status');
        Route::post('/saving-tips/{tip}/bookmark', [SavingTipController::class, 'toggleBookmark'])->name('saving-tips.bookmark');
        Route::post('/saving-tips/{tip}/note', [SavingTipController::class, 'saveNote'])->name('saving-tips.note');
        Route::post('/saving-tips/{tip}/email', [SavingTipController::class, 'email'])->name('saving-tips.email');
    });

    // Administrator Panel Routes
    Route::middleware('admin')->prefix('admin')->as('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
        Route::put('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');

        // Default Categories
        Route::get('/categories', [AdminController::class, 'categories'])->name('categories');
        Route::post('/categories', [AdminController::class, 'storeCategory'])->name('categories.store');
        Route::put('/categories/{category}', [AdminController::class, 'updateCategory'])->name('categories.update');
        Route::delete('/categories/{category}', [AdminController::class, 'destroyCategory'])->name('categories.destroy');

        // System Announcements
        Route::get('/announcements', [AdminController::class, 'announcements'])->name('announcements');
        Route::post('/announcements', [AdminController::class, 'storeAnnouncement'])->name('announcements.store');
        Route::post('/announcements/{announcement}/toggle', [AdminController::class, 'toggleAnnouncement'])->name('announcements.toggle');
        Route::delete('/announcements/{announcement}', [AdminController::class, 'destroyAnnouncement'])->name('announcements.destroy');

        // Saving tip templates
        Route::get('/tip-templates', [AdminController::class, 'tipTemplates'])->name('tip-templates');
        Route::post('/tip-templates', [AdminController::class, 'storeTipTemplate'])->name('tip-templates.store');
        Route::put('/tip-templates/{template}', [AdminController::class, 'updateTipTemplate'])->name('tip-templates.update');
        Route::delete('/tip-templates/{template}', [AdminController::class, 'destroyTipTemplate'])->name('tip-templates.destroy');

        // User Account Oversight
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::get('/users/reports/export', [AdminController::class, 'exportUserReports'])->name('users.reports.export');
        Route::post('/users/{user}/toggle', [AdminController::class, 'toggleUserStatus'])->name('users.toggle');
        Route::post('/users/{user}/reset-password', [AdminController::class, 'resetUserPassword'])->name('users.reset-password');
        Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
    });
});
