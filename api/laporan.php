<?php

/**
 * api/laporan.php — Laporan barang & export CSV/PDF
 * GET ?export=csv  → download file CSV
 * GET ?export=pdf  → download file PDF
 * GET              → JSON data laporan
 * Akses: Admin & Guru
 */
define('BASE_URL', '../');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
requireRole(['admin', 'guru']);

$pdo    = getDB();

// Query laporan barang
$where  = ['1=1'];
$params = [];
if (!empty($_GET['kondisi'])) {
    $where[] = 'b.kondisi = ?';
    $params[] = $_GET['kondisi'];
}
if (!empty($_GET['status'])) {
    $where[] = 'b.status = ?';
    $params[] = $_GET['status'];
}
if (!empty($_GET['kategori'])) {
    $where[] = 'k.nama = ?';
    $params[] = $_GET['kategori'];
}

$sql = "
    SELECT b.nama, k.nama as kategori, b.jenis, b.kode, b.tahun,
           b.kondisi, b.sumber, b.lokasi, b.status, b.catatan,
           b.created_at
    FROM barang b
    JOIN kategori k ON k.id = b.kategori_id
    WHERE " . implode(' AND ', $where) . "
    ORDER BY k.nama, b.nama
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

// ─── Export PDF ───────────────────────────────────────────────────────────────
if (!empty($_GET['export']) && $_GET['export'] === 'pdf') {
    // Get school/report info
    $filterInfo = [];
    if (!empty($_GET['kondisi'])) $filterInfo[] = 'Kondisi: ' . $_GET['kondisi'];
    if (!empty($_GET['status']))  $filterInfo[] = 'Status: ' . $_GET['status'];
    if (!empty($_GET['kategori'])) $filterInfo[] = 'Kategori: ' . $_GET['kategori'];
    $filterText = count($filterInfo) ? implode(' | ', $filterInfo) : 'Semua Data';
    
    $total = count($rows);
    $baik  = count(array_filter($rows, fn($r) => $r['kondisi'] === 'Baik'));
    $cukup = count(array_filter($rows, fn($r) => $r['kondisi'] === 'Cukup Baik'));
    $rusak = count(array_filter($rows, fn($r) => $r['kondisi'] === 'Rusak'));
    $pinjam = count(array_filter($rows, fn($r) => $r['status'] === 'Dipinjam'));
    
    // Generate PDF using raw PDF writing (no external library needed)
    $filename = 'laporan_inventaris_' . date('Ymd_His') . '.pdf';
    
    // We'll output HTML that the browser can print as PDF
    // This is a server-side HTML-to-print approach
    header('Content-Type: text/html; charset=UTF-8');
    
    $html = '<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Inventaris Sekolah</title>
<style>
    @media print {
        @page { size: A4 landscape; margin: 15mm; }
        body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .no-print { display: none !important; }
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: "Segoe UI", Arial, sans-serif; font-size: 11px; color: #1a1a2e; background: #fff; padding: 20px; }
    .print-btn { position: fixed; top: 20px; right: 20px; background: #4f8aff; color: #fff; border: none; padding: 12px 28px; border-radius: 8px; font-size: 14px; cursor: pointer; z-index: 1000; box-shadow: 0 4px 12px rgba(79,138,255,0.3); }
    .print-btn:hover { background: #3a6fd4; }
    .header { text-align: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 3px solid #1a1a2e; }
    .header h1 { font-size: 20px; font-weight: 800; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 4px; }
    .header h2 { font-size: 14px; font-weight: 600; color: #4f8aff; margin-bottom: 4px; }
    .header .date { font-size: 11px; color: #666; }
    .header .filter { font-size: 10px; color: #888; margin-top: 4px; }
    .stats { display: flex; gap: 10px; margin-bottom: 18px; justify-content: center; }
    .stat-box { flex: 1; max-width: 150px; background: #f8f9fa; border: 1px solid #e0e0e0; border-radius: 8px; padding: 10px; text-align: center; }
    .stat-box .value { font-size: 22px; font-weight: 800; color: #1a1a2e; }
    .stat-box .label { font-size: 9px; text-transform: uppercase; color: #888; letter-spacing: 0.5px; }
    .stat-box.baik { border-left: 4px solid #34d399; }
    .stat-box.cukup { border-left: 4px solid #fb923c; }
    .stat-box.rusak { border-left: 4px solid #f87171; }
    .stat-box.pinjam { border-left: 4px solid #a78bfa; }
    .stat-box.total { border-left: 4px solid #4f8aff; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
    th { background: #1a1a2e; color: #fff; padding: 8px 6px; text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
    td { padding: 7px 6px; border-bottom: 1px solid #e8e8e8; font-size: 11px; }
    tr:nth-child(even) { background: #f8f9fa; }
    tr:hover { background: #eef2ff; }
    .badge { padding: 2px 8px; border-radius: 4px; font-size: 9px; font-weight: 600; display: inline-block; }
    .badge-baik { background: #d1fae5; color: #065f46; }
    .badge-cukup { background: #fed7aa; color: #9a3412; }
    .badge-rusak { background: #fecaca; color: #991b1b; }
    .badge-tersedia { background: #dbeafe; color: #1e40af; }
    .badge-dipinjam { background: #e9d5ff; color: #6b21a8; }
    .footer { text-align: center; margin-top: 20px; padding-top: 15px; border-top: 1px solid #e0e0e0; font-size: 10px; color: #888; }
    .signature { display: flex; justify-content: space-between; margin-top: 40px; padding: 0 50px; }
    .sign-box { text-align: center; width: 200px; }
    .sign-box .line { border-bottom: 1px solid #333; margin-top: 60px; margin-bottom: 5px; }
    .sign-box .title { font-size: 11px; font-weight: 600; }
    .sign-box .subtitle { font-size: 10px; color: #666; }
</style>
</head>
<body>
<button class="print-btn no-print" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>

<div class="header">
    <h1>📦 Inventaris Sekolah</h1>
    <h2>Laporan Data Barang Inventaris</h2>
    <div class="date">Tanggal Cetak: ' . date('d F Y, H:i') . ' WIB</div>
    <div class="filter">Filter: ' . htmlspecialchars($filterText) . '</div>
</div>

<div class="stats">
    <div class="stat-box total"><div class="value">' . $total . '</div><div class="label">Total Barang</div></div>
    <div class="stat-box baik"><div class="value">' . $baik . '</div><div class="label">Kondisi Baik</div></div>
    <div class="stat-box cukup"><div class="value">' . $cukup . '</div><div class="label">Cukup Baik</div></div>
    <div class="stat-box rusak"><div class="value">' . $rusak . '</div><div class="label">Rusak</div></div>
    <div class="stat-box pinjam"><div class="value">' . $pinjam . '</div><div class="label">Dipinjam</div></div>
</div>

<table>
<thead>
    <tr>
        <th style="width:30px">No</th>
        <th>Nama Barang</th>
        <th>Kategori</th>
        <th>Jenis</th>
        <th>Kode</th>
        <th>Tahun</th>
        <th>Kondisi</th>
        <th>Sumber</th>
        <th>Lokasi</th>
        <th>Status</th>
        <th>Catatan</th>
    </tr>
</thead>
<tbody>';

    foreach ($rows as $i => $r) {
        $kondisiClass = $r['kondisi'] === 'Baik' ? 'baik' : ($r['kondisi'] === 'Cukup Baik' ? 'cukup' : 'rusak');
        $statusClass = $r['status'] === 'Dipinjam' ? 'dipinjam' : 'tersedia';
        $html .= '
    <tr>
        <td style="text-align:center;color:#888">' . ($i + 1) . '</td>
        <td style="font-weight:600">' . htmlspecialchars($r['nama']) . '</td>
        <td>' . htmlspecialchars($r['kategori']) . '</td>
        <td>' . htmlspecialchars($r['jenis'] ?? '-') . '</td>
        <td style="font-family:monospace">' . htmlspecialchars($r['kode'] ?? '-') . '</td>
        <td>' . htmlspecialchars($r['tahun'] ?? '-') . '</td>
        <td><span class="badge badge-' . $kondisiClass . '">' . htmlspecialchars($r['kondisi']) . '</span></td>
        <td>' . htmlspecialchars($r['sumber'] ?? '-') . '</td>
        <td>' . htmlspecialchars($r['lokasi'] ?? '-') . '</td>
        <td><span class="badge badge-' . $statusClass . '">' . htmlspecialchars($r['status']) . '</span></td>
        <td style="max-width:150px;font-size:10px">' . htmlspecialchars($r['catatan'] ?? '-') . '</td>
    </tr>';
    }

    $html .= '
</tbody>
</table>

<div class="signature">
    <div class="sign-box">
        <div class="title">Mengetahui,</div>
        <div class="subtitle">Kepala Sekolah</div>
        <div class="line"></div>
        <div class="subtitle">NIP. ___________________</div>
    </div>
    <div class="sign-box">
        <div class="title">Dibuat oleh,</div>
        <div class="subtitle">Pengelola Inventaris</div>
        <div class="line"></div>
        <div class="subtitle">NIP. ___________________</div>
    </div>
</div>

<div class="footer">
    Dokumen ini dicetak secara otomatis oleh Sistem Inventaris Sekolah — ' . date('Y') . '
</div>

<script>
// Auto-trigger print dialog
window.addEventListener("load", function() {
    // Small delay to ensure rendering is complete
    setTimeout(function() { /* ready */ }, 500);
});
</script>
</body>
</html>';
    
    echo $html;
    exit;
}

// ─── Export Laporan Peminjaman PDF ─────────────────────────────────────────────
if (!empty($_GET['export']) && $_GET['export'] === 'pdf_peminjaman') {
    $statusFilter = $_GET['status_pinjam'] ?? '';
    
    $sqlP = "
        SELECT p.*, b.nama as barang_nama, b.kode as barang_kode, 
               k.nama as kategori, b.lokasi,
               u.nama as pencatat_nama
        FROM peminjaman p
        JOIN barang b ON b.id = p.barang_id
        JOIN kategori k ON k.id = b.kategori_id
        LEFT JOIN users u ON u.id = p.user_id
    ";
    $paramsP = [];
    if ($statusFilter) {
        $sqlP .= " WHERE p.status = ?";
        $paramsP[] = $statusFilter;
    }
    $sqlP .= " ORDER BY p.created_at DESC";
    
    $stmtP = $pdo->prepare($sqlP);
    $stmtP->execute($paramsP);
    $pinjamRows = $stmtP->fetchAll();
    
    $totalP = count($pinjamRows);
    $aktifP = count(array_filter($pinjamRows, fn($r) => $r['status'] === 'Aktif'));
    $selesaiP = count(array_filter($pinjamRows, fn($r) => $r['status'] === 'Selesai'));
    
    header('Content-Type: text/html; charset=UTF-8');
    
    $html = '<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Peminjaman Barang</title>
<style>
    @media print {
        @page { size: A4 landscape; margin: 15mm; }
        body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .no-print { display: none !important; }
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: "Segoe UI", Arial, sans-serif; font-size: 11px; color: #1a1a2e; background: #fff; padding: 20px; }
    .print-btn { position: fixed; top: 20px; right: 20px; background: #4f8aff; color: #fff; border: none; padding: 12px 28px; border-radius: 8px; font-size: 14px; cursor: pointer; z-index: 1000; box-shadow: 0 4px 12px rgba(79,138,255,0.3); }
    .print-btn:hover { background: #3a6fd4; }
    .header { text-align: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 3px solid #1a1a2e; }
    .header h1 { font-size: 20px; font-weight: 800; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 4px; }
    .header h2 { font-size: 14px; font-weight: 600; color: #4f8aff; margin-bottom: 4px; }
    .header .date { font-size: 11px; color: #666; }
    .stats { display: flex; gap: 10px; margin-bottom: 18px; justify-content: center; }
    .stat-box { flex: 1; max-width: 150px; background: #f8f9fa; border: 1px solid #e0e0e0; border-radius: 8px; padding: 10px; text-align: center; }
    .stat-box .value { font-size: 22px; font-weight: 800; color: #1a1a2e; }
    .stat-box .label { font-size: 9px; text-transform: uppercase; color: #888; letter-spacing: 0.5px; }
    .stat-box.total { border-left: 4px solid #4f8aff; }
    .stat-box.aktif { border-left: 4px solid #fb923c; }
    .stat-box.selesai { border-left: 4px solid #34d399; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
    th { background: #1a1a2e; color: #fff; padding: 8px 6px; text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
    td { padding: 7px 6px; border-bottom: 1px solid #e8e8e8; font-size: 11px; }
    tr:nth-child(even) { background: #f8f9fa; }
    .badge { padding: 2px 8px; border-radius: 4px; font-size: 9px; font-weight: 600; display: inline-block; }
    .badge-aktif { background: #fed7aa; color: #9a3412; }
    .badge-selesai { background: #d1fae5; color: #065f46; }
    .badge-terlambat { background: #fecaca; color: #991b1b; }
    .footer { text-align: center; margin-top: 20px; padding-top: 15px; border-top: 1px solid #e0e0e0; font-size: 10px; color: #888; }
    .signature { display: flex; justify-content: space-between; margin-top: 40px; padding: 0 50px; }
    .sign-box { text-align: center; width: 200px; }
    .sign-box .line { border-bottom: 1px solid #333; margin-top: 60px; margin-bottom: 5px; }
    .sign-box .title { font-size: 11px; font-weight: 600; }
    .sign-box .subtitle { font-size: 10px; color: #666; }
</style>
</head>
<body>
<button class="print-btn no-print" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>

<div class="header">
    <h1>📦 Inventaris Sekolah</h1>
    <h2>Laporan Peminjaman Barang</h2>
    <div class="date">Tanggal Cetak: ' . date('d F Y, H:i') . ' WIB</div>
</div>

<div class="stats">
    <div class="stat-box total"><div class="value">' . $totalP . '</div><div class="label">Total Peminjaman</div></div>
    <div class="stat-box aktif"><div class="value">' . $aktifP . '</div><div class="label">Masih Aktif</div></div>
    <div class="stat-box selesai"><div class="value">' . $selesaiP . '</div><div class="label">Selesai</div></div>
</div>

<table>
<thead>
    <tr>
        <th style="width:30px">No</th>
        <th>Barang</th>
        <th>Kode</th>
        <th>Peminjam</th>
        <th>Tgl Pinjam</th>
        <th>Rencana Kembali</th>
        <th>Tgl Dikembalikan</th>
        <th>Status</th>
        <th>Keterangan</th>
        <th>Dicatat Oleh</th>
    </tr>
</thead>
<tbody>';

    foreach ($pinjamRows as $i => $r) {
        $late = $r['status'] === 'Aktif' && $r['tgl_kembali'] && strtotime($r['tgl_kembali']) < time();
        $statusClass = $late ? 'terlambat' : ($r['status'] === 'Aktif' ? 'aktif' : 'selesai');
        $statusLabel = $late ? 'Terlambat' : $r['status'];
        
        $html .= '
    <tr>
        <td style="text-align:center;color:#888">' . ($i + 1) . '</td>
        <td style="font-weight:600">' . htmlspecialchars($r['barang_nama']) . '</td>
        <td style="font-family:monospace">' . htmlspecialchars($r['barang_kode'] ?? '-') . '</td>
        <td>' . htmlspecialchars($r['peminjam']) . '</td>
        <td>' . htmlspecialchars($r['tgl_pinjam']) . '</td>
        <td>' . htmlspecialchars($r['tgl_kembali'] ?? '-') . '</td>
        <td>' . htmlspecialchars($r['tgl_dikembalikan'] ?? '-') . '</td>
        <td><span class="badge badge-' . $statusClass . '">' . $statusLabel . '</span></td>
        <td style="max-width:150px;font-size:10px">' . htmlspecialchars($r['keterangan'] ?? '-') . '</td>
        <td>' . htmlspecialchars($r['pencatat_nama'] ?? '-') . '</td>
    </tr>';
    }

    $html .= '
</tbody>
</table>

<div class="signature">
    <div class="sign-box">
        <div class="title">Mengetahui,</div>
        <div class="subtitle">Kepala Sekolah</div>
        <div class="line"></div>
        <div class="subtitle">NIP. ___________________</div>
    </div>
    <div class="sign-box">
        <div class="title">Dibuat oleh,</div>
        <div class="subtitle">Pengelola Inventaris</div>
        <div class="line"></div>
        <div class="subtitle">NIP. ___________________</div>
    </div>
</div>

<div class="footer">
    Dokumen ini dicetak secara otomatis oleh Sistem Inventaris Sekolah — ' . date('Y') . '
</div>
</body>
</html>';
    
    echo $html;
    exit;
}

// ─── Export CSV ───────────────────────────────────────────────────────────────
if (!empty($_GET['export']) && $_GET['export'] === 'csv') {
    $filename = 'laporan_inventaris_' . date('Ymd_His') . '.csv';
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    echo "\xEF\xBB\xBF"; // UTF-8 BOM for Excel

    $out = fopen('php://output', 'w');
    fputcsv($out, ['No', 'Nama Barang', 'Kategori', 'Jenis', 'Kode', 'Tahun', 'Kondisi', 'Sumber', 'Lokasi', 'Status', 'Catatan']);
    foreach ($rows as $i => $r) {
        fputcsv($out, [
            $i + 1,
            $r['nama'],
            $r['kategori'],
            $r['jenis']    ?? '-',
            $r['kode']     ?? '-',
            $r['tahun']    ?? '-',
            $r['kondisi'],
            $r['sumber']   ?? '-',
            $r['lokasi']   ?? '-',
            $r['status'],
            $r['catatan']  ?? '-',
        ]);
    }
    fclose($out);
    exit;
}

// ─── JSON ─────────────────────────────────────────────────────────────────────
header('Content-Type: application/json');

// Statistik ringkasan
$total  = count($rows);
$baik   = count(array_filter($rows, fn($r) => $r['kondisi'] === 'Baik'));
$cukup  = count(array_filter($rows, fn($r) => $r['kondisi'] === 'Cukup Baik'));
$rusak  = count(array_filter($rows, fn($r) => $r['kondisi'] === 'Rusak'));
$pinjam = count(array_filter($rows, fn($r) => $r['status'] === 'Dipinjam'));

echo json_encode([
    'stats' => compact('total', 'baik', 'cukup', 'rusak', 'pinjam'),
    'data'  => $rows,
]);
