<?php

namespace App\Notifications;

use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CollaborationAccepted extends Notification
{
    use Queueable;

    public function __construct(
        public Post $post,
        public User $collaborator
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'collaboration_accepted',
            'title' => 'Collaboration accepted',
            'message' =>
                '@' . $this->collaborator->username
                . ' accepted your invitation to edit "'
                . ($this->post->title ?: 'Untitled poem')
                . '".',
            'action_label' => 'Manage collaborators',
            'action_url' => route(
                'collaborations.manage',
                $this->post->id
            ),
        ];
    }
}
