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
        $userId = $request->user()?->id;

        $feed = $request->string('feed')->toString();
        if (!in_array($feed, ['poems', 'posts', 'all'], true)) {
            $feed = 'poems';
        }

        $source = $request->string('source')->toString();
        if (!in_array($source, ['for_you', 'following', 'groups', 'mine'], true)) {
            $source = 'for_you';
        }

        if (!$userId && $source !== 'for_you') {
            $source = 'for_you';
        }

        $sort = $request->string('sort')->toString();

        if (!in_array(
            $sort,
            ['newest', 'popular', 'discussed', 'trending'],
            true
        )) {
            $sort = 'newest';
        }

        $genre = trim($request->string('genre')->toString());
        if (mb_strlen($genre) > 50) {
            $genre = mb_substr($genre, 0, 50);
        }

        $sourceFeedQuery = Post::postsForTimeline($userId);

        if ($source === 'following' && $userId) {
            $sourceFeedQuery->whereIn('posts.user_id', function ($query) use ($userId) {
                $query->select('user_id')
                    ->from('followers')
                    ->where('follower_id', $userId);
            });
        } elseif ($source === 'groups' && $userId) {
            $sourceFeedQuery->whereNotNull('posts.group_id');
        } elseif ($source === 'mine' && $userId) {
            $sourceFeedQuery->where('posts.user_id', $userId);
        }

        $genreCounts = (clone $sourceFeedQuery)
            ->withoutEagerLoads()
            ->where('posts.type', 'poem')
            ->get(['posts.id', 'posts.poem_genres'])
            ->flatMap(fn (Post $post) => $post->poem_genres ?? [])
            ->filter()
            ->countBy()
            ->sortDesc()
            ->map(fn (int $count, string $name) => ['name' => $name, 'count' => $count])
            ->values();

        $postsQuery = clone $sourceFeedQuery;

        if ($feed === 'poems') {
            $postsQuery->where('posts.type', 'poem');
        } elseif ($feed === 'posts') {
            $postsQuery->where('posts.type', 'post');
            $genre = '';
        }

        if ($genre !== '') {
            $postsQuery->where('posts.type', 'poem')
                ->whereJsonContains('posts.poem_genres', $genre);
        }

        if ($sort !== 'newest') {
            $postsQuery->withCount('comments');
        }

        if ($sort === 'popular') {
            $postsQuery
                ->reorder()
                ->orderByDesc('reactions_count')
                ->orderByDesc('posts.created_at');
        } elseif ($sort === 'discussed') {
            $postsQuery
                ->reorder()
                ->orderByDesc('comments_count')
                ->orderByDesc('posts.created_at');
        } elseif ($sort === 'trending') {
            $postsQuery
                ->reorder()
                ->orderByDesc('reactions_count')
                ->orderByDesc('comments_count')
                ->orderByDesc('posts.created_at');
        }

        $posts = PostResource::collection(
            $postsQuery
                ->paginate(10)
                ->withQueryString()
        );

        if ($request->wantsJson()) {
            return $posts;
        }

        return Inertia::render('Home', [
            'posts' => $posts,
            'feedFilters' => [
                'feed' => $feed,
                'source' => $source,
                'genre' => $genre !== '' ? $genre : null,
                'sort' => $sort,
            ],
            'genreCounts' => $genreCounts,
        ]);
    }
}
