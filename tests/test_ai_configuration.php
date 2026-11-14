<?php
/**
 * Test Suite: Fitur Konfigurasi AI Assistant Multi-Model & API Key
 * Memverifikasi:
 * 1. AIConfigService (daftar provider, pembacaan config, test connection, hot-reload)
 * 2. Node.js Chatbot Service /api/config/update
 * 3. Provider OpenAICompatibleProvider (DeepSeek, OpenRouter, OpenAI)
 * 4. Stabilitas integrasi AIService dan Rule-Based Fallback
 */

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/services/AIService.php';
require_once __DIR__ . '/../app/services/ai/AIConfigService.php';
require_once __DIR__ . '/../app/services/ai/OpenAICompatibleProvider.php';

echo "=== TEST SUITE: KONFIGURASI AI ASSISTANT MULTI-MODEL ===\n\n";

$passed = 0;
$failed = 0;

function assertTest($name, $condition, $details = '') {
    global $passed, $failed;
    if ($condition) {
        echo " [PASS] $name\n";
        if ($details) echo "        $details\n";
        $passed++;
    } else {
        echo " [FAIL] $name\n";
        if ($details) echo "        $details\n";
        $failed++;
    }
}

// 1. Uji Dukungan Provider
$configService = new AIConfigService();
$providers = $configService->getSupportedProviders();
assertTest(
    "Provider didukung mencakup Gemini, DeepSeek, OpenRouter, OpenAI, dan Rule Engine",
    isset($providers['gemini']) && isset($providers['deepseek']) && isset($providers['openrouter']) && isset($providers['openai']) && isset($providers['rule_engine']),
    "Total provider terdaftar: " . count($providers)
);

// 2. Uji Pembacaan Konfigurasi Saat Ini
$currentConfig = $configService->getCurrentConfig();
assertTest(
    "Konfigurasi saat ini dapat dibaca dari environment",
    isset($currentConfig['provider']) && isset($currentConfig['model']),
    "Provider: " . $currentConfig['provider'] . " | Model: " . $currentConfig['model']
);

// 3. Uji Koneksi Rule-Based Engine (Lokal Offline)
$ruleTest = $configService->testConnection('rule_engine', '', 'rule_engine');
assertTest(
    "Uji coba koneksi Rule-Based Engine lokal berhasil instan (offline)",
    $ruleTest['status'] === 'success' && isset($ruleTest['latency_ms']),
    "Pesan: " . $ruleTest['message']
);

// 4. Uji Validasi API Key Kosong untuk Provider Eksternal
$emptyKeyTest = $configService->testConnection('deepseek', '', 'deepseek-chat');
assertTest(
    "Validasi menolak API Key kosong untuk provider eksternal",
    $emptyKeyTest['status'] === 'error' && str_contains($emptyKeyTest['message'], 'wajib diisi'),
    "Pesan: " . $emptyKeyTest['message']
);

// 5. Uji Hot-Reload ke Node.js Chatbot Service (Port 3001)
$ch = curl_init('http://127.0.0.1:3001/api/config/update');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'provider' => 'rule_engine',
    'apiKey' => '',
    'model' => 'rule_engine'
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 3);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$nodeUpdated = false;
if ($httpCode === 200 && $response) {
    $resJson = json_decode($response, true);
    $nodeUpdated = ($resJson['status'] === 'ok' && $resJson['config']['provider'] === 'rule_engine');
}
assertTest(
    "Endpoint Hot-Reload Node.js (/api/config/update) merespons sukses real-time",
    $nodeUpdated,
    "HTTP Code: $httpCode | Respons: " . substr($response ?: '', 0, 80)
);

// 6. Uji Instansiasi Multi-Provider di AIService
$aiServiceDeepSeek = new AIService(new OpenAICompatibleProvider('deepseek', 'sk-dummy-key-for-test', 'deepseek-chat'));
assertTest(
    "AIService dapat diinisialisasi dengan OpenAICompatibleProvider (DeepSeek)",
    $aiServiceDeepSeek instanceof AIService,
    "Inisialisasi berhasil tanpa exception"
);

$aiServiceClaude = new AIService(new OpenAICompatibleProvider('openrouter', 'sk-or-dummy-key', 'anthropic/claude-3.5-sonnet'));
assertTest(
    "AIService dapat diinisialisasi dengan OpenRouter/Claude",
    $aiServiceClaude instanceof AIService,
    "Inisialisasi berhasil tanpa exception"
);

// 7. Uji Stabilitas Respons Chatbot (Fallback Rule-Based Engine)
$aiService = new AIService();
$chatResult = $aiService->generateResponse('Berapa total fasilitas yang ada di kampus?', 1);
assertTest(
    "AI Assistant tetap berfungsi stabil menghasilkan jawaban berbasis database kampus",
    $chatResult['status'] === 'success' && !empty($chatResult['response']),
    "Cuplikan Respons: " . substr(strip_tags($chatResult['response']), 0, 75) . "..."
);

// 8. Uji Chatbot Service Node.js (POST /api/chat)
$chChat = curl_init('http://127.0.0.1:3001/api/chat');
curl_setopt($chChat, CURLOPT_RETURNTRANSFER, true);
curl_setopt($chChat, CURLOPT_POST, true);
curl_setopt($chChat, CURLOPT_POSTFIELDS, json_encode([
    'message' => 'Halo, kamu asisten apa?'
]));
curl_setopt($chChat, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($chChat, CURLOPT_TIMEOUT, 5);
$chatResp = curl_exec($chChat);
$chatHttp = curl_getinfo($chChat, CURLINFO_HTTP_CODE);
curl_close($chChat);

$chatOk = false;
if ($chatHttp === 200 && $chatResp) {
    $chatData = json_decode($chatResp, true);
    $chatOk = ($chatData['status'] === 'success' && !empty($chatData['response']));
}
assertTest(
    "Chatbot Service Node.js port 3001 berfungsi normal merespons percakapan",
    $chatOk,
    "HTTP Code: $chatHttp | Balasan: " . substr($chatData['response'] ?? '', 0, 75) . "..."
);

// 9. Uji Penyimpanan Konfigurasi Baru via AIConfigService (Sinkronisasi .env & Hot Reload)
$saveTest = $configService->saveConfig('gemini', 'AIzaSyDemoKeyTest12345', 'gemini-1.5-flash');
assertTest(
    "Penyimpanan konfigurasi berhasil dan memicu hot-reload",
    $saveTest['status'] === 'success' && $saveTest['hot_reload'] === true,
    "Status: " . $saveTest['status'] . " | Hot-Reload: " . ($saveTest['hot_reload'] ? 'Berhasil' : 'Gagal')
);

// 10. Uji Baca Ulang Konfigurasi Pasca Simpan
$reloadedConfig = $configService->getCurrentConfig();
assertTest(
    "Konfigurasi tersimpan sinkron di pembacaan runtime",
    $reloadedConfig['provider'] === 'gemini' && $reloadedConfig['model'] === 'gemini-1.5-flash',
    "Provider tersimpan: " . $reloadedConfig['provider'] . " | Model: " . $reloadedConfig['model']
);

// Kembalikan konfigurasi default aman
$configService->saveConfig('gemini', '', 'gemini-1.5-flash');

echo "\n=======================================================\n";
echo "HASIL PENGUJIAN: $passed LULUS, $failed GAGAL\n";
echo "=======================================================\n";
