<?php

namespace App\Services;

use App\Mail\SavingsGoalDeadlineMail;
use App\Models\Budget;
use App\Models\SiteSetting;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SavingsGoalService
{
    public function savedAmount(User $user, CarbonInterface $throughDate): float
    {
        if (! $user->savings_goal_started_at) {
            return 0.0;
        }

        $start = Carbon::parse($user->savings_goal_started_at)->startOfDay();
        $end = Carbon::parse($throughDate)->startOfDay();
        if ($end->lt($start)) {
            return 0.0;
        }

        $saved = 0.0;
        $month = $start->copy()->startOfMonth();

        while ($month->lte($end)) {
            $monthStart = $month->copy()->max($start);
            $monthEnd = $month->copy()->endOfMonth()->min($end);

            $transactions = Transaction::query()
                ->where('user_id', $user->id)
                ->whereDate('date', '>=', $monthStart->toDateString())
                ->whereDate('date', '<=', $monthEnd->toDateString());
            $income = (float) (clone $transactions)
                ->where('type', 'income')
                ->sum('amount');

            $budgetLimits = Budget::query()
                ->where('user_id', $user->id)
                ->where('month', $month->format('Y-m'))
                ->whereHas('category', fn ($query) => $query->where('type', 'expense'))
                ->pluck('limit_amount', 'category_id')
                ->map(fn ($amount) => (float) $amount);

            $budgetTotal = $budgetLimits->sum();
            $incomeBeforeGoal = $monthStart->gt($month->copy()->startOfMonth())
                ? (float) Transaction::query()
                    ->where('user_id', $user->id)
                    ->where('type', 'income')
                    ->whereBetween('date', [$month->copy()->startOfMonth()->toDateString(), $monthStart->copy()->subDay()->toDateString()])
                    ->sum('amount')
                : 0.0;
            $unfundedBudgetAmount = max(0, $budgetTotal - min($incomeBeforeGoal, $budgetTotal));
            $fundedBudgets = min($income, $unfundedBudgetAmount);
            $expenses = (clone $transactions)
                ->where('type', 'expense')
                ->selectRaw('category_id, SUM(amount) as total')
                ->groupBy('category_id')
                ->get();
            $overBudgetSpending = $expenses->sum(function ($expense) use ($budgetLimits): float {
                return max(0, (float) $expense->total - (float) $budgetLimits->get($expense->category_id, 0));
            });

            $saved += $income - $fundedBudgets - $overBudgetSpending;
            $month->addMonth();
        }

        return round(max(0, $saved), 2);
    }

    public function updateGoal(User $user, float $goalAmount, ?string $dueDate): bool
    {
        $goalAmount = round($goalAmount, 2);
        $dueDate = $goalAmount > 0 && $dueDate !== null
            ? Carbon::parse($dueDate)->toDateString()
            : null;
        $goalChanged = $goalAmount !== (float) $user->monthly_savings_goal
            || $dueDate !== $user->savings_goal_due_date?->toDateString();

        if (! $goalChanged) {
            return false;
        }

        $user->update([
            'monthly_savings_goal' => $goalAmount,
            'savings_goal_due_date' => $dueDate,
            'savings_goal_started_at' => $goalAmount > 0 ? now()->toDateString() : null,
            'savings_goal_revision' => $user->savings_goal_revision + 1,
        ]);

        return true;
    }

    public function sendDueNotifications(?CarbonInterface $today = null): int
    {
        $today = Carbon::parse($today ?? now())->startOfDay();
        $sent = 0;

        User::query()
            ->where('role', 'student')
            ->where('monthly_savings_goal', '>', 0)
            ->whereNotNull('savings_goal_due_date')
            ->whereDate('savings_goal_due_date', '>=', $today->toDateString())
            ->whereDate('savings_goal_due_date', '<=', $today->copy()->addDay()->toDateString())
            ->orderBy('id')
            ->chunkById(100, function ($users) use ($today, &$sent): void {
                foreach ($users as $user) {
                    $dueDate = Carbon::parse($user->savings_goal_due_date)->startOfDay();
                    $isReminderDay = $dueDate->copy()->subDay()->isSameDay($today);
                    $isGoalDay = $dueDate->isSameDay($today);

                    if (! $isReminderDay && ! $isGoalDay) {
                        continue;
                    }

                    $savedAmount = $this->savedAmount(
                        $user,
                        $isReminderDay ? $today : $dueDate
                    );
                    $goalAmount = round((float) $user->monthly_savings_goal, 2);
                    $notificationType = $isReminderDay
                        ? ($savedAmount < $goalAmount ? 'reminder' : null)
                        : ($savedAmount >= $goalAmount ? 'achieved' : null);

                    if ($notificationType === null || ! $this->claimNotification($user, $notificationType, $savedAmount)) {
                        continue;
                    }

                    try {
                        Mail::to($user->email)->send(new SavingsGoalDeadlineMail(
                            $user,
                            $goalAmount,
                            $savedAmount,
                            max(0, round($goalAmount - $savedAmount, 2)),
                            $dueDate->format('F j, Y'),
                            $notificationType,
                            $user->currency_symbol ?: (SiteSetting::query()->find(1)?->currency_symbol ?: '$')
                        ));
                    } catch (\Throwable $exception) {
                        DB::table('savings_goal_notifications')
                            ->where('user_id', $user->id)
                            ->where('goal_revision', $user->savings_goal_revision)
                            ->where('notification_type', $notificationType)
                            ->delete();

                        throw $exception;
                    }

                    $sent++;
                }
            });

        return $sent;
    }

    private function claimNotification(User $user, string $type, float $savedAmount): bool
    {
        return DB::table('savings_goal_notifications')->insertOrIgnore([
            'user_id' => $user->id,
            'goal_revision' => $user->savings_goal_revision,
            'notification_type' => $type,
            'goal_amount' => number_format((float) $user->monthly_savings_goal, 2, '.', ''),
            'saved_amount' => number_format($savedAmount, 2, '.', ''),
            'goal_due_date' => $user->savings_goal_due_date,
            'sent_at' => now(),
        ]) === 1;
    }
}
