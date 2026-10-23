<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'name' => $this->name,

            'slug' => $this->slug,

            'status' =>
                $this->relationLoaded(
                    'currentUserGroup'
                )
                    ? $this->currentUserGroup?->status
                    : $this->status,

            'role' =>
                $this->relationLoaded(
                    'currentUserGroup'
                )
                    ? $this->currentUserGroup?->role
                    : $this->role,

            'pinned_post_id' =>
                $this->pinned_post_id,

            'thumbnail_url' =>
                $this->thumbnail_path
                    ? Storage::disk('public')
                        ->url($this->thumbnail_path)
                    : null,

            'cover_url' =>
                $this->cover_path
                    ? Storage::disk('public')
                        ->url($this->cover_path)
                    : null,

            'visibility' =>
                $this->visibility,

            'auto_approval' =>
                $this->auto_approval,

            'about' =>
                $this->about,

            'description' =>
                Str::words(
                    $this->about ?? '',
                    10
                ),

            'user_id' =>
                $this->user_id,

            'member_count' => (int) ($this->approved_users_count ?? 0),

            'owner' => $this->whenLoaded('owner', fn () => $this->owner ? [
                'name' => $this->owner->name,
                'username' => $this->owner->username,
            ] : null),

            'created_at' =>
                $this->created_at,

            'updated_at' =>
                $this->updated_at,
        ];
    }
}