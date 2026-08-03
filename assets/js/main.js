/**
 * ===================================
 * INVENTARIS SEKOLAH
 * Main JavaScript Application
 * Mode: API-based (fetch to PHP)
 * ===================================
 */

// ===================================
// GLOBAL STATE
// ===================================
let kategoriList = [];  // Cached dari API

const kondisiIcon = { 'Baik': '✅', 'Cukup Baik': '⚠️', 'Rusak': '🔴' };

// ===================================
// API HELPER
// ===================================
async function api(url, method = 'GET', body = null) {
  const opts = { method, headers: { 'Content-Type': 'application/json' } };
  if (body) opts.body = JSON.stringify(body);
  const res = await fetch(url, opts);
  
  // Cek apakah response berupa JSON
  const contentType = res.headers.get('content-type') || '';
  if (!contentType.includes('application/json')) {
    // Jika redirect ke login (sesi habis) atau respons non-JSON
    if (res.redirected || res.url.includes('login.php')) {
      window.location.href = 'login.php';
      throw new Error('Sesi telah berakhir, mengalihkan ke halaman login...');
    }
    throw new Error('Server mengembalikan respons yang tidak valid (bukan JSON)');
  }
  
  let data;
  try {
    data = await res.json();
  } catch (e) {
    throw new Error('Gagal memproses respons dari server');
  }
  
  if (!res.ok) {
    // Jika 401, redirect ke login
    if (res.status === 401) {
      window.location.href = 'login.php';
    }
    throw new Error(data.error || 'Gagal memproses permintaan');
  }
  return data;
}

// ===================================
// NAVIGATION
// ===================================
function showPage(id, el) {
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
  const page = document.getElementById('page-' + id);
  if (page) page.classList.add('active');
  if (el) el.classList.add('active');
  renderPage(id);
  if (window.innerWidth < 768) {
    document.getElementById('sidebar').classList.remove('open');
  }
}

function toggleSidebar() {
  document.getElementById('sidebar').classList.toggle('open');
}

// ===================================
// RENDER DISPATCHER
// ===================================
function renderPage(p) {
  switch (p) {
    case 'dashboard': renderDashboard(); break;
    case 'barang': loadBarang(); break;
    case 'peminjaman': loadPeminjaman(); break;
    case 'kondisi': loadKondisi(); break;
    case 'riwayat': loadRiwayat(); break;
    case 'laporan': loadLaporan(); break;
    case 'kelola-users': loadUsers(); break;
  }
}

// ===================================
// DASHBOARD
// ===================================
async function renderDashboard() {
  try {
    const d = await api('api/dashboard.php');

    document.getElementById('stat-total').textContent = d.total;
    document.getElementById('stat-baik').textContent = d.baik;
    document.getElementById('stat-cukup').textContent = d.cukup;
    document.getElementById('stat-rusak').textContent = d.rusak;
    document.getElementById('stat-pinjam').textContent = d.pinjam;

    // Badge sidebar
    document.getElementById('badge-total').textContent = d.total;
    const bp = document.getElementById('badge-pinjam');
    bp.style.display = d.pinjam ? '' : 'none';
    if (d.pinjam) bp.textContent = d.pinjam;

    // Chart kategori
    const chartEl = document.getElementById('kategori-chart');
    chartEl.innerHTML = d.kategori.filter(k => k.jumlah > 0).map(k => `
      <div style="margin-bottom:14px">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px">
          <span style="font-size:13px">${k.ikon} ${k.nama}</span>
          <span style="font-size:12px;color:var(--text-muted);font-weight:600">${k.jumlah}</span>
        </div>
        <div class="progress-bar">
          <div class="progress-fill" style="width:${d.total ? k.jumlah / d.total * 100 : 0}%;background:var(--accent)"></div>
        </div>
      </div>`).join('') || '<div class="empty-state" style="padding:20px 0"><div class="empty-icon">📊</div><div>Belum ada data</div></div>';

    // Barang terbaru
    const recentEl = document.getElementById('barang-terbaru');
    recentEl.innerHTML = d.terbaru.length ? d.terbaru.map(b => `
      <div style="display:flex;align-items:center;gap:12px;padding:12px 20px;border-bottom:1px solid rgba(42,48,80,0.4)">
        <div class="item-thumb" style="background:rgba(79,138,255,0.1)">${b.ikon}</div>
        <div style="flex:1;min-width:0">
          <div style="font-size:13px;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${b.nama}</div>
          <div style="font-size:11px;color:var(--text-muted)">${b.kategori} · ${b.tahun || '-'}</div>
        </div>
        <span class="badge badge-${b.kondisi === 'Baik' ? 'baik' : b.kondisi === 'Cukup Baik' ? 'cukup' : 'rusak'}">${b.kondisi}</span>
      </div>`).join('') : '<div class="empty-state" style="padding:30px 0"><div class="empty-icon">📦</div><div>Belum ada barang</div></div>';

    // Admin: tampilkan stat user
    if (typeof d.total_user === 'number') {
      const statUser = document.getElementById('stat-users');
      if (statUser) statUser.textContent = d.total_user;
    }

    // Terlambat warning
    if (d.terlambat > 0) {
      toast(`⚠️ ${d.terlambat} barang melewati batas waktu pengembalian!`, 'error');
    }
  } catch (e) {
    toast('Gagal memuat dashboard: ' + e.message, 'error');
  }
}

// ===================================
// BARANG
// ===================================
async function loadBarang(q = '', kat = '', kond = '') {
  const p = new URLSearchParams();
  if (q) p.set('q', q);
  if (kat) p.set('kategori', kat);
  if (kond) p.set('kondisi', kond);
  try {
    const list = await api('api/barang.php?' + p.toString());
    renderTabelBarang(list);
  } catch (e) {
    toast('Gagal memuat barang: ' + e.message, 'error');
  }
}

function renderTabelBarang(list) {
  const tbody = document.getElementById('tabel-barang');
  const empty = document.getElementById('empty-barang');
  const canEdit = APP_ROLE === 'admin';

  if (!list.length) { tbody.innerHTML = ''; empty.style.display = 'block'; return; }
  empty.style.display = 'none';
  tbody.innerHTML = list.map(b => `
    <tr>
      <td>
        <div style="display:flex;align-items:center;gap:10px">
          <div class="item-thumb" style="background:rgba(79,138,255,0.08)">${b.ikon}</div>
          <div>
            <div style="font-weight:500;font-size:13px">${b.nama}</div>
            <div style="font-size:11px;color:var(--text-muted)">${b.lokasi || '-'}</div>
          </div>
        </div>
      </td>
      <td>${b.kategori}</td>
      <td style="font-family:monospace;font-size:12px;color:var(--text-dim)">${b.kode || '-'}</td>
      <td>${b.tahun || '-'}</td>
      <td><span class="badge badge-${b.kondisi === 'Baik' ? 'baik' : b.kondisi === 'Cukup Baik' ? 'cukup' : 'rusak'}">
        <span class="dot dot-${b.kondisi === 'Baik' ? 'baik' : b.kondisi === 'Cukup Baik' ? 'cukup' : 'rusak'}"></span>
        ${b.kondisi}</span></td>
      <td><span class="badge ${b.status === 'Dipinjam' ? 'badge-dipinjam' : 'badge-tersedia'}">${b.status}</span></td>
      <td>
        <div style="display:flex;gap:6px">
          <button class="btn btn-secondary btn-sm" onclick="lihatDetail(${b.id})">Detail</button>
          ${canEdit ? `<button class="btn btn-secondary btn-sm" onclick="editBarang(${b.id})">Edit</button>
          <button class="btn btn-danger btn-sm" onclick="hapusBarang(${b.id},'${b.nama.replace(/'/g, "\\'")}')">Hapus</button>` : ''}
        </div>
      </td>
    </tr>`).join('');
}

async function lihatDetail(id) {
  try {
    const list = await api(`api/barang.php?q=`);
    const b = list.find(x => x.id === id);
    if (!b) return;
    document.getElementById('modal-detail-body').innerHTML = `
      <div style="text-align:center;margin-bottom:24px">
        <div style="font-size:48px;margin-bottom:8px">${b.ikon}</div>
        <div style="font-family:'Syne',sans-serif;font-size:20px;font-weight:700">${b.nama}</div>
        <div style="margin-top:8px;display:flex;gap:8px;justify-content:center;flex-wrap:wrap">
          <span class="badge badge-${b.kondisi === 'Baik' ? 'baik' : b.kondisi === 'Cukup Baik' ? 'cukup' : 'rusak'}">${b.kondisi}</span>
          <span class="badge ${b.status === 'Dipinjam' ? 'badge-dipinjam' : 'badge-tersedia'}">${b.status}</span>
        </div>
      </div>
      <div class="detail-grid" style="margin-bottom:20px">
        <div class="detail-row"><div class="detail-label">Kategori</div><div class="detail-value">${b.kategori}</div></div>
        <div class="detail-row"><div class="detail-label">Jenis</div><div class="detail-value">${b.jenis || '-'}</div></div>
        <div class="detail-row"><div class="detail-label">Kode</div><div class="detail-value" style="font-family:monospace">${b.kode || '-'}</div></div>
        <div class="detail-row"><div class="detail-label">Tahun</div><div class="detail-value">${b.tahun || '-'}</div></div>
        <div class="detail-row"><div class="detail-label">Sumber</div><div class="detail-value">${b.sumber || '-'}</div></div>
        <div class="detail-row"><div class="detail-label">Lokasi</div><div class="detail-value">${b.lokasi || '-'}</div></div>
      </div>
      ${b.catatan ? `<div style="background:var(--surface2);border:1px solid var(--border);border-radius:8px;padding:14px">
        <div style="font-size:11px;text-transform:uppercase;color:var(--text-muted);margin-bottom:6px">Catatan</div>
        <div style="font-size:13px">${b.catatan}</div></div>` : ''}`;
    document.getElementById('modal-detail').classList.add('open');
  } catch (e) { toast('Gagal memuat detail: ' + e.message, 'error'); }
}

// ===================================
// SIMPAN BARANG (Tambah / Edit)
// ===================================
async function simpanBarang() {
  const nama = document.getElementById('inp-nama').value.trim();
  const kat_nama = document.getElementById('inp-kategori').value;
  if (!nama || !kat_nama) { toast('Nama dan Kategori wajib diisi!', 'error'); return; }

  // Cari id kategori
  const kat = kategoriList.find(k => k.nama === kat_nama);
  if (!kat) { toast('Kategori tidak valid!', 'error'); return; }

  const body = {
    nama,
    kategori_id: kat.id,
    jenis: document.getElementById('inp-jenis').value.trim(),
    kode: document.getElementById('inp-kode').value.trim(),
    tahun: document.getElementById('inp-tahun').value,
    kondisi: document.getElementById('inp-kondisi').value,
    sumber: document.getElementById('inp-sumber').value,
    lokasi: document.getElementById('inp-lokasi').value.trim(),
    catatan: document.getElementById('inp-catatan').value.trim(),
  };

  const editId = document.getElementById('edit-id').value;
  try {
    if (editId) {
      await api('api/barang.php', 'PUT', { ...body, id: parseInt(editId), status: document.getElementById('inp-status')?.value || 'Tersedia' });
      toast('Barang berhasil diperbarui!', 'success');
    } else {
      await api('api/barang.php', 'POST', body);
      toast('Barang berhasil ditambahkan!', 'success');
    }
    closeModal('modal-tambah');
    loadBarang();
    renderDashboard();
  } catch (e) { toast(e.message, 'error'); }
}

async function editBarang(id) {
  try {
    const list = await api('api/barang.php');
    const b = list.find(x => x.id === id);
    if (!b) return;
    document.getElementById('edit-id').value = b.id;
    document.getElementById('inp-nama').value = b.nama;
    document.getElementById('inp-kategori').value = b.kategori;
    document.getElementById('inp-jenis').value = b.jenis || '';
    document.getElementById('inp-kode').value = b.kode || '';
    document.getElementById('inp-tahun').value = b.tahun || '';
    document.getElementById('inp-kondisi').value = b.kondisi;
    document.getElementById('inp-sumber').value = b.sumber || 'Dana Sekolah';
    document.getElementById('inp-lokasi').value = b.lokasi || '';
    document.getElementById('inp-catatan').value = b.catatan || '';
    if (document.getElementById('inp-status')) {
      document.getElementById('inp-status').value = b.status || 'Tersedia';
    }
    document.getElementById('modal-tambah-title').textContent = 'Edit Barang';
    document.getElementById('modal-tambah').classList.add('open');
  } catch (e) { toast('Gagal memuat data barang', 'error'); }
}

async function hapusBarang(id, nama) {
  if (!confirm(`Hapus barang "${nama}"?`)) return;
  try {
    await api('api/barang.php?id=' + id, 'DELETE');
    toast('Barang dihapus', 'info');
    loadBarang();
    renderDashboard();
  } catch (e) { toast(e.message, 'error'); }
}

// ===================================
// FILTER BARANG
// ===================================
let searchBarangT;
function filterBarang() {
  clearTimeout(searchBarangT);
  searchBarangT = setTimeout(() => {
    const q = document.getElementById('search-barang').value;
    const kat = document.getElementById('filter-kategori').value;
    const kond = document.getElementById('filter-kondisi').value;
    loadBarang(q, kat, kond);
  }, 300);
}

// ===================================
// PEMINJAMAN
// ===================================
async function loadPeminjaman() {
  try {
    const list = await api('api/peminjaman.php?status=Aktif');
    const tbody = document.getElementById('tabel-pinjam');
    const empty = document.getElementById('empty-pinjam');

    if (!list.length) { tbody.innerHTML = ''; empty.style.display = 'block'; return; }
    empty.style.display = 'none';
    tbody.innerHTML = list.map(p => {
      const late = p.tgl_kembali && new Date(p.tgl_kembali) < new Date();
      return `<tr>
        <td style="font-weight:500"><span style="margin-right:6px">${p.barang_ikon}</span>${p.barang_nama}</td>
        <td>${p.peminjam}</td>
        <td>${p.tgl_pinjam}</td>
        <td><span ${late ? 'style="color:var(--danger);font-weight:600"' : ''}>${p.tgl_kembali || '-'}${late ? ' ⚠ Terlambat' : ''}</span></td>
        <td><span class="badge ${late ? 'badge-rusak' : 'badge-dipinjam'}">${late ? '⚠ Terlambat' : 'Aktif'}</span></td>
        <td>
          <button class="btn btn-primary btn-sm" onclick="kembalikanBarang(${p.id})">Dikembalikan</button>
          ${APP_ROLE === 'admin' ? `<button class="btn btn-danger btn-sm" onclick="hapusPeminjaman(${p.id})" style="padding:5px 8px;margin-left:4px;" title="Hapus Data">🗑️</button>` : ''}
        </td>
      </tr>`;
    }).join('');
  } catch (e) { toast('Gagal memuat peminjaman: ' + e.message, 'error'); }
}

async function simpanPeminjaman() {
  const barang_id = parseInt(document.getElementById('pinjam-barang').value);
  const peminjam = document.getElementById('pinjam-peminjam').value.trim();
  if (!barang_id || !peminjam) { toast('Barang dan Peminjam wajib diisi!', 'error'); return; }
  try {
    await api('api/peminjaman.php', 'POST', {
      barang_id,
      peminjam,
      tgl_pinjam: document.getElementById('pinjam-tgl').value,
      tgl_kembali: document.getElementById('pinjam-kembali').value,
      keterangan: document.getElementById('pinjam-ket').value.trim(),
    });
    toast('Peminjaman dicatat!', 'success');
    closeModal('modal-pinjam');
    loadPeminjaman();
    loadBarang();
    renderDashboard();
  } catch (e) { toast(e.message, 'error'); }
}

async function kembalikanBarang(id) {
  if (!confirm('Konfirmasi pengembalian barang ini?')) return;
  try {
    await api('api/peminjaman.php', 'PUT', { id });
    toast('Barang berhasil dikembalikan!', 'success');
    loadPeminjaman();
    loadBarang();
    renderDashboard();
  } catch (e) { toast(e.message, 'error'); }
}

async function hapusPeminjaman(id) {
  if (!confirm('Apakah Anda yakin ingin menghapus data peminjaman ini secara permanen?')) return;
  try {
    await api(`api/peminjaman.php?id=${id}`, 'DELETE');
    toast('Data peminjaman berhasil dihapus', 'success');
    loadPeminjaman();
    loadPeminjamanSelesai();
    loadBarang();
    renderDashboard();
  } catch (e) { toast(e.message, 'error'); }
}

// Tab switching for peminjaman
function switchPinjamTab(tab) {
  const aktifCard = document.getElementById('card-pinjam-aktif');
  const selesaiCard = document.getElementById('card-pinjam-selesai');
  const tabAktif = document.getElementById('tab-pinjam-aktif');
  const tabSelesai = document.getElementById('tab-pinjam-selesai');

  if (tab === 'aktif') {
    aktifCard.style.display = '';
    selesaiCard.style.display = 'none';
    tabAktif.className = 'btn btn-primary btn-sm';
    tabSelesai.className = 'btn btn-secondary btn-sm';
    loadPeminjaman();
  } else {
    aktifCard.style.display = 'none';
    selesaiCard.style.display = '';
    tabAktif.className = 'btn btn-secondary btn-sm';
    tabSelesai.className = 'btn btn-primary btn-sm';
    loadPeminjamanSelesai();
  }
}

async function loadPeminjamanSelesai() {
  try {
    const list = await api('api/peminjaman.php?status=Selesai');
    const tbody = document.getElementById('tabel-pinjam-selesai');
    const empty = document.getElementById('empty-pinjam-selesai');
    const countEl = document.getElementById('count-pinjam-selesai');

    if (countEl) countEl.textContent = `${list.length} data`;

    if (!list.length) { tbody.innerHTML = ''; empty.style.display = 'block'; return; }
    empty.style.display = 'none';
    tbody.innerHTML = list.map(p => `<tr>
      <td style="font-weight:500"><span style="margin-right:6px">${p.barang_ikon}</span>${p.barang_nama}</td>
      <td>${p.peminjam}</td>
      <td>${p.tgl_pinjam}</td>
      <td>${p.tgl_dikembalikan || p.tgl_kembali || '-'}</td>
      <td><span class="badge badge-tersedia">Selesai</span></td>
      <td>
        <span style="font-size:12px;color:var(--text-muted);max-width:200px;display:inline-block">${p.keterangan || '-'}</span>
        ${APP_ROLE === 'admin' ? `<button class="btn btn-danger btn-sm" onclick="hapusPeminjaman(${p.id})" style="padding:3px 6px;margin-left:8px;" title="Hapus Data">🗑️</button>` : ''}
      </td>
    </tr>`).join('');
  } catch (e) { toast('Gagal memuat riwayat peminjaman: ' + e.message, 'error'); }
}

// ===================================
// KONDISI BARANG
// ===================================
async function loadKondisi() {
  try {
    const list = await api('api/barang.php');
    const baik = list.filter(b => b.kondisi === 'Baik').length;
    const cukup = list.filter(b => b.kondisi === 'Cukup Baik').length;
    const rusak = list.filter(b => b.kondisi === 'Rusak').length;
    const canEdit = APP_ROLE === 'admin';

    document.getElementById('kondisi-cards').innerHTML = `
      <div class="stat-card" style="--card-color:#34d399"><div class="stat-icon">✅</div><div class="stat-value">${baik}</div><div class="stat-label">Kondisi Baik</div></div>
      <div class="stat-card" style="--card-color:#fb923c"><div class="stat-icon">⚠️</div><div class="stat-value">${cukup}</div><div class="stat-label">Cukup Baik</div></div>
      <div class="stat-card" style="--card-color:#f87171"><div class="stat-icon">🔴</div><div class="stat-value">${rusak}</div><div class="stat-label">Perlu Perbaikan</div></div>`;

    const perlu = list.filter(b => b.kondisi !== 'Baik');
    const tbody = document.getElementById('tabel-kondisi');
    const empty = document.getElementById('empty-kondisi');
    if (!perlu.length) { tbody.innerHTML = ''; empty.style.display = 'block'; return; }
    empty.style.display = 'none';
    tbody.innerHTML = perlu.map(b => `<tr>
      <td>
        <div style="font-weight:500">${b.nama}</div>
        <div style="font-size:11px;color:var(--text-muted)">${b.kategori}</div>
      </td>
      <td><span class="badge badge-${b.kondisi === 'Cukup Baik' ? 'cukup' : 'rusak'}">${b.kondisi}</span></td>
      <td style="font-size:12px;color:var(--text-dim);max-width:200px">${b.catatan || '-'}</td>
      <td>${canEdit ? `<button class="btn btn-secondary btn-sm" onclick="editBarang(${b.id})">Update Kondisi</button>` : '<span style="color:var(--text-muted);font-size:12px">View Only</span>'}</td>
    </tr>`).join('');
  } catch (e) { toast('Gagal memuat kondisi: ' + e.message, 'error'); }
}

// ===================================
// RIWAYAT
// ===================================
async function loadRiwayat() {
  try {
    const rows = await api('api/riwayat.php');
    const el = document.getElementById('riwayat-list');
    if (!rows.length) {
      el.innerHTML = '<div class="empty-state"><div class="empty-icon">📋</div><div>Belum ada aktivitas</div></div>';
      return;
    }
    const peranLabel = { admin: '👑', operator: '⚙️', guru: '📚' };
    el.innerHTML = `<div class="timeline">${rows.map((r, i) => `
      <div class="timeline-item">
        <div class="timeline-dot" style="background:${i === 0 ? 'var(--accent)' : 'var(--border)'}"></div>
        <div class="timeline-content">
          <div class="timeline-action">${r.aksi}</div>
          <div class="timeline-date">${r.user_nama ? `${peranLabel[r.peran] || ''} ${r.user_nama} · ` : ''}${new Date(r.created_at).toLocaleString('id-ID')}</div>
        </div>
      </div>`).join('')}</div>`;
  } catch (e) { toast('Gagal memuat riwayat: ' + e.message, 'error'); }
}

// ===================================
// LAPORAN (Admin & Guru)
// ===================================
async function loadLaporan() {
  if (APP_ROLE !== 'admin' && APP_ROLE !== 'guru') return;
  const p = new URLSearchParams();
  const kat = document.getElementById('lap-kategori')?.value;
  const kond = document.getElementById('lap-kondisi')?.value;
  const stat = document.getElementById('lap-status')?.value;
  if (kat) p.set('kategori', kat);
  if (kond) p.set('kondisi', kond);
  if (stat) p.set('status', stat);
  try {
    const res = await api('api/laporan.php?' + p.toString());
    const { stats, data } = res;

    // Stats
    document.getElementById('lap-stats').innerHTML = `
      <div class="stat-card" style="--card-color:#4f8aff"><div class="stat-icon">📦</div><div class="stat-value">${stats.total}</div><div class="stat-label">Total</div></div>
      <div class="stat-card" style="--card-color:#34d399"><div class="stat-icon">✅</div><div class="stat-value">${stats.baik}</div><div class="stat-label">Kondisi Baik</div></div>
      <div class="stat-card" style="--card-color:#fb923c"><div class="stat-icon">⚠️</div><div class="stat-value">${stats.cukup}</div><div class="stat-label">Cukup Baik</div></div>
      <div class="stat-card" style="--card-color:#f87171"><div class="stat-icon">🔴</div><div class="stat-value">${stats.rusak}</div><div class="stat-label">Perlu Perbaikan</div></div>
      <div class="stat-card" style="--card-color:#a78bfa"><div class="stat-icon">🔄</div><div class="stat-value">${stats.pinjam}</div><div class="stat-label">Dipinjam</div></div>`;

    document.getElementById('lap-count').textContent = `${data.length} item`;

    const tbody = document.getElementById('tabel-laporan');
    const empty = document.getElementById('empty-laporan');
    if (!data.length) { tbody.innerHTML = ''; empty.style.display = 'block'; return; }
    empty.style.display = 'none';
    tbody.innerHTML = data.map((r, i) => `<tr>
      <td style="color:var(--text-muted);font-size:12px">${i + 1}</td>
      <td style="font-weight:500">${r.nama}</td>
      <td>${r.kategori}</td>
      <td style="font-family:monospace;font-size:12px">${r.kode || '-'}</td>
      <td>${r.tahun || '-'}</td>
      <td><span class="badge badge-${r.kondisi === 'Baik' ? 'baik' : r.kondisi === 'Cukup Baik' ? 'cukup' : 'rusak'}">${r.kondisi}</span></td>
      <td>${r.sumber || '-'}</td>
      <td>${r.lokasi || '-'}</td>
      <td><span class="badge ${r.status === 'Dipinjam' ? 'badge-dipinjam' : 'badge-tersedia'}">${r.status}</span></td>
    </tr>`).join('');
  } catch (e) { toast('Gagal memuat laporan: ' + e.message, 'error'); }
}

function exportCSV() {
  const p = new URLSearchParams({ export: 'csv' });
  const kat = document.getElementById('lap-kategori')?.value;
  const kond = document.getElementById('lap-kondisi')?.value;
  const stat = document.getElementById('lap-status')?.value;
  if (kat) p.set('kategori', kat);
  if (kond) p.set('kondisi', kond);
  if (stat) p.set('status', stat);
  window.open('api/laporan.php?' + p.toString(), '_blank');
}

function exportPDF() {
  const p = new URLSearchParams({ export: 'pdf' });
  const kat = document.getElementById('lap-kategori')?.value;
  const kond = document.getElementById('lap-kondisi')?.value;
  const stat = document.getElementById('lap-status')?.value;
  if (kat) p.set('kategori', kat);
  if (kond) p.set('kondisi', kond);
  if (stat) p.set('status', stat);
  window.open('api/laporan.php?' + p.toString(), '_blank');
}

function exportPeminjamanPDF() {
  const p = new URLSearchParams({ export: 'pdf_peminjaman' });
  window.open('api/laporan.php?' + p.toString(), '_blank');
}

// ===================================
// KELOLA USERS (Admin Only)
// ===================================
async function loadUsers() {
  if (APP_ROLE !== 'admin') return;
  try {
    const list = await api('api/users.php');
    const tbody = document.getElementById('tabel-users');
    const empty = document.getElementById('empty-users');
    document.getElementById('users-count').textContent = `${list.length} pengguna`;
    if (!list.length) { tbody.innerHTML = ''; empty.style.display = 'block'; return; }
    empty.style.display = 'none';
    const peranColor = { admin: '#f87171', guru: '#34d399' };
    const peranLabel = { admin: '👑 Admin', guru: '📚 Guru' };
    tbody.innerHTML = list.map(u => `<tr>
      <td style="font-weight:500">${u.nama}</td>
      <td style="font-family:monospace;font-size:12px">${u.username}</td>
      <td><span style="font-size:12px;font-weight:600;color:${peranColor[u.peran]}">${peranLabel[u.peran]}</span></td>
      <td><span class="badge ${u.aktif ? 'badge-tersedia' : 'badge-dipinjam'}">${u.aktif ? 'Aktif' : 'Nonaktif'}</span></td>
      <td style="font-size:12px;color:var(--text-muted)">${new Date(u.created_at).toLocaleDateString('id-ID')}</td>
      <td>
        <div style="display:flex;gap:6px">
          <button class="btn btn-secondary btn-sm" onclick="editUser(${JSON.stringify(u).replace(/"/g, '&quot;')})">Edit</button>
          ${u.id !== APP_UID && u.id !== 1 ? `<button class="btn btn-danger btn-sm" onclick="hapusUser(${u.id},'${u.username}')">Hapus</button>` : ''}
        </div>
      </td>
    </tr>`).join('');
  } catch (e) { toast('Gagal memuat user: ' + e.message, 'error'); }
}

function editUser(u) {
  document.getElementById('user-edit-id').value = u.id;
  document.getElementById('user-nama').value = u.nama;
  document.getElementById('user-username').value = u.username;
  document.getElementById('user-password').value = '';
  document.getElementById('user-peran').value = u.peran;
  document.getElementById('user-aktif').value = u.aktif;
  document.getElementById('modal-user-title').textContent = 'Edit User';
  document.getElementById('user-pw-hint').textContent = '(kosongkan jika tidak diubah)';
  document.getElementById('modal-user').classList.add('open');
}

async function simpanUser() {
  const nama = document.getElementById('user-nama').value.trim();
  const username = document.getElementById('user-username').value.trim();
  const password = document.getElementById('user-password').value;
  const peran = document.getElementById('user-peran').value;
  const aktif = document.getElementById('user-aktif').value;
  const editId = document.getElementById('user-edit-id').value;

  if (!nama || !username) { toast('Nama dan username wajib diisi!', 'error'); return; }
  if (!editId) { toast('Hanya bisa mengedit user yang sudah ada!', 'error'); return; }

  try {
    await api('api/users.php', 'PUT', { id: parseInt(editId), nama, username, password, peran, aktif: parseInt(aktif) });
    toast('User berhasil diperbarui!', 'success');
    closeModal('modal-user');
    loadUsers();
  } catch (e) { toast(e.message, 'error'); }
}

async function hapusUser(id, username) {
  if (!confirm(`Nonaktifkan user "${username}"?`)) return;
  try {
    await api('api/users.php?id=' + id, 'DELETE');
    toast('User dinonaktifkan', 'info');
    loadUsers();
  } catch (e) { toast(e.message, 'error'); }
}

// ===================================
// MODAL MANAGEMENT
// ===================================
async function openModal(id) {
  if (id === 'modal-tambah') {
    if (!document.getElementById('edit-id').value) {
      document.getElementById('modal-tambah-title').textContent = 'Tambah Barang Baru';
      ['inp-nama', 'inp-jenis', 'inp-kode', 'inp-tahun', 'inp-lokasi', 'inp-catatan'].forEach(f => {
        document.getElementById(f).value = '';
      });
      document.getElementById('inp-kategori').value = '';
      document.getElementById('inp-kondisi').value = 'Baik';
      document.getElementById('inp-sumber').value = 'Dana Sekolah';
    }
    document.getElementById('modal-tambah').classList.add('open');

  } else if (id === 'modal-pinjam') {
    try {
      const list = await api('api/barang.php?status=Tersedia');
      const tersedia = list.filter(b => b.status === 'Tersedia');
      const select = document.getElementById('pinjam-barang');
      select.innerHTML = '<option value="">Pilih barang...</option>' +
        tersedia.map(b => `<option value="${b.id}">${b.ikon} ${b.nama}</option>`).join('');
    } catch (e) { /* fallback */ }
    document.getElementById('pinjam-tgl').value = new Date().toISOString().split('T')[0];
    document.getElementById('pinjam-peminjam').value = '';
    document.getElementById('pinjam-kembali').value = '';
    document.getElementById('pinjam-ket').value = '';
    document.getElementById('modal-pinjam').classList.add('open');
  }
}

function closeModal(id) {
  document.getElementById(id).classList.remove('open');
  if (id === 'modal-tambah') document.getElementById('edit-id').value = '';
}

document.querySelectorAll('.modal-overlay').forEach(el => {
  el.addEventListener('click', function (e) {
    if (e.target === this) this.classList.remove('open');
  });
});

// ===================================
// TOAST NOTIFICATION
// ===================================
function toast(msg, type = 'info') {
  const c = document.getElementById('toast-container');
  const el = document.createElement('div');
  el.className = `toast ${type}`;
  el.innerHTML = `${type === 'success' ? '✅' : type === 'error' ? '❌' : 'ℹ️'} ${msg}`;
  c.appendChild(el);
  setTimeout(() => {
    el.style.cssText += 'opacity:0;transform:translateX(20px);transition:0.3s';
    setTimeout(() => el.remove(), 300);
  }, 3000);
}

// ===================================
// INIT: Load Kategori & Populate Filters
// ===================================
async function init() {
  try {
    kategoriList = await api('api/kategori.php');

    // Isi select kategori di modal tambah barang
    const katSelect = document.getElementById('inp-kategori');
    if (katSelect) {
      katSelect.innerHTML = '<option value="">Pilih kategori...</option>' +
        kategoriList.map(k => `<option value="${k.nama}">${k.ikon} ${k.nama}</option>`).join('');
    }

    // Isi filter kategori di halaman barang
    const filterKat = document.getElementById('filter-kategori');
    if (filterKat) {
      filterKat.innerHTML = '<option value="">Semua Kategori</option>' +
        kategoriList.map(k => `<option value="${k.nama}">${k.nama}</option>`).join('');
    }

    // Isi filter kategori di laporan
    const lapKat = document.getElementById('lap-kategori');
    if (lapKat) {
      lapKat.innerHTML = '<option value="">Semua Kategori</option>' +
        kategoriList.map(k => `<option value="${k.nama}">${k.nama}</option>`).join('');
    }
  } catch (e) {
    toast('Gagal memuat konfigurasi: ' + e.message, 'error');
  }

  // Dashboard sebagai halaman pertama
  renderDashboard();
}

init();
