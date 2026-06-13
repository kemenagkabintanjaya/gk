<?php
declare(strict_types=1);
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/includes/layout.php';
if (!file_exists(DB_PATH)) { redirect('install.php'); }
session_boot();
require_login();

$u = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $nama = post_str('nama_lengkap');
    $lama = post_str('password_lama');
    $baru = post_str('password_baru');
    $ulang= post_str('password_ulang');

    if ($nama !== '') {
        db()->prepare('UPDATE users SET nama_lengkap = ? WHERE id = ?')->execute([$nama, (int)$u['id']]);
    }

    if ($baru !== '' || $ulang !== '') {
        $stmt = db()->prepare('SELECT password_hash FROM users WHERE id = ?');
        $stmt->execute([(int)$u['id']]);
        $hash = (string)$stmt->fetchColumn();
        if (!password_verify($lama, $hash)) {
            flash('Kata sandi lama salah.', 'error');
            redirect('profile.php');
        }
        if (strlen($baru) < 6) { flash('Kata sandi baru minimal 6 karakter.', 'error'); redirect('profile.php'); }
        if ($baru !== $ulang) { flash('Konfirmasi kata sandi tidak cocok.', 'error'); redirect('profile.php'); }
        db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([password_hash($baru, PASSWORD_DEFAULT), (int)$u['id']]);
        log_activity('change_password', 'Mengubah kata sandi sendiri');
    }
    flash('Profil berhasil diperbarui.', 'success');
    redirect('profile.php');
}

layout_header('Profil Saya', 'profile.php');
?>
<div class="sec-head"><h2>🔑 Profil Saya</h2></div>
<form class="form-card" method="post" action="profile.php">
  <?= csrf_field() ?>
  <div class="form-grid">
    <div class="form-group">
      <label>Nama Lengkap</label>
      <input type="text" name="nama_lengkap" value="<?= e($u['nama_lengkap']) ?>" maxlength="120">
    </div>
    <div class="form-group">
      <label>Username</label>
      <input type="text" value="<?= e($u['username']) ?>" readonly>
    </div>
    <div class="form-group">
      <label>Peran</label>
      <input type="text" value="<?= e(role_label($u['role'])) ?>" readonly>
    </div>
    <div class="form-group">
      <label>Distrik</label>
      <input type="text" value="<?= e($u['distrik'] ?: '—') ?>" readonly>
    </div>
    <div class="form-section-title">Ubah Kata Sandi</div>
    <div class="form-group">
      <label>Kata Sandi Lama</label>
      <input type="password" name="password_lama" autocomplete="current-password">
    </div>
    <div class="form-group"></div>
    <div class="form-group">
      <label>Kata Sandi Baru</label>
      <input type="password" name="password_baru" minlength="6" autocomplete="new-password">
    </div>
    <div class="form-group">
      <label>Ulangi Kata Sandi Baru</label>
      <input type="password" name="password_ulang" minlength="6" autocomplete="new-password">
    </div>
  </div>
  <div class="form-actions">
    <button type="submit" class="btn-link">💾 Simpan Perubahan</button>
  </div>
</form>
<?php layout_footer();
