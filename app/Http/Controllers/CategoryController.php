<?php

namespace App\Http\Controllers;

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
}
