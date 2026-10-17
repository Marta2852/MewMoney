<?php

namespace App\Http\Controllers;
use App\Models\Category;

use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'category_name' => 'required|string|max:255',
            'category_type' => 'required|in:income,expense',
        ]);

        auth()->user()->categories()->create([
            'category_name' => $request->category_name,
            'category_type' => $request->category_type,
        ]);

        return redirect()->route('transactions');
    }

    

    public function index()
    {
        $categories = Category::where('user_id', auth()->id())
            ->orderBy('category_name')
            ->get();

        return view('categories.index', compact('categories'));
    }

    public function edit($id)
    {
        $category = Category::where('user_id', auth()->id())
            ->findOrFail($id);

        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, $id)
    {
        $category = Category::where('user_id', auth()->id())
            ->findOrFail($id);

        $validated = $request->validate([
            'category_name' => 'required|string|max:255',
            'category_type' => 'required|in:income,expense',
        ]);

        $category->update($validated);

        return redirect()->route('categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy($id)
    {
        $category = Category::where('user_id', auth()->id())
            ->findOrFail($id);

        if ($category->transactions()->exists()) {
            return redirect()->route('categories.index')
                ->with('error', 'Cannot delete a category that has transactions.');
        }

        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Category deleted successfully.');
    }

}
