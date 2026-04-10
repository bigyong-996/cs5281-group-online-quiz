<?php
declare(strict_types=1);

require_once __DIR__ . '/storage.php';

function groupsFile(?string $override = null): string
{
    return $override ?? DATA_DIR . '/groups.json';
}

function saveGroup(array $input, ?string $override = null): array
{
    $groupName = trim((string) ($input['group_name'] ?? ''));
    if ($groupName === '') {
        throw new InvalidArgumentException('Group name is required.');
    }

    $file = groupsFile($override);
    $groups = loadRecords($file);
    $group = [
        'id' => nextId($groups),
        'group_name' => $groupName,
    ];
    $groups[] = $group;
    saveRecords($file, $groups);

    return $group;
}

function updateGroup(int $id, array $input, ?string $override = null): array
{
    $groupName = trim((string) ($input['group_name'] ?? ''));
    if ($groupName === '') {
        throw new InvalidArgumentException('Group name is required.');
    }

    $file = groupsFile($override);
    $groups = loadRecords($file);
    foreach ($groups as &$group) {
        if ((int) $group['id'] === $id) {
            $group['group_name'] = $groupName;
            saveRecords($file, $groups);
            return $group;
        }
    }

    throw new RuntimeException('Group not found.');
}

function deleteGroup(int $id, ?string $groupsOverride = null, ?string $usersOverride = null): void
{
    $groupsPath = groupsFile($groupsOverride);
    $groups = array_values(array_filter(loadRecords($groupsPath), static fn(array $group): bool => (int) $group['id'] !== $id));
    saveRecords($groupsPath, $groups);

    $usersPath = $usersOverride ?? DATA_DIR . '/users.json';
    $users = loadRecords($usersPath);
    foreach ($users as &$user) {
        if ((int) ($user['group_id'] ?? 0) === $id) {
            $user['group_id'] = null;
        }
    }
    saveRecords($usersPath, $users);
}

function assignStudentToGroup(string $username, ?int $groupId, ?string $usersOverride = null): bool
{
    $usersFile = $usersOverride ?? DATA_DIR . '/users.json';
    $users = loadRecords($usersFile);
    $assigned = false;

    foreach ($users as &$user) {
        if (($user['username'] ?? '') === $username && ($user['role'] ?? '') === 'student') {
            $user['group_id'] = $groupId;
            $assigned = true;
            break;
        }
    }

    saveRecords($usersFile, $users);
    return $assigned;
}

function groupNameById(array $groups, ?int $id): string
{
    if ($id === null) {
        return 'Unassigned';
    }

    foreach ($groups as $group) {
        if ((int) $group['id'] === $id) {
            return $group['group_name'];
        }
    }

    return 'Unknown group';
}
