<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(['post', 'comment', 'user'])],
            'id' => ['required', 'integer', 'min:1'],
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
            'details' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->ensureReportableIsAccessible(
            $request,
            $data['type'],
            (int) $data['id']
        );

        Report::query()->updateOrCreate(
            [
                'reporter_id' => $request->user()->id,
                'reportable_type' => $data['type'],
                'reportable_id' => $data['id'],
            ],
            [
                'reason' => $data['reason'],
                'details' => $data['details'] ?? null,
                'status' => 'pending',
                'reviewed_by' => null,
                'reviewed_at' => null,
            ]
        );

        if ($request->wantsJson()) {
            return response()->json(['reported' => true]);
        }

        return back()->with('success', 'Report submitted.');
    }

    private function ensureReportableIsAccessible(
        Request $request,
        string $type,
        int $id
    ): void {
        if ($type === 'user') {
            User::query()->findOrFail($id);

            abort_if(
                $request->user()->id === $id,
                422,
                'You cannot report your own account.'
            );

            return;
        }

        if ($type === 'post') {
            $post = Post::query()->findOrFail($id);

            abort_unless(
                $post->isVisibleTo($request->user()->id),
                404
            );

            return;
        }

        $comment = Comment::query()
            ->with('post')
            ->findOrFail($id);

        abort_unless(
            $comment->post?->isVisibleTo($request->user()->id),
            404
        );
    }
}
