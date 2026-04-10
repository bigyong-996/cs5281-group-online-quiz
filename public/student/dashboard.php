<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/auth.php';

$user = requireRole('student');
renderPageStart('Student Dashboard', $user);
?>
<section class="card">
    <p>Welcome, <?= h($user['display_name']) ?>.</p>
    <p class="muted">Published quizzes assigned to your group will appear here.</p>
</section>
<?php renderPageEnd(); ?>
