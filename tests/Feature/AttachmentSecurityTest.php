<?php

use App\Models\PostAttachment;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

test('approved group members can download group attachments but outsiders cannot', function () {
    Storage::fake('public');

    $owner =
        User::factory()
            ->create();

    $member =
        User::factory()
            ->create();

    $outsider =
        User::factory()
            ->create();

    $group =
        poetTestGroup(
            $owner
        );

    poetTestAddGroupUser(
        $group,
        $member
    );

    $post =
        poetTestPost(
            $owner,
            [
                'group_id' =>
                    $group->id,
            ]
        );

    $path =
        'attachments/' .
        $post->id .
        '/notes.txt';

    Storage::disk('public')
        ->put(
            $path,
            'private group notes'
        );

    $attachment =
        PostAttachment::create([
            'post_id' =>
                $post->id,

            'name' =>
                'notes.txt',

            'path' =>
                $path,

            'url' =>
                '/storage/' .
                $path,

            'mime' =>
                'text/plain',

            'size' =>
                19,

            'created_by' =>
                $owner->id,
        ]);

    $this
        ->actingAs($member)
        ->get(
            route(
                'post.download',
                $attachment
            )
        )
        ->assertOk();

    $this
        ->actingAs($outsider)
        ->get(
            route(
                'post.download',
                $attachment
            )
        )
        ->assertNotFound();
});

test('draft attachments are available only to their owner', function () {
    Storage::fake('public');

    $owner =
        User::factory()
            ->create();

    $otherUser =
        User::factory()
            ->create();

    $draft =
        poetTestPost(
            $owner,
            [
                'status' =>
                    'draft',

                'published_at' =>
                    null,

                'draft_saved_at' =>
                    now(),
            ]
        );

    $path =
        'attachments/' .
        $draft->id .
        '/draft.txt';

    Storage::disk('public')
        ->put(
            $path,
            'unfinished'
        );

    $attachment =
        PostAttachment::create([
            'post_id' =>
                $draft->id,

            'name' =>
                'draft.txt',

            'path' =>
                $path,

            'url' =>
                '/storage/' .
                $path,

            'mime' =>
                'text/plain',

            'size' =>
                10,

            'created_by' =>
                $owner->id,
        ]);

    $this
        ->actingAs($owner)
        ->get(
            route(
                'post.download',
                $attachment
            )
        )
        ->assertOk();

    $this
        ->actingAs($otherUser)
        ->get(
            route(
                'post.download',
                $attachment
            )
        )
        ->assertNotFound();
});
