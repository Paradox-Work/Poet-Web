<?php

namespace App\Notifications;

use App\Models\Group;
use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PostCreated extends Notification
{
    use Queueable;

    public function __construct(
        public Post $post,
        public User $author,
        public ?Group $group = null
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

        if ($this->group) {

            return (new MailMessage)
                ->subject(
                    'New post in ' .
                    $this->group->name
                )
                ->line(
                    '@' .
                    $this->author->username .
                    ' published a new post in "' .
                    $this->group->name .
                    '".'
                )
                ->action(
                    'View post',
                    route(
                        'post.view',
                        $this->post->id
                    )
                );
        }


        return (new MailMessage)
            ->subject(
                'New post from @' .
                $this->author->username
            )
            ->line(
                '@' .
                $this->author->username .
                ' published a new post.'
            )
            ->action(
                'View post',
                route(
                    'post.view',
                    $this->post->id
                )
            );
    }

    public function toArray(
        object $notifiable
    ): array {
        return [];
    }
}