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

        } else {

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

        return back();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostRequest $request, Post $post){

        $data = $request->validated();

        $user = $request->user();

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
                'body' => $data['body'] ?? null,
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
