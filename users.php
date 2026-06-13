<?php
declare(strict_types=1);
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/includes/layout.php';
if (!file_exists(DB_PATH)) { redirect('install.php'); }
session_boot();
require_login();
require_role(['administrator']);

$rows = db()->query('SELECT * FROM users ORDER BY role, username')->fetchAll();

layout_header('Manajemen Pengguna', 'users.php');
?>
<div class="sec-head">
  <h2>👤 Manajemen Pengguna</h2>
  <a href="user_form.php" class="btn-link">➕ Tambah Pengguna</a>
</div>
<div class="flash flash-info">Kelola akun Administrator, Kepala Seksi, dan Penyuluh beserta hak aksesnya.</div>
<div class="tbl-wrap">
  <div class="tbl-header"><span class="tbl-title"><?= count($rows) ?> PENGGUNA</span></div>
  <div class="tbl-scroll">
    <table>
      <thead><tr><th>No</th><th>Nama Lengkap</th><th>Username</th><th>Peran</th><th>Distrik</th><th>Status</th><th>Login Terakhir</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php $no = 1; foreach ($rows as $r):
        $rb = ['administrator' => 'b-admin', 'kepala_seksi' => 'b-kepala', 'penyuluh' => 'b-penyuluh'][$r['role']] ?? 'b-penyuluh';
      ?>
        <tr>
          <td><?= $no++ ?></td>
          <td style="font-weight:500"><?= e($r['nama_lengkap']) ?></td>
          <td><code><?= e($r['username']) ?></code></td>
          <td><span class="badge <?= $rb ?>"><?= e(role_label($r['role'])) ?></span></td>
          <td><?= e($r['distrik'] ?: '—') ?></td>
          <td><?= ((int)$r['aktif'] === 1) ? '<span class="badge b-ver">Aktif</span>' : '<span class="badge b-tolak">Nonaktif</span>' ?></td>
          <td style="font-size:12px"><?= e($r['last_login'] ?: 'Belum pernah') ?></td>
          <td>
            <div class="actions-cell">
              <a class="btn-secondary btn-sm" href="user_form.php?id=<?= (int)$r['id'] ?>">✏️ Edit</a>
              <?php if ((int)$r['id'] !== (int)current_user()['id']): ?>
                <form method="post" action="user_delete.php" onsubmit="return confirm('Hapus pengguna ini?')" style="display:inline">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                  <button type="submit" class="btn-danger btn-sm">🗑 Hapus</button>
                </form>
              <?php endif; ?>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php layout_footer();
