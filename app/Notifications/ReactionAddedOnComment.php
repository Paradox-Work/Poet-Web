<?php

namespace App\Notifications;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class ReactionAddedOnComment extends Notification
{
    use Queueable;

    public function __construct(
        public Post $post,
        public Comment $comment,
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
            $this->post->group
                ? route(
                    'group.profile',
                    $this->post->group->slug
                )
                : route('dashboard');

        return (new MailMessage)
            ->subject(
                'Someone liked your comment'
            )
            ->line(
                '@' .
                $this->user->username .
                ' liked your comment.'
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