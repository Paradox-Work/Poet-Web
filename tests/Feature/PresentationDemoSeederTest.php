<?php

use App\Models\Bookmark;
use App\Models\Conversation;
use App\Models\Group;
use App\Models\Post;
use App\Models\PostCollaborator;
use App\Models\ReadingList;
use App\Models\Report;
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

    $elza = User::query()
        ->where('email', 'elza@poetweb.lv')
        ->firstOrFail();

    expect($elza->is_site_admin)->toBeTrue();

    expect(
        Conversation::query()->count()
    )->toBeGreaterThan(0);

    expect(
        Bookmark::query()
            ->where('user_id', $elza->id)
            ->exists()
    )->toBeTrue();

    expect(
        ReadingList::query()
            ->where('user_id', $elza->id)
            ->exists()
    )->toBeTrue();

    expect(
        PostCollaborator::query()
            ->where('status', 'accepted')
            ->exists()
    )->toBeTrue();

    expect(
        Report::query()
            ->where('status', 'pending')
            ->exists()
    )->toBeTrue();
});
