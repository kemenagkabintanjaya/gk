<?php
declare(strict_types=1);
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/includes/layout.php';
if (!file_exists(DB_PATH)) { redirect('install.php'); }
session_boot();
require_login();
require_role(['administrator']);

$id  = get_int('id');
$row = null;
if ($id > 0) {
    $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) { flash('Pengguna tidak ditemukan.', 'error'); redirect('users.php'); }
}
$distrikOpts = db()->query('SELECT nama FROM distrik ORDER BY nama')->fetchAll(PDO::FETCH_COLUMN);
function uv($row, $k, $d = '') { return $row[$k] ?? $d; }

layout_header($id ? 'Edit Pengguna' : 'Tambah Pengguna', 'users.php');
?>
<div class="sec-head">
  <h2><?= $id ? '✏️ Edit Pengguna' : '➕ Tambah Pengguna' ?></h2>
  <a href="users.php" class="btn-secondary">← Kembali</a>
</div>
<form class="form-card" method="post" action="user_save.php">
  <?= csrf_field() ?>
  <input type="hidden" name="id" value="<?= (int)$id ?>">
  <div class="form-grid">
    <div class="form-group">
      <label>Nama Lengkap <span class="required">*</span></label>
      <input type="text" name="nama_lengkap" required maxlength="120" value="<?= e(uv($row, 'nama_lengkap')) ?>">
    </div>
    <div class="form-group">
      <label>Username <span class="required">*</span></label>
      <input type="text" name="username" required maxlength="50" pattern="[a-zA-Z0-9_.]+" value="<?= e(uv($row, 'username')) ?>">
      <span class="help-text">Hanya huruf, angka, titik, dan garis bawah.</span>
    </div>
    <div class="form-group">
      <label>Peran <span class="required">*</span></label>
      <select name="role" id="role-select" required>
        <option value="administrator" <?= uv($row, 'role') === 'administrator' ? 'selected' : '' ?>>Administrator</option>
        <option value="kepala_seksi" <?= uv($row, 'role') === 'kepala_seksi' ? 'selected' : '' ?>>Kepala Seksi Kristen/Kristen</option>
        <option value="penyuluh" <?= uv($row, 'role', 'penyuluh') === 'penyuluh' ? 'selected' : '' ?>>Penyuluh</option>
      </select>
    </div>
    <div class="form-group">
      <label>Distrik <span class="help-text">(khusus Penyuluh)</span></label>
      <select name="distrik">
        <option value="">— Tidak ada —</option>
        <?php foreach ($distrikOpts as $d): ?>
          <option value="<?= e($d) ?>" <?= uv($row, 'distrik') === $d ? 'selected' : '' ?>><?= e($d) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label>Kata Sandi <?= $id ? '' : '<span class="required">*</span>' ?></label>
      <input type="password" name="password" <?= $id ? '' : 'required' ?> minlength="6" autocomplete="new-password">
      <span class="help-text"><?= $id ? 'Kosongkan jika tidak ingin mengubah kata sandi.' : 'Minimal 6 karakter.' ?></span>
    </div>
    <div class="form-group">
      <label>Status Akun</label>
      <select name="aktif">
        <option value="1" <?= (string)uv($row, 'aktif', '1') === '1' ? 'selected' : '' ?>>Aktif</option>
        <option value="0" <?= (string)uv($row, 'aktif', '1') === '0' ? 'selected' : '' ?>>Nonaktif</option>
      </select>
    </div>
  </div>
  <div class="form-actions">
    <button type="submit" class="btn-link">💾 Simpan</button>
    <a href="users.php" class="btn-secondary">Batal</a>
  </div>
</form>
<?php layout_footer();
