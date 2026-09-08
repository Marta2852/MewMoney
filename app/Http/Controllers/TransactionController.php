<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = auth()->user()->transactions()
            ->with(['account', 'category'])
            ->latest('transaction_date')
            ->get();

        $accounts = auth()->user()->accounts;
        $categories = auth()->user()->categories;

        return view('transactions.index', compact(
            'transactions',
            'accounts',
            'categories'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'transaction_type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
            'transaction_date' => 'required|date',
            'account_id' => 'required|exists:accounts,id',
            'category_id' => 'required|exists:categories,id',
        ]);

        $account = auth()->user()->accounts()->findOrFail($request->account_id);

        $category = auth()->user()->categories()->findOrFail($request->category_id);

        if ($category->category_type !== $request->transaction_type) {
            return back()->withErrors([
                'category_id' => 'The selected category does not match the transaction type.',
            ])->withInput();
        }

        auth()->user()->transactions()->create([
            'account_id' => $account->id,
            'category_id' => $category->id,
            'transaction_type' => $request->transaction_type,
            'amount' => $request->amount,
            'description' => $request->description,
            'transaction_date' => $request->transaction_date,
        ]);

        if ($request->transaction_type === 'income') {
            $account->balance += $request->amount;
        } else {
            $account->balance -= $request->amount;
        }

        $account->save();

        return redirect()->route('transactions');
    }
}