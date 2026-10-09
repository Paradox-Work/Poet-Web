<?php

namespace App\Notifications;

use App\Models\Group;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserRemovedFromGroup extends Notification
{
    use Queueable;

    public function __construct(
        public Group $group
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
                'Removed from ' .
                $this->group->name
            )
            ->line(
                'You have been removed from "' .
                $this->group->name .
                '" by a group administrator.'
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
                'group_removed',

            'title' =>
                'Removed from group',

            'message' =>
                'You were removed from "' .
                $this->group->name .
                '".',

            'action_label' =>
                'Open group',

            'action_url' =>
                route(
                    'group.profile',
                    $this->group->slug
                ),
        ];
    }
}
