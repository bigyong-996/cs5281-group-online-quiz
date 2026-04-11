<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../src/layout.php';

ob_start();
renderPageStart('Sample Page', ['role' => 'instructor', 'display_name' => 'Teacher']);
renderPageEnd();
$html = ob_get_clean();

assertTrueValue(str_contains($html, 'Sample Page'), 'Rendered page should contain the page title.');
assertTrueValue(str_contains($html, 'nav-links'), 'Rendered page should include the shared navigation.');

echo "OK layout_smoke_test\n";
