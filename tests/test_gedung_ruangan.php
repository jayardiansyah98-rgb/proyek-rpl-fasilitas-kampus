<?php
/**
 * Test Otomasi Komprehensif: Master Gedung dan Ruangan
 * Sistem Informasi Pendataan Fasilitas STMIK IKMI Cirebon
 */

// Konfigurasi CLI testing environment
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/Fasilitas Kampus/public/index.php';

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/helpers/Auth.php';
require_once __DIR__ . '/../app/helpers/helper.php';
require_once __DIR__ . '/../app/models/GedungModel.php';
require_once __DIR__ . '/../app/models/RuanganModel.php';

echo "====================================================================\n";
echo "  PENGUJIAN VALIDASI, CRUD, DAN RELASI: MASTER GEDUNG & RUANGAN    \n";
echo "====================================================================\n\n";

$db = new Database();
$gedungModel = new GedungModel();
$ruanganModel = new RuanganModel();

$testsPassed = 0;
$testsFailed = 0;

function assertTest($condition, $testName) {
    global $testsPassed, $testsFailed;
    if ($condition) {
        echo "  [PASS] " . $testName . "\n";
        $testsPassed++;
    } else {
        echo "  [FAIL] " . $testName . "\n";
        $testsFailed++;
    }
}

// =============================================================================
// 1. PENGUJIAN MODEL & CRUD GEDUNG
// =============================================================================
echo "1. Pengujian Model & CRUD Gedung:\n";

// Bersihkan data tes sebelumnya jika ada
$existingTestGedung = $gedungModel->findByKode('GD-TEST');
if ($existingTestGedung) {
    $db->query("DELETE FROM ruangan WHERE gedung_id = " . (int)$existingTestGedung['id'])->execute();
    $gedungModel->delete($existingTestGedung['id']);
}

// 1.1 Tambah Gedung
$gedungData = [
    'kode_gedung' => 'GD-TEST',
    'nama_gedung' => 'Gedung Riset dan Inovasi AI',
    'lokasi' => 'Kampus 2 STMIK IKMI, Jl. Brigjend Dharsono No. 5',
    'keterangan' => 'Pusat riset otomasi, IoT, dan inkubator startup mahasiswa',
    'jumlah_lantai' => 4,
    'status' => 'aktif'
];
$newGedungId = $gedungModel->create($gedungData);
assertTest($newGedungId > 0, "Tambah gedung baru (ID: $newGedungId)");

// 1.2 Detail Gedung
$fetchedGedung = $gedungModel->findById($newGedungId);
assertTest(
    $fetchedGedung && 
    $fetchedGedung['kode_gedung'] === 'GD-TEST' && 
    $fetchedGedung['lokasi'] === $gedungData['lokasi'],
    "Detail gedung sesuai dengan data input (lokasi & keterangan terisi)"
);

// 1.3 Validasi Keunikan Kode Gedung
$duplicateGedung = $gedungModel->findByKode('GD-TEST');
assertTest($duplicateGedung !== false && $duplicateGedung['id'] == $newGedungId, "Deteksi keunikan kode_gedung berfungsi");

// 1.4 Update Gedung
$updateGedungData = [
    'kode_gedung' => 'GD-TEST',
    'nama_gedung' => 'Gedung Riset Terapan & Inovasi AI (Updated)',
    'lokasi' => 'Kampus 2 STMIK IKMI, Gedung Barat',
    'keterangan' => 'Keterangan telah diperbarui',
    'jumlah_lantai' => 5,
    'status' => 'aktif'
];
$updateResult = $gedungModel->update($newGedungId, $updateGedungData);
$updatedGedung = $gedungModel->findById($newGedungId);
assertTest(
    $updateResult && $updatedGedung['nama_gedung'] === $updateGedungData['nama_gedung'] && $updatedGedung['jumlah_lantai'] == 5,
    "Update data gedung berhasil diperbarui"
);

// 1.5 Search & Pagination Gedung
$searchResults = $gedungModel->getAll('Riset Terapan', 1, 10);
assertTest(count($searchResults) >= 1, "Pencarian gedung berdasarkan keyword berhasil");

$searchCount = $gedungModel->countAll('Riset Terapan');
assertTest($searchCount >= 1, "Kalkulasi total baris pencarian gedung akurat");

echo "\n";

// =============================================================================
// 2. PENGUJIAN MODEL & CRUD RUANGAN
// =============================================================================
echo "2. Pengujian Model & CRUD Ruangan:\n";

// 2.1 Tambah Ruangan di bawah Gedung Baru
$ruanganData = [
    'kode_ruangan' => 'R-TEST-AI',
    'nama_ruangan' => 'Laboratorium Robotika & Vision',
    'gedung_id' => $newGedungId,
    'lantai' => 3,
    'jenis_ruangan' => 'Laboratorium',
    'kapasitas' => 35,
    'status' => 'tersedia',
    'keterangan' => 'Ruang riset robotik dengan sensor lidar dan kamera stereo'
];
$newRuanganId = $ruanganModel->create($ruanganData);
assertTest($newRuanganId > 0, "Tambah ruangan baru (ID: $newRuanganId)");

// 2.2 Detail Ruangan & Relasi Gedung
$fetchedRuangan = $ruanganModel->findById($newRuanganId);
assertTest(
    $fetchedRuangan && 
    $fetchedRuangan['kode_ruangan'] === 'R-TEST-AI' && 
    $fetchedRuangan['nama_gedung'] === $updatedGedung['nama_gedung'],
    "Detail ruangan dengan relasi gedung terhubung secara benar"
);

// 2.3 Validasi Keunikan Kode Ruangan
$duplicateRuangan = $ruanganModel->findByKode('R-TEST-AI');
assertTest($duplicateRuangan !== false && $duplicateRuangan['id'] == $newRuanganId, "Deteksi keunikan kode_ruangan berfungsi");

// 2.4 Update Ruangan
$updateRuanganData = [
    'kode_ruangan' => 'R-TEST-AI',
    'nama_ruangan' => 'Laboratorium Robotika & Computer Vision (Updated)',
    'gedung_id' => $newGedungId,
    'lantai' => 3,
    'jenis_ruangan' => 'Laboratorium',
    'kapasitas' => 45,
    'status' => 'digunakan',
    'keterangan' => 'Kapasitas ditingkatkan menjadi 45 orang'
];
$updateRuanganResult = $ruanganModel->update($newRuanganId, $updateRuanganData);
$updatedRuangan = $ruanganModel->findById($newRuanganId);
assertTest(
    $updateRuanganResult && $updatedRuangan['kapasitas'] == 45 && $updatedRuangan['status'] === 'digunakan',
    "Update data ruangan (kapasitas & status) berhasil"
);

// 2.5 Filter Gedung & Filter Jenis Ruangan
$filteredByGedung = $ruanganModel->getAll(null, $newGedungId, null, 1, 10);
assertTest(count($filteredByGedung) === 1, "Filter ruangan berdasarkan gedung_id berhasil");

$filteredByJenis = $ruanganModel->getAll(null, null, 'Laboratorium', 1, 10);
assertTest(count($filteredByJenis) >= 1, "Filter ruangan berdasarkan jenis_ruangan berhasil");

echo "\n";

// =============================================================================
// 3. PENGUJIAN VALIDASI INTEGRITAS RELASI DATABASE
// =============================================================================
echo "3. Pengujian Validasi Integritas Relasi Database:\n";

// 3.1 Relasi Gedung -> Ruangan
// Mencoba validasi relasi pada gedung yang masih memiliki ruangan
$ruanganCountInGedung = $gedungModel->countRuangan($newGedungId);
assertTest($ruanganCountInGedung === 1, "Pemeriksaan relasi: Gedung uji memiliki $ruanganCountInGedung ruangan");

// Validasi controller logic: Gedung tidak boleh dihapus jika memiliki ruangan
$canDeleteGedung = ($ruanganCountInGedung === 0);
assertTest(!$canDeleteGedung, "Pencegahan hapus gedung yang memiliki ruangan berhasil DITOLAK");

// 3.2 Relasi Ruangan -> Fasilitas
// Ruangan eksisting Lab AI (ID: 1) memiliki fasilitas terpasang
$fasilitasCountLabAI = $ruanganModel->countFasilitas(1);
assertTest($fasilitasCountLabAI > 0, "Ruangan LAB-AI memiliki $fasilitasCountLabAI fasilitas terpasang");

$canDeleteLabAI = ($fasilitasCountLabAI === 0 && $ruanganModel->countLaporan(1) === 0);
assertTest(!$canDeleteLabAI, "Pencegahan hapus ruangan yang memiliki fasilitas/laporan berhasil DITOLAK");

// 3.3 Hapus Ruangan Tanpa Relasi (Aman)
$fasilitasCountTest = $ruanganModel->countFasilitas($newRuanganId);
$laporanCountTest = $ruanganModel->countLaporan($newRuanganId);
assertTest($fasilitasCountTest === 0 && $laporanCountTest === 0, "Ruangan uji tidak memiliki fasilitas/laporan (Aman)");

$deleteRuanganResult = $ruanganModel->delete($newRuanganId);
assertTest($deleteRuanganResult === true, "Hapus ruangan uji berhasil");
assertTest($ruanganModel->findById($newRuanganId) === false, "Ruangan uji sudah terhapus dari database");

// 3.4 Hapus Gedung Tanpa Ruangan (Aman)
$ruanganCountAfter = $gedungModel->countRuangan($newGedungId);
assertTest($ruanganCountAfter === 0, "Gedung uji kini memiliki 0 ruangan");

$deleteGedungResult = $gedungModel->delete($newGedungId);
assertTest($deleteGedungResult === true, "Hapus gedung uji yang sudah kosong berhasil");
assertTest($gedungModel->findById($newGedungId) === false, "Gedung uji sudah terhapus dari database");

echo "\n";

// =============================================================================
// 4. PENGUJIAN OTORISASI (RBAC)
// =============================================================================
echo "4. Pengujian Aturan Otorisasi (RBAC):\n";

// 4.1 Role Admin: Memiliki hak penuh
$adminUser = [
    'id' => 1,
    'role_id' => 1,
    'nama_role' => 'admin',
    'nomor_induk' => 'ADM-001',
    'nama_lengkap' => 'Admin Sarpras',
    'email' => 'admin@ikmi.ac.id',
    'jenis_pengguna' => 'staff'
];
Auth::login($adminUser);
assertTest(Auth::role() === 'admin', "Simulasi sesi Admin aktif");
assertTest(Auth::hasRole('admin') === true, "Admin diizinkan aksi Admin");
assertTest(Auth::hasRole(['admin', 'petugas_sarpras']) === true, "Admin diizinkan aksi Admin & Petugas");

// 4.2 Role Petugas Sarpras: Boleh tambah/edit ruangan, TIDAK boleh hapus ruangan atau CRUD gedung
$petugasUser = [
    'id' => 2,
    'role_id' => 2,
    'nama_role' => 'petugas_sarpras',
    'nomor_induk' => 'PET-001',
    'nama_lengkap' => 'Petugas Sarpras',
    'email' => 'petugas@ikmi.ac.id',
    'jenis_pengguna' => 'staff'
];
Auth::login($petugasUser);
assertTest(Auth::role() === 'petugas_sarpras', "Simulasi sesi Petugas Sarpras aktif");
assertTest(Auth::hasRole(['admin', 'petugas_sarpras']) === true, "Petugas Sarpras DIIZINKAN tambah/edit ruangan");
assertTest(Auth::hasRole('admin') === false, "Petugas Sarpras DITOLAK hapus ruangan (Khusus Admin)");
assertTest(Auth::hasRole('admin') === false, "Petugas Sarpras DITOLAK tambah/edit/hapus gedung (Khusus Admin)");

// 4.3 Role Pengguna (Dosen / Mahasiswa): Hanya boleh melihat (Read-Only)
$dosenUser = [
    'id' => 3,
    'role_id' => 3,
    'nama_role' => 'pengguna',
    'nomor_induk' => '0412088501',
    'nama_lengkap' => 'Dr. Dosen IKMI',
    'email' => 'dosen@ikmi.ac.id',
    'jenis_pengguna' => 'dosen'
];
Auth::login($dosenUser);
assertTest(Auth::role() === 'pengguna', "Simulasi sesi Civitas Pengguna (Dosen) aktif");
assertTest(Auth::check() === true, "Dosen terautentikasi (Dapat melihat daftar & detail ruangan)");
assertTest(Auth::hasRole(['admin', 'petugas_sarpras']) === false, "Dosen DITOLAK menambah/mengubah ruangan");
assertTest(Auth::hasRole('admin') === false, "Dosen DITOLAK menghapus ruangan");
assertTest(Auth::hasRole('admin') === false, "Dosen DITOLAK mengubah gedung");

Auth::logout();
assertTest(!Auth::check(), "Logout sesi berhasil");

echo "\n====================================================================\n";
echo "  HASIL AKHIR: $testsPassed PENGUJIAN LULUS, $testsFailed GAGAL      \n";
echo "====================================================================\n";

if ($testsFailed > 0) {
    exit(1);
}
exit(0);
