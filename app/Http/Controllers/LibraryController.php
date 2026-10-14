<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Models\Bookmark;
use App\Models\Post;
use App\Models\ReadingList;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LibraryController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $bookmarkedPosts = PostResource::collection(
            Post::postsForTimeline($user->id)
                ->whereIn(
                    'posts.id',
                    Bookmark::query()
                        ->where('user_id', $user->id)
                        ->select('post_id')
                )
                ->paginate(12, ['posts.*'], 'bookmarks_page')
                ->withQueryString()
        );

        $lists = ReadingList::query()
            ->where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(function (ReadingList $list) use ($request, $user) {
                $visiblePosts = Post::postsForTimeline($user->id)
                    ->whereIn(
                        'posts.id',
                        $list->posts()->select('posts.id')
                    )
                    ->limit(50)
                    ->get();

                return [
                    'id' => $list->id,
                    'name' => $list->name,
                    'description' => $list->description,
                    'posts' => PostResource::collection(
                        $visiblePosts
                    )->resolve($request),
                ];
            });

        return Inertia::render('Library/Index', [
            'bookmarks' => $bookmarkedPosts,
            'lists' => $lists,
        ]);
    }

    public function toggleBookmark(Request $request, Post $post)
    {
        if (!$post->isVisibleTo($request->user()->id)) {
            abort(404);
        }

        $bookmark = Bookmark::query()
            ->where('user_id', $request->user()->id)
            ->where('post_id', $post->id)
            ->first();

        if ($bookmark) {
            $bookmark->delete();
            $bookmarked = false;
        } else {
            Bookmark::query()->create([
                'user_id' => $request->user()->id,
                'post_id' => $post->id,
            ]);
            $bookmarked = true;
        }

        return response()->json([
            'bookmarked' => $bookmarked,
        ]);
    }

    public function storeList(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:300'],
        ]);

        $request->user()->readingLists()->create($data);

        return back()->with('success', 'Reading list created.');
    }

    public function destroyList(
        Request $request,
        ReadingList $readingList
    ) {
        $this->authorizeList($request, $readingList);
        $readingList->delete();

        return back()->with('success', 'Reading list deleted.');
    }

    public function addToList(
        Request $request,
        ReadingList $readingList,
        Post $post
    ) {
        $this->authorizeList($request, $readingList);

        if (!$post->isVisibleTo($request->user()->id)) {
            abort(404);
        }

        $readingList->posts()->syncWithoutDetaching([
            $post->id,
        ]);

        return response()->json(['added' => true]);
    }

    public function removeFromList(
        Request $request,
        ReadingList $readingList,
        Post $post
    ) {
        $this->authorizeList($request, $readingList);
        $readingList->posts()->detach($post->id);

        return response()->json(['removed' => true]);
    }

    private function authorizeList(
        Request $request,
        ReadingList $readingList
    ): void {
        if ($readingList->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
