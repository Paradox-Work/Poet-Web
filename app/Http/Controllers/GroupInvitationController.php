<?php

namespace App\Http\Controllers;

use App\Enums\GroupUserRole;
use App\Enums\GroupUserStatus;
use App\Http\Requests\InviteUsersRequest;
use App\Models\Group;
use App\Models\GroupUser;
use App\Notifications\InvitationApproved;
use App\Notifications\InvitationInGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class GroupInvitationController extends Controller
{
    public function inviteUsers(
            InviteUsersRequest $request,
            Group $group
        ) {
            $user = $request->invitedUser();
    
            $hours = 24;
    
            $token = Str::random(64);
    
            GroupUser::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'group_id' => $group->id,
                ],
                [
                    'status' =>
                        GroupUserStatus::PENDING->value,
    
                    'role' =>
                        GroupUserRole::MEMBER->value,
    
                    'token' => $token,
    
                    'token_expire_date' =>
                        now()->addHours($hours),
    
                    'token_used' => null,
    
                    'created_by' =>
                        $request->user()->id,
                ]
            );
    
            $user->notify(
                new InvitationInGroup(
                    $group,
                    $hours,
                    $token
                )
            );
    
            return back()->with(
                'success',
                'Invitation sent.'
            );
        }

    public function showInvitation(
            Request $request,
            string $token
        ) {
            $groupUser =
                GroupUser::query()
                    ->with('group')
                    ->where(
                        'token',
                        $token
                    )
                    ->firstOrFail();
    
            if (
                $groupUser->user_id !==
                $request->user()->id
            ) {
                abort(
                    403,
                    'This invitation belongs to another user.'
                );
            }
    
            if (
                $groupUser->token_used ||
                $groupUser->status !==
                    GroupUserStatus::PENDING->value
            ) {
                return redirect()
                    ->route('group.index')
                    ->with(
                        'success',
                        'This invitation has already been resolved.'
                    );
            }
    
            if (
                !$groupUser->token_expire_date ||
                $groupUser
                    ->token_expire_date
                    ->isPast()
            ) {
                abort(
                    410,
                    'This invitation has expired.'
                );
            }
    
            return Inertia::render(
                'Group/Invitation',
                [
                    'invitation' => [
                        'group' => [
                            'name' =>
                                $groupUser
                                    ->group
                                    ->name,
    
                            'slug' =>
                                $groupUser
                                    ->group
                                    ->slug,
                        ],
    
                        'expires_at' =>
                            $groupUser
                                ->token_expire_date
                                ->toISOString(),
    
                        'accept_url' =>
                            route(
                                'group.approveInvitation',
                                [
                                    'token' =>
                                        $token,
                                ]
                            ),
    
                        'decline_url' =>
                            route(
                                'group.declineInvitation',
                                [
                                    'token' =>
                                        $token,
                                ]
                            ),
                    ],
                ]
            );
        }

    public function approveInvitation(
            Request $request,
            string $token
        ) {
            $groupUser =
                GroupUser::query()
                    ->with([
                        'group',
                        'user',
                        'adminUser',
                    ])
                    ->where('token', $token)
                    ->firstOrFail();
    
            if (
                $groupUser->user_id !==
                $request->user()->id
            ) {
                abort(
                    403,
                    'This invitation belongs to another user.'
                );
            }
    
            if (
                $groupUser->token_used ||
                $groupUser->status ===
                    GroupUserStatus::APPROVED->value
            ) {
                return redirect()
                    ->route(
                        'group.profile',
                        $groupUser->group->slug
                    )
                    ->with(
                        'success',
                        'This invitation has already been accepted.'
                    );
            }
    
            if (
                !$groupUser->token_expire_date ||
                $groupUser->token_expire_date->isPast()
            ) {
                abort(
                    410,
                    'This invitation has expired.'
                );
            }
    
            $groupUser->update([
                'status' =>
                    GroupUserStatus::APPROVED->value,
    
                'token_used' => now(),
            ]);
    
            $this->markInvitationNotificationRead(
                $request->user(),
                $token
            );
    
            $groupUser->adminUser?->notify(
                new InvitationApproved(
                    $groupUser->group,
                    $groupUser->user
                )
            );
    
            return redirect()
                ->route(
                    'group.profile',
                    $groupUser->group->slug
                )
                ->with(
                    'success',
                    'You joined "' .
                    $groupUser->group->name .
                    '".'
                );
        }

    public function declineInvitation(
            Request $request,
            string $token
        ) {
            $groupUser =
                GroupUser::query()
                    ->with('group')
                    ->where(
                        'token',
                        $token
                    )
                    ->firstOrFail();
    
            if (
                $groupUser->user_id !==
                $request->user()->id
            ) {
                abort(
                    403,
                    'This invitation belongs to another user.'
                );
            }
    
            if (
                $groupUser->status ===
                GroupUserStatus::APPROVED->value
            ) {
                return back()->with(
                    'success',
                    'You already joined this group.'
                );
            }
    
            if ($groupUser->token_used) {
                return back()->with(
                    'success',
                    'This invitation has already been resolved.'
                );
            }
    
            if (
                !$groupUser->token_expire_date ||
                $groupUser
                    ->token_expire_date
                    ->isPast()
            ) {
                abort(
                    410,
                    'This invitation has expired.'
                );
            }
    
            $groupUser->update([
                'status' =>
                    GroupUserStatus::REJECTED->value,
    
                'token_used' => now(),
            ]);
    
            $this->markInvitationNotificationRead(
                $request->user(),
                $token
            );
    
            return back()->with(
                'success',
                'Invitation to "' .
                $groupUser->group->name .
                '" declined.'
            );
        }

    private function markInvitationNotificationRead(
            object $user,
            string $token
        ): void {
            $notification =
                $user
                    ->unreadNotifications()
                    ->get()
                    ->first(
                        function ($notification) use (
                            $token
                        ) {
                            return (
                                $notification
                                    ->data['kind']
                                    ?? null
                            ) ===
                                'group_invitation' &&
                                (
                                    $notification
                                        ->data['invitation_token']
                                        ?? null
                                ) ===
                                $token;
                        }
                    );
    
            $notification?->markAsRead();
        }
}
