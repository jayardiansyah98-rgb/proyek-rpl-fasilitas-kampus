<?php
/**
 * Test Otomatis AI Facility Analysis & AI Maintenance Recommendation
 */

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/services/AIService.php';
require_once __DIR__ . '/../app/services/ai/AIFacilityAnalyzer.php';
require_once __DIR__ . '/../app/services/ai/AIMaintenanceRecommender.php';

echo "=== MEMULAI TEST SUITE AI FACILITY ANALYSIS & MAINTENANCE RECOMMENDATIONS ===\n\n";
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

// --------------------------------------------------------------------------
// 1. TEST AI FACILITY ANALYZER
// --------------------------------------------------------------------------
echo "--- 1. Testing AIFacilityAnalyzer ---\n";
$analyzer = new AIFacilityAnalyzer();
$analysis = $analyzer->analyze();

assertTrue($analysis['cukup_data'] === true, "Analyzer: cukup_data bernilai true");
assertTrue(isset($analysis['ringkasan']), "Analyzer: ringkasan kondisi fasilitas tersedia");
assertTrue($analysis['ringkasan']['total_fasilitas'] >= 1, "Analyzer: total fasilitas terhitung ({$analysis['ringkasan']['total_fasilitas']})");
assertTrue($analysis['ringkasan']['total_unit'] >= 1, "Analyzer: total unit fasilitas terhitung ({$analysis['ringkasan']['total_unit']})");
assertTrue(isset($analysis['ringkasan']['kondisi_counts']['baik']), "Analyzer: kondisi_counts baik terhitung");
assertTrue(isset($analysis['ringkasan']['kondisi_counts']['rusak_ringan']), "Analyzer: kondisi_counts rusak ringan terhitung");
assertTrue($analysis['ringkasan']['rata_rata_usia_tahun'] >= 0, "Analyzer: rata-rata umur aset terhitung ({$analysis['ringkasan']['rata_rata_usia_tahun']} tahun)");

// Output: Fasilitas bermasalah
assertTrue(is_array($analysis['fasilitas_bermasalah']), "Analyzer: fasilitas_bermasalah berupa array");
if (!empty($analysis['fasilitas_bermasalah'])) {
    $fb = $analysis['fasilitas_bermasalah'][0];
    assertTrue(isset($fb['nama']), "Fasilitas bermasalah memiliki nama ({$fb['nama']})");
    assertTrue(isset($fb['umur_tahun']), "Fasilitas bermasalah memiliki umur_tahun ({$fb['umur_tahun']} thn)");
    assertTrue(isset($fb['total_laporan']), "Fasilitas bermasalah memiliki total_laporan ({$fb['total_laporan']})");
    assertTrue(is_array($fb['alasan_masalah']) && count($fb['alasan_masalah']) > 0, "Fasilitas bermasalah memiliki rincian alasan masalah");
}

// Output: Gedung dengan masalah terbanyak
assertTrue($analysis['gedung_masalah_terbanyak'] !== null, "Analyzer: gedung_masalah_terbanyak teridentifikasi ({$analysis['gedung_masalah_terbanyak']['nama_gedung']})");
assertTrue(isset($analysis['gedung_masalah_terbanyak']['fasilitas_rusak']), "Gedung masalah terbanyak memiliki counter fasilitas rusak");

// Output: Ruangan dengan masalah terbanyak
assertTrue($analysis['ruangan_masalah_terbanyak'] !== null, "Analyzer: ruangan_masalah_terbanyak teridentifikasi ({$analysis['ruangan_masalah_terbanyak']['nama_ruangan']})");
assertTrue(isset($analysis['ruangan_masalah_terbanyak']['total_laporan']), "Ruangan masalah terbanyak memiliki total laporan");

// Output: Pola kerusakan yang terlihat
assertTrue(is_array($analysis['pola_kerusakan']) && count($analysis['pola_kerusakan']) >= 3, "Analyzer: minimal 3 pola kerusakan terdeteksi (Found: " . count($analysis['pola_kerusakan']) . ")");
foreach ($analysis['pola_kerusakan'] as $idx => $p) {
    assertTrue(!empty($p['judul']) && !empty($p['deskripsi']) && !empty($p['rekomendasi']), "Pola " . ($idx + 1) . " memiliki judul, deskripsi, dan saran");
}

// --------------------------------------------------------------------------
// 2. TEST AI MAINTENANCE RECOMMENDER
// --------------------------------------------------------------------------
echo "\n--- 2. Testing AIMaintenanceRecommender ---\n";
$recommender = new AIMaintenanceRecommender();
$recs = $recommender->getRecommendations();

assertTrue($recs['cukup_data'] === true, "Recommender: cukup_data bernilai true");
assertTrue(isset($recs['prioritas_tinggi']), "Recommender: kategori prioritas_tinggi tersedia");
assertTrue(isset($recs['prioritas_sedang']), "Recommender: kategori prioritas_sedang tersedia");
assertTrue(isset($recs['prioritas_rendah']), "Recommender: kategori prioritas_rendah tersedia");
assertTrue($recs['total_rekomendasi'] > 0, "Recommender: total rekomendasi > 0 ({$recs['total_rekomendasi']})");

// Periksa kelengkapan item rekomendasi (nama, kondisi, total_laporan, umur, alasan, tindakan)
$sampleRec = null;
if (!empty($recs['prioritas_tinggi'])) {
    $sampleRec = $recs['prioritas_tinggi'][0];
} elseif (!empty($recs['prioritas_sedang'])) {
    $sampleRec = $recs['prioritas_sedang'][0];
} elseif (!empty($recs['prioritas_rendah'])) {
    $sampleRec = $recs['prioritas_rendah'][0];
}

assertTrue($sampleRec !== null, "Sample rekomendasi ditemukan untuk pengujian");
assertTrue(!empty($sampleRec['nama']), "Rekomendasi memiliki nama ({$sampleRec['nama']})");
assertTrue(!empty($sampleRec['kondisi']), "Rekomendasi memiliki kondisi ({$sampleRec['kondisi']})");
assertTrue(isset($sampleRec['umur_tahun']), "Rekomendasi memiliki umur_tahun ({$sampleRec['umur_tahun']} thn)");
assertTrue(isset($sampleRec['total_laporan']), "Rekomendasi memiliki total_laporan ({$sampleRec['total_laporan']})");
assertTrue(!empty($sampleRec['alasan_ringkas']), "Rekomendasi memiliki alasan_ringkas");
assertTrue(!empty($sampleRec['tindakan_disarankan']), "Rekomendasi memiliki tindakan_disarankan");

// Verifikasi aturan: AI tidak mengubah status di DB (hanya membaca)
$proyektorDb = (new Database())->query("SELECT kondisi FROM fasilitas WHERE kode_inventaris = 'INV-2022-PRJ-011'")->single();
assertTrue($proyektorDb['kondisi'] === 'rusak_ringan', "Aturan Kepatuhan: Status fasilitas di database TIDAK berubah otomatis oleh AI (Tetap: {$proyektorDb['kondisi']})");

// --------------------------------------------------------------------------
// 3. TEST AISERVICE INTEGRATION
// --------------------------------------------------------------------------
echo "\n--- 3. Testing AIService Integration Methods ---\n";
$aiService = new AIService();

$srvAnalysis = $aiService->getFacilityAnalysis();
assertTrue($srvAnalysis['cukup_data'] === true, "AIService::getFacilityAnalysis() menghasilkan output valid");

$srvRecs = $aiService->getMaintenanceRecommendations();
assertTrue($srvRecs['cukup_data'] === true, "AIService::getMaintenanceRecommendations() menghasilkan output valid");

echo "\n============================================\n";
echo "HASIL PENGUJIAN: $passCount PASS / $failCount FAIL\n";
if ($failCount === 0) {
    echo "SELURUH PENGUJIAN AI FACILITY ANALYSIS & RECOMMENDATIONS BERHASIL! (100% OK)\n";
} else {
    echo "ADA PENGUJIAN YANG GAGAL!\n";
}
