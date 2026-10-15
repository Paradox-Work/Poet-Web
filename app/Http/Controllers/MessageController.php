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
    public function index(Request $request, ?Conversation $conversation = null)
    {
        $user = $request->user();

        if ($conversation) {
            $this->ensureParticipant($conversation, $user->id);
            $this->markRead($conversation, $user->id);
        }

        return Inertia::render('Messages/Index', [
            'conversations' => $this->conversationSummaries($user->id),
            'activeConversation' => $conversation
                ? $this->conversationPayload($conversation, $user->id)
                : null,
        ]);
    }

    public function start(Request $request, User $user)
    {
        $actor = $request->user();

        abort_if($actor->id === $user->id, 422, 'You cannot message yourself.');

        // Serialize attempts for the same pair of users before checking.
        // The sorted lock order also avoids lock-order deadlocks on MySQL.
        $conversation = DB::transaction(function () use ($actor, $user) {
            User::query()
                ->whereIn('id', [$actor->id, $user->id])
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $existing = Conversation::query()
                ->whereHas('users', fn ($q) => $q->where('users.id', $actor->id))
                ->whereHas('users', fn ($q) => $q->where('users.id', $user->id))
                ->withCount('users')
                ->get()
                ->first(fn ($item) => $item->users_count === 2);

            if ($existing) {
                return $existing;
            }

            $created = Conversation::query()->create();
            $created->users()->attach([
                $actor->id => ['last_read_at' => now()],
                $user->id => ['last_read_at' => null],
            ]);

            return $created;
        });

        return to_route('messages.show', $conversation);
    }

    public function store(Request $request, Conversation $conversation)
    {
        $this->ensureParticipant($conversation, $request->user()->id);

        $data = $request->validate([
            'body' => [
                'required',
                'string',
                'max:5000',
                'regex:/\S/u',
            ],
        ]);

        $message = Message::query()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $request->user()->id,
            'body' => trim($data['body']),
        ]);

        $conversation->touch();
        $this->markRead($conversation, $request->user()->id);

        return response()->json([
            'message' => $this->messagePayload($message->load('user')),
        ], 201);
    }

    public function messages(Request $request, Conversation $conversation)
    {
        $this->ensureParticipant($conversation, $request->user()->id);
        $this->markRead($conversation, $request->user()->id);

        $messages = $conversation
            ->messages()
            ->with('user')
            ->latest('id')
            ->limit(100)
            ->get()
            ->reverse()
            ->values()
            ->map(fn (Message $message) => $this->messagePayload($message));

        return response()->json([
            'messages' => $messages,
        ]);
    }

    private function ensureParticipant(Conversation $conversation, int $userId): void
    {
        abort_unless(
            $conversation->users()->where('users.id', $userId)->exists(),
            404
        );
    }

    private function markRead(Conversation $conversation, int $userId): void
    {
        $conversation->users()->updateExistingPivot(
            $userId,
            ['last_read_at' => now()]
        );
    }

    private function conversationSummaries(int $userId): array
    {
        return Conversation::query()
            ->whereHas('users', fn ($q) => $q->where('users.id', $userId))
            ->with([
                'users',
                'messages' => fn ($q) => $q->latest('id')->limit(1),
            ])
            ->latest('updated_at')
            ->get()
            ->map(function (Conversation $conversation) use ($userId) {
                $other = $conversation->users->firstWhere('id', '!=', $userId);
                $pivot = $conversation->users->firstWhere('id', $userId)?->pivot;
                $last = $conversation->messages->first();

                $unread = $conversation->messages()
                    ->where('user_id', '!=', $userId)
                    ->when(
                        $pivot?->last_read_at,
                        fn ($query, $lastRead) =>
                            $query->where('created_at', '>', $lastRead)
                    )
                    ->count();

                return [
                    'id' => $conversation->id,
                    'other_user' => $other
                        ? (new UserResource($other))->resolve(request())
                        : null,
                    'last_message' => $last?->body,
                    'updated_at' => $conversation->updated_at?->toISOString(),
                    'unread_count' => $unread,
                ];
            })
            ->values()
            ->all();
    }

    private function conversationPayload(Conversation $conversation, int $userId): array
    {
        $conversation->load('users');

        $other = $conversation->users->firstWhere('id', '!=', $userId);

        return [
            'id' => $conversation->id,
            'other_user' => $other
                ? (new UserResource($other))->resolve(request())
                : null,
            'messages' => $conversation
                ->messages()
                ->with('user')
                ->latest('id')
                ->limit(100)
                ->get()
                ->reverse()
                ->values()
                ->map(fn (Message $message) => $this->messagePayload($message)),
        ];
    }

    private function messagePayload(Message $message): array
    {
        return [
            'id' => $message->id,
            'body' => $message->body,
            'created_at' => $message->created_at?->toISOString(),
            'user' => (new UserResource($message->user))->resolve(request()),
        ];
    }
}
