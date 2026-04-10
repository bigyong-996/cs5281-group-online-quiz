<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/auth.php';

$user = requireRole('student');
$submissionId = (int) ($_GET['id'] ?? 0);
$submission = findRecordById(loadRecords(DATA_DIR . '/submissions.json'), $submissionId);

if ($submission === null || (int) $submission['student_id'] !== (int) $user['id']) {
    flashMessage('error', 'Result not found.');
    redirectTo('/student/history.php');
}

renderPageStart('Quiz Result', $user);
?>
<section class="card">
    <p class="stat"><?= (int) $submission['score'] ?></p>
    <p>Score</p>
</section>
<section class="card">
    <h2>Answer Breakdown</h2>
    <table>
        <thead><tr><th>Question</th><th>Your Answer</th><th>Correct Answer</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($submission['details'] as $detail): ?>
            <tr>
                <td><?= h($detail['question_text'] ?? '') ?></td>
                <td><?= h($detail['student_answer'] ?? '') ?></td>
                <td><?= h($detail['correct_answer']) ?></td>
                <td><?= $detail['is_correct'] ? 'Correct' : 'Incorrect' ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php renderPageEnd(); ?>
