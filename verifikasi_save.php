<?php
declare(strict_types=1);
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/helpers.php';
if (!file_exists(DB_PATH)) { redirect('install.php'); }
session_boot();
require_login();
require_role(['administrator', 'kepala_seksi']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('verifikasi.php'); }
csrf_check();

$u      = current_user();
$id     = post_int('id');
$status = post_str('status_verifikasi');
$catatan= post_str('catatan_verifikasi');

if (!in_array($status, ['Terverifikasi', 'Belum Diverifikasi', 'Ditolak'], true)) {
    flash('Status verifikasi tidak valid.', 'error');
    redirect('verifikasi.php');
}
if ($id <= 0) { redirect('verifikasi.php'); }

$verBy = $status === 'Belum Diverifikasi' ? null : (int)$u['id'];
$verAt = $status === 'Belum Diverifikasi' ? null : date('Y-m-d H:i:s');

$sql = db()->prepare('UPDATE gereja SET status_verifikasi = ?, catatan_verifikasi = ?, verified_by = ?, verified_at = ?, updated_at = ? WHERE id = ?');
$sql->execute([$status, $catatan, $verBy, $verAt, date('Y-m-d H:i:s'), $id]);

log_activity('verifikasi', "Mengubah status verifikasi #$id menjadi $status");
flash('Status verifikasi berhasil disimpan.', 'success');
redirect('verifikasi.php');
