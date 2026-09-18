<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $totalBalance = $user->accounts()->sum('balance');

        $incomeThisMonth = $user->transactions()
            ->where('transaction_type', 'income')
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->sum('amount');
        
        $expensesThisMonth = $user->transactions()
            ->where('transaction_type', 'expense')
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->sum('amount');

        $savedThisMonth = $user->transactions()
            ->where('transaction_type', 'transfer')
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->sum('amount');

        $expensesByCategory = $user->transactions()
            ->with('category')
            ->where('transaction_type', 'expense')
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->get()
            ->groupBy(function ($transaction) {
                return $transaction->category
                    ? $transaction->category->category_name
                    : 'Uncategorized';
            })
            ->map(function ($transactions) {
                return $transactions->sum('amount');
            });

            return view('dashboard', compact(
                'totalBalance',
                'incomeThisMonth', 
                'expensesThisMonth',
                'savedThisMonth',
                'expensesByCategory'
            ));
    }
}
