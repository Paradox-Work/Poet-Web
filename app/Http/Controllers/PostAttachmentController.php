<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostAttachment;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostAttachmentController extends Controller
{
    public function viewAttachment(Request $request, PostAttachment $attachment)
    {
        $this->authorizeAttachment($request, $attachment);

        [$disk, $path] = $this->locate($attachment);

        return $disk->response($path, $attachment->name, [
            'Content-Type' => $attachment->mime ?: 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function downloadAttachment(Request $request, PostAttachment $attachment)
    {
        $this->authorizeAttachment($request, $attachment);

        [$disk, $path] = $this->locate($attachment);

        return $disk->download($path, $attachment->name);
    }

    private function authorizeAttachment(Request $request, PostAttachment $attachment): void
    {
        $post = Post::query()->whereKey($attachment->post_id)->firstOrFail();
        $userId = $request->user()?->id;

        if ($post->status === 'draft') {
            if (!$userId || $post->user_id !== $userId) {
                abort(404);
            }

            return;
        }

        if (!$post->isVisibleTo($userId)) {
            abort(404);
        }
    }

    private function locate(PostAttachment $attachment): array
    {
        foreach (['local', 'public'] as $diskName) {
            $disk = Storage::disk($diskName);

            if ($disk->exists($attachment->path)) {
                return [$disk, $attachment->path];
            }
        }

        abort(404);
    }
}
