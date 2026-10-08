<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $userId =
            $request->user()->id;

        $posts =
            Post::postsForTimeline(
                $userId
            )
                ->where(
                    function ($query) use (
                        $userId
                    ) {
                        $query
                            ->where(
                                'posts.user_id',
                                $userId
                            )
                            ->orWhereNotNull(
                                'posts.group_id'
                            )
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

        return Inertia::render(
            'Home',
            [
                'posts' => $posts,
            ]
        );
    }
}
