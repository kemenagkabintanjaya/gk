<?php
declare(strict_types=1);
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/helpers.php';
if (!file_exists(DB_PATH)) { redirect('install.php'); }
session_boot();
require_login();
require_role(['administrator']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('users.php'); }
csrf_check();

$id = post_int('id');
$me = (int)current_user()['id'];
if ($id === $me) { flash('Anda tidak dapat menghapus akun sendiri.', 'error'); redirect('users.php'); }

if ($id > 0) {
    // Jangan biarkan administrator terakhir terhapus
    $admins = (int)db()->query("SELECT COUNT(*) FROM users WHERE role = 'administrator' AND aktif = 1")->fetchColumn();
    $target = db()->prepare('SELECT username, role FROM users WHERE id = ?');
    $target->execute([$id]);
    $t = $target->fetch();
    if (!$t) { flash('Pengguna tidak ditemukan.', 'error'); redirect('users.php'); }
    if ($t['role'] === 'administrator' && $admins <= 1) {
        flash('Tidak dapat menghapus administrator terakhir.', 'error');
        redirect('users.php');
    }
    $del = db()->prepare('DELETE FROM users WHERE id = ?');
    $del->execute([$id]);
    log_activity('delete_user', "Menghapus pengguna: {$t['username']} (#$id)");
    flash('Pengguna berhasil dihapus.', 'success');
}
redirect('users.php');
