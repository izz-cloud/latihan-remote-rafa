<?php
require_once 'config/db.php';

try {
    $pdo = getDB();
    echo "DB Connection SUCCESS\n";

    $stmt = $pdo->query("SELECT * FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "User Count: " . count($users) . "\n";
    foreach ($users as $u) {
        echo "User: {$u['username']} | Aktif: {$u['aktif']}\n";
        $passToTest = "{$u['username']}123";
        $valid = password_verify($passToTest, $u['password']) ? 'VALID' : 'INVALID';
        echo "Password test ($passToTest): $valid\n\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
