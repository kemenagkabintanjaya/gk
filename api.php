<?php
declare(strict_types=1);
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/stats.php';
session_boot();

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Tidak terautentikasi']);
    exit;
}

$action = $_GET['action'] ?? 'statistik';
$u = current_user();

if ($action === 'statistik') {
    echo json_encode(compute_statistik(), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'data') {
    $sql = "SELECT id,
                   nama_gereja      AS Nama_Gereja,
                   aras_organisasi  AS Aras,
                   nama_sinode      AS Sinode,
                   distrik          AS Distrik,
                   desa_kelurahan   AS Desa_Kelurahan,
                   pimpinan_nama    AS Pimpinan_Jemaat,
                   total_warga_gereja AS Total_Warga_Gereja,
                   status_verifikasi  AS Status_Verifikasi
            FROM gereja";
    $params = [];
    if (($u['role'] ?? '') === 'penyuluh' && !empty($u['distrik'])) {
        $sql .= ' WHERE distrik = ?';
        $params[] = $u['distrik'];
    }
    $sql .= ' ORDER BY nama_gereja';
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    echo json_encode(['data' => $stmt->fetchAll()], JSON_UNESCAPED_UNICODE);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Aksi tidak dikenal']);
