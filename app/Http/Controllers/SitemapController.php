<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Post;
use App\Models\User;

class SitemapController extends Controller
{
    public function __invoke()
    {
        $urls = collect([
            route('dashboard'),
            route('group.index'),
            route('about'),
            route('legal.show', 'guidelines'),
            route('legal.show', 'privacy'),
            route('legal.show', 'terms'),
            route('legal.show', 'copyright'),
        ]);

        User::query()
            ->select('username')
            ->orderBy('id')
            ->chunk(
                200,
                function ($users) use ($urls) {
                    foreach ($users as $user) {
                        $urls->push(
                            route(
                                'profile',
                                $user->username
                            )
                        );
                    }
                }
            );

        Group::query()
            ->where(
                'visibility',
                'public'
            )
            ->select('slug')
            ->orderBy('id')
            ->chunk(
                200,
                function ($groups) use ($urls) {
                    foreach ($groups as $group) {
                        $urls->push(
                            route(
                                'group.profile',
                                $group->slug
                            )
                        );
                    }
                }
            );

        Post::query()
            ->where(
                'status',
                'published'
            )
            ->where(
                function ($query) {
                    $query
                        ->whereNull('group_id')
                        ->orWhereHas(
                            'group',
                            fn ($groupQuery) =>
                                $groupQuery->where(
                                    'visibility',
                                    'public'
                                )
                        );
                }
            )
            ->select('id')
            ->orderBy('id')
            ->chunk(
                200,
                function ($posts) use ($urls) {
                    foreach ($posts as $post) {
                        $urls->push(
                            route(
                                'post.view',
                                $post->id
                            )
                        );
                    }
                }
            );

        $xml =
            '<?xml version="1.0" encoding="UTF-8"?>'
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach (
            $urls->unique()
            as $url
        ) {
            $xml .=
                '<url><loc>'
                . htmlspecialchars(
                    $url,
                    ENT_XML1
                )
                . '</loc></url>';
        }

        $xml .= '</urlset>';

        return response(
            $xml,
            200,
            [
                'Content-Type' =>
                    'application/xml; charset=UTF-8',
            ]
        );
    }
}
