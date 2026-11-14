<?php
/**
 * Migrasi Skema Tabel Pemeliharaan
 * Menambahkan kolom sesuai spesifikasi modul Pemeliharaan & Riwayat Fasilitas
 */

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Database.php';

try {
    $db = new Database();
    echo "=== MEMULAI MIGRASI TABEL PEMELIHARAAN ===\n";

    // 1. Ambil kolom yang sudah ada
    $existingColumns = [];
    $cols = $db->query("DESCRIBE `pemeliharaan`")->resultSet();
    foreach ($cols as $c) {
        $existingColumns[] = $c['Field'];
    }

    // 2. Modifikasi jenis_tindakan menjadi VARCHAR(50)
    echo "1. Memperbarui kolom 'jenis_tindakan'...\n";
    $db->query("ALTER TABLE `pemeliharaan` MODIFY COLUMN `jenis_tindakan` VARCHAR(50) NOT NULL DEFAULT 'Pemeliharaan Rutin'");
    $db->execute();
    echo "   [✓] 'jenis_tindakan' berhasil diperbarui.\n";

    // 3. Tambah kolom tanggal jika belum ada
    if (!in_array('tanggal', $existingColumns)) {
        echo "2. Menambahkan kolom 'tanggal'...\n";
        $db->query("ALTER TABLE `pemeliharaan` ADD COLUMN `tanggal` DATE NULL AFTER `petugas_id`");
        $db->execute();
        $db->query("UPDATE `pemeliharaan` SET `tanggal` = `tanggal_mulai` WHERE `tanggal` IS NULL");
        $db->execute();
        echo "   [✓] Kolom 'tanggal' berhasil ditambahkan & disinkronkan.\n";
    }

    // 4. Tambah kolom kondisi_sebelum jika belum ada
    if (!in_array('kondisi_sebelum', $existingColumns)) {
        echo "3. Menambahkan kolom 'kondisi_sebelum'...\n";
        $db->query("ALTER TABLE `pemeliharaan` ADD COLUMN `kondisi_sebelum` ENUM('baik', 'rusak_ringan', 'rusak_berat', 'tidak_layak', 'hilang') NOT NULL DEFAULT 'baik' AFTER `jenis_tindakan`");
        $db->execute();
        echo "   [✓] Kolom 'kondisi_sebelum' berhasil ditambahkan.\n";
    }

    // 5. Tambah kolom kondisi_sesudah jika belum ada
    if (!in_array('kondisi_sesudah', $existingColumns)) {
        echo "4. Menambahkan kolom 'kondisi_sesudah'...\n";
        $db->query("ALTER TABLE `pemeliharaan` ADD COLUMN `kondisi_sesudah` ENUM('baik', 'rusak_ringan', 'rusak_berat', 'tidak_layak', 'hilang') NOT NULL DEFAULT 'baik' AFTER `kondisi_sebelum`");
        $db->execute();
        echo "   [✓] Kolom 'kondisi_sesudah' berhasil ditambahkan.\n";
    }

    // 6. Tambah kolom deskripsi jika belum ada
    if (!in_array('deskripsi', $existingColumns)) {
        echo "5. Menambahkan kolom 'deskripsi'...\n";
        $db->query("ALTER TABLE `pemeliharaan` ADD COLUMN `deskripsi` TEXT NULL AFTER `kondisi_sesudah`");
        $db->execute();
        $db->query("UPDATE `pemeliharaan` SET `deskripsi` = `keterangan` WHERE `deskripsi` IS NULL AND `keterangan` IS NOT NULL");
        $db->execute();
        echo "   [✓] Kolom 'deskripsi' berhasil ditambahkan.\n";
    }

    // 7. Tambah kolom tampil_publik jika belum ada
    if (!in_array('tampil_publik', $existingColumns)) {
        echo "6. Menambahkan kolom 'tampil_publik'...\n";
        $db->query("ALTER TABLE `pemeliharaan` ADD COLUMN `tampil_publik` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = Tampil untuk publik/pengguna, 0 = Internal sarpras' AFTER `status`");
        $db->execute();
        echo "   [✓] Kolom 'tampil_publik' berhasil ditambahkan.\n";
    }

    // 8. Normalisasi jenis tindakan lama
    echo "7. Normalisasi data jenis tindakan lama...\n";
    $db->query("UPDATE `pemeliharaan` SET `jenis_tindakan` = 'Pemeliharaan Rutin' WHERE `jenis_tindakan` IN ('pencegahan', 'Pemeliharaan Rutin')");
    $db->execute();
    $db->query("UPDATE `pemeliharaan` SET `jenis_tindakan` = 'Perbaikan' WHERE `jenis_tindakan` IN ('perbaikan', 'Perbaikan')");
    $db->query("UPDATE `pemeliharaan` SET `jenis_tindakan` = 'Penggantian' WHERE `jenis_tindakan` IN ('penggantian_part', 'Penggantian')");
    $db->query("UPDATE `pemeliharaan` SET `jenis_tindakan` = 'Pemeriksaan' WHERE `jenis_tindakan` IN ('kalibrasi', 'Pemeriksaan')");

    // Perbarui data seeder contoh
    $db->query("UPDATE `pemeliharaan` SET 
                `kondisi_sebelum` = 'baik', 
                `kondisi_sesudah` = 'baik',
                `deskripsi` = 'Perawatan berkala dan pembersihan filter unit AC'
                WHERE `id` = 1");
    $db->execute();

    $db->query("UPDATE `pemeliharaan` SET 
                `kondisi_sebelum` = 'rusak_ringan', 
                `kondisi_sesudah` = 'baik',
                `deskripsi` = 'Penggantian modul lampu dan perbaikan konektor display proyektor'
                WHERE `id` = 2");
    $db->execute();

    echo "   [✓] Data eksisting berhasil dinormalisasi.\n";
    echo "\n=== MIGRASI SELESAI DENGAN SUKSES ===\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
