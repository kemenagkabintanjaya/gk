<?php
declare(strict_types=1);
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/includes/layout.php';

if (!file_exists(DB_PATH)) {
    redirect('install.php');
}
session_boot();

if (is_logged_in()) {
    redirect('dashboard.php');
}

$error = '';
$now = time();
$att = $_SESSION['login_attempts'] ?? ['count' => 0, 'first' => $now];
// reset jendela bila sudah lewat
if ($now - (int)$att['first'] > LOGIN_LOCK_SECONDS) {
    $att = ['count' => 0, 'first' => $now];
}
$locked = ($att['count'] >= LOGIN_MAX_ATTEMPTS) && ($now - (int)$att['first'] <= LOGIN_LOCK_SECONDS);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if ($locked) {
        $sisa = LOGIN_LOCK_SECONDS - ($now - (int)$att['first']);
        $error = 'Terlalu banyak percobaan gagal. Coba lagi dalam ' . ceil($sisa / 60) . ' menit.';
    } else {
        $username = post_str('username');
        $password = (string)($_POST['password'] ?? '');
        if ($username !== '' && login_user($username, $password)) {
            unset($_SESSION['login_attempts']);
            redirect('dashboard.php');
        }
        $att['count']++;
        $_SESSION['login_attempts'] = $att;
        $error = 'Username atau kata sandi salah, atau akun tidak aktif.';
        log_activity('login_gagal', 'username=' . $username);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Masuk — <?= e(APP_NAME) ?></title>
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body class="login-body">
  <div class="login-card">
    <div class="login-head">
      <?= kemenag_seal() ?>
      <h1>Sistem Pendataan Gereja Kristen</h1>
      <p>BIMBINGAN MASYARAKAT KRISTEN · KEMENAG KAB. INTAN JAYA</p>
    </div>
    <form class="login-form" method="post" action="login.php" autocomplete="off">
      <?php if ($error): ?><div class="login-err"><?= e($error) ?></div><?php endif; ?>
      <?= csrf_field() ?>
      <div class="form-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required autofocus value="<?= e($_POST['username'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label for="password">Kata Sandi</label>
        <input type="password" id="password" name="password" required>
      </div>
      <button type="submit" class="login-btn">Masuk</button>
    </form>
    <div class="login-foot">Kantor Kementerian Agama Kabupaten Intan Jaya · Papua Tengah</div>
  </div>
</body>
</html>
