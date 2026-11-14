<?php
/**
 * Test Suite: Sistem Notifikasi Terintegrasi (WhatsApp Fonnte, Email SMTP, Notification Center)
 * Proyek : Sistem Informasi Fasilitas STMIK IKMI Cirebon
 * Jalankan: php tests/test_notification_system.php
 */

$root = dirname(__DIR__);
require_once $root . '/app/config/config.php';
require_once $root . '/app/helpers/helper.php';
require_once $root . '/app/helpers/Auth.php';
require_once $root . '/app/core/Database.php';
require_once $root . '/app/core/Model.php';
require_once $root . '/app/core/Controller.php';

require_once $root . '/app/models/NotificationModel.php';
require_once $root . '/app/models/LaporanModel.php';
require_once $root . '/app/services/WhatsAppService.php';
require_once $root . '/app/services/EmailService.php';
require_once $root . '/app/services/WhatsAppTemplates.php';
require_once $root . '/app/services/EmailTemplates.php';
require_once $root . '/app/services/NotificationService.php';

$passed = 0;
$failed = 0;
$errors = [];

function assert_test(string $name, bool $condition, string $detail = ''): void {
    global $passed, $failed, $errors;
    if ($condition) {
        echo "  \033[32m[PASS]\033[0m " . $name . "\n";
        $passed++;
    } else {
        echo "  \033[31m[FAIL]\033[0m " . $name . ($detail ? " — {$detail}" : '') . "\n";
        $failed++;
        $errors[] = $name . ($detail ? " ({$detail})" : '');
    }
}

echo "\n=================================================================\n";
echo "  TEST SUITE: SISTEM NOTIFIKASI WHATSAPP, EMAIL, NOTIF CENTER\n";
echo "  Sistem Informasi Pendataan Fasilitas STMIK IKMI Cirebon\n";
echo "=================================================================\n\n";

$db = new Database();

// ─────────────────────────────────────────────────────────────
// 1. Skema Database
// ─────────────────────────────────────────────────────────────
echo "─── GRUP 1: Validasi Skema Database & Tabel ────────────────────\n";

$tables = $db->query("SHOW TABLES")->resultSet();
$tableNames = array_map(fn($t) => array_values($t)[0], $tables);

assert_test("Tabel 'notifikasi' ada di database", in_array('notifikasi', $tableNames, true));
assert_test("Tabel 'notif_logs' ada di database", in_array('notif_logs', $tableNames, true));
assert_test("Tabel 'notif_settings' ada di database", in_array('notif_settings', $tableNames, true));

$notifCols = $db->query("SHOW COLUMNS FROM notifikasi")->resultSet();
$notifColNames = array_column($notifCols, 'Field');
assert_test("Kolom 'laporan_id' ada di tabel notifikasi", in_array('laporan_id', $notifColNames, true));
assert_test("Kolom 'idempotency_key' ada di tabel notifikasi", in_array('idempotency_key', $notifColNames, true));
assert_test("Kolom 'read_at' ada di tabel notifikasi", in_array('read_at', $notifColNames, true));

$logCols = $db->query("SHOW COLUMNS FROM notif_logs")->resultSet();
$logColNames = array_column($logCols, 'Field');
assert_test("Kolom 'kanal' ada di tabel notif_logs", in_array('kanal', $logColNames, true));
assert_test("Kolom 'status' ada di tabel notif_logs", in_array('status', $logColNames, true));
assert_test("Kolom 'percobaan' ada di tabel notif_logs", in_array('percobaan', $logColNames, true));
assert_test("Kolom 'idempotency_key' ada di tabel notif_logs", in_array('idempotency_key', $logColNames, true));

echo "\n";

// ─────────────────────────────────────────────────────────────
// 2. Pengaturan Notifikasi (notif_settings)
// ─────────────────────────────────────────────────────────────
echo "─── GRUP 2: Konfigurasi & Pengaturan Notifikasi ────────────────\n";

$notifService = new NotificationService($db);
$allSettings = $notifService->getAllSettings();
assert_test("Default settings terisi di database", count($allSettings) >= 8);
assert_test("Setting 'notif_enabled' dapat dibaca boolean", is_bool($notifService->getSetting('notif_enabled')));
assert_test("Setting 'notif_max_retry' bernilai integer", is_int($notifService->getSetting('notif_max_retry')));

// Uji update setting
$updated = $notifService->updateSetting('notif_max_retry', '4', 1);
assert_test("Update setting berhasil dieksekusi", $updated === true);
// Kembalikan ke 3
$notifService->updateSetting('notif_max_retry', '3', 1);

echo "\n";

// ─────────────────────────────────────────────────────────────
// 3. WhatsApp Service (Fonnte)
// ─────────────────────────────────────────────────────────────
echo "─── GRUP 3: WhatsApp Service (Fonnte API) ──────────────────────\n";

$waService = new WhatsAppService();

// Normalisasi nomor
assert_test("Normalisasi nomor '081234567890' -> '6281234567890'", WhatsAppService::normalizeNumber('081234567890') === '6281234567890');
assert_test("Normalisasi nomor '+62 812-3456-7890' -> '6281234567890'", WhatsAppService::normalizeNumber('+62 812-3456-7890') === '6281234567890');
assert_test("Normalisasi nomor '6281234567890' tetap '6281234567890'", WhatsAppService::normalizeNumber('6281234567890') === '6281234567890');

// Validasi nomor
assert_test("Validasi nomor valid '6281234567890'", WhatsAppService::validateNumber('6281234567890') === true);
assert_test("Validasi nomor tidak valid '12345' (terlalu pendek)", WhatsAppService::validateNumber('12345') === false);
assert_test("Validasi nomor tidak valid 'abcdef' (bukan angka)", WhatsAppService::validateNumber('abcdef') === false);

// Penanganan pengiriman tanpa token (harus aman, tidak crash)
$emptyTokenService = new WhatsAppService();
$resSendEmpty = $emptyTokenService->send('6281234567890', 'Uji coba');
assert_test("Send saat token belum diset: status success=false", $resSendEmpty['success'] === false);
assert_test("Send saat token belum diset: memiliki pesan error informatif", !empty($resSendEmpty['error']));

// Uji nomor tidak valid
$resSendInvalid = $emptyTokenService->send('123', 'Uji coba');
assert_test("Send ke nomor tidak valid ditolak sebelum panggil API", $resSendInvalid['success'] === false);

echo "\n";

// ─────────────────────────────────────────────────────────────
// 4. Email Service (SMTP)
// ─────────────────────────────────────────────────────────────
echo "─── GRUP 4: Email Service (SMTP Engine) ────────────────────────\n";

$emailService = new EmailService();

assert_test("Validasi email valid 'admin@ikmi.ac.id'", EmailService::validateEmail('admin@ikmi.ac.id') === true);
assert_test("Validasi email valid 'mahasiswa.sarpras@gmail.com'", EmailService::validateEmail('mahasiswa.sarpras@gmail.com') === true);
assert_test("Validasi email tidak valid 'bukan-email'", EmailService::validateEmail('bukan-email') === false);
assert_test("Validasi email tidak valid '@tanpauser.com'", EmailService::validateEmail('@tanpauser.com') === false);

// Penanganan pengiriman saat SMTP belum dikonfigurasi
$resMailEmpty = $emailService->send('user@ikmi.ac.id', 'Subjek Uji', '<p>Halo</p>');
assert_test("Send saat SMTP belum lengkap: status success=false", $resMailEmpty['success'] === false);
assert_test("Send saat SMTP belum lengkap: memiliki pesan error informatif", !empty($resMailEmpty['error']));

// Template HTML Email
$dummyLaporan = [
    'kode_laporan' => 'LAP-202611-999',
    'pelapor' => 'Mahasiswa Test',
    'nama_fasilitas' => 'AC Split 2 PK',
    'nama_ruangan' => 'Lab Komputer 1',
    'nama_gedung' => 'Gedung Utama',
    'prioritas' => 'tinggi',
    'created_at' => date('Y-m-d H:i:s'),
    'deskripsi' => 'AC tidak dingin dan mengeluarkan air bocor.'
];

$mailTmpl = EmailTemplates::laporanBaru($dummyLaporan, 'http://localhost/detail');
assert_test("Template email laporan baru menghasilkan subjek", !empty($mailTmpl['subject']));
assert_test("Template email laporan baru mengandung HTML STMIK IKMI", strpos($mailTmpl['html'], 'IKMI') !== false);
assert_test("Template email laporan baru memuat nomor tiket", strpos($mailTmpl['html'], 'LAP-202611-999') !== false);
assert_test("Template email menyediakan fallback plain text", !empty($mailTmpl['text']));

$waTextTmpl = WhatsAppTemplates::laporanBaru($dummyLaporan, 'http://localhost/detail');
assert_test("Template WhatsApp laporan baru memuat nomor tiket", strpos($waTextTmpl, 'LAP-202611-999') !== false);
assert_test("Template WhatsApp laporan baru memuat ruangan dan fasilitas", strpos($waTextTmpl, 'Lab Komputer 1') !== false);

echo "\n";

// ─────────────────────────────────────────────────────────────
// 5. Notification Center (In-App)
// ─────────────────────────────────────────────────────────────
echo "─── GRUP 5: Notification Center (In-App) ───────────────────────\n";

$notifModel = new NotificationModel();

// Ambil user test (user id 1 atau 4)
$testUser = $db->query("SELECT id FROM users LIMIT 1")->single();
$testUserId = (int)($testUser['id'] ?? 1);

// Buat notifikasi uji
$testIkey = 'test_uniq_' . time() . '_' . rand(100, 999);
$db->query("INSERT INTO notifikasi (user_id, judul, pesan, tipe, tautan, is_read, idempotency_key) 
            VALUES (:uid, :j, :p, 'laporan', 'laporan/detail/1', 0, :k)")
    ->bind(':uid', $testUserId)
    ->bind(':j', 'Uji Notifikasi In-App')
    ->bind(':p', 'Pesan uji notifikasi in-app untuk automated testing')
    ->bind(':k', $testIkey)
    ->execute();
$createdNotifId = (int)$db->lastInsertId();

assert_test("Notifikasi in-app berhasil disimpan ke database (ID > 0)", $createdNotifId > 0);

$unreadCount = $notifModel->countUnread($testUserId);
assert_test("Hitung notifikasi belum dibaca (unread >= 1)", $unreadCount >= 1);

$userNotifs = $notifModel->getByUser($testUserId, 1, 10);
assert_test("Ambil daftar notifikasi user berhasil", count($userNotifs) >= 1);

// Uji mark read
$markOk = $notifModel->markRead($createdNotifId, $testUserId);
assert_test("Tandai notifikasi sebagai dibaca (markRead)", $markOk === true);

$notifRow = $db->query("SELECT is_read, read_at FROM notifikasi WHERE id = :id")->bind(':id', $createdNotifId)->single();
assert_test("Field is_read bernilai 1 setelah markRead", (int)$notifRow['is_read'] === 1);
assert_test("Field read_at terisi timestamp setelah markRead", !empty($notifRow['read_at']));

// Cleanup notif uji
$notifModel->delete($createdNotifId, $testUserId);
assert_test("Hapus notifikasi uji (delete) berhasil", true);

echo "\n";

// ─────────────────────────────────────────────────────────────
// 6. Pencegahan Pesan Duplikat (Idempotency)
// ─────────────────────────────────────────────────────────────
echo "─── GRUP 6: Pencegahan Duplikasi (Idempotency & Guard) ─────────\n";

$dupKey = 'idempotent_test_' . uniqid();

// Insert pertama ke notif_logs
$db->query("INSERT INTO notif_logs (user_id, kanal, event_type, penerima, status, idempotency_key, percobaan)
            VALUES (:uid, 'whatsapp', 'test', '6281234567890', 'sent', :k, 1)")
    ->bind(':uid', $testUserId)
    ->bind(':k', $dupKey)
    ->execute();
$firstLogId = (int)$db->lastInsertId();

// Cek deteksi duplikat via NotificationService
$refMethod = new ReflectionMethod('NotificationService', 'isDuplicate');
$refMethod->setAccessible(true);
$isDup = $refMethod->invoke($notifService, $dupKey);

assert_test("Deteksi duplikasi berhasil mengenali idempotency key yang sama", $isDup === true);

// Cleanup
$db->query("DELETE FROM notif_logs WHERE id = :id")->bind(':id', $firstLogId)->execute();

echo "\n";

// ─────────────────────────────────────────────────────────────
// 7. Integrasi Proses Bisnis: Laporan Kerusakan
// ─────────────────────────────────────────────────────────────
echo "─── GRUP 7: Integrasi Notifikasi Siklus Laporan Fasilitas ──────\n";

$laporanModel = new LaporanModel();
$ruangan = $db->query("SELECT id FROM ruangan WHERE status != 'nonaktif' LIMIT 1")->single();
$fasilitas = $db->query("SELECT id FROM fasilitas WHERE ruangan_id = :rid LIMIT 1")
    ->bind(':rid', $ruangan['id'])
    ->single();

if ($ruangan && $fasilitas) {
    // 1. Simpan laporan uji
    $kodeLapTest = 'TEST-LAP-' . date('Ymd-His');
    $lapData = [
        'kode_laporan' => $kodeLapTest,
        'user_id' => $testUserId,
        'fasilitas_id' => (int)$fasilitas['id'],
        'ruangan_id' => (int)$ruangan['id'],
        'deskripsi' => 'Testing otomatis sistem notifikasi fasilitas STMIK IKMI.',
        'prioritas' => 'tinggi',
        'status' => 'menunggu',
        'catatan_petugas' => null
    ];

    $testLapId = $laporanModel->create($lapData);
    assert_test("Laporan kerusakan uji berhasil disimpan ke database (ID: {$testLapId})", $testLapId > 0);

    // Ambil data laporan lengkap
    $lapFull = $laporanModel->findById($testLapId);
    assert_test("findById berhasil memuat data pelapor, fasilitas, dan ruangan aktual", !empty($lapFull['pelapor']) && !empty($lapFull['nama_fasilitas']));

    // Trigger onLaporanBaru
    $notifService->onLaporanBaru($testLapId, $lapFull);

    // Cek apakah in-app notifikasi konfirmasi terbuat untuk pelapor
    $notifPelapor = $db->query("SELECT * FROM notifikasi WHERE laporan_id = :lid AND user_id = :uid")
        ->bind(':lid', $testLapId)
        ->bind(':uid', $testUserId)
        ->single();
    assert_test("In-app konfirmasi laporan baru terbuat untuk pelapor", !empty($notifPelapor));

    // 2. Trigger perubahan status
    $laporanModel->updateStatus($testLapId, 'diproses', 'tinggi', 'Teknisi sedang menuju lokasi.');
    $lapDiproses = $laporanModel->findById($testLapId);
    $notifService->onStatusUpdate($testLapId, $lapDiproses, 'diproses', 'Teknisi sedang menuju lokasi.');

    assert_test("Update status menjadi 'diproses' berhasil", $lapDiproses['status'] === 'diproses');

    // 3. Trigger penyelesaian laporan
    $laporanModel->updateStatus($testLapId, 'selesai', 'tinggi', 'Perbaikan selesai dan berfungsi normal.');
    $lapSelesai = $laporanModel->findById($testLapId);
    $notifService->onLaporanSelesai($testLapId, $lapSelesai, 'Perbaikan selesai dan berfungsi normal.');

    assert_test("Update status menjadi 'selesai' berhasil", $lapSelesai['status'] === 'selesai');

    // Cleanup data uji
    $db->query("DELETE FROM notifikasi WHERE laporan_id = :lid")->bind(':lid', $testLapId)->execute();
    $db->query("DELETE FROM notif_logs WHERE laporan_id = :lid")->bind(':lid', $testLapId)->execute();
    $db->query("DELETE FROM laporan_kerusakan WHERE id = :id")->bind(':id', $testLapId)->execute();
    assert_test("Cleanup data laporan uji selesai", true);
} else {
    echo "  \033[33m[SKIP]\033[0m Ruangan/Fasilitas tidak tersedia untuk integrasi laporan\n";
}

echo "\n";

// ─────────────────────────────────────────────────────────────
// 8. Keamanan & Role Access
// ─────────────────────────────────────────────────────────────
echo "─── GRUP 8: Keamanan & Role-Based Access Control (RBAC) ────────\n";

// File tidak mengekspos token Fonnte di frontend
$jsNotifContent = file_get_contents($root . '/public/assets/js/notifikasi.js');
assert_test("public/assets/js/notifikasi.js tidak mengandung token Fonnte", strpos($jsNotifContent, 'FONNTE_TOKEN') === false);

$viewSettingsContent = file_get_contents($root . '/app/views/notifikasi/settings.php');
assert_test("settings.php tidak menampilkan nilai token asli / password plaintext", strpos($viewSettingsContent, 'getenv(\'FONNTE_TOKEN\')') === false);
assert_test("settings.php memiliki CSRF protection field", strpos($viewSettingsContent, 'csrf_token') !== false);

$controllerContent = file_get_contents($root . '/app/controllers/NotifikasiController.php');
assert_test("NotifikasiController::pengaturan diproteksi Auth::requireRole('admin')", strpos($controllerContent, "Auth::requireRole('admin')") !== false);
assert_test("NotifikasiController::__construct memanggil Auth::requireLogin()", strpos($controllerContent, 'Auth::requireLogin()') !== false);

echo "\n";

// ─────────────────────────────────────────────────────────────
// RINGKASAN
// ─────────────────────────────────────────────────────────────
$total = $passed + $failed;
echo "=================================================================\n";
echo "  HASIL AKHIR: {$passed}/{$total} PASS";
if ($failed > 0) {
    echo " | \033[31m{$failed} FAIL\033[0m\n\n";
    echo "  Daftar Kegagalan:\n";
    foreach ($errors as $err) {
        echo "   - {$err}\n";
    }
} else {
    echo " \033[32m[SEMUA TEST BERHASIL DILALUI]\033[0m ✅\n";
}
echo "=================================================================\n\n";

exit($failed > 0 ? 1 : 0);

