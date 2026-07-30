<?php

/**
 * pages/laporan.php — Halaman Laporan & Export CSV/PDF (Admin & Guru)
 */
?>
<div class="page" id="page-laporan">
    <div class="page-header">
        <div>
            <div class="page-title">📈 Laporan Inventaris</div>
            <div class="page-subtitle">Filter, analisis, dan export data inventaris</div>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
            <button class="btn btn-secondary" onclick="loadLaporan()">🔄 Refresh</button>
            <button class="btn btn-secondary" onclick="exportCSV()">📄 Export CSV</button>
            <button class="btn btn-primary" onclick="exportPDF()">📑 Export PDF Barang</button>
            <button class="btn btn-primary" onclick="exportPeminjamanPDF()">📋 Export PDF Peminjaman</button>
        </div>
    </div>

    <!-- Filter -->
    <div class="card" style="margin-bottom:20px">
        <div class="card-header">
            <div class="card-title">Filter Laporan</div>
        </div>
        <div style="padding:16px 20px;display:flex;gap:12px;flex-wrap:wrap">
            <select id="lap-kategori" style="flex:1;min-width:140px;background:var(--surface2);border:1.5px solid var(--border);border-radius:8px;padding:8px 12px;color:var(--text);font-size:13px" onchange="loadLaporan()">
                <option value="">Semua Kategori</option>
            </select>
            <select id="lap-kondisi" style="flex:1;min-width:140px;background:var(--surface2);border:1.5px solid var(--border);border-radius:8px;padding:8px 12px;color:var(--text);font-size:13px" onchange="loadLaporan()">
                <option value="">Semua Kondisi</option>
                <option value="Baik">Baik</option>
                <option value="Cukup Baik">Cukup Baik</option>
                <option value="Rusak">Rusak</option>
            </select>
            <select id="lap-status" style="flex:1;min-width:140px;background:var(--surface2);border:1.5px solid var(--border);border-radius:8px;padding:8px 12px;color:var(--text);font-size:13px" onchange="loadLaporan()">
                <option value="">Semua Status</option>
                <option value="Tersedia">Tersedia</option>
                <option value="Dipinjam">Dipinjam</option>
            </select>
        </div>
    </div>

    <!-- Statistik ringkasan -->
    <div class="stats-grid" id="lap-stats" style="margin-bottom:20px"></div>

    <!-- Tabel -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">Data Barang</div>
            <span id="lap-count" style="font-size:12px;color:var(--text-muted)"></span>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Barang</th>
                        <th>Kategori</th>
                        <th>Kode</th>
                        <th>Tahun</th>
                        <th>Kondisi</th>
                        <th>Sumber</th>
                        <th>Lokasi</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="tabel-laporan"></tbody>
            </table>
            <div id="empty-laporan" class="empty-state" style="display:none">
                <div class="empty-icon">📈</div>
                <div>Tidak ada data sesuai filter</div>
            </div>
        </div>
    </div>
</div>