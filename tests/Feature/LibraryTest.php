<?php

use App\Models\Bookmark;
use App\Models\ReadingList;
use App\Models\User;

test('users can toggle bookmarks on visible publications', function () {
    $user = User::factory()->create();
    $author = User::factory()->create();
    $post = poetTestPost($author);

    $this
        ->actingAs($user)
        ->postJson(route('bookmarks.toggle', $post))
        ->assertOk()
        ->assertJson(['bookmarked' => true]);

    expect(
        Bookmark::query()
            ->where('user_id', $user->id)
            ->where('post_id', $post->id)
            ->exists()
    )->toBeTrue();

    $this
        ->actingAs($user)
        ->postJson(route('bookmarks.toggle', $post))
        ->assertOk()
        ->assertJson(['bookmarked' => false]);

    expect(Bookmark::query()->count())->toBe(0);
});

test('users cannot bookmark protected private group publications', function () {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();
    $group = poetTestGroup($owner, ['visibility' => 'private']);
    $post = poetTestPost($owner, ['group_id' => $group->id]);

    $this
        ->actingAs($outsider)
        ->postJson(route('bookmarks.toggle', $post))
        ->assertNotFound();
});

test('reading lists are private to their owner', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $author = User::factory()->create();
    $post = poetTestPost($author);

    $list = ReadingList::query()->create([
        'user_id' => $owner->id,
        'name' => 'Favorites',
    ]);

    $this
        ->actingAs($owner)
        ->postJson(
            route('reading-lists.posts.add', [
                'readingList' => $list,
                'post' => $post,
            ])
        )
        ->assertOk();

    expect($list->posts()->whereKey($post->id)->exists())
        ->toBeTrue();

    $this
        ->actingAs($other)
        ->deleteJson(
            route('reading-lists.posts.remove', [
                'readingList' => $list,
                'post' => $post,
            ])
        )
        ->assertForbidden();
});
