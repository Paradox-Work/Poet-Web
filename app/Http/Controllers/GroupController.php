<?php

namespace App\Http\Controllers;

use App\Enums\GroupUserRole;
use App\Enums\GroupUserStatus;
use App\Http\Requests\StoreGroupRequest;
use App\Http\Requests\UpdateGroupRequest;
use App\Http\Resources\GroupMemberResource;
use App\Http\Resources\GroupResource;
use App\Http\Resources\GroupSummaryResource;
use App\Http\Resources\PostAttachmentResource;
use App\Http\Resources\PostResource;
use App\Http\Resources\UserResource;
use App\Models\Group;
use App\Models\GroupUser;
use App\Models\Post;
use App\Models\PostAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

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
}
