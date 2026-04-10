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
        ]);
        redirectTo('/student/result.php?id=' . (int) $submission['id']);
    }
} catch (Throwable $error) {
    flashMessage('error', $error->getMessage());
    redirectTo('/student/dashboard.php');
}

renderPageStart('Take Quiz', $user);
?>
<section class="card">
    <h2><?= h($quiz['title']) ?></h2>
    <p><?= h($quiz['description']) ?></p>
    <p id="quiz-timer" class="timer" data-timer-minutes="<?= (int) $quiz['time_limit_minutes'] ?>">Time remaining: --:--</p>
    <form method="post" data-quiz-form>
        <input type="hidden" name="quiz_id" value="<?= (int) $quiz['id'] ?>">
        <?php foreach ($quiz['question_ids'] as $questionId): ?>
            <?php
            $question = $questionsById[(int) $questionId];
            $options = $question['options'];
            shuffle($options);
            ?>
            <fieldset class="card" data-question>
                <legend><?= h($question['question_text']) ?></legend>
                <?php foreach ($options as $option): ?>
                    <label><input type="radio" name="answers[<?= (int) $questionId ?>]" value="<?= h($option) ?>" required> <?= h($option) ?></label>
                <?php endforeach; ?>
            </fieldset>
        <?php endforeach; ?>
        <button type="submit">Submit Quiz</button>
    </form>
</section>
<?php renderPageEnd(); ?>
