<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/auth.php';
require_once __DIR__ . '/../../src/questions.php';

$user = requireRole('instructor');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $options = [
            trim((string) $_POST['option_1']),
            trim((string) $_POST['option_2']),
            trim((string) $_POST['option_3']),
            trim((string) $_POST['option_4']),
        ];
        $correctAnswer = $options[(int) ($_POST['correct_option_index'] ?? -1)] ?? '';
        $input = [
            'question_text' => $_POST['question_text'] ?? '',
            'topic' => $_POST['topic'] ?? '',
            'options' => $options,
            'correct_answer' => $correctAnswer,
        ];

        if (($_POST['question_id'] ?? '') !== '') {
            updateQuestion((int) $_POST['question_id'], $input);
            flashMessage('success', 'Question updated.');
        } else {
            saveQuestion($input);
            flashMessage('success', 'Question saved.');
        }

        redirectTo('/instructor/questions.php');
    }

    if (isset($_GET['delete'])) {
        deleteQuestion((int) $_GET['delete']);
        flashMessage('success', 'Question deleted.');
        redirectTo('/instructor/questions.php');
    }
} catch (Throwable $error) {
    flashMessage('error', $error->getMessage());
    redirectTo('/instructor/questions.php');
}

$questions = loadRecords(DATA_DIR . '/questions.json');
$editing = isset($_GET['edit']) ? findRecordById($questions, (int) $_GET['edit']) : null;
$editingOptions = $editing['options'] ?? ['', '', '', ''];
$correctIndex = $editing === null ? 0 : max(0, array_search($editing['correct_answer'], $editingOptions, true));

renderPageStart('Question Bank', $user);
?>
<section class="card">
    <h2><?= $editing === null ? 'Add MCQ Question' : 'Edit MCQ Question' ?></h2>
    <form method="post">
        <input type="hidden" name="question_id" value="<?= h($editing['id'] ?? '') ?>">
        <label>Question <textarea name="question_text" required><?= h($editing['question_text'] ?? '') ?></textarea></label>
        <label>Topic <input name="topic" value="<?= h($editing['topic'] ?? '') ?>"></label>
        <?php for ($i = 0; $i < 4; $i++): ?>
            <label>Option <?= $i + 1 ?> <input name="option_<?= $i + 1 ?>" value="<?= h($editingOptions[$i] ?? '') ?>" required></label>
        <?php endfor; ?>
        <label>Correct Answer
            <select name="correct_option_index" required>
                <?php for ($i = 0; $i < 4; $i++): ?>
                    <option value="<?= $i ?>" <?= $i === (int) $correctIndex ? 'selected' : '' ?>>Option <?= $i + 1 ?></option>
                <?php endfor; ?>
            </select>
        </label>
        <button type="submit"><?= $editing === null ? 'Save Question' : 'Update Question' ?></button>
    </form>
</section>
<section class="card">
    <h2>Questions</h2>
    <table>
        <thead><tr><th>ID</th><th>Question</th><th>Topic</th><th>Correct Answer</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($questions as $question): ?>
            <tr>
                <td><?= (int) $question['id'] ?></td>
                <td><?= h($question['question_text']) ?></td>
                <td><?= h($question['topic']) ?></td>
                <td><?= h($question['correct_answer']) ?></td>
                <td>
                    <a href="/instructor/questions.php?edit=<?= (int) $question['id'] ?>">Edit</a>
                    |
                    <a href="/instructor/questions.php?delete=<?= (int) $question['id'] ?>">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php renderPageEnd(); ?>
