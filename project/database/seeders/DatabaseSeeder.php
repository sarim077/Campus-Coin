<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\Budget;
use App\Models\Insight;
use App\Models\SavingTip;
use App\Models\Announcement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Default Categories
        $incomeCategories = [
            'Allowance' => 'fas fa-hand-holding-usd',
            'Part-time Job' => 'fas fa-briefcase',
            'Scholarship' => 'fas fa-graduation-cap',
            'Gift' => 'fas fa-gift',
            'Other Income' => 'fas fa-coins'
        ];

        foreach ($incomeCategories as $name => $icon) {
            Category::firstOrCreate(
                ['name' => $name, 'type' => 'income'],
                ['is_default' => true, 'icon' => $icon]
            );
        }

        $expenseCategories = [
            'Food' => 'fas fa-utensils',
            'Transport' => 'fas fa-bus',
            'Hostel/Rent' => 'fas fa-home',
            'Academics (books, tuition, stationery)' => 'fas fa-book',
            'Subscriptions (streaming, apps)' => 'fas fa-play-circle',
            'Entertainment (movies, outings)' => 'fas fa-film',
            'Miscellaneous' => 'fas fa-box-open'
        ];

        foreach ($expenseCategories as $name => $icon) {
            Category::firstOrCreate(
                ['name' => $name, 'type' => 'expense'],
                ['is_default' => true, 'icon' => $icon]
            );
        }

        // 2. Create Admin Account
        $admin = User::updateOrCreate(
            ['email' => 'admin@campuscoin.com'],
            [
                'name' => 'Campus Admin',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'academic_year' => 'Faculty/Staff',
                'monthly_allowance_baseline' => 0.00,
                'monthly_savings_goal' => 0.00,
                'is_disabled' => false,
            ]
        );

        // 3. Create Sample Student Account
        $student = User::updateOrCreate(
            ['email' => 'student@campuscoin.com'],
            [
                'name' => 'Alex Morgan',
                'password' => Hash::make('student123'),
                'role' => 'student',
                'academic_year' => 'Senior Year 2026',
                'monthly_allowance_baseline' => 500.00,
                'monthly_savings_goal' => 120.00,
                'is_disabled' => false,
            ]
        );

        // 4. Create Sample Announcements
        Announcement::firstOrCreate(
            ['title' => 'Welcome to Campus Coin 2.0!'],
            [
                'content' => 'Track your allowances, manage monthly budgets, and receive instant AI-powered savings tips styled for university life.',
                'is_active' => true,
                'created_by' => $admin->id
            ]
        );

        Announcement::firstOrCreate(
            ['title' => 'Midterm Savings Challenge'],
            [
                'content' => 'Set up category budgets this week and track your coffee & canteen spending to stay on top of your semester goals!',
                'is_active' => true,
                'created_by' => $admin->id
            ]
        );

        // 5. Seed Transactions for Student
        $foodCat = Category::where('name', 'Food')->first();
        $transportCat = Category::where('name', 'Transport')->first();
        $hostelCat = Category::where('name', 'Hostel/Rent')->first();
        $academicsCat = Category::where('name', 'like', 'Academics%')->first();
        $subsCat = Category::where('name', 'like', 'Subscriptions%')->first();
        $entCat = Category::where('name', 'like', 'Entertainment%')->first();

        $allowanceCat = Category::where('name', 'Allowance')->first();
        $partTimeCat = Category::where('name', 'Part-time Job')->first();
        $scholarshipCat = Category::where('name', 'Scholarship')->first();

        $currentMonth = Carbon::now()->format('Y-m');

        // Income entries
        Transaction::firstOrCreate([
            'user_id' => $student->id,
            'description' => 'Monthly Family Allowance',
            'date' => Carbon::now()->startOfMonth()->toDateString(),
        ], [
            'category_id' => $allowanceCat->id,
            'amount' => 350.00,
            'type' => 'income',
            'is_recurring' => true,
            'recurrence_frequency' => 'monthly'
        ]);

        Transaction::firstOrCreate([
            'user_id' => $student->id,
            'description' => 'Campus Library Assistant Stint',
            'date' => Carbon::now()->subDays(10)->toDateString(),
        ], [
            'category_id' => $partTimeCat->id,
            'amount' => 150.00,
            'type' => 'income',
            'is_recurring' => false
        ]);

        Transaction::firstOrCreate([
            'user_id' => $student->id,
            'description' => 'Merit Grant Award',
            'date' => Carbon::now()->subDays(20)->toDateString(),
        ], [
            'category_id' => $scholarshipCat->id,
            'amount' => 100.00,
            'type' => 'income',
            'is_recurring' => false
        ]);

        // Expense entries
        $sampleExpenses = [
            ['cat' => $hostelCat->id, 'amount' => 180.00, 'desc' => 'Dorm Room Monthly Rent', 'days_ago' => 22, 'recurring' => true],
            ['cat' => $foodCat->id, 'amount' => 24.50, 'desc' => 'Campus Canteen Lunch & Smoothies', 'days_ago' => 2, 'recurring' => false],
            ['cat' => $foodCat->id, 'amount' => 35.00, 'desc' => 'Campus Cafe Weekend Dining', 'days_ago' => 5, 'recurring' => false],
            ['cat' => $foodCat->id, 'amount' => 18.20, 'desc' => 'Hostel Grocery Snacks', 'days_ago' => 8, 'recurring' => false],
            ['cat' => $transportCat->id, 'amount' => 25.00, 'desc' => 'Monthly Student Transit Pass', 'days_ago' => 15, 'recurring' => true],
            ['cat' => $academicsCat->id, 'amount' => 45.00, 'desc' => 'Data Science Reference Textbook', 'days_ago' => 12, 'recurring' => false],
            ['cat' => $subsCat->id, 'amount' => 12.99, 'desc' => 'Spotify & Netflix Student Bundle', 'days_ago' => 14, 'recurring' => true],
            ['cat' => $entCat->id, 'amount' => 30.00, 'desc' => 'Weekend Cinema with Campus Crew', 'days_ago' => 6, 'recurring' => false],
        ];

        foreach ($sampleExpenses as $exp) {
            Transaction::firstOrCreate([
                'user_id' => $student->id,
                'description' => $exp['desc'],
                'date' => Carbon::now()->subDays($exp['days_ago'])->toDateString(),
            ], [
                'category_id' => $exp['cat'],
                'amount' => $exp['amount'],
                'type' => 'expense',
                'is_recurring' => $exp['recurring'],
                'recurrence_frequency' => $exp['recurring'] ? 'monthly' : null,
                'ai_suggested_category_id' => $exp['cat']
            ]);
        }

        // 6. Budgets
        Budget::firstOrCreate([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'month' => $currentMonth,
        ], ['limit_amount' => 90.00]);

        Budget::firstOrCreate([
            'user_id' => $student->id,
            'category_id' => $transportCat->id,
            'month' => $currentMonth,
        ], ['limit_amount' => 40.00]);

        Budget::firstOrCreate([
            'user_id' => $student->id,
            'category_id' => $entCat->id,
            'month' => $currentMonth,
        ], ['limit_amount' => 35.00]);

        // 7. Saving Tips
        SavingTip::firstOrCreate([
            'user_id' => $student->id,
            'tip_title' => 'Optimize Campus Dining Options',
        ], [
            'category_id' => $foodCat->id,
            'tip_body' => 'You spent \$77.70 on food this month. Meal prepping on Sundays or getting the weekly meal pass at the campus cafeteria could save up to \$30.00 per month.',
            'potential_savings' => 30.00,
            'status' => 'pinned',
            'is_bookmarked' => true
        ]);

        SavingTip::firstOrCreate([
            'user_id' => $student->id,
            'tip_title' => 'Share Academic Subscriptions',
        ], [
            'category_id' => $subsCat->id,
            'tip_body' => 'Switch to a shared student multi-user plan for your streaming & app services to split monthly costs with roommates.',
            'potential_savings' => 6.50,
            'status' => 'active',
            'is_bookmarked' => false
        ]);

        // 8. Monthly Insight
        Insight::firstOrCreate([
            'user_id' => $student->id,
            'month' => $currentMonth,
        ], [
            'summary_text' => 'This month, your total income reached \$600.00 against \$345.69 in expenses. Your largest expense category was Hostel/Rent (\$180.00), followed closely by Food (\$77.70). You are currently on track to reach your monthly savings goal of \$120.00 with \$254.31 remaining balance!',
            'tip_text' => 'Food delivery and cafe outings rose 25% compared to your historical baseline. Consider setting a \$20 weekly cap on takeaway orders to boost your end-of-semester emergency fund.',
            'is_bookmarked' => true,
            'generated_at' => Carbon::now()
        ]);
    }
}
