<?php
require_once 'config/db.php';
$pdo = getDB();

$users = [
    'admin' => 'admin123',
    'operator' => 'operator123',
    'guru' => 'guru123'
];

foreach ($users as $username => $password) {
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $pdo->prepare("UPDATE users SET password = ? WHERE username = ?")->execute([$hash, $username]);
    echo "Updated password for: $username\n";
}
