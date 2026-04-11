<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/auth.php';

$user = requireRole('instructor');
$students = array_values(array_filter(loadRecords(DATA_DIR . '/users.json'), static fn(array $record): bool => ($record['role'] ?? '') === 'student'));
$groups = loadRecords(DATA_DIR . '/groups.json');
$questions = loadRecords(DATA_DIR . '/questions.json');
$quizzes = loadRecords(DATA_DIR . '/quizzes.json');
$submissions = loadRecords(DATA_DIR . '/submissions.json');

renderPageStart('Instructor Dashboard', $user);
?>
<section class="card hero-card">
    <p class="eyebrow eyebrow-dark">Instructor Workspace</p>
    <h2>Manage student groups, build richer quizzes, and follow class performance from one place.</h2>
    <p class="muted">Phase 2 sharpens the authoring and reporting flow while keeping the original PHP and JSON architecture lightweight.</p>
</section>
<section>
    <?php renderSectionHeader('Overview', 'Current system activity'); ?>
    <div class="stat-grid">
        <?php renderStatCard('Students', count($students), 'warm'); ?>
        <?php renderStatCard('Groups', count($groups), 'cool'); ?>
        <?php renderStatCard('Questions', count($questions), 'accent'); ?>
        <?php renderStatCard('Quizzes', count($quizzes), 'warm'); ?>
        <?php renderStatCard('Submissions', count($submissions), 'cool'); ?>
    </div>
</section>
<section>
    <?php renderSectionHeader('Workflow', 'Move from roster setup to published quiz review'); ?>
    <div class="action-grid">
        <a class="card action-card" href="/instructor/import_students.php">
            <h3>Import Students</h3>
            <p class="muted">Load the course roster from CSV and seed new student accounts quickly.</p>
        </a>
        <a class="card action-card" href="/instructor/groups.php">
            <h3>Create Groups</h3>
            <p class="muted">Organize students into teaching groups before assigning quiz access.</p>
        </a>
        <a class="card action-card" href="/instructor/questions.php">
            <h3>Build Questions</h3>
            <p class="muted">Draft MCQs with formatted content, option sets, and a question image.</p>
        </a>
        <a class="card action-card" href="/instructor/quizzes.php">
            <h3>Publish Quizzes</h3>
            <p class="muted">Combine questions, assign groups, and control draft, published, or closed status.</p>
        </a>
        <a class="card action-card" href="/instructor/results.php">
            <h3>Review Results</h3>
            <p class="muted">Inspect summary metrics and per-question accuracy from the analytics dashboard.</p>
        </a>
    </div>
</section>
<?php renderPageEnd(); ?>
