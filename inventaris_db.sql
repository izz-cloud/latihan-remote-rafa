-- ============================================================
-- DATABASE: inventaris_db
-- Sistem Inventaris Sekolah dengan Multi-Peran
-- ============================================================

CREATE DATABASE IF NOT EXISTS inventaris_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE inventaris_db;

-- ============================================================
-- TABEL: kategori
-- ============================================================
CREATE TABLE IF NOT EXISTS kategori (
  id        INT AUTO_INCREMENT PRIMARY KEY,
  nama      VARCHAR(100) NOT NULL,
  ikon      VARCHAR(10)  DEFAULT '📦',
  created_at DATETIME    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO kategori (nama, ikon) VALUES
  ('Elektronik',          '💻'),
  ('Furnitur',            '🪑'),
  ('Alat Tulis',          '✏️'),
  ('Media Pembelajaran',  '📚'),
  ('Lainnya',             '📦');

-- ============================================================
-- TABEL: users
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  nama         VARCHAR(150) NOT NULL,
  username     VARCHAR(80)  NOT NULL UNIQUE,
  password     VARCHAR(255) NOT NULL,
  peran        ENUM('admin','guru') NOT NULL DEFAULT 'guru',
  aktif        TINYINT(1)   NOT NULL DEFAULT 1,
  created_at   DATETIME     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Password: admin123 / guru123
INSERT INTO users (nama, username, password, peran) VALUES
  ('Administrator Sistem', 'admin',    '$2y$10$xvUQnyINjZ0Lrz.BD7DIqeLQZYm14Ba/YVbIi6ValWI5/pp1C7YVe', 'admin'),
  ('Guru Pengelola',       'guru',     '$2y$10$IHenxbdkWygOamaBb4KTjubTIgPvKcbxJa1fB3MZuXDixYQRE0B0O', 'guru');

-- ============================================================
-- TABEL: barang
-- ============================================================
CREATE TABLE IF NOT EXISTS barang (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  nama         VARCHAR(200) NOT NULL,
  kategori_id  INT          NOT NULL,
  jenis        VARCHAR(100) DEFAULT NULL,
  kode         VARCHAR(50)  DEFAULT NULL,
  tahun        YEAR         DEFAULT NULL,
  kondisi      ENUM('Baik','Cukup Baik','Rusak') NOT NULL DEFAULT 'Baik',
  sumber       ENUM('Pribadi','Dana Sekolah','Donasi','Lainnya') DEFAULT 'Dana Sekolah',
  lokasi       VARCHAR(150) DEFAULT NULL,
  catatan      TEXT         DEFAULT NULL,
  status       ENUM('Tersedia','Dipinjam','Perbaikan') NOT NULL DEFAULT 'Tersedia',
  user_id      INT          DEFAULT NULL,
  created_at   DATETIME     DEFAULT CURRENT_TIMESTAMP,
  updated_at   DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (kategori_id) REFERENCES kategori(id),
  FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

INSERT INTO barang (nama, kategori_id, jenis, kode, tahun, kondisi, sumber, lokasi, catatan, status, user_id) VALUES
  ('Laptop ASUS VivoBook 15',      1, 'Laptop',     'LPT-001', 2022, 'Baik',      'Dana Sekolah', 'Ruang Guru', 'Digunakan untuk administrasi',          'Tersedia', 1),
  ('Proyektor Epson EB-X41',       1, 'Proyektor',  'PRY-001', 2021, 'Cukup Baik','Dana Sekolah', 'Kelas 9A',   'Lampu mulai redup, perlu penggantian',  'Tersedia', 1),
  ('Papan Tulis Putih 120x240',    4, 'Whiteboard', 'PWB-002', 2020, 'Baik',      'Dana Sekolah', 'Kelas 9A',   '',                                      'Tersedia', 1),
  ('Speaker Aktif Logitech Z200',  1, 'Speaker',    'SPK-001', 2023, 'Baik',      'Pribadi',      'Ruang Guru', '',                                      'Dipinjam', 1),
  ('Tripod Kamera Aluminium',      5, 'Tripod',     'TRP-001', 2021, 'Rusak',     'Pribadi',      'Gudang',     'Engsel kaki patah, perlu perbaikan',     'Tersedia', 1),
  ('Keyboard Wireless Logitech',   1, 'Keyboard',   'KBD-001', 2022, 'Baik',      'Pribadi',      'Ruang Guru', '',                                      'Tersedia', 1),
  ('Meja Guru Kayu Jati',          2, 'Meja',       'MBL-001', 2019, 'Cukup Baik','Dana Sekolah', 'Kelas 9A',   'Cat mulai mengelupas di bagian sudut',  'Tersedia', 1),
  ('Mouse Wireless Logitech M235', 1, 'Mouse',      'MOU-001', 2023, 'Baik',      'Pribadi',      'Ruang Guru', '',                                      'Tersedia', 2),
  ('Kursi Siswa Chitose',          2, 'Kursi',      'KRS-001', 2020, 'Cukup Baik','Dana Sekolah', 'Kelas 9A',   '30 unit, beberapa kaki goyah',          'Tersedia', 2),
  ('Penghapus Papan Tulis',        3, 'Penghapus',  'PHG-001', 2024, 'Baik',      'Dana Sekolah', 'Kelas 9A',   '',                                      'Tersedia', 2);

-- ============================================================
-- TABEL: peminjaman
-- ============================================================
CREATE TABLE IF NOT EXISTS peminjaman (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  barang_id    INT          NOT NULL,
  peminjam     VARCHAR(150) NOT NULL,
  jumlah       INT          NOT NULL DEFAULT 1,
  tgl_pinjam   DATE         NOT NULL,
  tgl_kembali  DATE         DEFAULT NULL,
  tgl_dikembalikan DATE     DEFAULT NULL,
  keterangan   TEXT         DEFAULT NULL,
  status       ENUM('Aktif','Selesai') NOT NULL DEFAULT 'Aktif',
  user_id      INT          DEFAULT NULL,
  created_at   DATETIME     DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (barang_id) REFERENCES barang(id),
  FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

INSERT INTO peminjaman (barang_id, peminjam, tgl_pinjam, tgl_kembali, keterangan, status, user_id) VALUES
  (4, 'Ahmad Fauzi (Guru Olahraga)', '2026-02-10', '2026-02-28', 'Untuk kegiatan ekstra kurikuler', 'Aktif', 1);

-- ============================================================
-- TABEL: riwayat
-- ============================================================
CREATE TABLE IF NOT EXISTS riwayat (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  aksi       VARCHAR(255) NOT NULL,
  user_id    INT          DEFAULT NULL,
  created_at DATETIME     DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

INSERT INTO riwayat (aksi, user_id) VALUES
  ('Sistem inventaris diinisialisasi dengan 10 barang contoh', 1),
  ('Peminjaman Speaker Aktif Logitech Z200 oleh Ahmad Fauzi dicatat', 2);
