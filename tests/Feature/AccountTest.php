
<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows a user to create a financial account', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('accounts.store'), [
        'account_name' => 'Everyday Account',
        'balance' => 250,
    ]);

    $response->assertRedirect(route('accounts'));

    $this->assertDatabaseHas('accounts', [
        'user_id' => $user->id,
        'account_name' => 'Everyday Account',
        'balance' => 250,
    ]);
});

it('rejects an account with invalid data', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('accounts.store'), [
        'account_name' => '',
        'balance' => -10,
    ]);

    $response->assertSessionHasErrors([
        'account_name',
        'balance',
    ]);
});
