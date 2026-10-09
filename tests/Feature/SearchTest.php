<?php

use App\Models\Group;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('user and group search results are paginated', function () {
    $viewer =
        User::factory()
            ->create([
                'name' =>
                    'Viewer',
            ]);

    foreach (
        range(1, 15)
        as $index
    ) {
        User::factory()
            ->create([
                'name' =>
                    sprintf(
                        'Needle Person %02d',
                        $index
                    ),

                'email' =>
                    sprintf(
                        'needle-user-%02d@example.com',
                        $index
                    ),
            ]);

        Group::create([
            'name' =>
                sprintf(
                    'Needle Group %02d',
                    $index
                ),

            'user_id' =>
                $viewer->id,

            'auto_approval' =>
                true,

            'about' =>
                'Needle search fixture',
        ]);
    }

    $this
        ->actingAs($viewer)
        ->get(
            route(
                'search',
                [
                    'search' =>
                        'Needle',
                ]
            )
        )
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) =>
                $page
                    ->component(
                        'Search'
                    )
                    ->has(
                        'users.data',
                        12
                    )
                    ->where(
                        'users.meta.total',
                        15
                    )
                    ->has(
                        'groups.data',
                        12
                    )
                    ->where(
                        'groups.meta.total',
                        15
                    )
        );
});

test('search supports independent second pages for users and groups', function () {
    $viewer =
        User::factory()
            ->create([
                'name' =>
                    'Viewer',
            ]);

    foreach (
        range(1, 15)
        as $index
    ) {
        User::factory()
            ->create([
                'name' =>
                    sprintf(
                        'Paged Person %02d',
                        $index
                    ),

                'email' =>
                    sprintf(
                        'paged-user-%02d@example.com',
                        $index
                    ),
            ]);

        Group::create([
            'name' =>
                sprintf(
                    'Paged Group %02d',
                    $index
                ),

            'user_id' =>
                $viewer->id,

            'auto_approval' =>
                true,

            'about' =>
                'Paged search fixture',
        ]);
    }

    $this
        ->actingAs($viewer)
        ->get(
            route(
                'search',
                [
                    'search' =>
                        'Paged',

                    'users_page' =>
                        2,

                    'groups_page' =>
                        2,
                ]
            )
        )
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) =>
                $page
                    ->has(
                        'users.data',
                        3
                    )
                    ->where(
                        'users.meta.current_page',
                        2
                    )
                    ->has(
                        'groups.data',
                        3
                    )
                    ->where(
                        'groups.meta.current_page',
                        2
                    )
        );
});
