<?php

use App\Models\PostCollaborator;
use App\Models\User;

test('poem owners can invite collaborators who can accept and edit', function () {
    $owner = User::factory()->create();
    $collaborator = User::factory()->create();
    $poem = poetTestPost($owner, [
        'type' => 'poem',
        'title' => 'Shared poem',
        'poem_form' => 'free_verse',
    ]);

    $this
        ->actingAs($owner)
        ->post(
            route('collaborations.invite', $poem),
            ['username' => $collaborator->username]
        )
        ->assertSessionHasNoErrors();

    $record = PostCollaborator::query()->firstOrFail();

    expect(
        $collaborator
            ->notifications()
            ->get()
            ->contains(
                fn ($notification) =>
                    ($notification->data['kind'] ?? null)
                    === 'collaboration_invited'
            )
    )->toBeTrue();

    $this
        ->actingAs($collaborator)
        ->post(route('collaborations.accept', $record))
        ->assertRedirect();

    $this
        ->actingAs($collaborator)
        ->put(
            route('post.update', $poem),
            poetTestPostPayload([
                'type' => 'poem',
                'title' => 'Edited together',
                'poem_form' => 'free_verse',
            ])
        )
        ->assertSessionHasNoErrors();

    expect($poem->fresh()->title)->toBe('Edited together');

    expect(
        $owner
            ->notifications()
            ->get()
            ->contains(
                fn ($notification) =>
                    ($notification->data['kind'] ?? null)
                    === 'collaboration_accepted'
            )
    )->toBeTrue();
});

test('non collaborators cannot edit another users poem', function () {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();
    $poem = poetTestPost($owner, [
        'type' => 'poem',
        'poem_form' => 'free_verse',
    ]);

    $this
        ->actingAs($outsider)
        ->put(
            route('post.update', $poem),
            poetTestPostPayload([
                'type' => 'poem',
                'poem_form' => 'free_verse',
            ])
        )
        ->assertForbidden();
});

test('private group poems can only invite approved group members', function () {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();
    $group = poetTestGroup($owner, ['visibility' => 'private']);
    $poem = poetTestPost($owner, [
        'type' => 'poem',
        'poem_form' => 'free_verse',
        'group_id' => $group->id,
    ]);

    $this
        ->actingAs($owner)
        ->post(
            route('collaborations.invite', $poem),
            ['username' => $outsider->username]
        )
        ->assertSessionHasErrors('username');

    expect(PostCollaborator::query()->count())->toBe(0);
});


test('collaborators cannot convert an owners poem into a normal post', function () {
    $owner = User::factory()->create();
    $collaborator = User::factory()->create();
    $poem = poetTestPost($owner, [
        'type' => 'poem',
        'poem_form' => 'free_verse',
    ]);

    PostCollaborator::query()->create([
        'post_id' => $poem->id,
        'user_id' => $collaborator->id,
        'invited_by' => $owner->id,
        'status' => 'accepted',
        'role' => 'editor',
    ]);

    $this
        ->actingAs($collaborator)
        ->put(
            route('post.update', $poem),
            poetTestPostPayload([
                'type' => 'post',
                'poem_form' => null,
            ])
        )
        ->assertSessionHasErrors('type');

    expect($poem->fresh()->type)->toBe('poem');
});
