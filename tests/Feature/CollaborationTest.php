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
