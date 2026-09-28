<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateRecurringTransactions extends Command
{
    protected $signature = 'transactions:generate-recurring';

    protected $description = 'Create due occurrences from active weekly and monthly transaction entries';

    public function handle(): int
    {
        $created = 0;

        Transaction::query()
            ->where('is_recurring', true)
            ->whereNotNull('next_occurrence_at')
            ->whereDate('next_occurrence_at', '<=', now()->toDateString())
            ->orderBy('id')
            ->chunkById(100, function ($templates) use (&$created): void {
                foreach ($templates as $template) {
                    $created += DB::transaction(function () use ($template): int {
                        $locked = Transaction::whereKey($template->id)->lockForUpdate()->first();
                        if (! $locked || ! $locked->is_recurring || ! $locked->next_occurrence_at) {
                            return 0;
                        }

                        $count = 0;
                        $dueDate = Carbon::parse($locked->next_occurrence_at);
                        while ($dueDate->lte(now()->startOfDay())) {
                            $occurrence = Transaction::withTrashed()->firstOrCreate(
                                [
                                    'recurrence_parent_id' => $locked->id,
                                    'date' => $dueDate->toDateString(),
                                ],
                                [
                                    'user_id' => $locked->user_id,
                                    'category_id' => $locked->category_id,
                                    'amount' => $locked->amount,
                                    'type' => $locked->type,
                                    'description' => $locked->description,
                                    'is_recurring' => false,
                                    'recurrence_frequency' => null,
                                    'ai_suggested_category_id' => $locked->ai_suggested_category_id,
                                ]
                            );
                            if ($occurrence->wasRecentlyCreated) {
                                $count++;
                            }
                            $dueDate = $locked->recurrence_frequency === 'weekly'
                                ? $dueDate->addWeek()
                                : $dueDate->addMonthNoOverflow();
                        }

                        $locked->update(['next_occurrence_at' => $dueDate->toDateString()]);

                        return $count;
                    });
                }
            });

        $this->info("Created {$created} recurring transaction occurrence(s).");

        return self::SUCCESS;
    }
}
