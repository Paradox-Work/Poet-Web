<?php

use App\Models\User;

it('authenticated user can create a post with a title and category', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post('/posts', [
            'title' => 'Night Bloom',
            'body' => 'A quiet poem under moonlight.',
            'category' => 'love',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $this->assertDatabaseHas('posts', [
        'user_id' => $user->id,
        'title' => 'Night Bloom',
        'body' => 'A quiet poem under moonlight.',
        'category' => 'love',
    ]);
});
