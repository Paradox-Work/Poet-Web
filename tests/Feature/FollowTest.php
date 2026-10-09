<?php

use App\Models\Follower;
use App\Models\User;
use App\Notifications\FollowUser;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

test('database prevents duplicate follow relationships', function () {
    $target =
        User::factory()
            ->create();

    $follower =
        User::factory()
            ->create();

    DB::table('followers')
        ->insert([
            'user_id' =>
                $target->id,

            'follower_id' =>
                $follower->id,

            'created_at' =>
                now(),
        ]);

    expect(
        fn () =>
            DB::table('followers')
                ->insert([
                    'user_id' =>
                        $target->id,

                    'follower_id' =>
                        $follower->id,

                    'created_at' =>
                        now(),
                ])
    )->toThrow(
        QueryException::class
    );
});

test('repeated follow requests create one relationship', function () {
    Notification::fake();

    $target =
        User::factory()
            ->create();

    $follower =
        User::factory()
            ->create();

    $this
        ->actingAs($follower)
        ->post(
            route(
                'user.follow',
                $target
            ),
            [
                'follow' =>
                    true,
            ]
        )
        ->assertSessionHasNoErrors();

    $this
        ->actingAs($follower)
        ->post(
            route(
                'user.follow',
                $target
            ),
            [
                'follow' =>
                    true,
            ]
        )
        ->assertSessionHasNoErrors();

    expect(
        Follower::query()
            ->where(
                'user_id',
                $target->id
            )
            ->where(
                'follower_id',
                $follower->id
            )
            ->count()
    )->toBe(1);

    Notification::assertSentTo(
        $target,
        FollowUser::class
    );
});

test('users cannot follow themselves', function () {
    $user =
        User::factory()
            ->create();

    $this
        ->actingAs($user)
        ->post(
            route(
                'user.follow',
                $user
            ),
            [
                'follow' =>
                    true,
            ]
        )
        ->assertStatus(422);

    expect(
        Follower::query()
            ->count()
    )->toBe(0);
});
