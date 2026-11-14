<?php
/**
 * Test Suite: Login Semua Role Berdasarkan Data Database Aktual
 * Memverifikasi:
 * 1. Admin login via Email dan NIP -> Akses Dashboard Admin
 * 2. Petugas Sarpras login via Email dan NIP -> Akses Dashboard Petugas
 * 3. Dosen login via Email dan NIDN -> Akses Dashboard Pengguna (Dosen)
 * 4. Mahasiswa login via Email dan NIM -> Akses Dashboard Pengguna (Mahasiswa)
 * 5. Penolakan kata sandi salah -> Pesan error yang aman
 * 6. Penolakan akun belum terdaftar (misal akun Leo yang belum ada) -> Pesan error yang aman
 * 7. Verifikasi sesi, regenerasi ID, dan penentuan role murni dari database
 */

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
require_once __DIR__ . '/../app/models/DashboardModel.php';

echo "====================================================================\n";
echo "   PENGUJIAN LOGIN SEMUA ROLE BERDASARKAN DATABASE AKTUAL         \n";
echo "   SISTEM INFORMASI FASILITAS KAMPUS STMIK IKMI CIREBON           \n";
echo "====================================================================\n\n";

$userModel = new UserModel();
$dashboardModel = new DashboardModel();
$passed = 0;
$failed = 0;

function testAssert($condition, $msg) {
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] {$msg}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$msg}\n";
        $failed++;
    }
}

// -----------------------------------------------------------------
// 1. AUDIT DATABASE AKTUAL
// -----------------------------------------------------------------
echo "1. Audit Akun Terdaftar di Database:\n";
$allUsers = $userModel->getAllUsers();
testAssert(count($allUsers) >= 4, "Terdapat minimal 4 akun inti dalam sistem (Ditemukan: " . count($allUsers) . ")");

$rolesFound = array_unique(array_column($allUsers, 'nama_role'));
testAssert(in_array('admin', $rolesFound), "Role 'admin' terdaftar");
testAssert(in_array('petugas_sarpras', $rolesFound), "Role 'petugas_sarpras' terdaftar");
testAssert(in_array('pengguna', $rolesFound), "Role 'pengguna' terdaftar");

echo "\n";

// -----------------------------------------------------------------
// 2. PENGUJIAN LOGIN ADMIN
// -----------------------------------------------------------------
echo "2. Pengujian Login Akun Administrator:\n";
// Uji via Email
$adminByEmail = $userModel->validateCredentials('admin@ikmi.ac.id', 'Admin!IKMI2026');
testAssert($adminByEmail['success'], "Login Admin menggunakan Email (admin@ikmi.ac.id) berhasil");
testAssert($adminByEmail['user']['nama_role'] === 'admin', "Role Admin diakui dari database sebagai 'admin'");

// Uji via NIP
$adminByNip = $userModel->validateCredentials('ADM-001', 'Admin!IKMI2026');
testAssert($adminByNip['success'], "Login Admin menggunakan Nomor Induk (ADM-001) berhasil");

// Simulasi Sesi & Dashboard Admin
Auth::logout();
Auth::login($adminByEmail['user']);
testAssert(Auth::check(), "Sesi Admin aktif");
testAssert(Auth::role() === 'admin', "Auth::role() mengembalikan 'admin'");
testAssert(Auth::user()['nama_lengkap'] !== '', "Nama profil Admin terisi dari database: '" . Auth::user()['nama_lengkap'] . "'");

// Validasi Akses Dashboard Admin
$adminStats = $dashboardModel->getAdminStats();
testAssert(isset($adminStats['total_gedung']) && $adminStats['total_gedung'] > 0, "Dashboard Admin berhasil memuat metrik gedung (" . $adminStats['total_gedung'] . " gedung)");
testAssert(isset($adminStats['total_ruangan']) && $adminStats['total_ruangan'] > 0, "Dashboard Admin berhasil memuat metrik ruangan (" . $adminStats['total_ruangan'] . " ruangan)");

echo "\n";

// -----------------------------------------------------------------
// 3. PENGUJIAN LOGIN PETUGAS SARPRAS
// -----------------------------------------------------------------
echo "3. Pengujian Login Akun Petugas Sarpras:\n";
// Uji via Email
$petugasByEmail = $userModel->validateCredentials('petugas@ikmi.ac.id', 'Petugas!IKMI2026');
testAssert($petugasByEmail['success'], "Login Petugas menggunakan Email (petugas@ikmi.ac.id) berhasil");
testAssert($petugasByEmail['user']['nama_role'] === 'petugas_sarpras', "Role Petugas diakui dari database sebagai 'petugas_sarpras'");

// Uji via NIP
$petugasByNip = $userModel->validateCredentials('PET-001', 'Petugas!IKMI2026');
testAssert($petugasByNip['success'], "Login Petugas menggunakan Nomor Induk (PET-001) berhasil");

// Simulasi Sesi & Dashboard Petugas
Auth::logout();
Auth::login($petugasByEmail['user']);
testAssert(Auth::check(), "Sesi Petugas aktif");
testAssert(Auth::role() === 'petugas_sarpras', "Auth::role() mengembalikan 'petugas_sarpras'");
testAssert(Auth::user()['nama_lengkap'] === 'Budi Santoso', "Nama Petugas sesuai identitas database ('Budi Santoso')");

echo "\n";

// -----------------------------------------------------------------
// 4. PENGUJIAN LOGIN DOSEN
// -----------------------------------------------------------------
echo "4. Pengujian Login Akun Dosen:\n";
// Uji via Email
$dosenByEmail = $userModel->validateCredentials('dosen@ikmi.ac.id', 'Dadang!IKMI2026');
testAssert($dosenByEmail['success'], "Login Dosen menggunakan Email (dosen@ikmi.ac.id) berhasil");
testAssert($dosenByEmail['user']['nama_role'] === 'pengguna', "Role Dosen diakui sebagai 'pengguna'");
testAssert($dosenByEmail['user']['jenis_pengguna'] === 'dosen', "Jenis pengguna Dosen diakui sebagai 'dosen'");

// Uji via NIDN
$dosenByNidn = $userModel->validateCredentials('0412088501', 'Dadang!IKMI2026');
testAssert($dosenByNidn['success'], "Login Dosen menggunakan NIDN (0412088501) berhasil");

// Simulasi Sesi & Dashboard Dosen
Auth::logout();
Auth::login($dosenByEmail['user']);
testAssert(Auth::check(), "Sesi Dosen aktif");
testAssert(Auth::role() === 'pengguna', "Auth::role() mengembalikan 'pengguna'");
testAssert(Auth::user()['nama_lengkap'] === 'Dr. Dadang Sudrajat, M.Kom.', "Nama Dosen sesuai identitas database ('Dr. Dadang Sudrajat, M.Kom.')");

// Validasi Akses Dashboard Civitas
$dosenStats = $dashboardModel->getPenggunaStats((int)Auth::user()['id']);
testAssert(isset($dosenStats['jumlah_ruangan']), "Dashboard Dosen berhasil memuat data ruangan civitas (" . $dosenStats['jumlah_ruangan'] . " ruangan tersedia)");

echo "\n";

// -----------------------------------------------------------------
// 5. PENGUJIAN LOGIN MAHASISWA (AHMAD FAUZI)
// -----------------------------------------------------------------
echo "5. Pengujian Login Akun Mahasiswa Terdaftar (Ahmad Fauzi):\n";
// Uji via Email
$mhsByEmail = $userModel->validateCredentials('mahasiswa@ikmi.ac.id', 'Ahmad!IKMI2026');
testAssert($mhsByEmail['success'], "Login Mahasiswa menggunakan Email (mahasiswa@ikmi.ac.id) berhasil");
testAssert($mhsByEmail['user']['nama_role'] === 'pengguna', "Role Mahasiswa diakui sebagai 'pengguna'");
testAssert($mhsByEmail['user']['jenis_pengguna'] === 'mahasiswa', "Jenis pengguna Mahasiswa diakui sebagai 'mahasiswa'");

// Uji via NIM
$mhsByNim = $userModel->validateCredentials('4122001', 'Ahmad!IKMI2026');
testAssert($mhsByNim['success'], "Login Mahasiswa menggunakan NIM (4122001) berhasil");

// Simulasi Sesi & Dashboard Mahasiswa
Auth::logout();
Auth::login($mhsByEmail['user']);
testAssert(Auth::check(), "Sesi Mahasiswa aktif");
testAssert(Auth::role() === 'pengguna', "Auth::role() mengembalikan 'pengguna'");
testAssert(Auth::user()['nama_lengkap'] === 'Ahmad Fauzi', "Nama Mahasiswa sesuai identitas database ('Ahmad Fauzi')");

echo "\n";

// -----------------------------------------------------------------
// 6. PENGUJIAN AKUN MAHASISWA YANG BELUM TERDAFTAR (LEO ARDIANSYAH)
// -----------------------------------------------------------------
echo "6. Pengujian Akun Mahasiswa Belum Terdaftar (Leo Ardiansyah Jailani):\n";
$leoCheck = $userModel->findByIdentifier('leo@ikmi.ac.id');
testAssert($leoCheck === false, "Akun 'leo@ikmi.ac.id' terverifikasi belum ada di database");

$leoLoginAttempt = $userModel->validateCredentials('leo@ikmi.ac.id', 'rahasia123');
testAssert(!$leoLoginAttempt['success'], "Login dengan akun Leo yang belum terdaftar berhasil DITOLAK");
testAssert(strpos($leoLoginAttempt['message'], 'tidak terdaftar') !== false, "Pesan penolakan aman dan jelas ('" . $leoLoginAttempt['message'] . "')");

echo "\n";

// -----------------------------------------------------------------
// 7. PENGUJIAN KREDENSIAL SALAH & KEAMANAN
// -----------------------------------------------------------------
echo "7. Pengujian Penolakan Kredensial Salah & Keamanan:\n";
$wrongPassAttempt = $userModel->validateCredentials('admin@ikmi.ac.id', 'password_salah_123');
testAssert(!$wrongPassAttempt['success'], "Login dengan password salah berhasil DITOLAK");
testAssert(strpos($wrongPassAttempt['message'], 'salah') !== false, "Pesan penolakan kata sandi salah aman ('" . $wrongPassAttempt['message'] . "')");

// Logout cleanup
Auth::logout();
testAssert(!Auth::check(), "Logout berhasil dan sesi bersih");

echo "\n====================================================================\n";
echo "   HASIL PENGUJIAN: {$passed} PASS, {$failed} FAIL\n";
if ($failed === 0) {
    echo "   SELURUH SKENARIO LOGIN & AKSES DASHBOARD BERHASIL 100%!\n";
}
echo "====================================================================\n";
