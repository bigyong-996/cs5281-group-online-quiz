<?php
declare(strict_types=1);

require_once __DIR__ . '/storage.php';

function submissionsFile(?string $override = null): string
{
    return $override ?? DATA_DIR . '/submissions.json';
}

function scoreSubmission(array $quiz, array $questionsById, array $answers): array
{
    $details = [];
    $correctCount = 0;

    foreach ($quiz['question_ids'] as $questionId) {
        $questionId = (int) $questionId;
        if (! isset($questionsById[$questionId])) {
            continue;
        }

        $question = $questionsById[$questionId];
        $studentAnswer = isset($answers[$questionId]) ? trim((string) $answers[$questionId]) : null;
        $isCorrect = $studentAnswer !== null && $studentAnswer === $question['correct_answer'];
        $details[$questionId] = [
            'question_text' => $question['question_text'] ?? '',
            'student_answer' => $studentAnswer,
            'correct_answer' => $question['correct_answer'],
            'is_correct' => $isCorrect,
        ];

        if ($isCorrect) {
            $correctCount++;
        }
    }

    $total = max(count($quiz['question_ids']), 1);

    return [
        'score' => (int) round(($correctCount / $total) * 100),
        'correct_count' => $correctCount,
        'total_count' => $total,
        'details' => $details,
    ];
}

function hasStudentSubmitted(int $quizId, int $studentId, ?string $override = null): bool
{
    foreach (loadRecords(submissionsFile($override)) as $submission) {
        if ((int) $submission['quiz_id'] === $quizId && (int) $submission['student_id'] === $studentId) {
            return true;
        }
    }

    return false;
}

function saveSubmissionRecord(array $input, ?string $override = null): array
{
    if (hasStudentSubmitted((int) $input['quiz_id'], (int) $input['student_id'], $override)) {
        throw new RuntimeException('This student has already submitted the quiz.');
    }

    $file = submissionsFile($override);
    $submissions = loadRecords($file);
    $record = [
        'id' => nextId($submissions),
        'quiz_id' => (int) $input['quiz_id'],
        'student_id' => (int) $input['student_id'],
        'answers' => $input['answers'],
        'details' => $input['details'],
        'score' => (int) $input['score'],
        'correct_count' => isset($input['correct_count']) ? (int) $input['correct_count'] : null,
        'total_count' => isset($input['total_count']) ? (int) $input['total_count'] : null,
        'submitted_at' => $input['submitted_at'] ?? date('c'),
    ];
    $submissions[] = $record;
    saveRecords($file, $submissions);

    return $record;
}

function listSubmissionsForStudent(int $studentId, ?string $override = null): array
{
    return array_values(array_filter(
        loadRecords(submissionsFile($override)),
        static fn(array $submission): bool => (int) $submission['student_id'] === $studentId
    ));
}

function listSubmissionsForQuiz(int $quizId, ?string $override = null): array
{
    return array_values(array_filter(
        loadRecords(submissionsFile($override)),
        static fn(array $submission): bool => (int) $submission['quiz_id'] === $quizId
    ));
}
