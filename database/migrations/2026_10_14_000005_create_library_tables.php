<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'post_id']);
        });

        Schema::create('reading_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 80);
            $table->string('description', 300)->nullable();
            $table->timestamps();
            $table->index(['user_id', 'name']);
        });

        Schema::create('reading_list_post', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reading_list_id')
                ->constrained('reading_lists')
                ->cascadeOnDelete();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['reading_list_id', 'post_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reading_list_post');
        Schema::dropIfExists('reading_lists');
        Schema::dropIfExists('bookmarks');
    }
};
