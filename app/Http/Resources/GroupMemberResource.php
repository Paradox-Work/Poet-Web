<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class GroupMemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,

            'avatar_url' =>
                $this->avatar_path
                    ? Storage::url($this->avatar_path)
                    : null,

            'role' => $this->pivot?->role,

            'status' => $this->pivot?->status,
        ];
    }
}