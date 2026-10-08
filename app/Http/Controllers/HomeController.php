<?php

namespace App\Http\Controllers;

use App\Http\Resources\GroupResource;
use App\Http\Resources\PostResource;
use App\Http\Resources\UserResource;
use App\Models\Group;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $userId =
            $request->user()->id;

        $filters = Validator::make(
            $request->only([
                'sort',
                'genre',
            ]),
            [
                'sort' => [
                    'nullable',
                    Rule::in([
                        'latest',
                        'trending',
                    ]),
                ],
                'genre' => [
                    'nullable',
                    'string',
                    'max:50',
                ],
            ]
        )->validate();

        $sort =
            $filters['sort']
                ?? 'latest';

        $genre =
            $filters['genre']
                ?? null;

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
         * Build genre counts from the full feed
         * before applying the active genre filter.
         */
        $genreCounts = (
            clone $baseFeedQuery
        )
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
                fn ($count, $name) => [
                    'name' => $name,
                    'count' => $count,
                ]
            )
            ->values();

        $postsQuery =
            clone $baseFeedQuery;

        if ($genre) {
            $postsQuery
                ->whereJsonContains(
                    'posts.poem_genres',
                    $genre
                );
        }

        if (
            $sort ===
            'trending'
        ) {
            $postsQuery
                ->withCount('comments')
                ->reorder()
                ->orderByRaw(
                    '((reactions_count * 2) + (comments_count * 3)) DESC'
                )
                ->orderByDesc(
                    'posts.published_at'
                )
                ->orderByDesc(
                    'posts.id'
                );
        } else {
            $postsQuery
                ->reorder()
                ->orderByDesc(
                    'posts.published_at'
                )
                ->orderByDesc(
                    'posts.id'
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

        $groups =
            Group::query()
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
                ->orderBy(
                    'group_users.role'
                )
                ->orderBy(
                    'groups.name'
                )
                ->get();

        $followings =
            $request
                ->user()
                ->followings()
                ->orderBy(
                    'users.name'
                )
                ->get();

        return Inertia::render(
            'Home',
            [
                'posts' => $posts,
                'groups' =>
                    GroupResource::collection(
                        $groups
                    ),
                'followings' =>
                    UserResource::collection(
                        $followings
                    ),
                'feedFilters' => [
                    'sort' => $sort,
                    'genre' => $genre,
                ],
                'genreCounts' =>
                    $genreCounts,
            ]
        );
    }
}
