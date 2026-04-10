<?php
declare(strict_types=1);

require_once __DIR__ . '/storage.php';

function questionsFile(?string $override = null): string
{
    return $override ?? DATA_DIR . '/questions.json';
}

function normalizeQuestionInput(array $input): array
{
    $options = array_map('trim', $input['options'] ?? []);

    return [
        'question_text' => trim((string) ($input['question_text'] ?? '')),
        'topic' => trim((string) ($input['topic'] ?? '')),
        'options' => $options,
        'correct_answer' => trim((string) ($input['correct_answer'] ?? '')),
    ];
}

function validateQuestionInput(array $input): void
{
    if ($input['question_text'] === '') {
        throw new InvalidArgumentException('Question text is required.');
    }

    if (count($input['options']) !== 4) {
        throw new InvalidArgumentException('Each MCQ must have exactly four options.');
    }

    foreach ($input['options'] as $option) {
        if ($option === '') {
            throw new InvalidArgumentException('All four options are required.');
        }
    }

    if (! in_array($input['correct_answer'], $input['options'], true)) {
        throw new InvalidArgumentException('Correct answer must match one of the four options.');
    }
}

function saveQuestion(array $input, ?string $override = null): array
{
    $normalized = normalizeQuestionInput($input);
    validateQuestionInput($normalized);

    $file = questionsFile($override);
    $questions = loadRecords($file);
    $question = [
        'id' => nextId($questions),
        ...$normalized,
    ];
    $questions[] = $question;
    saveRecords($file, $questions);

    return $question;
}

function updateQuestion(int $id, array $input, ?string $override = null): array
{
    $normalized = normalizeQuestionInput($input);
    validateQuestionInput($normalized);

    $file = questionsFile($override);
    $questions = loadRecords($file);
    foreach ($questions as &$question) {
        if ((int) $question['id'] === $id) {
            $question = ['id' => $id, ...$normalized];
            saveRecords($file, $questions);
            return $question;
        }
    }

    throw new RuntimeException('Question not found.');
}

function deleteQuestion(int $id, ?string $override = null): void
{
    $file = questionsFile($override);
    $questions = array_values(array_filter(loadRecords($file), static fn(array $question): bool => (int) $question['id'] !== $id));
    saveRecords($file, $questions);
}

function questionsById(array $questions): array
{
    $byId = [];
    foreach ($questions as $question) {
        $byId[(int) $question['id']] = $question;
    }

    return $byId;
}
