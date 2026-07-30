<?php
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

// Mock session
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['nama'] = 'Admin';
$_SESSION['username'] = 'admin';
$_SESSION['peran'] = 'admin';

// Override input stream reading
function get_input() {
    return json_encode([
        'barang_id' => 1,
        'peminjam' => 'Test User',
        'tgl_pinjam' => '2026-05-20',
        'tgl_kembali' => '',
        'keterangan' => 'Test',
    ]);
}

// We'll require the script, but we need to intercept file_get_contents. 
// Instead of that, let's just create a test connection and run the same code.

require_once 'config/db.php';
$pdo = getDB();

$d = [
    'barang_id' => 1, // Laptop ASUS (Tersedia)
    'peminjam' => 'Test User',
    'tgl_pinjam' => '2026-05-20',
    'tgl_kembali' => null,
    'keterangan' => 'Test',
];

$cek = $pdo->prepare('SELECT status, nama FROM barang WHERE id=?');
$cek->execute([$d['barang_id']]);
$brg = $cek->fetch();
var_dump($brg);

try {
    $stmt = $pdo->prepare("
        INSERT INTO peminjaman (barang_id, peminjam, tgl_pinjam, tgl_kembali, keterangan, user_id)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $res = $stmt->execute([
        $d['barang_id'],
        $d['peminjam'],
        $d['tgl_pinjam'],
        $d['tgl_kembali'],
        $d['keterangan'],
        1,
    ]);
    var_dump($res);
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
