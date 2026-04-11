<?php
declare(strict_types=1);

function sanitizeQuestionContent(string $rawHtml): string
{
    $allowedTags = '<p><br><strong><em><ul><ol><li>';
    $sanitized = strip_tags($rawHtml, $allowedTags);
    $sanitized = preg_replace('/\s+on\w+\s*=\s*(".*?"|\'.*?\'|[^\s>]+)/i', '', $sanitized) ?? '';
    $sanitized = preg_replace('/\s+style\s*=\s*(".*?"|\'.*?\'|[^\s>]+)/i', '', $sanitized) ?? '';

    return trim($sanitized);
}

function normalizeQuestionContent(string $rawHtml, string $fallbackText): string
{
    $sanitized = sanitizeQuestionContent($rawHtml);
    if ($sanitized !== '') {
        return $sanitized;
    }

    $escapedFallback = trim(htmlspecialchars($fallbackText, ENT_QUOTES, 'UTF-8'));
    return $escapedFallback === '' ? '' : '<p>' . $escapedFallback . '</p>';
}
