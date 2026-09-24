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
        $posts = Post::query()->latest()->paginate(20);
        
        return inertia::render('Home', [
            'posts' => PostResource::collection($posts)
        ]);
    }
}
