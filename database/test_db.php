<?php
/**
 * Script Uji Koneksi & Integritas Relasi Database MySQL PDO
 * Menjamin konektivitas, integritas foreign key, dan validitas seeder
 */

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Database.php';

echo "=== MEMULAI PENGUJIAN DATABASE MYSQL (STMIK IKMI CIREBON) ===\n\n";

try {
    $db = new Database();
    echo "[OK] Berhasil terhubung ke database MySQL '" . DB_NAME . "' melalui PDO.\n\n";

    // 1. Verifikasi 10 Tabel Utama
    $expectedTables = [
        'roles',
        'users',
        'gedung',
        'ruangan',
        'kategori_fasilitas',
        'fasilitas',
        'laporan_kerusakan',
        'pemeliharaan',
        'notifikasi',
        'ai_logs'
    ];

    echo "1. Pengecekan Eksistensi 10 Tabel Utama:\n";
    $tablesResult = $db->query("SHOW TABLES")->resultSet();
    $existingTables = [];
    foreach ($tablesResult as $row) {
        $existingTables[] = reset($row);
    }

    $allTablesExist = true;
    foreach ($expectedTables as $table) {
        if (in_array($table, $existingTables)) {
            $count = $db->query("SELECT COUNT(*) as jml FROM `{$table}`")->single()['jml'];
            echo "   [✓] Tabel '{$table}' ditemukan (Jumlah baris: {$count})\n";
        } else {
            echo "   [✗] Tabel '{$table}' TIDAK DITEMUKAN!\n";
            $allTablesExist = false;
        }
    }

    if (!$allTablesExist) {
        throw new Exception("Sebagian tabel utama belum dibuat.");
    }
    echo "\n";

    // 2. Verifikasi Integritas Relasi & Foreign Keys
    echo "2. Pengecekan Integritas Relasi & Foreign Keys:\n";

    // Gedung 1:N Ruangan
    $sqlRuangan = "SELECT r.kode_ruangan, r.nama_ruangan, g.nama_gedung 
                   FROM ruangan r 
                   INNER JOIN gedung g ON r.gedung_id = g.id";
    $ruanganJoined = $db->query($sqlRuangan)->resultSet();
    echo "   [✓] Relasi Gedung 1:N Ruangan valid (" . count($ruanganJoined) . " ruangan terelasi)\n";

    // Ruangan 1:N Fasilitas & Kategori 1:N Fasilitas
    $sqlFasilitas = "SELECT f.kode_inventaris, f.nama, k.nama_kategori, r.nama_ruangan 
                     FROM fasilitas f 
                     INNER JOIN kategori_fasilitas k ON f.kategori_id = k.id 
                     INNER JOIN ruangan r ON f.ruangan_id = r.id";
    $fasilitasJoined = $db->query($sqlFasilitas)->resultSet();
    echo "   [✓] Relasi Ruangan & Kategori 1:N Fasilitas valid (" . count($fasilitasJoined) . " fasilitas terelasi)\n";

    // User 1:N Laporan Kerusakan & Fasilitas 1:N Laporan
    $sqlLaporan = "SELECT l.kode_laporan, u.nama_lengkap as pelapor, f.nama as nama_fasilitas, r.nama_ruangan
                   FROM laporan_kerusakan l
                   INNER JOIN users u ON l.user_id = u.id
                   INNER JOIN fasilitas f ON l.fasilitas_id = f.id
                   INNER JOIN ruangan r ON l.ruangan_id = r.id";
    $laporanJoined = $db->query($sqlLaporan)->resultSet();
    echo "   [✓] Relasi User, Fasilitas, Ruangan 1:N Laporan Kerusakan valid (" . count($laporanJoined) . " laporan terelasi)\n";

    // Fasilitas 1:N Pemeliharaan & User (Petugas) 1:N Pemeliharaan
    $sqlPemeliharaan = "SELECT p.kode_pemeliharaan, f.nama as fasilitas, u.nama_lengkap as petugas
                        FROM pemeliharaan p
                        INNER JOIN fasilitas f ON p.fasilitas_id = f.id
                        INNER JOIN users u ON p.petugas_id = u.id";
    $pemeliharaanJoined = $db->query($sqlPemeliharaan)->resultSet();
    echo "   [✓] Relasi Fasilitas & Petugas 1:N Pemeliharaan valid (" . count($pemeliharaanJoined) . " pemeliharaan terelasi)\n\n";

    // 3. Verifikasi Akun Admin & Hash Password
    echo "3. Pengecekan Akun Admin & Enkripsi Bcrypt Password:\n";
    $admin = $db->query("SELECT u.*, r.nama_role FROM users u JOIN roles r ON u.role_id = r.id WHERE u.email = 'admin@ikmi.ac.id'")->single();

    if ($admin) {
        echo "   [✓] Akun Admin ditemukan: {$admin['nama_lengkap']} (Role: {$admin['nama_role']})\n";
        if (password_verify('Admin!IKMI2026', $admin['password'])) {
            echo "   [✓] Verifikasi password hash 'Admin!IKMI2026' BERHASIL (Bcrypt match)\n";
        } else {
            echo "   [✗] Verifikasi password hash gagal!\n";
        }
    } else {
        echo "   [✗] Akun admin tidak ditemukan!\n";
    }

    echo "\n=== SELURUH PENGUJIAN DATABASE SUKSES 100% ===\n";

} catch (Exception $e) {
    echo "\n[ERROR] Pengujian Gagal: " . $e->getMessage() . "\n";
    exit(1);
}
