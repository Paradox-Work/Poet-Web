<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function create(
        Request $request,
        string $type,
        int $id
    ) {
        [, $label] = $this->resolveTarget(
            $request,
            $type,
            $id
        );

        return Inertia::render(
            'Report/Create',
            [
                'target' => [
                    'type' => $type,
                    'id' => $id,
                    'label' => $label,
                ],
            ]
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'target_type' => [
                'required',
                Rule::in([
                    'post',
                    'comment',
                    'user',
                ]),
            ],
            'target_id' => [
                'required',
                'integer',
            ],
            'reason' => [
                'required',
                Rule::in([
                    'spam',
                    'harassment',
                    'copyright',
                    'inappropriate',
                    'impersonation',
                    'other',
                ]),
            ],
            'details' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $this->resolveTarget(
            $request,
            $data['target_type'],
            (int) $data['target_id']
        );

        Report::query()->firstOrCreate(
            [
                'reporter_id' =>
                    $request->user()->id,
                'target_type' =>
                    $data['target_type'],
                'target_id' =>
                    $data['target_id'],
                'status' => 'pending',
            ],
            [
                'reason' =>
                    $data['reason'],
                'details' =>
                    $data['details'] ?? null,
            ]
        );

        return to_route('dashboard')
            ->with(
                'success',
                'Report submitted for review.'
            );
    }

    private function resolveTarget(
        Request $request,
        string $type,
        int $id
    ): array {
        if (
            !in_array(
                $type,
                ['post', 'comment', 'user'],
                true
            )
        ) {
            abort(404);
        }

        if ($type === 'post') {
            $post =
                Post::query()->findOrFail($id);

            if (
                !$post->isVisibleTo(
                    $request->user()->id
                )
            ) {
                abort(404);
            }

            return [
                $post,
                $post->title
                    ?: 'Publication #' . $post->id,
            ];
        }

        if ($type === 'comment') {
            $comment =
                Comment::query()
                    ->with('post')
                    ->findOrFail($id);

            if (
                !$comment->post->isVisibleTo(
                    $request->user()->id
                )
            ) {
                abort(404);
            }

            return [
                $comment,
                'Comment #' . $comment->id,
            ];
        }

        $user =
            User::query()->findOrFail($id);

        if (
            $user->id ===
            $request->user()->id
        ) {
            abort(422);
        }

        return [
            $user,
            '@' . $user->username,
        ];
    }
}
