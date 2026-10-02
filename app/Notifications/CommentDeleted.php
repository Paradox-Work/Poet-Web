<?php

namespace App\Notifications;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class CommentDeleted extends Notification
{
    use Queueable;

    public function __construct(
        public Comment $comment,
        public Post $post
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
                'Your comment was removed'
            )
            ->line(
                'Your comment "' .
                Str::words(
                    strip_tags(
                        $this->comment->comment
                    ),
                    8
                ) .
                '" was removed by the post owner.'
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