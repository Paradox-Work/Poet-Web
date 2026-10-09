<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PoemController extends Controller
{
    public function writePoem(
            Request $request
        ) {
            $drafts = Post::query()
                ->where(
                    'user_id',
                    $request->user()->id
                )
                ->where(
                    'status',
                    'draft'
                )
                ->where(
                    'type',
                    'poem'
                )
                ->latest('draft_saved_at')
                ->get()
                ->map(
                    fn (Post $draft) => [
                        'id' => $draft->id,
                        'status' => 'draft',
                        'type' => 'poem',
                        'content_rating' =>
                            $draft->content_rating
                                ?? 'general',
                        'poem_form' =>
                            $draft->poem_form
                                ?? 'free_verse',
                        'poem_genres' =>
                            $draft->poem_genres ?? [],
                        'title' => $draft->title,
                        'caption' => $draft->caption,
                        'hashtags' =>
                            $draft->hashtags ?? [],
                        'body' => $draft->body,
                        'group_id' =>
                            $draft->group_id,
                        'draft_saved_at' =>
                            $draft->draft_saved_at
                                ?->toISOString(),
                    ]
                );
    
            $editPoem = null;
    
            if ($editId = $request->integer('edit')) {
                $post = Post::query()
                    ->whereKey($editId)
                    ->where(
                        'user_id',
                        $request->user()->id
                    )
                    ->where(
                        'status',
                        'published'
                    )
                    ->where(
                        'type',
                        'poem'
                    )
                    ->firstOrFail();
    
                $editPoem = [
                    'id' => $post->id,
                    'status' => 'published',
                    'type' => 'poem',
                    'content_rating' =>
                        $post->content_rating
                            ?? 'general',
                    'poem_form' =>
                        $post->poem_form
                            ?? 'free_verse',
                    'poem_genres' =>
                        $post->poem_genres ?? [],
                    'title' => $post->title,
                    'caption' => $post->caption,
                    'hashtags' =>
                        $post->hashtags ?? [],
                    'body' => $post->body,
                    'group_id' =>
                        $post->group_id,
                ];
            }
    
            return inertia(
                'Poem/Write',
                [
                    'drafts' => $drafts,
                    'openDraftId' =>
                        $request->integer('draft')
                            ?: null,
                    'groupId' =>
                        $request->integer('group')
                            ?: null,
                    'editPoem' => $editPoem,
                ]
            );
        }
}
