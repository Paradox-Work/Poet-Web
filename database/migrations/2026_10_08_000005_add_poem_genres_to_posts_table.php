<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('posts', 'poem_genres')) {
            Schema::table('posts', function (Blueprint $table) {
                $table
                    ->json('poem_genres')
                    ->nullable()
                    ->after('poem_form');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('posts', 'poem_genres')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->dropColumn('poem_genres');
            });
        }
    }
};
