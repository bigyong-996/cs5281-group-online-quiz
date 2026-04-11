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
if ($quiz === null) {
    echo json_encode(['error' => 'Quiz not found.'], JSON_UNESCAPED_SLASHES);
    exit;
}

$summary = summarizeQuizStatistics($quiz, $questionsById, $submissions);
echo json_encode([
    'quiz_id' => $summary['quiz_id'],
    'quiz_title' => $summary['quiz_title'],
    'average_score' => $summary['average_score'],
    'submission_count' => $summary['submission_count'],
    'high_score' => $summary['high_score'],
    'per_question_accuracy' => $summary['per_question_accuracy'],
], JSON_UNESCAPED_SLASHES);
