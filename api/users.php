<?php

/**
 * api/users.php — Kelola User (Admin Only)
 * GET    → list semua user
 * POST   → tambah user
 * PUT    → edit user
 * DELETE → hapus (nonaktifkan) user
 */
define('BASE_URL', '../');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
requireRole('admin');

header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];
$pdo    = getDB();
$user   = getCurrentUser();

if ($method === 'GET') {
    $rows = $pdo->query("SELECT id, nama, username, peran, aktif, created_at FROM users ORDER BY id")->fetchAll();
    echo json_encode($rows);
    exit;
}

if ($method === 'POST') {
    $d = json_decode(file_get_contents('php://input'), true);
    if (empty($d['nama']) || empty($d['username']) || empty($d['password'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Nama, username, dan password wajib diisi']);
        exit;
    }
    // Cek duplikat
    $cek = $pdo->prepare('SELECT id FROM users WHERE username=?');
    $cek->execute([$d['username']]);
    if ($cek->fetch()) {
        http_response_code(409);
        echo json_encode(['error' => 'Username sudah digunakan']);
        exit;
    }

    $hash = password_hash($d['password'], PASSWORD_BCRYPT);
    $stmt = $pdo->prepare('INSERT INTO users (nama, username, password, peran) VALUES (?, ?, ?, ?)');
    $stmt->execute([$d['nama'], $d['username'], $hash, $d['peran'] ?? 'guru']);
    $newId = $pdo->lastInsertId();

    $pdo->prepare('INSERT INTO riwayat (aksi, user_id) VALUES (?, ?)')
        ->execute(["User \"{$d['username']}\" ({$d['peran']}) ditambahkan", $user['id']]);

    echo json_encode(['success' => true, 'id' => $newId]);
    exit;
}

if ($method === 'PUT') {
    $d  = json_decode(file_get_contents('php://input'), true);
    $id = (int) ($d['id'] ?? 0);
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID tidak valid']);
        exit;
    }

    // Tidak boleh hapus admin utama
    if ($id === 1 && isset($d['peran']) && $d['peran'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Peran admin utama tidak dapat diubah']);
        exit;
    }

    if (!empty($d['password'])) {
        $pdo->prepare('UPDATE users SET nama=?, username=?, password=?, peran=?, aktif=? WHERE id=?')
            ->execute([$d['nama'], $d['username'], password_hash($d['password'], PASSWORD_BCRYPT), $d['peran'] ?? 'guru', $d['aktif'] ?? 1, $id]);
    } else {
        $pdo->prepare('UPDATE users SET nama=?, username=?, peran=?, aktif=? WHERE id=?')
            ->execute([$d['nama'], $d['username'], $d['peran'] ?? 'guru', $d['aktif'] ?? 1, $id]);
    }

    $pdo->prepare('INSERT INTO riwayat (aksi, user_id) VALUES (?, ?)')
        ->execute(["User \"{$d['username']}\" diperbarui", $user['id']]);

    echo json_encode(['success' => true]);
    exit;
}

if ($method === 'DELETE') {
    $id = (int) ($_GET['id'] ?? 0);
    if (!$id || $id === $user['id']) {
        http_response_code(400);
        echo json_encode(['error' => 'Tidak dapat menghapus akun sendiri atau ID tidak valid']);
        exit;
    }
    if ($id === 1) {
        http_response_code(403);
        echo json_encode(['error' => 'Admin utama tidak dapat dihapus']);
        exit;
    }
    $uname = $pdo->prepare('SELECT username FROM users WHERE id=?');
    $uname->execute([$id]);
    $row = $uname->fetch();

    $pdo->prepare("UPDATE users SET aktif=0 WHERE id=?")->execute([$id]);
    $pdo->prepare('INSERT INTO riwayat (aksi, user_id) VALUES (?, ?)')
        ->execute(["User \"{$row['username']}\" dinonaktifkan", $user['id']]);

    echo json_encode(['success' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
