<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            !Schema::hasTable(
                'followers'
            )
        ) {
            return;
        }

        /*
         * Clean historical duplicates before
         * adding the database-level invariant.
         */
        $duplicates =
            DB::table('followers')
                ->select(
                    'user_id',
                    'follower_id',
                    DB::raw(
                        'MIN(id) as keep_id'
                    )
                )
                ->groupBy(
                    'user_id',
                    'follower_id'
                )
                ->havingRaw(
                    'COUNT(*) > 1'
                )
                ->get();

        foreach (
            $duplicates
            as $duplicate
        ) {
            DB::table('followers')
                ->where(
                    'user_id',
                    $duplicate->user_id
                )
                ->where(
                    'follower_id',
                    $duplicate->follower_id
                )
                ->where(
                    'id',
                    '!=',
                    $duplicate->keep_id
                )
                ->delete();
        }

        if (
            !$this->hasUniqueConstraint()
        ) {
            Schema::table(
                'followers',
                function (
                    Blueprint $table
                ) {
                    $table->unique(
                        [
                            'user_id',
                            'follower_id',
                        ],
                        'followers_user_follower_unique'
                    );
                }
            );
        }
    }

    public function down(): void
    {
        if (
            Schema::hasTable(
                'followers'
            ) &&
            $this->hasUniqueConstraint()
        ) {
            Schema::table(
                'followers',
                function (
                    Blueprint $table
                ) {
                    $table->dropUnique(
                        'followers_user_follower_unique'
                    );
                }
            );
        }
    }

    private function hasUniqueConstraint(): bool
    {
        return collect(
            Schema::getIndexes(
                'followers'
            )
        )
            ->contains(
                function (
                    array $index
                ) {
                    return (
                        $index['unique']
                        ?? false
                    ) &&
                        (
                            $index['columns']
                            ?? []
                        ) ===
                        [
                            'user_id',
                            'follower_id',
                        ];
                }
            );
    }
};
