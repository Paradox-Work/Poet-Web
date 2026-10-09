<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GroupSummaryResource extends JsonResource
{
    /**
     * Compact group data for browsing and
     * membership management.
     *
     * @return array<string, mixed>
     */
    public function toArray(
        Request $request
    ): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,

            'status' =>
                $this->relationLoaded(
                    'currentUserGroup'
                )
                    ? $this
                        ->currentUserGroup
                        ?->status
                    : null,

            'role' =>
                $this->relationLoaded(
                    'currentUserGroup'
                )
                    ? $this
                        ->currentUserGroup
                        ?->role
                    : null,

            'thumbnail_url' =>
                $this->thumbnail_path
                    ? Storage::disk(
                        'public'
                    )->url(
                        $this->thumbnail_path
                    )
                    : null,

            'cover_url' =>
                $this->cover_path
                    ? Storage::disk(
                        'public'
                    )->url(
                        $this->cover_path
                    )
                    : null,

            'auto_approval' =>
                (bool)
                $this->auto_approval,

            'about' =>
                $this->about,

            'description' =>
                Str::words(
                    $this->about ?? '',
                    18
                ),

            'member_count' =>
                (int) (
                    $this
                        ->approved_users_count
                    ?? 0
                ),
        ];
    }
}
