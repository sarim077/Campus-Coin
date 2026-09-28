<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $defaultIncomeCategories = Category::where('is_default', true)->where('type', 'income')->get();
        $defaultExpenseCategories = Category::where('is_default', true)->where('type', 'expense')->get();

        $userIncomeCategories = Category::where('user_id', $user->id)->where('type', 'income')->get();
        $userExpenseCategories = Category::where('user_id', $user->id)->where('type', 'expense')->get();

        return view('categories.index', compact(
            'defaultIncomeCategories',
            'defaultExpenseCategories',
            'userIncomeCategories',
            'userExpenseCategories'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:income,expense'],
            'icon' => ['nullable', 'string', 'max:100'],
        ]);

        Category::create([
            'name' => $request->name,
            'type' => $request->type,
            'is_default' => false,
            'user_id' => auth()->id(),
            'icon' => $request->icon ?? 'fas fa-tag',
        ]);

        return redirect()->route('categories.index')->with('success', 'Custom category created successfully!');
    }

    public function update(Request $request, Category $category)
    {
        if ($category->is_default || $category->user_id !== auth()->id()) {
            return redirect()->route('categories.index')->with('error', 'Default or unauthorized category cannot be updated.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:income,expense'],
            'icon' => ['nullable', 'string', 'max:100'],
        ]);

        $category->update([
            'name' => $request->name,
            'type' => $request->type,
            'icon' => $request->icon ?? $category->icon,
        ]);

        return redirect()->route('categories.index')->with('success', 'Category updated.');
    }

    public function destroy(Category $category)
    {
        if ($category->is_default || $category->user_id !== auth()->id()) {
            return redirect()->route('categories.index')->with('error', 'System default categories cannot be deleted.');
        }

        $category->delete();
        return redirect()->route('categories.index')->with('success', 'Custom category deleted.');
    }
}
