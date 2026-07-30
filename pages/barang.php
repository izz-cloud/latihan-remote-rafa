<?php

/**
 * pages/barang.php
 * Halaman Daftar Barang: tabel dengan search & filter
 */
?>
<!-- ===========================
     HALAMAN DAFTAR BARANG
     =========================== -->
<div class="page" id="page-barang">

    <!-- Header Halaman -->
    <div class="page-header">
        <div>
            <div class="page-title">Daftar Barang</div>
            <div class="page-subtitle">Kelola semua inventaris Anda</div>
        </div>
        <button class="btn btn-primary" onclick="openModal('modal-tambah')">+ Tambah Barang</button>
    </div>

    <!-- Card Tabel -->
    <div class="card">

        <!-- Toolbar: Search & Filter -->
        <div class="card-header">
            <div class="toolbar">

                <!-- Search -->
                <div class="search-wrap">
                    <input type="text"
                        class="search-input"
                        placeholder="Cari barang..."
                        id="search-barang"
                        oninput="filterBarang()">
                </div>

                <!-- Filter Kategori -->
                <select class="filter-select" id="filter-kategori" onchange="filterBarang()">
                    <option value="">Semua Kategori</option>
                    <option>Elektronik</option>
                    <option>Furnitur</option>
                    <option>Alat Tulis</option>
                    <option>Media Pembelajaran</option>
                    <option>Lainnya</option>
                </select>

                <!-- Filter Kondisi -->
                <select class="filter-select" id="filter-kondisi" onchange="filterBarang()">
                    <option value="">Semua Kondisi</option>
                    <option>Baik</option>
                    <option>Cukup Baik</option>
                    <option>Rusak</option>
                </select>

            </div>
        </div>

        <!-- Tabel Barang -->
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Barang</th>
                        <th>Kategori</th>
                        <th>Kode</th>
                        <th>Tahun</th>
                        <th>Kondisi</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="tabel-barang"></tbody>
            </table>

            <!-- Empty State -->
            <div class="empty-state" id="empty-barang" style="display:none">
                <div class="empty-icon">📦</div>
                <div class="empty-text">Belum ada barang. Tambahkan barang pertama Anda!</div>
            </div>
        </div>

    </div>
</div>