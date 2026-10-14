<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $urls = collect([
            url('/'),
            route('about'),
            route('group.index'),
            route('privacy'),
            route('terms'),
            route('community'),
            route('copyright'),
        ]);

        User::query()
            ->orderBy('id')
            ->select(['id', 'username'])
            ->chunk(500, function ($users) use ($urls) {
                foreach ($users as $user) {
                    $urls->push(
                        route('profile', $user->username)
                    );
                }
            });

        Group::query()
            ->where('visibility', 'public')
            ->orderBy('id')
            ->select(['id', 'slug'])
            ->chunk(500, function ($groups) use ($urls) {
                foreach ($groups as $group) {
                    $urls->push(
                        route('group.profile', $group->slug)
                    );
                }
            });

        Post::postsForTimeline(null)
            ->reorder()
            ->select('posts.id')
            ->chunk(500, function ($posts) use ($urls) {
                foreach ($posts as $post) {
                    $urls->push(
                        route('post.view', $post->id)
                    );
                }
            });

        $body = view('sitemap', [
            'urls' => $urls->unique()->values(),
        ])->render();

        return response(
            $body,
            200,
            ['Content-Type' => 'application/xml; charset=UTF-8']
        );
    }
}
