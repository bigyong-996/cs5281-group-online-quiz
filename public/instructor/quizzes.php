<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/auth.php';
require_once __DIR__ . '/../../src/quizzes.php';
require_once __DIR__ . '/../../src/questions.php';

$user = requireRole('instructor');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        saveQuiz([
            'title' => $_POST['title'] ?? '',
            'description' => $_POST['description'] ?? '',
            'time_limit_minutes' => $_POST['time_limit_minutes'] ?? 1,
            'assigned_group_ids' => $_POST['assigned_group_ids'] ?? [],
            'question_ids' => $_POST['question_ids'] ?? [],
            'status' => $_POST['status'] ?? 'draft',
        ]);
        flashMessage('success', 'Quiz saved.');
        redirectTo('/instructor/quizzes.php');
    }

    if (isset($_GET['status'], $_GET['id'])) {
        updateQuizStatus((int) $_GET['id'], (string) $_GET['status']);
        flashMessage('success', 'Quiz status updated.');
        redirectTo('/instructor/quizzes.php');
    }

    if (isset($_GET['delete'])) {
        deleteQuiz((int) $_GET['delete']);
        flashMessage('success', 'Quiz deleted.');
        redirectTo('/instructor/quizzes.php');
    }
} catch (Throwable $error) {
    flashMessage('error', $error->getMessage());
    redirectTo('/instructor/quizzes.php');
}

$groups = loadRecords(DATA_DIR . '/groups.json');
$questions = loadRecords(DATA_DIR . '/questions.json');
$quizzes = loadRecords(DATA_DIR . '/quizzes.json');

renderPageStart('Quiz Management', $user);
?>
<section class="card">
    <?php renderSectionHeader('Create Quiz', 'Build, review, and publish a richer quiz'); ?>
    <form method="post" class="quiz-builder-form" data-quiz-builder-form>
        <div class="form-columns">
            <div class="stack-md">
                <label>Quiz Title <input name="title" required></label>
                <label>Description <textarea name="description"></textarea></label>
                <label>Time Limit (minutes) <input name="time_limit_minutes" type="number" min="1" value="20" required></label>
                <label>Status
                    <select name="status">
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                        <option value="closed">Closed</option>
                    </select>
                </label>
                <fieldset>
                    <legend>Assign to Groups</legend>
                    <div class="checkbox-grid">
                        <?php foreach ($groups as $group): ?>
                            <label><input type="checkbox" name="assigned_group_ids[]" value="<?= (int) $group['id'] ?>"> <?= h($group['group_name']) ?></label>
                        <?php endforeach; ?>
                    </div>
                </fieldset>
                <fieldset>
                    <legend>Select Questions</legend>
                    <div class="checkbox-grid">
                        <?php foreach ($questions as $question): ?>
                            <label>
                                <input type="checkbox" name="question_ids[]" value="<?= (int) $question['id'] ?>">
                                <span><?= h($question['question_text']) ?></span>
                                <small class="muted"><?= h(questionPlainSummary($question)) ?></small>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </fieldset>
                <button type="submit">Save Quiz</button>
            </div>
            <aside class="preview-panel">
                <h3>Quiz Preview</h3>
                <p class="muted" id="quiz-live-summary" data-quiz-summary>0 questions selected.</p>
                <div class="preview-list" id="quiz-selected-questions"></div>
            </aside>
        </div>
    </form>
</section>
<section class="card">
    <?php renderSectionHeader('Quizzes', 'Review composition and state before publishing or closing'); ?>
    <table class="data-table">
        <thead><tr><th>Quiz</th><th>Status</th><th>Time Limit</th><th>Questions</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($quizzes as $quiz): ?>
            <tr>
                <td>
                    <strong><?= h($quiz['title']) ?></strong>
                    <p class="muted"><?= h(summarizeQuizCard($quiz)) ?></p>
                </td>
                <td><?= h($quiz['status']) ?></td>
                <td><?= (int) $quiz['time_limit_minutes'] ?> minutes</td>
                <td><?= count($quiz['question_ids']) ?></td>
                <td>
                    <a href="/instructor/quizzes.php?id=<?= (int) $quiz['id'] ?>&status=published">Publish</a>
                    |
                    <a href="/instructor/quizzes.php?id=<?= (int) $quiz['id'] ?>&status=closed">Close</a>
                    |
                    <a href="/instructor/quizzes.php?delete=<?= (int) $quiz['id'] ?>">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php renderPageEnd(); ?>
