<?php
declare(strict_types=1);
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/includes/layout.php';
session_boot();

$alreadyInstalled = file_exists(DB_PATH);
$done = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    try {
        db_init();
        $pdo = db();
        $count = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
        if ($count === 0) {
            seed_database($pdo);
        }
        $done = true;
    } catch (Throwable $ex) {
        $error = $ex->getMessage();
    }
}

function gereja_columns(): array
{
    return [
        'nama_gereja','singkatan','alamat','desa_kelurahan','distrik','kode_pos','telepon','aras_organisasi','nama_sinode',
        'no_imb','tahun_berdiri','no_sk_pendirian','no_sk_pendeta','no_sk_kemenag','status_registrasi','no_registrasi_daerah',
        'gedung_permanen','gedung_semi','gedung_darurat','milik_sendiri','milik_sewa','milik_lain','kapasitas','perpustakaan',
        'bapak','ibu','pemuda','remaja','anak_sm','total_warga_gereja','laki','perempuan',
        'pimpinan_nama','pimpinan_jk','pimpinan_ttl','pimpinan_alamat','pimpinan_hp','pimpinan_mulai','pimpinan_pendidikan',
        'pengurus_ketua','pengurus_sekretaris','pengurus_bendahara','pengurus_koster',
        'pdt_p','pdt_w','pdm_p','pdm_w','pdp_p','pdp_w','majelis_p','majelis_w','diaken_p','diaken_w',
        'guru_injil_p','guru_injil_w','guru_sm_p','guru_sm_w','penginjil_p','penginjil_w','total_personil',
        'email','media_sosial','jadwal_ibadah','keterangan',
        'status_verifikasi','created_by','verified_by','verified_at',
    ];
}

function insert_gereja(PDO $pdo, array $g): void
{
    $cols = gereja_columns();
    $defaults = array_fill_keys($cols, null);
    foreach (['gedung_permanen','gedung_semi','gedung_darurat','milik_sendiri','milik_sewa','milik_lain','kapasitas',
        'bapak','ibu','pemuda','remaja','anak_sm','total_warga_gereja','laki','perempuan',
        'pdt_p','pdt_w','pdm_p','pdm_w','pdp_p','pdp_w','majelis_p','majelis_w','diaken_p','diaken_w',
        'guru_injil_p','guru_injil_w','guru_sm_p','guru_sm_w','penginjil_p','penginjil_w','total_personil'] as $numCol) {
        $defaults[$numCol] = 0;
    }
    $defaults['aras_organisasi']   = 'Jemaat';
    $defaults['perpustakaan']      = 'Tidak Ada';
    $defaults['status_registrasi'] = 'Belum Terdaftar';
    $defaults['status_verifikasi'] = 'Belum Diverifikasi';

    $row = array_merge($defaults, $g);

    // Hitung otomatis bila belum diisi
    if ((int)$row['total_warga_gereja'] === 0) {
        $row['total_warga_gereja'] = (int)$row['laki'] + (int)$row['perempuan'];
    }
    if ((int)$row['total_personil'] === 0) {
        $row['total_personil'] = (int)$row['pdt_p']+(int)$row['pdt_w']+(int)$row['pdm_p']+(int)$row['pdm_w']
            +(int)$row['pdp_p']+(int)$row['pdp_w']+(int)$row['majelis_p']+(int)$row['majelis_w']
            +(int)$row['diaken_p']+(int)$row['diaken_w']+(int)$row['guru_injil_p']+(int)$row['guru_injil_w']
            +(int)$row['guru_sm_p']+(int)$row['guru_sm_w']+(int)$row['penginjil_p']+(int)$row['penginjil_w'];
    }

    $colList = implode(', ', $cols);
    $ph = implode(', ', array_map(fn($c) => ":$c", $cols));
    $stmt = $pdo->prepare("INSERT INTO gereja ($colList) VALUES ($ph)");
    $bind = [];
    foreach ($cols as $c) { $bind[$c] = $row[$c]; }
    $stmt->execute($bind);
}

function seed_database(PDO $pdo): void
{
    $distrikList = ['Sugapa', 'Homeyo', 'Agisiga', 'Biandoga', 'Hitadipa', 'Mbiandoga',
        'Tomosiga', 'Wandai', 'Ugimba', 'Yokatapa', 'Pogapa', 'Tinginambut'];
    $sd = $pdo->prepare('INSERT OR IGNORE INTO distrik (nama) VALUES (?)');
    foreach ($distrikList as $d) { $sd->execute([$d]); }

    // Pengguna default
    $su = $pdo->prepare('INSERT INTO users (username, password_hash, nama_lengkap, role, distrik) VALUES (?,?,?,?,?)');
    $su->execute(['admin', password_hash('Admin#2026', PASSWORD_DEFAULT), 'Administrator Sistem', 'administrator', null]);
    $su->execute(['kepala', password_hash('Kepala#2026', PASSWORD_DEFAULT), 'Kepala Seksi Bimas Kristen', 'kepala_seksi', null]);
    $su->execute(['penyuluh', password_hash('Penyuluh#2026', PASSWORD_DEFAULT), 'Penyuluh Kristen Sugapa', 'penyuluh', 'Sugapa']);
    $penyuluhId = (int)$pdo->lastInsertId();
    $now = date('Y-m-d H:i:s');

    $samples = [
        [
            'nama_gereja'=>'Jemaat GKII Eben Haezer Sugapa','singkatan'=>'GKII','alamat'=>'Jl. Pusat Sugapa','desa_kelurahan'=>'Sugapa','distrik'=>'Sugapa','telepon'=>'0822-0000-0001',
            'aras_organisasi'=>'Wilayah','nama_sinode'=>'Gereja Kemah Injil Indonesia (GKII)','tahun_berdiri'=>1998,
            'status_registrasi'=>'Terdaftar','no_sk_kemenag'=>'SK.01/BIMAS-KRISTEN/2015',
            'gedung_permanen'=>1,'milik_sendiri'=>1,'kapasitas'=>500,'perpustakaan'=>'Ada',
            'bapak'=>140,'ibu'=>150,'pemuda'=>90,'remaja'=>70,'anak_sm'=>110,'laki'=>270,'perempuan'=>290,
            'pimpinan_nama'=>'Pdt. Yulius Wandik, S.Th','pimpinan_jk'=>'Pria','pimpinan_hp'=>'0822-0000-0001','pimpinan_pendidikan'=>'S1 Teologi','pimpinan_mulai'=>'2012-01-15',
            'pengurus_ketua'=>'Yairus Sani','pengurus_sekretaris'=>'Marthen Bagau','pengurus_bendahara'=>'Naomi Kobogau','pengurus_koster'=>'Daud Sondegau',
            'pdt_p'=>1,'pdm_p'=>1,'majelis_p'=>6,'majelis_w'=>2,'diaken_p'=>3,'diaken_w'=>3,'guru_sm_p'=>1,'guru_sm_w'=>3,
            'email'=>'gkii.sugapa@gmail.com','jadwal_ibadah'=>'Minggu 09.00 WIT','status_verifikasi'=>'Terverifikasi','verified_by'=>1,'verified_at'=>$now,'created_by'=>$penyuluhId,
        ],
        [
            'nama_gereja'=>'Jemaat GIDI Filadelfia Homeyo','singkatan'=>'GIDI','alamat'=>'Kampung Homeyo','desa_kelurahan'=>'Homeyo','distrik'=>'Homeyo',
            'aras_organisasi'=>'Jemaat','nama_sinode'=>'Gereja Injili di Indonesia (GIDI)','tahun_berdiri'=>2003,
            'status_registrasi'=>'Terdaftar','gedung_permanen'=>1,'milik_sendiri'=>1,'kapasitas'=>350,
            'bapak'=>95,'ibu'=>100,'pemuda'=>60,'remaja'=>50,'anak_sm'=>80,'laki'=>185,'perempuan'=>200,
            'pimpinan_nama'=>'Pdt. Enos Pigai','pimpinan_jk'=>'Pria','pimpinan_pendidikan'=>'D3 Teologi','pimpinan_mulai'=>'2016-07-01',
            'pengurus_ketua'=>'Soleman Tabuni','pengurus_sekretaris'=>'Yakob Murib','pengurus_bendahara'=>'Debora Kogoya',
            'pdt_p'=>1,'majelis_p'=>4,'majelis_w'=>1,'diaken_p'=>2,'diaken_w'=>2,'guru_sm_p'=>2,'guru_sm_w'=>2,'penginjil_p'=>1,
            'jadwal_ibadah'=>'Minggu 10.00 WIT','status_verifikasi'=>'Terverifikasi','verified_by'=>1,'verified_at'=>$now,'created_by'=>$penyuluhId,
        ],
        [
            'nama_gereja'=>'Jemaat Kingmi Betlehem Agisiga','singkatan'=>'KINGMI','desa_kelurahan'=>'Agisiga','distrik'=>'Agisiga',
            'aras_organisasi'=>'Jemaat','nama_sinode'=>'Gereja Kingmi di Tanah Papua','tahun_berdiri'=>2008,
            'status_registrasi'=>'Dalam Proses','gedung_semi'=>1,'milik_sendiri'=>1,'kapasitas'=>200,
            'bapak'=>60,'ibu'=>65,'pemuda'=>40,'remaja'=>35,'anak_sm'=>50,'laki'=>120,'perempuan'=>130,
            'pimpinan_nama'=>'Pdt. Markus Mote','pimpinan_jk'=>'Pria','pimpinan_pendidikan'=>'S1 Teologi','pimpinan_mulai'=>'2018-03-10',
            'pengurus_ketua'=>'Pilemon Mote','pengurus_sekretaris'=>'Yunus Sani',
            'pdt_p'=>1,'majelis_p'=>3,'diaken_p'=>2,'diaken_w'=>1,'guru_sm_w'=>2,
            'jadwal_ibadah'=>'Minggu 09.30 WIT','status_verifikasi'=>'Belum Diverifikasi','created_by'=>$penyuluhId,
        ],
        [
            'nama_gereja'=>'Jemaat GKI Pniel Biandoga','singkatan'=>'GKI','desa_kelurahan'=>'Biandoga','distrik'=>'Biandoga',
            'aras_organisasi'=>'Jemaat','nama_sinode'=>'GKI di Tanah Papua','tahun_berdiri'=>2010,
            'status_registrasi'=>'Belum Terdaftar','gedung_semi'=>1,'milik_sendiri'=>1,'kapasitas'=>180,
            'bapak'=>45,'ibu'=>48,'pemuda'=>30,'remaja'=>25,'anak_sm'=>40,'laki'=>92,'perempuan'=>96,
            'pimpinan_nama'=>'Pdt. Ruben Kobak','pimpinan_jk'=>'Pria','pimpinan_pendidikan'=>'D3 Teologi',
            'pengurus_ketua'=>'Timotius Belau',
            'pdt_p'=>1,'majelis_p'=>2,'majelis_w'=>1,'diaken_p'=>1,'diaken_w'=>1,'guru_sm_w'=>2,
            'jadwal_ibadah'=>'Minggu 10.00 WIT','status_verifikasi'=>'Belum Diverifikasi','created_by'=>$penyuluhId,
        ],
        [
            'nama_gereja'=>'Jemaat GKII Maranatha Hitadipa','singkatan'=>'GKII','desa_kelurahan'=>'Hitadipa','distrik'=>'Hitadipa',
            'aras_organisasi'=>'Jemaat','nama_sinode'=>'Gereja Kemah Injil Indonesia (GKII)','tahun_berdiri'=>2005,
            'status_registrasi'=>'Terdaftar','gedung_permanen'=>1,'milik_sendiri'=>1,'kapasitas'=>260,'perpustakaan'=>'Ada',
            'bapak'=>70,'ibu'=>72,'pemuda'=>45,'remaja'=>38,'anak_sm'=>55,'laki'=>140,'perempuan'=>145,
            'pimpinan_nama'=>'Pdt. Agustina Tabuni, S.Th','pimpinan_jk'=>'Wanita','pimpinan_pendidikan'=>'S1 Teologi','pimpinan_mulai'=>'2015-09-01',
            'pengurus_ketua'=>'Niko Sani','pengurus_sekretaris'=>'Petrus Murib','pengurus_bendahara'=>'Yosina Bagau',
            'pdt_w'=>1,'pdm_p'=>1,'majelis_p'=>3,'majelis_w'=>2,'diaken_p'=>2,'diaken_w'=>2,'guru_sm_w'=>3,
            'jadwal_ibadah'=>'Minggu 09.00 WIT','status_verifikasi'=>'Terverifikasi','verified_by'=>1,'verified_at'=>$now,'created_by'=>$penyuluhId,
        ],
        [
            'nama_gereja'=>'Jemaat GIDI Imanuel Tomosiga','singkatan'=>'GIDI','desa_kelurahan'=>'Tomosiga','distrik'=>'Tomosiga',
            'aras_organisasi'=>'Jemaat','nama_sinode'=>'Gereja Injili di Indonesia (GIDI)','tahun_berdiri'=>2012,
            'status_registrasi'=>'Terdaftar','gedung_permanen'=>1,'milik_sendiri'=>1,'kapasitas'=>240,
            'bapak'=>62,'ibu'=>66,'pemuda'=>42,'remaja'=>34,'anak_sm'=>48,'laki'=>126,'perempuan'=>132,
            'pimpinan_nama'=>'Pdt. Daniel Degei','pimpinan_jk'=>'Pria','pimpinan_pendidikan'=>'S1 Teologi','pimpinan_mulai'=>'2019-01-20',
            'pengurus_ketua'=>'Esau Kogoya','pengurus_sekretaris'=>'Lukas Wandik',
            'pdt_p'=>1,'majelis_p'=>4,'diaken_p'=>2,'diaken_w'=>2,'guru_sm_p'=>1,'guru_sm_w'=>2,'penginjil_p'=>1,
            'jadwal_ibadah'=>'Minggu 10.00 WIT','status_verifikasi'=>'Terverifikasi','verified_by'=>1,'verified_at'=>$now,'created_by'=>$penyuluhId,
        ],
        [
            'nama_gereja'=>'Jemaat Kingmi Galilea Wandai','singkatan'=>'KINGMI','desa_kelurahan'=>'Wandai','distrik'=>'Wandai',
            'aras_organisasi'=>'Jemaat','nama_sinode'=>'Gereja Kingmi di Tanah Papua','tahun_berdiri'=>2014,
            'status_registrasi'=>'Belum Terdaftar','gedung_darurat'=>1,'milik_lain'=>1,'kapasitas'=>120,
            'bapak'=>32,'ibu'=>34,'pemuda'=>22,'remaja'=>18,'anak_sm'=>28,'laki'=>66,'perempuan'=>68,
            'pimpinan_nama'=>'Pdp. Yohanis Sani','pimpinan_jk'=>'Pria','pimpinan_pendidikan'=>'SMA Teologi',
            'pengurus_ketua'=>'Musa Belau',
            'pdp_p'=>1,'majelis_p'=>2,'diaken_p'=>1,'guru_sm_w'=>1,
            'jadwal_ibadah'=>'Minggu 09.30 WIT','status_verifikasi'=>'Belum Diverifikasi','created_by'=>$penyuluhId,
        ],
        [
            'nama_gereja'=>'Jemaat GKII Sion Pogapa','singkatan'=>'GKII','desa_kelurahan'=>'Pogapa','distrik'=>'Pogapa',
            'aras_organisasi'=>'Jemaat','nama_sinode'=>'Gereja Kemah Injil Indonesia (GKII)','tahun_berdiri'=>2009,
            'status_registrasi'=>'Terdaftar','gedung_permanen'=>1,'milik_sendiri'=>1,'kapasitas'=>220,
            'bapak'=>55,'ibu'=>58,'pemuda'=>38,'remaja'=>30,'anak_sm'=>44,'laki'=>110,'perempuan'=>115,
            'pimpinan_nama'=>'Pdt. Simon Kobogau','pimpinan_jk'=>'Pria','pimpinan_pendidikan'=>'S1 Teologi','pimpinan_mulai'=>'2017-05-01',
            'pengurus_ketua'=>'Andreas Mote','pengurus_sekretaris'=>'Yulianus Sani',
            'pdt_p'=>1,'majelis_p'=>3,'majelis_w'=>1,'diaken_p'=>2,'diaken_w'=>1,'guru_sm_w'=>2,
            'jadwal_ibadah'=>'Minggu 09.00 WIT','status_verifikasi'=>'Terverifikasi','verified_by'=>1,'verified_at'=>$now,'created_by'=>$penyuluhId,
        ],
    ];

    foreach ($samples as $g) { insert_gereja($pdo, $g); }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Instalasi — <?= e(APP_NAME) ?></title>
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body class="login-body">
  <div class="login-card" style="max-width:520px">
    <div class="login-head"><?= kemenag_seal() ?><h1>Instalasi Sistem Pendataan</h1><p>BIMAS KRISTEN · KEMENAG KAB. INTAN JAYA</p></div>
    <div class="login-form">
      <?php if ($done): ?>
        <div class="flash flash-success" style="margin-bottom:16px">
          <strong>Instalasi berhasil!</strong><br>Basis data dan data contoh telah dibuat.
        </div>
        <div style="font-size:13px;color:var(--ts);line-height:1.8;background:var(--sl-l);padding:14px 16px;border-radius:10px;margin-bottom:18px">
          <strong>Akun default (segera ganti kata sandinya):</strong><br>
          🔑 Administrator — <code>admin</code> / <code>Admin#2026</code><br>
          🔑 Kepala Seksi — <code>kepala</code> / <code>Kepala#2026</code><br>
          🔑 Penyuluh — <code>penyuluh</code> / <code>Penyuluh#2026</code>
        </div>
        <a href="login.php" class="login-btn" style="display:block;text-align:center;text-decoration:none">Lanjut ke Halaman Masuk</a>
        <p style="font-size:11px;color:var(--red);margin-top:14px;text-align:center">⚠️ Demi keamanan, hapus file <code>install.php</code> setelah instalasi selesai.</p>
      <?php else: ?>
        <?php if ($error): ?><div class="login-err"><?= e($error) ?></div><?php endif; ?>
        <p style="font-size:13.5px;color:var(--ts);margin-bottom:18px">
          <?php if ($alreadyInstalled): ?>
            Basis data sudah ada. Menjalankan ulang hanya akan memastikan struktur tabel lengkap dan tidak akan menimpa data yang ada.
          <?php else: ?>
            Klik tombol di bawah untuk membuat basis data SQLite, struktur tabel, akun default, dan data contoh gereja Kristen.
          <?php endif; ?>
        </p>
        <form method="post" action="install.php">
          <?= csrf_field() ?>
          <button type="submit" class="login-btn">Jalankan Instalasi</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
