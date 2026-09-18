<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $posts = Post::query()->paginate(20);
        return inertia::render('Home', [
            'posts' => $posts
        ]);
    }
}
