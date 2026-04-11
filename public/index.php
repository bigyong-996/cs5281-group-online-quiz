<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth.php';

$existingUser = currentUser();
if ($existingUser !== null) {
    redirectTo(routeForRole($existingUser['role']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $user = attemptLogin($username, $password);

    if ($user !== null) {
        loginUser($user);
        redirectTo(routeForRole($user['role']));
    }

    flashMessage('error', 'Invalid username or password.');
    redirectTo('/index.php');
}

renderPageStart('Online Quiz Login');
?>
<section class="hero-panel">
    <div class="hero-copy">
        <p class="eyebrow eyebrow-dark">Course Project Workspace</p>
        <h2>Sign in to build quizzes, review results, and take assigned assessments.</h2>
        <p class="muted">Use the instructor account to manage content and the student accounts from your CSV import to experience the learner flow.</p>
        <ul class="feature-list">
            <li>Rich question authoring with lightweight formatting</li>
            <li>Clearer quiz timing, progress, and submission flow</li>
            <li>Dashboard-style analytics for faster review</li>
        </ul>
    </div>
    <div class="card login-card">
        <p class="muted">Default instructor login: <strong>instructor</strong> / <strong>instructor123</strong></p>
        <form method="post">
            <label>Username <input name="username" autocomplete="username" required></label>
            <label>Password <input name="password" type="password" autocomplete="current-password" required></label>
            <button type="submit">Log In</button>
        </form>
    </div>
</section>
<?php renderPageEnd(); ?>
