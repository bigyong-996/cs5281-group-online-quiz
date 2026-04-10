<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/auth.php';
require_once __DIR__ . '/../../src/quizzes.php';
require_once __DIR__ . '/../../src/submissions.php';

$user = requireRole('student');
$availableQuizzes = $user['group_id'] === null ? [] : listQuizzesForGroup((int) $user['group_id']);
renderPageStart('Student Dashboard', $user);
?>
<section class="card">
    <p>Welcome, <?= h($user['display_name']) ?>.</p>
    <p class="muted">Choose an assigned quiz to begin. Each quiz can be submitted once.</p>
</section>
<section class="card">
    <h2>Available Quizzes</h2>
    <table>
        <thead><tr><th>Quiz</th><th>Description</th><th>Time Limit</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
        <?php foreach ($availableQuizzes as $quiz): ?>
            <?php $submitted = hasStudentSubmitted((int) $quiz['id'], (int) $user['id']); ?>
            <tr>
                <td><?= h($quiz['title']) ?></td>
                <td><?= h($quiz['description']) ?></td>
                <td><?= (int) $quiz['time_limit_minutes'] ?> minutes</td>
                <td><?= $submitted ? 'Submitted' : 'Open' ?></td>
                <td>
                    <?php if ($submitted): ?>
                        <a href="/student/history.php">View History</a>
                    <?php else: ?>
                        <a class="button" href="/student/quiz.php?id=<?= (int) $quiz['id'] ?>">Start Quiz</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if ($availableQuizzes === []): ?>
            <tr><td colspan="5">No quizzes are currently assigned to your group.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</section>
<?php renderPageEnd(); ?>
