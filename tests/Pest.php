<?php

use App\Enums\GroupUserRole;
use App\Enums\GroupUserStatus;
use App\Models\Group;
use App\Models\GroupUser;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

function poetTestGroup(
    User $owner,
    array $attributes = []
): Group {
    $group =
        Group::create(
            array_merge(
                [
                    'name' =>
                        'Test Writers ' .
                        Str::random(8),

                    'user_id' =>
                        $owner->id,

                    'auto_approval' =>
                        false,

                    'about' =>
                        'A group used by the automated qualification tests.',
                ],
                $attributes
            )
        );

    poetTestAddGroupUser(
        $group,
        $owner,
        GroupUserStatus::APPROVED->value,
        GroupUserRole::ADMIN->value,
        $owner
    );

    return $group;
}

function poetTestAddGroupUser(
    Group $group,
    User $user,
    string $status =
        GroupUserStatus::APPROVED->value,
    string $role =
        GroupUserRole::MEMBER->value,
    ?User $createdBy = null,
    array $attributes = []
): GroupUser {
    return GroupUser::create(
        array_merge(
            [
                'status' =>
                    $status,

                'role' =>
                    $role,

                'user_id' =>
                    $user->id,

                'group_id' =>
                    $group->id,

                'created_by' =>
                    $createdBy?->id
                    ?? $group->user_id,
            ],
            $attributes
        )
    );
}

function poetTestPost(
    User $user,
    array $attributes = []
): Post {
    return Post::create(
        array_merge(
            [
                'user_id' =>
                    $user->id,

                'group_id' =>
                    null,

                'type' =>
                    'post',

                'content_rating' =>
                    'general',

                'poem_form' =>
                    null,

                'poem_genres' =>
                    [],

                'title' =>
                    null,

                'caption' =>
                    null,

                'hashtags' =>
                    [],

                'body' =>
                    '<p>Test publication</p>',

                'status' =>
                    'published',

                'published_at' =>
                    now(),

                'draft_saved_at' =>
                    null,
            ],
            $attributes
        )
    );
}

function poetTestPostPayload(
    array $overrides = []
): array {
    return array_merge(
        [
            'type' =>
                'post',

            'content_rating' =>
                'general',

            'poem_form' =>
                null,

            'poem_genres' =>
                [],

            'title' =>
                null,

            'caption' =>
                null,

            'hashtags' =>
                [],

            'body' =>
                '<p>Test publication</p>',

            'group_id' =>
                null,
        ],
        $overrides
    );
}
