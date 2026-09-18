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

it('authenticated user can comment on a post', function () {
    $postAuthor = User::factory()->create();
    $commenter = User::factory()->create();
    $post = \App\Models\Post::create([
        'user_id' => $postAuthor->id,
        'title' => 'Moonlight',
        'body' => 'Poem body',
        'category' => 'love',
    ]);

    $response = $this
        ->actingAs($commenter)
        ->post('/posts/' . $post->id . '/comments', [
            'comment' => 'Beautiful poem.',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $this->assertDatabaseHas('comments', [
        'post_id' => $post->id,
        'user_id' => $commenter->id,
        'comment' => 'Beautiful poem.',
    ]);
});