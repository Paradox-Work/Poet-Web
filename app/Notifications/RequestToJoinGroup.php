<?php

namespace App\Notifications;

use App\Models\Group;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RequestToJoinGroup extends Notification
{
    use Queueable;

    public function __construct(
        public Group $group,
        public User $user
    ) {
    }

    public function via(
        object $notifiable
    ): array {
        return [
            'mail',
            'database',
        ];
    }

    public function toMail(
        object $notifiable
    ): MailMessage {
        return (new MailMessage)
            ->subject(
                'New request to join ' .
                $this->group->name
            )
            ->line(
                $this->user->name .
                ' requested to join the group "' .
                $this->group->name .
                '".'
            )
            ->action(
                'Open group',
                route(
                    'group.profile',
                    $this->group->slug
                )
            );
    }

    public function toArray(
        object $notifiable
    ): array {
        return [
            'kind' =>
                'group_join_request',

            'title' =>
                'New join request',

            'message' =>
                $this->user->name .
                ' wants to join "' .
                $this->group->name .
                '".',

            'action_label' =>
                'Review',

            'action_url' =>
                route(
                    'group.profile',
                    $this->group->slug
                ),
        ];
    }
}
