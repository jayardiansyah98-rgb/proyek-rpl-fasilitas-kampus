<?php
/**
 * Test Otomatis AI Assistant
 * Menguji Service Layer, Whitelist Query Data Retriever, Intent Resolver,
 * Abstraksi Provider, dan Logging Interaksi.
 */

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/services/AIService.php';

echo "=== MEMULAI TEST SUITE AI ASSISTANT ===\n\n";
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
// 1. TEST INTENT RESOLVER DENGAN 6 CONTOH PERTANYAAN RESMI
// --------------------------------------------------------------------------
echo "--- 1. Testing Intent Resolver (6 Pertanyaan Spesifikasi) ---\n";
$resolver = new IntentResolver();

$testCases = [
    [
        'q' => 'Berapa jumlah fasilitas yang rusak berat?',
        'expected_intent' => 'COUNT_CONDITION',
        'expected_method' => 'countFacilitiesByCondition'
    ],
    [
        'q' => 'Fasilitas apa yang paling banyak mengalami kerusakan?',
        'expected_intent' => 'MOST_DAMAGED',
        'expected_method' => 'getMostDamagedFacilities'
    ],
    [
        'q' => 'Berapa jumlah fasilitas di Gedung A?',
        'expected_intent' => 'FACILITIES_BY_BUILDING',
        'expected_method' => 'getFacilitiesByBuilding'
    ],
    [
        'q' => 'Ruangan mana yang memiliki fasilitas paling lengkap?',
        'expected_intent' => 'ROOM_MOST_FACILITIES',
        'expected_method' => 'getRoomWithMostFacilities'
    ],
    [
        'q' => 'Fasilitas apa yang perlu diprioritaskan untuk diperbaiki?',
        'expected_intent' => 'PRIORITY_REPAIR',
        'expected_method' => 'getPriorityRepairFacilities'
    ],
    [
        'q' => 'Tampilkan kondisi fasilitas di Laboratorium Komputer 1.',
        'expected_intent' => 'FACILITIES_IN_ROOM',
        'expected_method' => 'getFacilitiesInRoom'
    ]
];

foreach ($testCases as $idx => $tc) {
    $res = $resolver->resolve($tc['q']);
    $num = $idx + 1;
    assertTrue($res['intent'] === $tc['expected_intent'], "P{$num}: Intent '{$tc['q']}' => {$res['intent']}");
    assertTrue($res['method'] === $tc['expected_method'], "P{$num}: Method '{$tc['q']}' => {$res['method']}");
}

// --------------------------------------------------------------------------
// 2. TEST FACILITY DATA RETRIEVER (WHITELIST QUERY YANG DIIZINKAN)
// --------------------------------------------------------------------------
echo "\n--- 2. Testing Facility Data Retriever (Whitelist Parameterized Queries) ---\n";
$retriever = new FacilityDataRetriever();

$c1 = $retriever->countFacilitiesByCondition('rusak_berat');
assertTrue(isset($c1['total_item']) && isset($c1['total_unit']), "Retriever 1: countFacilitiesByCondition rusak_berat");

$c2 = $retriever->getMostDamagedFacilities(5);
assertTrue(isset($c2['fasilitas']) && is_array($c2['fasilitas']), "Retriever 2: getMostDamagedFacilities");

$c3 = $retriever->getFacilitiesByBuilding('GD-A');
assertTrue($c3['found'] === true && isset($c3['stats']), "Retriever 3: getFacilitiesByBuilding GD-A");

$c4 = $retriever->getRoomWithMostFacilities(5);
assertTrue(isset($c4['ruangan']) && count($c4['ruangan']) > 0, "Retriever 4: getRoomWithMostFacilities");

$c5 = $retriever->getPriorityRepairFacilities(6);
assertTrue(isset($c5['fasilitas']) && is_array($c5['fasilitas']), "Retriever 5: getPriorityRepairFacilities");

$c6 = $retriever->getFacilitiesInRoom('Laboratorium AI & Data Science');
assertTrue($c6['found'] === true && isset($c6['stats']), "Retriever 6: getFacilitiesInRoom Lab AI");

$c7 = $retriever->getTotalFacilityStats();
assertTrue(isset($c7['total_gedung']) && $c7['total_gedung'] >= 2, "Retriever 7: getTotalFacilityStats");

$c8 = $retriever->recommendMaintenance();
assertTrue(isset($c8['total_rekomendasi']), "Retriever 8: recommendMaintenance");

$c9 = $retriever->analyzeFacilityDetail(1);
assertTrue($c9 !== null && isset($c9['fasilitas']), "Retriever 9: analyzeFacilityDetail ID=1");

// --------------------------------------------------------------------------
// 3. TEST AI SERVICE LAYER & LOGGING
// --------------------------------------------------------------------------
echo "\n--- 3. Testing AIService::generateResponse() & Logging ---\n";
$aiService = new AIService();
$testUserId = 1; // Admin

// Uji pemrosesan pertanyaan 1
$resp1 = $aiService->generateResponse('Berapa jumlah fasilitas yang rusak berat?', $testUserId);
assertTrue($resp1['status'] === 'success', "AIService P1 status success");
assertTrue(!empty($resp1['response']), "AIService P1 menghasilkan teks respons");
assertTrue($resp1['execution_time_ms'] >= 0, "AIService P1 mencatat waktu eksekusi ({$resp1['execution_time_ms']} ms)");

// Uji pemrosesan pertanyaan 2
$resp2 = $aiService->generateResponse('Fasilitas apa yang paling banyak mengalami kerusakan?', $testUserId);
assertTrue($resp2['status'] === 'success', "AIService P2 status success");
assertTrue(stripos($resp2['response'], 'kerusakan') !== false || stripos($resp2['response'], 'fasilitas') !== false, "AIService P2 respons relevan");

// Uji pemrosesan pertanyaan 3
$resp3 = $aiService->generateResponse('Berapa jumlah fasilitas di Gedung A?', $testUserId);
assertTrue($resp3['status'] === 'success', "AIService P3 status success");
assertTrue(stripos($resp3['response'], 'Gedung') !== false, "AIService P3 respons menyebutkan Gedung");

// Uji pemrosesan pertanyaan 4
$resp4 = $aiService->generateResponse('Ruangan mana yang memiliki fasilitas paling lengkap?', $testUserId);
assertTrue($resp4['status'] === 'success', "AIService P4 status success");
assertTrue(stripos($resp4['response'], 'lengkap') !== false || stripos($resp4['response'], 'Laboratorium') !== false, "AIService P4 respons ruangan");

// Uji pemrosesan pertanyaan 5
$resp5 = $aiService->generateResponse('Fasilitas apa yang perlu diprioritaskan untuk diperbaiki?', $testUserId);
assertTrue($resp5['status'] === 'success', "AIService P5 status success");
assertTrue(!empty($resp5['response']), "AIService P5 respons prioritas ada");

// Uji pemrosesan pertanyaan 6
$resp6 = $aiService->generateResponse('Tampilkan kondisi fasilitas di Laboratorium Komputer 1.', $testUserId);
assertTrue($resp6['status'] === 'success', "AIService P6 status success");
assertTrue(stripos($resp6['response'], 'Laboratorium') !== false, "AIService P6 respons laboratorium");

// --------------------------------------------------------------------------
// 4. TEST METODE TAMBAHAN AISERVICE
// --------------------------------------------------------------------------
echo "\n--- 4. Testing AIService::analyzeFacility() & recommendMaintenance() ---\n";
$analysis = $aiService->analyzeFacility(1);
assertTrue($analysis['status'] === 'success', "AIService analyzeFacility(1) berhasil");
assertTrue(isset($analysis['analisis_text']), "AIService analyzeFacility memiliki analisis_text");

$rec = $aiService->recommendMaintenance();
assertTrue($rec['status'] === 'success', "AIService recommendMaintenance() berhasil");
assertTrue(isset($rec['items']), "AIService recommendMaintenance mengembalikan daftar items");

// --------------------------------------------------------------------------
// 5. TEST LOGGING KE TABEL ai_logs
// --------------------------------------------------------------------------
echo "\n--- 5. Testing ai_logs Database Records ---\n";
$db = new Database();
$latestLog = $db->query("SELECT * FROM ai_logs WHERE user_id = :uid ORDER BY id DESC LIMIT 1")
                ->bind(':uid', $testUserId)
                ->single();

assertTrue($latestLog !== false && !empty($latestLog), "Log tersimpan di ai_logs");
assertTrue(!empty($latestLog['question']), "Kolom 'question' terisi di database: '{$latestLog['question']}'");
assertTrue(!empty($latestLog['response']), "Kolom 'response' terisi di database");
assertTrue(!empty($latestLog['created_at']), "Kolom 'created_at' tersimpan");

echo "\n============================================\n";
echo "HASIL PENGUJIAN: $passCount PASS / $failCount FAIL\n";
if ($failCount === 0) {
    echo "SELURUH PENGUJIAN AI ASSISTANT BERHASIL! (100% OK)\n";
} else {
    echo "ADA PENGUJIAN YANG GAGAL!\n";
}
