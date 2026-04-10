<?php
declare(strict_types=1);

require_once __DIR__ . '/storage.php';

function importStudentsFromCsv(string $csvPath, string $usersFile): array
{
    $handle = fopen($csvPath, 'rb');
    if ($handle === false) {
        throw new RuntimeException('Unable to open CSV file.');
    }

    $header = fgetcsv($handle, 0, ',', '"', '');
    if ($header !== ['username', 'display_name', 'initial_password']) {
        fclose($handle);
        throw new RuntimeException('CSV header must be username,display_name,initial_password.');
    }

    $users = loadRecords($usersFile);
    $usernames = array_map('strtolower', array_column($users, 'username'));
    $created = 0;
    $skipped = 0;
    $errors = [];
    $line = 1;

    while (($row = fgetcsv($handle, 0, ',', '"', '')) !== false) {
        $line++;
        if (count($row) !== 3) {
            $errors[] = "Line {$line}: expected 3 columns.";
            $skipped++;
            continue;
        }

        [$username, $displayName, $password] = array_map('trim', $row);
        if ($username === '' || $displayName === '' || $password === '') {
            $errors[] = "Line {$line}: username, display name, and password are required.";
            $skipped++;
            continue;
        }

        $usernameKey = strtolower($username);
        if (in_array($usernameKey, $usernames, true)) {
            $skipped++;
            continue;
        }

        $users[] = [
            'id' => nextId($users),
            'username' => $username,
            'display_name' => $displayName,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 'student',
            'group_id' => null,
        ];
        $usernames[] = $usernameKey;
        $created++;
    }

    fclose($handle);
    saveRecords($usersFile, $users);

    return ['created' => $created, 'skipped' => $skipped, 'errors' => $errors];
}

function buildQuizResultsCsv(array $quiz, array $submissions, array $usersById = []): string
{
    $handle = fopen('php://temp', 'rb+');
    if ($handle === false) {
        throw new RuntimeException('Unable to create CSV stream.');
    }

    fputcsv($handle, ['student_id', 'student_name', 'score', 'submitted_at'], ',', '"', '');
    foreach ($submissions as $submission) {
        if ((int) $submission['quiz_id'] !== (int) $quiz['id']) {
            continue;
        }

        $studentId = (int) $submission['student_id'];
        fputcsv($handle, [
            $studentId,
            $usersById[$studentId]['display_name'] ?? '',
            (int) $submission['score'],
            $submission['submitted_at'] ?? '',
        ], ',', '"', '');
    }

    rewind($handle);
    $csv = stream_get_contents($handle);
    fclose($handle);

    return $csv === false ? '' : $csv;
}
