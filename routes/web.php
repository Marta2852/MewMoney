<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountController;
use App\Models\Account;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', function () {
    $totalBalance = auth()->user()->accounts()->sum('balance');

    return view('dashboard', compact('totalBalance'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/accounts', [AccountController::class, 'index'])
    ->middleware(['auth'])
    ->name('accounts');

Route::post('/accounts', [AccountController::class, 'store'])
    ->middleware(['auth'])
    ->name('accounts.store');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
