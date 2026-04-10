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
