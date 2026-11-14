<?php
/**
 * Test Suite - Verifikasi Penempatan Chatbot Widget
 * Task B: Pindah chatbot dari Beranda publik ke Dashboard (semua role)
 *
 * Jalankan: php tests/test_chatbot_placement.php
 */

$passed = 0;
$failed = 0;
$errors = [];

function assert_test(string $name, bool $condition, string $message = ''): void {
    global $passed, $failed, $errors;
    if ($condition) {
        echo "\033[32m  [PASS]\033[0m $name\n";
        $passed++;
    } else {
        echo "\033[31m  [FAIL]\033[0m $name" . ($message ? " — $message" : '') . "\n";
        $failed++;
        $errors[] = $name;
    }
}

$rootPath = dirname(__DIR__);

echo "\n========================================================\n";
echo "  TEST SUITE: Chatbot Widget Placement\n";
echo "  Proyek: Sistem Informasi Fasilitas STMIK IKMI Cirebon\n";

echo "========================================================\n\n";

// ─── GRUP 1: Keberadaan File ──────────────────────────────────
echo "─── GRUP 1: Keberadaan File ───────────────────────────\n";

$homeIndexPath     = $rootPath . '/app/views/home/index.php';
$dashFooterPath    = $rootPath . '/app/views/layouts/dashboard_footer.php';
$widgetPath        = $rootPath . '/app/views/home/chatbot_widget.php';
$chatbotJsPath     = $rootPath . '/public/assets/js/chatbot.js';
$serverJsPath      = $rootPath . '/chatbot-service/server.js';

assert_test('home/index.php ada',           file_exists($homeIndexPath));
assert_test('dashboard_footer.php ada',     file_exists($dashFooterPath));
assert_test('chatbot_widget.php ada',       file_exists($widgetPath));
assert_test('chatbot.js ada',               file_exists($chatbotJsPath));
assert_test('chatbot-service/server.js ada', file_exists($serverJsPath));

echo "\n";

// ─── GRUP 2: Beranda Publik TIDAK Ada Chatbot ─────────────────
echo "─── GRUP 2: Beranda Publik — Chatbot TIDAK Muncul ────\n";

$homeContent = file_get_contents($homeIndexPath);

assert_test(
    'home/index.php TIDAK mengandung chatbot_widget',
    strpos($homeContent, 'chatbot_widget') === false,
    'Chatbot masih ada di beranda publik!'
);
assert_test(
    'home/index.php TIDAK mengandung chatbot.js script tag',
    !preg_match('/<script[^>]+chatbot\.js/i', $homeContent),
    'Script chatbot.js masih di-load di beranda!'
);
assert_test(
    'home/index.php TIDAK mengandung chatbot-toggle-btn',
    strpos($homeContent, 'chatbot-toggle-btn') === false,
    'Tombol chatbot masih ada di beranda!'
);
assert_test(
    'home/index.php TIDAK mengandung chatbot-panel',
    strpos($homeContent, 'chatbot-panel') === false,
    'Panel chatbot masih ada di beranda!'
);

echo "\n";

// ─── GRUP 3: Dashboard Footer ADA Chatbot ────────────────────
echo "─── GRUP 3: Dashboard Footer — Chatbot ADA ───────────\n";

$footerContent = file_get_contents($dashFooterPath);

assert_test(
    'dashboard_footer.php mengandung Auth::check()',
    strpos($footerContent, 'Auth::check()') !== false,
    'Guard Auth::check() hilang!'
);
assert_test(
    'dashboard_footer.php mengandung require chatbot_widget.php',
    strpos($footerContent, 'chatbot_widget.php') !== false,
    'require_once chatbot_widget.php hilang!'
);
assert_test(
    'dashboard_footer.php mengandung script chatbot.js',
    preg_match('/chatbot\.js/i', $footerContent) === 1,
    'Script chatbot.js tidak di-load di dashboard!'
);
assert_test(
    'dashboard_footer.php diakhiri </body> dan </html>',
    preg_match('/<\/body>\s*<\/html>\s*$/si', $footerContent) === 1,
    'Tag penutup tidak ditemukan!'
);
assert_test(
    'dashboard_footer.php chatbot di-load HANYA SEKALI (tidak duplikat)',
    substr_count($footerContent, 'chatbot_widget.php') === 1,
    'chatbot_widget.php di-require lebih dari sekali!'
);
$chatbotScriptTagCount = preg_match_all('/<script[^>]+chatbot\.js/i', $footerContent);
assert_test(
    'dashboard_footer.php chatbot.js di-load HANYA SEKALI (tidak duplikat tag <script>)',
    $chatbotScriptTagCount === 1,
    "Ditemukan $chatbotScriptTagCount tag <script chatbot.js> — harusnya 1!"
);

echo "\n";

// ─── GRUP 4: Verifikasi chatbot_widget.php ────────────────────
echo "─── GRUP 4: chatbot_widget.php — Konten Valid ─────────\n";

$widgetContent = file_get_contents($widgetPath);

assert_test(
    'Widget mengandung chatbot-toggle-btn',
    strpos($widgetContent, 'chatbot-toggle-btn') !== false,
    'Tombol toggle tidak ada!'
);
assert_test(
    'Widget mengandung chatbot-panel',
    strpos($widgetContent, 'chatbot-panel') !== false,
    'Panel chatbot tidak ada!'
);
assert_test(
    'Widget mengandung data-session-id',
    strpos($widgetContent, 'data-session-id') !== false,
    'Session ID attribute tidak ada!'
);
assert_test(
    'Widget menggunakan Auth::check() untuk session ID',
    strpos($widgetContent, 'Auth::check()') !== false,
    'Auth::check() tidak ada di widget!'
);
assert_test(
    'Widget mengandung tombol reset/refresh percakapan',
    strpos($widgetContent, 'reset') !== false
        || strpos($widgetContent, 'Reset') !== false
        || strpos($widgetContent, 'refresh') !== false
        || strpos($widgetContent, 'Refresh') !== false,
    'Tombol reset/refresh tidak ada!'
);
assert_test(
    'Widget mengandung character counter',
    strpos($widgetContent, 'char') !== false || strpos($widgetContent, 'counter') !== false,
    'Character counter tidak ada!'
);

echo "\n";

// ─── GRUP 5: Verifikasi chatbot.js ──────────────────────────
echo "─── GRUP 5: chatbot.js — Fungsi Kunci ─────────────────\n";

$jsContent = file_get_contents($chatbotJsPath);

assert_test(
    'chatbot.js mengandung getChatSessionId()',
    strpos($jsContent, 'getChatSessionId') !== false,
    'Fungsi getChatSessionId tidak ada!'
);
assert_test(
    'chatbot.js mengandung chatSessionId dalam sendMessage',
    strpos($jsContent, 'chatSessionId') !== false,
    'chatSessionId tidak dikirim ke backend!'
);
assert_test(
    'chatbot.js tidak mengandung API key hardcoded',
    !preg_match('/AIza[0-9A-Za-z\-_]{35}/i', $jsContent),
    'API KEY GEMINI TEREKSPOS DI FRONTEND!'
);
assert_test(
    'chatbot.js mengandung auto-scroll',
    strpos($jsContent, 'scrollTop') !== false || strpos($jsContent, 'scrollIntoView') !== false,
    'Auto-scroll tidak ada!'
);

echo "\n";

// ─── GRUP 6: Verifikasi Dashboard View Files (tidak duplikasi) ──
echo "─── GRUP 6: Dashboard Views — Tidak Duplikasi JS ──────\n";

$dashViews = [
    'admin'    => $rootPath . '/app/views/dashboard/admin.php',
    'petugas'  => $rootPath . '/app/views/dashboard/petugas.php',
    'pengguna' => $rootPath . '/app/views/dashboard/pengguna.php',
];

foreach ($dashViews as $role => $viewPath) {
    if (!file_exists($viewPath)) {
        // Coba path alternatif
        $altPaths = glob($rootPath . '/app/views/dashboard/*' . $role . '*.php');
        if (!empty($altPaths)) {
            $viewPath = $altPaths[0];
        }
    }

    if (file_exists($viewPath)) {
        $viewContent = file_get_contents($viewPath);
        $basename = basename($viewPath);
        assert_test(
            "dashboard/$basename TIDAK meng-include chatbot_widget secara terpisah",
            strpos($viewContent, 'chatbot_widget') === false,
            "Duplikasi chatbot di $basename!"
        );
        assert_test(
            "dashboard/$basename TIDAK memuat chatbot.js secara terpisah",
            !preg_match('/<script[^>]+chatbot\.js/i', $viewContent),
            "Duplikasi chatbot.js di $basename!"
        );
    } else {
        echo "\033[33m  [SKIP]\033[0m dashboard/$role.php tidak ditemukan — skip\n";
    }
}

echo "\n";

// ─── GRUP 7: Verifikasi server.js Node.js ────────────────────
echo "─── GRUP 7: server.js — Fitur Keamanan & Konteks ──────\n";

$serverContent = file_get_contents($serverJsPath);

assert_test(
    'server.js mengandung conversationStore (in-memory session)',
    strpos($serverContent, 'conversationStore') !== false,
    'In-memory conversation store tidak ada!'
);
assert_test(
    'server.js mengandung follow-up detector',
    strpos($serverContent, 'followUp') !== false || strpos($serverContent, 'follow') !== false,
    'Follow-up detector tidak ada!'
);
assert_test(
    'server.js mengandung getFacilityList',
    strpos($serverContent, 'getFacilityList') !== false,
    'Fungsi getFacilityList tidak ada!'
);
assert_test(
    'server.js mengandung validasi role/sessionId',
    strpos($serverContent, 'sessionId') !== false,
    'Validasi sessionId tidak ada!'
);
assert_test(
    'server.js tidak mengekspos API key di response',
    !preg_match('/res\.json.*AIza/i', $serverContent),
    'API key terekspos di response!'
);

echo "\n";

// ─── RINGKASAN ──────────────────────────────────────────────
$total = $passed + $failed;
echo "========================================================\n";
echo "  HASIL: $passed/$total PASS";
if ($failed > 0) {
    echo " | $failed FAIL\n";
    echo "\n  Test yang gagal:\n";
    foreach ($errors as $e) {
        echo "    - $e\n";
    }
} else {
    echo " ✅\n";
}
echo "========================================================\n\n";

exit($failed > 0 ? 1 : 0);
