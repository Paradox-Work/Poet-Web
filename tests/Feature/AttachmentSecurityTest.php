<?php

use App\Models\PostAttachment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
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


test('new publication attachments are stored on the private disk', function () {
    Storage::fake('local');
    Storage::fake('public');

    $user = User::factory()->create();

    $this
        ->actingAs($user)
        ->post(
            route('post.create'),
            poetTestPostPayload([
                'attachments' => [
                    UploadedFile::fake()->image('poem.jpg'),
                ],
            ])
        )
        ->assertSessionHasNoErrors();

    $attachment = PostAttachment::query()
        ->latest('id')
        ->firstOrFail();

    Storage::disk('local')
        ->assertExists($attachment->path);

    Storage::disk('public')
        ->assertMissing($attachment->path);

    expect($attachment->url)
        ->toBe('');
});

test('guests cannot access attachments from member-only group publications', function () {
    Storage::fake('local');

    $owner = User::factory()->create();
    $group = poetTestGroup($owner);
    $post = poetTestPost($owner, [
        'group_id' => $group->id,
    ]);

    $path = 'attachments/' . $post->id . '/members.txt';

    Storage::disk('local')
        ->put($path, 'members only');

    $attachment = PostAttachment::create([
        'post_id' => $post->id,
        'name' => 'members.txt',
        'path' => $path,
        'url' => '',
        'mime' => 'text/plain',
        'size' => 12,
        'created_by' => $owner->id,
    ]);

    $this
        ->get(
            route('post.attachment.view', $attachment)
        )
        ->assertNotFound();

    $this
        ->get(
            route('post.download', $attachment)
        )
        ->assertNotFound();
});
