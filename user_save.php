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

$id       = post_int('id');
$nama     = post_str('nama_lengkap');
$username = strtolower(trim(post_str('username')));
$role     = post_str('role');
$distrik  = post_str('distrik');
$password = post_str('password');
$aktif    = post_int('aktif') === 1 ? 1 : 0;

if ($nama === '' || $username === '') { flash('Nama dan username wajib diisi.', 'error'); redirect($id ? 'user_form.php?id=' . $id : 'user_form.php'); }
if (!preg_match('/^[a-z0-9_.]+$/', $username)) { flash('Format username tidak valid.', 'error'); redirect($id ? 'user_form.php?id=' . $id : 'user_form.php'); }
if (!in_array($role, ['administrator', 'kepala_seksi', 'penyuluh'], true)) { $role = 'penyuluh'; }
if ($role !== 'penyuluh') { $distrik = null; }
if ($distrik === '') { $distrik = null; }

// Cek keunikan username
$chk = db()->prepare('SELECT id FROM users WHERE username = ? AND id != ?');
$chk->execute([$username, $id]);
if ($chk->fetch()) { flash('Username sudah digunakan.', 'error'); redirect($id ? 'user_form.php?id=' . $id : 'user_form.php'); }

try {
    if ($id > 0) {
        if ($password !== '') {
            if (strlen($password) < 6) { flash('Kata sandi minimal 6 karakter.', 'error'); redirect('user_form.php?id=' . $id); }
            $sql = db()->prepare('UPDATE users SET nama_lengkap=?, username=?, role=?, distrik=?, aktif=?, password_hash=? WHERE id=?');
            $sql->execute([$nama, $username, $role, $distrik, $aktif, password_hash($password, PASSWORD_DEFAULT), $id]);
        } else {
            $sql = db()->prepare('UPDATE users SET nama_lengkap=?, username=?, role=?, distrik=?, aktif=? WHERE id=?');
            $sql->execute([$nama, $username, $role, $distrik, $aktif, $id]);
        }
        log_activity('update_user', "Memperbarui pengguna: $username (#$id)");
        flash('Pengguna berhasil diperbarui.', 'success');
    } else {
        if (strlen($password) < 6) { flash('Kata sandi minimal 6 karakter.', 'error'); redirect('user_form.php'); }
        $sql = db()->prepare('INSERT INTO users (username, password_hash, nama_lengkap, role, distrik, aktif) VALUES (?,?,?,?,?,?)');
        $sql->execute([$username, password_hash($password, PASSWORD_DEFAULT), $nama, $role, $distrik, $aktif]);
        log_activity('create_user', "Menambah pengguna: $username");
        flash('Pengguna baru berhasil ditambahkan.', 'success');
    }
} catch (Throwable $ex) {
    flash('Gagal menyimpan pengguna: ' . $ex->getMessage(), 'error');
    redirect('users.php');
}
redirect('users.php');
