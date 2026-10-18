<?php

namespace App\Support;

use App\Models\Post;

/**
 * Repairs ONLY known demo publications whose stored HTML contains the
 * mojibake produced by earlier DOMDocument parsing without UTF-8.
 *
 * The seeder supplies the trusted original text; unrelated user content is
 * never modified, and correctly encoded demo edits are left untouched.
 */
final class PresentationDemoTextRepair
{
    public static function restoreIfCorrupted(Post $post, string $originalHtml): void
    {
        $storedHtml = $post->getRawOriginal('body');

        if (!is_string($storedHtml) || !self::containsMojibake($storedHtml)) {
            return;
        }

        $post->body = $originalHtml;
        $post->save();
    }

    private static function containsMojibake(string $text): bool
    {
        foreach (['Ã', 'Â', 'Ä', "\u{FFFD}"] as $marker) {
            if (str_contains($text, $marker)) {
                return true;
            }
        }

        return false;
    }
}
