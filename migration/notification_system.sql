-- ============================================================
-- Migration: Sistem Notifikasi WhatsApp & Email Terintegrasi
-- Proyek : Sistem Informasi Fasilitas STMIK IKMI Cirebon
-- Tanggal : 2026-11-03
-- ============================================================

-- LANGKAH 1: Perpanjang tabel notifikasi yang sudah ada
-- (Tambahkan kolom baru tanpa menghapus yang lama)
ALTER TABLE `notifikasi`
    ADD COLUMN IF NOT EXISTS `laporan_id`        INT UNSIGNED     NULL         AFTER `user_id`,
    ADD COLUMN IF NOT EXISTS `idempotency_key`   VARCHAR(100)     NULL         AFTER `laporan_id`,
    MODIFY COLUMN `tipe` ENUM('info','laporan','pemeliharaan','sistem','whatsapp','email') NOT NULL DEFAULT 'info',
    ADD COLUMN IF NOT EXISTS `read_at`           TIMESTAMP        NULL         AFTER `is_read`,
    ADD INDEX IF NOT EXISTS `idx_laporan_id`     (`laporan_id`),
    ADD INDEX IF NOT EXISTS `idx_idempotency`    (`idempotency_key`);

-- LANGKAH 2: Tabel log pengiriman WhatsApp & Email (kanal eksternal)
CREATE TABLE IF NOT EXISTS `notif_logs` (
    `id`                INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `notifikasi_id`     INT UNSIGNED    NULL         COMMENT 'FK ke notifikasi (in-app), NULL jika standalone',
    `user_id`           INT UNSIGNED    NULL,
    `laporan_id`        INT UNSIGNED    NULL,
    `kanal`             ENUM('whatsapp','email')  NOT NULL,
    `event_type`        VARCHAR(60)     NOT NULL     COMMENT 'laporan_baru, status_update, selesai, dll',
    `penerima`          VARCHAR(150)    NOT NULL     COMMENT 'nomor WA atau alamat email',
    `judul`             VARCHAR(200)    NULL         COMMENT 'Subject email / judul WA',
    `ringkasan_pesan`   TEXT            NULL         COMMENT 'Ringkasan (bukan isi lengkap)',
    `status`            ENUM('pending','processing','sent','failed') NOT NULL DEFAULT 'pending',
    `percobaan`         TINYINT         NOT NULL DEFAULT 0,
    `max_percobaan`     TINYINT         NOT NULL DEFAULT 3,
    `sent_at`           TIMESTAMP       NULL,
    `next_retry_at`     TIMESTAMP       NULL,
    `ref_id`            VARCHAR(100)    NULL         COMMENT 'ID dari penyedia (Fonnte msg ID, dsb)',
    `respons_provider`  TEXT            NULL         COMMENT 'Respons API yang sudah disanitasi',
    `keterangan_gagal`  TEXT            NULL,
    `idempotency_key`   VARCHAR(100)    NULL,
    `created_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_kanal_status`    (`kanal`, `status`),
    INDEX `idx_user_id`         (`user_id`),
    INDEX `idx_laporan_id`      (`laporan_id`),
    INDEX `idx_idempotency`     (`idempotency_key`),
    INDEX `idx_next_retry`      (`next_retry_at`),
    INDEX `idx_created_at`      (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Log pengiriman notifikasi WhatsApp dan Email';

-- LANGKAH 3: Tabel konfigurasi notifikasi (pengaturan Administrator)
CREATE TABLE IF NOT EXISTS `notif_settings` (
    `id`                INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `setting_key`       VARCHAR(100)    NOT NULL UNIQUE,
    `setting_value`     TEXT            NULL,
    `setting_type`      ENUM('boolean','string','json','number') NOT NULL DEFAULT 'string',
    `label`             VARCHAR(150)    NOT NULL,
    `deskripsi`         TEXT            NULL,
    `updated_by`        INT UNSIGNED    NULL,
    `updated_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Konfigurasi sistem notifikasi';

-- LANGKAH 4: Data default pengaturan notifikasi
INSERT IGNORE INTO `notif_settings`
    (`setting_key`, `setting_value`, `setting_type`, `label`, `deskripsi`) VALUES
-- Toggle global
('notif_enabled',           '1', 'boolean', 'Aktifkan Sistem Notifikasi',              'Master switch untuk seluruh notifikasi sistem.'),
('whatsapp_enabled',        '0', 'boolean', 'Aktifkan Notifikasi WhatsApp',            'Kirim pesan WhatsApp melalui API Fonnte.'),
('email_enabled',           '0', 'boolean', 'Aktifkan Notifikasi Email',               'Kirim email melalui server SMTP.'),
-- Event triggers
('notif_laporan_baru',      '1', 'boolean', 'Notifikasi Laporan Baru',                 'Kirim notifikasi saat laporan kerusakan baru dibuat.'),
('notif_status_update',     '1', 'boolean', 'Notifikasi Perubahan Status',             'Kirim notifikasi saat status laporan berubah.'),
('notif_selesai',           '1', 'boolean', 'Notifikasi Laporan Selesai',              'Kirim notifikasi saat laporan selesai ditangani.'),
('notif_ditolak',           '1', 'boolean', 'Notifikasi Laporan Ditolak',              'Kirim notifikasi saat laporan ditolak.'),
-- Penerima per event
('penerima_laporan_baru_roles', '["petugas_sarpras","admin"]', 'json', 'Penerima Laporan Baru (Role)', 'Role yang menerima notifikasi laporan baru.'),
('penerima_status_update_roles','["pengguna"]',                'json', 'Penerima Update Status (Role)','Role yang menerima notifikasi perubahan status.'),
-- WhatsApp tambahan (nomor manual)
('wa_notif_tambahan',       '[]',   'json',    'Nomor WA Tambahan',                   'Nomor WhatsApp tambahan (selain dari data user) dalam format JSON array.'),
-- Email tambahan
('email_notif_tambahan',    '[]',   'json',    'Email Tambahan',                       'Alamat email tambahan dalam format JSON array.'),
-- Retry config
('notif_max_retry',         '3',    'number',  'Maks Percobaan Pengiriman',            'Jumlah maksimum percobaan ulang pengiriman.'),
('notif_retry_delay_min',   '5',    'number',  'Jeda Retry (menit)',                   'Jeda waktu antar percobaan ulang dalam menit.');

