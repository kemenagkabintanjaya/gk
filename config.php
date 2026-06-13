<?php
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Konfigurasi Aplikasi
|--------------------------------------------------------------------------
| Sistem Pendataan Gereja Kristen — Kemenag Kabupaten Intan Jaya
*/

define('APP_NAME', 'Pendataan Gereja Kristen — Kemenag Intan Jaya');
define('APP_VERSION', '1.0');

define('BASE_DIR', __DIR__);
define('STORAGE_DIR', BASE_DIR . '/storage');
define('DB_PATH', STORAGE_DIR . '/gereja.sqlite');

// Zona waktu Papua Tengah (WIT)
date_default_timezone_set('Asia/Jayapura');

// Keamanan sesi
define('SESSION_NAME', 'GEREJA_KRISTEN_INTANJAYA_SID');
define('SESSION_LIFETIME', 60 * 60 * 4); // 4 jam idle timeout

// Batas percobaan login
define('LOGIN_MAX_ATTEMPTS', 5);
define('LOGIN_LOCK_SECONDS', 300); // 5 menit

// Error reporting (matikan display_errors di produksi)
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
