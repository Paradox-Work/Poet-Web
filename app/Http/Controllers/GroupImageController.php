<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GroupImageController extends Controller
{

    public function updateImage(
        Request $request,
        Group $group
    ) {
        $user = $request->user();

        if (!$group->isAdmin($user->id)) {
            abort(
                403,
                "You don't have permission to update this group."
            );
        }

        $data = $request->validate([
            'cover' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'thumbnail' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ]);

        $message = null;

        if ($cover = $data['cover'] ?? null) {

            if ($group->cover_path) {
                Storage::disk('public')
                    ->delete($group->cover_path);
            }

            $path = $cover->store(
                'groups/' . $group->id,
                'public'
            );

            $group->update([
                'cover_path' => $path,
            ]);

            $message =
                'Group cover image updated.';
        }

        if ($thumbnail =
            $data['thumbnail'] ?? null) {

            if ($group->thumbnail_path) {
                Storage::disk('public')
                    ->delete($group->thumbnail_path);
            }

            $path = $thumbnail->store(
                'groups/' . $group->id,
                'public'
            );

            $group->update([
                'thumbnail_path' => $path,
            ]);

            $message =
                'Group thumbnail updated.';
        }

        return back()->with(
            'success',
            $message
        );
    }
}
