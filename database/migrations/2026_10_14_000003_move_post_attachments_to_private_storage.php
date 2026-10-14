<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('post_attachments')
            ->orderBy('id')
            ->each(function ($attachment) {
                $path = $attachment->path;

                if (
                    Storage::disk('local')->exists($path) ||
                    !Storage::disk('public')->exists($path)
                ) {
                    return;
                }

                $stream = Storage::disk('public')->readStream($path);

                if ($stream === false) {
                    throw new RuntimeException("Unable to read attachment {$attachment->id}.");
                }

                try {
                    if (!Storage::disk('local')->writeStream($path, $stream)) {
                        throw new RuntimeException("Unable to privatize attachment {$attachment->id}.");
                    }
                } finally {
                    if (is_resource($stream)) {
                        fclose($stream);
                    }
                }

                Storage::disk('public')->delete($path);
            });
    }

    public function down(): void
    {
        DB::table('post_attachments')
            ->orderBy('id')
            ->each(function ($attachment) {
                $path = $attachment->path;

                if (
                    Storage::disk('public')->exists($path) ||
                    !Storage::disk('local')->exists($path)
                ) {
                    return;
                }

                $stream = Storage::disk('local')->readStream($path);

                if ($stream === false) {
                    throw new RuntimeException("Unable to read attachment {$attachment->id}.");
                }

                try {
                    if (!Storage::disk('public')->writeStream($path, $stream)) {
                        throw new RuntimeException("Unable to restore attachment {$attachment->id}.");
                    }
                } finally {
                    if (is_resource($stream)) {
                        fclose($stream);
                    }
                }

                Storage::disk('local')->delete($path);
            });
    }
};
