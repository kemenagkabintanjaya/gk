<?php
declare(strict_types=1);
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/helpers.php';
session_boot();
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('data.php'); }
csrf_check();

$u  = current_user();
$id = post_int('id');
$isEdit = $id > 0;

$pdo = db();

// Muat baris lama bila edit, untuk pengecekan hak akses
$old = null;
if ($isEdit) {
    $stmt = $pdo->prepare('SELECT * FROM gereja WHERE id = ?');
    $stmt->execute([$id]);
    $old = $stmt->fetch();
    if (!$old) { flash('Data gereja tidak ditemukan.', 'error'); redirect('data.php'); }
    if (!can('edit_gereja', $old)) { flash('Anda tidak berhak mengubah data ini.', 'error'); redirect('data.php'); }
} else {
    if (!can('create_gereja')) { flash('Anda tidak berhak menambah data.', 'error'); redirect('data.php'); }
}

// Penyuluh hanya boleh untuk distriknya sendiri
$distrik = post_str('distrik');
if (($u['role'] ?? '') === 'penyuluh') {
    $distrik = (string)($u['distrik'] ?? '');
}

$opsiAras  = ['Jemaat', 'Resort/Klasis', 'Wilayah', 'Sinode'];
$opsiReg   = ['Terdaftar', 'Dalam Proses', 'Belum Terdaftar'];
$opsiPerpus= ['Ada', 'Tidak Ada'];
$opsiJk    = ['Pria', 'Wanita'];

$aras = post_str('aras_organisasi');
if (!in_array($aras, $opsiAras, true)) { $aras = 'Jemaat'; }
$reg = post_str('status_registrasi');
if (!in_array($reg, $opsiReg, true)) { $reg = 'Belum Terdaftar'; }
$perpus = post_str('perpustakaan');
if (!in_array($perpus, $opsiPerpus, true)) { $perpus = 'Tidak Ada'; }
$pjk = post_str('pimpinan_jk');
if (!in_array($pjk, $opsiJk, true)) { $pjk = ''; }

$nama = trim(post_str('nama_gereja'));
if ($nama === '') {
    flash('Nama gereja wajib diisi.', 'error');
    redirect($isEdit ? 'gereja_form.php?id=' . $id : 'gereja_form.php');
}

$laki = post_int('laki');
$perempuan = post_int('perempuan');
$totalWarga = post_int('total_warga_gereja');
if ($totalWarga <= 0) { $totalWarga = $laki + $perempuan; }

$personilCols = ['pdt_p','pdt_w','pdm_p','pdm_w','pdp_p','pdp_w','majelis_p','majelis_w',
    'diaken_p','diaken_w','guru_injil_p','guru_injil_w','guru_sm_p','guru_sm_w','penginjil_p','penginjil_w'];
$personil = [];
$totalPersonil = 0;
foreach ($personilCols as $c) { $personil[$c] = post_int($c); $totalPersonil += $personil[$c]; }

$data = [
    'nama_gereja'        => $nama,
    'singkatan'          => post_str('singkatan'),
    'alamat'             => post_str('alamat'),
    'desa_kelurahan'     => post_str('desa_kelurahan'),
    'distrik'            => $distrik,
    'kode_pos'           => post_str('kode_pos'),
    'telepon'            => post_str('telepon'),
    'aras_organisasi'    => $aras,
    'nama_sinode'        => post_str('nama_sinode'),
    'no_imb'             => post_str('no_imb'),
    'tahun_berdiri'      => post_int('tahun_berdiri') ?: null,
    'no_sk_pendirian'    => post_str('no_sk_pendirian'),
    'no_sk_pendeta'      => post_str('no_sk_pendeta'),
    'no_sk_kemenag'      => post_str('no_sk_kemenag'),
    'status_registrasi'  => $reg,
    'no_registrasi_daerah' => post_str('no_registrasi_daerah'),
    'gedung_permanen'    => post_int('gedung_permanen'),
    'gedung_semi'        => post_int('gedung_semi'),
    'gedung_darurat'     => post_int('gedung_darurat'),
    'milik_sendiri'      => post_int('milik_sendiri'),
    'milik_sewa'         => post_int('milik_sewa'),
    'milik_lain'         => post_int('milik_lain'),
    'kapasitas'          => post_int('kapasitas'),
    'perpustakaan'       => $perpus,
    'bapak'              => post_int('bapak'),
    'ibu'                => post_int('ibu'),
    'pemuda'             => post_int('pemuda'),
    'remaja'             => post_int('remaja'),
    'anak_sm'            => post_int('anak_sm'),
    'total_warga_gereja' => $totalWarga,
    'laki'               => $laki,
    'perempuan'          => $perempuan,
    'pimpinan_nama'      => post_str('pimpinan_nama'),
    'pimpinan_jk'        => $pjk,
    'pimpinan_ttl'       => post_str('pimpinan_ttl'),
    'pimpinan_alamat'    => post_str('pimpinan_alamat'),
    'pimpinan_hp'        => post_str('pimpinan_hp'),
    'pimpinan_mulai'     => post_str('pimpinan_mulai'),
    'pimpinan_pendidikan'=> post_str('pimpinan_pendidikan'),
    'pengurus_ketua'     => post_str('pengurus_ketua'),
    'pengurus_sekretaris'=> post_str('pengurus_sekretaris'),
    'pengurus_bendahara' => post_str('pengurus_bendahara'),
    'pengurus_koster'    => post_str('pengurus_koster'),
    'total_personil'     => $totalPersonil,
    'email'              => post_str('email'),
    'media_sosial'       => post_str('media_sosial'),
    'jadwal_ibadah'      => post_str('jadwal_ibadah'),
    'keterangan'         => post_str('keterangan'),
];
$data = array_merge($data, $personil);

try {
    if ($isEdit) {
        $sets = [];
        $params = [];
        foreach ($data as $k => $v) { $sets[] = "$k = :$k"; $params[$k] = $v; }
        $sets[] = "updated_at = datetime('now','localtime')";
        $params['id'] = $id;
        $sql = 'UPDATE gereja SET ' . implode(', ', $sets) . ' WHERE id = :id';
        $pdo->prepare($sql)->execute($params);
        log_activity('ubah_gereja', "Mengubah data: $nama");
        flash('Data gereja berhasil diperbarui.', 'success');
    } else {
        $data['status_verifikasi'] = 'Belum Diverifikasi';
        $data['created_by'] = (int)$u['id'];
        $cols = array_keys($data);
        $ph = array_map(fn($c) => ":$c", $cols);
        $sql = 'INSERT INTO gereja (' . implode(', ', $cols) . ') VALUES (' . implode(', ', $ph) . ')';
        $pdo->prepare($sql)->execute($data);
        log_activity('tambah_gereja', "Menambah data: $nama");
        flash('Data gereja baru berhasil disimpan.', 'success');
    }
    redirect('data.php');
} catch (Throwable $ex) {
    flash('Gagal menyimpan data: ' . $ex->getMessage(), 'error');
    redirect($isEdit ? 'gereja_form.php?id=' . $id : 'gereja_form.php');
}
