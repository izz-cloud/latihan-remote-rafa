<?php

/**
 * index.php — Entry point utama aplikasi Inventaris Sekolah kita.
 * Session auth dibajak di includes/header.php
 */
require_once 'includes/header.php';
$peran = $_SESSION['peran'];
?>

<!-- Tombol Menu Mobile -->
<button class="mobile-menu-btn" onclick="toggleSidebar()">☰</button>

<!-- App Wrapper -->
<div class="app">

      <?php require_once 'includes/sidebar.php'; ?>

      <main class="main">

            <?php require_once 'pages/dashboard.php'; ?>
            <?php require_once 'pages/barang.php'; ?>
            <?php require_once 'pages/peminjaman.php'; ?>
            <?php require_once 'pages/kondisi.php'; ?>


            <?php if ($peran === 'admin' || $peran === 'guru'): ?>
                  <?php require_once 'pages/laporan.php'; ?>
            <?php endif; ?>

            <?php if ($peran === 'admin'): ?>
                  <?php require_once 'pages/kelola_users.php'; ?>
            <?php endif; ?>

      </main>
</div>

<!-- Modals -->
<?php require_once 'modals/modal_tambah.php'; ?>
<?php require_once 'modals/modal_detail.php'; ?>
<?php require_once 'modals/modal_pinjam.php'; ?>

<?php require_once 'includes/footer.php'; ?>