<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/../src/storage.php';

function assertSameValue(mixed $expected, mixed $actual, string $message): void
{
    if ($expected !== $actual) {
        fwrite(STDERR, $message . PHP_EOL . 'Expected: ' . var_export($expected, true) . PHP_EOL . 'Actual: ' . var_export($actual, true) . PHP_EOL);
        exit(1);
    }
}

function assertTrueValue(bool $condition, string $message): void
{
    if (! $condition) {
        fwrite(STDERR, $message . PHP_EOL);
        exit(1);
    }
}

function makeTempDir(string $label): string
{
    $dir = sys_get_temp_dir() . '/quiz-system-' . $label . '-' . bin2hex(random_bytes(4));
    mkdir($dir, 0777, true);
    return $dir;
}
