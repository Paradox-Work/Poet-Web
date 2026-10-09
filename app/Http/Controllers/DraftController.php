<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DraftController extends Controller
{
    public function drafts(
            Request $request
        ) {
            $drafts = Post::query()
                ->with('group')
                ->where(
                    'user_id',
                    $request->user()->id
                )
                ->where(
                    'status',
                    'draft'
                )
                ->latest('draft_saved_at')
                ->get()
                ->map(
                    function (Post $draft) {
                        $plainBody =
                            trim(
                                preg_replace(
                                    '/\\s+/',
                                    ' ',
                                    strip_tags(
                                        $draft->body ?? ''
                                    )
                                )
                            );
    
                        return [
                            'id' => $draft->id,
                            'status' => 'draft',
                            'type' =>
                                $draft->type ?? 'poem',
                            'content_rating' =>
                                $draft->content_rating
                                    ?? 'general',
                            'poem_form' =>
                                $draft->poem_form
                                    ?? 'free_verse',
                        'poem_genres' =>
                            $draft->poem_genres ?? [],
                            'title' =>
                                $draft->title,
                            'caption' =>
                                $draft->caption,
                            'hashtags' =>
                                $draft->hashtags ?? [],
                            'body' =>
                                $draft->body,
                            'preview' =>
                                mb_strimwidth(
                                    $plainBody,
                                    0,
                                    180,
                                    '…'
                                ),
                            'group' =>
                                $draft->group
                                    ? [
                                        'id' =>
                                            $draft->group->id,
                                        'name' =>
                                            $draft->group->name,
                                        'slug' =>
                                            $draft->group->slug,
                                    ]
                                    : null,
                            'draft_saved_at' =>
                                $draft->draft_saved_at
                                    ?->toISOString(),
                        ];
                    }
                );
    
            return inertia(
                'Drafts/Index',
                [
                    'drafts' => $drafts,
                ]
            );
        }

    public function latestDraft(
            Request $request
        ) {
            $data = $request->validate([
                'type' => [
                    'required',
                    Rule::in([
                        'post',
                        'poem',
                    ]),
                ],
                'group_id' => [
                    'nullable',
                    'integer',
                    'exists:groups,id',
                ],
            ]);
    
            $draft = Post::query()
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
                    $data['type']
                )
                ->when(
                    $data['group_id'] ?? null,
                    fn ($query, $groupId) =>
                        $query->where(
                            'group_id',
                            $groupId
                        ),
                    fn ($query) =>
                        $query->whereNull(
                            'group_id'
                        )
                )
                ->latest('draft_saved_at')
                ->first();
    
            return response()->json([
                'draft' =>
                    $draft
                        ? [
                            'id' => $draft->id,
                            'type' => $draft->type,
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
                        : null,
            ]);
        }

    public function storeDraft(
            Request $request
        ) {
            $data =
                $this->validateDraft(
                    $request
                );
    
            $draft = Post::create([
                ...$data,
                'user_id' =>
                    $request->user()->id,
                'status' => 'draft',
                'draft_saved_at' => now(),
            ]);
    
            return response()->json([
                'draft' => [
                    'id' => $draft->id,
                    'draft_saved_at' =>
                        $draft->draft_saved_at
                            ?->toISOString(),
                ],
            ], 201);
        }

    public function updateDraft(
            Request $request,
            Post $post
        ) {
            if (
                $post->user_id !==
                    $request->user()->id ||
                $post->status !== 'draft'
            ) {
                abort(
                    403,
                    "You don't have permission to edit this draft."
                );
            }
    
            $data =
                $this->validateDraft(
                    $request
                );
    
            $post->update([
                ...$data,
                'draft_saved_at' => now(),
            ]);
    
            return response()->json([
                'draft' => [
                    'id' => $post->id,
                    'draft_saved_at' =>
                        $post->draft_saved_at
                            ?->toISOString(),
                ],
            ]);
        }

    private function validateDraft(
            Request $request
        ): array {
            $data = $request->validate([
                'type' => [
                    'required',
                    Rule::in([
                        'post',
                        'poem',
                    ]),
                ],
                'content_rating' => [
                    'required',
                    Rule::in([
                        'general',
                        'mature',
                    ]),
                ],
                'poem_form' => [
                    'nullable',
                    'string',
                    Rule::in([
                        'free_verse',
                        'haiku',
                        'tanka',
                        'shakespearean_sonnet',
                        'petrarchan_sonnet',
                        'limerick',
                        'villanelle',
                        'sestina',
                        'ballad',
                        'ode',
                        'elegy',
                        'acrostic',
                        'cinquain',
                        'ghazal',
                        'pantoum',
                        'rondeau',
                        'blank_verse',
                        'prose_poem',
                    ]),
                ],
                'poem_genres' => [
                    'nullable',
                    'array',
                    'max:8',
                ],
                'poem_genres.*' => [
                    'string',
                    'max:50',
                ],
                'title' => [
                    'nullable',
                    'string',
                    'max:160',
                ],
                'caption' => [
                    'nullable',
                    'string',
                    'max:500',
                ],
                'hashtags' => [
                    'nullable',
                    'array',
                    'max:10',
                ],
                'hashtags.*' => [
                    'string',
                    'max:50',
                    'regex:/^[\\pL\\pN_]+$/u',
                ],
                'body' => [
                    'nullable',
                    'string',
                ],
                'group_id' => [
                    'nullable',
                    'integer',
                    'exists:groups,id',
                ],
            ]);
    
            if ($groupId =
                $data['group_id'] ?? null) {
                $group =
                    Group::findOrFail(
                        $groupId
                    );
    
                if (
                    !$group->hasApprovedUser(
                        $request->user()->id
                    )
                ) {
                    abort(
                        403,
                        "You don't have permission to create drafts in this group."
                    );
                }
            }
    
            if (
                $data['type'] !== 'poem'
            ) {
                $data['poem_form'] = null;
                $data['poem_genres'] = [];
                $data['title'] = null;
                $data['caption'] = null;
            } else {
                $data['poem_form'] =
                    $data['poem_form']
                        ?? 'free_verse';
    
                $data['poem_genres'] =
                    array_values(
                        array_unique(
                            array_filter(
                                array_map(
                                    fn ($genre) =>
                                        trim($genre),
                                    $data['poem_genres']
                                        ?? []
                                )
                            )
                        )
                    );
            }
    
            $data['hashtags'] =
                $data['hashtags'] ?? [];
    
            return $data;
        }
}
