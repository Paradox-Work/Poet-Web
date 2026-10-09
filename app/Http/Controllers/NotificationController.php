<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function markRead(
        Request $request,
        string $notification
    ) {
        $item =
            $request
                ->user()
                ->notifications()
                ->whereKey(
                    $notification
                )
                ->firstOrFail();

        $item->markAsRead();

        return back();
    }

    public function markAllRead(
        Request $request
    ) {
        $request
            ->user()
            ->unreadNotifications()
            ->update([
                'read_at' => now(),
            ]);

        return back();
    }
}
