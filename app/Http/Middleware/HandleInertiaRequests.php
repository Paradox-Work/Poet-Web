<?php

namespace App\Http\Middleware;

use App\Http\Requests\StorePostRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
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

                                        'decline_url' =>
                                            $notification
                                                ->data['decline_url']
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
