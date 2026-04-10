<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/auth.php';

$user = requireRole('instructor');
$quizzes = array_values(array_filter(loadRecords(DATA_DIR . '/quizzes.json'), static fn(array $quiz): bool => ($quiz['status'] ?? '') !== 'draft'));

renderPageStart('Quiz Results', $user);
?>
<section class="card">
    <label>Select Quiz
        <select id="results-quiz-select" data-results-endpoint="/instructor/results_data.php">
            <option value="">Choose a quiz</option>
            <?php foreach ($quizzes as $quiz): ?>
                <option value="<?= (int) $quiz['id'] ?>"><?= h($quiz['title']) ?> (<?= h($quiz['status']) ?>)</option>
            <?php endforeach; ?>
        </select>
    </label>
    <p><a id="results-export-link" class="button button-secondary" href="#">Export Current Quiz as CSV</a></p>
</section>
<section class="card" id="results-summary">
    <p>Select a quiz to load statistics.</p>
</section>
<?php renderPageEnd(); ?>
