<?php

use App\Enums\GroupUserStatus;
use App\Models\GroupUser;
use App\Models\User;
use App\Notifications\InvitationInGroup;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

test('group admins can invite users', function () {
    Notification::fake();

    $owner =
        User::factory()
            ->create();

    $invitee =
        User::factory()
            ->create();

    $group =
        poetTestGroup(
            $owner
        );

    $this
        ->actingAs($owner)
        ->post(
            route(
                'group.inviteUsers',
                $group
            ),
            [
                'identifier' =>
                    $invitee->username,
            ]
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
                $invitee->id
            )
            ->firstOrFail();

    expect($membership->status)
        ->toBe(
            GroupUserStatus::PENDING->value
        );

    expect($membership->token)
        ->not
        ->toBeNull();

    Notification::assertSentTo(
        $invitee,
        InvitationInGroup::class
    );
});

test('non admins cannot invite group members', function () {
    Notification::fake();

    $owner =
        User::factory()
            ->create();

    $member =
        User::factory()
            ->create();

    $target =
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

    $this
        ->actingAs($member)
        ->post(
            route(
                'group.inviteUsers',
                $group
            ),
            [
                'identifier' =>
                    $target->username,
            ]
        )
        ->assertForbidden();

    expect(
        GroupUser::query()
            ->where(
                'group_id',
                $group->id
            )
            ->where(
                'user_id',
                $target->id
            )
            ->exists()
    )->toBeFalse();
});

test('viewing an invitation does not accept it', function () {
    $owner =
        User::factory()
            ->create();

    $invitee =
        User::factory()
            ->create();

    $group =
        poetTestGroup(
            $owner
        );

    $token =
        Str::random(64);

    $membership =
        poetTestAddGroupUser(
            $group,
            $invitee,
            GroupUserStatus::PENDING->value,
            'member',
            $owner,
            [
                'token' =>
                    $token,

                'token_expire_date' =>
                    now()
                        ->addHour(),
            ]
        );

    $this
        ->actingAs($invitee)
        ->get(
            route(
                'group.invitation',
                [
                    'token' =>
                        $token,
                ]
            )
        )
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) =>
                $page
                    ->component(
                        'Group/Invitation'
                    )
                    ->where(
                        'invitation.group.name',
                        $group->name
                    )
        );

    $membership->refresh();

    expect($membership->status)
        ->toBe(
            GroupUserStatus::PENDING->value
        );

    expect($membership->token_used)
        ->toBeNull();
});

test('invited users can accept invitations with post', function () {
    Notification::fake();

    $owner =
        User::factory()
            ->create();

    $invitee =
        User::factory()
            ->create();

    $group =
        poetTestGroup(
            $owner
        );

    $token =
        Str::random(64);

    $membership =
        poetTestAddGroupUser(
            $group,
            $invitee,
            GroupUserStatus::PENDING->value,
            'member',
            $owner,
            [
                'token' =>
                    $token,

                'token_expire_date' =>
                    now()
                        ->addHour(),
            ]
        );

    $this
        ->actingAs($invitee)
        ->post(
            route(
                'group.approveInvitation',
                [
                    'token' =>
                        $token,
                ]
            )
        )
        ->assertRedirect(
            route(
                'group.profile',
                $group->slug
            )
        );

    $membership->refresh();

    expect($membership->status)
        ->toBe(
            GroupUserStatus::APPROVED->value
        );

    expect($membership->token_used)
        ->not
        ->toBeNull();
});

test('users cannot accept another users invitation', function () {
    Notification::fake();

    $owner =
        User::factory()
            ->create();

    $invitee =
        User::factory()
            ->create();

    $attacker =
        User::factory()
            ->create();

    $group =
        poetTestGroup(
            $owner
        );

    $token =
        Str::random(64);

    poetTestAddGroupUser(
        $group,
        $invitee,
        GroupUserStatus::PENDING->value,
        'member',
        $owner,
        [
            'token' =>
                $token,

            'token_expire_date' =>
                now()
                    ->addHour(),
        ]
    );

    $this
        ->actingAs($attacker)
        ->post(
            route(
                'group.approveInvitation',
                [
                    'token' =>
                        $token,
                ]
            )
        )
        ->assertForbidden();
});

test('expired invitations cannot be accepted', function () {
    Notification::fake();

    $owner =
        User::factory()
            ->create();

    $invitee =
        User::factory()
            ->create();

    $group =
        poetTestGroup(
            $owner
        );

    $token =
        Str::random(64);

    poetTestAddGroupUser(
        $group,
        $invitee,
        GroupUserStatus::PENDING->value,
        'member',
        $owner,
        [
            'token' =>
                $token,

            'token_expire_date' =>
                now()
                    ->subMinute(),
        ]
    );

    $this
        ->actingAs($invitee)
        ->post(
            route(
                'group.approveInvitation',
                [
                    'token' =>
                        $token,
                ]
            )
        )
        ->assertStatus(410);
});
