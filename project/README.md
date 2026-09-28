# Campus Coin — Smart Spending Student Style
**Theme:** NextGen BudgetBee  
**Category:** End-to-End Web Solutions  
**Version:** 1.0 (Software Requirements Specification Implementation)

---

## 1. Problem Definition & Overview
College and university students routinely receive money from multiple, irregular sources such as monthly allowance from family, part-time or gig-work income, scholarships, and occasional gifts, yet rarely track where that money actually goes. Generic personal-finance apps are built for salaried adults with fixed pay cycles and bank integrations, making them overly complex or irrelevant to student spending patterns (canteen food, hostel expenses, textbooks, transport, subscriptions, and social outings).

**Campus Coin** closes this gap by offering a lightweight, student-first Web application that makes it effortless to log income and expenses, view spending by category, set monthly category budgets, receive AI savings guidance, and track 6-month financial trends without requiring linked bank accounts or financial expertise.

---

## 2. Accounts & Access

Student accounts are created from the registration page. Existing students and administrators sign in with their own credentials. The public site no longer displays one-click student or administrator demo access; administrator accounts should be provisioned by the database owner.

### Language & theme
- Signed-in students can use the **globe** menu in the student panel to translate the page with Google Translate. Public pages and administrator accounts do not have a language switcher.
- Registration asks students to choose a country/region, which sets the currency symbol used in that account's dashboard, transactions, reports, and savings emails. This changes the displayed currency label only; amounts are not converted using exchange rates. The site remains in English until the student explicitly changes language.
- Registration only collects account details, academic year, and country/region; allowance and savings goals can be set later from the dashboard or profile.
- New students receive a welcome email after successful registration. Configure Laravel's mail transport in `.env` for delivery.
- Use the **moon / sun** button to switch **light** and **dark** theme. The choice is saved in the browser.
- Font size (small / normal / large) is in the **A** menu.

### Account security and email
- Administrators can sign in directly at `/admin/login`; student and administrator accounts can opt in to authenticator-app TOTP from **Profile → Manage two-factor security**.
- The authenticator QR code is labelled **Campus Coin** and the account email. Ten one-time recovery codes are shown once at setup; store them safely. A recovery code can complete sign-in or turn off/replace 2FA when the authenticator device is unavailable.
- Password-reset links, administrator-reset notices, and user-requested report/tip/insight shares use Laravel Mail. Configure a real SMTP provider in `.env`; do not commit SMTP credentials.
- Campus Coin email messages use the site's blue visual identity with responsive, email-client-safe inline styling. Password recovery displays an explicit error when an address is not registered.
- Student saving tips are generated from each account's own budget and spending totals. To enable additional AI recommendations, configure `OPENROUTER_API_KEY` in the local `.env`; requests send category-level totals and budget figures only, not names, emails, or transaction descriptions. If the provider is unconfigured or unavailable, the existing deterministic tips and admin templates remain available.

Database is **MySQL only**. Import the provided `campus_coin.sql` dump; it uses MySQL-specific `ENUM`, `InnoDB`, foreign keys, and `utf8mb4` definitions. SQLite is not a supported application or evaluation database.

---

## 3. Implemented Functional Panels & Features

### A. Student Control Panel (`/dashboard`)
1. **Personalized Dashboard & Balance:**
   - Personalized greeting, current month balance (income vs. expense), baseline allowance tracker, and a dated savings-goal thermometer.
   - The dashboard shows remaining monthly budget allocations and the available balance after budgets and out-of-budget expenses. Expenses in a budget category reduce that category's remaining budget; the **Other / unbudgeted** expense category lets students record spending that reduces the available balance and savings-goal progress instead. Set or edit the goal amount and due date from the dashboard thermometer or **Profile**; changing either starts a new goal period. The app sends a one-day-before shortfall reminder and a due-date congratulations email if the goal amount remains saved.
   - Dynamic widgets: *This Month's Top Category*, *Budget vs. Actual Progress Bars*, and *6-Month Trend Chart*.
2. **Income and Expense Logging (`/transactions`):**
   - Quick-add modal & full CRUD transaction logging for income and expense categories.
   - Recurring weekly/monthly templates create due transactions through Laravel's scheduler. Deleted transactions are retained in a recoverable history.
   - **Optional AI-Driven Expense Categorization Assistant:** Automatically suggests appropriate category as student types description (e.g., "Campus Cafe" -> Food, "Bookstore" -> Academics).
   - **Bulk CSV Transaction Import:** Allows students to preview rows and review or override category suggestions before confirming an import.
3. **Category Management (`/categories`):**
   - Manage personal income & expense categories under 'Manage Own Categories'.
   - Access to default system categories (Allowance, Part-time Job, Scholarship, Gift / Food, Transport, Hostel/Rent, Academics, Subscriptions, Entertainment, Miscellaneous).
4. **Budget Goals & Real-time Alerts (`/budgets`):**
   - Monthly category limit setting with spending progress bars. Logged income funds configured category budgets in the order they were set; any income remaining after budget funding contributes to the savings goal.
   - In-app alert triggers when category consumption reaches warning (&ge;80%) or exceeded status (&ge;100%).
   - Students can hide unused categories from their budget page and restore them later; hiding does not delete budgets, transactions, or spending history.
5. **Monthly Reports & Analytics (`/reports`):**
   - Category-wise monthly spending distribution chart.
   - 6-Month Income vs. Expense trend chart.
   - Daily and weekly spending summaries.
   - Date range, expense-category, and income-source filters.
   - **PDF/printable and PNG exports:** Download a report image or print/save the statement as PDF (`/reports/pdf`).
   - Email a financial summary to an address selected by the student.
6. **Optional AI-Generated Monthly Spending Insights (`/insights`):**
   - Analyzes monthly transaction history to produce plain-language narrative summaries.
   - Flags high-growth spending categories (e.g., "Food & dining rose 35% over baseline").
   - Actionable advice & bookmarking functionality.
7. **Personalized Saving Tips Engine (`/saving-tips`):**
   - Ranks saving opportunities by potential dollar impact.
   - Compares expense categories with recent completed-month averages and configured budgets.
   - Allows students to pin, dismiss, bookmark, annotate, and email saving tips.
   - Students can add private notes to and email monthly insights.

### B. Admin Control Panel (`/admin/dashboard`)
1. **Executive System Dashboard:** View active student accounts, total transaction volume, total income/expense logged, and most-used categories.
2. **Default System Categories (`/admin/categories`):** Add, edit, and remove system-wide default income/expense categories available to all students.
3. **System Announcements (`/admin/announcements`) and Saving-tip Templates (`/admin/tip-templates`):** Publish dashboard announcements and manage reusable personalized-tip templates.
4. **User Account Oversight (`/admin/users`):** View student list, toggle access status (enable/disable accounts), and perform password resets.

### C. Accessibility & UI Enhancements
- **Dark Mode Toggle:** Persistent light/dark mode switch in top navbar.
- **Font-size Adjustment:** Small, Medium, Large text size switcher in top navbar.
- **Interactive Visual Sitemap:** Flowchart diagram rendered on landing page (`/`#sitemap).

---

## 4. Database Schema Structure (`campus_coin.sql`)

The database consists of 8 relational tables exported in `campus_coin.sql`:
- `users`: User profile, credentials, role (`student`/`admin`), academic year, allowance baseline, savings goal, account status.
- `categories`: Income and expense categories (system default & user custom).
- `transactions`: Logged financial entries, category foreign key, recurring schedule, recoverable deletion timestamp, and AI suggested category references.
- `budgets`: Category monthly budget limits (`YYYY-MM`).
- `insights`: AI-generated narrative summaries, flagged patterns, bookmarking status.
- `saving_tips`: Recommended saving opportunities, potential savings amount, pin/dismiss/bookmark status.
- `announcements`: System-wide admin broadcast announcements.
- `recently_viewed`: Session activity audit trail for transaction edits.
- `saving_tip_templates`: Reusable admin-managed saving-tip templates.
- `category_corrections`: Per-user category corrections used to improve future suggestions.
- `users`, `saving_tips`, and `insights` also store two-factor/recovery-code data and private notes as appropriate.

---

## 5. Installation & Setup Instructions

### Prerequisites
- PHP &ge; 8.1
- MySQL Server (XAMPP / MariaDB)
- Composer

### Step 1: Clone / Copy Project
Place the project files inside your web server directory (e.g., `C:\xampp\htdocs\nn`).

### Step 2: Environment Configuration
Ensure `.env` file exists and has correct database parameters:
```ini
APP_NAME="Campus Coin"
APP_ENV=local
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=campus_coin
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your-smtp-username
MAIL_PASSWORD=your-smtp-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=campus-coin@example.com
MAIL_FROM_NAME="Campus Coin"
```

The checked-in `.env.example` uses Mailpit for local development. Replace these values with credentials from an SMTP provider before testing real email. Keep `.env` private.

### Step 3: Database Import
Import the provided `campus_coin.sql` file into MySQL:
```bash
# Using MySQL CLI or phpMyAdmin:
mysql -u root -e "CREATE DATABASE IF NOT EXISTS campus_coin;"
mysql -u root campus_coin < campus_coin.sql
```
After importing the dump, apply new additive migrations:
```bash
php artisan migrate
```
The Laravel migrations are also MySQL-compatible for a fresh install:
```bash
php artisan migrate:fresh --seed
```
Do not create or configure a `database.sqlite` file.

### Step 4: Run Application Server
Run the built-in Artisan development server or access via XAMPP Apache:
```bash
php artisan serve
```
Access the application in your browser at `http://127.0.0.1:8000` or `http://localhost/nn/public`.

### Recurring-entry scheduler
Due recurring entries are created by `transactions:generate-recurring`. To run the Laravel scheduler locally, start:
```bash
php artisan schedule:work
```
On Windows production/development hosts, configure Task Scheduler to run `php artisan schedule:run` every minute. The scheduled task runs the due-entry generator daily at 00:05 and savings-goal emails daily at 09:00. Email delivery also requires valid SMTP settings in `.env`.

---

## 6. Project Deliverables Included
1. Complete Laravel Source Code & Blade Views
2. Complete MySQL Database Dump: `campus_coin.sql`
3. SRS Documentation & Installation Instructions: `README.md`
4. Interactive Application Sitemap embedded on Home Page
