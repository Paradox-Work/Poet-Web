<?php

use App\Models\Post;
use App\Support\RichTextSanitizer;
use Database\Seeders\PresentationDemoSeeder;

test('rich text sanitizer preserves Latvian UTF-8 through repeated processing', function () {
    $html = '<p>Rīts saloka miglu pār upi,<br>un pilsēta mostas bez steigas.</p>';

    $clean = RichTextSanitizer::sanitize($html);

    expect($clean)
        ->toContain('Rīts saloka miglu pār upi')
        ->toContain('pilsēta mostas bez steigas')
        ->not->toContain('RÄ');

    expect(RichTextSanitizer::sanitize($clean))->toBe($clean);
});

test('reseeding repairs corrupted featured demo poem and keeps English demo author', function () {
    $this->seed(PresentationDemoSeeder::class);

    $post = Post::query()->where('title', 'Rīts pie Gaujas')->firstOrFail();
    $post->setRawAttributes(array_merge($post->getAttributes(), [
        'body' => '<p>RÄ«ts saloka miglu pÄr upi.</p>',
    ]));
    $post->saveQuietly();

    $this->seed(PresentationDemoSeeder::class);

    $post->refresh();
    expect($post->body)->toContain('Rīts saloka miglu pār upi');

    $author = \App\Models\User::query()
        ->where('email', 'rihards@poetweb.lv')
        ->firstOrFail();

    expect($author->name)->toBe('Oliver Reed');
    expect(Post::query()->where('user_id', $author->id)->where('title', 'Across the Old Bridge')->exists())->toBeTrue();
});
