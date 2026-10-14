<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;

class DraftCollaboratorController extends Controller
{
    public function store(Request $request, Post $post)
    {
        $this->ensureOwner($request, $post);

        $data = $request->validate([
            'username' => ['required', 'string', 'exists:users,username'],
        ]);

        $user = User::query()
            ->where('username', $data['username'])
            ->firstOrFail();

        abort_if(
            $user->id === $request->user()->id,
            422,
            'You are already the owner of this draft.'
        );

        if (
            $post->group_id &&
            !$post->group?->hasApprovedUser(
                $user->id
            )
        ) {
            abort(
                422,
                'Collaborators on a group draft must be approved members of that group.'
            );
        }

        $post->collaborators()->syncWithoutDetaching([
            $user->id => [
                'added_by' => $request->user()->id,
            ],
        ]);

        return response()->json([
            'collaborators' => $post->collaborators()
                ->get()
                ->map(fn (User $user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                ]),
        ]);
    }

    public function destroy(
        Request $request,
        Post $post,
        User $user
    ) {
        $this->ensureOwner($request, $post);

        $post->collaborators()->detach($user->id);

        return response()->noContent();
    }

    private function ensureOwner(Request $request, Post $post): void
    {
        abort_unless(
            $post->status === 'draft'
            && $post->user_id === $request->user()->id,
            403
        );
    }
}
