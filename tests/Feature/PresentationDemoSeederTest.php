<?php

use App\Models\Group;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\PresentationDemoSeeder;

test('presentation demo seeder creates reusable qualification data', function () {
    $this->seed(PresentationDemoSeeder::class);

    expect(
        User::query()
            ->where('email', 'elza@poetweb.lv')
            ->exists()
    )->toBeTrue();

    expect(
        Group::query()
            ->where('name', 'Cēsu Dzejas Telpa')
            ->where('visibility', 'public')
            ->exists()
    )->toBeTrue();

    expect(
        Group::query()
            ->where('name', 'Klusie Melnraksti')
            ->where('visibility', 'private')
            ->exists()
    )->toBeTrue();

    expect(
        Post::query()
            ->where('title', 'Rīts pie Gaujas')
            ->where('status', 'published')
            ->exists()
    )->toBeTrue();
});

test('presentation demo seeding can run twice without duplicate publications', function () {
    $this->seed(PresentationDemoSeeder::class);

    $firstPostCount = Post::query()->count();
    $firstCommentCount = \App\Models\Comment::query()->count();
    $firstMessageCount = \App\Models\Message::query()->count();

    $this->seed(PresentationDemoSeeder::class);

    expect(Post::query()->count())->toBe($firstPostCount);
    expect(\App\Models\Comment::query()->count())->toBe($firstCommentCount);
    expect(\App\Models\Message::query()->count())->toBe($firstMessageCount);

    expect(Post::query()->where('type', 'poem')->where('status', 'published')->count())
        ->toBeGreaterThanOrEqual(15);

    expect(Post::query()->where('status', 'draft')->exists())->toBeTrue();
});

test('presentation demo preserves existing demo account credentials', function () {
    $user = User::factory()->create([
        'name' => 'Existing Account',
        'email' => 'elza@poetweb.lv',
        'password' => 'AnExistingPassword123!',
    ]);

    $oldHash = $user->password;

    $this->seed(PresentationDemoSeeder::class);

    expect($user->fresh()->password)->toBe($oldHash);
});
