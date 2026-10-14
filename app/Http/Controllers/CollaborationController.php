<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostCollaborator;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CollaborationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $pending =
            PostCollaborator::query()
                ->with([
                    'post.user',
                    'inviter',
                ])
                ->where(
                    'user_id',
                    $user->id
                )
                ->where(
                    'status',
                    'pending'
                )
                ->latest()
                ->get()
                ->map(
                    fn (
                        PostCollaborator $item
                    ) =>
                        $this
                            ->collaborationPayload(
                                $item
                            )
                );

        $shared =
            PostCollaborator::query()
                ->with('post.user')
                ->where(
                    'user_id',
                    $user->id
                )
                ->where(
                    'status',
                    'accepted'
                )
                ->latest()
                ->get()
                ->map(
                    fn (
                        PostCollaborator $item
                    ) =>
                        $this
                            ->collaborationPayload(
                                $item
                            )
                );

        return Inertia::render(
            'Collaborations/Index',
            [
                'pending' => $pending,
                'shared' => $shared,
            ]
        );
    }

    public function manage(
        Request $request,
        Post $post
    ) {
        $this->authorizeView(
            $request->user(),
            $post
        );

        return Inertia::render(
            'Collaborations/Manage',
            [
                'post' => [
                    'id' => $post->id,
                    'title' =>
                        $post->title
                        ?: 'Untitled poem',
                    'status' => $post->status,
                    'owner_id' => $post->user_id,
                ],
                'canInvite' =>
                    $post->user_id ===
                    $request->user()->id,
                'collaborators' =>
                    PostCollaborator::query()
                        ->with('user')
                        ->where(
                            'post_id',
                            $post->id
                        )
                        ->latest()
                        ->get()
                        ->map(
                            fn (
                                PostCollaborator $item
                            ) => [
                                'id' => $item->id,
                                'status' =>
                                    $item->status,
                                'role' =>
                                    $item->role,
                                'user' => [
                                    'id' =>
                                        $item->user->id,
                                    'name' =>
                                        $item->user->name,
                                    'username' =>
                                        $item->user
                                            ->username,
                                ],
                            ]
                        ),
            ]
        );
    }

    public function invite(
        Request $request,
        Post $post
    ) {
        if (
            $post->user_id !==
                $request->user()->id ||
            $post->type !== 'poem' ||
            $post->status !== 'published'
        ) {
            abort(403);
        }

        $data = $request->validate([
            'username' => [
                'required',
                'string',
                'exists:users,username',
            ],
        ]);

        $user =
            User::query()
                ->where(
                    'username',
                    $data['username']
                )
                ->firstOrFail();

        if (
            $user->id ===
            $request->user()->id
        ) {
            return back()->withErrors([
                'username' =>
                    'You are already the poem owner.',
            ]);
        }

        if (
            $post->group_id &&
            !$post->group?->hasApprovedUser($user->id)
        ) {
            return back()->withErrors([
                'username' =>
                    'Group poems can only be shared with approved group members.',
            ]);
        }

        PostCollaborator::query()
            ->updateOrCreate(
                [
                    'post_id' => $post->id,
                    'user_id' => $user->id,
                ],
                [
                    'invited_by' =>
                        $request->user()->id,
                    'status' => 'pending',
                    'role' => 'editor',
                ]
            );

        return back()->with(
            'success',
            'Collaboration invitation sent.'
        );
    }

    public function accept(
        Request $request,
        PostCollaborator $collaborator
    ) {
        if (
            $collaborator->user_id !==
            $request->user()->id
        ) {
            abort(403);
        }

        $collaborator->update([
            'status' => 'accepted',
        ]);

        return to_route(
            'collaborations.manage',
            $collaborator->post_id
        )->with(
            'success',
            'Collaboration accepted.'
        );
    }

    public function decline(
        Request $request,
        PostCollaborator $collaborator
    ) {
        if (
            $collaborator->user_id !==
            $request->user()->id
        ) {
            abort(403);
        }

        $collaborator->delete();

        return back()->with(
            'success',
            'Collaboration declined.'
        );
    }

    public function remove(
        Request $request,
        PostCollaborator $collaborator
    ) {
        $post = $collaborator->post;

        if (
            $post->user_id !==
                $request->user()->id &&
            $collaborator->user_id !==
                $request->user()->id
        ) {
            abort(403);
        }

        $collaborator->delete();

        return back()->with(
            'success',
            'Collaborator removed.'
        );
    }

    private function authorizeView(
        User $user,
        Post $post
    ): void {
        if (
            $post->type !== 'poem'
            || $post->status !== 'published'
        ) {
            abort(404);
        }

        if (
            $post->user_id ===
            $user->id
        ) {
            return;
        }

        if (
            PostCollaborator::query()
                ->where(
                    'post_id',
                    $post->id
                )
                ->where(
                    'user_id',
                    $user->id
                )
                ->where(
                    'status',
                    'accepted'
                )
                ->exists()
        ) {
            if (
                !$post->group_id
                || $post->group?->hasApprovedUser($user->id)
            ) {
                return;
            }
        }

        abort(403);
    }

    private function collaborationPayload(
        PostCollaborator $item
    ): array {
        return [
            'id' => $item->id,
            'status' => $item->status,
            'role' => $item->role,
            'post' => [
                'id' => $item->post->id,
                'title' =>
                    $item->post->title
                    ?: 'Untitled poem',
                'status' =>
                    $item->post->status,
                'owner' => [
                    'id' =>
                        $item->post->user?->id,
                    'name' =>
                        $item->post->user?->name,
                    'username' =>
                        $item->post->user
                            ?->username,
                ],
            ],
        ];
    }
}
