<?php
/**
 * NotifikasiController
 * Sistem Informasi Fasilitas STMIK IKMI Cirebon
 *
 * Mengelola Notification Center (daftar, tandai baca, hapus)
 * dan Halaman Pengaturan Notifikasi (khusus Admin).
 */

require_once ROOT_PATH . '/app/services/NotificationService.php';
require_once ROOT_PATH . '/app/services/WhatsAppService.php';
require_once ROOT_PATH . '/app/services/EmailService.php';

class NotifikasiController extends Controller {

    protected $notifModel;
    protected $notifService;

    public function __construct() {
        Auth::requireLogin();
        $this->notifModel   = $this->model('NotificationModel');
        $db                 = new Database();
        $this->notifService = new NotificationService($db);
    }

    // ─────────────────────────────────────────────────────────
    // Notification Center — Semua Role
    // ─────────────────────────────────────────────────────────

    /**
     * Halaman daftar semua notifikasi pengguna
     */
    public function index() {
        $user    = Auth::user();
        $role    = Auth::role();
        $userId  = (int)$user['id'];

        $tipe   = isset($_GET['tipe']) && in_array($_GET['tipe'], ['info','laporan','pemeliharaan','sistem','whatsapp','email']) ? $_GET['tipe'] : null;
        $page   = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 20;

        $total  = $this->notifModel->countByUser($userId, $tipe);
        $pages  = $total > 0 ? (int)ceil($total / $perPage) : 1;
        $notifs = $this->notifModel->getByUser($userId, $page, $perPage, $tipe);
        $unread = $this->notifModel->countUnread($userId);

        $data = [
            'title'       => 'Pusat Notifikasi — ' . APP_NAME,
            'user'        => $user,
            'role'        => $role,
            'notifs'      => $notifs,
            'unread'      => $unread,
            'tipe_filter' => $tipe,
            'page'        => $page,
            'per_page'    => $perPage,
            'total'       => $total,
            'total_pages' => $pages,
        ];

        $this->view('layouts/dashboard_header', $data);
        $this->view('layouts/sidebar', $data);
        $this->view('layouts/topbar', $data);
        $this->view('notifikasi/index', $data);
        $this->view('layouts/dashboard_footer', $data);
    }

    /**
     * AJAX: Tandai satu notifikasi sebagai dibaca
     */
    public function markRead($id = null) {
        $this->requireAjax();
        $userId = (int)Auth::id();
        if (!$id || !is_numeric($id)) {
            $this->jsonResponse(['success' => false, 'message' => 'ID tidak valid.']);
        }
        $ok = $this->notifModel->markRead((int)$id, $userId);
        $unread = $this->notifModel->countUnread($userId);
        $this->jsonResponse(['success' => $ok, 'unread' => $unread]);
    }

    /**
     * AJAX: Tandai semua notifikasi sebagai dibaca
     */
    public function markAllRead() {
        $this->requireAjax();
        if (!csrf_validate()) {
            $this->jsonResponse(['success' => false, 'message' => 'Token CSRF tidak valid.']);
        }
        $userId = (int)Auth::id();
        $ok     = $this->notifModel->markAllRead($userId);
        $this->jsonResponse(['success' => $ok, 'unread' => 0]);
    }

    /**
     * AJAX: Hapus satu notifikasi (hanya milik sendiri)
     */
    public function hapus($id = null) {
        $this->requireAjax();
        if (!csrf_validate()) {
            $this->jsonResponse(['success' => false, 'message' => 'Token CSRF tidak valid.']);
        }
        $userId = (int)Auth::id();
        if (!$id || !is_numeric($id)) {
            $this->jsonResponse(['success' => false, 'message' => 'ID tidak valid.']);
        }
        $ok = $this->notifModel->delete((int)$id, $userId);
        $this->jsonResponse(['success' => $ok]);
    }

    /**
     * AJAX: Polling jumlah unread dan daftar terbaru (untuk bell dropdown)
     */
    public function poll() {
        header('Content-Type: application/json; charset=utf-8');
        if (!Auth::check()) {
            echo json_encode(['unread' => 0, 'items' => []]);
            exit;
        }

        $userId  = (int)Auth::id();
        $unread  = $this->notifModel->countUnread($userId);
        $latest  = $this->notifModel->getLatest($userId, 8);

        // Hitung waktu relatif
        foreach ($latest as &$n) {
            $n['waktu_relatif'] = $this->timeAgo($n['created_at']);
        }

        echo json_encode(['unread' => $unread, 'items' => $latest]);
        exit;
    }

    // ─────────────────────────────────────────────────────────
    // Pengaturan Notifikasi — Hanya Admin
    // ─────────────────────────────────────────────────────────

    /**
     * Halaman pengaturan notifikasi (CRUD settings + riwayat log)
     */
    public function pengaturan() {
        Auth::requireRole('admin');

        $user = Auth::user();
        $role = Auth::role();

        $page    = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $kanal   = $_GET['kanal'] ?? null;
        $status  = $_GET['status'] ?? null;
        $perPage = 25;

        $settings    = $this->notifModel->getSettings();
        $logTotal    = $this->notifModel->countLogs($kanal, $status);
        $logPages    = $logTotal > 0 ? (int)ceil($logTotal / $perPage) : 1;
        $logs        = $this->notifModel->getLogs($page, $perPage, $kanal, $status);
        $logStats    = $this->notifModel->getLogStats();
        $waConfig    = (new WhatsAppService())->isConfigured();
        $emailConfig = (new EmailService())->isConfigured();

        $data = [
            'title'        => 'Pengaturan Notifikasi — ' . APP_NAME,
            'user'         => $user,
            'role'         => $role,
            'settings'     => $settings,
            'logs'         => $logs,
            'log_stats'    => $logStats,
            'log_total'    => $logTotal,
            'log_pages'    => $logPages,
            'page'         => $page,
            'per_page'     => $perPage,
            'kanal_filter' => $kanal,
            'status_filter'=> $status,
            'wa_configured'=> $waConfig,
            'email_configured' => $emailConfig,
        ];

        $this->view('layouts/dashboard_header', $data);
        $this->view('layouts/sidebar', $data);
        $this->view('layouts/topbar', $data);
        $this->view('notifikasi/settings', $data);
        $this->view('layouts/dashboard_footer', $data);
    }

    /**
     * POST: Simpan perubahan pengaturan notifikasi
     */
    public function savePengaturan() {
        Auth::requireRole('admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_validate()) {
            $_SESSION['flash_error'] = 'Token keamanan tidak valid.';
            redirect('notifikasi/pengaturan');
            return;
        }

        $userId   = (int)Auth::id();

        // 1. Simpan toggle boolean (jika uncheck di form HTML, tidak dikirim di $_POST maka bernilai '0')
        $booleanKeys = [
            'notif_enabled', 'whatsapp_enabled', 'email_enabled',
            'notif_laporan_baru', 'notif_status_update', 'notif_selesai', 'notif_ditolak'
        ];

        foreach ($booleanKeys as $bKey) {
            $val = isset($_POST[$bKey]) ? '1' : '0';
            $this->notifService->updateSetting($bKey, $val, $userId);
        }

        // 2. Simpan setting lainnya
        $otherKeys = ['notif_max_retry', 'notif_retry_delay_min', 'wa_notif_tambahan', 'email_notif_tambahan'];
        foreach ($otherKeys as $oKey) {
            if (!isset($_POST[$oKey])) continue;
            $val = $_POST[$oKey];
            if (in_array($oKey, ['notif_max_retry', 'notif_retry_delay_min'])) {
                $val = (string)max(1, (int)$val);
            } elseif (in_array($oKey, ['wa_notif_tambahan', 'email_notif_tambahan'])) {
                $val = $this->sanitizeContactList($val, $oKey === 'email_notif_tambahan');
            }
            $this->notifService->updateSetting($oKey, $val, $userId);
        }

        $_SESSION['flash_success'] = 'Pengaturan notifikasi berhasil disimpan.';
        redirect('notifikasi/pengaturan');
    }

    /**
     * POST: Kirim WhatsApp uji coba
     */
    public function testWhatsapp() {
        Auth::requireRole('admin');
        $this->requireAjax();

        if (!csrf_validate()) {
            $this->jsonResponse(['success' => false, 'message' => 'Token CSRF tidak valid.']);
        }

        $nomor = trim($_POST['nomor'] ?? '');
        $pesan = trim($_POST['pesan'] ?? 'Ini adalah pesan uji coba dari Sistem Informasi Fasilitas STMIK IKMI Cirebon.');

        if (empty($nomor)) {
            $this->jsonResponse(['success' => false, 'message' => 'Nomor WhatsApp tidak boleh kosong.']);
        }

        $result = $this->notifService->testWhatsApp($nomor, $pesan);
        $this->jsonResponse([
            'success' => $result['success'],
            'message' => $result['success']
                ? 'Pesan WhatsApp berhasil dikirim ke ' . WhatsAppService::normalizeNumber($nomor) . '.'
                : ($result['error'] ?? 'Pengiriman gagal.'),
        ]);
    }

    /**
     * POST: Kirim Email uji coba
     */
    public function testEmail() {
        Auth::requireRole('admin');
        $this->requireAjax();

        if (!csrf_validate()) {
            $this->jsonResponse(['success' => false, 'message' => 'Token CSRF tidak valid.']);
        }

        $alamat = trim($_POST['email'] ?? '');
        $subjek = trim($_POST['subjek'] ?? '[TEST] Notifikasi Email — Sistem Fasilitas IKMI');
        $pesan  = trim($_POST['pesan']  ?? 'Ini adalah email uji coba dari Sistem Informasi Fasilitas STMIK IKMI Cirebon.');

        if (empty($alamat)) {
            $this->jsonResponse(['success' => false, 'message' => 'Alamat email tidak boleh kosong.']);
        }

        $result = $this->notifService->testEmail($alamat, $subjek, $pesan);
        $this->jsonResponse([
            'success' => $result['success'],
            'message' => $result['success']
                ? 'Email berhasil dikirim ke ' . htmlspecialchars($alamat) . '.'
                : ($result['error'] ?? 'Pengiriman gagal.'),
        ]);
    }

    /**
     * POST: Test koneksi API Fonnte
     */
    public function testFonnteConnection() {
        Auth::requireRole('admin');
        $this->requireAjax();
        $result = $this->notifService->testFonnteConnection();
        $this->jsonResponse($result);
    }

    /**
     * POST: Test koneksi SMTP
     */
    public function testSmtpConnection() {
        Auth::requireRole('admin');
        $this->requireAjax();
        $result = $this->notifService->testSmtpConnection();
        $this->jsonResponse($result);
    }

    /**
     * POST: Proses retry notifikasi yang gagal
     */
    public function retry() {
        Auth::requireRole(['admin', 'petugas_sarpras']);

        if (!csrf_validate()) {
            $_SESSION['flash_error'] = 'Token keamanan tidak valid.';
            redirect('notifikasi/pengaturan');
            return;
        }

        $processed = $this->notifService->processRetry();
        $_SESSION['flash_success'] = "Proses retry selesai. {$processed} notifikasi diproses ulang.";
        redirect('notifikasi/pengaturan');
    }

    // ─────────────────────────────────────────────────────────
    // Private Helpers
    // ─────────────────────────────────────────────────────────

    private function requireAjax(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !isset($_GET['ajax'])) {
            // Allow GET for poll
        }
        header('Content-Type: application/json; charset=utf-8');
    }

    private function jsonResponse(array $data): never {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    private function timeAgo(string $datetime): string {
        $time = time() - strtotime($datetime);
        if ($time < 60)   return 'Baru saja';
        if ($time < 3600) return floor($time / 60) . ' mnt lalu';
        if ($time < 86400)return floor($time / 3600) . ' jam lalu';
        if ($time < 604800) return floor($time / 86400) . ' hari lalu';
        return date('d M Y', strtotime($datetime));
    }

    private function sanitizeContactList(string $raw, bool $isEmail): string {
        $items = json_decode($raw, true);
        if (!is_array($items)) {
            // Coba parse sebagai newline-separated
            $items = array_filter(array_map('trim', explode("\n", $raw)));
        }
        $valid = [];
        foreach ($items as $item) {
            $item = trim($item);
            if ($isEmail && EmailService::validateEmail($item)) {
                $valid[] = $item;
            } elseif (!$isEmail && WhatsAppService::validateNumber(WhatsAppService::normalizeNumber($item))) {
                $valid[] = WhatsAppService::normalizeNumber($item);
            }
        }
        return json_encode(array_values(array_unique($valid)));
    }
}

