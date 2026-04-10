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
<section class="grid">
    <article class="card">
        <p class="stat"><?= count($students) ?></p>
        <p>Students</p>
    </article>
    <article class="card">
        <p class="stat"><?= count($groups) ?></p>
        <p>Groups</p>
    </article>
    <article class="card">
        <p class="stat"><?= count($questions) ?></p>
        <p>Questions</p>
    </article>
    <article class="card">
        <p class="stat"><?= count($quizzes) ?></p>
        <p>Quizzes</p>
    </article>
    <article class="card">
        <p class="stat"><?= count($submissions) ?></p>
        <p>Submissions</p>
    </article>
</section>
<section class="card">
    <h2>Workflow</h2>
    <ol>
        <li><a href="/instructor/import_students.php">Import students</a></li>
        <li><a href="/instructor/groups.php">Create groups and assign students</a></li>
        <li><a href="/instructor/questions.php">Add MCQ questions</a></li>
        <li><a href="/instructor/quizzes.php">Create and publish a quiz</a></li>
        <li><a href="/instructor/results.php">Review results</a></li>
    </ol>
</section>
<?php renderPageEnd(); ?>
