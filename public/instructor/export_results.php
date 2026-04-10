<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/auth.php';
require_once __DIR__ . '/../../src/csv_tools.php';

requireRole('instructor');

$quizId = (int) ($_GET['quiz_id'] ?? 0);
$quiz = findRecordById(loadRecords(DATA_DIR . '/quizzes.json'), $quizId);

if ($quiz === null) {
    flashMessage('error', 'Quiz not found.');
    redirectTo('/instructor/results.php');
}

$usersById = [];
foreach (loadRecords(DATA_DIR . '/users.json') as $userRecord) {
    $usersById[(int) $userRecord['id']] = $userRecord;
}

$csv = buildQuizResultsCsv($quiz, loadRecords(DATA_DIR . '/submissions.json'), $usersById);

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="quiz-' . $quizId . '-results.csv"');
echo $csv;
