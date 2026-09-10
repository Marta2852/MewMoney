<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;

class SavingsGoalController extends Controller
{
    public function index()
    {
        $savingsGoals = auth()->user()->savingsGoals()->get();

        return view('savings.index', compact('savingsGoals'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'target_amount' => 'required|numeric|min:0.01',
            'current_amount' => 'nullable|numeric|min:0',
            'target_date' => 'nullable|date',
        ]);

        auth()->user()->savingsGoals()->create([
            'goal_name' => $request->name,
            'target_amount' => $request->target_amount,
            'current_amount' => $request->current_amount ?? 0,
            'target_date' => $request->target_date,
        ]);

        return redirect()->route('savings');
    }

    public function addMoney(Request $request)
    {
        $request->validate([
            'goal_id' => 'required|exists:savings_goals,id',
            'account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $goal = auth()->user()->savingsGoals()->findOrFail($request->goal_id);
        $account = auth()->user()->accounts()->findOrFail($request->account_id);

        if ($account->balance < $request->amount) {
            return redirect()->route('savings')
            ->with('error', 'Insufficient funds in the selected account.');
        }

        $account->decrement('balance', $request->amount);
        $goal->increment('current_amount', $request->amount);
        Transaction::create([
            'user_id' => auth()->id(),
            'account_id' => $account->id,
            'savings_goal_id' => $goal->id,
            'transaction_type' => 'expense',
            'amount' => $request->amount,
            'description' => 'Transfer to ' . $goal->goal_name,
            'transaction_date' => now(),
        ]);

       

        return redirect()->route('savings');
    }
}