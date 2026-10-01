<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'parent_id' =>
                $this->parent_id,

            'comment' => $this->comment,

            'created_at' =>
                $this->created_at->format(
                    'Y-m-d H:i:s'
                ),

            'updated_at' =>
                $this->updated_at->format(
                    'Y-m-d H:i:s'
                ),

            'num_of_reactions' =>
                $this->reactions_count ?? 0,

            'current_user_has_reaction' =>
                $this->relationLoaded('reactions')
                    && $this->reactions->isNotEmpty(),

            'num_of_comments' =>
                0,

            'comments' =>
                [],
    
            'user' =>
                new UserResource($this->user),
        ];
    }
}