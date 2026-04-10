<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/auth.php';
require_once __DIR__ . '/../../src/submissions.php';

$user = requireRole('student');
$history = listSubmissionsForStudent((int) $user['id']);
$quizzesById = [];
foreach (loadRecords(DATA_DIR . '/quizzes.json') as $quiz) {
    $quizzesById[(int) $quiz['id']] = $quiz;
}

renderPageStart('Quiz History', $user);
?>
<section class="card">
    <table>
        <thead><tr><th>Quiz</th><th>Score</th><th>Submitted At</th><th>View</th></tr></thead>
        <tbody>
        <?php foreach ($history as $submission): ?>
            <tr>
                <td><?= h($quizzesById[(int) $submission['quiz_id']]['title'] ?? ('Quiz #' . $submission['quiz_id'])) ?></td>
                <td><?= (int) $submission['score'] ?></td>
                <td><?= h($submission['submitted_at']) ?></td>
                <td><a href="/student/result.php?id=<?= (int) $submission['id'] ?>">Open</a></td>
            </tr>
        <?php endforeach; ?>
        <?php if ($history === []): ?>
            <tr><td colspan="4">No submissions yet.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</section>
<?php renderPageEnd(); ?>
