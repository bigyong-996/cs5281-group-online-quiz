# Online Quiz System Phase 2 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Upgrade the Phase 1 quiz system with a redesigned UI, lightweight rich question content with image upload, stronger quiz authoring, better student answering flow, and a more polished analytics experience.

**Architecture:** Keep the current PHP page architecture and JSON file storage, then layer Phase 2 enhancements on top of it. Add a small rich-content and upload layer under `src/`, evolve the existing pages rather than replacing them, and use vanilla JavaScript for preview, submission confirmation, and AJAX improvements.

**Tech Stack:** PHP 8.5+, HTML5, CSS3, vanilla JavaScript, JSON file storage, local image uploads, PHP CLI tests

---

## File Structure

### Modify existing shared logic

- Modify: `src/bootstrap.php`  
  Register the upload directory and load the new rich-content helpers.
- Modify: `src/layout.php`  
  Expand layout helpers to support stronger page chrome, section headers, and dashboard cards.
- Modify: `src/questions.php`  
  Extend the question model with rich-content fields while preserving Phase 1 compatibility.
- Modify: `src/quizzes.php`  
  Add quiz summary helpers for authoring previews and management cards.
- Modify: `src/submissions.php`  
  Add quiz progress metadata and submission confirmation support where needed.
- Modify: `src/statistics.php`  
  Produce richer result summaries for dashboard-style rendering.

### Create new shared helpers

- Create: `src/rich_content.php`  
  Sanitize and normalize lightweight formatted question content.
- Create: `src/uploads.php`  
  Validate and store question images under `public/uploads/questions/`.

### Modify frontend assets

- Modify: `public/assets/styles.css`  
  Replace the Phase 1 visual layer with a fuller design system.
- Modify: `public/assets/app.js`  
  Add question-content preview, quiz submit confirmation, and improved AJAX rendering.

### Modify instructor pages

- Modify: `public/instructor/dashboard.php`
- Modify: `public/instructor/questions.php`
- Modify: `public/instructor/quizzes.php`
- Modify: `public/instructor/results.php`
- Modify: `public/instructor/results_data.php`

### Modify student pages

- Modify: `public/student/dashboard.php`
- Modify: `public/student/quiz.php`
- Modify: `public/student/result.php`
- Modify: `public/student/history.php`

### Create upload directories

- Create: `public/uploads/.gitkeep`
- Create: `public/uploads/questions/.gitkeep`

### Add or expand tests

- Create: `tests/layout_smoke_test.php`
- Create: `tests/rich_content_test.php`
- Create: `tests/uploads_test.php`
- Modify: `tests/questions_test.php`
- Modify: `tests/quizzes_test.php`
- Modify: `tests/submissions_test.php`
- Modify: `tests/statistics_test.php`

## Implementation Notes

- Keep backward compatibility for old question records by falling back from `question_content_html` to `question_text`.
- Rich content is limited to the question body and must be sanitized through a strict allowlist.
- Support one uploaded image per question in Phase 2 core scope.
- Store uploaded images under `public/uploads/questions/` and save relative paths such as `/uploads/questions/<filename>.png`.
- Keep the existing JSON storage approach; no database migration is introduced.
- Use manual smoke verification for image upload and preview because the CLI tests will only cover normalization and file-handling helpers.

## Task 1: Add Rich Content and Upload Foundations

**Files:**
- Modify: `src/bootstrap.php`
- Create: `src/rich_content.php`
- Create: `src/uploads.php`
- Create: `public/uploads/.gitkeep`
- Create: `public/uploads/questions/.gitkeep`
- Test: `tests/rich_content_test.php`
- Test: `tests/uploads_test.php`

- [ ] **Step 1: Write the failing rich-content and upload tests**

```php
// tests/rich_content_test.php
<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../src/rich_content.php';

$raw = '<p>Hello <strong>Quiz</strong></p><script>alert(1)</script><ul><li>One</li></ul>';
$sanitized = sanitizeQuestionContent($raw);

assertTrueValue(str_contains($sanitized, '<strong>Quiz</strong>'), 'Allowed tags should remain.');
assertTrueValue(! str_contains($sanitized, '<script>'), 'Scripts must be stripped.');
assertSameValue('<p>Fallback</p>', normalizeQuestionContent('', 'Fallback'), 'Empty rich content should fall back to plain question text.');

echo "OK rich_content_test\n";
```

```php
// tests/uploads_test.php
<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../src/uploads.php';

$tempDir = makeTempDir('uploads');
$sourceImage = $tempDir . '/sample.png';
file_put_contents($sourceImage, 'fake-image-data');

$storedPath = storeQuestionImage([
    'tmp_name' => $sourceImage,
    'name' => 'diagram.png',
    'error' => UPLOAD_ERR_OK,
], $tempDir . '/uploads/questions');

assertTrueValue(str_starts_with($storedPath, '/uploads/questions/'), 'Stored image path should use the public uploads prefix.');
assertTrueValue(file_exists($tempDir . $storedPath), 'The stored image should exist in the target upload directory.');

echo "OK uploads_test\n";
```

- [ ] **Step 2: Run the new tests to verify they fail**

Run: `php tests/rich_content_test.php`  
Expected: FAIL because `src/rich_content.php` does not exist yet.

Run: `php tests/uploads_test.php`  
Expected: FAIL because `src/uploads.php` does not exist yet.

- [ ] **Step 3: Implement the rich-content sanitizer, upload helper, and bootstrap wiring**

```php
// src/rich_content.php
<?php
declare(strict_types=1);

function sanitizeQuestionContent(string $rawHtml): string
{
    $allowedTags = '<p><br><strong><em><ul><ol><li>';
    $sanitized = strip_tags($rawHtml, $allowedTags);
    $sanitized = preg_replace('/\s+on\w+="[^"]*"/i', '', $sanitized) ?? '';
    $sanitized = preg_replace('/\s+style="[^"]*"/i', '', $sanitized) ?? '';

    return trim($sanitized);
}

function normalizeQuestionContent(string $rawHtml, string $fallbackText): string
{
    $sanitized = sanitizeQuestionContent($rawHtml);
    if ($sanitized !== '') {
        return $sanitized;
    }

    $escapedFallback = htmlspecialchars($fallbackText, ENT_QUOTES, 'UTF-8');
    return $escapedFallback === '' ? '' : '<p>' . $escapedFallback . '</p>';
}
```

```php
// src/uploads.php
<?php
declare(strict_types=1);

function ensureUploadDirectory(string $absolutePath): void
{
    if (! is_dir($absolutePath)) {
        mkdir($absolutePath, 0777, true);
    }
}

function storeQuestionImage(array $file, string $absoluteUploadDir): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Image upload failed.');
    }

    $originalName = (string) ($file['name'] ?? 'question-image');
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    if (! in_array($extension, ['png', 'jpg', 'jpeg', 'gif', 'webp'], true)) {
        throw new InvalidArgumentException('Unsupported image type.');
    }

    ensureUploadDirectory($absoluteUploadDir);
    $targetName = uniqid('question_', true) . '.' . $extension;
    $targetPath = rtrim($absoluteUploadDir, '/') . '/' . $targetName;

    if (! @copy((string) $file['tmp_name'], $targetPath)) {
        throw new RuntimeException('Unable to store uploaded image.');
    }

    return '/uploads/questions/' . $targetName;
}
```

```php
// src/bootstrap.php additions
define('PUBLIC_DIR', PROJECT_ROOT . '/public');
define('QUESTION_UPLOAD_DIR', PUBLIC_DIR . '/uploads/questions');

require_once SRC_DIR . '/rich_content.php';
require_once SRC_DIR . '/uploads.php';

ensureUploadDirectory(PUBLIC_DIR . '/uploads');
ensureUploadDirectory(QUESTION_UPLOAD_DIR);
```

- [ ] **Step 4: Run the tests and syntax checks**

Run: `php tests/rich_content_test.php`  
Expected: `OK rich_content_test`

Run: `php tests/uploads_test.php`  
Expected: `OK uploads_test`

Run: `php -l src/rich_content.php`  
Expected: `No syntax errors detected in src/rich_content.php`

Run: `php -l src/uploads.php`  
Expected: `No syntax errors detected in src/uploads.php`

- [ ] **Step 5: Commit the Phase 2 foundations**

```bash
git add src/bootstrap.php src/rich_content.php src/uploads.php public/uploads tests/rich_content_test.php tests/uploads_test.php
git commit -m "feat: add phase 2 rich content foundations"
```

## Task 2: Redesign the Shared Visual System

**Files:**
- Modify: `src/layout.php`
- Modify: `public/assets/styles.css`
- Modify: `public/index.php`
- Modify: `public/instructor/dashboard.php`
- Modify: `public/student/dashboard.php`

- [ ] **Step 1: Add a layout regression smoke test**

```php
// tests/layout_smoke_test.php
<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/storage.php';
require_once __DIR__ . '/../src/layout.php';

ob_start();
renderPageStart('Sample Page', ['role' => 'instructor', 'display_name' => 'Teacher']);
renderPageEnd();
$html = ob_get_clean();

assertTrueValue(str_contains($html, 'Sample Page'), 'Rendered page should contain the page title.');
assertTrueValue(str_contains($html, 'nav-links'), 'Rendered page should include the shared navigation.');

echo "OK layout_smoke_test\n";
```

- [ ] **Step 2: Run the layout smoke test to verify it fails**

Run: `php tests/layout_smoke_test.php`  
Expected: FAIL because `assertTrueValue` is not available until the test includes the shared test bootstrap.

- [ ] **Step 3: Fix the smoke test and implement the redesign foundation**

```php
// tests/layout_smoke_test.php corrected header
<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../src/layout.php';
```

```php
// src/layout.php additions
function renderSectionHeader(string $title, string $subtitle = ''): void
{
    echo '<div class="section-header">';
    echo '<div><h2>' . h($title) . '</h2>';
    if ($subtitle !== '') {
        echo '<p class="muted">' . h($subtitle) . '</p>';
    }
    echo '</div></div>';
}

function renderStatCard(string $label, string|int $value): void
{
    echo '<article class="stat-card">';
    echo '<p class="stat-card-value">' . h($value) . '</p>';
    echo '<p class="stat-card-label">' . h($label) . '</p>';
    echo '</article>';
}
```

```css
/* public/assets/styles.css key additions */
body {
  background:
    linear-gradient(180deg, #eff6ff 0%, #f8fafc 180px, #f4f7fb 180px, #f4f7fb 100%);
}

.topbar {
  padding: 22px 28px;
  background: linear-gradient(120deg, #0f172a, #1d4ed8 58%, #0891b2);
}

.hero-card,
.card,
.stat-card {
  background: rgba(255, 255, 255, 0.96);
  border: 1px solid rgba(148, 163, 184, 0.22);
  box-shadow: 0 14px 32px rgba(15, 23, 42, 0.08);
}

.page-shell {
  width: min(1160px, calc(100% - 32px));
  margin: 28px auto 48px;
}

.section-header {
  display: flex;
  align-items: end;
  justify-content: space-between;
  margin-bottom: 14px;
}

.stat-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 16px;
}

.stat-card {
  border-radius: 8px;
  padding: 18px;
}

.stat-card-value {
  margin: 0;
  font-size: 34px;
  font-weight: 700;
}

.stat-card-label {
  margin: 6px 0 0;
  color: var(--muted);
}
```

```php
// public/instructor/dashboard.php structure change
renderPageStart('Instructor Dashboard', $user);
?>
<section class="hero-card card">
    <p class="eyebrow">Phase 2 Instructor Workspace</p>
    <h2>Manage quizzes, question content, and class performance in one place.</h2>
    <p class="muted">Use the sections below to import students, build richer question content, publish quizzes, and review analytics.</p>
</section>
<section>
    <?php renderSectionHeader('Overview', 'Current system activity'); ?>
    <div class="stat-grid">
        <?php renderStatCard('Students', count($students)); ?>
        <?php renderStatCard('Groups', count($groups)); ?>
        <?php renderStatCard('Questions', count($questions)); ?>
        <?php renderStatCard('Quizzes', count($quizzes)); ?>
        <?php renderStatCard('Submissions', count($submissions)); ?>
    </div>
</section>
<?php
```

```php
// public/student/dashboard.php structure change
renderPageStart('Student Dashboard', $user);
?>
<section class="hero-card card">
    <p class="eyebrow">Student Workspace</p>
    <h2>Track active quizzes and review your finished attempts.</h2>
    <p class="muted">Open assigned quizzes from the list below and use history to review completed work.</p>
</section>
<?php
```

- [ ] **Step 4: Run the smoke test and syntax checks**

Run: `php tests/layout_smoke_test.php`  
Expected: `OK layout_smoke_test`

Run: `php -l src/layout.php`  
Expected: `No syntax errors detected in src/layout.php`

Run: `php -l public/instructor/dashboard.php`  
Expected: `No syntax errors detected in public/instructor/dashboard.php`

- [ ] **Step 5: Commit the visual redesign foundation**

```bash
git add src/layout.php public/assets/styles.css public/index.php public/instructor/dashboard.php public/student/dashboard.php tests/layout_smoke_test.php
git commit -m "feat: add phase 2 visual system foundation"
```

## Task 3: Upgrade the Question Bank for Rich Content and Image Preview

**Files:**
- Modify: `src/questions.php`
- Modify: `public/instructor/questions.php`
- Modify: `tests/questions_test.php`

- [ ] **Step 1: Extend the failing question test for Phase 2 fields**

```php
// tests/questions_test.php additions
$question = saveQuestion([
    'question_text' => 'What does the chart show?',
    'question_content_html' => '<p><strong>Observe the chart</strong> and answer the question.</p>',
    'question_image_path' => '/uploads/questions/example.png',
    'topic' => 'Analytics',
    'options' => ['A', 'B', 'C', 'D'],
    'correct_answer' => 'A',
], $questionsFile);

assertSameValue('/uploads/questions/example.png', $question['question_image_path'], 'Question image path should persist.');
assertTrueValue(str_contains($question['question_content_html'], '<strong>Observe the chart</strong>'), 'Rich content should be stored.');
```

- [ ] **Step 2: Run the question test to confirm it fails**

Run: `php tests/questions_test.php`  
Expected: FAIL because `saveQuestion()` does not preserve the new rich-content fields yet.

- [ ] **Step 3: Implement backward-compatible rich question storage and authoring UI**

```php
// src/questions.php changes
function normalizeQuestionInput(array $input): array
{
    $questionText = trim((string) ($input['question_text'] ?? ''));
    $questionContent = normalizeQuestionContent((string) ($input['question_content_html'] ?? ''), $questionText);

    return [
        'question_text' => $questionText,
        'question_content_html' => $questionContent,
        'question_image_path' => trim((string) ($input['question_image_path'] ?? '')),
        'topic' => trim((string) ($input['topic'] ?? '')),
        'options' => array_map('trim', $input['options'] ?? []),
        'correct_answer' => trim((string) ($input['correct_answer'] ?? '')),
    ];
}
```

```php
// public/instructor/questions.php POST handling core
$questionImagePath = $editing['question_image_path'] ?? '';
if (isset($_FILES['question_image']) && $_FILES['question_image']['error'] !== UPLOAD_ERR_NO_FILE) {
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
```

```php
// public/instructor/questions.php form additions
<label>Formatted Question Content
    <textarea name="question_content_html" data-rich-content-input><?= h($editing['question_content_html'] ?? '') ?></textarea>
</label>
<label>Question Image
    <input type="file" name="question_image" accept=".png,.jpg,.jpeg,.gif,.webp">
</label>
<section class="card">
    <?php renderSectionHeader('Question Preview', 'Formatted text and question image preview'); ?>
    <div id="question-preview" class="question-preview" data-preview-target></div>
</section>
```

- [ ] **Step 4: Run the question test and syntax checks**

Run: `php tests/questions_test.php`  
Expected: `OK questions_test`

Run: `php -l public/instructor/questions.php`  
Expected: `No syntax errors detected in public/instructor/questions.php`

- [ ] **Step 5: Commit the rich question authoring upgrade**

```bash
git add src/questions.php public/instructor/questions.php tests/questions_test.php
git commit -m "feat: add rich question content authoring"
```

## Task 4: Upgrade Quiz Management and Student Answering UX

**Files:**
- Modify: `src/quizzes.php`
- Modify: `public/instructor/quizzes.php`
- Modify: `public/student/quiz.php`
- Modify: `public/assets/app.js`
- Modify: `tests/quizzes_test.php`
- Modify: `tests/submissions_test.php`

- [ ] **Step 1: Extend quiz and submission tests**

```php
// tests/quizzes_test.php additions
$summary = summarizeQuizCard($published);
assertTrueValue(str_contains($summary, '2 questions'), 'Quiz summary should mention the number of questions.');
```

```php
// tests/submissions_test.php additions
$result = scoreSubmission($quiz, $questions, [10 => 'Paris', 11 => '5']);
assertSameValue(2, $result['total_count'], 'Submission summary should expose the total question count.');
```

- [ ] **Step 2: Run the updated tests to confirm they fail**

Run: `php tests/quizzes_test.php`  
Expected: FAIL because `summarizeQuizCard()` does not exist yet.

Run: `php tests/submissions_test.php`  
Expected: FAIL because the scoring result does not expose the expected metadata yet.

- [ ] **Step 3: Implement quiz summaries, stronger authoring layout, progress UI, and submit confirmation**

```php
// src/quizzes.php addition
function summarizeQuizCard(array $quiz): string
{
    $questionCount = count($quiz['question_ids']);
    $groupCount = count($quiz['assigned_group_ids']);

    return sprintf(
        '%d questions · %d groups · %d minutes',
        $questionCount,
        $groupCount,
        (int) $quiz['time_limit_minutes']
    );
}
```

```php
// public/instructor/quizzes.php structure change
<section class="card">
    <?php renderSectionHeader('Create Quiz', 'Build, review, and publish a richer quiz'); ?>
    <form method="post" class="quiz-builder-form">
        <div class="form-columns">
            <div>
                <label>Quiz Title <input name="title" required></label>
                <label>Description <textarea name="description"></textarea></label>
                <label>Time Limit (minutes) <input name="time_limit_minutes" type="number" min="1" value="20" required></label>
            </div>
            <div class="preview-panel">
                <h3>Quiz Preview</h3>
                <p id="quiz-live-summary">0 questions selected.</p>
            </div>
        </div>
```

```php
// public/student/quiz.php key additions
<aside class="card quiz-sidebar">
    <p class="timer" id="quiz-timer" data-timer-minutes="<?= (int) $quiz['time_limit_minutes'] ?>">Time remaining: --:--</p>
    <p id="quiz-progress" data-question-count="<?= count($quiz['question_ids']) ?>">0 / <?= count($quiz['question_ids']) ?> answered</p>
    <button type="submit" form="student-quiz-form">Submit Quiz</button>
</aside>

<form id="student-quiz-form" method="post" data-quiz-form data-confirm-submit="You are about to submit this quiz. Continue?">
```

```js
// public/assets/app.js additions
const previewInput = document.querySelector('[data-rich-content-input]');
const previewTarget = document.querySelector('[data-preview-target]');

if (previewInput && previewTarget) {
  const renderPreview = () => {
    previewTarget.innerHTML = previewInput.value.trim() || '<p class="muted">Preview appears here.</p>';
  };
  renderPreview();
  previewInput.addEventListener('input', renderPreview);
}

const progressNode = document.querySelector('#quiz-progress');
if (quizForm && progressNode) {
  const updateProgress = () => {
    const answered = quizForm.querySelectorAll('input[type="radio"]:checked').length;
    const total = Number(progressNode.dataset.questionCount || '0');
    progressNode.textContent = `${answered} / ${total} answered`;
  };
  quizForm.addEventListener('change', updateProgress);
  updateProgress();

  quizForm.addEventListener('submit', (event) => {
    const confirmMessage = quizForm.dataset.confirmSubmit || '';
    if (confirmMessage !== '' && !window.confirm(confirmMessage)) {
      event.preventDefault();
    }
  });
}
```

- [ ] **Step 4: Run the updated tests and syntax checks**

Run: `php tests/quizzes_test.php`  
Expected: `OK quizzes_test`

Run: `php tests/submissions_test.php`  
Expected: `OK submissions_test`

Run: `php -l public/student/quiz.php`  
Expected: `No syntax errors detected in public/student/quiz.php`

- [ ] **Step 5: Commit the quiz-flow upgrade**

```bash
git add src/quizzes.php public/instructor/quizzes.php public/student/quiz.php public/assets/app.js tests/quizzes_test.php tests/submissions_test.php
git commit -m "feat: upgrade quiz authoring and student flow"
```

## Task 5: Redesign Results, Student History, and Analytics Presentation

**Files:**
- Modify: `src/statistics.php`
- Modify: `public/instructor/results.php`
- Modify: `public/instructor/results_data.php`
- Modify: `public/student/result.php`
- Modify: `public/student/history.php`
- Modify: `tests/statistics_test.php`

- [ ] **Step 1: Extend the statistics test**

```php
// tests/statistics_test.php additions
$summary = summarizeQuizStatistics($quiz, $questionsById, $submissions);
assertSameValue('Week 1 Quiz', $summary['quiz_title'], 'The statistics summary should include the quiz title.');
assertSameValue('Capital of France?', $summary['per_question_accuracy'][10]['question_text'], 'Per-question accuracy should include readable question text.');
```

- [ ] **Step 2: Run the statistics test to confirm it fails**

Run: `php tests/statistics_test.php`  
Expected: FAIL because the summary payload does not include the expected display fields yet.

- [ ] **Step 3: Implement richer summary payloads and dashboard-style rendering**

```php
// src/statistics.php change
return [
    'quiz_id' => (int) $quiz['id'],
    'quiz_title' => $quiz['title'],
    'average_score' => $averageScore,
    'submission_count' => count($quizSubmissions),
    'high_score' => $quizSubmissions === [] ? 0 : max(array_column($quizSubmissions, 'score')),
    'per_question_accuracy' => $perQuestionAccuracy,
];
```

```php
// public/instructor/results.php structure change
<section class="card">
    <?php renderSectionHeader('Quiz Results', 'Load a published or closed quiz to inspect statistics'); ?>
    <div class="results-toolbar">
        <label>Select Quiz
            <select id="results-quiz-select" data-results-endpoint="/instructor/results_data.php">
                <option value="">Choose a quiz</option>
                <?php foreach ($quizzes as $quiz): ?>
                    <option value="<?= (int) $quiz['id'] ?>"><?= h($quiz['title']) ?> (<?= h($quiz['status']) ?>)</option>
                <?php endforeach; ?>
            </select>
        </label>
        <a id="results-export-link" class="button button-secondary" href="#">Export Current Quiz as CSV</a>
    </div>
</section>
<section id="results-summary" class="card">
    <div class="stat-grid">
        <article class="stat-card"><p class="stat-card-value">--</p><p class="stat-card-label">Average Score</p></article>
        <article class="stat-card"><p class="stat-card-value">--</p><p class="stat-card-label">Submissions</p></article>
        <article class="stat-card"><p class="stat-card-value">--</p><p class="stat-card-label">Highest Score</p></article>
    </div>
    <div id="results-question-breakdown"></div>
</section>
```

```php
// public/instructor/results_data.php payload shape
header('Content-Type: application/json');
echo json_encode([
    'quiz_id' => $summary['quiz_id'],
    'quiz_title' => $summary['quiz_title'],
    'average_score' => $summary['average_score'],
    'submission_count' => $summary['submission_count'],
    'high_score' => $summary['high_score'],
    'per_question_accuracy' => $summary['per_question_accuracy'],
], JSON_UNESCAPED_SLASHES);
```

```js
// public/assets/app.js AJAX rendering update
resultsSummary.innerHTML = `
  <div class="stat-grid">
    <article class="stat-card"><p class="stat-card-value">${data.average_score}</p><p class="stat-card-label">Average Score</p></article>
    <article class="stat-card"><p class="stat-card-value">${data.submission_count}</p><p class="stat-card-label">Submissions</p></article>
    <article class="stat-card"><p class="stat-card-value">${data.high_score}</p><p class="stat-card-label">Highest Score</p></article>
  </div>
  <div class="card">
    <h3>${data.quiz_title}</h3>
    <ul>
      ${Object.values(data.per_question_accuracy).map((detail) => `<li>${detail.question_text}: ${detail.accuracy}% correct</li>`).join('')}
    </ul>
  </div>
`;
```

```php
// public/student/result.php structure change
<section class="hero-card card">
    <p class="eyebrow">Submission Summary</p>
    <h2>Your score is <?= (int) $submission['score'] ?></h2>
    <p class="muted">Review each question below to compare your answer with the correct answer.</p>
</section>
```

- [ ] **Step 4: Run the statistics test and syntax checks**

Run: `php tests/statistics_test.php`  
Expected: `OK statistics_test`

Run: `php -l public/instructor/results.php`  
Expected: `No syntax errors detected in public/instructor/results.php`

Run: `php -l public/student/history.php`  
Expected: `No syntax errors detected in public/student/history.php`

- [ ] **Step 5: Commit the analytics redesign**

```bash
git add src/statistics.php public/instructor/results.php public/instructor/results_data.php public/student/result.php public/student/history.php tests/statistics_test.php
git commit -m "feat: redesign phase 2 analytics pages"
```

## Manual Verification Checklist

Run this after Task 5:

1. Start the app with `php -S 127.0.0.1:8000 -t public`.
2. Log in as `instructor / instructor123`.
3. Import `tests/fixtures/students.csv`.
4. Create a question that includes formatted body content.
5. Upload one image to that question and confirm the preview renders it.
6. Create a quiz that includes the rich-content question.
7. Publish the quiz and verify the quiz summary area updates as questions are selected.
8. Log in as an imported student and open the quiz.
9. Confirm the question image, formatted content, timer, and progress counter all render.
10. Submit the quiz and confirm the browser shows the confirmation dialog before submit.
11. Open the student result page and verify the redesigned summary renders.
12. Return to the instructor results page, load the quiz via AJAX, and confirm the summary cards and per-question breakdown render.

## Spec Coverage Map

- Visual system redesign: Task 2 and Task 5
- Rich question content and image upload: Task 1 and Task 3
- Instructor quiz authoring upgrade: Task 3 and Task 4
- Student answering experience upgrade: Task 4
- Results and analytics upgrade: Task 5
- Compatibility with Phase 1 data: Task 1 and Task 3
- Enhancement items remain intentionally optional and are tracked outside the core task list
