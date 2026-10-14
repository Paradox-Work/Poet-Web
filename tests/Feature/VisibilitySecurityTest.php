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


test('former group members cannot edit or delete comments after losing access', function () {
    $owner = User::factory()->create();
    $formerMember = User::factory()->create();
    $group = poetTestGroup($owner);

    $membership = poetTestAddGroupUser(
        $group,
        $formerMember
    );

    $post = poetTestPost($owner, [
        'group_id' => $group->id,
    ]);

    $comment = Comment::create([
        'post_id' => $post->id,
        'user_id' => $formerMember->id,
        'comment' => 'Old member comment',
    ]);

    $membership->delete();

    $this
        ->actingAs($formerMember)
        ->putJson(
            route('post.comment.update', $comment),
            ['comment' => 'Unauthorized edit']
        )
        ->assertForbidden();

    $this
        ->actingAs($formerMember)
        ->deleteJson(
            route('post.comment.delete', $comment)
        )
        ->assertNotFound();

    expect($comment->fresh()->comment)
        ->toBe('Old member comment');
});
