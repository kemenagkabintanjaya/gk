<?php
declare(strict_types=1);
require_once __DIR__ . '/lib/auth.php';
session_boot();
logout_user();
redirect('login.php');
