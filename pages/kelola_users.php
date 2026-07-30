<?php

/**
 * pages/kelola_users.php — Halaman Kelola User (Admin Only)
 */
?>
<div class="page" id="page-kelola-users">
    <div class="page-header">
        <div>
            <div class="page-title">👥 Kelola User</div>
            <div class="page-subtitle">Manajemen akun pengguna sistem inventaris</div>
        </div>
    </div>

    <!-- Tabel User -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">Daftar Pengguna</div>
            <span id="users-count" style="font-size:12px;color:var(--text-muted)"></span>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Peran</th>
                        <th>Status</th>
                        <th>Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="tabel-users"></tbody>
            </table>
            <div id="empty-users" class="empty-state" style="display:none">
                <div class="empty-icon">👥</div>
                <div>Belum ada pengguna</div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit User (tanpa tambah user baru) -->
<div class="modal-overlay" id="modal-user">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title" id="modal-user-title">Edit User</div>
            <button class="modal-close" onclick="closeModal('modal-user')">✕</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="user-edit-id">
            <div class="form-row">
                <div class="form-group">
                    <label>Nama Lengkap *</label>
                    <input type="text" id="user-nama" placeholder="Nama user">
                </div>
                <div class="form-group">
                    <label>Username *</label>
                    <input type="text" id="user-username" placeholder="username" autocomplete="off">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Password <span id="user-pw-hint" style="color:var(--text-muted);font-weight:400">(kosongkan jika tidak diubah)</span></label>
                    <input type="password" id="user-password" placeholder="Password baru" autocomplete="new-password">
                </div>
                <div class="form-group">
                    <label>Peran *</label>
                    <select id="user-peran">
                        <option value="guru">📚 Guru</option>
                        <option value="admin">👑 Admin</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select id="user-aktif">
                    <option value="1">✅ Aktif</option>
                    <option value="0">🚫 Nonaktif</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('modal-user')">Batal</button>
            <button class="btn btn-primary" onclick="simpanUser()">💾 Simpan</button>
        </div>
    </div>
</div>