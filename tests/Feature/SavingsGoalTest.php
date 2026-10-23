<?php

use App\Models\User;
use App\Models\Account;
use App\Models\SavingsGoal;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can create a savings goal', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('savings.store'), [
            'name' => 'New Laptop',
            'target_amount' => 1000,
            'current_amount' => 0,
        ])
        ->assertRedirect(route('savings'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('savings_goals', [
        'user_id' => $user->id,
        'goal_name' => 'New Laptop',
        'target_amount' => 1000,
        'current_amount' => 0,
    ]);
});

test('user can add money to a savings goal', function () {
    $user = User::factory()->create();

    $account = $user->accounts()->create([
        'account_name' => 'Main account',
        'balance' => 100,
    ]);

    $goal = $user->savingsGoals()->create([
        'goal_name' => 'New Laptop',
        'target_amount' => 200,
        'current_amount' => 0,
    ]);

    $this->actingAs($user)
        ->post(route('savings.add'), [
            'goal_id' => $goal->id,
            'account_id' => $account->id,
            'amount' => 30,
        ])
        ->assertRedirect(route('savings'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('accounts', [
        'id' => $account->id,
        'balance' => 70,
    ]);

    $this->assertDatabaseHas('savings_goals', [
        'id' => $goal->id,
        'current_amount' => 30,
    ]);

    $this->assertDatabaseHas('transactions', [
        'user_id' => $user->id,
        'account_id' => $account->id,
        'savings_goal_id' => $goal->id,
        'transaction_type' => 'transfer',
        'amount' => 30,
    ]);
});

test('adding money is rejected when the account has insufficient funds', function () {
    $user = User::factory()->create();

    $account = $user->accounts()->create([
        'account_name' => 'Main account',
        'balance' => 20,
    ]);

    $goal = $user->savingsGoals()->create([
        'goal_name' => 'New Laptop',
        'target_amount' => 200,
        'current_amount' => 0,
    ]);

    $this->actingAs($user)
        ->post(route('savings.add'), [
            'goal_id' => $goal->id,
            'account_id' => $account->id,
            'amount' => 50,
        ])
        ->assertSessionHasErrors('amount');

    $this->assertDatabaseHas('accounts', [
        'id' => $account->id,
        'balance' => 20,
    ]);

    $this->assertDatabaseHas('savings_goals', [
        'id' => $goal->id,
        'current_amount' => 0,
    ]);

    $this->assertDatabaseCount('transactions', 0);
});

test('savings goal creation rejects invalid data', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('savings.store'), [
            'name' => '',
            'target_amount' => 0,
        ])
        ->assertSessionHasErrors([
            'name',
            'target_amount',
        ]);

    $this->assertDatabaseCount('savings_goals', 0);
});

test('user cannot lower the savings target below the saved amount', function () {
    $user = User::factory()->create();

    $goal = $user->savingsGoals()->create([
        'goal_name' => 'New Laptop',
        'target_amount' => 500,
        'current_amount' => 300,
    ]);

    $this->actingAs($user)
        ->put(route('savings.update', $goal->id), [
            'goal_name' => 'New Laptop',
            'target_amount' => 200,
        ])
        ->assertSessionHasErrors('target_amount');

    $this->assertDatabaseHas('savings_goals', [
        'id' => $goal->id,
        'target_amount' => 500,
        'current_amount' => 300,
    ]);
});
