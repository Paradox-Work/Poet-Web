<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('groups feed excludes public groups without approved membership', function () {
    $owner = User::factory()->create();
    $visitor = User::factory()->create();
    $group = poetTestGroup($owner, ['visibility' => 'public']);
    poetTestPost($owner, ['group_id' => $group->id, 'title' => 'Public group poem']);

    $this->actingAs($visitor)
        ->get('/?source=groups')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Home')->has('posts.data', 0));

    // Public group content remains discoverable in the main feed.
    $this->actingAs($visitor)
        ->get('/?source=for_you')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Home')->has('posts.data', 1));
});
