<?php

namespace App\Notifications;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class CommentCreated extends Notification
{
    use Queueable;

    public function __construct(
        public Comment $comment
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

        $post =
            $this->comment->post;

        $destination =
            $post->group
                ? route(
                    'group.profile',
                    $post->group->slug
                )
                : route('dashboard');

        return (new MailMessage)
            ->subject(
                'New comment on your post'
            )
            ->line(
                'Someone commented on your post.'
            )
            ->line(
                '"' .
                Str::words(
                    strip_tags(
                        $this->comment->comment
                    ),
                    12
                ) .
                '"'
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