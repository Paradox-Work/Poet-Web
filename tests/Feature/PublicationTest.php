<?php

use App\Models\Post;
use App\Models\User;

test('a mature poem is stored with sanitized rich text', function () {
    $user =
        User::factory()
            ->create();

    $response =
        $this
            ->actingAs($user)
            ->post(
                route(
                    'post.create'
                ),
                poetTestPostPayload([
                    'type' =>
                        'poem',

                    'content_rating' =>
                        'mature',

                    'poem_form' =>
                        'free_verse',

                    'poem_genres' =>
                        [
                            'Reflection',
                        ],

                    'title' =>
                        'A Test Poem',

                    'hashtags' =>
                        [
                            'testing',
                        ],

                    'body' =>
                        '<p>Hello <strong>world</strong></p>' .
                        '<script>alert(1)</script>' .
                        '<a href="javascript:alert(2)">unsafe</a>',
                ])
            );

    $response
        ->assertSessionHasNoErrors();

    $post =
        Post::query()
            ->latest('id')
            ->firstOrFail();

    expect($post->type)
        ->toBe('poem');

    expect($post->content_rating)
        ->toBe('mature');

    expect($post->status)
        ->toBe('published');

    $storedBody =
        $post->getRawOriginal(
            'body'
        );

    expect($storedBody)
        ->toContain(
            '<strong>world</strong>'
        )
        ->not
        ->toContain(
            '<script'
        )
        ->not
        ->toContain(
            'javascript:'
        );
});

test('a user cannot update another users publication', function () {
    $owner =
        User::factory()
            ->create();

    $otherUser =
        User::factory()
            ->create();

    $post =
        poetTestPost(
            $owner
        );

    $this
        ->actingAs($otherUser)
        ->put(
            route(
                'post.update',
                $post
            ),
            poetTestPostPayload([
                'body' =>
                    '<p>Unauthorized edit</p>',
            ])
        )
        ->assertForbidden();

    expect(
        $post
            ->fresh()
            ->body
    )->toContain(
        'Test publication'
    );
});

test('a user cannot delete another standalone publication', function () {
    $owner =
        User::factory()
            ->create();

    $otherUser =
        User::factory()
            ->create();

    $post =
        poetTestPost(
            $owner
        );

    $this
        ->actingAs($otherUser)
        ->delete(
            route(
                'post.destroy',
                $post
            )
        )
        ->assertForbidden();

    expect(
        Post::withTrashed()
            ->find($post->id)
            ?->deleted_at
    )->toBeNull();
});

test('publication owners can delete their own publication', function () {
    $owner =
        User::factory()
            ->create();

    $post =
        poetTestPost(
            $owner
        );

    $this
        ->actingAs($owner)
        ->delete(
            route(
                'post.destroy',
                $post
            )
        )
        ->assertRedirect();

    expect(
        Post::withTrashed()
            ->findOrFail(
                $post->id
            )
            ->trashed()
    )->toBeTrue();
});
