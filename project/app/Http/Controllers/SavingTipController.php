<?php

namespace App\Http\Controllers;

use App\Mail\ShareSummaryMail;
use App\Models\Budget;
use App\Models\Category;
use App\Models\SavingTip;
use App\Models\SavingTipTemplate;
use App\Models\Transaction;
use App\Services\PersonalizedSavingTipGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SavingTipController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $tips = SavingTip::where('user_id', $user->id)
            ->orderByRaw("CASE WHEN status = 'pinned' THEN 0 WHEN status = 'active' THEN 1 ELSE 2 END")
            ->orderByDesc('potential_savings')
            ->get();

        return view('saving_tips.index', compact('tips'));
    }

    public function generateTips(PersonalizedSavingTipGenerator $personalizedTips)
    {
        $user = auth()->user();
        $today = now()->toImmutable();
        $monthStart = $today->startOfMonth();
        $monthEnd = $today->endOfMonth();
        $historyStart = $today->subMonthsNoOverflow(3)->startOfMonth();
        $monthKey = $today->format('Y-m');

        $categories = Category::where('type', 'expense')
            ->where(function ($query) use ($user) {
                $query->where('is_default', true)->orWhere('user_id', $user->id);
            })->get();
        $hasSpendingSignal = false;
        $topSpendingCategory = null;
        $topCategorySpend = 0;

        foreach ($categories as $category) {
            $currentSpend = (float) Transaction::where('user_id', $user->id)
                ->where('category_id', $category->id)
                ->where('type', 'expense')
                ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->sum('amount');

            $history = Transaction::where('user_id', $user->id)
                ->where('category_id', $category->id)
                ->where('type', 'expense')
                ->whereDate('date', '>=', $historyStart->toDateString())
                ->whereDate('date', '<=', $monthStart->subDay()->toDateString())
                ->sum('amount');
            $historicalAverage = (float) $history / 3;

            $budget = Budget::where('user_id', $user->id)
                ->where('category_id', $category->id)
                ->where('month', $monthKey)
                ->first();
            $overBudget = $budget && (float) $budget->limit_amount > 0
                && $currentSpend >= (float) $budget->limit_amount * 0.8;
            $aboveAverage = $historicalAverage > 0 && $currentSpend > $historicalAverage * 1.15;

            if (! $overBudget && ! $aboveAverage) {
                if ($currentSpend > $topCategorySpend) {
                    $topSpendingCategory = $category;
                    $topCategorySpend = $currentSpend;
                }

                continue;
            }
            $hasSpendingSignal = true;

            $title = 'Review '.$category->name.' spending';
            $savings = $overBudget
                ? max(0, $currentSpend - (float) $budget->limit_amount * 0.8)
                : max(0, $currentSpend - $historicalAverage);
            $body = $overBudget
                ? 'You have spent '.number_format($currentSpend, 2).' of your '.number_format((float) $budget->limit_amount, 2)." monthly {$category->name} budget. Reducing the remaining month's spending can help you stay within your target."
                : "Your {$category->name} spending is above your recent monthly average of ".number_format($historicalAverage, 2).'. Review the transactions and look for a manageable way to reduce it.';

            $tip = SavingTip::firstOrNew(['user_id' => $user->id, 'tip_title' => $title]);
            $tip->fill([
                'category_id' => $category->id,
                'tip_body' => $body,
                'potential_savings' => round($savings, 2),
            ]);
            if (! $tip->exists) {
                $tip->status = 'active';
                $tip->is_bookmarked = false;
            }
            $tip->save();
        }

        if (! $hasSpendingSignal && $topSpendingCategory && $topCategorySpend > 0) {
            $suggestedSavings = round($topCategorySpend * 0.1, 2);
            $tip = SavingTip::firstOrNew([
                'user_id' => $user->id,
                'tip_title' => "Try a 10% reduction in {$topSpendingCategory->name}",
            ]);
            $tip->fill([
                'category_id' => $topSpendingCategory->id,
                'tip_body' => 'You have spent '.number_format($topCategorySpend, 2)." on {$topSpendingCategory->name} this month. Try spending 10% less next month by reviewing recent purchases and choosing one manageable change. The 10% figure is a planning target, not guaranteed savings.",
                'potential_savings' => $suggestedSavings,
                'is_ai_generated' => false,
            ]);
            if (! $tip->exists) {
                $tip->status = 'active';
                $tip->is_bookmarked = false;
            }
            $tip->save();
        }

        if ($user->monthly_savings_goal > 0) {
            $tip = SavingTip::firstOrNew(['user_id' => $user->id, 'tip_title' => '50/30/20 Rule for Allowance']);
            $tip->fill([
                'category_id' => null,
                'tip_body' => 'Allocate 50% of your monthly allowance ('.number_format($user->monthly_allowance_baseline * 0.5, 2).') to essentials, 30% to student social life, and 20% directly toward your savings goal.',
                'potential_savings' => round($user->monthly_allowance_baseline * 0.2, 2),
            ]);
            if (! $tip->exists) {
                $tip->status = 'active';
                $tip->is_bookmarked = false;
            }
            $tip->save();
        }

        foreach (SavingTipTemplate::where('is_active', true)->get() as $template) {
            $tip = SavingTip::firstOrNew([
                'user_id' => $user->id,
                'source_template_id' => $template->id,
            ]);
            $tip->fill([
                'category_id' => $template->category_id,
                'tip_title' => $template->title,
                'tip_body' => $template->body,
                'potential_savings' => $template->potential_savings,
            ]);
            if (! $tip->exists) {
                $tip->status = 'active';
                $tip->is_bookmarked = false;
            }
            $tip->save();
        }

        $generatedCount = $personalizedTips->generate($user);
        $hasTips = SavingTip::where('user_id', $user->id)
            ->whereIn('status', ['active', 'pinned'])
            ->exists();
        $message = $generatedCount > 0
            ? "Your tips are below. Updated your budget-based tips and generated {$generatedCount} AI-personalized recommendations."
            : ($hasTips
                ? 'Your tips are below. They use your budgets, spending, savings goal, and available tips.'
                : 'No tip could be generated yet. Add expense transactions, then click Generate My Tips again. AI tips also require OPENROUTER_API_KEY to be configured.');

        return redirect()->route('saving-tips.index')->with('success', $message);
    }

    public function updateStatus(Request $request, SavingTip $tip)
    {
        if ($tip->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'status' => 'required|in:active,pinned,dismissed',
        ]);

        $tip->update(['status' => $request->status]);

        return back()->with('success', "Tip status updated to {$request->status}.");
    }

    public function toggleBookmark(SavingTip $tip)
    {
        if ($tip->user_id !== auth()->id()) {
            abort(403);
        }

        $tip->update(['is_bookmarked' => ! $tip->is_bookmarked]);

        return back()->with('success', $tip->is_bookmarked ? 'Saving tip bookmarked!' : 'Bookmark removed.');
    }

    public function saveNote(Request $request, SavingTip $tip)
    {
        abort_unless($tip->user_id === $request->user()->id, 403);
        $validated = $request->validate(['user_note' => ['nullable', 'string', 'max:4000']]);
        $tip->update($validated);

        return back()->with('success', 'Your note was saved.');
    }

    public function email(Request $request, SavingTip $tip)
    {
        abort_unless($tip->user_id === $request->user()->id, 403);
        $validated = $request->validate(['email' => ['required', 'email', 'max:255']]);

        Mail::to($validated['email'])->send(new ShareSummaryMail(
            $tip->tip_title,
            $tip->tip_body,
            ['Estimated monthly savings' => number_format((float) $tip->potential_savings, 2)]
        ));

        return back()->with('success', 'Saving tip emailed successfully.');
    }
}
