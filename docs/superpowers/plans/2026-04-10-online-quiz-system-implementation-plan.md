# Online Quiz System Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a file-backed PHP online quiz system with Instructor and Student roles, CSV student import, group-based quiz assignment, automatic grading, results export, and one AJAX-powered instructor results view.

**Architecture:** Serve PHP pages from `public/`, keep shared business logic in small function libraries under `src/`, and persist application state in JSON files under `data/`. Use plain PHP CLI test scripts against temporary fixture files so the core rules stay testable without adding a heavyweight framework.

**Tech Stack:** PHP 8.x, built-in PHP server, HTML5, CSS3, vanilla JavaScript, JSON/CSV file storage, PHP CLI tests

---

## File Structure

### Repo-level files

- Modify: `.gitignore`  
  Ignore runtime export files and temporary test directories.

### App bootstrap and domain logic

- Create: `src/bootstrap.php`  
  Central include file, path constants, session start, shared requires.
- Create: `src/layout.php`  
  Shared page header, footer, navigation, flash message helpers.
- Create: `src/storage.php`  
  JSON file creation, read, write, and ID helpers.
- Create: `src/auth.php`  
  Login, logout, session guards, default instructor seeding.
- Create: `src/csv_tools.php`  
  CSV import and export helpers.
- Create: `src/groups.php`  
  Group CRUD and student-to-group assignment.
- Create: `src/questions.php`  
  MCQ CRUD helpers.
- Create: `src/quizzes.php`  
  Quiz CRUD, publishing, group filtering, quiz lookup.
- Create: `src/submissions.php`  
  Scoring, single-submission checks, submission persistence.
- Create: `src/statistics.php`  
  Instructor result summaries and per-question accuracy.

### Public pages

- Create: `public/index.php`
- Create: `public/logout.php`
- Create: `public/assets/styles.css`
- Create: `public/assets/app.js`
- Create: `public/instructor/dashboard.php`
- Create: `public/instructor/import_students.php`
- Create: `public/instructor/groups.php`
- Create: `public/instructor/questions.php`
- Create: `public/instructor/quizzes.php`
- Create: `public/instructor/results.php`
- Create: `public/instructor/results_data.php`
- Create: `public/instructor/export_results.php`
- Create: `public/student/dashboard.php`
- Create: `public/student/quiz.php`
- Create: `public/student/result.php`
- Create: `public/student/history.php`

### Data files

- Create: `data/users.json`
- Create: `data/groups.json`
- Create: `data/questions.json`
- Create: `data/quizzes.json`
- Create: `data/submissions.json`
- Create: `data/export/.gitkeep`

### Tests and fixtures

- Create: `tests/bootstrap.php`
- Create: `tests/fixtures/students.csv`
- Create: `tests/storage_test.php`
- Create: `tests/auth_test.php`
- Create: `tests/student_import_test.php`
- Create: `tests/questions_test.php`
- Create: `tests/quizzes_test.php`
- Create: `tests/submissions_test.php`
- Create: `tests/statistics_test.php`

## Implementation Notes

- Keep all persisted records as arrays of associative arrays in JSON files.
- Store user passwords as `password_hash` values, not plaintext.
- Fix MCQ questions to exactly four options to keep the form, storage, and grading simple.
- Store `correct_answer` as the option text rather than an index so option shuffling stays easy.
- Use `assigned_group_ids` as an array of integers.
- Use `time_limit_minutes` in implementation to avoid unit ambiguity.
- Allow exactly one submission per student per quiz.
- Seed one default instructor account automatically when `data/users.json` is empty:
  - username: `instructor`
  - password: `instructor123`

## Task 1: Scaffold Bootstrap, Storage, and Test Harness

**Files:**
- Modify: `.gitignore`
- Create: `src/bootstrap.php`
- Create: `src/layout.php`
- Create: `src/storage.php`
- Create: `public/assets/app.js`
- Create: `public/assets/styles.css`
- Create: `data/users.json`
- Create: `data/groups.json`
- Create: `data/questions.json`
- Create: `data/quizzes.json`
- Create: `data/submissions.json`
- Create: `data/export/.gitkeep`
- Create: `tests/bootstrap.php`
- Test: `tests/storage_test.php`

- [ ] **Step 1: Write the failing storage test and base test helpers**

```php
// tests/bootstrap.php
<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/../src/storage.php';

function assertSameValue(mixed $expected, mixed $actual, string $message): void
{
    if ($expected !== $actual) {
        fwrite(STDERR, $message . PHP_EOL . 'Expected: ' . var_export($expected, true) . PHP_EOL . 'Actual: ' . var_export($actual, true) . PHP_EOL);
        exit(1);
    }
}

function assertTrueValue(bool $condition, string $message): void
{
    if (! $condition) {
        fwrite(STDERR, $message . PHP_EOL);
        exit(1);
    }
}

function makeTempDir(string $label): string
{
    $dir = sys_get_temp_dir() . '/quiz-system-' . $label . '-' . bin2hex(random_bytes(4));
    mkdir($dir, 0777, true);
    return $dir;
}
```

```php
// tests/storage_test.php
<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$tempDir = makeTempDir('storage');
$groupsFile = $tempDir . '/groups.json';

ensureJsonFile($groupsFile);
assertSameValue([], loadRecords($groupsFile), 'New JSON files should start empty.');

saveRecords($groupsFile, [
    ['id' => 1, 'group_name' => 'Group A'],
]);

$groups = loadRecords($groupsFile);

assertSameValue('Group A', $groups[0]['group_name'], 'Stored group name should round-trip.');
assertSameValue(2, nextId($groups), 'nextId should advance from the largest current id.');

echo "OK storage_test\n";
```

- [ ] **Step 2: Run the storage test to confirm it fails**

Run: `php tests/storage_test.php`  
Expected: FAIL because `src/storage.php` and its functions do not exist yet.

- [ ] **Step 3: Implement bootstrap, storage, base layout, and seed data files**

```php
// src/storage.php
<?php
declare(strict_types=1);

function ensureJsonFile(string $path, array $default = []): void
{
    if (! is_dir(dirname($path))) {
        mkdir(dirname($path), 0777, true);
    }

    if (! file_exists($path)) {
        file_put_contents($path, json_encode($default, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}

function loadRecords(string $path): array
{
    ensureJsonFile($path);
    $json = file_get_contents($path);
    $data = json_decode($json === false ? '[]' : $json, true);

    return is_array($data) ? $data : [];
}

function saveRecords(string $path, array $records): void
{
    ensureJsonFile($path);
    file_put_contents($path, json_encode(array_values($records), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

function nextId(array $records): int
{
    if ($records === []) {
        return 1;
    }

    return max(array_map(static fn(array $record): int => (int) $record['id'], $records)) + 1;
}
```

```php
// src/bootstrap.php
<?php
declare(strict_types=1);

define('PROJECT_ROOT', dirname(__DIR__));
define('SRC_DIR', PROJECT_ROOT . '/src');
define('DATA_DIR', PROJECT_ROOT . '/data');

require_once SRC_DIR . '/storage.php';
require_once SRC_DIR . '/layout.php';

session_start();

foreach (['users', 'groups', 'questions', 'quizzes', 'submissions'] as $name) {
    ensureJsonFile(DATA_DIR . '/' . $name . '.json', []);
}
```

```php
// src/layout.php
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

function renderPageStart(string $title, ?array $user = null): void
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars($title) ?></title>
        <link rel="stylesheet" href="/assets/styles.css">
        <script defer src="/assets/app.js"></script>
    </head>
    <body>
    <header class="topbar">
        <h1><?= htmlspecialchars($title) ?></h1>
        <?php if ($user !== null): ?>
            <nav class="nav-links">
                <a href="/logout.php">Logout</a>
            </nav>
        <?php endif; ?>
    </header>
    <?php if ($flash !== null): ?>
        <p class="flash flash-<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></p>
    <?php endif; ?>
    <main class="page-shell">
    <?php
}

function renderPageEnd(): void
{
    echo "</main></body></html>";
}
```

```css
/* public/assets/styles.css */
body { font-family: Arial, sans-serif; margin: 0; background: #f6f7fb; color: #1f2937; }
.topbar { display: flex; justify-content: space-between; align-items: center; padding: 16px 24px; background: #1d4ed8; color: #fff; }
.page-shell { max-width: 1100px; margin: 24px auto; padding: 0 16px; }
.card { background: #fff; border: 1px solid #dbe2ea; border-radius: 8px; padding: 16px; margin-bottom: 16px; }
.flash { padding: 12px 16px; margin: 16px auto; max-width: 1100px; border-radius: 8px; }
.flash-success { background: #dcfce7; color: #166534; }
.flash-error { background: #fee2e2; color: #991b1b; }
table { width: 100%; border-collapse: collapse; background: #fff; }
th, td { border: 1px solid #dbe2ea; padding: 10px; text-align: left; }
form { display: grid; gap: 12px; }
input, select, textarea, button { font: inherit; padding: 10px; }
button { background: #2563eb; color: #fff; border: none; border-radius: 6px; cursor: pointer; }
```

```js
// public/assets/app.js
document.addEventListener('DOMContentLoaded', () => {});
```

```gitignore
# .gitignore additions
data/export/*.csv
tests/tmp/
```

```json
// data/users.json
[]
```

- [ ] **Step 4: Run the storage test and syntax checks**

Run: `php tests/storage_test.php`  
Expected: `OK storage_test`

Run: `php -l src/storage.php`  
Expected: `No syntax errors detected in src/storage.php`

Run: `php -l src/bootstrap.php`  
Expected: `No syntax errors detected in src/bootstrap.php`

- [ ] **Step 5: Commit the scaffold**

```bash
git add .gitignore src/bootstrap.php src/layout.php src/storage.php public/assets/app.js public/assets/styles.css data tests/bootstrap.php tests/storage_test.php
git commit -m "chore: scaffold php quiz system"
```

## Task 2: Add Authentication, Role Guards, and Basic Dashboards

**Files:**
- Create: `src/auth.php`
- Create: `public/index.php`
- Create: `public/logout.php`
- Create: `public/instructor/dashboard.php`
- Create: `public/student/dashboard.php`
- Test: `tests/auth_test.php`

- [ ] **Step 1: Write the failing auth test**

```php
// tests/auth_test.php
<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../src/auth.php';

$tempDir = makeTempDir('auth');
$usersFile = $tempDir . '/users.json';

saveRecords($usersFile, [
    [
        'id' => 1,
        'username' => 'teacher',
        'display_name' => 'Main Teacher',
        'password_hash' => password_hash('secret123', PASSWORD_DEFAULT),
        'role' => 'instructor',
        'group_id' => null,
    ],
]);

$user = attemptLogin('teacher', 'secret123', $usersFile);

assertSameValue('instructor', $user['role'], 'Successful login should return the matching user.');
assertTrueValue(attemptLogin('teacher', 'wrong-password', $usersFile) === null, 'Wrong password should fail.');
assertTrueValue(userHasRole($user, 'instructor'), 'Instructor should satisfy instructor role check.');

echo "OK auth_test\n";
```

- [ ] **Step 2: Run the auth test to confirm it fails**

Run: `php tests/auth_test.php`  
Expected: FAIL because `src/auth.php` and its functions do not exist yet.

- [ ] **Step 3: Implement auth helpers and login/logout pages**

```php
// src/auth.php
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
        if ($user['username'] === $username && password_verify($password, $user['password_hash'])) {
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
    session_destroy();
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
```

```php
// public/index.php
<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth.php';

if (currentUser() !== null) {
    redirectTo(currentUser()['role'] === 'instructor' ? '/instructor/dashboard.php' : '/student/dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = attemptLogin(trim($_POST['username'] ?? ''), $_POST['password'] ?? '');

    if ($user !== null) {
        loginUser($user);
        redirectTo($user['role'] === 'instructor' ? '/instructor/dashboard.php' : '/student/dashboard.php');
    }

    flashMessage('error', 'Invalid username or password.');
    redirectTo('/index.php');
}

renderPageStart('Online Quiz Login');
?>
<section class="card">
    <form method="post">
        <label>Username <input name="username" required></label>
        <label>Password <input name="password" type="password" required></label>
        <button type="submit">Log In</button>
    </form>
</section>
<?php renderPageEnd(); ?>
```

```php
// public/logout.php
<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth.php';

logoutUser();
session_start();
flashMessage('success', 'Logged out successfully.');
redirectTo('/index.php');
```

```php
// public/instructor/dashboard.php
<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/auth.php';

$user = requireRole('instructor');
renderPageStart('Instructor Dashboard', $user);
?>
<section class="card">
    <p>Welcome, <?= htmlspecialchars($user['display_name']) ?>.</p>
    <ul>
        <li><a href="/instructor/import_students.php">Import Students</a></li>
        <li><a href="/instructor/groups.php">Manage Groups</a></li>
        <li><a href="/instructor/questions.php">Question Bank</a></li>
        <li><a href="/instructor/quizzes.php">Manage Quizzes</a></li>
        <li><a href="/instructor/results.php">Results</a></li>
    </ul>
</section>
<?php renderPageEnd(); ?>
```

```php
// public/student/dashboard.php
<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/auth.php';

$user = requireRole('student');
renderPageStart('Student Dashboard', $user);
?>
<section class="card">
    <p>Welcome, <?= htmlspecialchars($user['display_name']) ?>.</p>
    <p>Your quizzes will appear here once quiz publishing is implemented.</p>
</section>
<?php renderPageEnd(); ?>
```

- [ ] **Step 4: Run the auth test and smoke-check the login page**

Run: `php tests/auth_test.php`  
Expected: `OK auth_test`

Run: `php -S 127.0.0.1:8000 -t public`  
Expected: PHP dev server starts and `/index.php` renders a login form.

- [ ] **Step 5: Commit authentication**

```bash
git add src/auth.php public/index.php public/logout.php public/instructor/dashboard.php public/student/dashboard.php tests/auth_test.php
git commit -m "feat: add login and role guards"
```

## Task 3: Add Student CSV Import and Group Management

**Files:**
- Create: `src/csv_tools.php`
- Create: `src/groups.php`
- Create: `public/instructor/import_students.php`
- Create: `public/instructor/groups.php`
- Create: `tests/fixtures/students.csv`
- Test: `tests/student_import_test.php`

- [ ] **Step 1: Write the failing import and group test**

```csv
# tests/fixtures/students.csv
username,display_name,initial_password
alice,Alice Chan,alice123
bob,Bob Lee,bob123
```

```php
// tests/student_import_test.php
<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../src/csv_tools.php';
require_once __DIR__ . '/../src/groups.php';

$tempDir = makeTempDir('students');
$usersFile = $tempDir . '/users.json';
$groupsFile = $tempDir . '/groups.json';

saveRecords($usersFile, []);
saveRecords($groupsFile, []);

$summary = importStudentsFromCsv(__DIR__ . '/fixtures/students.csv', $usersFile);
assertSameValue(2, $summary['created'], 'Two new student accounts should be created.');
assertSameValue(0, $summary['skipped'], 'First import should not skip any rows.');

$group = saveGroup(['group_name' => 'Group 1'], $groupsFile);
assignStudentToGroup('alice', (int) $group['id'], $usersFile);

$users = loadRecords($usersFile);
assertSameValue(1, $users[0]['group_id'], 'Assigned student should receive the group id.');

echo "OK student_import_test\n";
```

- [ ] **Step 2: Run the import/group test to confirm it fails**

Run: `php tests/student_import_test.php`  
Expected: FAIL because CSV and group helpers do not exist yet.

- [ ] **Step 3: Implement student import, group CRUD, and instructor pages**

```php
// src/csv_tools.php
<?php
declare(strict_types=1);

require_once __DIR__ . '/storage.php';

function importStudentsFromCsv(string $csvPath, string $usersFile): array
{
    $handle = fopen($csvPath, 'rb');
    if ($handle === false) {
        throw new RuntimeException('Unable to open CSV file.');
    }

    $header = fgetcsv($handle);
    if ($header !== ['username', 'display_name', 'initial_password']) {
        throw new RuntimeException('CSV header must be username,display_name,initial_password.');
    }

    $users = loadRecords($usersFile);
    $usernames = array_column($users, 'username');
    $created = 0;
    $skipped = 0;

    while (($row = fgetcsv($handle)) !== false) {
        [$username, $displayName, $password] = $row;
        if (in_array($username, $usernames, true)) {
            $skipped++;
            continue;
        }

        $users[] = [
            'id' => nextId($users),
            'username' => trim($username),
            'display_name' => trim($displayName),
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 'student',
            'group_id' => null,
        ];
        $usernames[] = $username;
        $created++;
    }

    fclose($handle);
    saveRecords($usersFile, $users);

    return ['created' => $created, 'skipped' => $skipped];
}
```

```php
// src/groups.php
<?php
declare(strict_types=1);

require_once __DIR__ . '/storage.php';

function groupsFile(): string
{
    return DATA_DIR . '/groups.json';
}

function saveGroup(array $input, ?string $override = null): array
{
    $file = $override ?? groupsFile();
    $groups = loadRecords($file);
    $group = [
        'id' => nextId($groups),
        'group_name' => trim($input['group_name']),
    ];
    $groups[] = $group;
    saveRecords($file, $groups);

    return $group;
}

function assignStudentToGroup(string $username, int $groupId, ?string $usersOverride = null): void
{
    $usersFile = $usersOverride ?? DATA_DIR . '/users.json';
    $users = loadRecords($usersFile);

    foreach ($users as &$user) {
        if ($user['username'] === $username) {
            $user['group_id'] = $groupId;
        }
    }

    saveRecords($usersFile, $users);
}
```

```php
// public/instructor/import_students.php
<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/auth.php';
require_once __DIR__ . '/../../src/csv_tools.php';

$user = requireRole('instructor');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['students_csv']['tmp_name'])) {
    $summary = importStudentsFromCsv($_FILES['students_csv']['tmp_name'], DATA_DIR . '/users.json');
    flashMessage('success', "Imported {$summary['created']} students, skipped {$summary['skipped']} duplicates.");
    redirectTo('/instructor/import_students.php');
}

renderPageStart('Import Students', $user);
?>
<section class="card">
    <form method="post" enctype="multipart/form-data">
        <label>Student CSV <input type="file" name="students_csv" accept=".csv" required></label>
        <button type="submit">Import Students</button>
    </form>
</section>
<?php renderPageEnd(); ?>
```

```php
// public/instructor/groups.php
<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/auth.php';
require_once __DIR__ . '/../../src/groups.php';

$user = requireRole('instructor');
$users = array_values(array_filter(loadRecords(DATA_DIR . '/users.json'), static fn(array $record): bool => $record['role'] === 'student'));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['group_name'])) {
    saveGroup($_POST);
    flashMessage('success', 'Group created.');
    redirectTo('/instructor/groups.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_username'], $_POST['assign_group_id'])) {
    assignStudentToGroup($_POST['assign_username'], (int) $_POST['assign_group_id']);
    flashMessage('success', 'Student assigned to group.');
    redirectTo('/instructor/groups.php');
}

$groups = loadRecords(DATA_DIR . '/groups.json');
renderPageStart('Group Management', $user);
?>
<section class="card">
    <form method="post">
        <label>New Group Name <input name="group_name" required></label>
        <button type="submit">Create Group</button>
    </form>
</section>
<section class="card">
    <form method="post">
        <label>Student
            <select name="assign_username">
                <?php foreach ($users as $student): ?>
                    <option value="<?= htmlspecialchars($student['username']) ?>"><?= htmlspecialchars($student['display_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Group
            <select name="assign_group_id">
                <?php foreach ($groups as $group): ?>
                    <option value="<?= (int) $group['id'] ?>"><?= htmlspecialchars($group['group_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <button type="submit">Assign Student</button>
    </form>
</section>
<?php renderPageEnd(); ?>
```

- [ ] **Step 4: Run the import/group test and manual page check**

Run: `php tests/student_import_test.php`  
Expected: `OK student_import_test`

Run: `php -S 127.0.0.1:8000 -t public`  
Expected: Instructor can import `tests/fixtures/students.csv` and create a group from the browser.

- [ ] **Step 5: Commit import and grouping**

```bash
git add src/csv_tools.php src/groups.php public/instructor/import_students.php public/instructor/groups.php tests/fixtures/students.csv tests/student_import_test.php
git commit -m "feat: add student import and group management"
```

## Task 4: Add Question Bank CRUD

**Files:**
- Create: `src/questions.php`
- Create: `public/instructor/questions.php`
- Test: `tests/questions_test.php`

- [ ] **Step 1: Write the failing question bank test**

```php
// tests/questions_test.php
<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../src/questions.php';

$tempDir = makeTempDir('questions');
$questionsFile = $tempDir . '/questions.json';
saveRecords($questionsFile, []);

$question = saveQuestion([
    'question_text' => 'What is the capital of France?',
    'topic' => 'Geography',
    'options' => ['Paris', 'London', 'Berlin', 'Rome'],
    'correct_answer' => 'Paris',
], $questionsFile);

assertSameValue('Paris', $question['correct_answer'], 'Question should keep its correct answer.');

$updated = updateQuestion((int) $question['id'], [
    'question_text' => 'What is the capital of Germany?',
    'topic' => 'Geography',
    'options' => ['Paris', 'London', 'Berlin', 'Rome'],
    'correct_answer' => 'Berlin',
], $questionsFile);

assertSameValue('Berlin', $updated['correct_answer'], 'Question update should overwrite the correct answer.');
deleteQuestion((int) $question['id'], $questionsFile);
assertSameValue([], loadRecords($questionsFile), 'Question delete should remove the record.');

echo "OK questions_test\n";
```

- [ ] **Step 2: Run the question test to confirm it fails**

Run: `php tests/questions_test.php`  
Expected: FAIL because question helpers do not exist yet.

- [ ] **Step 3: Implement question CRUD and the instructor question page**

```php
// src/questions.php
<?php
declare(strict_types=1);

require_once __DIR__ . '/storage.php';

function questionsFile(): string
{
    return DATA_DIR . '/questions.json';
}

function validateQuestionInput(array $input): void
{
    if (count($input['options']) !== 4) {
        throw new InvalidArgumentException('Each MCQ must have exactly four options.');
    }

    if (! in_array($input['correct_answer'], $input['options'], true)) {
        throw new InvalidArgumentException('Correct answer must match one of the four options.');
    }
}

function saveQuestion(array $input, ?string $override = null): array
{
    validateQuestionInput($input);
    $file = $override ?? questionsFile();
    $questions = loadRecords($file);
    $question = [
        'id' => nextId($questions),
        'question_text' => trim($input['question_text']),
        'topic' => trim($input['topic']),
        'options' => array_map('trim', $input['options']),
        'correct_answer' => trim($input['correct_answer']),
    ];
    $questions[] = $question;
    saveRecords($file, $questions);
    return $question;
}

function updateQuestion(int $id, array $input, ?string $override = null): array
{
    validateQuestionInput($input);
    $file = $override ?? questionsFile();
    $questions = loadRecords($file);
    foreach ($questions as &$question) {
        if ((int) $question['id'] === $id) {
            $question['question_text'] = trim($input['question_text']);
            $question['topic'] = trim($input['topic']);
            $question['options'] = array_map('trim', $input['options']);
            $question['correct_answer'] = trim($input['correct_answer']);
            saveRecords($file, $questions);
            return $question;
        }
    }
    throw new RuntimeException('Question not found.');
}

function deleteQuestion(int $id, ?string $override = null): void
{
    $file = $override ?? questionsFile();
    $questions = array_values(array_filter(loadRecords($file), static fn(array $question): bool => (int) $question['id'] !== $id));
    saveRecords($file, $questions);
}
```

```php
// public/instructor/questions.php
<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/auth.php';
require_once __DIR__ . '/../../src/questions.php';

$user = requireRole('instructor');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['question_text'])) {
    $options = [
        trim($_POST['option_1']),
        trim($_POST['option_2']),
        trim($_POST['option_3']),
        trim($_POST['option_4']),
    ];
    $correctAnswer = $options[(int) ($_POST['correct_option_index'] ?? -1)] ?? '';
    saveQuestion([
        'question_text' => $_POST['question_text'],
        'topic' => $_POST['topic'],
        'options' => $options,
        'correct_answer' => $correctAnswer,
    ]);
    flashMessage('success', 'Question saved.');
    redirectTo('/instructor/questions.php');
}

if (isset($_GET['delete'])) {
    deleteQuestion((int) $_GET['delete']);
    flashMessage('success', 'Question deleted.');
    redirectTo('/instructor/questions.php');
}

$questions = loadRecords(DATA_DIR . '/questions.json');
renderPageStart('Question Bank', $user);
?>
<section class="card">
    <form method="post">
        <label>Question <textarea name="question_text" required></textarea></label>
        <label>Topic <input name="topic"></label>
        <label>Option 1 <input name="option_1" required></label>
        <label>Option 2 <input name="option_2" required></label>
        <label>Option 3 <input name="option_3" required></label>
        <label>Option 4 <input name="option_4" required></label>
        <label>Correct Answer
            <select name="correct_option_index" required>
                <option value="0">Option 1</option>
                <option value="1">Option 2</option>
                <option value="2">Option 3</option>
                <option value="3">Option 4</option>
            </select>
        </label>
        <button type="submit">Save Question</button>
    </form>
</section>
<section class="card">
    <table>
        <thead><tr><th>ID</th><th>Question</th><th>Topic</th><th>Correct Answer</th><th>Action</th></tr></thead>
        <tbody>
        <?php foreach ($questions as $question): ?>
            <tr>
                <td><?= (int) $question['id'] ?></td>
                <td><?= htmlspecialchars($question['question_text']) ?></td>
                <td><?= htmlspecialchars($question['topic']) ?></td>
                <td><?= htmlspecialchars($question['correct_answer']) ?></td>
                <td><a href="/instructor/questions.php?delete=<?= (int) $question['id'] ?>">Delete</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php renderPageEnd(); ?>
```

- [ ] **Step 4: Run the question test and syntax check**

Run: `php tests/questions_test.php`  
Expected: `OK questions_test`

Run: `php -l public/instructor/questions.php`  
Expected: `No syntax errors detected in public/instructor/questions.php`

- [ ] **Step 5: Commit the question bank**

```bash
git add src/questions.php public/instructor/questions.php tests/questions_test.php
git commit -m "feat: add question bank CRUD"
```

## Task 5: Add Quiz Creation, Group Assignment, and Publishing

**Files:**
- Create: `src/quizzes.php`
- Create: `public/instructor/quizzes.php`
- Modify: `public/instructor/dashboard.php`
- Test: `tests/quizzes_test.php`

- [ ] **Step 1: Write the failing quiz workflow test**

```php
// tests/quizzes_test.php
<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../src/quizzes.php';

$tempDir = makeTempDir('quizzes');
$quizzesFile = $tempDir . '/quizzes.json';
saveRecords($quizzesFile, []);

$quiz = saveQuiz([
    'title' => 'Week 1 Quiz',
    'description' => 'Intro quiz',
    'time_limit_minutes' => 20,
    'assigned_group_ids' => [1, 2],
    'question_ids' => [3, 4],
    'status' => 'draft',
], $quizzesFile);

assertSameValue('draft', $quiz['status'], 'New quiz should start in draft.');

$published = updateQuizStatus((int) $quiz['id'], 'published', $quizzesFile);
assertSameValue('published', $published['status'], 'Quiz status should update to published.');

$visible = listQuizzesForGroup(2, $quizzesFile);
assertSameValue(1, count($visible), 'Assigned group should see the published quiz.');
assertSameValue(0, count(listQuizzesForGroup(3, $quizzesFile)), 'Unassigned groups should not see the quiz.');

echo "OK quizzes_test\n";
```

- [ ] **Step 2: Run the quiz test to confirm it fails**

Run: `php tests/quizzes_test.php`  
Expected: FAIL because quiz helpers do not exist yet.

- [ ] **Step 3: Implement quiz storage and the instructor quiz page**

```php
// src/quizzes.php
<?php
declare(strict_types=1);

require_once __DIR__ . '/storage.php';

function quizzesFile(): string
{
    return DATA_DIR . '/quizzes.json';
}

function saveQuiz(array $input, ?string $override = null): array
{
    $file = $override ?? quizzesFile();
    $quizzes = loadRecords($file);
    $quiz = [
        'id' => nextId($quizzes),
        'title' => trim($input['title']),
        'description' => trim($input['description']),
        'time_limit_minutes' => (int) $input['time_limit_minutes'],
        'assigned_group_ids' => array_map('intval', $input['assigned_group_ids']),
        'question_ids' => array_map('intval', $input['question_ids']),
        'status' => $input['status'] ?? 'draft',
    ];
    $quizzes[] = $quiz;
    saveRecords($file, $quizzes);
    return $quiz;
}

function updateQuizStatus(int $id, string $status, ?string $override = null): array
{
    $file = $override ?? quizzesFile();
    $quizzes = loadRecords($file);
    foreach ($quizzes as &$quiz) {
        if ((int) $quiz['id'] === $id) {
            $quiz['status'] = $status;
            saveRecords($file, $quizzes);
            return $quiz;
        }
    }
    throw new RuntimeException('Quiz not found.');
}

function listQuizzesForGroup(int $groupId, ?string $override = null): array
{
    return array_values(array_filter(
        loadRecords($override ?? quizzesFile()),
        static fn(array $quiz): bool => $quiz['status'] === 'published' && in_array($groupId, $quiz['assigned_group_ids'], true)
    ));
}
```

```php
// public/instructor/quizzes.php
<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/auth.php';
require_once __DIR__ . '/../../src/quizzes.php';

$user = requireRole('instructor');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'])) {
    saveQuiz([
        'title' => $_POST['title'],
        'description' => $_POST['description'],
        'time_limit_minutes' => $_POST['time_limit_minutes'],
        'assigned_group_ids' => $_POST['assigned_group_ids'] ?? [],
        'question_ids' => $_POST['question_ids'] ?? [],
        'status' => $_POST['status'] ?? 'draft',
    ]);
    flashMessage('success', 'Quiz saved.');
    redirectTo('/instructor/quizzes.php');
}

if (isset($_GET['publish'])) {
    updateQuizStatus((int) $_GET['publish'], 'published');
    flashMessage('success', 'Quiz published.');
    redirectTo('/instructor/quizzes.php');
}

$groups = loadRecords(DATA_DIR . '/groups.json');
$questions = loadRecords(DATA_DIR . '/questions.json');
$quizzes = loadRecords(DATA_DIR . '/quizzes.json');

renderPageStart('Quiz Management', $user);
?>
<section class="card">
    <form method="post">
        <label>Quiz Title <input name="title" required></label>
        <label>Description <textarea name="description"></textarea></label>
        <label>Time Limit (minutes) <input name="time_limit_minutes" type="number" min="1" required></label>
        <fieldset>
            <legend>Assign to Groups</legend>
            <?php foreach ($groups as $group): ?>
                <label><input type="checkbox" name="assigned_group_ids[]" value="<?= (int) $group['id'] ?>"> <?= htmlspecialchars($group['group_name']) ?></label>
            <?php endforeach; ?>
        </fieldset>
        <fieldset>
            <legend>Select Questions</legend>
            <?php foreach ($questions as $question): ?>
                <label><input type="checkbox" name="question_ids[]" value="<?= (int) $question['id'] ?>"> <?= htmlspecialchars($question['question_text']) ?></label>
            <?php endforeach; ?>
        </fieldset>
        <button type="submit">Save Draft</button>
    </form>
</section>
<section class="card">
    <table>
        <thead><tr><th>Quiz</th><th>Status</th><th>Time Limit</th><th>Action</th></tr></thead>
        <tbody>
        <?php foreach ($quizzes as $quiz): ?>
            <tr>
                <td><?= htmlspecialchars($quiz['title']) ?></td>
                <td><?= htmlspecialchars($quiz['status']) ?></td>
                <td><?= (int) $quiz['time_limit_minutes'] ?> minutes</td>
                <td><a href="/instructor/quizzes.php?publish=<?= (int) $quiz['id'] ?>">Publish</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php renderPageEnd(); ?>
```

```php
// public/instructor/dashboard.php additions
$quizCount = count(loadRecords(DATA_DIR . '/quizzes.json'));
$submissionCount = count(loadRecords(DATA_DIR . '/submissions.json'));
```

- [ ] **Step 4: Run the quiz test and browser smoke test**

Run: `php tests/quizzes_test.php`  
Expected: `OK quizzes_test`

Run: `php -S 127.0.0.1:8000 -t public`  
Expected: Instructor can save a draft quiz and publish it from `/instructor/quizzes.php`.

- [ ] **Step 5: Commit quiz management**

```bash
git add src/quizzes.php public/instructor/quizzes.php public/instructor/dashboard.php tests/quizzes_test.php
git commit -m "feat: add quiz creation and publishing"
```

## Task 6: Add Student Quiz Taking, Timer, and Automatic Grading

**Files:**
- Create: `src/submissions.php`
- Modify: `public/assets/app.js`
- Modify: `public/student/dashboard.php`
- Create: `public/student/quiz.php`
- Create: `public/student/result.php`
- Create: `public/student/history.php`
- Test: `tests/submissions_test.php`

- [ ] **Step 1: Write the failing submission test**

```php
// tests/submissions_test.php
<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../src/submissions.php';

$quiz = [
    'id' => 1,
    'question_ids' => [10, 11],
];

$questions = [
    10 => ['id' => 10, 'correct_answer' => 'Paris'],
    11 => ['id' => 11, 'correct_answer' => '4'],
];

$result = scoreSubmission($quiz, $questions, [
    10 => 'Paris',
    11 => '5',
]);

assertSameValue(50, $result['score'], 'One correct answer out of two should score 50.');
assertSameValue(false, $result['details'][11]['is_correct'], 'Incorrect answer should be flagged.');

$tempDir = makeTempDir('submissions');
$submissionsFile = $tempDir . '/submissions.json';
saveRecords($submissionsFile, []);
assertTrueValue(hasStudentSubmitted(1, 7, $submissionsFile) === false, 'No submission should exist yet.');

saveSubmissionRecord([
    'quiz_id' => 1,
    'student_id' => 7,
    'score' => 50,
    'answers' => [10 => 'Paris', 11 => '5'],
    'details' => $result['details'],
], $submissionsFile);

assertTrueValue(hasStudentSubmitted(1, 7, $submissionsFile), 'Saved submission should block a second attempt.');

echo "OK submissions_test\n";
```

- [ ] **Step 2: Run the submission test to confirm it fails**

Run: `php tests/submissions_test.php`  
Expected: FAIL because submission helpers do not exist yet.

- [ ] **Step 3: Implement scoring rules and student quiz pages**

```php
// src/submissions.php
<?php
declare(strict_types=1);

require_once __DIR__ . '/storage.php';

function submissionsFile(): string
{
    return DATA_DIR . '/submissions.json';
}

function scoreSubmission(array $quiz, array $questionsById, array $answers): array
{
    $details = [];
    $correctCount = 0;

    foreach ($quiz['question_ids'] as $questionId) {
        $question = $questionsById[$questionId];
        $studentAnswer = $answers[$questionId] ?? null;
        $isCorrect = $studentAnswer === $question['correct_answer'];
        $details[$questionId] = [
            'student_answer' => $studentAnswer,
            'correct_answer' => $question['correct_answer'],
            'is_correct' => $isCorrect,
        ];
        if ($isCorrect) {
            $correctCount++;
        }
    }

    $score = (int) round(($correctCount / max(count($quiz['question_ids']), 1)) * 100);

    return ['score' => $score, 'details' => $details];
}

function hasStudentSubmitted(int $quizId, int $studentId, ?string $override = null): bool
{
    foreach (loadRecords($override ?? submissionsFile()) as $submission) {
        if ((int) $submission['quiz_id'] === $quizId && (int) $submission['student_id'] === $studentId) {
            return true;
        }
    }
    return false;
}

function saveSubmissionRecord(array $input, ?string $override = null): array
{
    $file = $override ?? submissionsFile();
    $submissions = loadRecords($file);
    $record = [
        'id' => nextId($submissions),
        'quiz_id' => (int) $input['quiz_id'],
        'student_id' => (int) $input['student_id'],
        'answers' => $input['answers'],
        'details' => $input['details'],
        'score' => (int) $input['score'],
        'submitted_at' => date('c'),
    ];
    $submissions[] = $record;
    saveRecords($file, $submissions);
    return $record;
}
```

```php
// public/student/dashboard.php
<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/auth.php';
require_once __DIR__ . '/../../src/quizzes.php';

$user = requireRole('student');
$availableQuizzes = listQuizzesForGroup((int) $user['group_id']);

renderPageStart('Student Dashboard', $user);
?>
<section class="card">
    <table>
        <thead><tr><th>Quiz</th><th>Time Limit</th><th>Action</th></tr></thead>
        <tbody>
        <?php foreach ($availableQuizzes as $quiz): ?>
            <tr>
                <td><?= htmlspecialchars($quiz['title']) ?></td>
                <td><?= (int) $quiz['time_limit_minutes'] ?> minutes</td>
                <td><a href="/student/quiz.php?id=<?= (int) $quiz['id'] ?>">Start</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php renderPageEnd(); ?>
```

```php
// public/student/quiz.php
<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/auth.php';
require_once __DIR__ . '/../../src/quizzes.php';
require_once __DIR__ . '/../../src/submissions.php';

$user = requireRole('student');
$quizId = (int) ($_GET['id'] ?? $_POST['quiz_id'] ?? 0);
$quizzes = loadRecords(DATA_DIR . '/quizzes.json');
$questions = loadRecords(DATA_DIR . '/questions.json');

$quiz = null;
foreach ($quizzes as $record) {
    if ((int) $record['id'] === $quizId) {
        $quiz = $record;
        break;
    }
}

if ($quiz === null || ! in_array((int) $user['group_id'], $quiz['assigned_group_ids'], true) || $quiz['status'] !== 'published') {
    flashMessage('error', 'Quiz is not available.');
    redirectTo('/student/dashboard.php');
}

if (hasStudentSubmitted($quizId, (int) $user['id'])) {
    flashMessage('error', 'You have already submitted this quiz.');
    redirectTo('/student/history.php');
}

$questionsById = [];
foreach ($questions as $question) {
    $questionsById[(int) $question['id']] = $question;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = scoreSubmission($quiz, $questionsById, $_POST['answers'] ?? []);
    $submission = saveSubmissionRecord([
        'quiz_id' => $quizId,
        'student_id' => (int) $user['id'],
        'answers' => $_POST['answers'] ?? [],
        'details' => $result['details'],
        'score' => $result['score'],
    ]);
    redirectTo('/student/result.php?id=' . (int) $submission['id']);
}

renderPageStart('Take Quiz', $user);
?>
<section class="card">
    <p id="quiz-timer" data-timer-minutes="<?= (int) $quiz['time_limit_minutes'] ?>">Time remaining: --:--</p>
    <form method="post" data-quiz-form>
        <input type="hidden" name="quiz_id" value="<?= (int) $quiz['id'] ?>">
        <?php foreach ($quiz['question_ids'] as $questionId): $question = $questionsById[$questionId]; $options = $question['options']; shuffle($options); ?>
            <fieldset class="card">
                <legend><?= htmlspecialchars($question['question_text']) ?></legend>
                <?php foreach ($options as $option): ?>
                    <label><input type="radio" name="answers[<?= (int) $questionId ?>]" value="<?= htmlspecialchars($option) ?>" required> <?= htmlspecialchars($option) ?></label>
                <?php endforeach; ?>
            </fieldset>
        <?php endforeach; ?>
        <button type="submit">Submit Quiz</button>
    </form>
</section>
<?php renderPageEnd(); ?>
```

```php
// public/student/result.php
<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/auth.php';

$user = requireRole('student');
$submissionId = (int) ($_GET['id'] ?? 0);
$submissions = loadRecords(DATA_DIR . '/submissions.json');

$submission = null;
foreach ($submissions as $record) {
    if ((int) $record['id'] === $submissionId && (int) $record['student_id'] === (int) $user['id']) {
        $submission = $record;
        break;
    }
}

if ($submission === null) {
    flashMessage('error', 'Result not found.');
    redirectTo('/student/history.php');
}

renderPageStart('Quiz Result', $user);
?>
<section class="card">
    <p>Score: <?= (int) $submission['score'] ?></p>
    <table>
        <thead><tr><th>Question ID</th><th>Your Answer</th><th>Correct Answer</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($submission['details'] as $questionId => $detail): ?>
            <tr>
                <td><?= (int) $questionId ?></td>
                <td><?= htmlspecialchars((string) ($detail['student_answer'] ?? '')) ?></td>
                <td><?= htmlspecialchars((string) $detail['correct_answer']) ?></td>
                <td><?= $detail['is_correct'] ? 'Correct' : 'Incorrect' ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php renderPageEnd(); ?>
```

```php
// public/student/history.php
<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/auth.php';

$user = requireRole('student');
$history = array_values(array_filter(
    loadRecords(DATA_DIR . '/submissions.json'),
    static fn(array $submission): bool => (int) $submission['student_id'] === (int) $user['id']
));

renderPageStart('Quiz History', $user);
?>
<section class="card">
    <table>
        <thead><tr><th>Quiz ID</th><th>Score</th><th>Submitted At</th><th>View</th></tr></thead>
        <tbody>
        <?php foreach ($history as $submission): ?>
            <tr>
                <td><?= (int) $submission['quiz_id'] ?></td>
                <td><?= (int) $submission['score'] ?></td>
                <td><?= htmlspecialchars($submission['submitted_at']) ?></td>
                <td><a href="/student/result.php?id=<?= (int) $submission['id'] ?>">Open</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php renderPageEnd(); ?>
```

```js
// public/assets/app.js
document.addEventListener('DOMContentLoaded', () => {
  const timer = document.querySelector('#quiz-timer');
  const quizForm = document.querySelector('[data-quiz-form]');

  if (timer && quizForm) {
    let remainingSeconds = Number(timer.dataset.timerMinutes || '0') * 60;

    const renderTimer = () => {
      const minutes = String(Math.floor(remainingSeconds / 60)).padStart(2, '0');
      const seconds = String(remainingSeconds % 60).padStart(2, '0');
      timer.textContent = `Time remaining: ${minutes}:${seconds}`;
    };

    renderTimer();
    const intervalId = window.setInterval(() => {
      remainingSeconds -= 1;
      renderTimer();
      if (remainingSeconds <= 0) {
        window.clearInterval(intervalId);
        quizForm.submit();
      }
    }, 1000);

    quizForm.addEventListener('submit', (event) => {
      const questionSets = quizForm.querySelectorAll('fieldset');
      const missing = Array.from(questionSets).filter((fieldset) => !fieldset.querySelector('input[type="radio"]:checked'));
      if (missing.length > 0) {
        event.preventDefault();
        alert('Please answer every question before submitting.');
      }
    });
  }
});
```

- [ ] **Step 4: Run the submission test and student flow smoke check**

Run: `php tests/submissions_test.php`  
Expected: `OK submissions_test`

Run: `php -S 127.0.0.1:8000 -t public`  
Expected: Student can open an assigned quiz, see a countdown timer, submit once, and land on a result page.

- [ ] **Step 5: Commit quiz taking and grading**

```bash
git add src/submissions.php public/assets/app.js public/student/dashboard.php public/student/quiz.php public/student/result.php public/student/history.php tests/submissions_test.php
git commit -m "feat: add student quiz taking and grading"
```

## Task 7: Add Instructor Results, Statistics, CSV Export, and AJAX

**Files:**
- Create: `src/statistics.php`
- Modify: `src/csv_tools.php`
- Create: `public/instructor/results.php`
- Create: `public/instructor/results_data.php`
- Create: `public/instructor/export_results.php`
- Modify: `public/assets/app.js`
- Test: `tests/statistics_test.php`

- [ ] **Step 1: Write the failing statistics and export test**

```php
// tests/statistics_test.php
<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../src/statistics.php';
require_once __DIR__ . '/../src/csv_tools.php';

$quiz = [
    'id' => 1,
    'title' => 'Week 1 Quiz',
    'question_ids' => [10, 11],
];

$questionsById = [
    10 => ['id' => 10, 'question_text' => 'Capital of France?', 'correct_answer' => 'Paris'],
    11 => ['id' => 11, 'question_text' => '2+2?', 'correct_answer' => '4'],
];

$submissions = [
    ['id' => 1, 'quiz_id' => 1, 'student_id' => 7, 'score' => 100, 'details' => [10 => ['is_correct' => true], 11 => ['is_correct' => true]]],
    ['id' => 2, 'quiz_id' => 1, 'student_id' => 8, 'score' => 50, 'details' => [10 => ['is_correct' => true], 11 => ['is_correct' => false]]],
];

$summary = summarizeQuizStatistics($quiz, $questionsById, $submissions);
assertSameValue(75, $summary['average_score'], 'Average score should be rounded from quiz submissions.');
assertSameValue(100, $summary['per_question_accuracy'][10], 'Question 10 should show 100 percent accuracy.');
assertSameValue(50, $summary['per_question_accuracy'][11], 'Question 11 should show 50 percent accuracy.');

$csv = buildQuizResultsCsv($quiz, $submissions);
assertTrueValue(str_contains($csv, 'student_id,score'), 'Export CSV should include a header row.');
assertTrueValue(str_contains($csv, "7,100"), 'Export CSV should contain student score rows.');

echo "OK statistics_test\n";
```

- [ ] **Step 2: Run the statistics test to confirm it fails**

Run: `php tests/statistics_test.php`  
Expected: FAIL because statistics and export helpers do not exist yet.

- [ ] **Step 3: Implement result summaries, export, and AJAX endpoints**

```php
// src/statistics.php
<?php
declare(strict_types=1);

function summarizeQuizStatistics(array $quiz, array $questionsById, array $submissions): array
{
    $quizSubmissions = array_values(array_filter(
        $submissions,
        static fn(array $submission): bool => (int) $submission['quiz_id'] === (int) $quiz['id']
    ));

    $averageScore = $quizSubmissions === []
        ? 0
        : (int) round(array_sum(array_column($quizSubmissions, 'score')) / count($quizSubmissions));

    $perQuestionAccuracy = [];
    foreach ($quiz['question_ids'] as $questionId) {
        $correctCount = 0;
        foreach ($quizSubmissions as $submission) {
            if (($submission['details'][$questionId]['is_correct'] ?? false) === true) {
                $correctCount++;
            }
        }
        $perQuestionAccuracy[$questionId] = $quizSubmissions === []
            ? 0
            : (int) round(($correctCount / count($quizSubmissions)) * 100);
    }

    return [
        'quiz_id' => (int) $quiz['id'],
        'average_score' => $averageScore,
        'submission_count' => count($quizSubmissions),
        'per_question_accuracy' => $perQuestionAccuracy,
    ];
}
```

```php
// src/csv_tools.php addition
function buildQuizResultsCsv(array $quiz, array $submissions): string
{
    $rows = ["student_id,score,submitted_at"];
    foreach ($submissions as $submission) {
        if ((int) $submission['quiz_id'] !== (int) $quiz['id']) {
            continue;
        }
        $rows[] = implode(',', [
            (int) $submission['student_id'],
            (int) $submission['score'],
            $submission['submitted_at'] ?? '',
        ]);
    }
    return implode(PHP_EOL, $rows) . PHP_EOL;
}
```

```php
// public/instructor/results_data.php
<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/auth.php';
require_once __DIR__ . '/../../src/statistics.php';

requireRole('instructor');

$quizId = (int) ($_GET['quiz_id'] ?? 0);
$quizzes = loadRecords(DATA_DIR . '/quizzes.json');
$questions = loadRecords(DATA_DIR . '/questions.json');
$submissions = loadRecords(DATA_DIR . '/submissions.json');

$quiz = null;
foreach ($quizzes as $record) {
    if ((int) $record['id'] === $quizId) {
        $quiz = $record;
        break;
    }
}

$questionsById = [];
foreach ($questions as $question) {
    $questionsById[(int) $question['id']] = $question;
}

header('Content-Type: application/json');
echo json_encode($quiz === null ? ['error' => 'Quiz not found'] : summarizeQuizStatistics($quiz, $questionsById, $submissions));
```

```php
// public/instructor/export_results.php
<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/auth.php';
require_once __DIR__ . '/../../src/csv_tools.php';

requireRole('instructor');

$quizId = (int) ($_GET['quiz_id'] ?? 0);
$quizzes = loadRecords(DATA_DIR . '/quizzes.json');
$submissions = loadRecords(DATA_DIR . '/submissions.json');

$quiz = null;
foreach ($quizzes as $record) {
    if ((int) $record['id'] === $quizId) {
        $quiz = $record;
        break;
    }
}

if ($quiz === null) {
    flashMessage('error', 'Quiz not found.');
    redirectTo('/instructor/results.php');
}

$csv = buildQuizResultsCsv($quiz, $submissions);
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="quiz-' . $quizId . '-results.csv"');
echo $csv;
```

```php
// public/instructor/results.php
<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/auth.php';

$user = requireRole('instructor');
$quizzes = array_values(array_filter(loadRecords(DATA_DIR . '/quizzes.json'), static fn(array $quiz): bool => $quiz['status'] === 'published'));

renderPageStart('Quiz Results', $user);
?>
<section class="card">
    <label>Select Quiz
        <select id="results-quiz-select" data-results-endpoint="/instructor/results_data.php">
            <option value="">Choose a quiz</option>
            <?php foreach ($quizzes as $quiz): ?>
                <option value="<?= (int) $quiz['id'] ?>"><?= htmlspecialchars($quiz['title']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <p><a id="results-export-link" href="#">Export Current Quiz as CSV</a></p>
</section>
<section class="card" id="results-summary">
    <p>Select a quiz to load statistics.</p>
</section>
<?php renderPageEnd(); ?>
```

```js
// public/assets/app.js addition
const resultsSelect = document.querySelector('#results-quiz-select');
const resultsSummary = document.querySelector('#results-summary');
const exportLink = document.querySelector('#results-export-link');

if (resultsSelect && resultsSummary && exportLink) {
  resultsSelect.addEventListener('change', async () => {
    if (!resultsSelect.value) {
      resultsSummary.innerHTML = '<p>Select a quiz to load statistics.</p>';
      exportLink.setAttribute('href', '#');
      return;
    }

    const response = await fetch(`${resultsSelect.dataset.resultsEndpoint}?quiz_id=${resultsSelect.value}`);
    const data = await response.json();

    exportLink.setAttribute('href', `/instructor/export_results.php?quiz_id=${resultsSelect.value}`);
    resultsSummary.innerHTML = `
      <p>Average score: ${data.average_score}</p>
      <p>Submission count: ${data.submission_count}</p>
      <ul>
        ${Object.entries(data.per_question_accuracy).map(([questionId, accuracy]) => `<li>Question ${questionId}: ${accuracy}% correct</li>`).join('')}
      </ul>
    `;
  });
}
```

- [ ] **Step 4: Run the statistics test and AJAX smoke check**

Run: `php tests/statistics_test.php`  
Expected: `OK statistics_test`

Run: `php -S 127.0.0.1:8000 -t public`  
Expected: Instructor can choose a quiz on `/instructor/results.php`, see stats update without a full page refresh, and download a CSV export.

- [ ] **Step 5: Commit results and AJAX**

```bash
git add src/statistics.php src/csv_tools.php public/instructor/results.php public/instructor/results_data.php public/instructor/export_results.php public/assets/app.js tests/statistics_test.php
git commit -m "feat: add results export and ajax statistics"
```

## Manual Verification Checklist

Run this end-to-end after Task 7:

1. Start the app with `php -S 127.0.0.1:8000 -t public`.
2. Log in as `instructor / instructor123`.
3. Import `tests/fixtures/students.csv`.
4. Create one group and assign both imported students into it.
5. Add two MCQ questions.
6. Create a quiz, assign it to the group, and publish it.
7. Log out and log in as one imported student.
8. Open the quiz, answer the questions, and submit once.
9. Verify the student result page shows the score and per-question breakdown.
10. Verify the student history page shows the submission.
11. Log back in as instructor and open the results page.
12. Use the AJAX quiz selector to confirm average score and question accuracy load without a full refresh.
13. Export the quiz results to CSV and confirm the file contains `student_id,score,submitted_at`.

## Spec Coverage Map

- Authentication and session management: Task 2
- Student CSV import and group management: Task 3
- Question bank CRUD: Task 4
- Quiz creation, publishing, and group assignment: Task 5
- Student quiz taking, timer, validation, and one submission rule: Task 6
- Auto grading, result review, and history: Task 6
- CSV export, basic statistics, and AJAX enhancement: Task 7
- Browser and demo verification: Manual Verification Checklist
