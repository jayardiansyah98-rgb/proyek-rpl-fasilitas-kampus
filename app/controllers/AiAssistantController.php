<?php
/**
 * AiAssistantController
 * Controller untuk Fitur AI Assistant Kampus STMIK IKMI Cirebon.
 * Membantu Admin dan Petugas Sarpras mendapatkan informasi fasilitas via bahasa natural.
 * Akses: /ai-assistant
 */

require_once __DIR__ . '/../services/AIService.php';

class AiAssistantController extends Controller {
    protected $aiService;
    protected $logModel;

    public function __construct() {
        // Akses khusus Admin dan Petugas Sarpras
        Auth::requireRole(['admin', 'petugas_sarpras']);
        $this->aiService = new AIService();
        $this->logModel = $this->model('AiLogModel');
    }

    /**
     * Halaman Utama Chat Interface AI Assistant
     */
    public function index() {
        $user = Auth::user();
        $chatHistory = $this->logModel->getUserChatHistory((int)$user['id'], 20);

        // Contoh pertanyaan cepat yang bisa langsung diklik
        $quickPrompts = [
            'Berapa jumlah fasilitas yang rusak berat?',
            'Fasilitas apa yang paling banyak mengalami kerusakan?',
            'Berapa jumlah fasilitas di Gedung A?',
            'Ruangan mana yang memiliki fasilitas paling lengkap?',
            'Fasilitas apa yang perlu diprioritaskan untuk diperbaiki?',
            'Tampilkan kondisi fasilitas di Laboratorium Komputer 1.',
            'Rekomendasikan jadwal pemeliharaan fasilitas.',
            'Berapa total fasilitas yang berkondisi baik?'
        ];

        $data = [
            'title' => 'AI Assistant Sarpras - ' . APP_NAME,
            'user' => $user,
            'role' => Auth::role(),
            'chat_history' => $chatHistory,
            'quick_prompts' => $quickPrompts,
            'provider_name' => $this->aiService->getProvider()->getName()
        ];

        $this->view('layouts/dashboard_header', $data);
        $this->view('layouts/sidebar', $data);
        $this->view('layouts/topbar', $data);
        $this->view('ai/assistant', $data);
        $this->view('layouts/dashboard_footer', $data);
    }

    /**
     * Endpoint API untuk memproses pertanyaan user via AJAX
     */
    public function ask() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Metode request tidak diizinkan.']);
            exit;
        }

        // Ambil input dari JSON payload atau POST form
        $input = json_decode(file_get_contents('php://input'), true);
        $question = $input['question'] ?? ($_POST['question'] ?? '');
        $question = trim($question);

        if (empty($question)) {
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'error',
                'message' => 'Pertanyaan tidak boleh kosong.'
            ]);
            exit;
        }

        $userId = (int)Auth::id();
        $result = $this->aiService->generateResponse($question, $userId);

        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    /**
     * Endpoint menghapus riwayat percakapan pengguna saat ini
     */
    public function clearHistory() {
        $userId = (int)Auth::id();
        $this->logModel->clearUserChat($userId);

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Riwayat obrolan berhasil dibersihkan.']);
            exit;
        }

        header('Location: ' . base_url('ai-assistant'));
        exit;
    }

    public function analysis() {
        header('Location: ' . base_url('ai/analysis'));
        exit;
    }

    public function recommendations() {
        header('Location: ' . base_url('ai/recommendations'));
        exit;
    }
}
