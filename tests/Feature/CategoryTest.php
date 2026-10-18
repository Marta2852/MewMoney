
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
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('categories', [
        'user_id' => $user->id,
        'category_name' => 'Food',
        'category_type' => 'expense',
    ]);
});
