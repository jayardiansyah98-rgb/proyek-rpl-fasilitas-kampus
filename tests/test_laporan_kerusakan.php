<?php
/**
 * Test Otomasi Komprehensif: Modul Pelaporan Kerusakan Fasilitas
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
require_once __DIR__ . '/../app/models/LaporanModel.php';
require_once __DIR__ . '/../app/models/RuanganModel.php';
require_once __DIR__ . '/../app/models/FasilitasModel.php';

echo "====================================================================\n";
echo "  PENGUJIAN VALIDASI, RBAC, CRUD & ALUR STATUS: PELAPORAN KERUSAKAN \n";
echo "====================================================================\n\n";

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

try {
    $db = new Database();
    $laporanModel = new LaporanModel();

    // =============================================================================
    // 1. PENGUJIAN GENERATE KODE LAPORAN
    // =============================================================================
    echo "1. Pengujian Generate Kode Tiket Laporan:\n";
    $kode = $laporanModel->generateKodeLaporan();
    $prefix = 'LAP-' . date('Ym') . '-';
    assertTest(strpos($kode, $prefix) === 0, "Kode laporan diawali prefix '{$prefix}' (Hasil: {$kode})");
    assertTest(strlen($kode) === strlen($prefix) + 3, "Kode laporan memiliki panjang konsisten");
    echo "\n";

    // =============================================================================
    // 2. PENGUJIAN PEMBUATAN LAPORAN BARU (DOSEN / MAHASISWA)
    // =============================================================================
    echo "2. Pengujian Pembuatan Laporan Kerusakan Baru (Pelapor: User ID 3):\n";

    // Cari fasilitas valid di DB untuk testing
    $sampleFasilitas = $db->query("SELECT f.id, f.ruangan_id FROM fasilitas f JOIN ruangan r ON f.ruangan_id = r.id LIMIT 1")->single();
    if (!$sampleFasilitas) {
        throw new Exception("Data master fasilitas tidak ditemukan untuk pengujian.");
    }

    $testReportData = [
        'kode_laporan' => $kode,
        'user_id' => 3, // Dosen Dr. Dadang Sudrajat
        'fasilitas_id' => $sampleFasilitas['id'],
        'ruangan_id' => $sampleFasilitas['ruangan_id'],
        'deskripsi' => 'Pengujian otomatis sistem: Kabel konektor proyektor longgar dan gambar berkedip saat presentasi.',
        'foto' => null,
        'prioritas' => 'tinggi',
        'status' => 'menunggu'
    ];

    $newId = $laporanModel->create($testReportData);
    assertTest($newId > 0, "Laporan baru berhasil disimpan dengan ID auto increment: {$newId}");

    $fetched = $laporanModel->findById($newId);
    assertTest($fetched && $fetched['kode_laporan'] === $kode, "Data laporan dapat diambil kembali dengan kode '{$kode}'");
    assertTest($fetched['status'] === 'menunggu', "Status awal laporan adalah 'menunggu'");
    assertTest($fetched['prioritas'] === 'tinggi', "Prioritas laporan tersimpan sebagai 'tinggi'");
    assertTest(!empty($fetched['pelapor']), "Relasi JOIN tabel users berhasil (Pelapor: {$fetched['pelapor']})");
    assertTest(!empty($fetched['nama_fasilitas']), "Relasi JOIN tabel fasilitas berhasil ({$fetched['nama_fasilitas']})");
    assertTest(!empty($fetched['nama_ruangan']), "Relasi JOIN tabel ruangan berhasil ({$fetched['nama_ruangan']})");
    echo "\n";

    // =============================================================================
    // 3. PENGUJIAN SECURITY & DATA SCOPING (PENGGUNA HANYA DAPAT MELIHAT LAPORANNYA)
    // =============================================================================
    echo "3. Pengujian Keamanan & Data Scoping Hak Akses:\n";

    // 3.1 Pelapor (User ID 3) mengakses laporannya sendiri
    $accessOwn = $laporanModel->findById($newId, 3);
    assertTest(!empty($accessOwn), "Pelapor (User ID 3) berhak mengakses detail laporannya sendiri");

    // 3.2 Pengguna lain (User ID 4) mencoba mengakses laporan milik User ID 3
    $accessOther = $laporanModel->findById($newId, 4);
    assertTest(empty($accessOther), "Pengguna lain (User ID 4) DITOLAK saat mengakses laporan milik User ID 3 (Data Scoping)");

    // 3.3 Petugas Sarpras / Admin (User ID null) dapat mengakses seluruh laporan
    $accessAdmin = $laporanModel->findById($newId, null);
    assertTest(!empty($accessAdmin), "Petugas Sarpras / Admin (userId = null) dapat mengakses seluruh tiket laporan");
    echo "\n";

    // =============================================================================
    // 4. PENGUJIAN ALUR STATUS NORMAL: MENUNGGU -> DIVERIFIKASI -> DIPROSES -> SELESAI
    // =============================================================================
    echo "4. Pengujian Alur Status Normal (Petugas Sarpras):\n";

    // 4.1 Verifikasi oleh Petugas Sarpras
    $res1 = $laporanModel->updateStatus($newId, 'diverifikasi', 'tinggi', 'Telah dicek oleh teknisi Sarpras. Masuk jadwal penanganan.');
    assertTest($res1 === true, "Petugas berhasil memverifikasi laporan ('diverifikasi')");
    $check1 = $laporanModel->findById($newId);
    assertTest($check1['status'] === 'diverifikasi', "Status tiket terupdate menjadi 'diverifikasi'");
    assertTest(!empty($check1['catatan_petugas']), "Catatan petugas tersimpan");

    // 4.2 Tindak Lanjut: Diproses
    $res2 = $laporanModel->updateStatus($newId, 'diproses', 'tinggi', 'Sedang dilakukan penggantian konektor kabel HDMI.');
    assertTest($res2 === true, "Status berhasil diubah menjadi 'diproses'");
    $check2 = $laporanModel->findById($newId);
    assertTest($check2['status'] === 'diproses', "Status tiket terupdate menjadi 'diproses'");

    // 4.3 Penyelesaian: Selesai
    $res3 = $laporanModel->updateStatus($newId, 'selesai', 'sedang', 'Kabel dan konektor berhasil diganti, tampilan display proyektor normal.');
    assertTest($res3 === true, "Status berhasil dituntaskan menjadi 'selesai'");
    $check3 = $laporanModel->findById($newId);
    assertTest($check3['status'] === 'selesai', "Status tiket terupdate menjadi 'selesai'");
    echo "\n";

    // =============================================================================
    // 5. PENGUJIAN ALUR STATUS ALTERNATIF: MENUNGGU -> DITOLAK
    // =============================================================================
    echo "5. Pengujian Alur Status Alternatif (Penolakan Laporan):\n";

    $kodeTolak = $laporanModel->generateKodeLaporan();
    $idTolak = $laporanModel->create([
        'kode_laporan' => $kodeTolak,
        'user_id' => 4, // Mahasiswa Ahmad Fauzi
        'fasilitas_id' => $sampleFasilitas['id'],
        'ruangan_id' => $sampleFasilitas['ruangan_id'],
        'deskripsi' => 'Laporan uji coba penolakan: Kerusakan duplikat yang sudah pernah dilaporkan sebelumnya.',
        'foto' => null,
        'prioritas' => 'rendah',
        'status' => 'menunggu'
    ]);

    assertTest($idTolak > 0, "Laporan kedua untuk uji penolakan berhasil dibuat");
    $resTolak = $laporanModel->updateStatus($idTolak, 'ditolak', 'rendah', 'Laporan ditolak: Kerusakan fasilitas ini sudah terdaftar pada tiket sebelumnya.');
    assertTest($resTolak === true, "Status berhasil diubah menjadi 'ditolak'");
    $checkTolak = $laporanModel->findById($idTolak);
    assertTest($checkTolak['status'] === 'ditolak', "Status tiket terkonfirmasi 'ditolak'");
    assertTest(strpos($checkTolak['catatan_petugas'], 'Laporan ditolak') !== false, "Catatan alasan penolakan tersimpan dengan tepat");
    echo "\n";

    // =============================================================================
    // 6. PENGUJIAN FILTER & PENCARIAN
    // =============================================================================
    echo "6. Pengujian Filter Status, Prioritas, dan Pencarian:\n";

    // 6.1 Filter Status 'selesai'
    $listSelesai = $laporanModel->getAll(null, 'selesai');
    $allSelesai = true;
    foreach ($listSelesai as $item) {
        if ($item['status'] !== 'selesai') { $allSelesai = false; break; }
    }
    assertTest($allSelesai && count($listSelesai) > 0, "Filter status 'selesai' berfungsi dengan benar");

    // 6.2 Filter Status 'ditolak'
    $listDitolak = $laporanModel->getAll(null, 'ditolak');
    $allDitolak = true;
    foreach ($listDitolak as $item) {
        if ($item['status'] !== 'ditolak') { $allDitolak = false; break; }
    }
    assertTest($allDitolak && count($listDitolak) > 0, "Filter status 'ditolak' berfungsi dengan benar");

    // 6.3 Pencarian kata kunci kode tiket
    $searchResult = $laporanModel->getAll(null, null, null, $kode);
    assertTest(count($searchResult) >= 1 && $searchResult[0]['kode_laporan'] === $kode, "Pencarian berdasarkan kode tiket '{$kode}' berhasil");

    // 6.4 Agregasi status summary
    $summary = $laporanModel->getStatusSummary();
    assertTest(isset($summary['total']) && isset($summary['selesai']) && isset($summary['ditolak']), "Ringkasan status menghasilkan counter lengkap");
    assertTest($summary['total'] > 0, "Total counter laporan > 0 (Total: {$summary['total']})");
    echo "\n";

    // =============================================================================
    // 7. PENGUJIAN HELPER RUANGAN & FASILITAS BERANTAI
    // =============================================================================
    echo "7. Pengujian Helper Dropdown Berantai Ruangan & Fasilitas:\n";

    $ruanganList = $laporanModel->getRuanganList();
    assertTest(is_array($ruanganList) && count($ruanganList) > 0, "getRuanganList() menghasilkan daftar ruangan aktif");

    $fasilitasRuangan = $laporanModel->getFasilitasByRuangan($sampleFasilitas['ruangan_id']);
    assertTest(is_array($fasilitasRuangan) && count($fasilitasRuangan) > 0, "getFasilitasByRuangan() menghasilkan daftar inventaris sesuai ruangan terpilih");
    echo "\n";

    // =============================================================================
    // 8. PENGUJIAN PENGHAPUSAN (ADMIN ONLY) & PEMBERSIHAN DATA UJI
    // =============================================================================
    echo "8. Pengujian Penghapusan Tiket (Admin Only) & Cleanup:\n";

    $del1 = $laporanModel->delete($newId);
    assertTest($del1 === true, "Admin berhasil menghapus tiket pengujian 1 (#{$kode})");
    $del2 = $laporanModel->delete($idTolak);
    assertTest($del2 === true, "Admin berhasil menghapus tiket pengujian 2 (#{$kodeTolak})");

    assertTest($laporanModel->findById($newId) === false, "Verifikasi tiket 1 sudah tidak ada di database");
    assertTest($laporanModel->findById($idTolak) === false, "Verifikasi tiket 2 sudah tidak ada di database");
    echo "\n";

} catch (Exception $e) {
    echo "\n[EXCEPTION DI TEST]: " . $e->getMessage() . "\n";
    $testsFailed++;
}

echo "====================================================================\n";
echo "  HASIL AKHIR PENGUJIAN: {$testsPassed} PASS, {$testsFailed} FAIL\n";
echo "====================================================================\n";

if ($testsFailed === 0) {
    echo "SELURUH PENGUJIAN MODUL PELAPORAN KERUSAKAN BERHASIL DILALUI DENGAN SUKSES.\n";
    exit(0);
} else {
    echo "TERDAPAT KEGAGALAN DALAM PENGUJIAN.\n";
    exit(1);
}
