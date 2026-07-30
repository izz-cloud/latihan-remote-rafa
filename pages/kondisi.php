<?php

/**
 * pages/kondisi.php
 * Halaman Kondisi Barang: monitor barang yang perlu perhatian
 */
?>
<!-- ===========================
     HALAMAN KONDISI BARANG
     =========================== -->
<div class="page" id="page-kondisi">

    <!-- Header Halaman -->
    <div class="page-header">
        <div>
            <div class="page-title">Kondisi Barang</div>
            <div class="page-subtitle">Monitor kondisi dan perawatan barang</div>
        </div>
    </div>

    <!-- Statistik Ringkasan Kondisi -->
    <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-bottom:20px"
        id="kondisi-cards">
    </div>

    <!-- Card: Barang Perlu Perhatian -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">Barang Perlu Perhatian</div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Barang</th>
                        <th>Kondisi</th>
                        <th>Catatan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="tabel-kondisi"></tbody>
            </table>

            <!-- Empty State -->
            <div class="empty-state" id="empty-kondisi" style="display:none">
                <div class="empty-icon">✅</div>
                <div class="empty-text">Semua barang dalam kondisi baik!</div>
            </div>
        </div>
    </div>

</div>