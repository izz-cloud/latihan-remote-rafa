<?php
require 'config/db.php';
$pdo = getDB();

$sql = file_get_contents('inventaris_db.sql');

// execute the SQL
try {
    $pdo->exec($sql);
    echo "Import success\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
