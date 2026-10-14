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
