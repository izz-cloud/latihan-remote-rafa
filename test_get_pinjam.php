<?php
require 'config/db.php';
$pdo = getDB();

$stmt = $pdo->prepare("
    SELECT p.*, b.nama as barang_nama, k.ikon as barang_ikon,
           b.kode as barang_kode, b.lokasi as barang_lokasi,
           u.nama as pencatat_nama
    FROM peminjaman p
    JOIN barang b ON b.id = p.barang_id
    JOIN kategori k ON k.id = b.kategori_id
    LEFT JOIN users u ON u.id = p.user_id
    WHERE p.status = 'Aktif'
    ORDER BY p.created_at DESC
");
$stmt->execute();
var_dump($stmt->fetchAll());
