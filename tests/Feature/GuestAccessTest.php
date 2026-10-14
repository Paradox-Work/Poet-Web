<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests can browse the public home feed', function () {
    $author = User::factory()->create();

    poetTestPost($author, [
        'type' => 'poem',
        'title' => 'Public poem',
    ]);

    $this
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) =>
                $page
                    ->component('Home')
                    ->has('posts.data', 1)
                    ->where('posts.data.0.title', 'Public poem')
        );
});

test('guests can search public content', function () {
    $author = User::factory()->create(['name' => 'Needle Writer']);

    poetTestPost($author, ['body' => '<p>Needle publication</p>']);

    $this
        ->get(route('search', ['search' => 'Needle']))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) =>
                $page
                    ->component('Search')
                    ->has('posts.data', 1)
                    ->has('users.data', 1)
        );
});

test('public profiles expose publications but not private email fields', function () {
    $author = User::factory()->create();

    poetTestPost($author, [
        'type' => 'poem',
        'title' => 'Visible poem',
    ]);

    $this
        ->get(route('profile', ['user' => $author->username]))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) =>
                $page
                    ->component('Profile/View')
                    ->has('posts.data', 1)
                    ->where('user.username', $author->username)
                    ->missing('user.email')
                    ->missing('user.email_verified_at')
        );
});
