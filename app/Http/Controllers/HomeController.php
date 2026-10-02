<?php

namespace App\Http\Controllers;

use App\Http\Resources\GroupResource;
use App\Http\Resources\UserResource;

use App\Models\Group;
use Illuminate\Http\Request;
use App\Models\Post;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;
 
        $group =
            $post->group;


        if ($group) {

            /*
            * Private group post:
            * notify approved group members.
            */
            $users =
                $group
                    ->approvedUsers()
                    ->where(
                        'users.id',
                        '!=',
                        $user->id
                    )
                    ->get();

            Notification::send(
                $users,
                new PostCreated(
                    $post,
                    $user,
                    $group
                )
            );

        } else {

            /*
            * Normal post:
            * notify followers.
            */
            $followers =
                $user
                    ->followers()
                    ->get();

            Notification::send(
                $followers,
                new PostCreated(
                    $post,
                    $user
                )
            );
        }


        return back();
        
        $groups = Group::query()
            ->select([
                'groups.*',
                'group_users.status',
                'group_users.role',
            ])
            ->join(
                'group_users',
                'group_users.group_id',
                '=',
                'groups.id'
            )
            ->where(
                'group_users.user_id',
                $userId
            )
            ->orderBy('group_users.role')
            ->orderBy('groups.name')
            ->get();

        $followings =
            $request
                ->user()
                ->followings()
                ->orderBy('users.name')
                ->get();

        return Inertia::render('Home', [
            'posts' => $posts,

            'groups' => GroupResource::collection($groups),

            'followings' =>UserResource::collection($followings),
        ]);
    }
}
