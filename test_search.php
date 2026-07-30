<?php
$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET['q'] = 'Lap';
$_SESSION['user_id'] = 1;
$_SESSION['peran'] = 'admin';

// Mock auth.php and db.php for testing logic directly or just include them
require_once __DIR__ . '/api/barang.php';
