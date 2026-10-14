<?php

use App\Models\Post;
use App\Models\User;

test('username updates must remain unique', function () {
    $first = User::factory()->create();
    $second = User::factory()->create();

    $this
        ->actingAs($second)
        ->patch(
            route('profile.update'),
            [
                'name' => $second->name,
                'username' => $first->username,
                'email' => $second->email,
            ]
        )
        ->assertSessionHasErrors('username');
});

test('empty publications are rejected', function () {
    $user = User::factory()->create();

    $this
        ->actingAs($user)
        ->post(
            route('post.create'),
            poetTestPostPayload([
                'body' => null,
            ])
        )
        ->assertSessionHasErrors('body');

    expect(Post::query()->count())->toBe(0);
});

test('deleting an established account anonymizes it and hides its publications', function () {
    $user = User::factory()->create();
    $post = poetTestPost($user);

    $this
        ->actingAs($user)
        ->delete(
            route('profile.destroy'),
            ['password' => 'password']
        )
        ->assertRedirect('/');

    $this->assertGuest();

    expect(
        User::query()->find($user->id)
    )->toBeNull();

    expect(
        User::withTrashed()
            ->findOrFail($user->id)
            ->name
    )->toBe('Deleted user');

    expect(
        Post::withTrashed()
            ->findOrFail($post->id)
            ->trashed()
    )->toBeTrue();
});
