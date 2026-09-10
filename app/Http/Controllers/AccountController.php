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
}
