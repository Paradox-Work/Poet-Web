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

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
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

    public function toArray(object $notifiable): array
    {
        return [];
    }
}