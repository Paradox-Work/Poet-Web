<?php

namespace App\Http\Middleware;

use App\Http\Requests\StorePostRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user()
                    ? new UserResource($request->user())
                    : null,
            ],

            'messages' => function () use ($request) {
                $user = $request->user();

                if (!$user) {
                    return [
                        'unread_count' => 0,
                    ];
                }

                $unreadCount =
                    DB::table('messages')
                        ->join(
                            'conversation_user',
                            function ($join) use ($user) {
                                $join
                                    ->on(
                                        'conversation_user.conversation_id',
                                        '=',
                                        'messages.conversation_id'
                                    )
                                    ->where(
                                        'conversation_user.user_id',
                                        '=',
                                        $user->id
                                    );
                            }
                        )
                        ->where(
                            'messages.user_id',
                            '!=',
                            $user->id
                        )
                        ->where(function ($query) {
                            $query
                                ->whereNull(
                                    'conversation_user.last_read_at'
                                )
                                ->orWhereColumn(
                                    'messages.created_at',
                                    '>',
                                    'conversation_user.last_read_at'
                                );
                        })
                        ->count();

                return [
                    'unread_count' => $unreadCount,
                ];
            },

            'notifications' => function () use ($request) {
                $user = $request->user();

                if (!$user) {
                    return [
                        'unread_count' => 0,
                        'items' => [],
                    ];
                }

                return [
                    'unread_count' =>
                        $user
                            ->unreadNotifications()
                            ->count(),

                    'items' =>
                        $user
                            ->notifications()
                            ->latest()
                            ->limit(8)
                            ->get()
                            ->map(
                                function ($notification) {
                                    return [
                                        'id' =>
                                            $notification->id,

                                        'kind' =>
                                            $notification
                                                ->data['kind']
                                                ?? 'notification',

                                        'title' =>
                                            $notification
                                                ->data['title']
                                                ?? 'Notification',

                                        'message' =>
                                            $notification
                                                ->data['message']
                                                ?? '',

                                        'action_url' =>
                                            $notification
                                                ->data['action_url']
                                                ?? null,

                                        'action_label' =>
                                            $notification
                                                ->data['action_label']
                                                ?? null,

                                        'accept_url' =>
                                            $notification
                                                ->data['accept_url']
                                                ?? null,

                                        'decline_url' =>
                                            $notification
                                                ->data['decline_url']
                                                ?? null,

                                        'expires_at' =>
                                            $notification
                                                ->data['expires_at']
                                                ?? null,

                                        'read_at' =>
                                            $notification
                                                ->read_at
                                                ?->toISOString(),

                                        'created_at' =>
                                            $notification
                                                ->created_at
                                                ?->toISOString(),
                                    ];
                                }
                            )
                            ->values(),
                ];
            },

            'attachmentExtensions' => StorePostRequest::$extensions,
        ];
    }
}
