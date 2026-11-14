<?php
/**
 * AiController
 * Modul Kecerdasan Buatan (AI) Sarana dan Prasarana STMIK IKMI Cirebon.
 * Mengelola:
 * 1. AI Facility Analysis (/ai/analysis)
 * 2. AI Maintenance Recommendation (/ai/recommendations)
 * 3. Log & Audit Trail AI (/ai atau /ai/logs)
 */

require_once __DIR__ . '/../services/AIService.php';
require_once __DIR__ . '/../services/ai/AIConfigService.php';

class AiController extends Controller {
    protected $aiService;

    public function __construct() {
        Auth::requireRole(['admin', 'petugas_sarpras']);
        $this->aiService = new AIService();
    }

    /**
     * Halaman Log & Audit Trail AI (Default /ai)
     */
    public function index() {
        $aiLogModel = $this->model('AiLogModel');
        $logs = $aiLogModel->getAllLogs(100);

        $data = [
            'title' => 'Log Analitik AI - ' . APP_NAME,
            'user' => Auth::user(),
            'role' => Auth::role(),
            'ai_logs' => $logs
        ];

        $this->renderView('ai/index', $data);
    }

    /**
     * Fitur 1: AI Facility Analysis (/ai/analysis)
     * Analisis komprehensif kondisi, umur, frekuensi kerusakan, dan pola anomali.
     */
    public function analysis() {
        $analysisResult = $this->aiService->getFacilityAnalysis();

        $data = [
            'title' => 'AI Facility Analysis - ' . APP_NAME,
            'user' => Auth::user(),
            'role' => Auth::role(),
            'analysis' => $analysisResult
        ];

        $this->renderView('ai/analysis', $data);
    }

    /**
     * Fitur 2: AI Maintenance Recommendation (/ai/recommendations)
     * Rekomendasi prioritas pemeliharaan (Tinggi, Sedang, Rendah) berbasis verifikasi teknisi.
     */
    public function recommendations() {
        $recommendations = $this->aiService->getMaintenanceRecommendations();

        $data = [
            'title' => 'AI Rekomendasi Pemeliharaan - ' . APP_NAME,
            'user' => Auth::user(),
            'role' => Auth::role(),
            'recommendations' => $recommendations
        ];

        $this->renderView('ai/recommendations', $data);
    }

    /**
     * Fitur 4: Konfigurasi AI Assistant & API Key (/ai/pengaturan)
     * Hanya dapat diakses oleh Administrator Sistem.
     */
    public function pengaturan() {
        Auth::requireRole('admin');

        $configService = new AIConfigService();
        $currentConfig = $configService->getCurrentConfig();
        $providers     = $configService->getSupportedProviders();

        $data = [
            'title'          => 'Pengaturan AI Assistant — ' . APP_NAME,
            'user'           => Auth::user(),
            'role'           => Auth::role(),
            'current_config' => $currentConfig,
            'providers'      => $providers,
            'flash_success'  => $_SESSION['flash_success'] ?? null,
            'flash_error'    => $_SESSION['flash_error'] ?? null
        ];

        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        $this->renderView('ai/pengaturan', $data);
    }

    /**
     * Endpoint Simpan Konfigurasi AI Assistant (POST /ai/save-pengaturan atau /ai/pengaturan)
     */
    public function savePengaturan() {
        Auth::requireRole('admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_validate()) {
            $_SESSION['flash_error'] = 'Token keamanan (CSRF) tidak valid atau telah kedaluwarsa.';
            redirect('ai/pengaturan');
            return;
        }

        $provider = trim($_POST['provider'] ?? 'gemini');
        $apiKey   = trim($_POST['api_key'] ?? '');
        $model    = trim($_POST['model'] ?? '');

        // Validasi Model Custom jika dipilih opsi 'custom'
        if ($model === 'custom' || empty($model)) {
            $customModel = trim($_POST['custom_model'] ?? '');
            if (!empty($customModel)) {
                $model = $customModel;
            }
        }

        $configService = new AIConfigService();
        $result = $configService->saveConfig($provider, $apiKey, $model);

        if ($result['status'] === 'success') {
            $_SESSION['flash_success'] = 'Konfigurasi AI Assistant berhasil disimpan! Model aktif: ' . htmlspecialchars($model);
        } else {
            $_SESSION['flash_error'] = 'Gagal menyimpan konfigurasi: ' . htmlspecialchars($result['message'] ?? 'Error tidak diketahui');
        }

        redirect('ai/pengaturan');
    }

    /**
     * Endpoint AJAX Uji Coba Koneksi API AI (POST /ai/test-connection)
     */
    public function testConnection() {
        Auth::requireRole('admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Metode request tidak diizinkan.']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $provider = $input['provider'] ?? ($_POST['provider'] ?? 'gemini');
        $apiKey   = $input['api_key'] ?? ($_POST['api_key'] ?? '');
        $model    = $input['model'] ?? ($_POST['model'] ?? '');

        if ($model === 'custom' || empty($model)) {
            $customModel = $input['custom_model'] ?? ($_POST['custom_model'] ?? '');
            if (!empty($customModel)) {
                $model = $customModel;
            }
        }

        $configService = new AIConfigService();
        $testResult = $configService->testConnection($provider, $apiKey, $model);

        header('Content-Type: application/json');
        echo json_encode($testResult);
        exit;
    }

    /**
     * Alias redirect ke AI Assistant
     */
    public function assistant() {
        header('Location: ' . base_url('ai-assistant'));
        exit;
    }

    /**
     * Helper render layout dashboard
     */
    private function renderView($viewName, $data) {
        $this->view('layouts/dashboard_header', $data);
        $this->view('layouts/sidebar', $data);
        $this->view('layouts/topbar', $data);
        $this->view($viewName, $data);
        $this->view('layouts/dashboard_footer', $data);
    }
}
