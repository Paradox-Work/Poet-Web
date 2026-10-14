<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Group;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ModerationController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureAdmin($request);

        $reports = Report::query()
            ->with(['reporter', 'reviewer'])
            ->latest()
            ->paginate(20)
            ->through(fn (Report $report) => [
                'id' => $report->id,
                'type' => $report->reportable_type,
                'target_id' => $report->reportable_id,
                'reason' => $report->reason,
                'details' => $report->details,
                'status' => $report->status,
                'reporter' => $report->reporter?->username,
                'reviewer' => $report->reviewer?->username,
                'created_at' => $report->created_at?->toISOString(),
                'reviewed_at' => $report->reviewed_at?->toISOString(),
            ]);

        return Inertia::render('Admin/Moderation', [
            'metrics' => [
                'users' => User::query()->count(),
                'published_posts' => Post::query()
                    ->where('status', 'published')
                    ->count(),
                'groups' => Group::query()->count(),
                'comments' => Comment::query()->count(),
                'pending_reports' => Report::query()
                    ->where('status', 'pending')
                    ->count(),
                'messages' => DB::table('messages')->count(),
                'bookmarks' => DB::table('bookmarks')->count(),
            ],
            'reports' => $reports,
        ]);
    }

    public function resolve(Request $request, Report $report)
    {
        $this->ensureAdmin($request);

        $data = $request->validate([
            'action' => [
                'required',
                Rule::in(['dismiss', 'remove_content']),
            ],
        ]);

        if ($data['action'] === 'remove_content') {
            $this->removeTarget($report);
        }

        $report->update([
            'status' =>
                $data['action'] === 'dismiss'
                    ? 'dismissed'
                    : 'actioned',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Report resolved.');
    }

    private function ensureAdmin(Request $request): void
    {
        abort_unless($request->user()?->is_admin, 403);
    }

    private function removeTarget(Report $report): void
    {
        if ($report->reportable_type === 'post') {
            Post::query()
                ->find($report->reportable_id)
                ?->delete();
        }

        if ($report->reportable_type === 'comment') {
            Comment::query()
                ->find($report->reportable_id)
                ?->delete();
        }
    }
}
