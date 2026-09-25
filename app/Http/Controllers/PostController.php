<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostAttachment;
use Illuminate\Http\Request;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{

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
    public function destroy(Post $post)
    {
        if ($post->user_id !== auth()->id()) {
            abort(403, "You don't have permission to delete this post.");
        }

        $post->delete();

        return back();
    }

    public function downloadAttachment(PostAttachment $attachment) {
        return Storage::disk('public')->download(
            $attachment->path,
            $attachment->name
        );
    }
}
