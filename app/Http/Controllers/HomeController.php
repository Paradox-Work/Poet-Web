<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use Illuminate\Http\Request;
use App\Models\Post;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;

       $posts = Post::query()
            ->withCount([
                'reactions',
                'comments',
            ])
            ->with([
                'comments.user',

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
        
        return Inertia::render('Home', [
            'posts' => PostResource::collection($posts)
        ]);
    }
}
