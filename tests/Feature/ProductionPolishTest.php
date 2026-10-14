<?php

use App\Models\Comment;
use App\Models\Conversation;
use App\Models\Group;
use App\Models\Message;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

test('users can bookmark and unbookmark visible publications', function () {
    $user = User::factory()->create();
    $author = User::factory()->create();
    $post = poetTestPost($author);

    $this
        ->actingAs($user)
        ->postJson(route('post.bookmark', $post))
        ->assertOk()
        ->assertJson(['bookmarked' => true]);

    $this->assertDatabaseHas('bookmarks', [
        'user_id' => $user->id,
        'post_id' => $post->id,
    ]);

    $this
        ->actingAs($user)
        ->get(route('saved.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) =>
                $page
                    ->component('Saved/Index')
                    ->has('posts.data', 1)
        );

    $this
        ->actingAs($user)
        ->postJson(route('post.bookmark', $post))
        ->assertOk()
        ->assertJson(['bookmarked' => false]);

    $this->assertDatabaseMissing('bookmarks', [
        'user_id' => $user->id,
        'post_id' => $post->id,
    ]);
});

test('private group publications cannot be bookmarked by outsiders', function () {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();
    $group = poetTestGroup($owner, ['visibility' => 'private']);
    $post = poetTestPost($owner, ['group_id' => $group->id]);

    $this
        ->actingAs($outsider)
        ->postJson(route('post.bookmark', $post))
        ->assertNotFound();
});

test('users can start direct conversations and exchange messages', function () {
    $first = User::factory()->create();
    $second = User::factory()->create();

    $response = $this
        ->actingAs($first)
        ->post(route('messages.start', $second));

    $conversation = Conversation::query()->firstOrFail();

    $response->assertRedirect(route('messages.show', $conversation));

    expect(
        $conversation->users()->pluck('users.id')->sort()->values()->all()
    )->toBe(
        collect([$first->id, $second->id])->sort()->values()->all()
    );

    $this
        ->actingAs($first)
        ->postJson(
            route('messages.store', $conversation),
            ['body' => 'Hello from Poet-Web']
        )
        ->assertCreated()
        ->assertJsonPath('message.body', 'Hello from Poet-Web');

    $this->assertDatabaseHas('messages', [
        'conversation_id' => $conversation->id,
        'user_id' => $first->id,
        'body' => 'Hello from Poet-Web',
    ]);

    $this
        ->actingAs($second)
        ->getJson(route('messages.items', $conversation))
        ->assertOk()
        ->assertJsonCount(1, 'messages');
});

test('non participants cannot read direct conversations', function () {
    $first = User::factory()->create();
    $second = User::factory()->create();
    $outsider = User::factory()->create();

    $conversation = Conversation::query()->create();
    $conversation->users()->attach([$first->id, $second->id]);

    $this
        ->actingAs($outsider)
        ->get(route('messages.show', $conversation))
        ->assertNotFound();
});

test('users can report visible content and site admins can resolve reports', function () {
    $author = User::factory()->create();
    $reporter = User::factory()->create();
    $admin = User::factory()->create(['is_admin' => true]);
    $post = poetTestPost($author);

    $this
        ->actingAs($reporter)
        ->postJson(
            route('reports.store'),
            [
                'type' => 'post',
                'id' => $post->id,
                'reason' => 'copyright',
                'details' => 'Demo report',
            ]
        )
        ->assertOk()
        ->assertJson(['reported' => true]);

    $report = Report::query()->firstOrFail();

    $this
        ->actingAs($reporter)
        ->get(route('admin.moderation'))
        ->assertForbidden();

    $this
        ->actingAs($admin)
        ->get(route('admin.moderation'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) =>
                $page
                    ->component('Admin/Moderation')
                    ->where('metrics.pending_reports', 1)
        );

    $this
        ->actingAs($admin)
        ->post(
            route('admin.reports.resolve', $report),
            ['action' => 'remove_content']
        )
        ->assertSessionHasNoErrors();

    expect($post->fresh())->toBeNull();
    expect($report->fresh()->status)->toBe('actioned');
});

test('draft owners can add collaborators and collaborators can autosave edits', function () {
    $owner = User::factory()->create();
    $collaborator = User::factory()->create();

    $draft = poetTestPost($owner, [
        'status' => 'draft',
        'published_at' => null,
        'draft_saved_at' => now(),
        'type' => 'poem',
        'poem_form' => 'free_verse',
    ]);

    $this
        ->actingAs($owner)
        ->postJson(
            route('draft.collaborators.store', $draft),
            ['username' => $collaborator->username]
        )
        ->assertOk()
        ->assertJsonCount(1, 'collaborators');

    $this->assertDatabaseHas('post_collaborators', [
        'post_id' => $draft->id,
        'user_id' => $collaborator->id,
        'added_by' => $owner->id,
    ]);

    $this
        ->actingAs($collaborator)
        ->putJson(
            route('draft.update', $draft),
            [
                'type' => 'poem',
                'content_rating' => 'general',
                'poem_form' => 'free_verse',
                'poem_genres' => ['reflection'],
                'title' => 'Shared draft',
                'caption' => null,
                'hashtags' => ['shared'],
                'body' => '<p>Collaborative edit</p>',
                'group_id' => null,
            ]
        )
        ->assertOk();

    expect($draft->fresh()->title)->toBe('Shared draft');
});

test('non collaborators cannot edit another users draft', function () {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();

    $draft = poetTestPost($owner, [
        'status' => 'draft',
        'published_at' => null,
        'draft_saved_at' => now(),
        'type' => 'poem',
    ]);

    $this
        ->actingAs($outsider)
        ->putJson(
            route('draft.update', $draft),
            [
                'type' => 'poem',
                'content_rating' => 'general',
                'poem_form' => 'free_verse',
                'poem_genres' => [],
                'title' => 'Nope',
                'caption' => null,
                'hashtags' => [],
                'body' => '<p>Nope</p>',
                'group_id' => null,
            ]
        )
        ->assertForbidden();
});

test('discovery can sort by reactions and comments without recommendations', function () {
    $author = User::factory()->create();
    $other = User::factory()->create();

    $plain = poetTestPost($author, ['type' => 'poem', 'title' => 'Plain']);
    $popular = poetTestPost($author, ['type' => 'poem', 'title' => 'Popular']);
    $discussed = poetTestPost($author, ['type' => 'poem', 'title' => 'Discussed']);

    $popular->reactions()->create([
        'user_id' => $other->id,
        'type' => 'like',
    ]);

    Comment::query()->create([
        'post_id' => $discussed->id,
        'user_id' => $other->id,
        'comment' => 'Discussion',
    ]);

    $this
        ->get(route('dashboard', ['feed' => 'poems', 'sort' => 'popular']))
        ->assertInertia(
            fn (Assert $page) =>
                $page->where('posts.data.0.id', $popular->id)
        );

    $this
        ->get(route('dashboard', ['feed' => 'poems', 'sort' => 'discussed']))
        ->assertInertia(
            fn (Assert $page) =>
                $page->where('posts.data.0.id', $discussed->id)
        );
});

test('legal pages and sitemap are public and sitemap excludes private group posts', function () {
    foreach (['privacy', 'terms', 'community', 'copyright'] as $routeName) {
        $this
            ->get(route($routeName))
            ->assertOk();
    }

    $owner = User::factory()->create();

    $publicGroup = poetTestGroup($owner, ['visibility' => 'public']);
    $privateGroup = poetTestGroup($owner, ['visibility' => 'private']);

    $publicPost = poetTestPost($owner, ['group_id' => $publicGroup->id]);
    $privatePost = poetTestPost($owner, ['group_id' => $privateGroup->id]);

    $this
        ->get(route('sitemap'))
        ->assertOk()
        ->assertHeader('content-type', 'application/xml; charset=UTF-8')
        ->assertSee(route('post.view', $publicPost), false)
        ->assertDontSee(route('post.view', $privatePost), false);
});
