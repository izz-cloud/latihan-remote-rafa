<?php

/**
 * modals/modal_pinjam.php
 * Modal: Catat Peminjaman Barang
 */
?>
<!-- ===========================
     MODAL CATAT PEMINJAMAN
     =========================== -->
<div class="modal-overlay" id="modal-pinjam">
    <div class="modal">

        <!-- Header Modal -->
        <div class="modal-header">
            <div class="modal-title">Catat Peminjaman</div>
            <button class="modal-close" onclick="closeModal('modal-pinjam')">✕</button>
        </div>

        <!-- Body Modal: Form Peminjaman -->
        <div class="modal-body">
            <div class="form-grid">

                <!-- Pilih Barang -->
                <div class="form-group full">
                    <label>Pilih Barang *</label>
                    <select id="pinjam-barang">
                        <option value="">Pilih barang...</option>
                    </select>
                </div>

                <!-- Nama Peminjam -->
                <div class="form-group full">
                    <label>Nama Peminjam *</label>
                    <input type="text" id="pinjam-peminjam" placeholder="cth: Budi Santoso">
                </div>

                <!-- Tanggal Pinjam -->
                <div class="form-group">
                    <label>Tanggal Pinjam</label>
                    <input type="date" id="pinjam-tgl">
                </div>

                <!-- Rencana Kembali -->
                <div class="form-group">
                    <label>Rencana Kembali</label>
                    <input type="date" id="pinjam-kembali">
                </div>

                <!-- Keterangan -->
                <div class="form-group full">
                    <label>Keterangan</label>
                    <textarea id="pinjam-ket" placeholder="Keperluan peminjaman..."></textarea>
                </div>

            </div>
        </div>

        <!-- Footer Modal: Tombol Aksi -->
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('modal-pinjam')">Batal</button>
            <button class="btn btn-primary" onclick="simpanPeminjaman()">Catat Peminjaman</button>
        </div>

    </div>
</div>