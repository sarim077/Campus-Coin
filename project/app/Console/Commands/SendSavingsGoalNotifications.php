<?php

namespace App\Console\Commands;

use App\Services\SavingsGoalService;
use Illuminate\Console\Command;

class SendSavingsGoalNotifications extends Command
{
    protected $signature = 'savings-goals:send-notifications';

    protected $description = 'Email students about savings goals due today or tomorrow';

    public function handle(SavingsGoalService $goals): int
    {
        $sent = $goals->sendDueNotifications();
        $this->info("Sent {$sent} savings goal notification(s).");

        return self::SUCCESS;
    }
}
