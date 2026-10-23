<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('public group publications are visible to guests', function () {
    $owner = User::factory()->create();

    $group = poetTestGroup($owner, [
        'visibility' => 'public',
    ]);

    $post = poetTestPost($owner, [
        'group_id' => $group->id,
        'title' => 'Public group poem',
    ]);

    $this
        ->get(route('post.view', $post))
        ->assertOk();
});

test('private group publications remain hidden from guests', function () {
    $owner = User::factory()->create();

    $group = poetTestGroup($owner, [
        'visibility' => 'private',
    ]);

    $post = poetTestPost($owner, [
        'group_id' => $group->id,
    ]);

    $this
        ->get(route('post.view', $post))
        ->assertNotFound();
});

test('public group profiles expose publications to guests', function () {
    $owner = User::factory()->create();

    $group = poetTestGroup($owner, [
        'visibility' => 'public',
    ]);

    poetTestPost($owner, [
        'group_id' => $group->id,
        'title' => 'Visible group publication',
    ]);

    $this
        ->get(route('group.profile', $group))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) =>
                $page
                    ->component('Group/View')
                    ->where('group.visibility', 'public')
                    ->has('posts.data', 1)
                    ->has('users', 1)
        );
});

test('private group profiles hide member content from guests', function () {
    $owner = User::factory()->create();

    $group = poetTestGroup($owner, [
        'visibility' => 'private',
    ]);

    poetTestPost($owner, [
        'group_id' => $group->id,
    ]);

    $this
        ->get(route('group.profile', $group))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) =>
                $page
                    ->component('Group/View')
                    ->where('group.visibility', 'private')
                    ->where('posts', null)
                    ->has('users', 0)
        );
});

test('group admins can change group visibility', function () {
    $owner = User::factory()->create();

    $group = poetTestGroup($owner, [
        'visibility' => 'private',
    ]);

    $this
        ->actingAs($owner)
        ->put(
            route('group.update', $group),
            [
                'name' => $group->name,
                'about' => $group->about,
                'auto_approval' => false,
                'visibility' => 'public',
            ]
        )
        ->assertSessionHasNoErrors();

    expect($group->fresh()->visibility)
        ->toBe('public');
});

test('group visibility accepts only public or private', function () {
    $owner = User::factory()->create();

    $group = poetTestGroup($owner);

    $this
        ->actingAs($owner)
        ->put(
            route('group.update', $group),
            [
                'name' => $group->name,
                'about' => $group->about,
                'auto_approval' => false,
                'visibility' => 'secret',
            ]
        )
        ->assertSessionHasErrors('visibility');
});

test('private groups disclose approved member count and owner without exposing member profiles', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $outsider = User::factory()->create();
    $group = poetTestGroup($owner, ['visibility' => 'private']);
    poetTestAddGroupUser($group, $member);

    $this->actingAs($outsider)
        ->get(route('group.profile', $group))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Group/View')
            ->where('group.member_count', 2)
            ->where('group.owner.name', $owner->name)
            ->where('group.owner.username', $owner->username)
            ->has('users', 0)
            ->where('posts', null));

    $this->actingAs($member)
        ->get(route('group.profile', $group))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Group/View')
            ->where('group.member_count', 2)
            ->has('users', 2));
});
