<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Category;
use App\Models\SavingTip;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class PersonalizedSavingTipGenerator
{
    public function generate(User $user): int
    {
        $snapshot = $this->spendingSnapshot($user);
        $categoriesWithActivity = $snapshot['categories'];
        if ($categoriesWithActivity === []) {
            return 0;
        }

        $apiKey = config('services.openrouter.key');
        if (! is_string($apiKey) || trim($apiKey) === '') {
            Log::notice('AI saving tips were not generated because OPENROUTER_API_KEY is not configured.', [
                'user_id' => $user->id,
            ]);

            return 0;
        }

        try {
            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->asJson()
                ->timeout(25)
                ->post(config('services.openrouter.endpoint'), [
                    'model' => config('services.openrouter.model'),
                    'temperature' => 0.4,
                    'max_tokens' => 900,
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are a practical student budgeting coach. Give specific, supportive, nonjudgmental ways to save. Use only the supplied figures. Never invent purchases or claim guaranteed savings. Return JSON only as {"tips":[{"category":"exact supplied category name or General","title":"short title","body":"specific actionable advice","potential_savings":12.34}]}. Return at most 4 tips. Potential savings must be nonnegative and no greater than the category potential_savings_limit supplied. Do not repeat generic advice when the figures do not support it.',
                        ],
                        [
                            'role' => 'user',
                            'content' => json_encode([
                                'currency' => $user->region ? config("regions.{$user->region}.currency") : 'account currency',
                                'month' => now()->format('F Y'),
                                'monthly_allowance_baseline' => (float) $user->monthly_allowance_baseline,
                                'monthly_savings_goal' => (float) $user->monthly_savings_goal,
                                'income_and_budget_funding' => $snapshot['income'],
                                'categories' => $categoriesWithActivity,
                            ], JSON_THROW_ON_ERROR),
                        ],
                    ],
                ]);

            if (! $response->successful()) {
                Log::warning('OpenRouter returned an unsuccessful response while generating saving tips.', [
                    'user_id' => $user->id,
                    'status' => $response->status(),
                ]);

                return 0;
            }

            $content = $response->json('choices.0.message.content');
            if (! is_string($content)) {
                Log::warning('OpenRouter returned no saving-tip content.', ['user_id' => $user->id]);

                return 0;
            }

            return $this->saveTips($user, $snapshot, $content);
        } catch (Throwable $exception) {
            Log::warning('AI saving-tip generation failed.', [
                'user_id' => $user->id,
                'exception' => $exception::class,
            ]);

            return 0;
        }
    }

    private function spendingSnapshot(User $user): array
    {
        $today = now()->toImmutable();
        $month = $today->format('Y-m');
        $monthStart = $today->startOfMonth();
        $historyStart = $today->subMonthsNoOverflow(3)->startOfMonth();
        $income = (float) Transaction::query()
            ->where('user_id', $user->id)
            ->where('type', 'income')
            ->whereDate('date', '>=', $monthStart->toDateString())
            ->whereDate('date', '<=', $today->toDateString())
            ->sum('amount');

        $categories = Category::query()
            ->where('type', 'expense')
            ->where(fn ($query) => $query->where('is_default', true)->orWhere('user_id', $user->id))
            ->get();
        $budgets = Budget::query()
            ->where('user_id', $user->id)
            ->where('month', $month)
            ->get()
            ->keyBy('category_id');
        $spent = Transaction::query()
            ->where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereDate('date', '>=', $monthStart->toDateString())
            ->whereDate('date', '<=', $today->toDateString())
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->get()
            ->keyBy('category_id');
        $history = Transaction::query()
            ->where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereBetween('date', [$historyStart->toDateString(), $monthStart->copy()->subDay()->toDateString()])
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->get()
            ->keyBy('category_id');

        return [
            'income' => $income,
            'categories' => $categories
                ->map(function (Category $category) use ($budgets, $spent, $history): array {
                    $categoryId = $category->id;
                    $budget = $budgets->get($categoryId);
                    $currentSpend = (float) ($spent->get($categoryId)?->total ?? 0);
                    $threeMonthAverage = round((float) ($history->get($categoryId)?->total ?? 0) / 3, 2);
                    $budgetUsedPercent = $budget && (float) $budget->limit_amount > 0
                        ? round($currentSpend / (float) $budget->limit_amount * 100, 1)
                        : null;
                    $overBudgetGuidanceLimit = $budgetUsedPercent !== null && $budgetUsedPercent >= 80
                        ? max(0, $currentSpend - (float) $budget->limit_amount * 0.8)
                        : 0;
                    $aboveAverageGuidanceLimit = $threeMonthAverage > 0
                        && $currentSpend > $threeMonthAverage * 1.15
                        ? max(0, $currentSpend - $threeMonthAverage * 1.15)
                        : 0;
                    $guidanceLimits = array_filter(
                        [$overBudgetGuidanceLimit, $aboveAverageGuidanceLimit],
                        fn (float $limit): bool => $limit > 0
                    );

                    return [
                        'name' => $category->name,
                        'current_spend' => round($currentSpend, 2),
                        'monthly_budget' => $budget ? (float) $budget->limit_amount : null,
                        'budget_used_percent' => $budgetUsedPercent,
                        'three_month_average' => $threeMonthAverage,
                        'potential_savings_limit' => round($guidanceLimits ? min($guidanceLimits) : 0, 2),
                        'is_actionable' => $overBudgetGuidanceLimit > 0 || $aboveAverageGuidanceLimit > 0,
                    ];
                })
                ->filter(fn (array $category): bool => $category['current_spend'] > 0 || $category['monthly_budget'] !== null)
                ->values()
                ->all(),
        ];
    }

    private function saveTips(User $user, array $snapshot, string $content): int
    {
        $decoded = json_decode($content, true);
        $tips = is_array($decoded) ? ($decoded['tips'] ?? null) : null;
        if (! is_array($tips)) {
            Log::warning('OpenRouter returned malformed saving-tip JSON.', ['user_id' => $user->id]);

            return 0;
        }

        $allowedCategories = collect($snapshot['categories'])->keyBy('name');
        $generalSavingsLimit = collect($snapshot['categories'])->sum('potential_savings_limit');
        $saved = 0;
        foreach (array_slice($tips, 0, 4) as $tipData) {
            if (! is_array($tipData)
                || ! is_string($tipData['title'] ?? null)
                || ! is_string($tipData['body'] ?? null)
                || ! is_numeric($tipData['potential_savings'] ?? null)) {
                continue;
            }

            $categoryName = is_string($tipData['category'] ?? null) ? $tipData['category'] : 'General';
            $categorySnapshot = $allowedCategories->get($categoryName);
            if ($categoryName !== 'General' && ! $categorySnapshot) {
                continue;
            }
            $category = $categorySnapshot
                ? Category::query()->where('type', 'expense')->where('name', $categoryName)
                    ->where(fn ($query) => $query->where('is_default', true)->orWhere('user_id', $user->id))
                    ->first()
                : null;

            $maxSavings = $categorySnapshot['potential_savings_limit'] ?? (
                $categoryName === 'General' ? $generalSavingsLimit : 0
            );
            $title = trim($tipData['title']);
            $body = trim($tipData['body']);
            if ($title === '' || $body === '' || mb_strlen($title) > 255 || mb_strlen($body) > 5000) {
                continue;
            }

            $tip = SavingTip::query()->firstOrNew([
                'user_id' => $user->id,
                'tip_title' => $title,
            ]);
            $tip->fill([
                'category_id' => $category?->id,
                'tip_body' => $body,
                'potential_savings' => round(min((float) $tipData['potential_savings'], $maxSavings), 2),
                'is_ai_generated' => true,
            ]);
            if (! $tip->exists) {
                $tip->status = 'active';
                $tip->is_bookmarked = false;
            }
            $tip->save();
            $saved++;
        }

        if ($saved === 0 && $tips !== []) {
            Log::warning('No valid personalized saving tips could be saved from OpenRouter output.', [
                'user_id' => $user->id,
            ]);
        }

        return $saved;
    }
}
