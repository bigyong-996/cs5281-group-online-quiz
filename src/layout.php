<?php
declare(strict_types=1);

function redirectTo(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function flashMessage(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function h(string|int|null $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function navItems(?array $user): array
{
    if ($user === null) {
        return [];
    }

    if (($user['role'] ?? '') === 'instructor') {
        return [
            '/instructor/dashboard.php' => 'Dashboard',
            '/instructor/import_students.php' => 'Import',
            '/instructor/groups.php' => 'Groups',
            '/instructor/questions.php' => 'Questions',
            '/instructor/quizzes.php' => 'Quizzes',
            '/instructor/results.php' => 'Results',
            '/logout.php' => 'Logout',
        ];
    }

    return [
        '/student/dashboard.php' => 'Quizzes',
        '/student/history.php' => 'History',
        '/logout.php' => 'Logout',
    ];
}

function renderPageStart(string $title, ?array $user = null): void
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    $navItems = navItems($user);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= h($title) ?></title>
        <link rel="stylesheet" href="/assets/styles.css">
        <script defer src="/assets/app.js"></script>
    </head>
    <body>
    <header class="topbar">
        <div>
            <p class="eyebrow">CS5281 Online Quiz</p>
            <h1><?= h($title) ?></h1>
        </div>
        <?php if ($navItems !== []): ?>
            <nav class="nav-links" aria-label="Main navigation">
                <?php foreach ($navItems as $href => $label): ?>
                    <a href="<?= h($href) ?>"><?= h($label) ?></a>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>
    </header>
    <?php if ($flash !== null): ?>
        <p class="flash flash-<?= h($flash['type']) ?>"><?= h($flash['message']) ?></p>
    <?php endif; ?>
    <main class="page-shell">
    <?php
}

function renderSectionHeader(string $title, string $subtitle = ''): void
{
    echo '<div class="section-header">';
    echo '<div>';
    echo '<h2>' . h($title) . '</h2>';
    if ($subtitle !== '') {
        echo '<p class="muted section-subtitle">' . h($subtitle) . '</p>';
    }
    echo '</div>';
    echo '</div>';
}

function renderStatCard(string $label, string|int $value, string $tone = 'default'): void
{
    echo '<article class="stat-card stat-card-' . h($tone) . '">';
    echo '<p class="stat-card-value">' . h($value) . '</p>';
    echo '<p class="stat-card-label">' . h($label) . '</p>';
    echo '</article>';
}

function renderPageEnd(): void
{
    echo "</main></body></html>";
}
