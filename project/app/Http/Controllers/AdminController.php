<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\Announcement;
use App\Models\SiteSetting;
use App\Models\SavingTipTemplate;
use App\Mail\AdminPasswordResetMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function settings()
    {
        return view('admin.settings', ['settings' => app('site.settings')]);
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'currency_symbol' => ['required', 'string', 'max:8'],
            'facebook_url' => ['nullable', 'url:http,https', 'max:255'],
            'instagram_url' => ['nullable', 'url:http,https', 'max:255'],
            'x_url' => ['nullable', 'url:http,https', 'max:255'],
            'linkedin_url' => ['nullable', 'url:http,https', 'max:255'],
            'youtube_url' => ['nullable', 'url:http,https', 'max:255'],
            'whatsapp_url' => ['nullable', 'url:http,https', 'max:255'],
        ]);

        $settings = SiteSetting::query()->findOrFail(1);
        $settings->update($validated);
        app()->forgetInstance('site.settings');

        return redirect()->route('admin.settings')->with('success', 'Site settings updated.');
    }

    public function dashboard()
    {
        $totalUsers = User::where('role', 'student')->count();
        $activeUsers = User::where('role', 'student')->where('is_disabled', false)->count();
        $disabledUsers = $totalUsers - $activeUsers;

        return view('admin.dashboard', compact('totalUsers', 'activeUsers', 'disabledUsers'));
    }

    // Default Category Management
    public function categories()
    {
        $incomeCategories = Category::where('is_default', true)->where('type', 'income')->get();
        $expenseCategories = Category::where('is_default', true)->where('type', 'expense')->get();

        return view('admin.categories', compact('incomeCategories', 'expenseCategories'));
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:income,expense',
            'icon' => 'nullable|string|max:100',
        ]);

        Category::create([
            'name' => $request->name,
            'type' => $request->type,
            'is_default' => true,
            'user_id' => null,
            'icon' => $request->icon ?? 'fas fa-tags',
        ]);

        return redirect()->route('admin.categories')->with('success', 'Default category added across system!');
    }

    public function updateCategory(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:income,expense',
            'icon' => 'nullable|string|max:100',
        ]);

        $category->update([
            'name' => $request->name,
            'type' => $request->type,
            'icon' => $request->icon ?? $category->icon,
        ]);

        return redirect()->route('admin.categories')->with('success', 'Default category updated.');
    }

    public function destroyCategory(Category $category)
    {
        $category->delete();
        return redirect()->route('admin.categories')->with('success', 'Default category removed.');
    }

    // System-wide Announcements
    public function announcements()
    {
        $announcements = Announcement::with('creator')->orderByDesc('created_at')->get();
        return view('admin.announcements', compact('announcements'));
    }

    public function storeAnnouncement(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:5000',
        ]);

        Announcement::create([
            'title' => $request->title,
            'content' => $request->content,
            'is_active' => true,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.announcements')->with('success', 'System announcement published.');
    }

    public function toggleAnnouncement(Announcement $announcement)
    {
        $announcement->update(['is_active' => !$announcement->is_active]);
        return back()->with('success', 'Announcement status updated.');
    }

    public function destroyAnnouncement(Announcement $announcement)
    {
        $announcement->delete();
        return back()->with('success', 'Announcement removed.');
    }

    // User Accounts Management
    public function users(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', 'in:all,student,admin'],
            'status' => ['nullable', 'in:all,active,disabled'],
            'month' => ['nullable', 'date_format:Y-m'],
        ]);

        $month = $filters['month'] ?? Carbon::now()->format('Y-m');
        $monthStart = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        $users = User::query()
            ->when($filters['q'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('academic_year', 'like', "%{$search}%");
                });
            })
            ->when(($filters['role'] ?? 'all') !== 'all', fn ($query) => $query->where('role', $filters['role']))
            ->when(($filters['status'] ?? 'all') !== 'all', fn ($query) => $query->where('is_disabled', $filters['status'] === 'disabled'))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $studentCount = User::where('role', 'student')->count();
        $activeStudentCount = User::where('role', 'student')->where('is_disabled', false)->count();
        $monthTransactionCount = Transaction::whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->whereHas('user', fn ($query) => $query->where('role', 'student'))
            ->count();

        return view('admin.users', compact('users', 'studentCount', 'activeStudentCount', 'monthTransactionCount', 'month'));
    }

    public function exportUserReports(Request $request)
    {
        $validated = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
        ]);

        $month = $validated['month'] ?? Carbon::now()->format('Y-m');
        $monthStart = Carbon::createFromFormat('Y-m', $month)->startOfMonth()->toDateString();
        $monthEnd = Carbon::createFromFormat('Y-m', $month)->endOfMonth()->toDateString();
        $defaultCurrencySymbol = app('site.settings')->currency_symbol ?: '$';
        $regions = config('regions', []);

        return response()->streamDownload(function () use ($monthStart, $monthEnd, $month, $defaultCurrencySymbol, $regions) {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");

            $safe = static function ($value): string {
                $value = (string) $value;
                return preg_match('/^[\s]*[=+\-@]/u', $value) ? "'{$value}" : $value;
            };

            fputcsv($output, ['Campus Coin - All Student Monthly Reports', $month]);
            fputcsv($output, []);
            fputcsv($output, ['STUDENT SUMMARIES']);
            fputcsv($output, ['User ID', 'Name', 'Email', 'Academic year', 'Status', 'Monthly allowance', 'Monthly savings goal', 'Income', 'Expenses', 'Net', 'Transactions', 'Currency']);

            $monthlyTotals = DB::table('transactions')
                ->select(
                    'user_id',
                    DB::raw("SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income"),
                    DB::raw("SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expenses"),
                    DB::raw('COUNT(*) as transaction_count')
                )
                ->whereBetween('date', [$monthStart, $monthEnd])
                ->groupBy('user_id');

            DB::table('users')
                ->leftJoinSub($monthlyTotals, 'monthly', fn ($join) => $join->on('users.id', '=', 'monthly.user_id'))
                ->where('users.role', 'student')
                ->select(
                    'users.id',
                    'users.name',
                    'users.email',
                    'users.academic_year',
                    'users.region',
                    'users.is_disabled',
                    'users.monthly_allowance_baseline',
                    'users.monthly_savings_goal',
                    'monthly.income',
                    'monthly.expenses',
                    'monthly.transaction_count'
                )
                ->orderBy('users.id')
                ->chunk(500, function ($students) use ($output, $safe, $defaultCurrencySymbol, $regions) {
                    foreach ($students as $student) {
                        $income = (float) ($student->income ?? 0);
                        $expenses = (float) ($student->expenses ?? 0);
                        $currencySymbol = $regions[$student->region]['symbol'] ?? $defaultCurrencySymbol;

                        fputcsv($output, [
                            $student->id,
                            $safe($student->name),
                            $safe($student->email),
                            $safe($student->academic_year ?? ''),
                            $student->is_disabled ? 'Disabled' : 'Active',
                            number_format((float) $student->monthly_allowance_baseline, 2, '.', ''),
                            number_format((float) $student->monthly_savings_goal, 2, '.', ''),
                            number_format($income, 2, '.', ''),
                            number_format($expenses, 2, '.', ''),
                            number_format($income - $expenses, 2, '.', ''),
                            (int) ($student->transaction_count ?? 0),
                            $safe($currencySymbol),
                        ]);
                    }
                });

            fputcsv($output, []);
            fputcsv($output, ['TRANSACTION DETAILS']);
            fputcsv($output, ['Transaction ID', 'User ID', 'Student name', 'Student email', 'Date', 'Type', 'Description', 'Category', 'Amount', 'Currency']);

            DB::table('transactions')
                ->join('users', 'users.id', '=', 'transactions.user_id')
                ->leftJoin('categories', 'categories.id', '=', 'transactions.category_id')
                ->where('users.role', 'student')
                ->whereBetween('transactions.date', [$monthStart, $monthEnd])
                ->select(
                    'transactions.id',
                    'users.id as user_id',
                    'users.name as student_name',
                    'users.email as student_email',
                    'users.region',
                    'transactions.date',
                    'transactions.type',
                    'transactions.description',
                    'categories.name as category_name',
                    'transactions.amount'
                )
                ->orderBy('transactions.id')
                ->chunkById(500, function ($transactions) use ($output, $safe, $defaultCurrencySymbol, $regions) {
                    foreach ($transactions as $transaction) {
                        $currencySymbol = $regions[$transaction->region]['symbol'] ?? $defaultCurrencySymbol;
                        fputcsv($output, [
                            $transaction->id,
                            $transaction->user_id,
                            $safe($transaction->student_name),
                            $safe($transaction->student_email),
                            $transaction->date,
                            ucfirst($transaction->type),
                            $safe($transaction->description),
                            $safe($transaction->category_name ?? 'Uncategorized'),
                            number_format((float) $transaction->amount, 2, '.', ''),
                            $safe($currencySymbol),
                        ]);
                    }
                }, 'transactions.id', 'id');

            fclose($output);
        }, "campus-coin-all-student-reports-{$month}.csv", [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function toggleUserStatus(User $user)
    {
        if ($user->isAdmin()) {
            return back()->with('error', 'Administrator status cannot be toggled.');
        }

        $user->update(['is_disabled' => !$user->is_disabled]);
        return back()->with('success', $user->is_disabled ? "User account {$user->name} has been disabled." : "User account {$user->name} has been enabled.");
    }

    public function destroyUser(Request $request, User $user)
    {
        if ($user->isAdmin() || $request->user()->is($user)) {
            return back()->with('error', 'Administrator accounts cannot be deleted.');
        }

        $name = $user->name;
        $user->delete();

        return back()->with('success', "Student account {$name} and its associated data have been permanently deleted.");
    }

    public function resetUserPassword(Request $request, User $user)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user->update(['password' => Hash::make($request->password)]);
        Mail::to($user->email)->send(new AdminPasswordResetMail($user, $request->user()->name));

        return back()->with('success', "Password for user {$user->name} reset successfully!");
    }

    public function tipTemplates()
    {
        $templates = SavingTipTemplate::with('category')->orderByDesc('updated_at')->get();
        $categories = Category::where('type', 'expense')
            ->where('is_default', true)
            ->orderBy('name')
            ->get();

        return view('admin.tip-templates', compact('templates', 'categories'));
    }

    public function storeTipTemplate(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'potential_savings' => ['required', 'numeric', 'min:0'],
        ]);

        if (!empty($validated['category_id'])) {
            abort_unless(Category::whereKey($validated['category_id'])->where('type', 'expense')->exists(), 422);
        }

        SavingTipTemplate::create($validated + [
            'is_active' => true,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.tip-templates')->with('success', 'Saving-tip template created.');
    }

    public function updateTipTemplate(Request $request, SavingTipTemplate $template)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'potential_savings' => ['required', 'numeric', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);

        if (!empty($validated['category_id'])) {
            abort_unless(Category::whereKey($validated['category_id'])->where('type', 'expense')->exists(), 422);
        }

        $template->update($validated);

        return redirect()->route('admin.tip-templates')->with('success', 'Saving-tip template updated.');
    }

    public function destroyTipTemplate(SavingTipTemplate $template)
    {
        $template->delete();

        return redirect()->route('admin.tip-templates')->with('success', 'Saving-tip template removed.');
    }
}
