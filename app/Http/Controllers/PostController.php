<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Models\PostAttachment;
use App\Notifications\PostCreated;
use App\Notifications\PostDeleted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

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
                    'content_rating' =>
                        $data['content_rating'],
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
}
