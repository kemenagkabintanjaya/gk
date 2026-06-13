<?php
declare(strict_types=1);
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/includes/layout.php';
if (!file_exists(DB_PATH)) { redirect('install.php'); }
session_boot();
require_login();

$u  = current_user();
$id = get_int('id');
$row = null;

if ($id > 0) {
    $stmt = db()->prepare('SELECT * FROM gereja WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) { flash('Data gereja tidak ditemukan.', 'error'); redirect('data.php'); }
    if (!can('edit_gereja', $row)) { http_response_code(403); exit('403 — Anda tidak berhak mengubah data ini.'); }
} else {
    if (!can('create_gereja')) { http_response_code(403); exit('403 — Anda tidak berhak menambah data.'); }
}

$distrikOpts = db()->query('SELECT nama FROM distrik ORDER BY nama')->fetchAll(PDO::FETCH_COLUMN);
$isPenyuluh  = $u['role'] === 'penyuluh';

function v($row, $k, $d = '') { return $row[$k] ?? $d; }
function sel($row, $k, $opt, $def = '') { return v($row, $k, $def) === $opt ? 'selected' : ''; }

$opsiAras  = ['Jemaat', 'Resort/Klasis', 'Wilayah', 'Sinode'];
$opsiReg   = ['Terdaftar', 'Dalam Proses', 'Belum Terdaftar'];
$opsiPerpus= ['Ada', 'Tidak Ada'];
$opsiJk    = ['Pria', 'Wanita'];

layout_header($id ? 'Edit Data Gereja' : 'Tambah Data Gereja', 'data.php');
?>
<div class="sec-head">
  <h2><?= $id ? '✏️ Edit Data Gereja' : '➕ Tambah Data Gereja' ?></h2>
  <a href="data.php" class="btn-secondary">← Kembali</a>
</div>

<form class="form-card" method="post" action="gereja_save.php">
  <?= csrf_field() ?>
  <input type="hidden" name="id" value="<?= (int)$id ?>">

  <!-- I. IDENTITAS UTAMA -->
  <div class="form-section-title">I. Identitas Utama Gereja</div>
  <div class="form-grid">
    <div class="form-group full">
      <label>Nama Lengkap Gereja <span class="required">*</span></label>
      <input type="text" name="nama_gereja" required maxlength="150" value="<?= e(v($row, 'nama_gereja')) ?>" placeholder="Contoh: Jemaat GKII Eben Haezer Sugapa">
    </div>
    <div class="form-group">
      <label>Singkatan / Sinode Resmi</label>
      <input type="text" name="singkatan" maxlength="40" value="<?= e(v($row, 'singkatan')) ?>" placeholder="GKII / GIDI / KINGMI / GKI">
    </div>
    <div class="form-group">
      <label>Aras Organisasi Gereja</label>
      <select name="aras_organisasi">
        <?php foreach ($opsiAras as $o): ?><option value="<?= e($o) ?>" <?= sel($row, 'aras_organisasi', $o, 'Jemaat') ?>><?= e($o) ?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="form-group full">
      <label>Nama Sinode / Induk Organisasi Gereja</label>
      <input type="text" name="nama_sinode" maxlength="150" value="<?= e(v($row, 'nama_sinode')) ?>" placeholder="Contoh: Gereja Kemah Injil Indonesia (GKII)">
    </div>
    <div class="form-group full">
      <label>Alamat Lengkap Jemaat</label>
      <input type="text" name="alamat" maxlength="200" value="<?= e(v($row, 'alamat')) ?>">
    </div>
    <div class="form-group">
      <label>Desa / Kelurahan</label>
      <input type="text" name="desa_kelurahan" maxlength="100" value="<?= e(v($row, 'desa_kelurahan')) ?>">
    </div>
    <div class="form-group">
      <label>Distrik / Kecamatan <?= $isPenyuluh ? '' : '' ?></label>
      <?php if ($isPenyuluh): ?>
        <input type="text" value="<?= e($u['distrik'] ?: '(belum ditetapkan)') ?>" disabled>
        <span class="help-text">Penyuluh hanya dapat mendata distriknya sendiri.</span>
      <?php else: ?>
        <select name="distrik">
          <option value="">— Pilih Distrik —</option>
          <?php foreach ($distrikOpts as $d): ?><option value="<?= e($d) ?>" <?= sel($row, 'distrik', $d) ?>><?= e($d) ?></option><?php endforeach; ?>
        </select>
      <?php endif; ?>
    </div>
    <div class="form-group">
      <label>Kode Pos</label>
      <input type="text" name="kode_pos" maxlength="10" value="<?= e(v($row, 'kode_pos')) ?>">
    </div>
    <div class="form-group">
      <label>No. Telp / HP / WA</label>
      <input type="text" name="telepon" maxlength="40" value="<?= e(v($row, 'telepon')) ?>">
    </div>
  </div>

  <!-- II. LEGALITAS -->
  <div class="form-section-title">II. Legalitas &amp; Dokumen Administrasi</div>
  <div class="form-grid">
    <div class="form-group">
      <label>Nomor IMB / PBG</label>
      <input type="text" name="no_imb" maxlength="80" value="<?= e(v($row, 'no_imb')) ?>">
    </div>
    <div class="form-group">
      <label>Tahun Berdiri Bangunan</label>
      <input type="number" name="tahun_berdiri" min="1900" max="2100" value="<?= e(v($row, 'tahun_berdiri')) ?>">
    </div>
    <div class="form-group">
      <label>No. SK Pendirian Gereja</label>
      <input type="text" name="no_sk_pendirian" maxlength="80" value="<?= e(v($row, 'no_sk_pendirian')) ?>">
    </div>
    <div class="form-group">
      <label>No. SK Penahbisan / Penugasan Pendeta</label>
      <input type="text" name="no_sk_pendeta" maxlength="80" value="<?= e(v($row, 'no_sk_pendeta')) ?>">
    </div>
    <div class="form-group">
      <label>No. SK Pendaftaran Kemenag RI</label>
      <input type="text" name="no_sk_kemenag" maxlength="80" value="<?= e(v($row, 'no_sk_kemenag')) ?>">
    </div>
    <div class="form-group">
      <label>Status Registrasi Instansi</label>
      <select name="status_registrasi">
        <?php foreach ($opsiReg as $o): ?><option value="<?= e($o) ?>" <?= sel($row, 'status_registrasi', $o, 'Belum Terdaftar') ?>><?= e($o) ?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label>Nomor Registrasi Resmi Daerah</label>
      <input type="text" name="no_registrasi_daerah" maxlength="80" value="<?= e(v($row, 'no_registrasi_daerah')) ?>">
    </div>
  </div>

  <!-- III. FISIK & FASILITAS -->
  <div class="form-section-title">III. Profil Fisik &amp; Fasilitas</div>
  <div class="help-text" style="margin-bottom:10px">Jumlah bangunan menurut kondisi fisik (dalam satuan buah).</div>
  <div class="form-grid">
    <div class="form-group"><label>Gedung Permanen (buah)</label><input type="number" name="gedung_permanen" min="0" value="<?= e(v($row, 'gedung_permanen', '0')) ?>"></div>
    <div class="form-group"><label>Gedung Semi Permanen (buah)</label><input type="number" name="gedung_semi" min="0" value="<?= e(v($row, 'gedung_semi', '0')) ?>"></div>
    <div class="form-group"><label>Gedung Darurat (buah)</label><input type="number" name="gedung_darurat" min="0" value="<?= e(v($row, 'gedung_darurat', '0')) ?>"></div>
    <div class="form-group"><label>Status: Hak Milik (buah)</label><input type="number" name="milik_sendiri" min="0" value="<?= e(v($row, 'milik_sendiri', '0')) ?>"></div>
    <div class="form-group"><label>Status: Sewa / Kontrak (buah)</label><input type="number" name="milik_sewa" min="0" value="<?= e(v($row, 'milik_sewa', '0')) ?>"></div>
    <div class="form-group"><label>Status: Lain-lain (buah)</label><input type="number" name="milik_lain" min="0" value="<?= e(v($row, 'milik_lain', '0')) ?>"></div>
    <div class="form-group"><label>Daya Tampung / Kapasitas (orang)</label><input type="number" name="kapasitas" min="0" value="<?= e(v($row, 'kapasitas', '0')) ?>"></div>
    <div class="form-group">
      <label>Fasilitas Perpustakaan</label>
      <select name="perpustakaan">
        <?php foreach ($opsiPerpus as $o): ?><option value="<?= e($o) ?>" <?= sel($row, 'perpustakaan', $o, 'Tidak Ada') ?>><?= e($o) ?></option><?php endforeach; ?>
      </select>
    </div>
  </div>

  <!-- IV. DEMOGRAFI JEMAAT -->
  <div class="form-section-title">IV. Kependudukan &amp; Demografi Jemaat</div>
  <div class="form-grid">
    <div class="form-group"><label>Bapak / Pria Dewasa</label><input type="number" name="bapak" min="0" value="<?= e(v($row, 'bapak', '0')) ?>"></div>
    <div class="form-group"><label>Ibu / Wanita Dewasa</label><input type="number" name="ibu" min="0" value="<?= e(v($row, 'ibu', '0')) ?>"></div>
    <div class="form-group"><label>Pemuda</label><input type="number" name="pemuda" min="0" value="<?= e(v($row, 'pemuda', '0')) ?>"></div>
    <div class="form-group"><label>Remaja</label><input type="number" name="remaja" min="0" value="<?= e(v($row, 'remaja', '0')) ?>"></div>
    <div class="form-group"><label>Anak Sekolah Minggu</label><input type="number" name="anak_sm" min="0" value="<?= e(v($row, 'anak_sm', '0')) ?>"></div>
    <div class="form-group"><label>Total Warga Gereja</label><input type="number" name="total_warga_gereja" min="0" value="<?= e(v($row, 'total_warga_gereja', '0')) ?>"><span class="help-text">Kosongkan untuk dihitung otomatis dari Laki-laki + Perempuan.</span></div>
    <div class="form-group"><label>Rekap Jenis Kelamin: Laki-laki</label><input type="number" name="laki" min="0" value="<?= e(v($row, 'laki', '0')) ?>"></div>
    <div class="form-group"><label>Rekap Jenis Kelamin: Perempuan</label><input type="number" name="perempuan" min="0" value="<?= e(v($row, 'perempuan', '0')) ?>"></div>
  </div>

  <!-- V. PIMPINAN -->
  <div class="form-section-title">V. Pimpinan Jemaat &amp; Pengurus Inti</div>
  <div class="form-grid">
    <div class="form-group"><label>Nama Lengkap Pimpinan (Pendeta/Gembala)</label><input type="text" name="pimpinan_nama" maxlength="120" value="<?= e(v($row, 'pimpinan_nama')) ?>"></div>
    <div class="form-group">
      <label>Jenis Kelamin Pimpinan</label>
      <select name="pimpinan_jk">
        <option value="">— Pilih —</option>
        <?php foreach ($opsiJk as $o): ?><option value="<?= e($o) ?>" <?= sel($row, 'pimpinan_jk', $o) ?>><?= e($o) ?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="form-group"><label>Tempat &amp; Tanggal Lahir</label><input type="text" name="pimpinan_ttl" maxlength="120" value="<?= e(v($row, 'pimpinan_ttl')) ?>"></div>
    <div class="form-group"><label>No. HP / WA Pimpinan</label><input type="text" name="pimpinan_hp" maxlength="40" value="<?= e(v($row, 'pimpinan_hp')) ?>"></div>
    <div class="form-group full"><label>Alamat Rumah Pimpinan</label><input type="text" name="pimpinan_alamat" maxlength="200" value="<?= e(v($row, 'pimpinan_alamat')) ?>"></div>
    <div class="form-group"><label>Tanggal Mulai Bertugas di Lokasi</label><input type="date" name="pimpinan_mulai" value="<?= e(v($row, 'pimpinan_mulai')) ?>"></div>
    <div class="form-group"><label>Pendidikan Terakhir</label><input type="text" name="pimpinan_pendidikan" maxlength="80" value="<?= e(v($row, 'pimpinan_pendidikan')) ?>"></div>
  </div>
  <div class="help-text" style="margin:6px 0 10px">Pengurus Harian (Badan Pekerja Jemaat)</div>
  <div class="form-grid">
    <div class="form-group"><label>Ketua Pengurus</label><input type="text" name="pengurus_ketua" maxlength="120" value="<?= e(v($row, 'pengurus_ketua')) ?>"></div>
    <div class="form-group"><label>Sekretaris</label><input type="text" name="pengurus_sekretaris" maxlength="120" value="<?= e(v($row, 'pengurus_sekretaris')) ?>"></div>
    <div class="form-group"><label>Bendahara</label><input type="text" name="pengurus_bendahara" maxlength="120" value="<?= e(v($row, 'pengurus_bendahara')) ?>"></div>
    <div class="form-group"><label>Penjaga Rumah Ibadah (Koster)</label><input type="text" name="pengurus_koster" maxlength="120" value="<?= e(v($row, 'pengurus_koster')) ?>"></div>
  </div>

  <!-- VI. PERSONIL PELAYANAN -->
  <div class="form-section-title">VI. Personil Pelayanan (Pria / Wanita)</div>
  <div class="tbl-scroll">
  <table class="personil-tbl">
    <thead><tr><th>Jenis Pelayan</th><th>Pria</th><th>Wanita</th></tr></thead>
    <tbody>
      <?php
      $personilRows = [
        ['Pendeta (Pdt.)', 'pdt_p', 'pdt_w'],
        ['Pendeta Muda (Pdm.)', 'pdm_p', 'pdm_w'],
        ['Pendeta Pembantu (Pdp.)', 'pdp_p', 'pdp_w'],
        ['Majelis / Penatua Jemaat', 'majelis_p', 'majelis_w'],
        ['Diaken / Syamas / Penatalayan', 'diaken_p', 'diaken_w'],
        ['Guru Injil', 'guru_injil_p', 'guru_injil_w'],
        ['Guru Sekolah Minggu', 'guru_sm_p', 'guru_sm_w'],
        ['Penginjil (Evangelis)', 'penginjil_p', 'penginjil_w'],
      ];
      foreach ($personilRows as $pr): ?>
        <tr>
          <td><?= e($pr[0]) ?></td>
          <td><input type="number" name="<?= $pr[1] ?>" min="0" value="<?= e(v($row, $pr[1], '0')) ?>"></td>
          <td><input type="number" name="<?= $pr[2] ?>" min="0" value="<?= e(v($row, $pr[2], '0')) ?>"></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
  <div class="help-text" style="margin-top:8px">Jumlah total personil dihitung otomatis saat disimpan.</div>

  <!-- VII. MEDIA & KETERANGAN -->
  <div class="form-section-title">VII. Media Komunikasi &amp; Keterangan</div>
  <div class="form-grid">
    <div class="form-group"><label>Alamat Email Resmi Gereja</label><input type="email" name="email" maxlength="120" value="<?= e(v($row, 'email')) ?>"></div>
    <div class="form-group"><label>Media Sosial Resmi (FB/IG/Youtube)</label><input type="text" name="media_sosial" maxlength="150" value="<?= e(v($row, 'media_sosial')) ?>"></div>
    <div class="form-group full"><label>Jadwal Layanan Ibadah Rutin Mingguan</label><input type="text" name="jadwal_ibadah" maxlength="200" value="<?= e(v($row, 'jadwal_ibadah')) ?>" placeholder="Contoh: Minggu 09.00 WIT, Rabu 17.00 WIT"></div>
    <div class="form-group full"><label>Keterangan Tambahan / Catatan Khusus</label><textarea name="keterangan" rows="3"><?= e(v($row, 'keterangan')) ?></textarea></div>
  </div>

  <div class="form-actions">
    <a href="data.php" class="btn-secondary">Batal</a>
    <button type="submit" class="login-btn" style="width:auto;padding:12px 32px"><?= $id ? '💾 Simpan Perubahan' : '💾 Simpan Data' ?></button>
  </div>
</form>
<?php layout_footer(); ?>
