<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can create an expense category', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/categories', [
            'category_name' => 'Food',
            'category_type' => 'expense',
        ])
        ->assertRedirect(route('transactions'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('categories', [
        'user_id' => $user->id,
        'category_name' => 'Food',
        'category_type' => 'expense',
    ]);
});

test('user can create an income category', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/categories', [
            'category_name' => 'Salary',
            'category_type' => 'income',
        ])
        ->assertRedirect(route('transactions'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('categories', [
        'user_id' => $user->id,
        'category_name' => 'Salary',
        'category_type' => 'income',
    ]);
});

test('category creation rejects invalid data', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/categories', [
            'category_name' => '',
            'category_type' => 'invalid',
        ])
        ->assertSessionHasErrors([
            'category_name',
            'category_type',
        ]);

    $this->assertDatabaseCount('categories', 0);
});

test('guest cannot create a category', function () {
    $this->post('/categories', [
        'category_name' => 'Food',
        'category_type' => 'expense',
    ])->assertRedirect('/login');

    $this->assertDatabaseCount('categories', 0);
});
