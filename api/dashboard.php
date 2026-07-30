<?php

/**
 * api/dashboard.php — Statistik untuk dashboard
 */
define('BASE_URL', '../');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
requireLogin();

header('Content-Type: application/json');

$pdo = getDB();

$total   = (int) $pdo->query('SELECT COUNT(*) FROM barang')->fetchColumn();
$baik    = (int) $pdo->query("SELECT COUNT(*) FROM barang WHERE kondisi='Baik'")->fetchColumn();
$cukup   = (int) $pdo->query("SELECT COUNT(*) FROM barang WHERE kondisi='Cukup Baik'")->fetchColumn();
$rusak   = (int) $pdo->query("SELECT COUNT(*) FROM barang WHERE kondisi='Rusak'")->fetchColumn();
$pinjam  = (int) $pdo->query("SELECT COUNT(*) FROM barang WHERE status='Dipinjam'")->fetchColumn();
$terlambat = (int) $pdo->query("SELECT COUNT(*) FROM peminjaman WHERE status='Aktif' AND tgl_kembali < CURDATE()")->fetchColumn();

// Per kategori
$katRows = $pdo->query("
    SELECT k.nama, k.ikon, COUNT(b.id) as jumlah
    FROM kategori k
    LEFT JOIN barang b ON b.kategori_id = k.id
    GROUP BY k.id, k.nama, k.ikon
    ORDER BY jumlah DESC
")->fetchAll();

// Barang terbaru
$terbaru = $pdo->query("
    SELECT b.*, k.nama as kategori, k.ikon
    FROM barang b
    JOIN kategori k ON k.id = b.kategori_id
    ORDER BY b.created_at DESC LIMIT 5
")->fetchAll();

// Total user (admin only)
$totalUser = null;
if ($_SESSION['peran'] === 'admin') {
    $totalUser = (int) $pdo->query('SELECT COUNT(*) FROM users WHERE aktif=1')->fetchColumn();
}

echo json_encode([
    'total'      => $total,
    'baik'       => $baik,
    'cukup'      => $cukup,
    'rusak'      => $rusak,
    'pinjam'     => $pinjam,
    'terlambat'  => $terlambat,
    'total_user' => $totalUser,
    'kategori'   => $katRows,
    'terbaru'    => $terbaru,
]);
