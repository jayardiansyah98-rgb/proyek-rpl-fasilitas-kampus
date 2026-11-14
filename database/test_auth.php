<?php
/**
 * Test Otomatis Authentication & Role-Based Access Control (RBAC)
 * Memverifikasi:
 * 1. Login Admin, Petugas Sarpras, Dosen, dan Mahasiswa
 * 2. Login via Email dan via Nomor Induk (NIM / NIDN / NIP)
 * 3. Penolakan kata sandi salah & email tidak terdaftar
 * 4. Regenerasi sesi dan method Auth::check(), Auth::user(), Auth::role()
 * 5. Proteksi hak akses server-side (RBAC Guard)
 * 6. Logout dan pembersihan sesi
 */

// Inisialisasi sesi sebelum output apapun
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/helpers/helper.php';
require_once __DIR__ . '/../app/helpers/Auth.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/core/Controller.php';
require_once __DIR__ . '/../app/models/UserModel.php';

echo "=== PENGUJIAN OTOMATIS AUTHENTICATION & RBAC (STMIK IKMI CIREBON) ===\n\n";

$userModel = new UserModel();
$passed = 0;
$failed = 0;

function assertTest($condition, $title) {
    global $passed, $failed;
    if ($condition) {
        echo "   [✓] {$title}\n";
        $passed++;
    } else {
        echo "   [✗] FAILED: {$title}\n";
        $failed++;
    }
}

// -------------------------------------------------------------
// 1. UJI VALIDASI KREDENSIAL & PASSWORD HASHING
// -------------------------------------------------------------
echo "1. Pengujian Kredensial Login & Password Hash (Bcrypt):\n";

// A. Admin via Email
$resAdmin = $userModel->validateCredentials('admin@ikmi.ac.id', 'Admin!IKMI2026');
assertTest($resAdmin['success'] && $resAdmin['user']['nama_role'] === 'admin', "Login Admin via Email (admin@ikmi.ac.id) berhasil");

// B. Admin via Nomor Induk
$resAdminNip = $userModel->validateCredentials('ADM-001', 'Admin!IKMI2026');
assertTest($resAdminNip['success'] && $resAdminNip['user']['nama_lengkap'] === 'Rizky Ramadhan', "Login Admin via Nomor Induk (ADM-001) berhasil");

// C. Petugas Sarpras
$resPetugas = $userModel->validateCredentials('petugas@ikmi.ac.id', 'Petugas!IKMI2026');
assertTest($resPetugas['success'] && $resPetugas['user']['nama_role'] === 'petugas_sarpras', "Login Petugas Sarpras (petugas@ikmi.ac.id) berhasil");

// D. Dosen Pengguna
$resDosen = $userModel->validateCredentials('dosen@ikmi.ac.id', 'Dadang!IKMI2026');
assertTest($resDosen['success'] && $resDosen['user']['nama_role'] === 'pengguna' && $resDosen['user']['jenis_pengguna'] === 'dosen', "Login Dosen Pengguna (dosen@ikmi.ac.id) berhasil");

// E. Mahasiswa via NIM
$resMhs = $userModel->validateCredentials('4122001', 'Ahmad!IKMI2026');
assertTest($resMhs['success'] && $resMhs['user']['nama_role'] === 'pengguna' && $resMhs['user']['jenis_pengguna'] === 'mahasiswa', "Login Mahasiswa via NIM (4122001) berhasil");

// F. Kata sandi salah
$resWrongPass = $userModel->validateCredentials('admin@ikmi.ac.id', 'passwordsalah');
assertTest(!$resWrongPass['success'] && strpos($resWrongPass['message'], 'salah') !== false, "Penolakan kata sandi salah berhasil ('" . $resWrongPass['message'] . "')");

// G. Akun tidak terdaftar
$resNotFound = $userModel->validateCredentials('tidakada@ikmi.ac.id', 'Admin!IKMI2026');
assertTest(!$resNotFound['success'] && strpos($resNotFound['message'], 'tidak terdaftar') !== false, "Penolakan akun tidak terdaftar berhasil");

echo "\n";

// -------------------------------------------------------------
// 2. UJI SESI DAN ROLE-BASED ACCESS CONTROL (RBAC) HELPER
// -------------------------------------------------------------
echo "2. Pengujian Sesi & Logika RBAC (Server-Side):\n";

// Pastikan sebelum login Auth::check() false
Auth::logout();
assertTest(!Auth::check(), "Auth::check() bernilai false saat belum login");

// A. Login sebagai Admin
Auth::login($resAdmin['user']);
assertTest(Auth::check(), "Auth::check() bernilai true setelah Auth::login()");
assertTest(Auth::role() === 'admin', "Auth::role() mengembalikan 'admin'");
assertTest(Auth::hasRole('admin'), "Admin memiliki hak role 'admin'");
assertTest(Auth::hasRole(['admin', 'petugas_sarpras']), "Admin cocok dengan array role ['admin', 'petugas_sarpras']");
assertTest(!Auth::hasRole('pengguna'), "Admin bukan role 'pengguna'");

// Simulasi Otorisasi Admin
$canAdminAccessUserModule = Auth::hasRole('admin');
$canAdminAccessMaintenance = Auth::hasRole(['admin', 'petugas_sarpras']);
assertTest($canAdminAccessUserModule, "Admin DIIZINKAN mengakses modul Manajemen User");
assertTest($canAdminAccessMaintenance, "Admin DIIZINKAN mengakses modul Pemeliharaan");

// B. Login sebagai Petugas Sarpras
Auth::logout();
Auth::login($resPetugas['user']);
assertTest(Auth::role() === 'petugas_sarpras', "Sesi berganti ke Petugas Sarpras");
assertTest(!Auth::hasRole('admin'), "Petugas Sarpras DITOLAK dari role 'admin'");
assertTest(Auth::hasRole(['admin', 'petugas_sarpras']), "Petugas Sarpras diizinkan role maintenance");

// Simulasi Otorisasi Petugas Sarpras
$canPetugasAccessUserModule = Auth::hasRole('admin');
$canPetugasAccessMaintenance = Auth::hasRole(['admin', 'petugas_sarpras']);
assertTest(!$canPetugasAccessUserModule, "Petugas Sarpras DITOLAK (403) dari modul Manajemen User");
assertTest($canPetugasAccessMaintenance, "Petugas Sarpras DIIZINKAN mengakses modul Pemeliharaan");

// C. Login sebagai Mahasiswa / Dosen (Pengguna)
Auth::logout();
Auth::login($resMhs['user']);
assertTest(Auth::role() === 'pengguna', "Sesi berganti ke Mahasiswa (role: 'pengguna')");
assertTest(!Auth::hasRole('admin'), "Pengguna DITOLAK dari role 'admin'");
assertTest(!Auth::hasRole('petugas_sarpras'), "Pengguna DITOLAK dari role 'petugas_sarpras'");

// Simulasi Otorisasi Pengguna
$canPenggunaAccessUserModule = Auth::hasRole('admin');
$canPenggunaAccessMaintenance = Auth::hasRole(['admin', 'petugas_sarpras']);
$canPenggunaAccessRooms = Auth::check(); // Semua yang login boleh lihat ruangan
assertTest(!$canPenggunaAccessUserModule, "Pengguna DITOLAK (403) dari modul Manajemen User");
assertTest(!$canPenggunaAccessMaintenance, "Pengguna DITOLAK (403) dari modul Pemeliharaan");
assertTest($canPenggunaAccessRooms, "Pengguna DIIZINKAN mengakses modul Daftar Ruangan");

// D. Logout
Auth::logout();
assertTest(!Auth::check(), "Logout berhasil dan sesi telah dibersihkan");

echo "\n";

// -------------------------------------------------------------
// 3. RINGKASAN HASIL
// -------------------------------------------------------------
echo "=== HASIL PENGUJIAN: {$passed} BERHASIL, {$failed} GAGAL ===\n";

if ($failed > 0) {
    exit(1);
} else {
    echo "Seluruh skenario Authentication & Role-Based Access Control berhasil 100%!\n";
    exit(0);
}
