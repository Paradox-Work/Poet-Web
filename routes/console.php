<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Artisan::command('poet:make-admin {email}', function (string $email) {
    $user = \App\Models\User::query()
        ->where('email', $email)
        ->first();

    if (!$user) {
        $this->error('User not found.');
        return 1;
    }

    $user->forceFill([
        'is_site_admin' => true,
        'suspended_at' => null,
    ])->save();

    $this->info("{$user->email} is now a Poet-Web site administrator.");

    return 0;
})->purpose('Promote an existing Poet-Web user to site administrator');
