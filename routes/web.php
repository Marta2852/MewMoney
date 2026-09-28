<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SavingsGoalController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminController;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\SavingsGoal;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/accounts', [AccountController::class, 'index'])
    ->middleware(['auth'])
    ->name('accounts');

Route::post('/accounts', [AccountController::class, 'store'])
    ->middleware(['auth'])
    ->name('accounts.store');

Route::get('/transactions', [TransactionController::class, 'index'])
    ->middleware(['auth'])
    ->name('transactions');
Route::post('/transactions', [TransactionController::class, 'store'])
    ->middleware(['auth'])
    ->name('transactions.store');

Route::post('/categories', [CategoryController::class, 'store'])
    ->middleware(['auth'])
    ->name('categories.store');

Route::get('/savings', [SavingsGoalController::class, 'index'])
    ->middleware(['auth'])
    ->name('savings');

Route::post('/savings', [SavingsGoalController::class, 'store'])
    ->middleware(['auth'])
    ->name('savings.store');

Route::post('/savings/add', [SavingsGoalController::class, 'addMoney'])
    ->middleware(['auth'])
    ->name('savings.add');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/admin', [AdminController::class, 'index'])
    ->middleware(['auth'])
    ->name('admin.index');

Route::get('/admin/users/{user}', [AdminController::class, 'show'])
    ->middleware('auth')
    ->name('admin.users.show');

Route::patch('/admin/users/{user}/role', [AdminController::class, 'updateRole'])
    ->middleware('auth')
    ->name('admin.users.updateRole');

Route::delete('/admin/users/{user}', [AdminController::class, 'destroy'])
    ->middleware('auth')
    ->name('admin.users.destroy');

require __DIR__.'/auth.php';
