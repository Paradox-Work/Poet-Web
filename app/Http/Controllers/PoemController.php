<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostCollaborator;
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
                    ->where('status', 'published')
                    ->where('type', 'poem')
                    ->where(function ($query) use ($request) {
                        $query
                            ->where('user_id', $request->user()->id)
                            ->orWhereHas(
                                'collaborators',
                                fn ($collaboratorQuery) =>
                                    $collaboratorQuery
                                        ->where('user_id', $request->user()->id)
                                        ->where('status', 'accepted')
                            );
                    })
                    ->firstOrFail();

                if (
                    $post->user_id !== $request->user()->id
                    && $post->group_id
                    && !$post->group?->hasApprovedUser($request->user()->id)
                ) {
                    abort(403);
                }
    
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
                    'is_collaborator' =>
                        $post->user_id !== $request->user()->id,
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
