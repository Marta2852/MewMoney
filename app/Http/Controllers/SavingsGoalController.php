<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class SavingsGoalController extends Controller
{
    public function index()
{
    $savingsGoals = auth()->user()->savingsGoals()->get();

    $savingsTransactions = auth()->user()->transactions()
        ->where('transaction_type', 'transfer')
        ->whereNotNull('savings_goal_id')
        ->orderBy('transaction_date')
        ->get();

    $savingsGrowth = [];

    foreach ($savingsGoals as $goal) {

        $totalSaved = 0;
        $goalData = [];

        $goalTransactions = $savingsTransactions
            ->where('savings_goal_id', $goal->id);

        foreach ($goalTransactions as $transaction) {

            $totalSaved += $transaction->amount;

            $goalData[] = [
                'date' => \Carbon\Carbon::parse($transaction->transaction_date)
                    ->format('M d'),
                'month' => \Carbon\Carbon::parse($transaction->transaction_date)
                    ->format('M Y'),
                'amount' => $totalSaved,
            ];
        }

        $savingsGrowth[$goal->goal_name] = $goalData;
    }

    return view('savings.index', compact(
        'savingsGoals',
        'savingsGrowth'
    ));
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
        
        $remaining = (float) $goal->target_amount - (float) $goal->current_amount;

        if ($request->amount > $remaining) {
            return back()->withErrors([
                'amount' => 'You can only add €' . number_format($remaining, 2) . ' to this goal.',
            ])->withInput();
        }
    

        if ($account->balance < $request->amount) {
            return back()->withErrors([
                'amount' => 'Insufficient funds in the selected account.',
            ])->withInput();
        }

        DB::transaction(function () use ($request, $account, $goal) {
            $account->decrement('balance', $request->amount);
            $goal->increment('current_amount', $request->amount);
            Transaction::create([
                'user_id' => auth()->id(),
                'account_id' => $account->id,
                'savings_goal_id' => $goal->id,
                'transaction_type' => 'transfer',
                'amount' => $request->amount,
                'description' => 'Transfer to ' . $goal->goal_name,
                'transaction_date' => now(),
            ]);
        });
       
        return redirect()->route('savings');
    }
}