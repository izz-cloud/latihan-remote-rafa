<?php

/**
 * pages/peminjaman.php
 * Halaman Peminjaman: daftar barang yang sedang dipinjam + riwayat
 */
?>
<!-- ===========================
     HALAMAN PEMINJAMAN
     =========================== -->
<div class="page" id="page-peminjaman">

    <!-- Header Halaman -->
    <div class="page-header">
        <div>
            <div class="page-title">Peminjaman</div>
            <div class="page-subtitle">Catat dan pantau peminjaman barang</div>
        </div>
        <button class="btn btn-primary" onclick="openModal('modal-pinjam')">+ Catat Peminjaman</button>
    </div>

    <!-- Tab Switcher -->
    <div style="display:flex;gap:8px;margin-bottom:16px">
        <button class="btn btn-primary btn-sm" id="tab-pinjam-aktif" onclick="switchPinjamTab('aktif')">🔄 Aktif Dipinjam</button>
        <button class="btn btn-secondary btn-sm" id="tab-pinjam-selesai" onclick="switchPinjamTab('selesai')">✅ Riwayat Selesai</button>
    </div>

    <!-- Card Tabel Peminjaman Aktif -->
    <div class="card" id="card-pinjam-aktif">
        <div class="card-header">
            <div class="card-title">Aktif Dipinjam</div>
            <span id="count-pinjam-aktif" style="font-size:12px;color:var(--text-muted)"></span>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Barang</th>
                        <th>Peminjam</th>
                        <th>Tgl Pinjam</th>
                        <th>Rencana Kembali</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="tabel-pinjam"></tbody>
            </table>

            <!-- Empty State -->
            <div class="empty-state" id="empty-pinjam" style="display:none">
                <div class="empty-icon">🔄</div>
                <div class="empty-text">Tidak ada peminjaman aktif saat ini</div>
            </div>
        </div>
    </div>

    <!-- Card Tabel Riwayat Peminjaman Selesai -->
    <div class="card" id="card-pinjam-selesai" style="display:none">
        <div class="card-header">
            <div class="card-title">Riwayat Peminjaman Selesai</div>
            <span id="count-pinjam-selesai" style="font-size:12px;color:var(--text-muted)"></span>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Barang</th>
                        <th>Peminjam</th>
                        <th>Tgl Pinjam</th>
                        <th>Tgl Dikembalikan</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody id="tabel-pinjam-selesai"></tbody>
            </table>

            <!-- Empty State -->
            <div class="empty-state" id="empty-pinjam-selesai" style="display:none">
                <div class="empty-icon">✅</div>
                <div class="empty-text">Belum ada riwayat pengembalian</div>
            </div>
        </div>
    </div>

</div>