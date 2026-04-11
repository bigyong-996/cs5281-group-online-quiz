<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../src/statistics.php';
require_once __DIR__ . '/../src/csv_tools.php';

$quiz = [
    'id' => 1,
    'title' => 'Week 1 Quiz',
    'question_ids' => [10, 11],
];

$questionsById = [
    10 => ['id' => 10, 'question_text' => 'Capital of France?', 'correct_answer' => 'Paris'],
    11 => ['id' => 11, 'question_text' => '2+2?', 'correct_answer' => '4'],
];

$submissions = [
    ['id' => 1, 'quiz_id' => 1, 'student_id' => 7, 'score' => 100, 'submitted_at' => '2026-04-10T10:00:00+08:00', 'details' => [10 => ['is_correct' => true], 11 => ['is_correct' => true]]],
    ['id' => 2, 'quiz_id' => 1, 'student_id' => 8, 'score' => 50, 'submitted_at' => '2026-04-10T10:05:00+08:00', 'details' => [10 => ['is_correct' => true], 11 => ['is_correct' => false]]],
];

$summary = summarizeQuizStatistics($quiz, $questionsById, $submissions);
assertSameValue(75, $summary['average_score'], 'Average score should be rounded from quiz submissions.');
assertSameValue('Week 1 Quiz', $summary['quiz_title'], 'The statistics summary should include the quiz title.');
assertSameValue('Capital of France?', $summary['per_question_accuracy'][10]['question_text'], 'Per-question accuracy should include readable question text.');
assertSameValue(100, $summary['high_score'], 'The statistics summary should include the highest score.');
assertSameValue(100, $summary['per_question_accuracy'][10]['accuracy'], 'Question 10 should show 100 percent accuracy.');
assertSameValue(50, $summary['per_question_accuracy'][11]['accuracy'], 'Question 11 should show 50 percent accuracy.');

$csv = buildQuizResultsCsv($quiz, $submissions, [7 => ['display_name' => 'Alice'], 8 => ['display_name' => 'Bob']]);
assertTrueValue(str_contains($csv, 'student_id,student_name,score,submitted_at'), 'Export CSV should include a header row.');
assertTrueValue(str_contains($csv, '7,Alice,100'), 'Export CSV should contain student score rows.');

echo "OK statistics_test\n";
