<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

function usersFile(?string $override = null): string
{
    return $override ?? DATA_DIR . '/users.json';
}

function ensureDefaultInstructor(?string $override = null): void
{
    $file = usersFile($override);
    $users = loadRecords($file);

    if ($users !== []) {
        return;
    }

    saveRecords($file, [[
        'id' => 1,
        'username' => 'instructor',
        'display_name' => 'Course Instructor',
        'password_hash' => password_hash('instructor123', PASSWORD_DEFAULT),
        'role' => 'instructor',
        'group_id' => null,
    ]]);
}

function attemptLogin(string $username, string $password, ?string $override = null): ?array
{
    ensureDefaultInstructor($override);

    foreach (loadRecords(usersFile($override)) as $user) {
        if (($user['username'] ?? '') === $username && password_verify($password, (string) ($user['password_hash'] ?? ''))) {
            return $user;
        }
    }

    return null;
}

function loginUser(array $user): void
{
    $_SESSION['user'] = [
        'id' => (int) $user['id'],
        'username' => $user['username'],
        'display_name' => $user['display_name'],
        'role' => $user['role'],
        'group_id' => $user['group_id'],
    ];
}

function logoutUser(): void
{
    $_SESSION = [];
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
}

function currentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

function requireLogin(): array
{
    $user = currentUser();
    if ($user === null) {
        flashMessage('error', 'Please log in first.');
        redirectTo('/index.php');
    }

    return $user;
}

function userHasRole(array $user, string $role): bool
{
    return ($user['role'] ?? '') === $role;
}

function requireRole(string $role): array
{
    $user = requireLogin();
    if (! userHasRole($user, $role)) {
        flashMessage('error', 'You do not have permission to access that page.');
        redirectTo('/index.php');
    }

    return $user;
}

function routeForRole(string $role): string
{
    return $role === 'instructor' ? '/instructor/dashboard.php' : '/student/dashboard.php';
}
