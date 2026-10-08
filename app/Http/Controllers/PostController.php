<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Notifications\CommentCreated;
use App\Notifications\PostCreated;
use App\Notifications\ReactionAddedOnComment;
use App\Notifications\ReactionAddedOnPost;
use Illuminate\Support\Facades\Notification;
use App\Notifications\CommentDeleted;
use App\Notifications\PostDeleted;
use App\Models\Post;
use App\Models\Group;
use App\Models\PostAttachment;
use Illuminate\Http\Request;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Enums\ReactionEnum;
use Illuminate\Validation\Rule;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Http\Requests\UpdateCommentRequest;

class PostController extends Controller
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

    private function notifyPostPublished(
        Post $post,
        $user
    ): void {
        $group =
            $post->group;

        if ($group) {
            $users =
                $group
                    ->approvedUsers()
                    ->where(
                        'users.id',
                        '!=',
                        $user->id
                    )
                    ->get();

            Notification::send(
                $users,
                new PostCreated(
                    $post,
                    $user,
                    $group
                )
            );

            return;
        }

        $followers =
            $user
                ->followers()
                ->get();

        Notification::send(
            $followers,
            new PostCreated(
                $post,
                $user
            )
        );
    }

    private function ensurePublished(
        Post $post
    ): void {
        if (
            $post->status !==
                'published'
        ) {
            abort(404);
        }
    }

    public function pinUnpin(
        Request $request,
        Post $post
    ) {
        $this->ensurePublished(
            $post
        );

        $user = $request->user();

        $data = $request->validate([
            'scope' => [
                'required',
                Rule::in([
                    'profile',
                    'group',
                ]),
            ],
        ]);

        $pinned = false;

        if ($data['scope'] === 'group') {
            $group = $post->group;

            if (!$group) {
                abort(
                    422,
                    'Only group posts can be pinned to a group.'
                );
            }

            if (!$group->isAdmin($user->id)) {
                abort(
                    403,
                    "You don't have permission to pin posts in this group."
                );
            }

            $pinned =
                $group->pinned_post_id !==
                $post->id;

            $group->pinned_post_id =
                $pinned
                    ? $post->id
                    : null;

            $group->save();
        } else {
            if ($post->user_id !== $user->id) {
                abort(
                    403,
                    "You can only pin your own posts to your profile."
                );
            }

            $pinned =
                $user->pinned_post_id !==
                $post->id;

            $user->pinned_post_id =
                $pinned
                    ? $post->id
                    : null;

            $user->save();
        }

        return back()->with(
            'success',
            $pinned
                ? 'Post pinned successfully.'
                : 'Post unpinned successfully.'
        );
    }


    public function view(
        Request $request,
        Post $post
    ) {
        $userId =
            $request->user()->id;

        /*
        * Re-query through the same visibility
        * rules used by the timeline.
        *
        * Normal posts are visible.
        * Group posts require approved membership.
        */
        $post =
            Post::postsForTimeline(
                $userId
            )
                ->whereKey($post->id)
                ->firstOrFail();

        return inertia(
            'Post/View',
            [
                'post' =>
                    (new PostResource($post))
                        ->resolve($request),
            ]
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request)
    {
        $data = $request->validated();

        $user = $request->user();


        $files = $data['attachments'] ?? [];

        unset($data['attachments']);

        $data['status'] = 'published';
        $data['published_at'] = now();
        $data['draft_saved_at'] = null;


        DB::beginTransaction();


        $storedPaths = [];


        try {

            $post = Post::create($data);


            foreach ($files as $file) {

                $path = $file->store(
                    'attachments/' . $post->id,
                    'public'
                );


                $storedPaths[] = $path;


                PostAttachment::create([
                    'post_id' => $post->id,

                    'name' =>
                        $file->getClientOriginalName(),

                    'path' => $path,

                    'url' =>
                        Storage::disk('public')->url($path),

                    'mime' =>
                        $file->getMimeType(),

                    'size' =>
                        $file->getSize(),

                    'created_by' =>
                        $user->id,
                ]);

            }


            DB::commit();

        } catch (\Throwable $exception) {

            foreach ($storedPaths as $path) {

                Storage::disk('public')->delete(
                    $path
                );

            }


            DB::rollBack();


            throw $exception;
        }

        $this->notifyPostPublished(
            $post,
            $user
        );

        return back();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostRequest $request, Post $post){

        $data = $request->validated();

        $user = $request->user();

        $wasDraft =
            $post->status === 'draft';

        $files = $data['attachments'] ?? [];

        $deletedIds =
            $data['deleted_file_ids'] ?? [];


        $attachmentsToDelete =
            PostAttachment::query()
                ->where('post_id', $post->id)
                ->whereIn('id', $deletedIds)
                ->get();


        $storedPaths = [];


        DB::beginTransaction();


        try {

            $post->update([
                'type' => $data['type'],
                'poem_form' =>
                    $data['type'] === 'poem'
                        ? ($data['poem_form'] ?? 'free_verse')
                        : null,
                'poem_genres' =>
                    $data['type'] === 'poem'
                        ? ($data['poem_genres'] ?? [])
                        : [],
                'title' =>
                    $data['type'] === 'poem'
                        ? ($data['title'] ?? null)
                        : null,
                'caption' =>
                    $data['type'] === 'poem'
                        ? ($data['caption'] ?? null)
                        : null,
                'hashtags' =>
                    $data['hashtags'] ?? [],
                'body' => $data['body'] ?? null,
                'status' => 'published',
                'published_at' =>
                    $wasDraft
                        ? now()
                        : $post->published_at,
                'draft_saved_at' => null,
            ]);


            foreach ($files as $file) {

                $path = $file->store(
                    'attachments/' . $post->id,
                    'public'
                );


                $storedPaths[] = $path;


                PostAttachment::create([
                    'post_id' => $post->id,

                    'name' =>
                        $file->getClientOriginalName(),

                    'path' => $path,

                    'url' =>
                        Storage::disk('public')
                            ->url($path),

                    'mime' =>
                        $file->getMimeType(),

                    'size' =>
                        $file->getSize(),

                    'created_by' =>
                        $user->id,
                ]);

            }


            foreach ($attachmentsToDelete as $attachment) {
                $attachment->delete();
            }


            DB::commit();


            foreach ($attachmentsToDelete as $attachment) {
                Storage::disk('public')
                    ->delete($attachment->path);
            }

        } catch (\Throwable $exception) {

            DB::rollBack();


            foreach ($storedPaths as $path) {
                Storage::disk('public')
                    ->delete($path);
            }


            throw $exception;
        }

        if ($wasDraft) {
            $this->notifyPostPublished(
                $post,
                $user
            );
        }

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(
        Request $request,
        Post $post
    ) {
        $userId = $request->user()->id;

        if (
            $post->status === 'draft' &&
            $post->user_id !== $userId
        ) {
            abort(404);
        }

        $isOwner =
            $post->user_id === $userId;

        $isGroupAdmin =
            $post->group &&
            $post->group->isAdmin($userId);

        if (
            !$isOwner &&
            !$isGroupAdmin
        ) {
            abort(
                403,
                "You don't have permission to delete this post."
            );
        }

        $postOwner = $post->user;
        $group = $post->group;

        /*
         * Keep an audit trail of who
         * performed the soft delete.
         */
        $post->deleted_by = $userId;
        $post->save();

        $post->delete();

        if (
            !$isOwner &&
            $group
        ) {
            $postOwner->notify(
                new PostDeleted($group)
            );
        }

        return back();
    }

    public function downloadAttachment(PostAttachment $attachment) {
        return Storage::disk('public')->download(
            $attachment->path,
            $attachment->name
        );
    }

    public function postReaction(
        Request $request,
        Post $post
    ) {
        $this->ensurePublished(
            $post
        );

        $data = $request->validate([
            'reaction' => [
                'required',
                Rule::enum(
                    ReactionEnum::class
                ),
            ],
        ]);

        $userId =
            $request->user()->id;

        $reaction =
            $post->reactions()
                ->where(
                    'user_id',
                    $userId
                )
                ->first();

        if ($reaction) {

            $reaction->delete();

            $hasReaction = false;

        } else {

            $post->reactions()->create([
                'user_id' =>
                    $userId,

                'type' =>
                    $data['reaction'],
            ]);

            $hasReaction = true;

            if (
                $post->user_id !==
                $userId
            ) {
                $post->user->notify(
                    new ReactionAddedOnPost(
                        $post,
                        $request->user()
                    )
                );
            }
        }

        return response()->json([
            'num_of_reactions' =>
                $post->reactions()->count(),

            'current_user_has_reaction' =>
                $hasReaction,
        ]);
    }

    public function commentReaction(
        Request $request,
        Comment $comment
    ) {
        $data = $request->validate([
            'reaction' => [
                'required',
                Rule::enum(
                    ReactionEnum::class
                ),
            ],
        ]);

        $userId =
            $request->user()->id;

        $reaction =
            $comment->reactions()
                ->where(
                    'user_id',
                    $userId
                )
                ->first();

        if ($reaction) {

            $reaction->delete();

            $hasReaction = false;

        } else {

            $comment->reactions()->create([
                'user_id' =>
                    $userId,

                'type' =>
                    $data['reaction'],
            ]);

            $hasReaction = true;

            if (
                $comment->user_id !==
                $userId
            ) {
                $comment->user->notify(
                    new ReactionAddedOnComment(
                        $comment->post,
                        $comment,
                        $request->user()
                    )
                );
            }
        }

        return response()->json([
            'num_of_reactions' =>
                $comment
                    ->reactions()
                    ->count(),

            'current_user_has_reaction' =>
                $hasReaction,
        ]);
    }

    public function createComment(
        Request $request,
        Post $post
    ) {
        $this->ensurePublished(
            $post
        );

        $data = $request->validate([
            'comment' => [
                'required',
                'string',
                'max:2000',
            ],

            'parent_id' => [
                'nullable',
                'integer',

                Rule::exists(
                    'comments',
                    'id'
                )->where(
                    fn ($query) =>
                        $query->where(
                            'post_id',
                            $post->id
                        )
                ),
            ],
        ]);

        $comment = $post
            ->comments()
            ->create([
                'comment' =>
                    $data['comment'],

                'user_id' =>
                    $request->user()->id,

                'parent_id' =>
                    $data['parent_id'] ?? null,
            ]);

            if (
                $post->user_id !==
                $request->user()->id
            ) {
                $post->user->notify(
                    new CommentCreated(
                        $comment
                    )
                );
            }

        return (
            new CommentResource($comment)
        )
            ->response()
            ->setStatusCode(201);
    }

public function updateComment(UpdateCommentRequest $request, Comment $comment) {
    $data = $request->validated();

    $comment->update([
        'comment' => $data['comment'],
    ]);

    $userId = $request->user()->id;

    $comment->loadCount([
        'reactions',
        'comments',
    ]);

    $comment->load([
        'user',

        'reactions' =>
            function ($query) use ($userId) {
                $query->where(
                    'user_id',
                    $userId
                );
            },
    ]);

    return new CommentResource($comment);
}

public function deleteComment(
    Request $request,
    Comment $comment
) {
    $userId =
        $request->user()->id;

    $post =
        $comment->post;

    $isCommentOwner =
        $comment->user_id ===
        $userId;

    $isPostOwner =
        $post->user_id ===
        $userId;

    if (
        !$isCommentOwner &&
        !$isPostOwner
    ) {
        abort(
            403,
            "You don't have permission to delete this comment."
        );
    }

    $commentOwner =
        $comment->user;

    $comment->delete();

    if (!$isCommentOwner) {
        $commentOwner->notify(
            new CommentDeleted(
                $comment,
                $post
            )
        );
    }

    return response()->json([
        'num_of_comments' =>
            $post->comments()->count(),
    ]);
}
}
