<?php

/**
 * pages/dashboard.php — Halaman Dashboard (semua peran)
 */
$peran = $_SESSION['peran'];
?>
<!-- HALAMAN DASHBOARD -->
<div class="page active" id="page-dashboard">

    <!-- Header Halaman -->
    <div class="page-header">
        <div>
            <div class="page-title">Dashboard</div>
            <div class="page-subtitle">Ringkasan inventaris sekolah</div>
        </div>
        <?php if ($peran === 'admin'): ?>
            <button class="btn btn-primary" onclick="openModal('modal-tambah')">+ Tambah Barang</button>
        <?php endif; ?>
    </div>

    <!-- Statistik Ringkasan -->
    <div class="stats-grid">
        <div class="stat-card" style="--card-color:#4f8aff">
            <div class="stat-icon">📦</div>
            <div class="stat-value" id="stat-total">—</div>
            <div class="stat-label">Total Barang</div>
        </div>
        <div class="stat-card" style="--card-color:#34d399">
            <div class="stat-icon">✅</div>
            <div class="stat-value" id="stat-baik">—</div>
            <div class="stat-label">Kondisi Baik</div>
        </div>
        <div class="stat-card" style="--card-color:#fb923c">
            <div class="stat-icon">⚠️</div>
            <div class="stat-value" id="stat-cukup">—</div>
            <div class="stat-label">Cukup Baik</div>
        </div>
        <div class="stat-card" style="--card-color:#f87171">
            <div class="stat-icon">🔴</div>
            <div class="stat-value" id="stat-rusak">—</div>
            <div class="stat-label">Perlu Perbaikan</div>
        </div>
        <div class="stat-card" style="--card-color:#a78bfa">
            <div class="stat-icon">🔄</div>
            <div class="stat-value" id="stat-pinjam">—</div>
            <div class="stat-label">Sedang Dipinjam</div>
        </div>
        <?php if ($peran === 'admin'): ?>
            <div class="stat-card" style="--card-color:#38bdf8">
                <div class="stat-icon">👥</div>
                <div class="stat-value" id="stat-users">—</div>
                <div class="stat-label">Pengguna Aktif</div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Chart & Barang Terbaru -->
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px">

        <!-- Chart Per Kategori -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">Per Kategori</div>
            </div>
            <div style="padding:16px 20px" id="kategori-chart">
                <div class="empty-state" style="padding:20px 0">
                    <div class="empty-icon">📊</div>
                    <div>Memuat data...</div>
                </div>
            </div>
        </div>

        <!-- Barang Terbaru -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">Barang Terbaru</div>
                <button class="btn btn-secondary btn-sm"
                    onclick="showPage('barang', document.querySelectorAll('.nav-item')[1])">
                    Lihat Semua
                </button>
            </div>
            <div id="barang-terbaru">
                <div class="empty-state" style="padding:30px 0">
                    <div class="empty-icon">📦</div>
                    <div>Memuat data...</div>
                </div>
            </div>
        </div>

    </div>
</div>