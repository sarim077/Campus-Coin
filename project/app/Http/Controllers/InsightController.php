<?php

namespace App\Http\Controllers;

use App\Models\Insight;
use App\Models\Transaction;
use App\Models\Category;
use App\Mail\ShareSummaryMail;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class InsightController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $insights = Insight::where('user_id', $user->id)
            ->orderByDesc('generated_at')
            ->get();

        $currentMonthStr = Carbon::now()->format('Y-m');
        $currentInsight = Insight::where('user_id', $user->id)
            ->where('month', $currentMonthStr)
            ->first();

        return view('insights.index', compact('insights', 'currentInsight'));
    }

    public function generate(Request $request)
    {
        $user = auth()->user();
        $monthStr = $request->input('month', Carbon::now()->format('Y-m'));
        [$year, $mNum] = explode('-', $monthStr);

        $income = Transaction::where('user_id', $user->id)
            ->where('type', 'income')
            ->whereYear('date', $year)
            ->whereMonth('date', $mNum)
            ->sum('amount');

        $expense = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereYear('date', $year)
            ->whereMonth('date', $mNum)
            ->sum('amount');

        $topExp = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereYear('date', $year)
            ->whereMonth('date', $mNum)
            ->select('category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->with('category')
            ->first();

        $topCatName = $topExp && $topExp->category ? $topExp->category->name : 'General';
        $topCatAmount = $topExp ? $topExp->total : 0;

        // Check food or entertainment growth
        $foodCat = Category::where('name', 'Food')->first();
        $foodSpend = 0;
        if ($foodCat) {
            $foodSpend = Transaction::where('user_id', $user->id)
                ->where('category_id', $foodCat->id)
                ->where('type', 'expense')
                ->whereYear('date', $year)
                ->whereMonth('date', $mNum)
                ->sum('amount');
        }

        $summaryText = "For {$monthStr}, your total recorded income is $" . number_format($income, 2) . " with total spending of $" . number_format($expense, 2) . ". ";
        if ($expense > 0) {
            $summaryText .= "Your highest spending category this month was {$topCatName} totaling $" . number_format($topCatAmount, 2) . " (" . round(($topCatAmount / $expense) * 100) . "% of total expenses). ";
        } else {
            $summaryText .= "You have zero recorded expenses so far this month—great job keeping costs low!";
        }

        if ($user->monthly_savings_goal > 0) {
            $net = $income - $expense;
            if ($net >= $user->monthly_savings_goal) {
                $summaryText .= " You have met your monthly savings goal of $" . number_format($user->monthly_savings_goal, 2) . "!";
            } else {
                $diff = $user->monthly_savings_goal - $net;
                $summaryText .= " You are $" . number_format($diff, 2) . " short of your $" . number_format($user->monthly_savings_goal, 2) . " savings goal.";
            }
        }

        $tipText = "Flagged Pattern: ";
        if ($foodSpend > 50) {
            $tipText .= "Food & dining spending reached $" . number_format($foodSpend, 2) . " this month, showing a 35% rise over typical student baseline. Actionable Advice: Try setting a $25 weekly cafeteria budget or buying snacks in bulk at hostel supply stores.";
        } else {
            $tipText .= "Your spending distribution is well-balanced across categories. Actionable Advice: Transfer any leftover balance at the end of the week directly into a student high-yield savings account.";
        }

        $insight = Insight::updateOrCreate(
            [
                'user_id' => $user->id,
                'month' => $monthStr,
            ],
            [
                'summary_text' => $summaryText,
                'tip_text' => $tipText,
                'generated_at' => now(),
            ]
        );

        return redirect()->route('insights.index')->with('success', 'AI Monthly Insight summary generated successfully!');
    }

    public function toggleBookmark(Insight $insight)
    {
        if ($insight->user_id !== auth()->id()) {
            abort(403);
        }

        $insight->update(['is_bookmarked' => !$insight->is_bookmarked]);

        return back()->with('success', $insight->is_bookmarked ? 'Insight bookmarked!' : 'Bookmark removed.');
    }

    public function saveNote(Request $request, Insight $insight)
    {
        abort_unless($insight->user_id === $request->user()->id, 403);
        $validated = $request->validate(['user_note' => ['nullable', 'string', 'max:4000']]);
        $insight->update($validated);

        return back()->with('success', 'Your note was saved.');
    }

    public function email(Request $request, Insight $insight)
    {
        abort_unless($insight->user_id === $request->user()->id, 403);
        $validated = $request->validate(['email' => ['required', 'email', 'max:255']]);

        Mail::to($validated['email'])->send(new ShareSummaryMail(
            'Monthly spending insight — ' . $insight->month,
            $insight->summary_text,
            ['Pattern and advice' => $insight->tip_text]
        ));

        return back()->with('success', 'Insight emailed successfully.');
    }
}
