<?php
declare(strict_types=1);

require_once __DIR__ . '/storage.php';

function quizzesFile(?string $override = null): string
{
    return $override ?? DATA_DIR . '/quizzes.json';
}

function normalizeQuizInput(array $input): array
{
    return [
        'title' => trim((string) ($input['title'] ?? '')),
        'description' => trim((string) ($input['description'] ?? '')),
        'time_limit_minutes' => max(1, (int) ($input['time_limit_minutes'] ?? 1)),
        'assigned_group_ids' => array_values(array_unique(array_map('intval', $input['assigned_group_ids'] ?? []))),
        'question_ids' => array_values(array_unique(array_map('intval', $input['question_ids'] ?? []))),
        'status' => $input['status'] ?? 'draft',
    ];
}

function validateQuizInput(array $input): void
{
    if ($input['title'] === '') {
        throw new InvalidArgumentException('Quiz title is required.');
    }

    if ($input['assigned_group_ids'] === []) {
        throw new InvalidArgumentException('Assign the quiz to at least one group.');
    }

    if ($input['question_ids'] === []) {
        throw new InvalidArgumentException('Select at least one question.');
    }

    if (! in_array($input['status'], ['draft', 'published', 'closed'], true)) {
        throw new InvalidArgumentException('Invalid quiz status.');
    }
}

function saveQuiz(array $input, ?string $override = null): array
{
    $normalized = normalizeQuizInput($input);
    validateQuizInput($normalized);

    $file = quizzesFile($override);
    $quizzes = loadRecords($file);
    $quiz = [
        'id' => nextId($quizzes),
        ...$normalized,
    ];
    $quizzes[] = $quiz;
    saveRecords($file, $quizzes);

    return $quiz;
}

function updateQuizStatus(int $id, string $status, ?string $override = null): array
{
    if (! in_array($status, ['draft', 'published', 'closed'], true)) {
        throw new InvalidArgumentException('Invalid quiz status.');
    }

    $file = quizzesFile($override);
    $quizzes = loadRecords($file);
    foreach ($quizzes as &$quiz) {
        if ((int) $quiz['id'] === $id) {
            $quiz['status'] = $status;
            saveRecords($file, $quizzes);
            return $quiz;
        }
    }

    throw new RuntimeException('Quiz not found.');
}

function deleteQuiz(int $id, ?string $override = null): void
{
    $file = quizzesFile($override);
    $quizzes = array_values(array_filter(loadRecords($file), static fn(array $quiz): bool => (int) $quiz['id'] !== $id));
    saveRecords($file, $quizzes);
}

function listQuizzesForGroup(int $groupId, ?string $override = null): array
{
    return array_values(array_filter(
        loadRecords(quizzesFile($override)),
        static fn(array $quiz): bool => ($quiz['status'] ?? '') === 'published' && in_array($groupId, $quiz['assigned_group_ids'] ?? [], true)
    ));
}

function getQuizById(int $quizId, ?string $override = null): ?array
{
    return findRecordById(loadRecords(quizzesFile($override)), $quizId);
}

function summarizeQuizCard(array $quiz): string
{
    $questionCount = count($quiz['question_ids'] ?? []);
    $groupCount = count($quiz['assigned_group_ids'] ?? []);

    return sprintf(
        '%d questions · %d groups · %d minutes',
        $questionCount,
        $groupCount,
        (int) ($quiz['time_limit_minutes'] ?? 0)
    );
}
