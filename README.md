# Campus Coin

A comprehensive student budget management and financial tracking application built with Laravel.

## 🌐 Live URL
https://campuscoin.xo.je/coin/project/public/index.php#home

## 👥 Team

**Team Leader:** Syed Azlan Ali

**Team Members:**
- Syeda Raiba
- Affan Bin Zeshan
- M.Sarim.Ansari

## 🔐 Test Credentials

### Admin Account
```
Email: admin@campuscoin.com
Password: CampusCoinAdmin!2026
```

### Demo Student Account
```
Email: student@campuscoin.com
Password: CampusCoinStudent!2026
```

## 📋 Features

- User authentication and role-based access (Admin/Student)
- Budget management and tracking
- Transaction recording with categories
- Income and expense categorization
- Savings goal setting and tracking
- AI-powered saving tips
- Monthly insights and financial summaries
- Recurring transaction support
- Multi-language support
- Two-factor authentication

## 🛠️ Tech Stack

- **Backend:** Laravel 10
- **Frontend:** Blade Templates
- **Database:** MySQL/MariaDB
- **Authentication:** Laravel Sanctum

## 📦 Installation

1. Clone the repository
2. Run `composer install`
3. Copy `.env.example` to `.env` and configure your database
4. Run `php artisan key:generate`
5. Run `php artisan migrate`
6. Run `php artisan db:seed`
7. Start the development server: `php artisan serve`

## 🗄️ Database Tables

The application includes the following tables:
- users
- categories
- transactions
- budgets
- insights
- saving_tips
- saving_tip_templates
- announcements
- recently_viewed
- site_settings
- password_reset_tokens
- password_reset_codes
- failed_jobs
- personal_access_tokens
- category_corrections
- savings_goal_achievements
- savings_goal_notifications
- hidden_budget_categories

## 📝 License

This project is developed for educational purposes.
