<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $posts = Post::query()
            ->with(['user', 'comments.user'])
            ->latest()
            ->paginate(20);

        return Inertia::render('Home', [
            'posts' => $posts,
        ]);
    }
}
