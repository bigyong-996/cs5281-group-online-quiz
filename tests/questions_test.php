<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../src/questions.php';

$tempDir = makeTempDir('questions');
$questionsFile = $tempDir . '/questions.json';
saveRecords($questionsFile, []);

$question = saveQuestion([
    'question_text' => 'What is the capital of France?',
    'topic' => 'Geography',
    'options' => ['Paris', 'London', 'Berlin', 'Rome'],
    'correct_answer' => 'Paris',
], $questionsFile);

assertSameValue('Paris', $question['correct_answer'], 'Question should keep its correct answer.');

$updated = updateQuestion((int) $question['id'], [
    'question_text' => 'What is the capital of Germany?',
    'topic' => 'Geography',
    'options' => ['Paris', 'London', 'Berlin', 'Rome'],
    'correct_answer' => 'Berlin',
], $questionsFile);

assertSameValue('Berlin', $updated['correct_answer'], 'Question update should overwrite the correct answer.');
deleteQuestion((int) $question['id'], $questionsFile);
assertSameValue([], loadRecords($questionsFile), 'Question delete should remove the record.');

echo "OK questions_test\n";
