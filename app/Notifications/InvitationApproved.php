<?php

namespace App\Notifications;

use App\Models\Group;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvitationApproved extends Notification
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
                'Group invitation accepted'
            )
            ->line(
                $this->user->name .
                ' accepted the invitation to join "' .
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
                'group_invitation_accepted',

            'title' =>
                'Invitation accepted',

            'message' =>
                $this->user->name .
                ' joined "' .
                $this->group->name .
                '" from your invitation.',

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
