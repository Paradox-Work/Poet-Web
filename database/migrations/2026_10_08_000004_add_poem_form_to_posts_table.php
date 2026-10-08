<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('posts', 'poem_form')) {
            Schema::table('posts', function (Blueprint $table) {
                $table
                    ->string('poem_form', 50)
                    ->nullable()
                    ->after('type');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('posts', 'poem_form')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->dropColumn('poem_form');
            });
        }
    }
};
