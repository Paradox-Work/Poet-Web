<?php

namespace App\Http\Controllers;


use App\Http\Requests\InviteUsersRequest;
use App\Notifications\InvitationApproved;
use App\Notifications\InvitationInGroup;
use Illuminate\Support\Str;
use App\Enums\GroupUserRole;
use App\Enums\GroupUserStatus;
use App\Http\Requests\StoreGroupRequest;
use App\Http\Resources\GroupResource;
use App\Models\Group;
use App\Models\GroupUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class GroupController extends Controller
{
    public function store(StoreGroupRequest $request)
    {
        $data = $request->validated();

        $user = $request->user();

        $group = DB::transaction(
            function () use ($data, $user) {

                $group = Group::create([
                    ...$data,
                    'user_id' => $user->id,
                ]);

                GroupUser::create([
                    'status' =>
                        GroupUserStatus::APPROVED->value,

                    'role' =>
                        GroupUserRole::ADMIN->value,

                    'user_id' =>
                        $user->id,

                    'group_id' =>
                        $group->id,

                    'created_by' =>
                        $user->id,
                ]);

                $group->status =
                    GroupUserStatus::APPROVED->value;

                $group->role =
                    GroupUserRole::ADMIN->value;

                return $group;
            }
        );

        return (
            new GroupResource($group)
        )
            ->response()
            ->setStatusCode(201);
    }

    public function profile(
        Request $request,
        Group $group
    ) {
        $userId = $request->user()?->id;

        if ($userId) {
            $membership = $group
                ->groupUsers()
                ->where('user_id', $userId)
                ->first();

            $group->status =
                $membership?->status;

            $group->role =
                $membership?->role;
        }

        return Inertia::render(
            'Group/View',
            [
                'group' =>
                    (new GroupResource($group))
                        ->resolve($request),

                'success' =>
                    session('success'),
            ]
        );
    }

    public function updateImage(
        Request $request,
        Group $group
    ) {
        $user = $request->user();

        if (!$group->isAdmin($user->id)) {
            abort(
                403,
                "You don't have permission to update this group."
            );
        }

        $data = $request->validate([
            'cover' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'thumbnail' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ]);

        $message = null;

        if ($cover = $data['cover'] ?? null) {

            if ($group->cover_path) {
                Storage::disk('public')
                    ->delete($group->cover_path);
            }

            $path = $cover->store(
                'groups/' . $group->id,
                'public'
            );

            $group->update([
                'cover_path' => $path,
            ]);

            $message =
                'Group cover image updated.';
        }

        if ($thumbnail =
            $data['thumbnail'] ?? null) {

            if ($group->thumbnail_path) {
                Storage::disk('public')
                    ->delete($group->thumbnail_path);
            }

            $path = $thumbnail->store(
                'groups/' . $group->id,
                'public'
            );

            $group->update([
                'thumbnail_path' => $path,
            ]);

            $message =
                'Group thumbnail updated.';
        }

        return back()->with(
            'success',
            $message
        );
    }

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
}