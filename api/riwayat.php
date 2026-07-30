<?php

/**
 * api/riwayat.php — Riwayat Aktivitas
 */
define('BASE_URL', '../');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
requireLogin();

header('Content-Type: application/json');
$pdo = getDB();

$rows = $pdo->query("
    SELECT r.aksi, r.created_at, u.nama as user_nama, u.peran
    FROM riwayat r
    LEFT JOIN users u ON u.id = r.user_id
    ORDER BY r.created_at DESC
    LIMIT 100
")->fetchAll();

echo json_encode($rows);
