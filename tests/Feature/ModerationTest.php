<?php

use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('users can report visible publications only once while pending', function () {
    $author = User::factory()->create();
    $reporter = User::factory()->create();
    $post = poetTestPost($author);

    $payload = [
        'target_type' => 'post',
        'target_id' => $post->id,
        'reason' => 'spam',
        'details' => 'Repeated promotion',
    ];

    $this
        ->actingAs($reporter)
        ->post(route('reports.store'), $payload)
        ->assertRedirect(route('dashboard'));

    $this
        ->actingAs($reporter)
        ->post(route('reports.store'), $payload)
        ->assertRedirect(route('dashboard'));

    expect(Report::query()->count())->toBe(1);
});

test('private group content cannot be reported by outsiders who cannot see it', function () {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();
    $group = poetTestGroup($owner, ['visibility' => 'private']);
    $post = poetTestPost($owner, ['group_id' => $group->id]);

    $this
        ->actingAs($outsider)
        ->post(route('reports.store'), [
            'target_type' => 'post',
            'target_id' => $post->id,
            'reason' => 'other',
        ])
        ->assertNotFound();
});

test('site admins can remove reported publications', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['is_site_admin' => true])->save();

    $author = User::factory()->create();
    $reporter = User::factory()->create();
    $post = poetTestPost($author);

    $report = Report::query()->create([
        'reporter_id' => $reporter->id,
        'target_type' => 'post',
        'target_id' => $post->id,
        'reason' => 'spam',
    ]);

    $this
        ->actingAs($admin)
        ->post(
            route('admin.reports.resolve', $report),
            ['action' => 'remove_content']
        )
        ->assertSessionHasNoErrors();

    expect(
        \App\Models\Post::query()
            ->find($post->id)
    )->toBeNull();

    expect(
        \App\Models\Post::withTrashed()
            ->findOrFail($post->id)
            ->trashed()
    )->toBeTrue();

    expect($report->fresh()->status)->toBe('resolved');
});

test('suspended users cannot sign in', function () {
    $user = User::factory()->create([
        'password' => 'password',
    ]);

    $user->forceFill([
        'suspended_at' => now(),
    ])->save();

    $this
        ->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('ordinary users cannot open the site admin dashboard', function () {
    $user = User::factory()->create();

    $this
        ->actingAs($user)
        ->get(route('admin.index'))
        ->assertForbidden();
});
