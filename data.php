<?php
declare(strict_types=1);
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/includes/layout.php';
if (!file_exists(DB_PATH)) { redirect('install.php'); }
session_boot();
require_login();

$u = current_user();
$cari  = get_str('cari');
$fDist = get_str('distrik');
$fAras = get_str('aras');
$fVer  = get_str('ver');

$where  = [];
$params = [];

// Penyuluh hanya melihat distriknya sendiri
if ($u['role'] === 'penyuluh' && !empty($u['distrik'])) {
    $where[]  = 'distrik = ?';
    $params[] = $u['distrik'];
}
if ($cari !== '') {
    $where[]  = '(nama_gereja LIKE ? OR distrik LIKE ? OR pimpinan_nama LIKE ? OR desa_kelurahan LIKE ? OR nama_sinode LIKE ?)';
    $like = '%' . $cari . '%';
    array_push($params, $like, $like, $like, $like, $like);
}
if ($fDist !== '') { $where[] = 'distrik = ?';         $params[] = $fDist; }
if ($fAras !== '') { $where[] = 'aras_organisasi = ?'; $params[] = $fAras; }
if ($fVer !== '')  { $where[] = 'status_verifikasi = ?'; $params[] = $fVer; }

$sql = 'SELECT * FROM gereja';
if ($where) { $sql .= ' WHERE ' . implode(' AND ', $where); }
$sql .= ' ORDER BY distrik, nama_gereja';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$distrikOpts = db()->query('SELECT nama FROM distrik ORDER BY nama')->fetchAll(PDO::FETCH_COLUMN);
$opsiAras = ['Jemaat', 'Resort/Klasis', 'Wilayah', 'Sinode'];
$badgeAras = ['Jemaat' => 'b-paroki', 'Resort/Klasis' => 'b-kuasi', 'Wilayah' => 'b-stasi', 'Sinode' => 'b-kapel'];
$badgeVer  = ['Terverifikasi' => 'b-ver', 'Belum Diverifikasi' => 'b-unver', 'Ditolak' => 'b-tolak'];

layout_header('Data Gereja', 'data.php');
?>
<div class="sec-head">
  <h2>📋 Daftar Gereja Kristen</h2>
  <?php if (can('create_gereja')): ?>
    <a href="gereja_form.php" class="btn-link">➕ Tambah Data Gereja</a>
  <?php endif; ?>
</div>

<?php foreach (get_flashes() as $f): ?>
  <div class="flash flash-<?= e($f['type']) ?>"><?= e($f['msg']) ?></div>
<?php endforeach; ?>

<?php if ($u['role'] === 'penyuluh'): ?>
  <div class="flash flash-info">Anda masuk sebagai Penyuluh. Anda hanya dapat melihat dan mengelola data untuk distrik <strong><?= e($u['distrik'] ?: '(belum ditetapkan)') ?></strong>.</div>
<?php endif; ?>

<form class="filter-bar" method="get" action="data.php">
  <input type="text" name="cari" placeholder="🔍 Cari nama gereja, sinode, pimpinan..." value="<?= e($cari) ?>">
  <?php if ($u['role'] !== 'penyuluh'): ?>
  <select name="distrik">
    <option value="">Semua Distrik</option>
    <?php foreach ($distrikOpts as $d): ?><option value="<?= e($d) ?>" <?= $fDist === $d ? 'selected' : '' ?>><?= e($d) ?></option><?php endforeach; ?>
  </select>
  <?php endif; ?>
  <select name="aras">
    <option value="">Semua Aras</option>
    <?php foreach ($opsiAras as $o): ?><option value="<?= e($o) ?>" <?= $fAras === $o ? 'selected' : '' ?>><?= e($o) ?></option><?php endforeach; ?>
  </select>
  <select name="ver">
    <option value="">Semua Status</option>
    <?php foreach (['Terverifikasi', 'Belum Diverifikasi', 'Ditolak'] as $o): ?><option value="<?= e($o) ?>" <?= $fVer === $o ? 'selected' : '' ?>><?= e($o) ?></option><?php endforeach; ?>
  </select>
  <button type="submit" class="btn-secondary">Filter</button>
  <a href="data.php" class="btn-secondary">Reset</a>
</form>

<div class="tbl-wrap">
  <div class="tbl-header">
    <span class="tbl-title">Total <?= count($rows) ?> gereja</span>
  </div>
  <div class="tbl-scroll">
  <table>
    <thead>
      <tr>
        <th>No</th><th>Nama Gereja</th><th>Aras</th><th>Sinode</th><th>Distrik</th><th>Desa/Kel.</th><th>Pimpinan</th><th>Jemaat</th><th>Verifikasi</th><th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!$rows): ?>
        <tr><td colspan="10"><div class="empty-state">Belum ada data gereja yang cocok dengan filter.</div></td></tr>
      <?php else: $i = 1; foreach ($rows as $r): ?>
        <tr>
          <td><?= $i++ ?></td>
          <td><strong><?= e($r['nama_gereja']) ?></strong><?php if (!empty($r['singkatan'])): ?><br><span class="help-text"><?= e($r['singkatan']) ?></span><?php endif; ?></td>
          <td><span class="badge <?= $badgeAras[$r['aras_organisasi']] ?? 'b-stasi' ?>"><?= e($r['aras_organisasi']) ?></span></td>
          <td><?= e($r['nama_sinode'] ?: '—') ?></td>
          <td><?= e($r['distrik'] ?: '—') ?></td>
          <td><?= e($r['desa_kelurahan'] ?: '—') ?></td>
          <td><?= e($r['pimpinan_nama'] ?: '—') ?></td>
          <td><?= fmt_int($r['total_warga_gereja']) ?></td>
          <td><span class="badge <?= $badgeVer[$r['status_verifikasi']] ?? 'b-unver' ?>"><?= e($r['status_verifikasi']) ?></span></td>
          <td class="actions-cell">
            <a href="verifikasi.php?id=<?= (int)$r['id'] ?>" class="btn-sm btn-secondary" title="Lihat detail">👁️</a>
            <?php if (can('edit_gereja', $r)): ?><a href="gereja_form.php?id=<?= (int)$r['id'] ?>" class="btn-sm btn-secondary" title="Edit">✏️</a><?php endif; ?>
            <?php if (can('delete_gereja', $r)): ?>
              <form method="post" action="gereja_delete.php" style="display:inline" onsubmit="return confirm('Hapus data gereja ini secara permanen?');">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                <button type="submit" class="btn-sm btn-danger" title="Hapus">🗑️</button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
  </div>
</div>
<?php layout_footer(); ?>
