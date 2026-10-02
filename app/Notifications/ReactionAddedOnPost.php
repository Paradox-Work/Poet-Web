<?php

namespace App\Notifications;

use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReactionAddedOnPost extends Notification
{
    use Queueable;

    public function __construct(
        public Post $post,
        public User $user
    ) {
    }

    public function via(
        object $notifiable
    ): array {
        return ['mail'];
    }

    public function toMail(
        object $notifiable
    ): MailMessage {

        $destination =
            route(
                'post.view',
                $this->post->id
            );

        return (new MailMessage)
            ->subject(
                'Someone liked your post'
            )
            ->line(
                '@' .
                $this->user->username .
                ' liked your post.'
            )
            ->action(
                'View posts',
                $destination
            );
    }

    public function toArray(
        object $notifiable
    ): array {
        return [];
    }
}