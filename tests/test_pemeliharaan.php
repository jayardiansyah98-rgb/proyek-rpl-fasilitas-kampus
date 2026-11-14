<?php
/**
 * Test Otomasi Komprehensif: Modul Pemeliharaan & Riwayat Fasilitas
 * Sistem Informasi Pendataan Fasilitas STMIK IKMI Cirebon
 */

$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/Fasilitas Kampus/public/index.php';

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/helpers/Auth.php';
require_once __DIR__ . '/../app/helpers/helper.php';
require_once __DIR__ . '/../app/models/PemeliharaanModel.php';
require_once __DIR__ . '/../app/models/FasilitasModel.php';
require_once __DIR__ . '/../app/models/LaporanModel.php';

echo "====================================================================\n";
echo "  PENGUJIAN VALIDASI, RBAC, CRUD & HISTORI: MODUL PEMELIHARAAN      \n";
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
    $pemeliharaanModel = new PemeliharaanModel();
    $fasilitasModel = new FasilitasModel();

    // 1. PENGUJIAN GENERATE KODE PEMELIHARAAN
    echo "1. Pengujian Generate Kode Pemeliharaan:\n";
    $kode = $pemeliharaanModel->generateKodePemeliharaan();
    $prefix = 'MT-' . date('Ym') . '-';
    assertTest(strpos($kode, $prefix) === 0, "Kode diawali prefix '{$prefix}' (Hasil: {$kode})");
    assertTest(strlen($kode) === strlen($prefix) + 3, "Panjang kode pemeliharaan konsisten");
    echo "\n";

    // 2. PENGUJIAN TAMBAH RIWAYAT PEMELIHARAAN
    echo "2. Pengujian Tambah Catatan Riwayat Pemeliharaan (Petugas Sarpras):\n";
    
    // Cari fasilitas untuk pengujian
    $targetFasilitas = $db->query("SELECT id, nama, kondisi FROM fasilitas LIMIT 1")->single();
    if (!$targetFasilitas) {
        throw new Exception("Data master fasilitas tidak ditemukan.");
    }
    $fid = $targetFasilitas['id'];

    $testData = [
        'fasilitas_id' => $fid,
        'petugas_id' => 2, // Petugas Sarpras
        'tanggal' => date('Y-m-d'),
        'jenis_tindakan' => 'Perbaikan',
        'kondisi_sebelum' => 'rusak_ringan',
        'kondisi_sesudah' => 'baik',
        'deskripsi' => 'Pengujian otomatis: Penggantian pasta termal dan kalibrasi unit pendingin.',
        'biaya' => 175000.00,
        'keterangan' => 'Komponen suku cadang baru dengan garansi 3 bulan',
        'status' => 'selesai',
        'tampil_publik' => 1,
        'update_fasilitas_kondisi' => true
    ];

    $createdId = $pemeliharaanModel->create($testData);
    assertTest($createdId > 0, "Catatan pemeliharaan berhasil di-insert ke DB (ID: {$createdId})");

    // Verifikasi data yang tersimpan
    $savedRecord = $pemeliharaanModel->findById($createdId);
    assertTest($savedRecord !== false, "Record berhasil di-query ulang dari database");
    assertTest($savedRecord['jenis_tindakan'] === 'Perbaikan', "Jenis tindakan tersimpan valid ('Perbaikan')");
    assertTest($savedRecord['kondisi_sebelum'] === 'rusak_ringan', "Kondisi sebelum tersimpan valid ('rusak_ringan')");
    assertTest($savedRecord['kondisi_sesudah'] === 'baik', "Kondisi sesudah tersimpan valid ('baik')");
    assertTest((float)$savedRecord['biaya'] === 175000.00, "Biaya tersimpan valid (Rp 175.000)");

    // Verifikasi fitur: Mengubah kondisi fasilitas setelah pemeliharaan
    $updatedFasilitas = $fasilitasModel->findById($fid);
    assertTest($updatedFasilitas['kondisi'] === 'baik', "Kondisi fasilitas fisik berhasil diperbarui menjadi 'baik' setelah pemeliharaan");
    echo "\n";

    // 3. PENGUJIAN EDIT RIWAYAT
    echo "3. Pengujian Edit Catatan Pemeliharaan:\n";
    $editData = [
        'fasilitas_id' => $fid,
        'petugas_id' => 2,
        'tanggal' => date('Y-m-d'),
        'jenis_tindakan' => 'Penggantian',
        'kondisi_sebelum' => 'rusak_ringan',
        'kondisi_sesudah' => 'baik',
        'deskripsi' => 'Revisi deskripsi: Penggantian kapasitor dan pembersihan menyeluruh.',
        'biaya' => 200000.00,
        'keterangan' => 'Revisi catatan logistik',
        'status' => 'selesai',
        'tampil_publik' => 1,
        'update_fasilitas_kondisi' => false
    ];

    $updateSuccess = $pemeliharaanModel->update($createdId, $editData);
    assertTest($updateSuccess === true, "Query UPDATE riwayat pemeliharaan berhasil dijalankan");

    $editedRecord = $pemeliharaanModel->findById($createdId);
    assertTest($editedRecord['jenis_tindakan'] === 'Penggantian', "Jenis tindakan berhasil diubah ke 'Penggantian'");
    assertTest((float)$editedRecord['biaya'] === 200000.00, "Biaya berhasil diperbarui menjadi Rp 200.000");
    echo "\n";

    // 4. PENGUJIAN MELIHAT HISTORI BERDASARKAN FASILITAS
    echo "4. Pengujian Melihat Histori Berdasarkan Fasilitas:\n";
    $histori = $pemeliharaanModel->getHistoriByFasilitas($fid);
    assertTest(is_array($histori) && count($histori) >= 1, "Histori spesifik fasilitas {$fid} berhasil diambil (Jumlah: " . count($histori) . ")");
    
    // Pastikan terurut tanggal terbaru
    $firstItem = $histori[0];
    assertTest($firstItem['id'] == $createdId, "Histori terurut tanggal terbaru di posisi paling atas");
    echo "\n";

    // 5. PENGUJIAN PERHITUNGAN SEDERHANA (TOTAL PEMELIHARAAN, BIAYA, JUMLAH KERUSAKAN)
    echo "5. Pengujian Perhitungan Sederhana Statistik Fasilitas:\n";
    $stats = $fasilitasModel->getStatistikFasilitas($fid);
    assertTest(isset($stats['total_pemeliharaan']) && $stats['total_pemeliharaan'] >= 1, "Perhitungan Total Pemeliharaan valid (Total: {$stats['total_pemeliharaan']})");
    assertTest(isset($stats['total_biaya']) && $stats['total_biaya'] >= 200000, "Perhitungan Total Biaya valid (Total: Rp " . number_format($stats['total_biaya'], 0, ',', '.') . ")");
    assertTest(isset($stats['jumlah_kerusakan']) && is_numeric($stats['jumlah_kerusakan']), "Perhitungan Jumlah Kerusakan valid (Total: {$stats['jumlah_kerusakan']} kasus)");
    echo "\n";

    // 6. PENGUJIAN DATA SCOPING & RBAC: PUBLIK VS INTERNAL
    echo "6. Pengujian Hak Akses Dosen/Mahasiswa (Data Scoping Publik vs Internal):\n";
    // Buat 1 record khusus internal (tampil_publik = 0)
    $internalData = [
        'fasilitas_id' => $fid,
        'petugas_id' => 2,
        'tanggal' => date('Y-m-d'),
        'jenis_tindakan' => 'Pemeriksaan',
        'kondisi_sebelum' => 'baik',
        'kondisi_sesudah' => 'baik',
        'deskripsi' => 'Pemeriksaan rahasia internal tim sarpras, tidak untuk publik.',
        'biaya' => 50000.00,
        'keterangan' => 'Audit internal Sarpras',
        'status' => 'selesai',
        'tampil_publik' => 0,
        'update_fasilitas_kondisi' => false
    ];
    $internalId = $pemeliharaanModel->create($internalData);
    assertTest($internalId > 0, "Record pemeliharaan internal (tampil_publik = 0) berhasil dibuat (ID: {$internalId})");

    // Admin & Petugas (onlyPublic = false): Dapat melihat data internal
    $adminView = $pemeliharaanModel->findById($internalId, false);
    assertTest($adminView !== false && $adminView['id'] == $internalId, "Admin/Petugas dapat melihat record internal");

    // Dosen / Mahasiswa (onlyPublic = true): Dibatasi, tidak dapat melihat data internal
    $publicView = $pemeliharaanModel->findById($internalId, true);
    assertTest($publicView === false, "Dosen/Mahasiswa dibatasi dari record bertanda internal (tampil_publik = 0)");

    // Pengujian pada getRiwayatPemeliharaan untuk publik
    $publicRiwayat = $fasilitasModel->getRiwayatPemeliharaan($fid, true);
    $foundInternalInPublic = false;
    foreach ($publicRiwayat as $r) {
        if ($r['id'] == $internalId) {
            $foundInternalInPublic = true;
            break;
        }
    }
    assertTest(!$foundInternalInPublic, "Riwayat fasilitas untuk publik tidak membocorkan catatan internal");
    echo "\n";

    // 7. PENGUJIAN UPDATE KONDISI FASILITAS
    echo "7. Pengujian Mengubah Kondisi Fasilitas Setelah Pemeliharaan:\n";
    $pemeliharaanModel->updateKondisiFasilitas($fid, 'rusak_ringan', 'Pengujian simulasi kerusakan');
    $checkF = $fasilitasModel->findById($fid);
    assertTest($checkF['kondisi'] === 'rusak_ringan', "Kondisi fasilitas berhasil diubah menjadi 'rusak_ringan'");

    $pemeliharaanModel->updateKondisiFasilitas($fid, 'baik', 'Selesai perbaikan dan lolos uji fungsi');
    $checkF2 = $fasilitasModel->findById($fid);
    assertTest($checkF2['kondisi'] === 'baik', "Kondisi fasilitas berhasil dinormalkan kembali ke 'baik'");
    echo "\n";

    // Bersihkan data testing
    $pemeliharaanModel->delete($createdId);
    $pemeliharaanModel->delete($internalId);
    assertTest($pemeliharaanModel->findById($createdId) === false, "Cleanup data pengujian berhasil");

    echo "\n====================================================================\n";
    echo "  RINGKASAN HASIL PENGUJIAN: {$testsPassed} PASS, {$testsFailed} FAIL \n";
    echo "====================================================================\n";

} catch (Exception $e) {
    echo "\n[ERROR PENGUJIAN]: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
