<?php

use App\Models\Post;
use App\Models\User;

test('users can save poem drafts', function () {
    $user =
        User::factory()
            ->create();

    $response =
        $this
            ->actingAs($user)
            ->postJson(
                route(
                    'draft.store'
                ),
                poetTestPostPayload([
                    'type' =>
                        'poem',

                    'content_rating' =>
                        'mature',

                    'poem_form' =>
                        'free_verse',

                    'title' =>
                        'Unfinished',

                    'body' =>
                        '<p>First line</p>',
                ])
            );

    $response
        ->assertCreated()
        ->assertJsonStructure([
            'draft' => [
                'id',
                'draft_saved_at',
            ],
        ]);

    $this->assertDatabaseHas(
        'posts',
        [
            'user_id' =>
                $user->id,

            'type' =>
                'poem',

            'status' =>
                'draft',

            'content_rating' =>
                'mature',

            'title' =>
                'Unfinished',
        ]
    );
});

test('users cannot edit another users draft', function () {
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

    $this
        ->actingAs($otherUser)
        ->putJson(
            route(
                'draft.update',
                $draft
            ),
            poetTestPostPayload([
                'body' =>
                    '<p>Stolen edit</p>',
            ])
        )
        ->assertForbidden();
});

test('group drafts require approved membership', function () {
    $owner =
        User::factory()
            ->create();

    $outsider =
        User::factory()
            ->create();

    $member =
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

    $payload =
        poetTestPostPayload([
            'type' =>
                'poem',

            'poem_form' =>
                'free_verse',

            'group_id' =>
                $group->id,
        ]);

    $this
        ->actingAs($outsider)
        ->postJson(
            route(
                'draft.store'
            ),
            $payload
        )
        ->assertForbidden();

    $this
        ->actingAs($member)
        ->postJson(
            route(
                'draft.store'
            ),
            $payload
        )
        ->assertCreated();

    expect(
        Post::query()
            ->where(
                'group_id',
                $group->id
            )
            ->where(
                'user_id',
                $member->id
            )
            ->where(
                'status',
                'draft'
            )
            ->exists()
    )->toBeTrue();
});

test('reopened poem draft can be saved again without publishing', function () {
    $owner = User::factory()->create();

    $draft = poetTestPost($owner, [
        'type' => 'poem',
        'status' => 'draft',
        'published_at' => null,
        'draft_saved_at' => now(),
    ]);

    $this->actingAs($owner)
        ->putJson(
            route('draft.update', $draft),
            poetTestPostPayload([
                'type' => 'poem',
                'title' => 'Rīts pār Gauju',
                'body' => '<p>Vēl viena nepabeigta rinda.</p>',
            ])
        )
        ->assertOk()
        ->assertJsonPath('draft.id', $draft->id);

    $draft->refresh();

    expect($draft->status)->toBe('draft')
        ->and($draft->published_at)->toBeNull()
        ->and($draft->title)->toBe('Rīts pār Gauju')
        ->and($draft->body)->toContain('nepabeigta rinda');

    $this->actingAs($owner)
        ->get(route('poem.write', ['draft' => $draft->id]))
        ->assertOk();
});

test('reopened draft may save an empty body without becoming published', function () {
    $owner = User::factory()->create();

    $draft = poetTestPost($owner, [
        'type' => 'poem',
        'status' => 'draft',
        'published_at' => null,
        'draft_saved_at' => now(),
    ]);

    $this->actingAs($owner)
        ->putJson(
            route('draft.update', $draft),
            poetTestPostPayload([
                'type' => 'poem',
                'title' => 'Still working',
                'body' => '',
            ])
        )
        ->assertOk();

    $draft->refresh();

    expect($draft->status)->toBe('draft')
        ->and($draft->published_at)->toBeNull()
        ->and($draft->body)->toBe('');
});
