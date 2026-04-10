<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/auth.php';
require_once __DIR__ . '/../../src/csv_tools.php';

$user = requireRole('instructor');
$summary = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (! isset($_FILES['students_csv']) || $_FILES['students_csv']['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Please upload a valid CSV file.');
        }

        $summary = importStudentsFromCsv($_FILES['students_csv']['tmp_name'], DATA_DIR . '/users.json');
        flashMessage('success', "Imported {$summary['created']} students; skipped {$summary['skipped']} rows.");
        $_SESSION['import_summary'] = $summary;
        redirectTo('/instructor/import_students.php');
    } catch (Throwable $error) {
        flashMessage('error', $error->getMessage());
        redirectTo('/instructor/import_students.php');
    }
}

$summary = $_SESSION['import_summary'] ?? null;
unset($_SESSION['import_summary']);

renderPageStart('Import Students', $user);
?>
<section class="card">
    <p class="muted">CSV format: username, display_name, initial_password</p>
    <form method="post" enctype="multipart/form-data">
        <label>Student CSV <input type="file" name="students_csv" accept=".csv" required></label>
        <button type="submit">Import Students</button>
    </form>
</section>
<?php if ($summary !== null): ?>
    <section class="card">
        <h2>Import Summary</h2>
        <p>Created: <?= (int) $summary['created'] ?></p>
        <p>Skipped: <?= (int) $summary['skipped'] ?></p>
        <?php if (($summary['errors'] ?? []) !== []): ?>
            <ul>
                <?php foreach ($summary['errors'] as $error): ?>
                    <li><?= h($error) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
<?php endif; ?>
<?php renderPageEnd(); ?>
