<?php

namespace App\Http\Controllers;

use App\Http\Resources\GroupResource;
use App\Models\Group;
use App\Http\Resources\PostResource;
use Illuminate\Http\Request;
use App\Models\Post;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $loadCommentTree = null;

        $loadCommentTree =
            function ($query) use (
                &$loadCommentTree,
                $userId
            ) {

                $query
                    ->with('user')
                    ->withCount([
                        'reactions',
                        'comments',
                    ])
                    ->with([
                        'reactions' =>
                            function ($query) use ($userId) {

                                $query->where(
                                    'user_id',
                                    $userId
                                );
                            },

                        'comments' =>
                            $loadCommentTree,
                    ]);
            };
 
        $posts = Post::query()
            ->withCount([
                'reactions',
                'comments',
            ])
            ->with([
                'comments' =>
                    function ($query) use (
                        $loadCommentTree
                    ) {

                        $query->whereNull(
                            'parent_id'
                        );

                        $loadCommentTree($query);
                    },

                'reactions' =>
                    function ($query) use ($userId) {

                        $query->where(
                            'user_id',
                            $userId
                        );
                    },
            ])
            ->latest()
            ->paginate(20);
        
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

        return Inertia::render('Home', [
            'posts' => PostResource::collection($posts),

            'groups' => GroupResource::collection($groups),
        ]);
    }
}
