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

        $feed =
            $request->string('feed')
                ->toString();

        if (
            !in_array(
                $feed,
                [
                    'poems',
                    'posts',
                    'all',
                ],
                true
            )
        ) {
            $feed = 'poems';
        }

        $genre =
            trim(
                $request
                    ->string('genre')
                    ->toString()
            );

        if (
            mb_strlen($genre) > 50
        ) {
            $genre =
                mb_substr(
                    $genre,
                    0,
                    50
                );
        }

        $baseFeedQuery =
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
                );

        /*
         * Genre counts come from every poem
         * visible in this user's Home feed,
         * before the selected genre is applied.
         */
        $genreCounts =
            (clone $baseFeedQuery)
                ->withoutEagerLoads()
                ->where(
                    'posts.type',
                    'poem'
                )
                ->get([
                    'posts.id',
                    'posts.poem_genres',
                ])
                ->flatMap(
                    fn (Post $post) =>
                        $post->poem_genres
                            ?? []
                )
                ->filter()
                ->countBy()
                ->sortDesc()
                ->map(
                    fn (
                        int $count,
                        string $name
                    ) => [
                        'name' => $name,
                        'count' => $count,
                    ]
                )
                ->values();

        $postsQuery =
            clone $baseFeedQuery;

        if (
            $feed === 'poems'
        ) {
            $postsQuery->where(
                'posts.type',
                'poem'
            );
        }

        if (
            $feed === 'posts'
        ) {
            $postsQuery->where(
                'posts.type',
                'post'
            );

            /*
             * Posts do not have poem genres.
             */
            $genre = '';
        }

        if ($genre !== '') {
            $postsQuery
                ->where(
                    'posts.type',
                    'poem'
                )
                ->whereJsonContains(
                    'posts.poem_genres',
                    $genre
                );
        }

        $posts =
            PostResource::collection(
                $postsQuery
                    ->paginate(10)
                    ->withQueryString()
            );

        if (
            $request->wantsJson()
        ) {
            return $posts;
        }

        return Inertia::render(
            'Home',
            [
                'posts' => $posts,

                'feedFilters' => [
                    'feed' => $feed,
                    'genre' =>
                        $genre !== ''
                            ? $genre
                            : null,
                ],

                'genreCounts' =>
                    $genreCounts,
            ]
        );
    }
}
