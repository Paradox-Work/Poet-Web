<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class MessageController extends Controller
{
    public function index(
        Request $request,
        ?Conversation $conversation = null
    ) {
        $user = $request->user();

        if ($conversation) {
            $this->authorizeConversation(
                $user->id,
                $conversation
            );
            $this->markRead(
                $conversation,
                $user->id
            );
        }

        $conversations = Conversation::query()
            ->whereHas(
                'users',
                fn ($query) =>
                    $query->whereKey($user->id)
            )
            ->with([
                'users' => fn ($query) =>
                    $query->where(
                        'users.id',
                        '!=',
                        $user->id
                    ),
                'messages' => fn ($query) =>
                    $query->latest()->limit(1),
            ])
            ->latest('updated_at')
            ->get()
            ->map(function (Conversation $item) use ($user, $request) {
                $other = $item->users->first();
                $last = $item->messages->first();

                $lastRead = DB::table('conversation_user')
                    ->where('conversation_id', $item->id)
                    ->where('user_id', $user->id)
                    ->value('last_read_at');

                return [
                    'id' => $item->id,
                    'other_user' => $other
                        ? (new UserResource($other))
                            ->resolve($request)
                        : null,
                    'last_message' => $last
                        ? $this->messagePayload($last)
                        : null,
                    'updated_at' =>
                        $item->updated_at?->toISOString(),
                    'unread_count' =>
                        $item->messages()
                            ->where('user_id', '!=', $user->id)
                            ->when(
                                $lastRead,
                                fn ($query) =>
                                    $query->where(
                                        'created_at',
                                        '>',
                                        $lastRead
                                    )
                            )
                            ->count(),
                ];
            });

        $messages = [];

        if ($conversation) {
            $messages = $conversation
                ->messages()
                ->with('user')
                ->latest('id')
                ->limit(100)
                ->get()
                ->reverse()
                ->values()
                ->map(
                    fn (Message $message) =>
                        $this->messagePayload($message)
                );
        }

        $search = trim(
            $request->string('q')->toString()
        );

        if (mb_strlen($search) > 80) {
            $search = mb_substr($search, 0, 80);
        }

        $userMatches = collect();

        if ($search !== '') {
            $like = '%' . $search . '%';

            $userMatches = User::query()
                ->where('id', '!=', $user->id)
                ->whereNull('suspended_at')
                ->where(function ($query) use ($like) {
                    $query
                        ->where('name', 'like', $like)
                        ->orWhere('username', 'like', $like);
                })
                ->limit(8)
                ->get();
        }

        return Inertia::render('Messages/Index', [
            'conversations' => $conversations,
            'activeConversation' => $conversation
                ? [
                    'id' => $conversation->id,
                    'users' =>
                        UserResource::collection(
                            $conversation
                                ->users()
                                ->where(
                                    'users.id',
                                    '!=',
                                    $user->id
                                )
                                ->get()
                        )->resolve($request),
                ]
                : null,
            'messages' => $messages,
            'search' => $search,
            'userMatches' => UserResource::collection(
                $userMatches
            )->resolve($request),
        ]);
    }

    public function start(Request $request, User $user)
    {
        $actor = $request->user();

        if (
            $actor->id === $user->id ||
            $user->suspended_at
        ) {
            abort(422);
        }

        $key = Conversation::directKey(
            $actor->id,
            $user->id
        );

        $conversation = DB::transaction(
            function () use ($actor, $user, $key) {
                $conversation =
                    Conversation::query()
                        ->firstOrCreate(
                            ['direct_key' => $key],
                            ['created_by' => $actor->id]
                        );

                $conversation
                    ->users()
                    ->syncWithoutDetaching([
                        $actor->id,
                        $user->id,
                    ]);

                return $conversation;
            }
        );

        return to_route(
            'messages.show',
            $conversation
        );
    }

    public function send(
        Request $request,
        Conversation $conversation
    ) {
        $user = $request->user();

        $this->authorizeConversation(
            $user->id,
            $conversation
        );

        $data = $request->validate([
            'body' => [
                'required',
                'string',
                'max:5000',
            ],
        ]);

        $message =
            $conversation
                ->messages()
                ->create([
                    'user_id' => $user->id,
                    'body' => trim($data['body']),
                ]);

        $conversation->touch();

        $this->markRead(
            $conversation,
            $user->id
        );

        $message->load('user');

        return response()->json(
            $this->messagePayload($message),
            201
        );
    }

    public function poll(
        Request $request,
        Conversation $conversation
    ) {
        $user = $request->user();

        $this->authorizeConversation(
            $user->id,
            $conversation
        );

        $after = max(
            0,
            $request->integer('after')
        );

        $messages =
            $conversation
                ->messages()
                ->with('user')
                ->where('id', '>', $after)
                ->orderBy('id')
                ->limit(100)
                ->get()
                ->map(
                    fn (Message $message) =>
                        $this->messagePayload($message)
                );

        $this->markRead(
            $conversation,
            $user->id
        );

        return response()->json([
            'messages' => $messages,
        ]);
    }

    private function authorizeConversation(
        int $userId,
        Conversation $conversation
    ): void {
        if (!$conversation->hasUser($userId)) {
            abort(404);
        }
    }

    private function markRead(
        Conversation $conversation,
        int $userId
    ): void {
        DB::table('conversation_user')
            ->where(
                'conversation_id',
                $conversation->id
            )
            ->where('user_id', $userId)
            ->update([
                'last_read_at' => now(),
                'updated_at' => now(),
            ]);
    }

    private function messagePayload(
        Message $message
    ): array {
        return [
            'id' => $message->id,
            'conversation_id' =>
                $message->conversation_id,
            'body' => $message->body,
            'created_at' =>
                $message->created_at?->toISOString(),
            'edited_at' =>
                $message->edited_at?->toISOString(),
            'user' => [
                'id' => $message->user?->id,
                'name' =>
                    $message->user?->name
                    ?? 'Deleted user',
                'username' =>
                    $message->user?->username,
                'avatar_url' =>
                    $message->user?->avatar_path
                        ? asset(
                            'storage/'
                            . $message->user->avatar_path
                        )
                        : '/img/default_avatar.svg',
            ],
        ];
    }
}
