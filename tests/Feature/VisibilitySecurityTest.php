<?php

use App\Models\Comment;
use App\Models\User;

test('outsiders cannot react to a private group publication', function () {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();
    $group = poetTestGroup($owner);
    $post = poetTestPost($owner, ['group_id' => $group->id]);

    $this
        ->actingAs($outsider)
        ->postJson(
            route('post.reaction', $post),
            ['reaction' => 'like']
        )
        ->assertNotFound();
});

test('outsiders cannot comment on a private group publication', function () {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();
    $group = poetTestGroup($owner);
    $post = poetTestPost($owner, ['group_id' => $group->id]);

    $this
        ->actingAs($outsider)
        ->postJson(
            route('post.comment.create', $post),
            ['comment' => 'Should not be accepted']
        )
        ->assertNotFound();

    expect(Comment::query()->count())->toBe(0);
});

test('outsiders cannot react to comments inside a private group publication', function () {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();
    $group = poetTestGroup($owner);
    $post = poetTestPost($owner, ['group_id' => $group->id]);

    $comment = Comment::create([
        'post_id' => $post->id,
        'user_id' => $owner->id,
        'comment' => 'Members only',
    ]);

    $this
        ->actingAs($outsider)
        ->postJson(
            route('post.comment.reaction', $comment),
            ['reaction' => 'like']
        )
        ->assertNotFound();
});
