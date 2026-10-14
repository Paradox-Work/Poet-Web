<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'created_at' => $this->created_at,
            'username' => $this->username,
            'pinned_post_id' => $this->pinned_post_id,
            'cover_url' => $this->cover_path ? Storage::url($this->cover_path) : null,
            'avatar_url' => $this->avatar_path
                ? Storage::url($this->avatar_path)
                : '/img/default_avatar.svg',
        ];

        if ($request->user()?->id === $this->id) {
            $data['email'] = $this->email;
            $data['email_verified_at'] = $this->email_verified_at;
            $data['updated_at'] = $this->updated_at;
            $data['is_site_admin'] = (bool) $this->is_site_admin;
        }

        return $data;
    }
}
