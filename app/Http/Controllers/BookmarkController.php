<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Models\Bookmark;
use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BookmarkController extends Controller
{
    public function index(Request $request)
    {
        $posts = PostResource::collection(
            Post::postsForTimeline($request->user()->id)
                ->whereHas(
                    'bookmarks',
                    fn ($query) =>
                        $query->where(
                            'user_id',
                            $request->user()->id
                        )
                )
                ->paginate(12)
                ->withQueryString()
        );

        if ($request->wantsJson()) {
            return $posts;
        }

        return Inertia::render('Saved/Index', [
            'posts' => $posts,
        ]);
    }

    public function toggle(Request $request, Post $post)
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

        if ($request->wantsJson()) {
            return response()->json([
                'bookmarked' => $bookmarked,
            ]);
        }

        return back();
    }
}
