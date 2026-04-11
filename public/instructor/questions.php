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
        $questions = loadRecords(DATA_DIR . '/questions.json');
        $editing = ($_POST['question_id'] ?? '') !== '' ? findRecordById($questions, (int) $_POST['question_id']) : null;
        $questionImagePath = trim((string) ($_POST['existing_question_image_path'] ?? ($editing['question_image_path'] ?? '')));

        if (isset($_FILES['question_image']) && ($_FILES['question_image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $questionImagePath = storeQuestionImage($_FILES['question_image'], QUESTION_UPLOAD_DIR);
        }

        $input = [
            'question_text' => $_POST['question_text'] ?? '',
            'question_content_html' => $_POST['question_content_html'] ?? '',
            'question_image_path' => $questionImagePath,
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
    <?php renderSectionHeader($editing === null ? 'Add MCQ Question' : 'Edit MCQ Question', 'Build a richer prompt with formatted content and one question image'); ?>
    <form method="post" enctype="multipart/form-data" class="question-form">
        <input type="hidden" name="question_id" value="<?= h($editing['id'] ?? '') ?>">
        <input type="hidden" name="existing_question_image_path" value="<?= h($editing['question_image_path'] ?? '') ?>">
        <div class="form-columns">
            <div class="stack-md">
                <label>Question Title <textarea name="question_text" required><?= h($editing['question_text'] ?? '') ?></textarea></label>
                <label>Formatted Question Content
                    <textarea
                        name="question_content_html"
                        data-rich-content-input
                        data-preview-image-input="#question-image-input"
                        data-preview-body="#question-preview-body"
                        data-preview-image="#question-preview-image"
                        data-existing-image="<?= h($editing['question_image_path'] ?? '') ?>"
                    ><?= h($editing['question_content_html'] ?? '') ?></textarea>
                </label>
                <label>Question Image
                    <input id="question-image-input" type="file" name="question_image" accept=".png,.jpg,.jpeg,.gif,.webp">
                </label>
                <label>Topic <input name="topic" value="<?= h($editing['topic'] ?? '') ?>"></label>
                <div class="option-grid">
                    <?php for ($i = 0; $i < 4; $i++): ?>
                        <label>Option <?= $i + 1 ?> <input name="option_<?= $i + 1 ?>" value="<?= h($editingOptions[$i] ?? '') ?>" required></label>
                    <?php endfor; ?>
                </div>
                <label>Correct Answer
                    <select name="correct_option_index" required>
                        <?php for ($i = 0; $i < 4; $i++): ?>
                            <option value="<?= $i ?>" <?= $i === (int) $correctIndex ? 'selected' : '' ?>>Option <?= $i + 1 ?></option>
                        <?php endfor; ?>
                    </select>
                </label>
                <button type="submit"><?= $editing === null ? 'Save Question' : 'Update Question' ?></button>
            </div>
            <div class="preview-panel">
                <h3>Question Preview</h3>
                <p class="muted">The preview mirrors the saved question body and image before submission.</p>
                <div id="question-preview-body" class="question-preview-body">
                    <?= questionContentHtml($editing ?? ['question_text' => '']) ?>
                </div>
                <img
                    id="question-preview-image"
                    class="question-preview-image <?= ($editing['question_image_path'] ?? '') === '' ? 'is-hidden' : '' ?>"
                    src="<?= h($editing['question_image_path'] ?? '') ?>"
                    alt="Question preview image"
                >
            </div>
        </div>
    </form>
</section>
<section class="card">
    <?php renderSectionHeader('Questions', 'Existing items keep Phase 1 compatibility while exposing richer summaries'); ?>
    <table class="data-table">
        <thead><tr><th>ID</th><th>Question</th><th>Topic</th><th>Correct Answer</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($questions as $question): ?>
            <tr>
                <td><?= (int) $question['id'] ?></td>
                <td>
                    <div class="question-listing">
                        <strong><?= h($question['question_text']) ?></strong>
                        <p class="muted"><?= h(questionPlainSummary($question)) ?></p>
                        <?php if (($question['question_image_path'] ?? '') !== ''): ?>
                            <span class="pill">Image attached</span>
                        <?php endif; ?>
                    </div>
                </td>
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
