<?php

use App\Models\Report;
use App\Models\User;

test('resubmitting a resolved report does not reopen it', function () {
    $author = User::factory()->create();
    $reporter = User::factory()->create();
    $post = poetTestPost($author);
    $payload = [
        'type' => 'post',
        'id' => $post->id,
        'reason' => 'spam',
        'details' => 'Original report',
    ];

    $this->actingAs($reporter)->post(route('reports.store'), $payload)
        ->assertSessionHasNoErrors();

    $report = Report::query()->where('reporter_id', $reporter->id)
        ->where('reportable_type', 'post')->where('reportable_id', $post->id)
        ->firstOrFail();
    $report->update(['status' => 'dismissed', 'reviewed_at' => now()]);

    $this->actingAs($reporter)->post(route('reports.store'), [
        ...$payload,
        'reason' => 'harassment',
    ])->assertSessionHasNoErrors();

    expect($report->fresh()->status)->toBe('dismissed');
    expect($report->fresh()->reason)->toBe('spam');
    expect(Report::query()->where('reporter_id', $reporter->id)->count())->toBe(1);
});
