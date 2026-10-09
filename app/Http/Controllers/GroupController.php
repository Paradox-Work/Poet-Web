<?php

namespace App\Http\Controllers;

use App\Notifications\UserRemovedFromGroup;
use App\Notifications\GroupJoinRequestResolved;
use App\Notifications\GroupRoleChanged;
use App\Notifications\InvitationApproved;
use App\Notifications\InvitationInGroup;
use App\Http\Resources\GroupMemberResource;
use Illuminate\Validation\Rule;
use App\Http\Resources\UserResource;
use App\Http\Resources\PostAttachmentResource;
use App\Models\PostAttachment;
use App\Notifications\RequestToJoinGroup;
use Illuminate\Support\Facades\Notification;
use App\Http\Requests\InviteUsersRequest;
use Illuminate\Support\Str;
use App\Enums\GroupUserRole;
use App\Enums\GroupUserStatus;
use App\Http\Requests\StoreGroupRequest;
use App\Http\Requests\UpdateGroupRequest;
use App\Http\Resources\GroupResource;
use App\Http\Resources\GroupSummaryResource;
use App\Models\Group;
use App\Models\GroupUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use App\Http\Resources\PostResource;
use App\Models\Post;

class GroupController extends Controller
{
    public function index(
        Request $request
    ) {
        $user =
            $request->user();

        $search =
            trim(
                $request
                    ->string('search')
                    ->toString()
            );

        if (
            mb_strlen($search) > 80
        ) {
            $search =
                mb_substr(
                    $search,
                    0,
                    80
                );
        }

        $joinedGroups =
            Group::query()
                ->whereHas(
                    'groupUsers',
                    function ($query) use (
                        $user
                    ) {
                        $query
                            ->where(
                                'user_id',
                                $user->id
                            )
                            ->where(
                                'status',
                                GroupUserStatus::APPROVED->value
                            );
                    }
                )
                ->with(
                    'currentUserGroup'
                )
                ->withCount(
                    'approvedUsers'
                )
                ->orderBy('name')
                ->get();

        $discoverGroups =
            Group::query()
                ->whereDoesntHave(
                    'groupUsers',
                    function ($query) use (
                        $user
                    ) {
                        $query
                            ->where(
                                'user_id',
                                $user->id
                            )
                            ->where(
                                'status',
                                GroupUserStatus::APPROVED->value
                            );
                    }
                )
                ->with(
                    'currentUserGroup'
                )
                ->withCount(
                    'approvedUsers'
                )
                ->when(
                    $search !== '',
                    function ($query) use (
                        $search
                    ) {
                        $like =
                            '%' .
                            $search .
                            '%';

                        $query->where(
                            function ($query) use (
                                $like
                            ) {
                                $query
                                    ->where(
                                        'name',
                                        'like',
                                        $like
                                    )
                                    ->orWhere(
                                        'about',
                                        'like',
                                        $like
                                    );
                            }
                        );
                    }
                )
                ->latest()
                ->paginate(
                    12,
                    ['*'],
                    'groups_page'
                )
                ->withQueryString();

        return Inertia::render(
            'Group/Index',
            [
                'joinedGroups' =>
                    GroupSummaryResource::collection(
                        $joinedGroups
                    )
                        ->resolve(
                            $request
                        ),

                'discoverGroups' =>
                    GroupSummaryResource::collection(
                        $discoverGroups
                    ),

                'joinedCount' =>
                    $joinedGroups->count(),

                'search' =>
                    $search,

                'success' =>
                    session('success'),
            ]
        );
    }

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

    public function update(
        UpdateGroupRequest $request,
        Group $group
    ) {
        $group->update(
            $request->validated()
        );

        return back()->with(
            'success',
            'Group settings updated.'
        );
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

        $isApprovedMember =
    $group->hasApprovedUser(
        $userId
    );

$posts = null;

if ($isApprovedMember) {
    $postsQuery =
        Post::postsForTimeline(
            $userId
        )
            ->where(
                'posts.group_id',
                $group->id
            );

    if ($group->pinned_post_id) {
        $postsQuery
            ->reorder()
            ->orderByRaw(
                'CASE WHEN posts.id = ? THEN 0 ELSE 1 END',
                [$group->pinned_post_id]
            )
            ->orderByDesc('posts.created_at');
    }

    $posts =
        $postsQuery
            ->paginate(10)
            ->withQueryString();
}


        /*
        * Infinite-scroll requests use
        * Accept: application/json.
        */
        if ($request->wantsJson()) {

            if (!$isApprovedMember) {
                abort(
                    403,
                    "You don't have permission to view posts in this group."
                );
            }

            return PostResource::collection(
                $posts
            );
        }

        $users = $group
            ->approvedUsers()
            ->orderBy('users.name')
            ->get();

        $requests = collect();

        if (
            $userId &&
            $group->isAdmin($userId)
        ) {
            $requests = $group
                ->pendingRequestUsers()
                ->orderBy('users.name')
                ->get();
        }

        $photos = collect();

        if ($isApprovedMember) {

            $groupPostIds =
                Post::postsForTimeline(
                    $userId
                )
                    ->where(
                        'posts.group_id',
                        $group->id
                    )
                    ->reorder()
                    ->select(
                        'posts.id'
                    );


            $photos =
                PostAttachment::query()
                    ->where(
                        'mime',
                        'like',
                        'image/%'
                    )
                    ->whereIn(
                        'post_id',
                        $groupPostIds
                    )
                    ->latest()
                    ->get();
        }

        return Inertia::render(
            'Group/View',
            [
                'posts' =>
                    $posts
                        ? PostResource::collection(
                            $posts
                        )
                        : null,

                'group' =>
                    (new GroupResource($group))
                        ->resolve($request),

                'photos' =>
                    PostAttachmentResource::collection(
                        $photos
                    )
                        ->resolve($request),

                'users' =>
                    GroupMemberResource::collection($users)
                        ->resolve($request),

                'requests' =>
                    UserResource::collection($requests)
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