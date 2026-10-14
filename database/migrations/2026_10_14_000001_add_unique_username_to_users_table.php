<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $seen = [];

        DB::table('users')
            ->orderBy('id')
            ->get(['id', 'username'])
            ->each(function ($user) use (&$seen) {
                $base = trim((string) $user->username);
                $base = $base !== '' ? $base : 'user' . $user->id;
                $candidate = $base;
                $suffix = 2;

                while (isset($seen[mb_strtolower($candidate)])) {
                    $candidate = $base . $suffix;
                    $suffix++;
                }

                if ($candidate !== $user->username) {
                    DB::table('users')
                        ->where('id', $user->id)
                        ->update(['username' => $candidate]);
                }

                $seen[mb_strtolower($candidate)] = true;
            });

        Schema::table('users', function (Blueprint $table) {
            $table->unique('username', 'users_username_unique');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_username_unique');
        });
    }
};
