<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('posts', 'type')) {
            Schema::table('posts', function (Blueprint $table) {
                $table
                    ->string('type', 20)
                    ->default('post')
                    ->after('group_id');
            });
        }

        if (!Schema::hasColumn('posts', 'title')) {
            Schema::table('posts', function (Blueprint $table) {
                $table
                    ->string('title', 160)
                    ->nullable()
                    ->after('type');
            });
        }

        if (!Schema::hasColumn('posts', 'caption')) {
            Schema::table('posts', function (Blueprint $table) {
                $table
                    ->text('caption')
                    ->nullable()
                    ->after('title');
            });
        }

        if (!Schema::hasColumn('posts', 'hashtags')) {
            Schema::table('posts', function (Blueprint $table) {
                $table
                    ->json('hashtags')
                    ->nullable()
                    ->after('caption');
            });
        }
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $columns = [];

            foreach ([
                'type',
                'title',
                'caption',
                'hashtags',
            ] as $column) {
                if (Schema::hasColumn('posts', $column)) {
                    $columns[] = $column;
                }
            }

            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }
};
