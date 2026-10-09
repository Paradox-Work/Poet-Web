<?php

namespace App\Notifications;

use App\Models\Group;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GroupRoleChanged extends Notification
{
    use Queueable;

    public function __construct(
        public Group $group,
        public string $role
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
                'Your role changed in ' .
                $this->group->name
            )
            ->line(
                'Your role in "' .
                $this->group->name .
                '" has been changed to "' .
                $this->role .
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
                'group_role_changed',

            'title' =>
                'Group role changed',

            'message' =>
                'Your role in "' .
                $this->group->name .
                '" is now ' .
                $this->role .
                '.',

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
