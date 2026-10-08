<?php

namespace App\Http\Controllers;

use App\Http\Resources\GroupResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\PostResource;
use App\Models\Group;
use Illuminate\Http\Request;
use App\Models\Post;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        
        $posts =
            Post::postsForTimeline(
                $userId
            )
                ->where(
                    function ($query) use (
                        $userId
                    ) {
                        $query
                            // My own posts
                            ->where(
                                'posts.user_id',
                                $userId
                            )

                            // Posts from groups I can access
                            ->orWhereNotNull(
                                'posts.group_id'
                            )

                            // Posts from people I follow
                            ->orWhereIn(
                                'posts.user_id',
                                function ($query) use (
                                    $userId
                                ) {
                                    $query
                                        ->select('user_id')
                                        ->from('followers')
                                        ->where(
                                            'follower_id',
                                            $userId
                                        );
                                }
                            );
                    }
                )
                ->paginate(10)
                ->withQueryString();


        $posts =
            PostResource::collection(
                $posts
            );


        if ($request->wantsJson()) {
            return $posts;
        }
        
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
