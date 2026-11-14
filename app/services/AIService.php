<?php
/**
 * AIService
 * Service Layer Utama untuk Fitur AI Assistant Kampus STMIK IKMI Cirebon.
 * 
 * Mengimplementasikan arsitektur aman:
 * User Question -> AI/Intent Processing -> Query yang Diizinkan -> Database -> Result -> AI Menyusun Jawaban -> User
 * 
 * Metode Utama:
 * ├── generateResponse()
 * ├── analyzeFacility()
 * └── recommendMaintenance()
 */

require_once __DIR__ . '/ai/AIProviderInterface.php';
require_once __DIR__ . '/ai/RuleBasedEngine.php';
require_once __DIR__ . '/ai/GeminiProvider.php';
require_once __DIR__ . '/ai/OpenAICompatibleProvider.php';
require_once __DIR__ . '/ai/FacilityDataRetriever.php';
require_once __DIR__ . '/ai/IntentResolver.php';
require_once __DIR__ . '/ai/AIFacilityAnalyzer.php';
require_once __DIR__ . '/ai/AIMaintenanceRecommender.php';
require_once __DIR__ . '/../models/AiLogModel.php';

class AIService {
    protected $provider;
    protected $retriever;
    protected $intentResolver;
    protected $logModel;
    protected $facilityAnalyzer;
    protected $maintenanceRecommender;

    public function __construct(?AIProviderInterface $customProvider = null) {
        $this->retriever = new FacilityDataRetriever();
        $this->intentResolver = new IntentResolver();
        $this->logModel = new AiLogModel();
        $this->facilityAnalyzer = new AIFacilityAnalyzer();
        $this->maintenanceRecommender = new AIMaintenanceRecommender();

        if ($customProvider !== null) {
            $this->provider = $customProvider;
        } else {
            // Resolusi konfigurasi real-time dari AIConfigService (.env)
            require_once __DIR__ . '/ai/AIConfigService.php';
            $configService = new AIConfigService();
            $currConfig = $configService->getCurrentConfig();
            $providerType = strtolower($currConfig['provider'] ?? 'gemini');
            $apiKey = $currConfig['api_key'] ?? '';
            $model = $currConfig['model'] ?? '';

            if ($providerType === 'gemini') {
                $this->provider = new GeminiProvider($apiKey, $model);
            } elseif (in_array($providerType, ['deepseek', 'openrouter', 'claude', 'openai', 'nvidia'], true)) {
                $this->provider = new OpenAICompatibleProvider($providerType, $apiKey, $model);
            } else {
                $this->provider = new RuleBasedEngine();
            }
        }
    }

    /**
     * 1. Menghasilkan jawaban cerdas dari pertanyaan bahasa natural pengguna
     * 
     * @param string $question Pertanyaan pengguna
     * @param int $userId ID pengguna yang sedang login
     * @return array Hasil respons, waktu eksekusi, dan metadata
     */
    public function generateResponse(string $question, int $userId): array {
        $startTime = microtime(true);
        $cleanQuestion = trim($question);

        if (empty($cleanQuestion)) {
            return [
                'status' => 'error',
                'question' => '',
                'response' => 'Pertanyaan tidak boleh kosong. Silakan ketik pertanyaan mengenai fasilitas kampus.',
                'intent' => 'EMPTY',
                'execution_time_ms' => 0
            ];
        }

        // Tahap 1: Intent Processing & Entity Extraction
        $intentInfo = $this->intentResolver->resolve($cleanQuestion);
        $intent = $intentInfo['intent'];
        $method = $intentInfo['method'];
        $params = $intentInfo['params'];
        $entity = $intentInfo['entity'] ?? null;

        // Tahap 2 & 3: Whitelisted Query Execution ke Database
        // HANYA memanggil method yang ada di FacilityDataRetriever
        if (!method_exists($this->retriever, $method)) {
            $method = 'getTotalFacilityStats';
            $params = [];
        }

        $dbResult = call_user_func_array([$this->retriever, $method], $params);

        // Tahap 4: AI Menyusun Jawaban Berdasarkan Fakta Database
        $contextData = [
            'intent' => $intent,
            'entity' => $entity,
            'data' => $dbResult
        ];

        $systemPrompt = "Anda adalah Asisten Virtual Cerdas Sarana & Prasarana STMIK IKMI Cirebon. " .
                        "Tugas Anda adalah memberikan jawaban yang ramah, sopan, faktual, dan presisi mengenai fasilitas kampus. " .
                        "Untuk pertanyaan faktual mengenai JUMLAH/ANGKA, jawablah secara SINGKAT, JELAS, LANGSUNG KE INTI dalam satu kalimat pendek tanpa salam pembuka atau penjelasan yang tidak diminta. " .
                        "Jika pengguna meminta rincian atau penjelasan (misalnya daftar ruangan yang tersedia), baru berikan daftar atau penjelasan lengkap. " .
                        "DILARANG menyamakan total jenis fasilitas dengan total unit fisik fasilitas. " .
                        "DILARANG mengarang angka atau informasi di luar data yang diberikan.";

        if ($intent === 'IDENTITY_AI_MODEL') {
            $provName = $this->provider->getName();
            $responseAnswer = "Halo! Saya adalah **Asisten Fasilitas IKMI** untuk Sistem Informasi Pendataan Fasilitas Ruangan Kampus STMIK IKMI Cirebon.\n\n" .
                "Saat ini sistem saya ditenagai oleh model kecerdasan buatan: **{$provName}**.\n\n" .
                "Saya bertugas menjawab informasi seputar data gedung, ruangan, inventaris fasilitas, dan pemeliharaan kampus secara faktual langsung dari database.";
        } else {
            $responseAnswer = $this->provider->generateAnswer($systemPrompt, $cleanQuestion, $contextData);
        }

        $elapsedMs = (int)round((microtime(true) - $startTime) * 1000);

        // Tahap 5: Logging Interaksi
        $this->logModel->record([
            'user_id' => $userId,
            'question' => $cleanQuestion,
            'response' => $responseAnswer,
            'fitur' => 'ai_assistant',
            'metadata_json' => json_encode([
                'intent' => $intent,
                'method' => $method,
                'entity' => $entity,
                'provider' => $this->provider->getName()
            ]),
            'status' => 'sukses',
            'waktu_eksekusi_ms' => $elapsedMs
        ]);

        return [
            'status' => 'success',
            'question' => $cleanQuestion,
            'response' => $responseAnswer,
            'intent' => $intent,
            'entity' => $entity,
            'provider' => $this->provider->getName(),
            'execution_time_ms' => $elapsedMs,
            'created_at' => date('Y-m-d H:i:s'),
            'timestamp' => date('d M Y H:i')
        ];
    }

    /**
     * 2. Analisis mendalam suatu fasilitas tertentu (Kondisi, riwayat rusak & biaya)
     * 
     * @param int $facilityId ID fasilitas
     * @return array
     */
    public function analyzeFacility(int $facilityId): array {
        $detail = $this->retriever->analyzeFacilityDetail($facilityId);

        if (!$detail) {
            return [
                'status' => 'not_found',
                'message' => 'Fasilitas dengan ID tersebut tidak ditemukan.'
            ];
        }

        $f = $detail['fasilitas'];
        $kondisi = ucfirst(str_replace('_', ' ', $f['kondisi']));
        $tahun = $f['tahun_pengadaan'];
        $usiaTahun = max(0, (int)date('Y') - (int)$tahun);

        $analisis = "Fasilitas **{$f['nama']}** (Kode: `{$f['kode_inventaris']}`) berlokasi di **{$f['nama_ruangan']} ({$f['nama_gedung']})**.\n\n" .
                    "• Kondisi Saat Ini: **{$kondisi}**\n" .
                    "• Usia Pakai: Sekitar {$usiaTahun} tahun (Pengadaan tahun {$tahun})\n" .
                    "• Total Riwayat Laporan Kerusakan: **{$detail['total_laporan']} tiket**\n" .
                    "• Total Tindakan Pemeliharaan: **{$detail['total_pemeliharaan']} kali servis**\n" .
                    "• Akumulasi Biaya Pemeliharaan: **Rp " . number_format($detail['total_biaya_perbaikan'], 0, ',', '.') . "**\n\n";

        if ($detail['total_laporan'] >= 3 || $f['kondisi'] === 'rusak_berat') {
            $analisis .= "**Kesimpulan Analisis:** Fasilitas ini memiliki frekuensi kerusakan tinggi atau berada dalam kondisi rusak berat. Disarankan untuk segera dilakukan penggantian unit atau evaluasi afkir.";
        } else {
            $analisis .= "**Kesimpulan Analisis:** Fasilitas ini masih dalam batas operasional wajar dan layak digunakan untuk aktivitas perkuliahan / operasional kampus.";
        }

        return [
            'status' => 'success',
            'fasilitas_id' => $facilityId,
            'detail_data' => $detail,
            'analisis_text' => $analisis
        ];
    }

    /**
     * 3. Rekomendasi pemeliharaan fasilitas terjadwal & preventif
     * 
     * @return array
     */
    public function recommendMaintenance(): array {
        $recommendations = $this->retriever->recommendMaintenance();
        $items = $recommendations['items'] ?? [];

        $recommendationText = "Terdapat **" . count($items) . " fasilitas prioritas** yang disarankan untuk segera masuk jadwal pemeliharaan tim Sarpras.";

        return [
            'status' => 'success',
            'total_rekomendasi' => count($items),
            'items' => $items,
            'summary_text' => $recommendationText
        ];
    }

    /**
     * Fitur 1: AI Facility Analysis (Analisis mendalam kondisi, frekuensi, umur, & pola)
     * 
     * @return array
     */
    public function getFacilityAnalysis(): array {
        return $this->facilityAnalyzer->analyze();
    }

    /**
     * Fitur 2: AI Maintenance Recommendation (Rekomendasi prioritas Tinggi, Sedang, Rendah)
     * 
     * @return array
     */
    public function getMaintenanceRecommendations(): array {
        return $this->maintenanceRecommender->getRecommendations();
    }

    /**
     * Mengambil instance provider saat ini
     */
    public function getProvider(): AIProviderInterface {
        return $this->provider;
    }
}
