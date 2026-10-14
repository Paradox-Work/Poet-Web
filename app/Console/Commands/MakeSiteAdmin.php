<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeSiteAdmin extends Command
{
    protected $signature = 'app:make-admin {username} {--remove : Remove site-admin access}';

    protected $description = 'Grant or remove Poet-Web site-administrator access by username.';

    public function handle(): int
    {
        $user = User::query()
            ->where('username', $this->argument('username'))
            ->first();

        if (!$user) {
            $this->error('User not found.');
            return self::FAILURE;
        }

        $user->is_admin = !$this->option('remove');
        $user->save();

        $this->info(
            $user->is_admin
                ? "@{$user->username} is now a site administrator."
                : "@{$user->username} is no longer a site administrator."
        );

        return self::SUCCESS;
    }
}
