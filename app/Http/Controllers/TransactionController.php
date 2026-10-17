<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
    $savingsGoals = auth()->user()->savingsGoals;

    return view('transactions.index', compact(
        'transactions',
        'accounts',
        'categories',
        'savingsGoals',
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

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'transaction_type' => 'required|in:income,expense,transfer',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
            'transaction_date' => 'required|date',
            'account_id' => 'required|integer',
            'category_id' => 'required_if:transaction_type,income,expense|nullable|integer',
            'savings_goal_id' => 'required_if:transaction_type,transfer|nullable|integer',
        ]);

        $user = auth()->user();

        $user->accounts()->findOrFail($validated['account_id']);
        $category = null;
        $savingsGoal = null;

        if ($validated['transaction_type'] === 'transfer') {
            $savingsGoal = $user->savingsGoals()->findOrFail($validated['savings_goal_id']);
        } else {
            $category = $user->categories()->findOrFail($validated['category_id']);

            if ($category->category_type !== $validated['transaction_type']) {
                return back()->withErrors([
                    'category_id' => 'The selected category does not match the transaction type.',
                ])->withInput();
            }
        }

        DB::transaction(function () use ($user, $id, $validated) {
            $transaction = $user->transactions()
                ->lockForUpdate()
                ->findOrFail($id);

            if (($transaction->transaction_type === 'transfer') !== ($validated['transaction_type'] === 'transfer')) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'transaction_type' => 'A transaction cannot be changed between transfer and non-transfer types.',
                ]);
            }

            $oldAccountId = $transaction->account_id;
            $newAccountId = (int) $validated['account_id'];

            $accountIds = array_unique([$oldAccountId, $newAccountId]);
            sort($accountIds);

            $accounts = $user->accounts()
                ->whereIn('id', $accountIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $oldAccount = $accounts->get($oldAccountId);
            $newAccount = $accounts->get($newAccountId);

            if ($transaction->transaction_type === 'transfer') {
                $goalIds = array_unique([
                    $transaction->savings_goal_id,
                    (int) $validated['savings_goal_id'],
                ]);
                sort($goalIds);

                $goals = $user->savingsGoals()
                    ->whereIn('id', $goalIds)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $oldGoal = $goals->get($transaction->savings_goal_id);
                $newGoal = $goals->get((int) $validated['savings_goal_id']);

                if (! $oldGoal || ! $newGoal) {
                    abort(404);
                }

                $oldAmount = (float) $transaction->amount;
                $newAmount = (float) $validated['amount'];

                $oldAccount->balance += $oldAmount;
                $oldGoal->current_amount -= $oldAmount;

                if ((float) $newAccount->balance < $newAmount) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'amount' => 'Insufficient funds in the selected account.',
                    ]);
                }

                if (round((float) $newGoal->current_amount + $newAmount, 2) > (float) $newGoal->target_amount) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'amount' => 'The transfer would exceed the savings goal target.',
                    ]);
                }

                $newAccount->balance -= $newAmount;
                $newGoal->current_amount += $newAmount;

                foreach ($accounts as $account) {
                    $account->save();
                }

                foreach ($goals as $goal) {
                    $goal->save();
                }

                $transaction->update([
                    'account_id' => $newAccountId,
                    'category_id' => null,
                    'savings_goal_id' => $newGoal->id,
                    'transaction_type' => 'transfer',
                    'amount' => $validated['amount'],
                    'description' => $validated['description'] ?? null,
                    'transaction_date' => $validated['transaction_date'],
                ]);

                return;
            }

            if ($transaction->transaction_type === 'income') {
                $oldAccount->balance -= $transaction->amount;
            } else {
                $oldAccount->balance += $transaction->amount;
            }

            if (
                $validated['transaction_type'] === 'expense' &&
                $newAccount->balance < $validated['amount']
            ) {

            throw \Illuminate\Validation\ValidationException::withMessages([
                'amount' => 'Insufficient funds in the selected account.',
            ]);

            }

            if ($validated['transaction_type'] === 'income') {
                $newAccount->balance += $validated['amount'];
            } else {
                $newAccount->balance -= $validated['amount'];
            }

            foreach ($accounts as $account) {
                $account->save();
            }

            $transaction->update([
                'account_id' => $newAccountId,
                'category_id' => $validated['category_id'],
                'savings_goal_id' => null,
                'transaction_type' => $validated['transaction_type'],
                'amount' => $validated['amount'],
                'description' => $validated['description'] ?? null,
                'transaction_date' => $validated['transaction_date'],
            ]);
        });

        if ($validated['transaction_type'] === 'transfer') {
            return redirect()->route('savings')
                ->with('success', 'Savings transfer updated successfully.');
        }

        return redirect()->route('transactions')
            ->with('success', 'Transaction updated successfully.');
    }

    public function destroy($id)
    {
        $user = auth()->user();

        DB::transaction(function () use ($user, $id) {
            $transaction = $user->transactions()
                ->lockForUpdate()
                ->findOrFail($id);

            $account = $user->accounts()
                ->where('id', $transaction->account_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($transaction->transaction_type === 'income') {
                $account->balance -= $transaction->amount;
            } elseif ($transaction->transaction_type === 'expense') {
                $account->balance += $transaction->amount;
            } elseif ($transaction->transaction_type === 'transfer') {
                $goal = $user->savingsGoals()
                    ->where('id', $transaction->savings_goal_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $account->balance += $transaction->amount;
                $goal->current_amount -= $transaction->amount;
                $goal->save();
            }

            $account->save();
            $transaction->delete();
        });

        return redirect()->route('transactions')
            ->with('success', 'Transaction deleted successfully.');
    }
}