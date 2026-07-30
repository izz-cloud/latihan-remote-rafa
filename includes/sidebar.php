<?php

/**
 * includes/sidebar.php — Sidebar navigasi berbasis peran
 */
$peran = $_SESSION['peran'];
$nama  = $_SESSION['nama'];
$initials = strtoupper(substr($nama, 0, 2));

$badgeColors = [
    'admin'    => ['bg' => 'rgba(248,113,113,0.15)', 'c' => '#f87171', 'label' => '👑 Admin'],
    'guru'     => ['bg' => 'rgba(52,211,153,0.15)',  'c' => '#34d399', 'label' => '📚 Guru'],
];
$badge = $badgeColors[$peran];
?>
<!-- SIDEBAR NAVIGASI -->
<aside class="sidebar" id="sidebar">

    <!-- Logo -->
    <div class="sidebar-logo">
        <div class="logo-mark">📦 INVENTARIS</div>
        <div class="logo-sub">Sistem Inventaris Sekolah</div>
    </div>

    <!-- Navigasi Utama -->
    <nav class="sidebar-nav">
        <div class="nav-section-label">Menu Utama</div>

        <button class="nav-item active" onclick="showPage('dashboard', this)">
            <span class="icon">📊</span> Dashboard
        </button>

        <button class="nav-item" onclick="showPage('barang', this)">
            <span class="icon">📦</span> Daftar Barang
            <span class="nav-badge" id="badge-total">0</span>
        </button>

        <button class="nav-item" onclick="showPage('peminjaman', this)">
            <span class="icon">🔄</span> Peminjaman
            <span class="nav-badge" id="badge-pinjam" style="display:none"></span>
        </button>

        <div class="nav-section-label" style="margin-top:8px">Laporan</div>

        <button class="nav-item" onclick="showPage('kondisi', this)">
            <span class="icon">🔧</span> Kondisi Barang
        </button>



        <?php if ($peran === 'admin' || $peran === 'guru'): ?>
            <button class="nav-item" onclick="showPage('laporan', this)">
                <span class="icon">📈</span> Laporan &amp; Export
            </button>
        <?php endif; ?>

        <?php if ($peran === 'admin'): ?>
            <div class="nav-section-label" style="margin-top:8px">Administrasi</div>
            <button class="nav-item" onclick="showPage('kelola-users', this)">
                <span class="icon">👥</span> Kelola User
            </button>
        <?php endif; ?>
    </nav>

    <!-- Info Pengguna -->
    <div class="sidebar-user">
        <div class="user-avatar"><?= htmlspecialchars($initials) ?></div>
        <div style="flex:1;min-width:0">
            <div class="user-name" title="<?= htmlspecialchars($nama) ?>"><?= htmlspecialchars($nama) ?></div>
            <div class="user-role" style="color:<?= $badge['c'] ?>"><?= $badge['label'] ?></div>
        </div>
        <a href="logout.php" class="logout-btn" title="Logout">⏻</a>
    </div>

</aside>