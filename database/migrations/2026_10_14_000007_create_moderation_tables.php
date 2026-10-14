<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_site_admin')->default(false)->after('pinned_post_id');
            $table->timestamp('suspended_at')->nullable()->after('is_site_admin');
            $table->index('is_site_admin');
        });

        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->string('target_type', 30);
            $table->unsignedBigInteger('target_id');
            $table->string('reason', 40);
            $table->text('details')->nullable();
            $table->string('status', 20)->default('pending');
            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('action', 40)->nullable();
            $table->text('action_note')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['target_type', 'target_id']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['is_site_admin']);
            $table->dropColumn(['is_site_admin', 'suspended_at']);
        });
    }
};
