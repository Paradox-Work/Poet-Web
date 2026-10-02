<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FollowUser extends Notification
{
    use Queueable;

    public function __construct(
        public User $user,
        public bool $follow = true
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

        $message =
            $this->follow
                ? '@' .
                    $this->user->username .
                    ' started following you.'
                : '@' .
                    $this->user->username .
                    ' unfollowed you.';

        return (new MailMessage)
            ->subject(
                $this->follow
                    ? 'New follower'
                    : 'Follower update'
            )
            ->line($message)
            ->action(
                'View profile',
                route(
                    'profile',
                    [
                        'user' =>
                            $this->user->username
                    ]
                )
            );
    }

    public function toArray(
        object $notifiable
    ): array {
        return [];
    }
}