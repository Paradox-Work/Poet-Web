<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

class RichTextSanitizer
{
    private const ALLOWED_TAGS = [
        'p',
        'br',
        'strong',
        'b',
        'em',
        'i',
        'u',
        's',
        'strike',
        'h1',
        'h2',
        'h3',
        'ul',
        'ol',
        'li',
        'blockquote',
        'a',
        'code',
        'pre',
        'hr',
    ];

    private const REMOVE_COMPLETELY = [
        'script',
        'style',
        'iframe',
        'object',
        'embed',
        'svg',
        'math',
        'form',
        'input',
        'button',
        'textarea',
        'select',
        'option',
        'link',
        'meta',
    ];

    public static function sanitize(
        ?string $html
    ): ?string {
        if ($html === null) {
            return null;
        }

        if (trim($html) === '') {
            return '';
        }

        if (
            !class_exists(
                DOMDocument::class
            )
        ) {
            return htmlspecialchars(
                strip_tags($html),
                ENT_QUOTES |
                    ENT_SUBSTITUTE,
                'UTF-8'
            );
        }

        $previousErrors =
            libxml_use_internal_errors(
                true
            );

        $dom =
            new DOMDocument(
                '1.0',
                'UTF-8'
            );

        $dom->loadHTML(
            '<div id="poet-root">' .
                $html .
            '</div>',
            LIBXML_HTML_NOIMPLIED |
                LIBXML_HTML_NODEFDTD
        );

        $root =
            $dom->getElementById(
                'poet-root'
            );

        if (!$root) {
            libxml_clear_errors();

            libxml_use_internal_errors(
                $previousErrors
            );

            return '';
        }

        self::sanitizeChildren(
            $root
        );

        $clean = '';

        foreach (
            iterator_to_array(
                $root->childNodes
            )
            as $child
        ) {
            $clean .=
                $dom->saveHTML(
                    $child
                );
        }

        libxml_clear_errors();

        libxml_use_internal_errors(
            $previousErrors
        );

        return $clean;
    }

    private static function sanitizeChildren(
        DOMNode $parent
    ): void {
        foreach (
            iterator_to_array(
                $parent->childNodes
            )
            as $child
        ) {
            if (
                !$child instanceof
                    DOMElement
            ) {
                continue;
            }

            $tag =
                strtolower(
                    $child->tagName
                );

            if (
                in_array(
                    $tag,
                    self::REMOVE_COMPLETELY,
                    true
                )
            ) {
                $child->parentNode
                    ?->removeChild(
                        $child
                    );

                continue;
            }

            if (
                !in_array(
                    $tag,
                    self::ALLOWED_TAGS,
                    true
                )
            ) {
                self::sanitizeChildren(
                    $child
                );

                self::unwrap(
                    $child
                );

                continue;
            }

            self::sanitizeAttributes(
                $child
            );

            self::sanitizeChildren(
                $child
            );
        }
    }

    private static function sanitizeAttributes(
        DOMElement $element
    ): void {
        $tag =
            strtolower(
                $element->tagName
            );

        $allowed =
            $tag === 'a'
                ? [
                    'href',
                    'target',
                    'title',
                ]
                : [];

        foreach (
            iterator_to_array(
                $element->attributes
            )
            as $attribute
        ) {
            if (
                !in_array(
                    strtolower(
                        $attribute->name
                    ),
                    $allowed,
                    true
                )
            ) {
                $element->removeAttribute(
                    $attribute->name
                );
            }
        }

        if ($tag !== 'a') {
            return;
        }

        $href =
            trim(
                html_entity_decode(
                    $element
                        ->getAttribute(
                            'href'
                        ),
                    ENT_QUOTES |
                        ENT_HTML5,
                    'UTF-8'
                )
            );

        if (
            !self::isSafeHref(
                $href
            )
        ) {
            $element->removeAttribute(
                'href'
            );
        }

        $target =
            $element->getAttribute(
                'target'
            );

        if (
            !in_array(
                $target,
                [
                    '',
                    '_self',
                    '_blank',
                ],
                true
            )
        ) {
            $element->removeAttribute(
                'target'
            );
        }

        if (
            $element->getAttribute(
                'target'
            ) === '_blank'
        ) {
            $element->setAttribute(
                'rel',
                'noopener noreferrer'
            );
        }
    }

    private static function isSafeHref(
        string $href
    ): bool {
        if ($href === '') {
            return false;
        }

        if (
            str_starts_with(
                $href,
                '#'
            ) ||
            str_starts_with(
                $href,
                '/'
            )
        ) {
            return true;
        }

        $scheme =
            strtolower(
                parse_url(
                    $href,
                    PHP_URL_SCHEME
                )
                ?? ''
            );

        return in_array(
            $scheme,
            [
                'http',
                'https',
                'mailto',
            ],
            true
        );
    }

    private static function unwrap(
        DOMElement $element
    ): void {
        $parent =
            $element->parentNode;

        if (!$parent) {
            return;
        }

        while (
            $element->firstChild
        ) {
            $parent->insertBefore(
                $element->firstChild,
                $element
            );
        }

        $parent->removeChild(
            $element
        );
    }
}
