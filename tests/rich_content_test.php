<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../src/rich_content.php';

$raw = '<p>Hello <strong>Quiz</strong></p><script>alert(1)</script><ul><li>One</li></ul>';
$sanitized = sanitizeQuestionContent($raw);

assertTrueValue(str_contains($sanitized, '<strong>Quiz</strong>'), 'Allowed tags should remain.');
assertTrueValue(! str_contains($sanitized, '<script>'), 'Scripts must be stripped.');
assertSameValue('<p>Fallback</p>', normalizeQuestionContent('', 'Fallback'), 'Empty rich content should fall back to plain question text.');

echo "OK rich_content_test\n";
