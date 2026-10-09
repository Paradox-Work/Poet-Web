<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            !Schema::hasColumn(
                'posts',
                'content_rating'
            )
        ) {
            Schema::table(
                'posts',
                function (
                    Blueprint $table
                ) {
                    $table
                        ->string(
                            'content_rating',
                            20
                        )
                        ->default(
                            'general'
                        )
                        ->after('type');
                }
            );
        }
    }

    public function down(): void
    {
        if (
            Schema::hasColumn(
                'posts',
                'content_rating'
            )
        ) {
            Schema::table(
                'posts',
                function (
                    Blueprint $table
                ) {
                    $table->dropColumn(
                        'content_rating'
                    );
                }
            );
        }
    }
};
