<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;

class PostController extends Controller
{
    public function store(StorePostRequest $request)
    {
        Post::create([
            'user_id' => auth()->id(),
            'title' => $request->validated('title'),
            'body' => $request->validated('body'),
            'category' => $request->validated('category', 'general'),
        ]);

        return redirect()->route('dashboard');
    }

    public function update(UpdatePostRequest $request, Post $post)
    {
        //
    }

    public function destroy(Post $post)
    {
        //
    }
}