<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../src/auth.php';

$tempDir = makeTempDir('auth');
$usersFile = $tempDir . '/users.json';

saveRecords($usersFile, [[
    'id' => 1,
    'username' => 'teacher',
    'display_name' => 'Main Teacher',
    'password_hash' => password_hash('secret123', PASSWORD_DEFAULT),
    'role' => 'instructor',
    'group_id' => null,
]]);

$user = attemptLogin('teacher', 'secret123', $usersFile);

assertSameValue('instructor', $user['role'], 'Successful login should return the matching user.');
assertTrueValue(attemptLogin('teacher', 'wrong-password', $usersFile) === null, 'Wrong password should fail.');
assertTrueValue(userHasRole($user, 'instructor'), 'Instructor should satisfy instructor role check.');

echo "OK auth_test\n";
