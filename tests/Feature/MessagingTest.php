<?php

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;

test('starting the same direct conversation is idempotent', function () {
    $first = User::factory()->create();
    $second = User::factory()->create();

    $this
        ->actingAs($first)
        ->post(route('messages.start', $second))
        ->assertRedirect();

    $this
        ->actingAs($first)
        ->post(route('messages.start', $second))
        ->assertRedirect();

    expect(Conversation::query()->count())->toBe(1);

    $conversation = Conversation::query()->firstOrFail();

    expect($conversation->users()->count())->toBe(2);
});

test('conversation participants can send and poll messages', function () {
    $first = User::factory()->create();
    $second = User::factory()->create();

    $conversation = Conversation::query()->create([
        'created_by' => $first->id,
        'direct_key' => Conversation::directKey($first->id, $second->id),
    ]);

    $conversation->users()->attach([$first->id, $second->id]);

    $response = $this
        ->actingAs($first)
        ->postJson(
            route('messages.send', $conversation),
            ['body' => 'Hello there']
        )
        ->assertCreated();

    $messageId = $response->json('id');

    expect(Message::query()->whereKey($messageId)->exists())
        ->toBeTrue();

    $this
        ->actingAs($second)
        ->getJson(
            route('messages.poll', [
                'conversation' => $conversation,
                'after' => 0,
            ])
        )
        ->assertOk()
        ->assertJsonPath('messages.0.body', 'Hello there');
});

test('outsiders cannot access another conversation', function () {
    $first = User::factory()->create();
    $second = User::factory()->create();
    $outsider = User::factory()->create();

    $conversation = Conversation::query()->create([
        'created_by' => $first->id,
        'direct_key' => Conversation::directKey($first->id, $second->id),
    ]);

    $conversation->users()->attach([$first->id, $second->id]);

    $this
        ->actingAs($outsider)
        ->get(route('messages.show', $conversation))
        ->assertNotFound();

    $this
        ->actingAs($outsider)
        ->postJson(
            route('messages.send', $conversation),
            ['body' => 'Nope']
        )
        ->assertNotFound();
});
