<?php
/**
 * Script Penerapan Kata Sandi Awal untuk 4 Role Utama
 * Sistem Informasi Fasilitas STMIK IKMI Cirebon
 * 
 * Target Kredensial:
 * 1. Admin: admin@ikmi.ac.id / ADM-001
 * 2. Petugas Sarpras: petugas@ikmi.ac.id / PET-001
 * 3. Dosen: dosen@ikmi.ac.id / 0412088501
 * 4. Mahasiswa: mahasiswa@ikmi.ac.id / 4122001
 */

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Database.php';

echo "=== MEMULAI PENERAPAN KATA SANDI AWAL (STMIK IKMI CIREBON) ===\n\n";

$targetUsers = [
    [
        'id' => 1,
        'email' => 'admin@ikmi.ac.id',
        'nomor_induk' => 'ADM-001',
        'nama_lengkap' => 'Rizky Ramadhan',
        'role' => 'admin',
        'plain_password' => 'Admin!IKMI2026'
    ],
    [
        'id' => 2,
        'email' => 'petugas@ikmi.ac.id',
        'nomor_induk' => 'PET-001',
        'nama_lengkap' => 'Budi Santoso',
        'role' => 'petugas_sarpras',
        'plain_password' => 'Petugas!IKMI2026'
    ],
    [
        'id' => 3,
        'email' => 'dosen@ikmi.ac.id',
        'nomor_induk' => '0412088501',
        'nama_lengkap' => 'Dr. Dadang Sudrajat, M.Kom.',
        'role' => 'pengguna (dosen)',
        'plain_password' => 'Dadang!IKMI2026'
    ],
    [
        'id' => 4,
        'email' => 'mahasiswa@ikmi.ac.id',
        'nomor_induk' => '4122001',
        'nama_lengkap' => 'Ahmad Fauzi',
        'role' => 'pengguna (mahasiswa)',
        'plain_password' => 'Ahmad!IKMI2026'
    ]
];

$db = new Database();
$allSuccess = true;

foreach ($targetUsers as $target) {
    // 1. Verifikasi keberadaan akun sebelum update
    $db->query("SELECT id, nomor_induk, email, nama_lengkap, status FROM users WHERE id = :id AND email = :email AND nomor_induk = :nomor_induk LIMIT 1");
    $db->bind(':id', $target['id'], PDO::PARAM_INT);
    $db->bind(':email', $target['email']);
    $db->bind(':nomor_induk', $target['nomor_induk']);
    $user = $db->single();

    if (!$user) {
        echo "[ERROR] Akun ID {$target['id']} ({$target['email']}) tidak ditemukan!\n";
        $allSuccess = false;
        continue;
    }

    // 2. Generate Bcrypt Hash
    $hashedPassword = password_hash($target['plain_password'], PASSWORD_BCRYPT);

    // 3. Update kata sandi ke database
    $db->query("UPDATE users SET password = :password, updated_at = CURRENT_TIMESTAMP WHERE id = :id AND email = :email AND nomor_induk = :nomor_induk");
    $db->bind(':password', $hashedPassword);
    $db->bind(':id', $target['id'], PDO::PARAM_INT);
    $db->bind(':email', $target['email']);
    $db->bind(':nomor_induk', $target['nomor_induk']);
    $updated = $db->execute();

    if ($updated) {
        // 4. Verifikasi ulang dari database
        $db->query("SELECT password FROM users WHERE id = :id LIMIT 1");
        $db->bind(':id', $target['id'], PDO::PARAM_INT);
        $check = $db->single();

        if ($check && password_verify($target['plain_password'], $check['password'])) {
            echo "[OK] Akun '{$target['role']}' [ID: {$target['id']} | {$target['email']} | {$target['nomor_induk']}]: Kata sandi awal BERHASIL diperbarui & terverifikasi (Bcrypt match).\n";
        } else {
            echo "[FAIL] Akun '{$target['role']}': Verifikasi kata sandi baru GAGAL!\n";
            $allSuccess = false;
        }
    } else {
        echo "[FAIL] Akun '{$target['role']}': Gagal mengeksekusi update kata sandi!\n";
        $allSuccess = false;
    }
}

echo "\n--------------------------------------------------------------------\n";
if ($allSuccess) {
    echo "STATUS: Seluruh kata sandi awal 4 role berhasil diterapkan 100%!\n";
} else {
    echo "STATUS: Terdapat kendala saat penerapan kata sandi.\n";
}
echo "--------------------------------------------------------------------\n";
