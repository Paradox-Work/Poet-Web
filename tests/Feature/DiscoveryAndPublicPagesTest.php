<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('popular discovery ordering prefers publications with more reactions', function () {
    $author = User::factory()->create();
    $reactorA = User::factory()->create();
    $reactorB = User::factory()->create();

    $quiet = poetTestPost($author, [
        'type' => 'poem',
        'title' => 'Quiet poem',
        'poem_form' => 'free_verse',
    ]);

    $popular = poetTestPost($author, [
        'type' => 'poem',
        'title' => 'Popular poem',
        'poem_form' => 'free_verse',
    ]);

    $popular->reactions()->create([
        'user_id' => $reactorA->id,
        'type' => 'like',
    ]);

    $popular->reactions()->create([
        'user_id' => $reactorB->id,
        'type' => 'like',
    ]);

    $this
        ->get('/?feed=poems&sort=popular')
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) =>
                $page
                    ->component('Home')
                    ->where(
                        'posts.data.0.title',
                        'Popular poem'
                    )
                    ->where(
                        'feedFilters.sort',
                        'popular'
                    )
        );
});

test('legal pages and sitemap are public', function () {
    $this
        ->get(route('legal.show', 'guidelines'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) =>
                $page
                    ->component('Legal/Show')
                    ->where('page', 'guidelines')
        );

    $this
        ->get(route('sitemap'))
        ->assertOk()
        ->assertHeader(
            'content-type',
            'application/xml; charset=UTF-8'
        );
});
