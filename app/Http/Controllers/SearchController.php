<?php

namespace App\Http\Controllers;

use App\Http\Resources\GroupResource;
use App\Http\Resources\PostResource;
use App\Http\Resources\UserResource;
use App\Models\Group;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(
        Request $request,
        ?string $search = null
    ) {
        $search =
            trim(
                (string) $search
            );

        if ($search === '') {
            return redirect(
                route('dashboard')
            );
        }

        if (
            mb_strlen($search) > 100
        ) {
            $search =
                mb_substr(
                    $search,
                    0,
                    100
                );
        }

        $like =
            '%' .
            $search .
            '%';

        $posts =
            PostResource::collection(
                Post::postsForTimeline(
                    $request->user()->id
                )
                    ->where(
                        'body',
                        'like',
                        $like
                    )
                    ->paginate(
                        20,
                        ['posts.*'],
                        'posts_page'
                    )
                    ->withQueryString()
            );

        /*
         * Infinite-scroll requests from
         * PostList only need publication
         * results, so avoid also loading
         * user/group result sets.
         */
        if ($request->wantsJson()) {
            return $posts;
        }

        $users =
            UserResource::collection(
                User::query()
                    ->where(
                        function (
                            $query
                        ) use ($like) {
                            $query
                                ->where(
                                    'name',
                                    'like',
                                    $like
                                )
                                ->orWhere(
                                    'username',
                                    'like',
                                    $like
                                );
                        }
                    )
                    ->latest()
                    ->paginate(
                        12,
                        ['*'],
                        'users_page'
                    )
                    ->withQueryString()
            );

        $groups =
            GroupResource::collection(
                Group::query()
                    ->where(
                        function (
                            $query
                        ) use ($like) {
                            $query
                                ->where(
                                    'name',
                                    'like',
                                    $like
                                )
                                ->orWhere(
                                    'about',
                                    'like',
                                    $like
                                );
                        }
                    )
                    ->latest()
                    ->paginate(
                        12,
                        ['*'],
                        'groups_page'
                    )
                    ->withQueryString()
            );

        return inertia(
            'Search',
            [
                'posts' =>
                    $posts,

                'search' =>
                    $search,

                'users' =>
                    $users,

                'groups' =>
                    $groups,
            ]
        );
    }
}
