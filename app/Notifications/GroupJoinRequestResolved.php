<?php

namespace App\Notifications;

use App\Models\Group;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GroupJoinRequestResolved extends Notification
{
    use Queueable;

    public function __construct(
        public Group $group,
        public bool $approved
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $action = $this->approved
            ? 'approved'
            : 'rejected';

        return (new MailMessage)
            ->subject(
                'Group membership request ' .
                $action
            )
            ->line(
                'Your request to join "' .
                $this->group->name .
                '" has been ' .
                $action .
                '.'
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