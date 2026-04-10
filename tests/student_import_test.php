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

$updatedGroup = updateGroup((int) $group['id'], ['group_name' => 'Group A'], $groupsFile);
assertSameValue('Group A', $updatedGroup['group_name'], 'Group update should save the new name.');

echo "OK student_import_test\n";
