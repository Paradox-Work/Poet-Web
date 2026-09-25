<?php

namespace App\Http\Resources;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'name' => $this->name,

            'slug' => $this->slug,

            'status' => $this->status,

            'role' => $this->role,

            'thumbnail_url' =>
                $this->thumbnail_path
                    ? Storage::disk('public')
                        ->url($this->thumbnail_path)
                    : null,

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

            'created_at' =>
                $this->created_at,

            'updated_at' =>
                $this->updated_at,
        ];
    }
}