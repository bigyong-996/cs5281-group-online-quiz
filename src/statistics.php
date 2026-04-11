<?php
declare(strict_types=1);

function summarizeQuizStatistics(array $quiz, array $questionsById, array $submissions): array
{
    $quizSubmissions = array_values(array_filter(
        $submissions,
        static fn(array $submission): bool => (int) $submission['quiz_id'] === (int) $quiz['id']
    ));

    $averageScore = $quizSubmissions === []
        ? 0
        : (int) round(array_sum(array_column($quizSubmissions, 'score')) / count($quizSubmissions));

    $perQuestionAccuracy = [];
    foreach ($quiz['question_ids'] as $questionId) {
        $questionId = (int) $questionId;
        $correctCount = 0;
        foreach ($quizSubmissions as $submission) {
            if (($submission['details'][$questionId]['is_correct'] ?? false) === true) {
                $correctCount++;
            }
        }

        $perQuestionAccuracy[$questionId] = [
            'question_text' => $questionsById[$questionId]['question_text'] ?? ('Question ' . $questionId),
            'accuracy' => $quizSubmissions === [] ? 0 : (int) round(($correctCount / count($quizSubmissions)) * 100),
        ];
    }

    return [
        'quiz_id' => (int) $quiz['id'],
        'quiz_title' => $quiz['title'],
        'average_score' => $averageScore,
        'submission_count' => count($quizSubmissions),
        'high_score' => $quizSubmissions === [] ? 0 : max(array_column($quizSubmissions, 'score')),
        'per_question_accuracy' => $perQuestionAccuracy,
    ];
}
