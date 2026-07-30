<?php
require 'config/db.php';
$pdo = getDB();
$rows = $pdo->query('SELECT * FROM kategori')->fetchAll();
var_dump($rows);
