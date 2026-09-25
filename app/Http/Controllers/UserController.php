<?php

namespace App\Http\Controllers;

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

            Follower::firstOrCreate([
                'user_id' =>
                    $user->id,

                'follower_id' =>
                    $currentUser->id,
            ]);

            $message =
                "You are now following {$user->name}.";

        } else {

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

            $message =
                "You unfollowed {$user->name}.";
        }

        return back()->with(
            'success',
            $message
        );
    }
}