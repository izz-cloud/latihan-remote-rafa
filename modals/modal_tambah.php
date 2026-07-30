<?php

/**
 * modals/modal_tambah.php
 * Modal: Tambah Barang Baru / Edit Barang
 */
?>
<!-- ===========================
     MODAL TAMBAH / EDIT BARANG
     =========================== -->
<div class="modal-overlay" id="modal-tambah">
    <div class="modal">

        <!-- Header Modal -->
        <div class="modal-header">
            <div class="modal-title" id="modal-tambah-title">Tambah Barang Baru</div>
            <button class="modal-close" onclick="closeModal('modal-tambah')">✕</button>
        </div>

        <!-- Body Modal: Form Input -->
        <div class="modal-body">
            <div class="form-grid">

                <!-- Hidden ID untuk mode edit -->
                <input type="hidden" id="edit-id">

                <!-- Nama Barang -->
                <div class="form-group full">
                    <label>Nama Barang *</label>
                    <input type="text" id="inp-nama" placeholder="cth: Laptop ASUS VivoBook">
                </div>

                <!-- Kategori -->
                <div class="form-group">
                    <label>Kategori *</label>
                    <select id="inp-kategori">
                        <option value="">Pilih kategori</option>
                        <option>Elektronik</option>
                        <option>Furnitur</option>
                        <option>Alat Tulis</option>
                        <option>Media Pembelajaran</option>
                        <option>Lainnya</option>
                    </select>
                </div>

                <!-- Jenis -->
                <div class="form-group">
                    <label>Jenis / Sub-tipe</label>
                    <input type="text" id="inp-jenis" placeholder="cth: Laptop, Proyektor">
                </div>

                <!-- Nomor Seri -->
                <div class="form-group">
                    <label>Nomor Seri / Kode</label>
                    <input type="text" id="inp-kode" placeholder="cth: SN123456">
                </div>

                <!-- Tahun Pengadaan -->
                <div class="form-group">
                    <label>Tahun Pengadaan</label>
                    <input type="number" id="inp-tahun" placeholder="2023" min="2000" max="2030">
                </div>

                <!-- Kondisi -->
                <div class="form-group">
                    <label>Kondisi</label>
                    <select id="inp-kondisi">
                        <option>Baik</option>
                        <option>Cukup Baik</option>
                        <option>Rusak</option>
                    </select>
                </div>

                <!-- Sumber Pengadaan -->
                <div class="form-group">
                    <label>Sumber Pengadaan</label>
                    <select id="inp-sumber">
                        <option>Pribadi</option>
                        <option>Dana Sekolah</option>
                        <option>Bantuan Pemerintah</option>
                        <option>Donasi</option>
                    </select>
                </div>

                <!-- Lokasi -->
                <div class="form-group full">
                    <label>Lokasi / Ruangan</label>
                    <input type="text" id="inp-lokasi" placeholder="cth: Ruang Guru, Kelas 9A">
                </div>

                <!-- Catatan -->
                <div class="form-group full">
                    <label>Catatan</label>
                    <textarea id="inp-catatan" placeholder="Catatan tambahan..."></textarea>
                </div>

            </div>
        </div>

        <!-- Footer Modal: Tombol Aksi -->
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('modal-tambah')">Batal</button>
            <button class="btn btn-primary" onclick="simpanBarang()">Simpan</button>
        </div>

    </div>
</div>