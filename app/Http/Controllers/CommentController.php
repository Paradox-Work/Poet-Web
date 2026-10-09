<?php

namespace App\Http\Controllers;

use App\Enums\ReactionEnum;
use App\Http\Requests\UpdateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Post;
use App\Notifications\CommentCreated;
use App\Notifications\CommentDeleted;
use App\Notifications\ReactionAddedOnComment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CommentController extends Controller
{

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
}
