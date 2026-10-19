<?php

use App\Support\RichTextSanitizer;

test('rich text sanitizer preserves Latvian UTF-8 characters', function () {
    $html = '<p>Rīts saloka miglu pār upi,<br>un pilsēta mostas bez steigas. '
        . 'Uz tilta paliek tikai soļi, kas zina ceļu uz gaismu.</p>';

    expect(RichTextSanitizer::sanitize($html))
        ->toBe($html);
});
