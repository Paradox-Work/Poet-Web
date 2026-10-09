<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Resources\UserResource;
use App\Http\Resources\ProfilePublicationResource;
use App\Http\Resources\PostAttachmentResource;
use App\Models\PostAttachment;
use App\Models\Post;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\User;
use App\Models\Follower;

class ProfileController extends Controller
{

    public function index(
        Request $request,
        User $user
    )
    {
        $currentUserId = Auth::id();

        $isCurrentUserFollower =
            $currentUserId
                ? Follower::query()
                    ->where(
                        'user_id',
                        $user->id
                    )
                    ->where(
                        'follower_id',
                        $currentUserId
                    )
                    ->exists()
                : false;

        $followerCount =
            Follower::query()
                ->where(
                    'user_id',
                    $user->id
                )
                ->count();

        $followingCount =
            Follower::query()
                ->where(
                    'follower_id',
                    $user->id
                )
                ->count();

        $profileTab =
            $request
                ->string('tab')
                ->toString();

        $allowedTabs = [
            'posts',
            'followers',
            'following',
            'photos',
            'my_profile',
        ];

        if (
            !in_array(
                $profileTab,
                $allowedTabs,
                true
            )
        ) {
            $profileTab = 'posts';
        }

        if (
            $profileTab === 'my_profile' &&
            $currentUserId !== $user->id
        ) {
            $profileTab = 'posts';
        }

        $peopleSearch =
            trim(
                $request
                    ->string('people_search')
                    ->toString()
            );

        if (
            mb_strlen($peopleSearch) > 80
        ) {
            $peopleSearch =
                mb_substr(
                    $peopleSearch,
                    0,
                    80
                );
        }

        $posts = null;

        if ($currentUserId) {

            $postsQuery =
                Post::query()
                    ->where(
                        'posts.status',
                        'published'
                    )
                    ->where(
                        'posts.user_id',
                        $user->id
                    )
                    ->whereNull(
                        'posts.group_id'
                    )
                    ->with('attachments')
                    ->withCount([
                        'reactions',
                        'comments',
                    ])
                    ->latest();

            if ($user->pinned_post_id) {
                $postsQuery
                    ->reorder()
                    ->orderByRaw(
                        'CASE WHEN posts.id = ? THEN 0 ELSE 1 END',
                        [$user->pinned_post_id]
                    )
                    ->orderByDesc(
                        'posts.created_at'
                    );
            }

            $posts =
                ProfilePublicationResource::collection(
                    $postsQuery
                        ->paginate(
                            12,
                            ['posts.*'],
                            'posts_page'
                        )
                        ->withQueryString()
                );
        }


        $followersQuery =
            $user
                ->followers()
                ->when(
                    $peopleSearch !== '',
                    function ($query) use (
                        $peopleSearch
                    ) {
                        $like =
                            '%' .
                            $peopleSearch .
                            '%';

                        $query->where(
                            function ($query) use (
                                $like
                            ) {
                                $query
                                    ->where(
                                        'users.name',
                                        'like',
                                        $like
                                    )
                                    ->orWhere(
                                        'users.username',
                                        'like',
                                        $like
                                    );
                            }
                        );
                    }
                )
                ->orderBy('users.name')
                ->orderBy('users.id');

        $followers =
            $followersQuery
                ->paginate(
                    12,
                    ['users.*'],
                    'followers_page'
                )
                ->withQueryString();

        $followingsQuery =
            $user
                ->followings()
                ->when(
                    $peopleSearch !== '',
                    function ($query) use (
                        $peopleSearch
                    ) {
                        $like =
                            '%' .
                            $peopleSearch .
                            '%';

                        $query->where(
                            function ($query) use (
                                $like
                            ) {
                                $query
                                    ->where(
                                        'users.name',
                                        'like',
                                        $like
                                    )
                                    ->orWhere(
                                        'users.username',
                                        'like',
                                        $like
                                    );
                            }
                        );
                    }
                )
                ->orderBy('users.name')
                ->orderBy('users.id');

        $followings =
            $followingsQuery
                ->paginate(
                    12,
                    ['users.*'],
                    'following_page'
                )
                ->withQueryString();
        
        $photos = null;

        if ($currentUserId) {

            $visiblePostIds =
                Post::postsForTimeline(
                    $currentUserId
                )
                    ->where(
                        'posts.user_id',
                        $user->id
                    )
                    ->whereNull(
                        'posts.group_id'
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
                        $visiblePostIds
                    )
                    ->latest()
                    ->get();
        }

        return Inertia::render(
            'Profile/View',
            [
                'mustVerifyEmail' =>
                    $user instanceof MustVerifyEmail,

                'status' =>
                    session('status'),

                'success' =>
                    session('success'),

                'isCurrentUserFollower' =>
                    $isCurrentUserFollower,

                'followerCount' =>
                    $followerCount,

                'followingCount' =>
                    $followingCount,

                'profileTab' =>
                    $profileTab,

                'peopleSearch' =>
                    $peopleSearch,

                'posts' =>
                    $posts,

                'followers' =>
                    UserResource::collection(
                        $followers
                    ),

                'followings' =>
                    UserResource::collection(
                        $followings
                    ),

                'photos' =>
                    $photos
                        ? PostAttachmentResource::collection(
                            $photos
                        )
                        : null,

                'user' =>
                    new UserResource($user),
            ]
        );
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
       
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return to_route('profile', $request->user())
            ->with('success', 'Your profile details were updated.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function updateImage(Request $request)
    {
        $data = $request->validate([
            'cover' => ['nullable', 'image', 'max:4096'],
            'avatar' => ['nullable', 'image', 'max:2048']
        ]);

        $user = $request->user();
        $cover  = $data['cover']  ?? null;
        $avatar = $data['avatar'] ?? null;

        $success = '';

        if ($cover) {
            if ($user->cover_path) {
                Storage::disk('public')->delete($user->cover_path);
            }
            $path = $cover->store('user-'.$user->id, 'public');
            $user->update(['cover_path' => $path]);
            $success = 'Your cover image was updated';
        }

        if ($avatar) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $path = $avatar->store('user-'.$user->id, 'public');
            $user->update(['avatar_path' => $path]);
            $success = 'Your avatar image was updated';
        }

         return back()->with('success', $success);
    }
}
