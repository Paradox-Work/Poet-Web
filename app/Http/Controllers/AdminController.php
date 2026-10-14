<?php

namespace App\Http\Controllers;

use App\Models\Bookmark;
use App\Models\Comment;
use App\Models\Conversation;
use App\Models\Group;
use App\Models\Message;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeAdmin($request);

        $reports =
            Report::query()
                ->with('reporter')
                ->latest()
                ->paginate(20)
                ->through(
                    fn (Report $report) => [
                        'id' => $report->id,
                        'target_type' =>
                            $report->target_type,
                        'target_id' =>
                            $report->target_id,
                        'reason' => $report->reason,
                        'details' => $report->details,
                        'status' => $report->status,
                        'action' => $report->action,
                        'action_note' =>
                            $report->action_note,
                        'created_at' =>
                            $report->created_at
                                ?->toISOString(),
                        'reporter' => [
                            'id' =>
                                $report->reporter?->id,
                            'name' =>
                                $report->reporter?->name
                                ?? 'Deleted user',
                            'username' =>
                                $report->reporter
                                    ?->username,
                        ],
                        'target' =>
                            $this->targetSummary($report),
                    ]
                );

        return Inertia::render(
            'Admin/Dashboard',
            [
                'stats' => [
                    'users' =>
                        User::query()->count(),
                    'suspended_users' =>
                        User::query()
                            ->whereNotNull(
                                'suspended_at'
                            )
                            ->count(),
                    'publications' =>
                        Post::query()
                            ->where(
                                'status',
                                'published'
                            )
                            ->count(),
                    'groups' =>
                        Group::query()->count(),
                    'comments' =>
                        Comment::query()->count(),
                    'conversations' =>
                        Conversation::query()->count(),
                    'messages' =>
                        Message::query()->count(),
                    'bookmarks' =>
                        Bookmark::query()->count(),
                    'pending_reports' =>
                        Report::query()
                            ->where(
                                'status',
                                'pending'
                            )
                            ->count(),
                    'new_users_7d' =>
                        User::query()
                            ->where(
                                'created_at',
                                '>=',
                                now()->subDays(7)
                            )
                            ->count(),
                    'publications_7d' =>
                        Post::query()
                            ->where(
                                'status',
                                'published'
                            )
                            ->where(
                                'created_at',
                                '>=',
                                now()->subDays(7)
                            )
                            ->count(),
                ],
                'reports' => $reports,
                'suspendedUsers' =>
                    User::query()
                        ->whereNotNull('suspended_at')
                        ->orderByDesc('suspended_at')
                        ->limit(20)
                        ->get()
                        ->map(fn (User $user) => [
                            'id' => $user->id,
                            'name' => $user->name,
                            'username' => $user->username,
                            'suspended_at' =>
                                $user->suspended_at?->toISOString(),
                        ]),
            ]
        );
    }

    public function resolve(
        Request $request,
        Report $report
    ) {
        $admin =
            $this->authorizeAdmin($request);

        $data = $request->validate([
            'action' => [
                'required',
                Rule::in([
                    'dismiss',
                    'remove_content',
                    'suspend_user',
                    'note_only',
                ]),
            ],
            'note' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        DB::transaction(
            function () use (
                $report,
                $data,
                $admin
            ) {
                if (
                    $data['action'] ===
                    'remove_content'
                ) {
                    $this->removeReportedContent(
                        $report,
                        $admin->id
                    );
                }

                if (
                    $data['action'] ===
                    'suspend_user'
                ) {
                    $this->suspendReportedUser(
                        $report
                    );
                }

                $report->update([
                    'status' => 'resolved',
                    'reviewed_by' =>
                        $admin->id,
                    'action' =>
                        $data['action'],
                    'action_note' =>
                        $data['note'] ?? null,
                    'reviewed_at' => now(),
                ]);
            }
        );

        return back()->with(
            'success',
            'Report resolved.'
        );
    }

    public function unsuspend(
        Request $request,
        User $user
    ) {
        $this->authorizeAdmin($request);

        $user->forceFill([
            'suspended_at' => null,
        ])->save();

        return back()->with(
            'success',
            'User suspension removed.'
        );
    }

    private function authorizeAdmin(
        Request $request
    ): User {
        $user = $request->user();

        if (
            !$user ||
            !$user->is_site_admin
        ) {
            abort(403);
        }

        return $user;
    }

    private function removeReportedContent(
        Report $report,
        int $adminId
    ): void {
        if (
            $report->target_type ===
            'post'
        ) {
            $post =
                Post::query()
                    ->find(
                        $report->target_id
                    );

            if ($post) {
                $post->deleted_by =
                    $adminId;
                $post->save();
                $post->delete();
            }

            return;
        }

        if (
            $report->target_type ===
            'comment'
        ) {
            Comment::query()
                ->whereKey(
                    $report->target_id
                )
                ->delete();
        }
    }

    private function suspendReportedUser(
        Report $report
    ): void {
        $userId = null;

        if (
            $report->target_type ===
            'user'
        ) {
            $userId = $report->target_id;
        } elseif (
            $report->target_type ===
            'post'
        ) {
            $userId =
                Post::withTrashed()
                    ->whereKey(
                        $report->target_id
                    )
                    ->value('user_id');
        } elseif (
            $report->target_type ===
            'comment'
        ) {
            $userId =
                Comment::query()
                    ->whereKey(
                        $report->target_id
                    )
                    ->value('user_id');
        }

        if ($userId) {
            User::query()
                ->whereKey($userId)
                ->where(
                    'is_site_admin',
                    false
                )
                ->update([
                    'suspended_at' => now(),
                ]);
        }
    }

    private function targetSummary(
        Report $report
    ): array {
        if (
            $report->target_type ===
            'post'
        ) {
            $post =
                Post::withTrashed()
                    ->find(
                        $report->target_id
                    );

            return [
                'exists' => (bool) $post,
                'label' => $post
                    ? (
                        $post->title
                        ?: 'Publication #' . $post->id
                    )
                    : 'Removed publication',
                'author_id' =>
                    $post?->user_id,
            ];
        }

        if (
            $report->target_type ===
            'comment'
        ) {
            $comment =
                Comment::query()
                    ->find(
                        $report->target_id
                    );

            return [
                'exists' => (bool) $comment,
                'label' => $comment
                    ? mb_strimwidth(
                        $comment->comment,
                        0,
                        100,
                        '…'
                    )
                    : 'Removed comment',
                'author_id' =>
                    $comment?->user_id,
            ];
        }

        $user =
            User::withTrashed()
                ->find(
                    $report->target_id
                );

        return [
            'exists' => (bool) $user,
            'label' => $user
                ? '@' . $user->username
                : 'Removed user',
            'author_id' => $user?->id,
            'suspended_at' =>
                $user?->suspended_at
                    ?->toISOString(),
        ];
    }
}
