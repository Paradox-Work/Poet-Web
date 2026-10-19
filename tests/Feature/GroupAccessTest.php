<?php

use App\Enums\GroupUserStatus;
use App\Models\GroupUser;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

test('manual group join requests remain pending until approved', function () {
    Notification::fake();

    $owner =
        User::factory()
            ->create();

    $applicant =
        User::factory()
            ->create();

    $group =
        poetTestGroup(
            $owner,
            [
                'auto_approval' =>
                    false,
            ]
        );

    $this
        ->actingAs($applicant)
        ->post(
            route(
                'group.join',
                $group
            )
        )
        ->assertSessionHasNoErrors();

    $membership =
        GroupUser::query()
            ->where(
                'group_id',
                $group->id
            )
            ->where(
                'user_id',
                $applicant->id
            )
            ->firstOrFail();

    expect($membership->status)
        ->toBe(
            GroupUserStatus::PENDING->value
        );

    expect($membership->token)
        ->toBeNull();
});

test('only group admins can resolve join requests', function () {
    Notification::fake();

    $owner =
        User::factory()
            ->create();

    $member =
        User::factory()
            ->create();

    $applicant =
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

    $requestMembership =
        poetTestAddGroupUser(
            $group,
            $applicant,
            GroupUserStatus::PENDING->value
        );

    $payload = [
        'user_id' =>
            $applicant->id,

        'action' =>
            'approve',
    ];

    $this
        ->actingAs($member)
        ->post(
            route(
                'group.resolveJoinRequest',
                $group
            ),
            $payload
        )
        ->assertForbidden();

    expect(
        $requestMembership
            ->fresh()
            ->status
    )->toBe(
        GroupUserStatus::PENDING->value
    );

    $this
        ->actingAs($owner)
        ->post(
            route(
                'group.resolveJoinRequest',
                $group
            ),
            $payload
        )
        ->assertSessionHasNoErrors();

    expect(
        $requestMembership
            ->fresh()
            ->status
    )->toBe(
        GroupUserStatus::APPROVED->value
    );
});

test('group publications are visible only to approved members', function () {
    $owner =
        User::factory()
            ->create();

    $member =
        User::factory()
            ->create();

    $outsider =
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

    $post =
        poetTestPost(
            $owner,
            [
                'group_id' =>
                    $group->id,
            ]
        );

    $this
        ->actingAs($member)
        ->get(
            route(
                'post.view',
                $post
            )
        )
        ->assertOk();

    $this
        ->actingAs($outsider)
        ->get(
            route(
                'post.view',
                $post
            )
        )
        ->assertNotFound();
});

test('only approved members can publish into a group', function () {
    Notification::fake();

    $owner =
        User::factory()
            ->create();

    $member =
        User::factory()
            ->create();

    $outsider =
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
            'group_id' =>
                $group->id,
        ]);

    $this
        ->actingAs($outsider)
        ->post(
            route(
                'post.create'
            ),
            $payload
        )
        ->assertSessionHasErrors(
            'group_id'
        );

    $this
        ->actingAs($member)
        ->post(
            route(
                'post.create'
            ),
            $payload
        )
        ->assertSessionHasNoErrors();

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
                'published'
            )
            ->exists()
    )->toBeTrue();
});

test('group admins can moderate a members group publication', function () {
    Notification::fake();

    $owner =
        User::factory()
            ->create();

    $member =
        User::factory()
            ->create();

    $outsider =
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

    $post =
        poetTestPost(
            $member,
            [
                'group_id' =>
                    $group->id,
            ]
        );

    $this
        ->actingAs($outsider)
        ->delete(
            route(
                'post.destroy',
                $post
            )
        )
        ->assertForbidden();

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

test('approved group member can leave without admin permissions', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $group = poetTestGroup($owner, ['visibility' => 'private']);
    poetTestAddGroupUser($group, $member);

    $this->actingAs($member)
        ->delete(route('group.leave', $group))
        ->assertRedirect(route('group.index'));

    $this->assertDatabaseMissing('group_users', [
        'group_id' => $group->id,
        'user_id' => $member->id,
    ]);

    expect($group->hasApprovedUser($member->id))->toBeFalse();
});

test('non-owner group admin can leave their group', function () {
    $owner = User::factory()->create();
    $admin = User::factory()->create();
    $group = poetTestGroup($owner);
    poetTestAddGroupUser(
        $group,
        $admin,
        GroupUserStatus::APPROVED->value,
        \App\Enums\GroupUserRole::ADMIN->value
    );

    $this->actingAs($admin)
        ->delete(route('group.leave', $group))
        ->assertRedirect(route('group.index'));

    expect($group->hasApprovedUser($admin->id))->toBeFalse();
});

test('group owners cannot leave without transferring ownership', function () {
    $owner = User::factory()->create();
    $group = poetTestGroup($owner);

    $this->actingAs($owner)
        ->delete(route('group.leave', $group))
        ->assertRedirect()
        ->assertSessionHasErrors('membership');

    expect($group->hasApprovedUser($owner->id))->toBeTrue();
});

test('users cannot remove another member via the leave route', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $other = User::factory()->create();
    $group = poetTestGroup($owner);
    poetTestAddGroupUser($group, $member);
    poetTestAddGroupUser($group, $other);

    $this->actingAs($member)
        ->delete(route('group.leave', $group), ['user_id' => $other->id])
        ->assertRedirect(route('group.index'));

    expect($group->hasApprovedUser($other->id))->toBeTrue()
        ->and($group->hasApprovedUser($member->id))->toBeFalse();
});
