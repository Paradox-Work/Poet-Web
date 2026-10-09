<?php

namespace App\Http\Controllers;

use App\Notifications\FollowUser;
use App\Models\Follower;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function follow(
        Request $request,
        User $user
    ) {
        $data = $request->validate([
            'follow' => [
                'required',
                'boolean',
            ],
        ]);

        $currentUser = $request->user();

        if ($currentUser->id === $user->id) {
            abort(
                422,
                'You cannot follow yourself.'
            );
        }

        if ($data['follow']) {

            $inserted =
                Follower::query()
                    ->insertOrIgnore([
                        'user_id' =>
                            $user->id,

                        'follower_id' =>
                            $currentUser->id,

                        'created_at' =>
                            now(),
                    ]);

            if ($inserted === 1) {
                $user->notify(
                    new FollowUser(
                        $currentUser,
                        true
                    )
                );
            }

            $message =
                "You are now following {$user->name}.";

        } else {

            $deleted =
                Follower::query()
                    ->where(
                        'user_id',
                        $user->id
                    )
                    ->where(
                        'follower_id',
                        $currentUser->id
                    )
                    ->delete();

            if ($deleted) {
                $user->notify(
                    new FollowUser(
                        $currentUser,
                        false
                    )
                );
            }

            $message =
                "You unfollowed {$user->name}.";
        }

        return back()->with(
            'success',
            $message
        );
    }
}