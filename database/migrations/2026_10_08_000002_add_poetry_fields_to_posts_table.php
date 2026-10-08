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
                ->string('type', 20)
                ->default('post')
                ->after('group_id');

            $table
                ->string('title', 160)
                ->nullable()
                ->after('type');

            $table
                ->text('caption')
                ->nullable()
                ->after('title');

            $table
                ->json('hashtags')
                ->nullable()
                ->after('caption');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn([
                'type',
                'title',
                'caption',
                'hashtags',
            ]);
        });
    }
};
