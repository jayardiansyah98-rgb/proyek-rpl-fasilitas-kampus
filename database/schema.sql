-- ==============================================================================
-- SISTEM INFORMASI PENDATAAN FASILITAS SETIAP RUANGAN DI KAMPUS STMIK IKMI CIREBON
-- BERBASIS WEB DENGAN AI
-- File: schema.sql (DDL Skema Relasional 3NF & Seeder Master Data)
-- Database Engine: MySQL / MariaDB (InnoDB, utf8mb4)
-- ==============================================================================

CREATE DATABASE IF NOT EXISTS `db_fasilitas_ikmi`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `db_fasilitas_ikmi`;

-- Nonaktifkan pemeriksaan foreign key sementara saat proses inisialisasi tabel
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `ai_logs`;
DROP TABLE IF EXISTS `notifikasi`;
DROP TABLE IF EXISTS `pemeliharaan`;
DROP TABLE IF EXISTS `laporan_kerusakan`;
DROP TABLE IF EXISTS `fasilitas`;
DROP TABLE IF EXISTS `kategori_fasilitas`;
DROP TABLE IF EXISTS `ruangan`;
DROP TABLE IF EXISTS `gedung`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `roles`;

SET FOREIGN_KEY_CHECKS = 1;

-- ==============================================================================
-- 1. TABEL: roles
-- Role pengguna sistem (Admin, Petugas Sarpras, Pengguna Civitas)
-- ==============================================================================
CREATE TABLE `roles` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama_role` VARCHAR(50) NOT NULL,
  `deskripsi` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_roles_nama_role` (`nama_role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================================
-- 2. TABEL: users
-- Akun pengguna kampus (Admin, Teknisi/Petugas Sarpras, Dosen, Mahasiswa, Staff)
-- ==============================================================================
CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_id` INT UNSIGNED NOT NULL,
  `nomor_induk` VARCHAR(50) NOT NULL COMMENT 'NIM / NIDN / NIP Civitas STMIK IKMI',
  `nama_lengkap` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `password` VARCHAR(255) NOT NULL COMMENT 'Password terenkripsi (Bcrypt hash)',
  `telepon` VARCHAR(20) DEFAULT NULL,
  `jenis_pengguna` ENUM('dosen', 'mahasiswa', 'staff', 'umum') NOT NULL DEFAULT 'mahasiswa',
  `status` ENUM('aktif', 'nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_nomor_induk` (`nomor_induk`),
  UNIQUE KEY `uq_users_email` (`email`),
  KEY `idx_users_role_id` (`role_id`),
  KEY `idx_users_status` (`status`),
  KEY `idx_users_jenis_pengguna` (`jenis_pengguna`),
  CONSTRAINT `fk_users_role` 
    FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) 
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================================
-- 3. TABEL: gedung
-- Gedung-gedung di lingkungan kampus STMIK IKMI Cirebon
-- ==============================================================================
CREATE TABLE `gedung` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kode_gedung` VARCHAR(20) NOT NULL,
  `nama_gedung` VARCHAR(100) NOT NULL,
  `lokasi` VARCHAR(255) DEFAULT NULL,
  `jumlah_lantai` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `deskripsi` TEXT DEFAULT NULL,
  `keterangan` TEXT DEFAULT NULL,
  `status` ENUM('aktif', 'renovasi', 'nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_gedung_kode_gedung` (`kode_gedung`),
  KEY `idx_gedung_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================================
-- 4. TABEL: ruangan
-- Ruangan pada gedung (Lab, Kelas Teori, Ruang Dosen, Ruang Rapat, dll.)
-- Relasi: Gedung 1:N Ruangan
-- ==============================================================================
CREATE TABLE `ruangan` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `gedung_id` INT UNSIGNED NOT NULL,
  `kode_ruangan` VARCHAR(30) NOT NULL,
  `nama_ruangan` VARCHAR(100) NOT NULL,
  `lantai` TINYINT NOT NULL DEFAULT 1,
  `jenis_ruangan` ENUM('Kelas', 'Laboratorium', 'Ruang Dosen', 'Ruang Administrasi', 'Ruang Rapat', 'Ruang Seminar', 'Lainnya') NOT NULL DEFAULT 'Kelas',
  `kapasitas` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `status` ENUM('tersedia', 'digunakan', 'perbaikan', 'nonaktif') NOT NULL DEFAULT 'tersedia',
  `keterangan` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ruangan_kode_ruangan` (`kode_ruangan`),
  KEY `idx_ruangan_gedung_id` (`gedung_id`),
  KEY `idx_ruangan_jenis_ruangan` (`jenis_ruangan`),
  KEY `idx_ruangan_status` (`status`),
  CONSTRAINT `fk_ruangan_gedung` 
    FOREIGN KEY (`gedung_id`) REFERENCES `gedung` (`id`) 
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================================
-- 5. TABEL: kategori_fasilitas
-- Klasifikasi kelompok fasilitas (Komputer, Jaringan, Multimedia, AC, Furnitur, dll.)
-- ==============================================================================
CREATE TABLE `kategori_fasilitas` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kode_kategori` VARCHAR(30) NOT NULL,
  `nama_kategori` VARCHAR(100) NOT NULL,
  `deskripsi` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_kategori_kode_kategori` (`kode_kategori`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================================
-- 6. TABEL: fasilitas
-- Inventaris barang/sarana dan prasarana di setiap ruangan
-- Relasi: Ruangan 1:N Fasilitas, Kategori 1:N Fasilitas
-- ==============================================================================
CREATE TABLE `fasilitas` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kode_inventaris` VARCHAR(50) NOT NULL,
  `nama` VARCHAR(150) NOT NULL,
  `kategori_id` INT UNSIGNED NOT NULL,
  `ruangan_id` INT UNSIGNED NOT NULL,
  `jumlah` INT UNSIGNED NOT NULL DEFAULT 1,
  `kondisi` ENUM('baik', 'rusak_ringan', 'rusak_berat', 'tidak_layak', 'hilang') NOT NULL DEFAULT 'baik',
  `tahun_pengadaan` YEAR NOT NULL,
  `foto` VARCHAR(255) DEFAULT NULL,
  `keterangan` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_fasilitas_kode_inventaris` (`kode_inventaris`),
  KEY `idx_fasilitas_kategori_id` (`kategori_id`),
  KEY `idx_fasilitas_ruangan_id` (`ruangan_id`),
  KEY `idx_fasilitas_kondisi` (`kondisi`),
  KEY `idx_fasilitas_tahun_pengadaan` (`tahun_pengadaan`),
  CONSTRAINT `fk_fasilitas_kategori` 
    FOREIGN KEY (`kategori_id`) REFERENCES `kategori_fasilitas` (`id`) 
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_fasilitas_ruangan` 
    FOREIGN KEY (`ruangan_id`) REFERENCES `ruangan` (`id`) 
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================================
-- 7. TABEL: laporan_kerusakan
-- Tiket pelaporan kerusakan fasilitas ruangan oleh user civitas/petugas
-- Relasi: User 1:N Laporan, Fasilitas 1:N Laporan, Ruangan 1:N Laporan
-- ==============================================================================
CREATE TABLE `laporan_kerusakan` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kode_laporan` VARCHAR(50) NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `fasilitas_id` INT UNSIGNED NOT NULL,
  `ruangan_id` INT UNSIGNED NOT NULL,
  `deskripsi` TEXT NOT NULL,
  `foto` VARCHAR(255) DEFAULT NULL,
  `prioritas` ENUM('rendah', 'sedang', 'tinggi', 'darurat') NOT NULL DEFAULT 'sedang',
  `status` ENUM('menunggu', 'diverifikasi', 'diproses', 'selesai', 'ditolak') NOT NULL DEFAULT 'menunggu',
  `catatan_petugas` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_laporan_kode_laporan` (`kode_laporan`),
  KEY `idx_laporan_user_id` (`user_id`),
  KEY `idx_laporan_fasilitas_id` (`fasilitas_id`),
  KEY `idx_laporan_ruangan_id` (`ruangan_id`),
  KEY `idx_laporan_status` (`status`),
  KEY `idx_laporan_prioritas` (`prioritas`),
  KEY `idx_laporan_created_at` (`created_at`),
  CONSTRAINT `fk_laporan_user` 
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) 
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_laporan_fasilitas` 
    FOREIGN KEY (`fasilitas_id`) REFERENCES `fasilitas` (`id`) 
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_laporan_ruangan` 
    FOREIGN KEY (`ruangan_id`) REFERENCES `ruangan` (`id`) 
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================================
-- 8. TABEL: pemeliharaan
-- Tindakan perawatan berkala atau perbaikan fasilitas oleh petugas sarpras
-- Relasi: Fasilitas 1:N Pemeliharaan, User (Petugas) 1:N Pemeliharaan, Laporan 1:N (opsional)
-- ==============================================================================
CREATE TABLE `pemeliharaan` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kode_pemeliharaan` VARCHAR(50) NOT NULL,
  `fasilitas_id` INT UNSIGNED NOT NULL,
  `laporan_id` INT UNSIGNED DEFAULT NULL,
  `petugas_id` INT UNSIGNED NOT NULL COMMENT 'User dengan role Petugas Sarpras',
  `tanggal` DATE DEFAULT NULL,
  `tanggal_mulai` DATE NOT NULL,
  `tanggal_selesai` DATE DEFAULT NULL,
  `jenis_tindakan` VARCHAR(50) NOT NULL DEFAULT 'Pemeliharaan Rutin' COMMENT 'Pemeriksaan, Perbaikan, Penggantian, Pemeliharaan Rutin',
  `kondisi_sebelum` ENUM('baik', 'rusak_ringan', 'rusak_berat', 'tidak_layak', 'hilang') NOT NULL DEFAULT 'baik',
  `kondisi_sesudah` ENUM('baik', 'rusak_ringan', 'rusak_berat', 'tidak_layak', 'hilang') NOT NULL DEFAULT 'baik',
  `deskripsi` TEXT DEFAULT NULL,
  `biaya` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
  `keterangan` TEXT DEFAULT NULL,
  `status` ENUM('dijadwalkan', 'sedang_dikerjakan', 'selesai', 'batal') NOT NULL DEFAULT 'dijadwalkan',
  `tampil_publik` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = Tampil untuk publik/pengguna, 0 = Internal sarpras',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pemeliharaan_kode_pemeliharaan` (`kode_pemeliharaan`),
  KEY `idx_pemeliharaan_fasilitas_id` (`fasilitas_id`),
  KEY `idx_pemeliharaan_laporan_id` (`laporan_id`),
  KEY `idx_pemeliharaan_petugas_id` (`petugas_id`),
  KEY `idx_pemeliharaan_status` (`status`),
  KEY `idx_pemeliharaan_tanggal` (`tanggal`),
  KEY `idx_pemeliharaan_tanggal_mulai` (`tanggal_mulai`),
  CONSTRAINT `fk_pemeliharaan_fasilitas` 
    FOREIGN KEY (`fasilitas_id`) REFERENCES `fasilitas` (`id`) 
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_pemeliharaan_laporan` 
    FOREIGN KEY (`laporan_id`) REFERENCES `laporan_kerusakan` (`id`) 
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_pemeliharaan_petugas` 
    FOREIGN KEY (`petugas_id`) REFERENCES `users` (`id`) 
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================================
-- 9. TABEL: notifikasi
-- Notifikasi interaktif untuk user tentang status laporan, pemeliharaan, dsb.
-- ==============================================================================
CREATE TABLE `notifikasi` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `judul` VARCHAR(150) NOT NULL,
  `pesan` TEXT NOT NULL,
  `tipe` ENUM('info', 'laporan', 'pemeliharaan', 'sistem') NOT NULL DEFAULT 'info',
  `tautan` VARCHAR(255) DEFAULT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_notifikasi_user_read` (`user_id`, `is_read`),
  KEY `idx_notifikasi_created_at` (`created_at`),
  CONSTRAINT `fk_notifikasi_user` 
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================================
-- 10. TABEL: ai_logs
-- Rekam jejak interaksi dan inferensi model AI (analisis gambar kerusakan, dsb.)
-- ==============================================================================
CREATE TABLE `ai_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `question` TEXT DEFAULT NULL COMMENT 'Pertanyaan input pengguna ke AI Assistant',
  `response` TEXT DEFAULT NULL COMMENT 'Jawaban terverifikasi yang dihasilkan AI Assistant',
  `fitur` VARCHAR(100) NOT NULL DEFAULT 'ai_assistant' COMMENT 'e.g. ai_assistant, analisis_gambar_kerusakan, estimasi_biaya',
  `prompt_input` TEXT DEFAULT NULL,
  `response_output` TEXT DEFAULT NULL,
  `metadata_json` JSON DEFAULT NULL,
  `status` ENUM('sukses', 'gagal', 'pending') NOT NULL DEFAULT 'sukses',
  `waktu_eksekusi_ms` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ai_logs_user_id` (`user_id`),
  KEY `idx_ai_logs_fitur` (`fitur`),
  KEY `idx_ai_logs_status` (`status`),
  KEY `idx_ai_logs_created_at` (`created_at`),
  CONSTRAINT `fk_ai_logs_user` 
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) 
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ==============================================================================
-- DATA SEEDER AWAL & MASTER TESTING
-- ==============================================================================

-- 1. Seed Roles
INSERT INTO `roles` (`id`, `nama_role`, `deskripsi`) VALUES
(1, 'admin', 'Administrator sistem penuh dengan hak akses konfigurasi, master data, dan kontrol pengguna'),
(2, 'petugas_sarpras', 'Petugas Sarana & Prasarana untuk verifikasi kondisi, tindakan pemeliharaan, dan penugasan perbaikan'),
(3, 'pengguna', 'Civitas akademika (Dosen & Mahasiswa) untuk melihat ketersediaan fasilitas dan pelaporan kerusakan');

-- 2. Seed Users (Admin, Petugas, Dosen, Mahasiswa)
-- Password default:
-- admin    : admin123     (hash bcrypt $2y$10$BUzV5pTq5KqHVYljy2cGiOG0lvAE4G5n1MnufbmqA5pM/Aq13AeyK)
-- petugas  : petugas123   (hash bcrypt $2y$10$iJnmYvZgEUILk6nPGn4tteMCmeGpDqRmFIltcM.6jVuNO2PTmUrQy)
-- dosen    : dosen123     (hash bcrypt $2y$10$MzXBv6JmlsJRkMQQLmWZpe2agVTE7lKT6QbiuDfOomuhnxalqW6SK)
-- mahasiswa: mahasiswa123 (hash bcrypt $2y$10$jcmSlwvadJpYVfjjY6MCdOU4CLXeL3U/8eMUXqJsfbii2Q/mU5TRu)
INSERT INTO `users` (`id`, `role_id`, `nomor_induk`, `nama_lengkap`, `email`, `password`, `telepon`, `jenis_pengguna`, `status`) VALUES
(1, 1, 'ADM-001', 'Rizky Ramadhan', 'admin@ikmi.ac.id', '$2y$10$BUzV5pTq5KqHVYljy2cGiOG0lvAE4G5n1MnufbmqA5pM/Aq13AeyK', '081234567890', 'staff', 'aktif'),
(2, 2, 'PET-001', 'Budi Santoso, S.Kom.', 'petugas@ikmi.ac.id', '$2y$10$iJnmYvZgEUILk6nPGn4tteMCmeGpDqRmFIltcM.6jVuNO2PTmUrQy', '081398765432', 'staff', 'aktif'),
(3, 3, '0412088501', 'Dr. Dadang Sudrajat, M.Kom.', 'dosen@ikmi.ac.id', '$2y$10$MzXBv6JmlsJRkMQQLmWZpe2agVTE7lKT6QbiuDfOomuhnxalqW6SK', '082111223344', 'dosen', 'aktif'),
(4, 3, '4122001', 'Ahmad Fauzi', 'mahasiswa@ikmi.ac.id', '$2y$10$CQjtPGTDfb/4dmyrmC2J4.b.vwaRdSguzxrCyMAKJw3NRQF3Rpdfa', '085712345678', 'mahasiswa', 'aktif');

-- 3. Seed Gedung Kampus STMIK IKMI Cirebon
INSERT INTO `gedung` (`id`, `kode_gedung`, `nama_gedung`, `lokasi`, `jumlah_lantai`, `deskripsi`, `keterangan`, `status`) VALUES
(1, 'GD-A', 'Gedung Rektorat & Akademik Utama', 'Kampus Utama, Jl. Perjuangan No. 10B Cirebon', 3, 'Pusat layanan akademik, rektorat, ruang dosen, dan ruang seminar serbaguna', 'Pusat layanan akademik, rektorat, ruang dosen, dan ruang seminar serbaguna', 'aktif'),
(2, 'GD-B', 'Gedung ICT & Laboratorium Terpadu', 'Kampus Utama Gedung Timur, Jl. Perjuangan No. 10B Cirebon', 4, 'Pusat laboratorium komputer, riset kecerdasan buatan, jaringan kampus, dan multimedia', 'Pusat laboratorium komputer, riset kecerdasan buatan, jaringan kampus, dan multimedia', 'aktif');

-- 4. Seed Ruangan Kampus
INSERT INTO `ruangan` (`id`, `gedung_id`, `kode_ruangan`, `nama_ruangan`, `lantai`, `jenis_ruangan`, `kapasitas`, `status`, `keterangan`) VALUES
(1, 2, 'LAB-AI', 'Laboratorium AI & Data Science', 2, 'Laboratorium', 40, 'tersedia', 'Laboratorium komputasi cerdas, neural network, dan praktikum data science'),
(2, 2, 'LAB-NET', 'Laboratorium Jaringan & Cyber Security', 1, 'Laboratorium', 35, 'tersedia', 'Dilengkapi rak server, router cisco, switch managed untuk praktikum jaringan'),
(3, 1, 'RK-201', 'Ruang Kuliah Teori 201', 2, 'Kelas', 50, 'tersedia', 'Ruang perkuliahan reguler dilengkapi smart proyektor dan pendingin ruangan'),
(4, 1, 'R-DOSEN', 'Ruang Dosen & Konsultasi Akademik', 1, 'Ruang Dosen', 25, 'tersedia', 'Ruang kerja dosen tetap dan bimbingan akademik mahasiswa');

-- 5. Seed Kategori Fasilitas
INSERT INTO `kategori_fasilitas` (`id`, `kode_kategori`, `nama_kategori`, `deskripsi`) VALUES
(1, 'KAT-KOMP', 'Komputer & Server', 'PC Workstation, server, monitor display, mini PC, dan peripheral input/output'),
(2, 'KAT-JAR', 'Jaringan & Internet', 'Router mikrotik/cisco, switch managed, access point Wi-Fi, kabel patch cord UTP'),
(3, 'KAT-MULT', 'Multimedia & Proyektor', 'LCD Projector, Smart TV Interactive, motorized projector screen, pointer remote'),
(4, 'KAT-PEND', 'Pendingin Ruangan (AC)', 'Air Conditioner split wall, standing floor unit, ceiling cassette'),
(5, 'KAT-FURN', 'Furnitur & Ergonomi', 'Meja komputer workstation, kursi kuliah hidrolik, meja dosen, papan tulis white board'),
(6, 'KAT-LIST', 'Kelistrikan & UPS', 'Uninterruptible Power Supply (UPS), panel distribusi listrik, smart plug, kabel power'),
(7, 'KAT-AUD', 'Audio & Sound System', 'Wireless microphone, active speaker, audio mixer, amplifier'),
(8, 'KAT-KEB', 'Kebersihan & Sanitasi', 'Tempat sampah daur ulang, dispenser air minum otomatis, pembersih udara');

-- 6. Seed Fasilitas Ruangan
INSERT INTO `fasilitas` (`id`, `kode_inventaris`, `nama`, `kategori_id`, `ruangan_id`, `jumlah`, `kondisi`, `tahun_pengadaan`, `foto`, `keterangan`) VALUES
(1, 'INV-2024-AI-001', 'Workstation PC Intel Core i9 GPU RTX 4080', 1, 1, 30, 'baik', 2024, 'pc_lab_ai.jpg', 'Digunakan untuk komputasi deep learning dan computer vision'),
(2, 'INV-2023-AC-014', 'AC Split Daikin Inverter 2 PK', 4, 1, 2, 'baik', 2023, 'ac_lab_ai.jpg', 'Pendingin ruangan Lab AI dengan fitur hemat energi'),
(3, 'INV-2023-NET-005', 'Cisco Catalyst 2960-X 48-Port Switch', 2, 2, 2, 'baik', 2023, 'cisco_switch.jpg', 'Switch managed tulang punggung jaringan praktikum'),
(4, 'INV-2022-PRJ-011', 'Epson EB-X51 Proyektor XGA 3800 Lumens', 3, 3, 1, 'rusak_ringan', 2022, 'proyektor_rk201.jpg', 'Lampu mulai redup dan port HDMI kadang terputus sinyal'),
(5, 'INV-2024-AC-022', 'AC Split Panasonic 1.5 PK', 4, 3, 2, 'baik', 2024, 'ac_rk201.jpg', 'Pendingin ruang kuliah teori lantai 2'),
(6, 'INV-2023-FRN-089', 'Kursi Kuliah Chitose Ergonomis Lipat', 5, 3, 50, 'baik', 2023, 'kursi_rk201.jpg', 'Kursi belajar mahasiswa dengan bantalan busa dan meja tulis'),
(7, 'INV-2024-FRN-003', 'Meja Kerja Partisi Dosen Kayu Jati Minimalis', 5, 4, 12, 'baik', 2024, 'meja_dosen.jpg', 'Meja kerja modular dosen dengan rak berkas dan laci gembok');

-- 7. Seed Laporan Kerusakan
INSERT INTO `laporan_kerusakan` (`id`, `kode_laporan`, `user_id`, `fasilitas_id`, `ruangan_id`, `deskripsi`, `foto`, `prioritas`, `status`, `catatan_petugas`) VALUES
(1, 'LAP-202609-001', 3, 4, 3, 'Tampilan proyektor di Ruang 201 sering berkedip dan lampu mulai redup saat dipakai perkuliahan pagi.', 'lap_proyektor_201.jpg', 'sedang', 'diproses', 'Sudah dijadwalkan pengecekan lampu dan kabel HDMI oleh teknisi.');

-- 8. Seed Pemeliharaan
INSERT INTO `pemeliharaan` (`id`, `kode_pemeliharaan`, `fasilitas_id`, `laporan_id`, `petugas_id`, `tanggal_mulai`, `tanggal_selesai`, `jenis_tindakan`, `biaya`, `keterangan`, `status`) VALUES
(1, 'MT-202609-001', 2, NULL, 2, '2026-09-01', '2026-09-01', 'pencegahan', 250000.00, 'Cuci berkala unit indoor/outdoor dan pengecekan tekanan freon AC Lab AI', 'selesai'),
(2, 'MT-202609-002', 4, 1, 2, '2026-09-05', NULL, 'perbaikan', 450000.00, 'Pemeriksaan modul lampu proyektor dan konektor port HDMI', 'dijadwalkan');

-- 9. Seed Notifikasi
INSERT INTO `notifikasi` (`id`, `user_id`, `judul`, `pesan`, `tipe`, `tautan`, `is_read`) VALUES
(1, 3, 'Laporan Kerusakan Sedang Diproses', 'Laporan Anda untuk Proyektor Ruang 201 telah diverifikasi dan masuk jadwal penanganan tim Sarpras.', 'laporan', '/laporan/detail/1', 0),
(2, 2, 'Jadwal Pemeliharaan Baru', 'Anda memiliki jadwal pemeliharaan Proyektor EB-X51 di Ruang 201.', 'pemeliharaan', '/pemeliharaan/detail/2', 0);

-- 10. Seed AI Logs
INSERT INTO `ai_logs` (`id`, `user_id`, `fitur`, `prompt_input`, `response_output`, `metadata_json`, `status`, `waktu_eksekusi_ms`) VALUES
(1, 3, 'analisis_gambar_kerusakan', 'Analisis foto display proyektor buram dan bergaris vertikal kuning.', 'Terdeteksi degradasi panel optik proyektor LCD dan kemungkinan kotoran debu pada lensa prisma. Rekomendasi: pembersihan prisma dan kalibrasi optik.', '{"model": "gemini-1.5-flash", "confidence": 0.92, "category": "display_optical"}', 'sukses', 1240);
