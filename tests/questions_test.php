<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../src/questions.php';

$tempDir = makeTempDir('questions');
$questionsFile = $tempDir . '/questions.json';
saveRecords($questionsFile, []);

$question = saveQuestion([
    'question_text' => 'What is the capital of France?',
    'question_content_html' => '<p><strong>Observe the chart</strong> and answer the question.</p>',
    'question_image_path' => '/uploads/questions/example.png',
    'topic' => 'Geography',
    'options' => ['Paris', 'London', 'Berlin', 'Rome'],
    'correct_answer' => 'Paris',
], $questionsFile);

assertSameValue('Paris', $question['correct_answer'], 'Question should keep its correct answer.');
assertSameValue('/uploads/questions/example.png', $question['question_image_path'], 'Question image path should persist.');
assertTrueValue(str_contains($question['question_content_html'], '<strong>Observe the chart</strong>'), 'Rich content should be stored.');

$updated = updateQuestion((int) $question['id'], [
    'question_text' => 'What is the capital of Germany?',
    'question_content_html' => '<p>Updated prompt</p>',
    'question_image_path' => '/uploads/questions/updated.png',
    'topic' => 'Geography',
    'options' => ['Paris', 'London', 'Berlin', 'Rome'],
    'correct_answer' => 'Berlin',
], $questionsFile);

assertSameValue('Berlin', $updated['correct_answer'], 'Question update should overwrite the correct answer.');
assertSameValue('/uploads/questions/updated.png', $updated['question_image_path'], 'Question update should overwrite the image path.');
deleteQuestion((int) $question['id'], $questionsFile);
assertSameValue([], loadRecords($questionsFile), 'Question delete should remove the record.');

echo "OK questions_test\n";
