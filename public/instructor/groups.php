<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/auth.php';
require_once __DIR__ . '/../../src/groups.php';

$user = requireRole('instructor');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_group') {
        saveGroup($_POST);
        flashMessage('success', 'Group created.');
        redirectTo('/instructor/groups.php');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_group') {
        updateGroup((int) $_POST['group_id'], $_POST);
        flashMessage('success', 'Group updated.');
        redirectTo('/instructor/groups.php');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'assign_student') {
        assignStudentToGroup((string) $_POST['assign_username'], $_POST['assign_group_id'] === '' ? null : (int) $_POST['assign_group_id']);
        flashMessage('success', 'Student assignment updated.');
        redirectTo('/instructor/groups.php');
    }

    if (isset($_GET['delete'])) {
        deleteGroup((int) $_GET['delete']);
        flashMessage('success', 'Group deleted.');
        redirectTo('/instructor/groups.php');
    }
} catch (Throwable $error) {
    flashMessage('error', $error->getMessage());
    redirectTo('/instructor/groups.php');
}

$groups = loadRecords(DATA_DIR . '/groups.json');
$students = array_values(array_filter(loadRecords(DATA_DIR . '/users.json'), static fn(array $record): bool => ($record['role'] ?? '') === 'student'));

renderPageStart('Group Management', $user);
?>
<section class="card">
    <h2>Create Group</h2>
    <form method="post">
        <input type="hidden" name="action" value="create_group">
        <label>Group Name <input name="group_name" required></label>
        <button type="submit">Create Group</button>
    </form>
</section>
<section class="card">
    <h2>Assign Student</h2>
    <form method="post">
        <input type="hidden" name="action" value="assign_student">
        <label>Student
            <select name="assign_username" required>
                <?php foreach ($students as $student): ?>
                    <option value="<?= h($student['username']) ?>"><?= h($student['display_name']) ?> (<?= h($student['username']) ?>)</option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Group
            <select name="assign_group_id">
                <option value="">Unassigned</option>
                <?php foreach ($groups as $group): ?>
                    <option value="<?= (int) $group['id'] ?>"><?= h($group['group_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <button type="submit">Assign Student</button>
    </form>
</section>
<section class="card">
    <h2>Groups</h2>
    <table>
        <thead><tr><th>ID</th><th>Name</th><th>Rename</th><th>Action</th></tr></thead>
        <tbody>
        <?php foreach ($groups as $group): ?>
            <tr>
                <td><?= (int) $group['id'] ?></td>
                <td><?= h($group['group_name']) ?></td>
                <td>
                    <form method="post">
                        <input type="hidden" name="action" value="update_group">
                        <input type="hidden" name="group_id" value="<?= (int) $group['id'] ?>">
                        <input name="group_name" value="<?= h($group['group_name']) ?>" required>
                        <button type="submit">Save</button>
                    </form>
                </td>
                <td><a href="/instructor/groups.php?delete=<?= (int) $group['id'] ?>">Delete</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<section class="card">
    <h2>Students</h2>
    <table>
        <thead><tr><th>Username</th><th>Name</th><th>Group</th></tr></thead>
        <tbody>
        <?php foreach ($students as $student): ?>
            <tr>
                <td><?= h($student['username']) ?></td>
                <td><?= h($student['display_name']) ?></td>
                <td><?= h(groupNameById($groups, $student['group_id'] === null ? null : (int) $student['group_id'])) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php renderPageEnd(); ?>
