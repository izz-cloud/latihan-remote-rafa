<?php

/**
 * logout.php — Hapus session dan redirect ke logina
 */
session_start();
if (!empty($_SESSION['user_id'])) {
    require_once __DIR__ . '/config/db.php';
    $pdo = getDB();
    $pdo->prepare('INSERT INTO riwayat (aksi, user_id) VALUES (?, ?)')
        ->execute(["Logout: {$_SESSION['nama']} ({$_SESSION['peran']})", $_SESSION['user_id']]);
}
session_destroy();
header('Location: login.php');
exit;
