<?php

/**
 * api/kategori.php — Daftar Kategori
 */
define('BASE_URL', '../');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
requireLogin();

header('Content-Type: application/json');
$pdo = getDB();
echo json_encode($pdo->query('SELECT * FROM kategori ORDER BY nama')->fetchAll());
