<?php

namespace App\Http\Controllers;

use App\Enums\GroupUserRole;
use App\Enums\GroupUserStatus;
use App\Models\Group;
use App\Models\GroupUser;
use App\Notifications\GroupJoinRequestResolved;
use App\Notifications\GroupRoleChanged;
use App\Notifications\RequestToJoinGroup;
use App\Notifications\UserRemovedFromGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;

class GroupMembershipController extends Controller
{

    public function join(
        Request $request,
        Group $group
    ) {
        $user = $request->user();

        $membership = GroupUser::query()
            ->where(
                'user_id',
                $user->id
            )
            ->where(
                'group_id',
                $group->id
            )
            ->first();

        if (
            $membership?->status ===
            GroupUserStatus::APPROVED->value
        ) {
            return back()->with(
                'success',
                'You are already a member of this group.'
            );
        }

        if (
            $membership?->status ===
            GroupUserStatus::PENDING->value
        ) {
            return back()->with(
                'success',
                'Your membership is already pending.'
            );
        }

        $status = $group->auto_approval
            ? GroupUserStatus::APPROVED->value
            : GroupUserStatus::PENDING->value;

        GroupUser::updateOrCreate(
            [
                'user_id' => $user->id,
                'group_id' => $group->id,
            ],
            [
                'status' => $status,

                'role' =>
                    GroupUserRole::MEMBER->value,

                'created_by' =>
                    $user->id,

                'token' => null,

                'token_expire_date' => null,

                'token_used' => null,
            ]
        );

        if ($group->auto_approval) {
            return back()->with(
                'success',
                'You joined "' .
                $group->name .
                '".'
            );
        }

        Notification::send(
            $group->adminUsers,
            new RequestToJoinGroup(
                $group,
                $user
            )
        );

        return back()->with(
            'success',
            'Your request to join "' .
            $group->name .
            '" has been sent.'
        );
    }

    
    public function resolveJoinRequest(
        Request $request,
        Group $group
    ) {
        $user = $request->user();

        if (!$group->isAdmin($user->id)) {
            abort(
                403,
                "You don't have permission to manage group requests."
            );
        }

        $data = $request->validate([
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],

            'action' => [
                'required',
                Rule::in([
                    'approve',
                    'reject',
                ]),
            ],
        ]);

        $membership = GroupUser::query()
            ->with('user')
            ->where(
                'user_id',
                $data['user_id']
            )
            ->where(
                'group_id',
                $group->id
            )
            ->where(
                'status',
                GroupUserStatus::PENDING->value
            )
            ->whereNull('token')
            ->firstOrFail();

        $approved =
            $data['action'] === 'approve';

        $membership->update([
            'status' =>
                $approved
                    ? GroupUserStatus::APPROVED->value
                    : GroupUserStatus::REJECTED->value,
        ]);

        $membership->user->notify(
            new GroupJoinRequestResolved(
                $group,
                $approved
            )
        );

        return back()->with(
            'success',
            $membership->user->name .
            ($approved
                ? ' was approved.'
                : ' was rejected.')
        );
    }

    
    public function removeUser(
        Request $request,
        Group $group
    ) {
        $actor =
            $request->user();

        if (!$group->isAdmin($actor->id)) {
            abort(
                403,
                "You don't have permission to remove group members."
            );
        }

        $data = $request->validate([
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],
        ]);

        $userId =
            (int) $data['user_id'];

        if ($group->isOwner($userId)) {
            abort(
                403,
                "The group owner cannot be removed."
            );
        }

        $membership =
            GroupUser::query()
                ->with('user')
                ->where(
                    'group_id',
                    $group->id
                )
                ->where(
                    'user_id',
                    $userId
                )
                ->where(
                    'status',
                    GroupUserStatus::APPROVED->value
                )
                ->firstOrFail();

        $user =
            $membership->user;

        $membership->delete();

        $user->notify(
            new UserRemovedFromGroup(
                $group
            )
        );

        return back()->with(
            'success',
            $user->name .
            ' was removed from the group.'
        );
    }


    public function changeRole(
        Request $request,
        Group $group
    ) {
        $actor = $request->user();

        if (!$group->isAdmin($actor->id)) {
            abort(
                403,
                "You don't have permission to change group roles."
            );
        }

        $data = $request->validate([
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],

            'role' => [
                'required',
                Rule::enum(GroupUserRole::class),
            ],
        ]);

        if (
            $group->isOwner(
                (int) $data['user_id']
            )
        ) {
            abort(
                403,
                "The group owner's role cannot be changed."
            );
        }

        $membership = GroupUser::query()
            ->with('user')
            ->where(
                'group_id',
                $group->id
            )
            ->where(
                'user_id',
                $data['user_id']
            )
            ->where(
                'status',
                GroupUserStatus::APPROVED->value
            )
            ->firstOrFail();

        if (
            $membership->role ===
            $data['role']
        ) {
            return back();
        }

        $membership->update([
            'role' => $data['role'],
        ]);

        $membership->user->notify(
            new GroupRoleChanged(
                $group,
                $data['role']
            )
        );

        return back()->with(
            'success',
            $membership->user->name .
            ' is now ' .
            $data['role'] .
            '.'
        );
    }
}
