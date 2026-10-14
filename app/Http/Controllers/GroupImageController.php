<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class GroupImageController extends Controller
{
    public function updateImage(Request $request, Group $group)
    {
        if (!$group->isAdmin($request->user()->id)) {
            abort(403, "You don't have permission to update this group.");
        }

        $data = $request->validate([
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $newPaths = [];
        $oldPaths = [];
        $updates = [];

        try {
            if ($cover = $data['cover'] ?? null) {
                $path = $cover->store('groups/' . $group->id, 'public');
                $newPaths[] = $path;
                $updates['cover_path'] = $path;

                if ($group->cover_path) {
                    $oldPaths[] = $group->cover_path;
                }
            }

            if ($thumbnail = $data['thumbnail'] ?? null) {
                $path = $thumbnail->store('groups/' . $group->id, 'public');
                $newPaths[] = $path;
                $updates['thumbnail_path'] = $path;

                if ($group->thumbnail_path) {
                    $oldPaths[] = $group->thumbnail_path;
                }
            }

            if ($updates !== []) {
                DB::transaction(fn () => $group->update($updates));
            }
        } catch (\Throwable $exception) {
            foreach ($newPaths as $path) {
                Storage::disk('public')->delete($path);
            }

            throw $exception;
        }

        foreach (array_unique($oldPaths) as $path) {
            Storage::disk('public')->delete($path);
        }

        return back()->with(
            'success',
            $updates === [] ? 'No group image changes were submitted.' : 'Group images updated.'
        );
    }
}
