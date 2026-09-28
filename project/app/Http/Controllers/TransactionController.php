<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CategoryCorrection;
use App\Models\RecentlyViewed;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use RuntimeException;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $showDeleted = $request->boolean('show_deleted');
        $query = Transaction::withTrashed()->where('user_id', $user->id)->with('category');
        $showDeleted ? $query->onlyTrashed() : $query->whereNull('deleted_at');

        if ($request->filled('type') && in_array($request->type, ['income', 'expense'])) {
            $query->where('type', $request->type);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        if ($request->filled('search')) {
            $query->where('description', 'like', '%'.$request->search.'%');
        }

        $transactions = $query->orderByDesc('date')->orderByDesc('id')->paginate(15)->withQueryString();

        $categories = Category::where(function ($q) use ($user) {
            $q->where('is_default', true)->orWhere('user_id', $user->id);
        })->get();

        return view('transactions.index', compact('transactions', 'categories', 'showDeleted'));
    }

    public function create()
    {
        $user = auth()->user();
        $incomeCategories = Category::where('type', 'income')->where(function ($q) use ($user) {
            $q->where('is_default', true)->orWhere('user_id', $user->id);
        })->get();

        $expenseCategories = Category::where('type', 'expense')->where(function ($q) use ($user) {
            $q->where('is_default', true)->orWhere('user_id', $user->id);
        })->get();

        return view('transactions.create', compact('incomeCategories', 'expenseCategories'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $unbudgetedExpense = $request->input('type') === 'expense'
            && $request->input('category_id') === 'unbudgeted';
        $categoryRules = ['required'];
        if ($unbudgetedExpense) {
            $categoryRules[] = Rule::in(['unbudgeted']);
        } else {
            $categoryRules[] = 'integer';
            $categoryRules[] = 'exists:categories,id';
        }

        $request->validate([
            'type' => ['required', 'in:income,expense'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'category_id' => $categoryRules,
            'description' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'is_recurring' => ['nullable', 'boolean'],
            'recurrence_frequency' => ['nullable', 'in:weekly,monthly'],
            'ai_suggested_category_id' => ['nullable', 'integer', 'exists:categories,id'],
        ]);

        $category = $unbudgetedExpense ? null : Category::whereKey($request->category_id)
            ->where('type', $request->type)
            ->where(function ($query) use ($user) {
                $query->where('is_default', true)->orWhere('user_id', $user->id);
            })->firstOrFail();
        $isRecurring = $request->boolean('is_recurring');
        $suggestedCategoryId = $category
            ? $this->visibleSuggestionId($user->id, $request->input('ai_suggested_category_id'), $request->type)
            : null;
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'type' => $request->type,
            'amount' => $request->amount,
            'category_id' => $category?->id,
            'description' => $request->description,
            'date' => $request->date,
            'is_recurring' => $isRecurring,
            'recurrence_frequency' => $isRecurring ? ($request->recurrence_frequency ?? 'monthly') : null,
            'next_occurrence_at' => $isRecurring ? $this->nextOccurrence($request->date, $request->recurrence_frequency ?? 'monthly') : null,
            'ai_suggested_category_id' => $suggestedCategoryId,
        ]);
        if ($category) {
            $this->learnFromCorrection($user->id, $request->type, $request->description, $category->id, $suggestedCategoryId);
        }

        return redirect()->route('transactions.index')->with('success', 'Transaction logged successfully!');
    }

    public function edit(Transaction $transaction)
    {
        if ($transaction->user_id !== auth()->id()) {
            abort(403);
        }

        // Track recently viewed
        RecentlyViewed::updateOrCreate(
            ['user_id' => auth()->id(), 'transaction_id' => $transaction->id],
            ['viewed_at' => now()]
        );

        $user = auth()->user();
        $categories = Category::where(function ($q) use ($user) {
            $q->where('is_default', true)->orWhere('user_id', $user->id);
        })->where('type', $transaction->type)->get();

        return view('transactions.edit', compact('transaction', 'categories'));
    }

    public function update(Request $request, Transaction $transaction)
    {
        if ($transaction->user_id !== auth()->id()) {
            abort(403);
        }

        $unbudgetedExpense = $transaction->type === 'expense'
            && $request->input('category_id') === 'unbudgeted';
        $categoryRules = ['required'];
        if ($unbudgetedExpense) {
            $categoryRules[] = Rule::in(['unbudgeted']);
        } else {
            $categoryRules[] = 'integer';
            $categoryRules[] = 'exists:categories,id';
        }

        $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'category_id' => $categoryRules,
            'description' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'is_recurring' => ['nullable', 'boolean'],
            'recurrence_frequency' => ['nullable', 'in:weekly,monthly'],
            'ai_suggested_category_id' => ['nullable', 'integer', 'exists:categories,id'],
        ]);

        $user = auth()->user();
        $category = $unbudgetedExpense ? null : Category::whereKey($request->category_id)
            ->where('type', $transaction->type)
            ->where(function ($query) use ($user) {
                $query->where('is_default', true)->orWhere('user_id', $user->id);
            })->firstOrFail();
        $isRecurring = $request->boolean('is_recurring');
        $suggestedCategoryId = $this->visibleSuggestionId(
            $user->id,
            $category ? $request->input('ai_suggested_category_id', $transaction->ai_suggested_category_id) : null,
            $transaction->type
        );
        $transaction->update([
            'amount' => $request->amount,
            'category_id' => $category?->id,
            'description' => $request->description,
            'date' => $request->date,
            'is_recurring' => $isRecurring,
            'recurrence_frequency' => $isRecurring ? ($request->recurrence_frequency ?? 'monthly') : null,
            'next_occurrence_at' => $isRecurring ? $this->nextOccurrence($request->date, $request->recurrence_frequency ?? 'monthly') : null,
        ]);
        if ($category) {
            $this->learnFromCorrection($user->id, $transaction->type, $request->description, $category->id, $suggestedCategoryId);
        }

        return redirect()->route('transactions.index')->with('success', 'Transaction updated successfully!');
    }

    public function destroy(Transaction $transaction)
    {
        if ($transaction->user_id !== auth()->id()) {
            abort(403);
        }

        $transaction->delete();

        return redirect()->route('transactions.index')->with('success', 'Transaction moved to your deleted transaction history. You can restore it at any time.');
    }

    public function restore(int $transaction)
    {
        $entry = Transaction::withTrashed()
            ->where('user_id', auth()->id())
            ->onlyTrashed()
            ->findOrFail($transaction);
        $entry->restore();

        return redirect()->route('transactions.index')->with('success', 'Transaction restored.');
    }

    // AI Expense Categorization Assistant API
    public function aiSuggestCategory(Request $request)
    {
        $description = strtolower(trim($request->input('description', '')));
        $requestedType = $request->input('type');
        if (! $description) {
            return response()->json(['suggested_category_id' => null, 'confidence' => 'none']);
        }

        $userId = auth()->id();
        $tokens = array_values(array_filter(explode(' ', preg_replace('/[^a-z0-9]+/i', ' ', $description) ?? ''), fn ($token) => strlen($token) >= 3));
        if ($tokens) {
            $corrections = CategoryCorrection::where('user_id', $userId)
                ->when(in_array($requestedType, ['income', 'expense'], true), fn ($query) => $query->where('type', $requestedType))
                ->where(function ($query) use ($tokens) {
                    foreach ($tokens as $token) {
                        $query->orWhere('normalized_description', 'like', '%'.$token.'%');
                    }
                })->latest('updated_at')->limit(100)->with('category')->get();

            $descriptionTokens = array_unique($tokens);
            $bestCorrection = $corrections->map(function (CategoryCorrection $correction) use ($descriptionTokens) {
                $correctionTokens = array_unique(explode(' ', $correction->normalized_description));
                $overlap = count(array_intersect($descriptionTokens, $correctionTokens));
                $score = $overlap / max(count($descriptionTokens), count($correctionTokens), 1);

                return ['correction' => $correction, 'score' => $score];
            })->sortByDesc('score')->first();

            if ($bestCorrection && $bestCorrection['score'] >= 0.5 && $bestCorrection['correction']->category) {
                $cat = $bestCorrection['correction']->category;

                return response()->json([
                    'suggested_category_id' => $cat->id,
                    'category_name' => $cat->name,
                    'type' => $cat->type,
                    'confidence' => 'high',
                    'source' => 'learned_correction',
                ]);
            }
        }

        // Check if user previously logged a similar description and retrieve their preferred category
        $historical = Transaction::where('user_id', auth()->id())
            ->when(in_array($requestedType, ['income', 'expense'], true), fn ($query) => $query->where('type', $requestedType))
            ->where('description', 'like', '%'.$description.'%')
            ->orderByDesc('id')
            ->first();

        if ($historical && $historical->category_id) {
            $cat = Category::find($historical->category_id);
            if ($cat) {
                return response()->json([
                    'suggested_category_id' => $cat->id,
                    'category_name' => $cat->name,
                    'type' => $cat->type,
                    'confidence' => 'high',
                    'source' => 'learned_history',
                ]);
            }
        }

        // Keyword rules matrix tailored for student spending
        $keywords = [
            'Food' => ['cafe', 'coffee', 'canteen', 'lunch', 'dinner', 'burger', 'pizza', 'starbucks', 'grocery', 'snack', 'food', 'restaurant', 'bakery', 'uber eats', 'doordash'],
            'Transport' => ['bus', 'metro', 'subway', 'train', 'uber', 'lyft', 'fuel', 'petrol', 'gas', 'transit', 'cab', 'fare', 'parking'],
            'Hostel/Rent' => ['hostel', 'rent', 'dorm', 'accommodation', 'utility', 'electricity', 'water bill'],
            'Academics (books, tuition, stationery)' => ['book', 'textbook', 'stationery', 'tuition', 'course', 'exam', 'print', 'copier', 'lab', 'udemy', 'coursera', 'pen', 'paper'],
            'Subscriptions (streaming, apps)' => ['netflix', 'spotify', 'youtube', 'apple', 'icloud', 'disney', 'amazon prime', 'subscription', 'software', 'chatgpt'],
            'Entertainment (movies, outings)' => ['movie', 'cinema', 'bowling', 'party', 'concert', 'gaming', 'steam', 'playstation', 'trip', 'outing'],
            'Allowance' => ['allowance', 'pocket money', 'family', 'parents'],
            'Part-time Job' => ['salary', 'wages', 'job', 'part-time', 'stint', 'freelance', 'paycheck'],
            'Scholarship' => ['scholarship', 'grant', 'stipend', 'bursary', 'award'],
            'Gift' => ['gift', 'present', 'birthday', 'reward'],
        ];

        foreach ($keywords as $categoryName => $wordList) {
            foreach ($wordList as $w) {
                if (str_contains($description, $w)) {
                    $type = in_array($categoryName, ['Allowance', 'Part-time Job', 'Scholarship', 'Gift', 'Other Income'], true)
                        ? 'income'
                        : 'expense';
                    if (in_array($requestedType, ['income', 'expense'], true) && $requestedType !== $type) {
                        continue;
                    }
                    $userId = auth()->id();
                    $cat = Category::where('name', 'like', '%'.$categoryName.'%')
                        ->where('type', $type)
                        ->where(function ($query) use ($userId) {
                            $query->where('is_default', true)->orWhere('user_id', $userId);
                        })->first();
                    if ($cat) {
                        return response()->json([
                            'suggested_category_id' => $cat->id,
                            'category_name' => $cat->name,
                            'type' => $cat->type,
                            'confidence' => 'medium',
                            'source' => 'ai_rules',
                        ]);
                    }
                }
            }
        }

        return response()->json(['suggested_category_id' => null, 'confidence' => 'none']);
    }

    // Parse a CSV into a review screen; no records are saved until the student confirms.
    public function importCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');
        if ($handle === false) {
            throw new RuntimeException('The uploaded CSV could not be opened.');
        }
        fgetcsv($handle, 1000, ',');
        $user = auth()->user();
        $rows = [];
        $line = 1;

        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            $line++;
            if (count($row) < 4) {
                fclose($handle);

                return back()->withErrors(['csv_file' => "CSV row {$line} must contain date, type, amount, and description."]);
            }

            $timestamp = strtotime(trim($row[0]));
            if ($timestamp === false) {
                fclose($handle);

                return back()->withErrors(['csv_file' => "CSV row {$line} has an invalid date."]);
            }
            $date = date('Y-m-d', $timestamp);
            $type = strtolower(trim($row[1] ?? 'expense'));
            if (! in_array($type, ['income', 'expense'], true)) {
                fclose($handle);

                return back()->withErrors(['csv_file' => "CSV row {$line} must specify income or expense."]);
            }
            if (! is_numeric($row[2] ?? null) || (float) $row[2] <= 0) {
                fclose($handle);

                return back()->withErrors(['csv_file' => "CSV row {$line} must have a positive numeric amount."]);
            }
            $amount = (float) $row[2];
            $description = trim($row[3] ?? '');
            if ($description === '' || mb_strlen($description) > 255) {
                fclose($handle);

                return back()->withErrors(['csv_file' => "CSV row {$line} needs a description of at most 255 characters."]);
            }
            $categoryName = trim($row[4] ?? '');
            $categoryId = null;
            $suggestedCategoryId = null;
            if ($categoryName) {
                $category = Category::where('name', $categoryName)
                    ->where('type', $type)
                    ->where(function ($query) use ($user) {
                        $query->where('is_default', true)->orWhere('user_id', $user->id);
                    })->first();
                if ($category) {
                    $categoryId = $category->id;
                }
            }

            if (! $categoryId) {
                $suggestion = $this->aiSuggestCategory(new Request(['description' => $description, 'type' => $type]))->getData(true);
                $suggested = $suggestion['suggested_category_id'] ?? null;
                $suggestedCategoryId = $suggested && Category::whereKey($suggested)->where('type', $type)->exists() ? (int) $suggested : null;
                $categoryId = $suggestedCategoryId;
            }

            $rows[] = [
                'date' => $date,
                'type' => $type,
                'amount' => number_format($amount, 2, '.', ''),
                'description' => $description,
                'category_id' => $categoryId,
                'suggested_category_id' => $suggestedCategoryId,
                'suggested' => $suggestedCategoryId !== null,
            ];
            if (count($rows) > 500) {
                fclose($handle);

                return back()->withErrors(['csv_file' => 'A single import can contain at most 500 transaction rows.']);
            }
        }

        fclose($handle);

        if (! $rows) {
            return back()->withErrors(['csv_file' => 'The CSV contains no transaction rows to import.']);
        }

        $batchId = (string) Str::uuid();
        $request->session()->put('csv_imports.'.$batchId, $rows);
        $categories = Category::where(function ($query) use ($user) {
            $query->where('is_default', true)->orWhere('user_id', $user->id);
        })->orderBy('type')->orderBy('name')->get();

        return view('transactions.import-review', compact('rows', 'categories', 'batchId'));
    }

    public function confirmImportCsv(Request $request)
    {
        $validated = $request->validate([
            'batch_id' => ['required', 'uuid'],
            'categories' => ['required', 'array'],
            'categories.*' => ['required', 'integer', 'exists:categories,id'],
        ]);
        $key = 'csv_imports.'.$validated['batch_id'];
        $rows = $request->session()->get($key);
        abort_unless(is_array($rows) && count($rows) === count($validated['categories']), 419, 'This import preview expired. Please upload the CSV again.');
        abort_unless(array_keys($validated['categories']) === range(0, count($rows) - 1), 422, 'The import rows changed. Please upload the CSV again.');

        foreach ($rows as $index => $row) {
            $category = Category::whereKey($validated['categories'][$index])
                ->where('type', $row['type'])
                ->where(function ($query) use ($request) {
                    $query->where('is_default', true)->orWhere('user_id', $request->user()->id);
                })->first();
            if (! $category) {
                return back()->withErrors(["categories.{$index}" => 'Choose a category of the correct type that is available to your account.']);
            }
        }

        DB::transaction(function () use ($request, $rows, $validated): void {
            foreach ($rows as $index => $row) {
                Transaction::create([
                    'user_id' => $request->user()->id,
                    'category_id' => $validated['categories'][$index],
                    'amount' => $row['amount'],
                    'type' => $row['type'],
                    'description' => $row['description'],
                    'date' => $row['date'],
                    'is_recurring' => false,
                    'ai_suggested_category_id' => $row['suggested_category_id'],
                ]);
                $this->learnFromCorrection(
                    $request->user()->id,
                    $row['type'],
                    $row['description'],
                    $validated['categories'][$index],
                    $row['suggested_category_id']
                );
            }
        });

        $request->session()->forget($key);

        return redirect()->route('transactions.index')->with('success', 'Imported '.count($rows).' reviewed transactions.');
    }

    private function nextOccurrence(string $date, string $frequency): string
    {
        $next = Carbon::parse($date);

        return ($frequency === 'weekly' ? $next->addWeek() : $next->addMonthNoOverflow())->toDateString();
    }

    private function learnFromCorrection(int $userId, string $type, string $description, int $categoryId, ?int $suggestedCategoryId): void
    {
        if (! $suggestedCategoryId || (int) $suggestedCategoryId === $categoryId) {
            return;
        }

        $categoryIsVisible = static fn (int $id) => Category::whereKey($id)
            ->where('type', $type)
            ->where(function ($query) use ($userId) {
                $query->where('is_default', true)->orWhere('user_id', $userId);
            })->exists();
        if (! $categoryIsVisible($categoryId) || ! $categoryIsVisible((int) $suggestedCategoryId)) {
            return;
        }

        $normalized = trim(preg_replace('/[^a-z0-9]+/i', ' ', strtolower($description)) ?? '');
        if ($normalized === '') {
            return;
        }

        CategoryCorrection::updateOrCreate(
            [
                'user_id' => $userId,
                'type' => $type,
                'normalized_description' => mb_substr($normalized, 0, 255),
            ],
            [
                'category_id' => $categoryId,
                'description' => $description,
            ]
        );
    }

    private function visibleSuggestionId(int $userId, mixed $suggestedCategoryId, string $type): ?int
    {
        if (! $suggestedCategoryId) {
            return null;
        }

        return Category::whereKey($suggestedCategoryId)
            ->where('type', $type)
            ->where(function ($query) use ($userId) {
                $query->where('is_default', true)->orWhere('user_id', $userId);
            })->value('id');
    }
}
