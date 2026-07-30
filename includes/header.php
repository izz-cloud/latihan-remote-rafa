<?php

/**
 * includes/header.php — Head HTML + session start + auth check
 */
define('BASE_URL', '');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
requireLogin();
$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inventaris Sekolah — <?= ucfirst(htmlspecialchars($currentUser['peran'])) ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
  <script>
    // Kirimkan data session ke JavaScript
    const APP_ROLE = <?= json_encode($currentUser['peran']) ?>;
    const APP_USER = <?= json_encode($currentUser['nama']) ?>;
    const APP_UID = <?= json_encode($currentUser['id']) ?>;
  </script>
</head>

<body>