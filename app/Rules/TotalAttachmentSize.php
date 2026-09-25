<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class TotalAttachmentSize implements ValidationRule
{
    public function __construct(
        private int $maxMegabytes = 90
    ) {
    }

    public function validate(
        string $attribute,
        mixed $value,
        Closure $fail
    ): void {
        $totalSize = collect($value)->sum(
            function ($file) {
                return $file instanceof UploadedFile
                    ? $file->getSize()
                    : 0;
            }
        );

        $maxBytes =
            $this->maxMegabytes * 1024 * 1024;

        if ($totalSize > $maxBytes) {
            $fail(
                "The total size of all attachments must not exceed {$this->maxMegabytes} MB."
            );
        }
    }
}