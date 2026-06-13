<?php
declare(strict_types=1);
require_once __DIR__ . '/lib/auth.php';

if (!file_exists(DB_PATH)) {
    redirect('install.php');
}
session_boot();
redirect(is_logged_in() ? 'dashboard.php' : 'login.php');
