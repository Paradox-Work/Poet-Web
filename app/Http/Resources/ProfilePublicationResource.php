<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfilePublicationResource extends JsonResource
{
    /**
     * Lightweight representation used by
     * the profile publication gallery.
     *
     * @return array<string, mixed>
     */
    public function toArray(
        Request $request
    ): array {
        return [
            'id' => $this->id,
            'type' =>
                $this->type ?? 'post',
            'content_rating' =>
                $this->content_rating
                    ?? 'general',
            'title' =>
                $this->title,
            'body' =>
                $this->body,
            'poem_form' =>
                $this->poem_form,
            'poem_genres' =>
                $this->poem_genres ?? [],
            'created_at' =>
                $this->created_at
                    ->format('Y-m-d H:i:s'),

            'attachments' =>
                PostAttachmentResource::collection(
                    $this->whenLoaded(
                        'attachments'
                    )
                ),

            'num_of_reactions' =>
                $this->reactions_count ?? 0,

            'num_of_comments' =>
                $this->comments_count ?? 0,
        ];
    }
}
