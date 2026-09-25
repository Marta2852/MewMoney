<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
{
    $period = $request->input('period', 'month');
    $type = $request->input('type', 'all');

    $date = $request->input(
        'date',
        $period === 'month'
            ? now()->format('Y-m')
            : now()->format('Y-m-d')        
    );

    $query = auth()->user()->transactions()
        ->with(['account', 'category']);

    if ($type !== 'all') {
        $query->where('transaction_type', $type);
    }

    if ($period === 'month') {
        $query->whereMonth('transaction_date', date('m', strtotime($date)))
              ->whereYear('transaction_date', date('Y', strtotime($date)));
    }

    if ($period === 'day') {
        $query->whereDate('transaction_date', $date);
    }

    if ($period === 'week') {
        $selectedDate = \Carbon\Carbon::parse($date);

        $query->whereBetween('transaction_date', [
            $selectedDate->copy()->startOfWeek(),
            $selectedDate->copy()->endOfWeek()
        ]);
    }

    $transactions = $query
        ->latest('transaction_date')
        ->paginate(10)
        ->withQueryString();

    $accounts = auth()->user()->accounts;
    $categories = auth()->user()->categories;

    return view('transactions.index', compact(
        'transactions',
        'accounts',
        'categories',
        'period',
        'date',
        'type'
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
        if (
            $request->transaction_type === 'expense' &&
            $account->balance < $request->amount
        ) {
            return back()->withErrors([
                'amount' => 'Insufficient funds in the selected account.',
            ])->withInput();
        }

        $category = auth()->user()->categories()->findOrFail($request->category_id);

        if ($category->category_type !== $request->transaction_type) {
            return back()->withErrors([
                'category_id' => 'The selected category does not match the transaction type.',
            ])->withInput();
        }

        DB::transaction(function () use ($request, $account, $category) {
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
        });

        return redirect()->route('transactions');
    }
}