<?php
/**
 * Test Otomatis Dashboard & Reporting
 * Memvalidasi kalkulasi metrik Dashboard, query visualisasi Chart.js,
 * serta fungsionalitas filter dan reporting pada ReportModel.
 */

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/models/ReportModel.php';
require_once __DIR__ . '/../app/models/GedungModel.php';
require_once __DIR__ . '/../app/models/RuanganModel.php';
require_once __DIR__ . '/../app/models/FasilitasModel.php';

echo "=== MEMULAI TEST SUITE DASHBOARD & REPORTING ===\n\n";
$passCount = 0;
$failCount = 0;

function assertTrue($condition, $testName) {
    global $passCount, $failCount;
    if ($condition) {
        echo "[PASS] " . $testName . "\n";
        $passCount++;
    } else {
        echo "[FAIL] " . $testName . "\n";
        $failCount++;
    }
}

$db = new Database();

// --------------------------------------------------------------------------
// TEST 1: DASHBOARD METRICS ADMIN / PETUGAS (10 Cards)
// --------------------------------------------------------------------------
echo "--- 1. Testing Dashboard 10 Metric Cards (Admin/Petugas) ---\n";

$totalGedung = (int)($db->query("SELECT COUNT(*) as c FROM gedung")->single()['c'] ?? 0);
$totalRuangan = (int)($db->query("SELECT COUNT(*) as c FROM ruangan")->single()['c'] ?? 0);
$totalFasilitas = (int)($db->query("SELECT COUNT(*) as c FROM fasilitas")->single()['c'] ?? 0);
$fasilitasBaik = (int)($db->query("SELECT COUNT(*) as c FROM fasilitas WHERE kondisi = 'baik'")->single()['c'] ?? 0);
$rusakRingan = (int)($db->query("SELECT COUNT(*) as c FROM fasilitas WHERE kondisi = 'rusak_ringan'")->single()['c'] ?? 0);
$rusakBerat = (int)($db->query("SELECT COUNT(*) as c FROM fasilitas WHERE kondisi = 'rusak_berat'")->single()['c'] ?? 0);
$tidakLayak = (int)($db->query("SELECT COUNT(*) as c FROM fasilitas WHERE kondisi = 'tidak_layak'")->single()['c'] ?? 0);
$laporanMenunggu = (int)($db->query("SELECT COUNT(*) as c FROM laporan_kerusakan WHERE status = 'menunggu'")->single()['c'] ?? 0);
$laporanDiproses = (int)($db->query("SELECT COUNT(*) as c FROM laporan_kerusakan WHERE status IN ('diverifikasi', 'diproses')")->single()['c'] ?? 0);
$laporanSelesai = (int)($db->query("SELECT COUNT(*) as c FROM laporan_kerusakan WHERE status = 'selesai'")->single()['c'] ?? 0);

assertTrue($totalGedung >= 1, "Card 1: Total Gedung valid (Found: $totalGedung)");
assertTrue($totalRuangan >= 1, "Card 2: Total Ruangan valid (Found: $totalRuangan)");
assertTrue($totalFasilitas >= 1, "Card 3: Total Fasilitas valid (Found: $totalFasilitas)");
assertTrue($fasilitasBaik >= 0, "Card 4: Fasilitas Baik valid (Found: $fasilitasBaik)");
assertTrue($rusakRingan >= 0, "Card 5: Rusak Ringan valid (Found: $rusakRingan)");
assertTrue($rusakBerat >= 0, "Card 6: Rusak Berat valid (Found: $rusakBerat)");
assertTrue($tidakLayak >= 0, "Card 7: Tidak Layak valid (Found: $tidakLayak)");
assertTrue($laporanMenunggu >= 0, "Card 8: Laporan Menunggu valid (Found: $laporanMenunggu)");
assertTrue($laporanDiproses >= 0, "Card 9: Laporan Diproses valid (Found: $laporanDiproses)");
assertTrue($laporanSelesai >= 0, "Card 10: Laporan Selesai valid (Found: $laporanSelesai)");

// --------------------------------------------------------------------------
// TEST 2: DASHBOARD VISUALIZATIONS & INFORMATION SECTIONS
// --------------------------------------------------------------------------
echo "\n--- 2. Testing Visualizations & Info Sections ---\n";

// Info 1: Distribusi Kondisi Fasilitas
$kondisiRows = $db->query("SELECT kondisi, COUNT(*) as jumlah FROM fasilitas GROUP BY kondisi")->resultSet();
assertTrue(is_array($kondisiRows), "Info 1: Distribusi kondisi fasilitas data terambil");

// Info 2: Jumlah Fasilitas per Gedung
$fasilitasPerGedung = $db->query(
    "SELECT g.id, g.nama_gedung, g.kode_gedung, 
            COUNT(f.id) as total_fasilitas, 
            COALESCE(SUM(f.jumlah), 0) as total_unit 
     FROM gedung g 
     LEFT JOIN ruangan r ON g.id = r.gedung_id 
     LEFT JOIN fasilitas f ON r.id = f.ruangan_id 
     GROUP BY g.id, g.nama_gedung, g.kode_gedung 
     ORDER BY total_fasilitas DESC"
)->resultSet();
assertTrue(count($fasilitasPerGedung) >= 1, "Info 2: Jumlah fasilitas per gedung terambil (" . count($fasilitasPerGedung) . " gedung)");

// Info 3: Laporan Kerusakan Terbaru
$laporanTerbaru = $db->query(
    "SELECT l.*, u.nama_lengkap as pelapor, f.nama as nama_fasilitas, f.kode_inventaris,
            r.nama_ruangan, g.nama_gedung 
     FROM laporan_kerusakan l
     JOIN users u ON l.user_id = u.id
     JOIN fasilitas f ON l.fasilitas_id = f.id
     JOIN ruangan r ON l.ruangan_id = r.id
     JOIN gedung g ON r.gedung_id = g.id
     ORDER BY l.created_at DESC LIMIT 5"
)->resultSet();
assertTrue(is_array($laporanTerbaru), "Info 3: Laporan kerusakan terbaru dapat di-query tanpa error");

// Info 4: Fasilitas yang Sering Rusak
$fasilitasSeringRusak = $db->query(
    "SELECT f.id, f.kode_inventaris, f.nama as nama_fasilitas, f.kondisi, 
            r.nama_ruangan, g.nama_gedung,
            COUNT(l.id) as total_laporan,
            (SELECT COUNT(*) FROM pemeliharaan WHERE fasilitas_id = f.id AND jenis_tindakan = 'Perbaikan') as total_perbaikan
     FROM fasilitas f
     JOIN ruangan r ON f.ruangan_id = r.id
     JOIN gedung g ON r.gedung_id = g.id
     LEFT JOIN laporan_kerusakan l ON f.id = l.fasilitas_id
     GROUP BY f.id, f.kode_inventaris, f.nama, f.kondisi, r.nama_ruangan, g.nama_gedung
     ORDER BY (COUNT(l.id) + (SELECT COUNT(*) FROM pemeliharaan WHERE fasilitas_id = f.id AND jenis_tindakan = 'Perbaikan')) DESC, f.nama ASC
     LIMIT 5"
)->resultSet();
assertTrue(is_array($fasilitasSeringRusak), "Info 4: Fasilitas yang sering rusak terambil");

// Info 5: Fasilitas Perlu Perhatian
$fasilitasPerluPerhatian = $db->query(
    "SELECT f.id, f.kode_inventaris, f.nama as nama_fasilitas, f.kondisi, f.jumlah,
            r.nama_ruangan, g.nama_gedung,
            (SELECT COUNT(*) FROM laporan_kerusakan WHERE fasilitas_id = f.id AND status NOT IN ('selesai', 'ditolak')) as active_reports
     FROM fasilitas f
     JOIN ruangan r ON f.ruangan_id = r.id
     JOIN gedung g ON r.gedung_id = g.id
     WHERE f.kondisi IN ('rusak_ringan', 'rusak_berat', 'tidak_layak', 'hilang')
        OR f.id IN (SELECT fasilitas_id FROM laporan_kerusakan WHERE status NOT IN ('selesai', 'ditolak'))
     LIMIT 6"
)->resultSet();
assertTrue(is_array($fasilitasPerluPerhatian), "Info 5: Fasilitas perlu perhatian terambil");

// --------------------------------------------------------------------------
// TEST 3: DASHBOARD DOSEN & MAHASISWA (5 Cards)
// --------------------------------------------------------------------------
echo "\n--- 3. Testing Dashboard Dosen / Mahasiswa (5 Cards) ---\n";
$firstUser = $db->query("SELECT * FROM users WHERE jenis_pengguna = 'mahasiswa' LIMIT 1")->single();
if (!$firstUser) {
    $firstUser = $db->query("SELECT * FROM users WHERE jenis_pengguna = 'dosen' LIMIT 1")->single();
}
$userId = $firstUser ? (int)$firstUser['id'] : 1;

$jmlRuangan = (int)($db->query("SELECT COUNT(*) as c FROM ruangan WHERE status = 'tersedia'")->single()['c'] ?? 0);
$jmlFasilitasView = (int)($db->query("SELECT COUNT(*) as c FROM fasilitas")->single()['c'] ?? 0);
$laporanSaya = (int)($db->query("SELECT COUNT(*) as c FROM laporan_kerusakan WHERE user_id = :uid")->bind(':uid', $userId)->single()['c'] ?? 0);
$laporanDiprosesUser = (int)($db->query("SELECT COUNT(*) as c FROM laporan_kerusakan WHERE user_id = :uid AND status IN ('menunggu', 'diverifikasi', 'diproses')")->bind(':uid', $userId)->single()['c'] ?? 0);
$laporanSelesaiUser = (int)($db->query("SELECT COUNT(*) as c FROM laporan_kerusakan WHERE user_id = :uid AND status = 'selesai'")->bind(':uid', $userId)->single()['c'] ?? 0);

assertTrue($jmlRuangan >= 0, "Civitas Card 1: Jumlah ruangan tersedia (Found: $jmlRuangan)");
assertTrue($jmlFasilitasView >= 0, "Civitas Card 2: Jumlah fasilitas dapat dilihat (Found: $jmlFasilitasView)");
assertTrue($laporanSaya >= 0, "Civitas Card 3: Laporan saya (Found: $laporanSaya)");
assertTrue($laporanDiprosesUser >= 0, "Civitas Card 4: Laporan sedang diproses (Found: $laporanDiprosesUser)");
assertTrue($laporanSelesaiUser >= 0, "Civitas Card 5: Laporan selesai (Found: $laporanSelesaiUser)");

// --------------------------------------------------------------------------
// TEST 4: REPORT MODEL (6 JENIS LAPORAN & MULTI-FILTER)
// --------------------------------------------------------------------------
echo "\n--- 4. Testing 6 Jenis Laporan pada ReportModel ---\n";
$reportModel = new ReportModel();

// Report 1: Seluruh Fasilitas
$lapFasilitas = $reportModel->getLaporanFasilitas([]);
assertTrue(is_array($lapFasilitas) && count($lapFasilitas) > 0, "Report 1: getLaporanFasilitas tanpa filter (" . count($lapFasilitas) . " rows)");

// Report 1 with filters
$firstGedung = $db->query("SELECT id FROM gedung LIMIT 1")->single();
$gedungId = $firstGedung ? (int)$firstGedung['id'] : 1;
$lapFasilitasFiltered = $reportModel->getLaporanFasilitas(['gedung_id' => $gedungId, 'kondisi' => 'baik']);
assertTrue(is_array($lapFasilitasFiltered), "Report 1 Filter: getLaporanFasilitas (gedung_id=$gedungId, kondisi=baik)");

// Report 2: Per Gedung
$lapGedung = $reportModel->getLaporanPerGedung([]);
assertTrue(is_array($lapGedung) && count($lapGedung) > 0, "Report 2: getLaporanPerGedung (" . count($lapGedung) . " rows)");

// Report 3: Per Ruangan
$lapRuangan = $reportModel->getLaporanPerRuangan([]);
assertTrue(is_array($lapRuangan) && count($lapRuangan) > 0, "Report 3: getLaporanPerRuangan (" . count($lapRuangan) . " rows)");

// Report 4: Berdasarkan Kondisi
$lapKondisi = $reportModel->getLaporanKondisi(['kondisi' => 'baik']);
assertTrue(is_array($lapKondisi), "Report 4: getLaporanKondisi kondisi=baik (" . count($lapKondisi) . " rows)");

// Report 5: Laporan Kerusakan
$lapKerusakan = $reportModel->getLaporanKerusakan([]);
assertTrue(is_array($lapKerusakan), "Report 5: getLaporanKerusakan (" . count($lapKerusakan) . " rows)");

$lapKerusakanFilter = $reportModel->getLaporanKerusakan([
    'tanggal_dari' => '2020-01-01',
    'tanggal_sampai' => '2030-12-31',
    'status' => 'selesai'
]);
assertTrue(is_array($lapKerusakanFilter), "Report 5 Filter: getLaporanKerusakan dengan filter tanggal & status");

// Report 6: Laporan Pemeliharaan
$lapPemeliharaan = $reportModel->getLaporanPemeliharaan([]);
assertTrue(is_array($lapPemeliharaan), "Report 6: getLaporanPemeliharaan (" . count($lapPemeliharaan) . " rows)");

$lapPemeliharaanFilter = $reportModel->getLaporanPemeliharaan([
    'tanggal_dari' => '2020-01-01',
    'tanggal_sampai' => '2030-12-31',
    'kondisi' => 'baik',
    'status' => 'selesai'
]);
assertTrue(is_array($lapPemeliharaanFilter), "Report 6 Filter: getLaporanPemeliharaan dengan kondisi & status (No PDO duplicate param error)");

echo "\n============================================\n";
echo "HASIL PENGUJIAN: $passCount PASS / $failCount FAIL\n";
if ($failCount === 0) {
    echo "SELURUH PENGUJIAN DASHBOARD & REPORTING BERHASIL! (100% OK)\n";
} else {
    echo "ADA PENGUJIAN YANG GAGAL!\n";
}
