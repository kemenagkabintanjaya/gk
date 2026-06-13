<?php
declare(strict_types=1);
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/includes/layout.php';
if (!file_exists(DB_PATH)) { redirect('install.php'); }
session_boot();
require_login();

$u  = current_user();
$id = get_int('id');
$canVerify = can('verify_gereja');

$badgeVer = ['Terverifikasi' => 'b-ver', 'Belum Diverifikasi' => 'b-unver', 'Ditolak' => 'b-tolak'];

// Mode detail satu gereja
if ($id > 0) {
    $stmt = db()->prepare('SELECT * FROM gereja WHERE id = ?');
    $stmt->execute([$id]);
    $r = $stmt->fetch();
    if (!$r) { flash('Data gereja tidak ditemukan.', 'error'); redirect('data.php'); }
    if ($u['role'] === 'penyuluh' && (string)$r['distrik'] !== (string)($u['distrik'] ?? '')) {
        http_response_code(403); exit('403 — Di luar wilayah Anda.');
    }
    layout_header('Detail Gereja', 'data.php');
    $g = fn($k) => e($r[$k] ?? '') ?: '—';
    ?>
    <div class="sec-head">
      <h2>👁️ Detail — <?= e($r['nama_gereja']) ?></h2>
      <a href="data.php" class="btn-secondary">← Kembali</a>
    </div>
    <?php foreach (get_flashes() as $f): ?><div class="flash flash-<?= e($f['type']) ?>"><?= e($f['msg']) ?></div><?php endforeach; ?>

    <div class="info-banner">
      <span class="badge <?= $badgeVer[$r['status_verifikasi']] ?? 'b-unver' ?>"><?= e($r['status_verifikasi']) ?></span>
      <span class="ib-meta"><span class="ib-meta-item">Aras: <strong><?= $g('aras_organisasi') ?></strong></span>
      <span class="ib-meta-item">Sinode: <strong><?= $g('nama_sinode') ?></strong></span>
      <span class="ib-meta-item">Distrik: <strong><?= $g('distrik') ?></strong></span></span>
    </div>

    <div class="form-card">
      <div class="form-section-title">I. Identitas Utama</div>
      <div class="detail-grid">
        <div><span class="detail-lbl">Nama Gereja</span><span class="detail-val"><?= $g('nama_gereja') ?></span></div>
        <div><span class="detail-lbl">Singkatan / Sinode</span><span class="detail-val"><?= $g('singkatan') ?></span></div>
        <div><span class="detail-lbl">Aras Organisasi</span><span class="detail-val"><?= $g('aras_organisasi') ?></span></div>
        <div><span class="detail-lbl">Nama Sinode/Induk</span><span class="detail-val"><?= $g('nama_sinode') ?></span></div>
        <div><span class="detail-lbl">Alamat</span><span class="detail-val"><?= $g('alamat') ?></span></div>
        <div><span class="detail-lbl">Desa/Kelurahan</span><span class="detail-val"><?= $g('desa_kelurahan') ?></span></div>
        <div><span class="detail-lbl">Kode Pos</span><span class="detail-val"><?= $g('kode_pos') ?></span></div>
        <div><span class="detail-lbl">Telp/HP/WA</span><span class="detail-val"><?= $g('telepon') ?></span></div>
      </div>

      <div class="form-section-title">II. Legalitas</div>
      <div class="detail-grid">
        <div><span class="detail-lbl">No. IMB/PBG</span><span class="detail-val"><?= $g('no_imb') ?></span></div>
        <div><span class="detail-lbl">Tahun Berdiri</span><span class="detail-val"><?= $g('tahun_berdiri') ?></span></div>
        <div><span class="detail-lbl">No. SK Pendirian</span><span class="detail-val"><?= $g('no_sk_pendirian') ?></span></div>
        <div><span class="detail-lbl">No. SK Pendeta</span><span class="detail-val"><?= $g('no_sk_pendeta') ?></span></div>
        <div><span class="detail-lbl">No. SK Kemenag RI</span><span class="detail-val"><?= $g('no_sk_kemenag') ?></span></div>
        <div><span class="detail-lbl">Status Registrasi</span><span class="detail-val"><?= $g('status_registrasi') ?></span></div>
        <div><span class="detail-lbl">No. Registrasi Daerah</span><span class="detail-val"><?= $g('no_registrasi_daerah') ?></span></div>
      </div>

      <div class="form-section-title">III. Fisik &amp; Fasilitas</div>
      <div class="detail-grid">
        <div><span class="detail-lbl">Gedung Permanen</span><span class="detail-val"><?= fmt_int($r['gedung_permanen']) ?> buah</span></div>
        <div><span class="detail-lbl">Gedung Semi Permanen</span><span class="detail-val"><?= fmt_int($r['gedung_semi']) ?> buah</span></div>
        <div><span class="detail-lbl">Gedung Darurat</span><span class="detail-val"><?= fmt_int($r['gedung_darurat']) ?> buah</span></div>
        <div><span class="detail-lbl">Daya Tampung</span><span class="detail-val"><?= fmt_int($r['kapasitas']) ?> orang</span></div>
        <div><span class="detail-lbl">Kepemilikan</span><span class="detail-val">Milik: <?= fmt_int($r['milik_sendiri']) ?> · Sewa: <?= fmt_int($r['milik_sewa']) ?> · Lain: <?= fmt_int($r['milik_lain']) ?></span></div>
        <div><span class="detail-lbl">Perpustakaan</span><span class="detail-val"><?= $g('perpustakaan') ?></span></div>
      </div>

      <div class="form-section-title">IV. Demografi Jemaat</div>
      <div class="detail-grid">
        <div><span class="detail-lbl">Total Warga Gereja</span><span class="detail-val"><strong><?= fmt_int($r['total_warga_gereja']) ?></strong> jiwa</span></div>
        <div><span class="detail-lbl">Laki-laki</span><span class="detail-val"><?= fmt_int($r['laki']) ?></span></div>
        <div><span class="detail-lbl">Perempuan</span><span class="detail-val"><?= fmt_int($r['perempuan']) ?></span></div>
        <div><span class="detail-lbl">Bapak</span><span class="detail-val"><?= fmt_int($r['bapak']) ?></span></div>
        <div><span class="detail-lbl">Ibu</span><span class="detail-val"><?= fmt_int($r['ibu']) ?></span></div>
        <div><span class="detail-lbl">Pemuda</span><span class="detail-val"><?= fmt_int($r['pemuda']) ?></span></div>
        <div><span class="detail-lbl">Remaja</span><span class="detail-val"><?= fmt_int($r['remaja']) ?></span></div>
        <div><span class="detail-lbl">Anak Sekolah Minggu</span><span class="detail-val"><?= fmt_int($r['anak_sm']) ?></span></div>
      </div>

      <div class="form-section-title">V. Pimpinan &amp; Pengurus</div>
      <div class="detail-grid">
        <div><span class="detail-lbl">Pimpinan</span><span class="detail-val"><?= $g('pimpinan_nama') ?> (<?= $g('pimpinan_jk') ?>)</span></div>
        <div><span class="detail-lbl">TTL</span><span class="detail-val"><?= $g('pimpinan_ttl') ?></span></div>
        <div><span class="detail-lbl">HP/WA</span><span class="detail-val"><?= $g('pimpinan_hp') ?></span></div>
        <div><span class="detail-lbl">Pendidikan</span><span class="detail-val"><?= $g('pimpinan_pendidikan') ?></span></div>
        <div><span class="detail-lbl">Mulai Bertugas</span><span class="detail-val"><?= $g('pimpinan_mulai') ?></span></div>
        <div><span class="detail-lbl">Ketua Pengurus</span><span class="detail-val"><?= $g('pengurus_ketua') ?></span></div>
        <div><span class="detail-lbl">Sekretaris</span><span class="detail-val"><?= $g('pengurus_sekretaris') ?></span></div>
        <div><span class="detail-lbl">Bendahara</span><span class="detail-val"><?= $g('pengurus_bendahara') ?></span></div>
        <div><span class="detail-lbl">Koster</span><span class="detail-val"><?= $g('pengurus_koster') ?></span></div>
      </div>

      <div class="form-section-title">VI. Personil Pelayanan</div>
      <div class="detail-grid">
        <div><span class="detail-lbl">Pendeta (Pdt.)</span><span class="detail-val"><?= fmt_int($r['pdt_p'] + $r['pdt_w']) ?> (L:<?= (int)$r['pdt_p'] ?>/P:<?= (int)$r['pdt_w'] ?>)</span></div>
        <div><span class="detail-lbl">Pendeta Muda</span><span class="detail-val"><?= fmt_int($r['pdm_p'] + $r['pdm_w']) ?></span></div>
        <div><span class="detail-lbl">Pendeta Pembantu</span><span class="detail-val"><?= fmt_int($r['pdp_p'] + $r['pdp_w']) ?></span></div>
        <div><span class="detail-lbl">Majelis/Penatua</span><span class="detail-val"><?= fmt_int($r['majelis_p'] + $r['majelis_w']) ?></span></div>
        <div><span class="detail-lbl">Diaken/Syamas</span><span class="detail-val"><?= fmt_int($r['diaken_p'] + $r['diaken_w']) ?></span></div>
        <div><span class="detail-lbl">Guru Injil</span><span class="detail-val"><?= fmt_int($r['guru_injil_p'] + $r['guru_injil_w']) ?></span></div>
        <div><span class="detail-lbl">Guru Sekolah Minggu</span><span class="detail-val"><?= fmt_int($r['guru_sm_p'] + $r['guru_sm_w']) ?></span></div>
        <div><span class="detail-lbl">Penginjil</span><span class="detail-val"><?= fmt_int($r['penginjil_p'] + $r['penginjil_w']) ?></span></div>
        <div><span class="detail-lbl">Total Personil</span><span class="detail-val"><strong><?= fmt_int($r['total_personil']) ?></strong> orang</span></div>
      </div>

      <div class="form-section-title">VII. Media &amp; Keterangan</div>
      <div class="detail-grid">
        <div><span class="detail-lbl">Email</span><span class="detail-val"><?= $g('email') ?></span></div>
        <div><span class="detail-lbl">Media Sosial</span><span class="detail-val"><?= $g('media_sosial') ?></span></div>
        <div><span class="detail-lbl">Jadwal Ibadah</span><span class="detail-val"><?= $g('jadwal_ibadah') ?></span></div>
        <div class="full"><span class="detail-lbl">Keterangan</span><span class="detail-val"><?= $g('keterangan') ?></span></div>
      </div>
    </div>

    <?php if ($canVerify): ?>
      <div class="form-card" style="margin-top:18px">
        <div class="form-section-title">✅ Tindakan Verifikasi</div>
        <form method="post" action="verifikasi_save.php">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
          <div class="form-grid">
            <div class="form-group">
              <label>Status Verifikasi</label>
              <select name="status_verifikasi">
                <?php foreach (['Terverifikasi', 'Belum Diverifikasi', 'Ditolak'] as $o): ?>
                  <option value="<?= e($o) ?>" <?= $r['status_verifikasi'] === $o ? 'selected' : '' ?>><?= e($o) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group full">
              <label>Catatan Verifikasi</label>
              <textarea name="catatan_verifikasi" rows="2"><?= e($r['catatan_verifikasi']) ?></textarea>
            </div>
          </div>
          <div class="form-actions">
            <button type="submit" class="login-btn" style="width:auto;padding:12px 32px">Simpan Verifikasi</button>
          </div>
        </form>
      </div>
    <?php endif; ?>
    <?php layout_footer(); exit; ?>
<?php } ?>
<?php
// Mode daftar: gereja yang menunggu verifikasi
$where = [];
$params = [];
if ($u['role'] === 'penyuluh' && !empty($u['distrik'])) { $where[] = 'distrik = ?'; $params[] = $u['distrik']; }
$sql = 'SELECT * FROM gereja';
if ($where) { $sql .= ' WHERE ' . implode(' AND ', $where); }
$sql .= " ORDER BY CASE status_verifikasi WHEN 'Belum Diverifikasi' THEN 0 WHEN 'Ditolak' THEN 1 ELSE 2 END, nama_gereja";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

layout_header('Verifikasi Data', 'verifikasi.php');
?>
<div class="sec-head"><h2>✅ Verifikasi Data Gereja</h2></div>
<?php foreach (get_flashes() as $f): ?><div class="flash flash-<?= e($f['type']) ?>"><?= e($f['msg']) ?></div><?php endforeach; ?>
<?php if (!$canVerify): ?><div class="flash flash-info">Anda dapat melihat status verifikasi, namun hanya Administrator dan Kepala Seksi yang dapat mengubahnya.</div><?php endif; ?>

<div class="tbl-wrap">
  <div class="tbl-header"><span class="tbl-title">Total <?= count($rows) ?> data</span></div>
  <div class="tbl-scroll">
  <table>
    <thead><tr><th>No</th><th>Nama Gereja</th><th>Aras</th><th>Distrik</th><th>Jemaat</th><th>Status</th><th>Aksi</th></tr></thead>
    <tbody>
      <?php if (!$rows): ?>
        <tr><td colspan="7"><div class="empty-state">Belum ada data.</div></td></tr>
      <?php else: $i = 1; foreach ($rows as $r): ?>
        <tr>
          <td><?= $i++ ?></td>
          <td><strong><?= e($r['nama_gereja']) ?></strong></td>
          <td><?= e($r['aras_organisasi']) ?></td>
          <td><?= e($r['distrik'] ?: '—') ?></td>
          <td><?= fmt_int($r['total_warga_gereja']) ?></td>
          <td><span class="badge <?= $badgeVer[$r['status_verifikasi']] ?? 'b-unver' ?>"><?= e($r['status_verifikasi']) ?></span></td>
          <td class="actions-cell"><a href="verifikasi.php?id=<?= (int)$r['id'] ?>" class="btn-sm btn-secondary"><?= $canVerify ? 'Tinjau' : 'Lihat' ?></a></td>
        </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
  </div>
</div>
<?php layout_footer(); ?>
