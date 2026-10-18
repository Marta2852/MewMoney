<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Account;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_add_an_income_transaction(): void
    {
        $user = User::factory()->create();

        $account = Account::create([
            'user_id' => $user->id,
            'account_name' => 'Main account',
            'balance' => 100,
        ]);

        $category = Category::create([
            'user_id' => $user->id,
            'category_name' => 'Salary',
            'category_type' => 'income',
        ]);

        $response = $this->actingAs($user)->post(
            route('transactions.store'),
            [
                'transaction_type' => 'income',
                'amount' => 50,
                'description' => 'Monthly salary',
                'transaction_date' => now()->toDateString(),
                'account_id' => $account->id,
                'category_id' => $category->id,
            ]
        );

        $response->assertRedirect(route('transactions'));

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'account_id' => $account->id,
            'category_id' => $category->id,
            'transaction_type' => 'income',
            'amount' => 50,
            'description' => 'Monthly salary',
        ]);

        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'balance' => 150,
        ]);
    }
}
