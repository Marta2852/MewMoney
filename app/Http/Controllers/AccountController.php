<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index()
    {

        $accounts = auth()->user()->accounts;
        $savings = auth()->user()->savingsGoals()->sum('current_amount');

        return view('accounts.index', compact('accounts', 'savings'));
    }

    public function edit()
    {
        $accounts = auth()->user()->accounts;

        return view('accounts.edit', compact('accounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'account_name' => 'required|string|max:255',
            'balance' => 'required|numeric|min:0',
        ]);

        auth()->user()->accounts()->create([
            'account_name' => $request->account_name,
            'balance' => $request->balance,
        ]);

        return redirect()->route('accounts');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'account_name' => 'required|string|max:255',
        ]);

        $account = $request->user()->accounts()->findOrFail($id);
        $account->update($validated);

        return redirect()->route('accounts.edit')
            ->with('success', 'Account name updated successfully.');
    }
}
