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
<section class="card">
    <p class="muted">Default instructor login: <strong>instructor</strong> / <strong>instructor123</strong></p>
    <form method="post">
        <label>Username <input name="username" autocomplete="username" required></label>
        <label>Password <input name="password" type="password" autocomplete="current-password" required></label>
        <button type="submit">Log In</button>
    </form>
</section>
<?php renderPageEnd(); ?>
