<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Transaction;
use App\Models\SavingsGoal;


class AdminController extends Controller
{
    public function index()
    {

        if (!auth()->user()->is_admin) {
            abort(403);
        }

        $totalUsers = User::count();

        $newUsersThisMonth = User::whereMonth(
            'created_at',
            now()->month
        )
        ->whereYear(
            'created_at',
            now()->year
        )
        ->count();

        $activeUsers = User::where('is_admin',false)->count();

        $search = request('search');

        $users = User::when($search, function ($query, $search) {
            $query->where('name', 'like', '%' . $search . '%')
                ->orWhere('email', 'like', '%' . $search . '%');
        })
        ->latest()
        ->get();

        $registrations = User::selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->whereYear('created_at', now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $recentUsers = User::latest()->take(3)->get();

        $recentTransactions = Transaction::with('user')
            ->latest()
            ->take(3)
            ->get();

        $recentSavingsGoals = SavingsGoal::with('user')
            ->latest()
            ->take(3)
            ->get();

        return view('admin.index', compact(
            'totalUsers',
            'newUsersThisMonth',
            'activeUsers',
            'users',
            'registrations',
            'recentUsers',
            'recentTransactions',
            'recentSavingsGoals'
        ));
    }

    public function show(User $user)
    {
        if (!auth()->user()->is_admin) {
            abort(403);
        }

        $accountsCount = $user->accounts()->count();
        $transactionsCount = $user->transactions()->count();
        $savingsGoalsCount = $user->savingsGoals()->count();

        return view('admin.user', compact(
            'user',
            'accountsCount',
            'transactionsCount',
            'savingsGoalsCount'
            ));
    }

    public function updateRole(User $user)
    {
        if (!auth()->user()->is_admin) {
            abort(403);
        }

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot change your own role.');
        }

        $user->is_admin = !$user->is_admin;
        $user->save();

        return back()->with('success', 'User role updated.');
    }

        public function destroy(User $user)
    {
        if (!auth()->user()->is_admin) {
            abort(403);
        }

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        DB::transaction(function () use ($user) {

            $user->transactions()->delete();
            $user->savingsGoals()->delete();
            $user->accounts()->delete();
            $user->categories()->delete();

            $user->delete();
        });

        return redirect()
            ->route('admin.index')
            ->with('success', 'User deleted successfully.');
    }
}