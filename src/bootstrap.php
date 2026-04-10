<?php
declare(strict_types=1);

define('PROJECT_ROOT', dirname(__DIR__));
define('SRC_DIR', PROJECT_ROOT . '/src');
define('DATA_DIR', PROJECT_ROOT . '/data');

require_once SRC_DIR . '/storage.php';
require_once SRC_DIR . '/layout.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

foreach (['users', 'groups', 'questions', 'quizzes', 'submissions'] as $name) {
    ensureJsonFile(DATA_DIR . '/' . $name . '.json', []);
}

if (! is_dir(DATA_DIR . '/export')) {
    mkdir(DATA_DIR . '/export', 0777, true);
}
