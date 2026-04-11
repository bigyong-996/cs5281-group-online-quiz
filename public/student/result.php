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
<section class="card hero-card">
    <p class="eyebrow eyebrow-dark">Submission Summary</p>
    <h2>Your score is <?= (int) $submission['score'] ?></h2>
    <p class="muted">You answered <?= (int) ($submission['correct_count'] ?? 0) ?> out of <?= (int) ($submission['total_count'] ?? count($submission['details'])) ?> questions correctly.</p>
</section>
<section class="card">
    <?php renderSectionHeader('Answer Breakdown', 'Compare your answer with the saved correct answer'); ?>
    <table class="data-table">
        <thead><tr><th>Question</th><th>Your Answer</th><th>Correct Answer</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($submission['details'] as $detail): ?>
            <tr>
                <td><?= h($detail['question_text'] ?? '') ?></td>
                <td><?= h($detail['student_answer'] ?? '') ?></td>
                <td><?= h($detail['correct_answer']) ?></td>
                <td><span class="pill <?= $detail['is_correct'] ? 'pill-success' : 'pill-danger' ?>"><?= $detail['is_correct'] ? 'Correct' : 'Incorrect' ?></span></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php renderPageEnd(); ?>
