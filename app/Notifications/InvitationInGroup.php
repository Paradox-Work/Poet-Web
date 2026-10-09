<?php

namespace App\Notifications;

use App\Models\Group;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvitationInGroup extends Notification
{
    use Queueable;

    public function __construct(
        public Group $group,
        public int $hours,
        public string $token
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
                'Invitation to join ' .
                $this->group->name
            )
            ->line(
                'You have been invited to join the group "' .
                $this->group->name .
                '".'
            )
            ->action(
                'Accept invitation',
                route(
                    'group.approveInvitation',
                    [
                        'token' =>
                            $this->token,
                    ]
                )
            )
            ->line(
                "This invitation expires in {$this->hours} hours."
            );
    }

    public function toArray(
        object $notifiable
    ): array {
        return [
            'kind' =>
                'group_invitation',

            'title' =>
                'Group invitation',

            'message' =>
                'You were invited to join "' .
                $this->group->name .
                '".',

            'group_id' =>
                $this->group->id,

            'group_name' =>
                $this->group->name,

            'group_slug' =>
                $this->group->slug,

            'invitation_token' =>
                $this->token,

            'expires_at' =>
                now()
                    ->addHours(
                        $this->hours
                    )
                    ->toISOString(),

            'action_label' =>
                'Accept',

            'action_url' =>
                route(
                    'group.approveInvitation',
                    [
                        'token' =>
                            $this->token,
                    ]
                ),

            'decline_url' =>
                route(
                    'group.declineInvitation',
                    [
                        'token' =>
                            $this->token,
                    ]
                ),
        ];
    }
}
