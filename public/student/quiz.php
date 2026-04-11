<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/auth.php';
require_once __DIR__ . '/../../src/quizzes.php';
require_once __DIR__ . '/../../src/questions.php';
require_once __DIR__ . '/../../src/submissions.php';

$user = requireRole('student');
$quizId = (int) ($_GET['id'] ?? $_POST['quiz_id'] ?? 0);
$quiz = getQuizById($quizId);

if (
    $quiz === null
    || ($quiz['status'] ?? '') !== 'published'
    || $user['group_id'] === null
    || ! in_array((int) $user['group_id'], $quiz['assigned_group_ids'], true)
) {
    flashMessage('error', 'Quiz is not available.');
    redirectTo('/student/dashboard.php');
}

if (hasStudentSubmitted($quizId, (int) $user['id'])) {
    flashMessage('error', 'You have already submitted this quiz.');
    redirectTo('/student/history.php');
}

$questionsById = questionsById(loadRecords(DATA_DIR . '/questions.json'));

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $answers = $_POST['answers'] ?? [];
        $result = scoreSubmission($quiz, $questionsById, $answers);
        $submission = saveSubmissionRecord([
            'quiz_id' => $quizId,
            'student_id' => (int) $user['id'],
            'answers' => $answers,
            'details' => $result['details'],
            'score' => $result['score'],
            'correct_count' => $result['correct_count'],
            'total_count' => $result['total_count'],
        ]);
        redirectTo('/student/result.php?id=' . (int) $submission['id']);
    }
} catch (Throwable $error) {
    flashMessage('error', $error->getMessage());
    redirectTo('/student/dashboard.php');
}

renderPageStart('Take Quiz', $user);
?>
<section class="card hero-card">
    <p class="eyebrow eyebrow-dark">Quiz Session</p>
    <h2><?= h($quiz['title']) ?></h2>
    <p class="muted"><?= h($quiz['description']) ?></p>
</section>
<section class="quiz-shell">
    <aside class="card quiz-sidebar">
        <p id="quiz-timer" class="timer" data-timer-minutes="<?= (int) $quiz['time_limit_minutes'] ?>">Time remaining: --:--</p>
        <p id="quiz-progress" class="progress-pill" data-question-count="<?= count($quiz['question_ids']) ?>">0 / <?= count($quiz['question_ids']) ?> answered</p>
        <p class="muted">Review each question before submission. The timer will submit automatically when it reaches zero.</p>
        <button type="submit" form="student-quiz-form">Submit Quiz</button>
    </aside>
    <form id="student-quiz-form" method="post" class="quiz-form" data-quiz-form data-confirm-submit="You are about to submit this quiz. Continue?">
        <input type="hidden" name="quiz_id" value="<?= (int) $quiz['id'] ?>">
        <?php foreach ($quiz['question_ids'] as $index => $questionId): ?>
            <?php
            $question = $questionsById[(int) $questionId];
            $options = $question['options'];
            shuffle($options);
            ?>
            <fieldset class="card question-card" data-question>
                <legend>Question <?= $index + 1 ?></legend>
                <div class="question-body"><?= questionContentHtml($question) ?></div>
                <?php if (($question['question_image_path'] ?? '') !== ''): ?>
                    <img class="question-image" src="<?= h($question['question_image_path']) ?>" alt="Question illustration">
                <?php endif; ?>
                <div class="option-list">
                    <?php foreach ($options as $option): ?>
                        <label class="option-item"><input type="radio" name="answers[<?= (int) $questionId ?>]" value="<?= h($option) ?>"> <?= h($option) ?></label>
                    <?php endforeach; ?>
                </div>
            </fieldset>
        <?php endforeach; ?>
    </form>
</section>
<?php renderPageEnd(); ?>
