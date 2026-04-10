<?php
declare(strict_types=1);

function ensureJsonFile(string $path, array $default = []): void
{
    $directory = dirname($path);
    if (! is_dir($directory)) {
        mkdir($directory, 0777, true);
    }

    if (! file_exists($path)) {
        file_put_contents($path, json_encode($default, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}

function loadRecords(string $path): array
{
    ensureJsonFile($path);
    $json = file_get_contents($path);
    $data = json_decode($json === false ? '[]' : $json, true);

    return is_array($data) ? $data : [];
}

function saveRecords(string $path, array $records): void
{
    ensureJsonFile($path);
    file_put_contents($path, json_encode(array_values($records), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

function nextId(array $records): int
{
    if ($records === []) {
        return 1;
    }

    return max(array_map(static fn(array $record): int => (int) ($record['id'] ?? 0), $records)) + 1;
}

function findRecordById(array $records, int $id): ?array
{
    foreach ($records as $record) {
        if ((int) ($record['id'] ?? 0) === $id) {
            return $record;
        }
    }

    return null;
}
