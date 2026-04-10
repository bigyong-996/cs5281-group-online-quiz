<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../src/submissions.php';

$quiz = [
    'id' => 1,
    'question_ids' => [10, 11],
];

$questions = [
    10 => ['id' => 10, 'question_text' => 'Capital of France?', 'correct_answer' => 'Paris'],
    11 => ['id' => 11, 'question_text' => '2+2?', 'correct_answer' => '4'],
];

$result = scoreSubmission($quiz, $questions, [
    10 => 'Paris',
    11 => '5',
]);

assertSameValue(50, $result['score'], 'One correct answer out of two should score 50.');
assertSameValue(false, $result['details'][11]['is_correct'], 'Incorrect answer should be flagged.');

$tempDir = makeTempDir('submissions');
$submissionsFile = $tempDir . '/submissions.json';
saveRecords($submissionsFile, []);
assertTrueValue(hasStudentSubmitted(1, 7, $submissionsFile) === false, 'No submission should exist yet.');

saveSubmissionRecord([
    'quiz_id' => 1,
    'student_id' => 7,
    'score' => 50,
    'answers' => [10 => 'Paris', 11 => '5'],
    'details' => $result['details'],
], $submissionsFile);

assertTrueValue(hasStudentSubmitted(1, 7, $submissionsFile), 'Saved submission should block a second attempt.');

echo "OK submissions_test\n";
