<?php

namespace App\Notifications;

use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CollaborationInvited extends Notification
{
    use Queueable;

    public function __construct(
        public Post $post,
        public User $inviter
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'collaboration_invited',
            'title' => 'Poem collaboration invitation',
            'message' =>
                '@' . $this->inviter->username
                . ' invited you to edit "'
                . ($this->post->title ?: 'Untitled poem')
                . '".',
            'action_label' => 'Review collaborations',
            'action_url' => route('collaborations.index'),
        ];
    }
}
