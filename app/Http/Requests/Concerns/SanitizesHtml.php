<?php

namespace App\Http\Requests\Concerns;

/**
 * Provides HTML sanitization helpers for FormRequests.
 *
 * strip_tags is used rather than HTMLPurifier to avoid a composer dependency.
 * The safe-tag allowlist blocks the most common XSS vectors (<script>, <iframe>,
 * inline event handlers) while preserving WYSIWYG formatting.
 *
 * For production with untrusted input, replace with HTMLPurifier or league/html-to-markdown.
 */
trait SanitizesHtml
{
    /**
     * Strip all HTML — use for plain-text fields (descriptions, titles).
     */
    protected function stripAllHtml(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        return strip_tags($value);
    }

    /**
     * Strip dangerous tags, keep safe formatting tags — use for WYSIWYG/rich-text content.
     */
    protected function stripDangerousTags(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $allowed = implode('', [
            '<p>', '<br>', '<b>', '<i>', '<u>', '<s>',
            '<strong>', '<em>', '<mark>',
            '<h1>', '<h2>', '<h3>', '<h4>', '<h5>', '<h6>',
            '<ul>', '<ol>', '<li>',
            '<blockquote>', '<code>', '<pre>',
            '<table>', '<thead>', '<tbody>', '<tr>', '<td>', '<th>',
            '<a>', '<img>', '<span>', '<div>',
            '<sup>', '<sub>',
        ]);

        return strip_tags($value, $allowed);
    }
}
