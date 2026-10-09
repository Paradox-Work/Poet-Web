<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostAttachmentController extends Controller
{

    public function downloadAttachment(
        Request $request,
        PostAttachment $attachment
    ) {
        $userId =
            $request->user()->id;

        $post =
            Post::query()
                ->whereKey(
                    $attachment->post_id
                )
                ->firstOrFail();

        if (
            $post->status === 'draft'
        ) {
            if (
                $post->user_id !==
                $userId
            ) {
                abort(404);
            }
        } else {
            Post::postsForTimeline(
                $userId
            )
                ->whereKey(
                    $post->id
                )
                ->firstOrFail();
        }

        abort_unless(
            Storage::disk('public')
                ->exists(
                    $attachment->path
                ),
            404
        );

        return Storage::disk('public')
            ->download(
                $attachment->path,
                $attachment->name
            );
    }
}
