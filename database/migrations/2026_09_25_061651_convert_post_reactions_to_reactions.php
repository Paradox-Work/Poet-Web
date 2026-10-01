<?php

use App\Models\Post;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'post_reactions',
            function (Blueprint $table) {
                $table->dropForeign([
                    'post_id'
                ]);
            }
        );

        Schema::table(
            'post_reactions',
            function (Blueprint $table) {
                $table->renameColumn(
                    'post_id',
                    'object_id'
                );
            }
        );

        Schema::rename(
            'post_reactions',
            'reactions'
        );

        Schema::table(
            'reactions',
            function (Blueprint $table) {
                $table
                    ->string('object_type')
                    ->after('object_id');

                $table->unique(
                    [
                        'object_id',
                        'object_type',
                        'user_id',
                    ],
                    'reactions_object_user_unique'
                );
            }
        );

        DB::table('reactions')
            ->update([
                'object_type' => Post::class,
            ]);
    }

    public function down(): void
    {
        Schema::table(
            'reactions',
            function (Blueprint $table) {
                $table->dropUnique(
                    'reactions_object_user_unique'
                );

                $table->dropColumn(
                    'object_type'
                );
            }
        );

        Schema::rename(
            'reactions',
            'post_reactions'
        );

        Schema::table(
            'post_reactions',
            function (Blueprint $table) {
                $table->renameColumn(
                    'object_id',
                    'post_id'
                );

                $table
                    ->foreign('post_id')
                    ->references('id')
                    ->on('posts');
            }
        );
    }
};