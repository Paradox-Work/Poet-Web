<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table
                ->string('status', 20)
                ->default('published')
                ->after('hashtags');

            $table
                ->timestamp('published_at')
                ->nullable()
                ->after('status');

            $table
                ->timestamp('draft_saved_at')
                ->nullable()
                ->after('published_at');

            $table->index([
                'user_id',
                'status',
                'draft_saved_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex([
                'user_id',
                'status',
                'draft_saved_at',
            ]);

            $table->dropColumn([
                'status',
                'published_at',
                'draft_saved_at',
            ]);
        });
    }
};
