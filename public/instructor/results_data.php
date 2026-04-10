<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/auth.php';
require_once __DIR__ . '/../../src/questions.php';
require_once __DIR__ . '/../../src/statistics.php';

requireRole('instructor');

$quizId = (int) ($_GET['quiz_id'] ?? 0);
$quiz = findRecordById(loadRecords(DATA_DIR . '/quizzes.json'), $quizId);
$questionsById = questionsById(loadRecords(DATA_DIR . '/questions.json'));
$submissions = loadRecords(DATA_DIR . '/submissions.json');

header('Content-Type: application/json');
echo json_encode(
    $quiz === null
        ? ['error' => 'Quiz not found.']
        : summarizeQuizStatistics($quiz, $questionsById, $submissions),
    JSON_UNESCAPED_SLASHES
);
