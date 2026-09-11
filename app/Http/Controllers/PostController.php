<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Home', [
            'posts' => Post::with('user')->latest()->get(),
            'following' => auth()->user()->following()->get(),
        ]);
    }

    public function store(StorePostRequest $request): RedirectResponse
    {
        $request->user()->posts()->create([
            'title' => $request->validated('title'),
            'body' => $request->validated('body'),
            'published_at' => now(),
        ]);

        return redirect()->route('dashboard');
    }
}