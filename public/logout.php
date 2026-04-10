<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth.php';

logoutUser();
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
flashMessage('success', 'Logged out successfully.');
redirectTo('/index.php');
