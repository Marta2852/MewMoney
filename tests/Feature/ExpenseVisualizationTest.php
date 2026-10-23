
<?php

use App\Models\User;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('displays the expense chart with category data', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $account = $user->accounts()->create([
        'account_name' => 'Main Account',
        'balance' => 400,
    ]);

    $category = $user->categories()->create([
        'category_name' => 'Food',
        'category_type' => 'expense',
    ]);

    $user->transactions()->create([
        'account_id' => $account->id,
        'category_id' => $category->id,
        'transaction_type' => 'expense',
        'amount' => 50,
        'description' => 'Groceries',
        'transaction_date' => now()->toDateString(),
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
    $response->assertSee('expensesChart');
    $response->assertSee('Food');
});
