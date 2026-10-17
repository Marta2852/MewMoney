<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class SavingsGoalController extends Controller
{
    public function index()
{
    $savingsGoals = auth()->user()->savingsGoals()->get();
    
    $activeGoals = $savingsGoals->filter(function ($goal) {
        return $goal->current_amount < $goal->target_amount;
    });
    $completedGoals = $savingsGoals->filter(function ($goal) {
        return $goal->current_amount >= $goal->target_amount;
    });

    $savingsTransactions = auth()->user()->transactions()
        ->where('transaction_type', 'transfer')
        ->whereNotNull('savings_goal_id')
        ->orderBy('transaction_date')
        ->get();

    $accounts = auth()->user()->accounts;
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
                'added' => (float) $transaction->amount,
            ];
        }

        $savingsGrowth[$goal->goal_name] = $goalData;
    }

    return view('savings.index', compact(
        'savingsGoals',
        'activeGoals',
        'completedGoals',
        'savingsGrowth',
        'accounts'
    ));
}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'target_amount' => 'required|numeric|min:0.01',
            'current_amount' => 'nullable|numeric|min:0',
            'target_date' => 'nullable|date',
            'starting_account_id' => 'nullable|integer',
        ]);

        $startingAmount = (float) ($validated['current_amount'] ?? 0);
        $user = auth()->user();

        if ($startingAmount > 0 && empty($validated['starting_account_id'])) {
            return back()
                ->withErrors(['starting_account_id' => 'Choose an account for the starting amount.'])
                ->withInput();
        }

        if ($startingAmount > (float) $validated['target_amount']) {
            return back()
                ->withErrors(['current_amount' => 'The starting amount cannot exceed the target amount.'])
                ->withInput();
        }

        DB::transaction(function () use ($user, $validated, $startingAmount) {
            $account = null;

            if ($startingAmount > 0) {
                $account = $user->accounts()
                    ->lockForUpdate()
                    ->findOrFail($validated['starting_account_id']);

                if ((float) $account->balance < $startingAmount) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'current_amount' => 'The selected account does not have enough money.',
                    ]);
                }
            }

            $goal = $user->savingsGoals()->create([
                'goal_name' => $validated['name'],
                'target_amount' => $validated['target_amount'],
                'current_amount' => 0,
                'target_date' => $validated['target_date'] ?? null,
            ]);

            if ($startingAmount > 0) {
                $account->decrement('balance', $startingAmount);

                Transaction::create([
                    'user_id' => $user->id,
                    'account_id' => $account->id,
                    'savings_goal_id' => $goal->id,
                    'transaction_type' => 'transfer',
                    'amount' => $startingAmount,
                    'description' => 'Initial transfer to ' . $goal->goal_name,
                    'transaction_date' => now(),
                ]);

                $goal->update([
                    'current_amount' => $startingAmount,
                ]);
            }
        });

        return redirect()->route('savings');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'goal_name' => 'required|string|max:255',
            'target_amount' => 'required|numeric|min:0.01',
            'target_date' => 'nullable|date',
        ]);

        $user = $request->user();

        DB::transaction(function () use ($user, $id, $validated) {
            $goal = $user->savingsGoals()
                ->lockForUpdate()
                ->findOrFail($id);

            if ((float) $validated['target_amount'] < (float) $goal->current_amount) {
                throw ValidationException::withMessages([
                    'target_amount' => 'The target amount cannot be lower than the amount already saved.',
                ]);
            }

            $goal->update([
                'goal_name' => $validated['goal_name'],
                'target_amount' => $validated['target_amount'],
                'target_date' => $validated['target_date'] ?? null,
            ]);
        });

        return redirect()->route('savings')
            ->with('success', 'Savings goal updated successfully.');
    }

    public function destroy($id)
    {
        $user = auth()->user();

        DB::transaction(function () use ($user, $id) {
            $goal = $user->savingsGoals()->findOrFail($id);

            $transfers = $user->transactions()
                ->where('savings_goal_id', $goal->id)
                ->where('transaction_type', 'transfer')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $refundsByAccount = $transfers
                ->groupBy('account_id')
                ->map(fn ($accountTransfers) => (float) $accountTransfers->sum('amount'));

            $accountIds = $refundsByAccount->keys()->sort()->values();
            $accounts = $user->accounts()
                ->whereIn('id', $accountIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $goal = $user->savingsGoals()
                ->lockForUpdate()
                ->findOrFail($id);

            foreach ($accounts as $account) {
                $account->balance = round(
                    (float) $account->balance + $refundsByAccount->get($account->id, 0),
                    2
                );
                $account->save();
            }

            $goal->delete();
        });

        return redirect()->route('savings')
            ->with('success', 'Savings goal deleted successfully.');
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