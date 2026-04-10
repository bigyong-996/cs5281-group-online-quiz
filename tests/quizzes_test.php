<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../src/quizzes.php';

$tempDir = makeTempDir('quizzes');
$quizzesFile = $tempDir . '/quizzes.json';
saveRecords($quizzesFile, []);

$quiz = saveQuiz([
    'title' => 'Week 1 Quiz',
    'description' => 'Intro quiz',
    'time_limit_minutes' => 20,
    'assigned_group_ids' => [1, 2],
    'question_ids' => [3, 4],
    'status' => 'draft',
], $quizzesFile);

assertSameValue('draft', $quiz['status'], 'New quiz should start in draft.');

$published = updateQuizStatus((int) $quiz['id'], 'published', $quizzesFile);
assertSameValue('published', $published['status'], 'Quiz status should update to published.');

$visible = listQuizzesForGroup(2, $quizzesFile);
assertSameValue(1, count($visible), 'Assigned group should see the published quiz.');
assertSameValue(0, count(listQuizzesForGroup(3, $quizzesFile)), 'Unassigned groups should not see the quiz.');

echo "OK quizzes_test\n";
