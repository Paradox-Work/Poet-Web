<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            if (!Schema::hasColumn('posts', 'title')) {
                $table->string('title')->after('id')->nullable();
            }

            if (!Schema::hasColumn('posts', 'category')) {
                $table->string('category')->default('general')->after('title');
            }
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            if (Schema::hasColumn('posts', 'category')) {
                $table->dropColumn('category');
            }

            if (Schema::hasColumn('posts', 'title')) {
                $table->dropColumn('title');
            }
        });
    }
};
