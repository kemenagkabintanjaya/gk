<?php
declare(strict_types=1);
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/helpers.php';
if (!file_exists(DB_PATH)) { redirect('install.php'); }
session_boot();
require_login();
require_role(['administrator']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('data.php'); }
csrf_check();

$id = post_int('id');
if ($id > 0) {
    $stmt = db()->prepare('SELECT nama_gereja FROM gereja WHERE id = ?');
    $stmt->execute([$id]);
    $nama = $stmt->fetchColumn();
    if ($nama !== false) {
        $del = db()->prepare('DELETE FROM gereja WHERE id = ?');
        $del->execute([$id]);
        log_activity('delete_gereja', "Menghapus data: $nama (#$id)");
        flash('Data gereja berhasil dihapus.', 'success');
    } else {
        flash('Data tidak ditemukan.', 'error');
    }
}
redirect('data.php');
