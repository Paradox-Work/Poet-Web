<?php

namespace App\Http\Controllers;

use App\Enums\ReactionEnum;
use App\Models\Post;
use App\Notifications\ReactionAddedOnPost;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PostInteractionController extends Controller
{

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
