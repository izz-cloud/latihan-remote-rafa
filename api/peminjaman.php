<?php

/**
 * api/peminjaman.php — CRUD Peminjaman
 * GET  → list peminjaman (filter by status)
 * POST → catat peminjaman baru
 * PUT  → kembalikan barang
 */
define('BASE_URL', '../');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

header('Content-Type: application/json');

// Auth check khusus API: kembalikan JSON error, bukan redirect HTML
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Sesi telah berakhir, silakan login kembali']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$pdo    = getDB();
$user   = getCurrentUser();

// ─── GET ──────────────────────────────────────────────────────────────────────
if ($method === 'GET') {
    $status = $_GET['status'] ?? 'Aktif';
    $stmt = $pdo->prepare("
        SELECT p.*, b.nama as barang_nama, k.ikon as barang_ikon,
               b.kode as barang_kode, b.lokasi as barang_lokasi,
               u.nama as pencatat_nama
        FROM peminjaman p
        JOIN barang b ON b.id = p.barang_id
        JOIN kategori k ON k.id = b.kategori_id
        LEFT JOIN users u ON u.id = p.user_id
        WHERE p.status = ?
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([$status]);
    echo json_encode($stmt->fetchAll());
    exit;
}

// ─── POST (Catat Peminjaman) ──────────────────────────────────────────────────
if ($method === 'POST') {
    try {
        $d = json_decode(file_get_contents('php://input'), true);

        if (empty($d['barang_id']) || empty($d['peminjam'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Barang dan Peminjam wajib diisi']);
            exit;
        }

        // Cek barang tersedia
        $cek = $pdo->prepare('SELECT status, nama FROM barang WHERE id=?');
        $cek->execute([$d['barang_id']]);
        $brg = $cek->fetch();
        if (!$brg || $brg['status'] !== 'Tersedia') {
            http_response_code(400);
            echo json_encode(['error' => 'Barang tidak tersedia untuk dipinjam']);
            exit;
        }

        $tglPinjam  = !empty($d['tgl_pinjam'])  ? $d['tgl_pinjam']  : date('Y-m-d');
        $tglKembali = !empty($d['tgl_kembali']) ? $d['tgl_kembali'] : null;
        $keterangan = !empty($d['keterangan'])  ? $d['keterangan']  : null;

        $fields = ['barang_id', 'peminjam', 'tgl_pinjam', 'user_id'];
        $placeholders = ['?', '?', '?', '?'];
        $values = [$d['barang_id'], $d['peminjam'], $tglPinjam, $user['id']];

        if ($tglKembali) {
            $fields[] = 'tgl_kembali';
            $placeholders[] = '?';
            $values[] = $tglKembali;
        }
        if ($keterangan) {
            $fields[] = 'keterangan';
            $placeholders[] = '?';
            $values[] = $keterangan;
        }

        $sql = "INSERT INTO peminjaman (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);

        $pdo->prepare("UPDATE barang SET status='Dipinjam' WHERE id=?")->execute([$d['barang_id']]);

        $pdo->prepare('INSERT INTO riwayat (aksi, user_id) VALUES (?, ?)')
            ->execute(["Barang \"{$brg['nama']}\" dipinjam oleh {$d['peminjam']}", $user['id']]);

        echo json_encode(['success' => true]);
        exit;
    } catch (\Throwable $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Server Error: ' . $e->getMessage() . ' di baris ' . $e->getLine()]);
        exit;
    }
}

// ─── PUT (Kembalikan) ─────────────────────────────────────────────────────────
if ($method === 'PUT') {
    try {
        $raw = file_get_contents('php://input');
        $d   = json_decode($raw, true);
        $id  = (int) ($d['id'] ?? 0);
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID tidak valid']);
            exit;
        }

        $pinjam = $pdo->prepare('SELECT p.*, b.nama as barang_nama FROM peminjaman p JOIN barang b ON b.id=p.barang_id WHERE p.id=?');
        $pinjam->execute([$id]);
        $p = $pinjam->fetch();
        if (!$p) {
            http_response_code(404);
            echo json_encode(['error' => 'Data peminjaman tidak ditemukan']);
            exit;
        }

        if ($p['status'] === 'Selesai') {
            http_response_code(400);
            echo json_encode(['error' => 'Barang sudah dikembalikan sebelumnya']);
            exit;
        }

        $pdo->beginTransaction();

        $pdo->prepare("UPDATE peminjaman SET status='Selesai', tgl_dikembalikan=CURDATE() WHERE id=?")->execute([$id]);
        $pdo->prepare("UPDATE barang SET status='Tersedia' WHERE id=?")->execute([$p['barang_id']]);

        $pdo->prepare('INSERT INTO riwayat (aksi, user_id) VALUES (?, ?)')
            ->execute(["Barang \"{$p['barang_nama']}\" dikembalikan oleh {$p['peminjam']}", $user['id']]);

        $pdo->commit();

        echo json_encode(['success' => true]);
        exit;
    } catch (\Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        http_response_code(500);
        echo json_encode(['error' => 'Server Error: ' . $e->getMessage()]);
        exit;
    }
}

// ─── DELETE (Hapus Data Peminjaman) ───────────────────────────────────────────
if ($method === 'DELETE') {
    requireRole(['admin']);
    $id = (int) ($_GET['id'] ?? 0);
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID tidak valid']);
        exit;
    }

    $pinjam = $pdo->prepare('SELECT p.*, b.nama as barang_nama FROM peminjaman p JOIN barang b ON b.id=p.barang_id WHERE p.id=?');
    $pinjam->execute([$id]);
    $p = $pinjam->fetch();
    if (!$p) {
        http_response_code(404);
        echo json_encode(['error' => 'Data tidak ditemukan']);
        exit;
    }

    // Kembalikan status barang
    if ($p['status'] === 'Aktif') {
        $pdo->prepare("UPDATE barang SET status='Tersedia' WHERE id=?")->execute([$p['barang_id']]);
    }
    
    // Hapus data peminjaman
    $pdo->prepare('DELETE FROM peminjaman WHERE id=?')->execute([$id]);

    $pdo->prepare('INSERT INTO riwayat (aksi, user_id) VALUES (?, ?)')
        ->execute(["Catatan peminjaman barang \"{$p['barang_nama']}\" dihapus", $user['id']]);

    echo json_encode(['success' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
