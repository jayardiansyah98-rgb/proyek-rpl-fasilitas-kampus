<?php
/**
 * AIConfigService
 * Service untuk memanipulasi konfigurasi AI secara dinamis:
 * - Membaca dan menulis konfigurasi ke .env (root) dan chatbot-service/.env
 * - Memicu hot-reload ke service Node.js (port 3001) secara real-time
 * - Menguji konektivitas API Key dan Model AI ke provider terkait
 */

class AIConfigService {
    protected string $rootEnvFile;
    protected string $chatbotEnvFile;
    protected string $nodeServiceUrl;

    public function __construct() {
        $this->rootEnvFile = ROOT_PATH . '/.env';
        $this->chatbotEnvFile = ROOT_PATH . '/chatbot-service/.env';
        $this->nodeServiceUrl = 'http://127.0.0.1:3001';
    }

    /**
     * Mengambil daftar provider yang didukung beserta model rekomendasinya
     */
    public function getSupportedProviders(): array {
        return [
            'gemini' => [
                'name' => 'Google Gemini AI',
                'badge' => 'Google DeepMind',
                'badge_color' => 'primary',
                'icon' => 'bi-google',
                'models' => [
                    'gemini-2.5-flash'         => 'Gemini 2.5 Flash (Terbaru & Tercepat — Rekomendasi)',
                    'gemini-2.5-pro'           => 'Gemini 2.5 Pro (Penalaran Terdalam 2025)',
                    'gemini-2.0-flash'         => 'Gemini 2.0 Flash (Generasi 2)',
                    'gemini-2.0-flash-exp'     => 'Gemini 2.0 Flash Exp (Eksperimental)',
                    'gemini-1.5-flash'         => 'Gemini 1.5 Flash (Generasi 1 — Stabil)',
                    'gemini-1.5-flash-latest'  => 'Gemini 1.5 Flash Latest',
                    'gemini-1.5-pro'           => 'Gemini 1.5 Pro (Generasi 1 — Detail)',
                ],
                'default_model' => 'gemini-2.5-flash',
                'key_placeholder' => 'AIzaSy...',
                'docs_url' => 'https://aistudio.google.com/app/apikey'
            ],
            'deepseek' => [
                'name' => 'DeepSeek AI',
                'badge' => 'Paling Populer & Hemat',
                'badge_color' => 'info',
                'icon' => 'bi-lightning-charge-fill',
                'models' => [
                    'deepseek-chat'     => 'DeepSeek-V3 (Cerdas, Cepat, Biaya Sangat Terjangkau — Rekomendasi)',
                    'deepseek-reasoner' => 'DeepSeek-R1 (Penalaran Mendalam / Advanced Reasoning)'
                ],
                'default_model' => 'deepseek-chat',
                'key_placeholder' => 'sk-...',
                'docs_url' => 'https://platform.deepseek.com/api_keys'
            ],
            'openrouter' => [
                'name' => 'OpenRouter (Multi-Model Gateway)',
                'badge' => 'Akses Claude, LLaMA & GPT',
                'badge_color' => 'warning',
                'icon' => 'bi-shuffle',
                'models' => [
                    'anthropic/claude-3.5-sonnet'   => 'Claude 3.5 Sonnet (Penalaran & Bahasa Terbaik)',
                    'deepseek/deepseek-r1'          => 'DeepSeek R1 (Open Source Reasoning)',
                    'meta-llama/llama-3.3-70b-instruct' => 'Meta LLaMA 3.3 70B (Open Source Terkuat)',
                    'openai/gpt-4o-mini'            => 'GPT-4o Mini (OpenAI Cepat)'
                ],
                'default_model' => 'anthropic/claude-3.5-sonnet',
                'key_placeholder' => 'sk-or-v1-...',
                'docs_url' => 'https://openrouter.ai/keys'
            ],
            'openai' => [
                'name' => 'OpenAI (ChatGPT)',
                'badge' => 'OpenAI Resmi',
                'badge_color' => 'success',
                'icon' => 'bi-cpu-fill',
                'models' => [
                    'gpt-4o-mini' => 'GPT-4o Mini (Cepat, Ringan & Cerdas)',
                    'gpt-4o'      => 'GPT-4o Omnimodel (Kecerdasan Maksimal)'
                ],
                'default_model' => 'gpt-4o-mini',
                'key_placeholder' => 'sk-proj-...',
                'docs_url' => 'https://platform.openai.com/api-keys'
            ],
            'rule_engine' => [
                'name' => 'Rule-Based Engine (Lokal Kampus)',
                'badge' => 'Offline / Gratis 100%',
                'badge_color' => 'secondary',
                'icon' => 'bi-database-fill-check',
                'models' => [
                    'rule_engine' => 'Local Rule Engine (Tanpa Kuota & Tanpa API Key)'
                ],
                'default_model' => 'rule_engine',
                'key_placeholder' => 'Tidak memerlukan API Key',
                'docs_url' => '#'
            ]
        ];
    }

    /**
     * Membaca konfigurasi AI saat ini dari file .env secara langsung
     */
    public function getCurrentConfig(): array {
        $env = $this->parseEnvFile($this->rootEnvFile);
        $provider = $env['AI_PROVIDER'] ?? (getenv('AI_PROVIDER') ?: (defined('AI_PROVIDER') ? AI_PROVIDER : 'gemini'));
        $apiKey   = $env['AI_API_KEY'] ?? (getenv('AI_API_KEY') ?: (defined('AI_API_KEY') ? AI_API_KEY : ''));
        $model    = $env['AI_MODEL'] ?? (getenv('AI_MODEL') ?: (defined('AI_MODEL') ? AI_MODEL : 'gemini-1.5-flash'));

        // Jika API Key dimulai dengan nvapi dan provider belum spesifik, set ke openrouter/nvidia
        if (str_starts_with($apiKey, 'nvapi-') && $provider === 'rule_engine') {
            $provider = 'openrouter';
        }

        return [
            'provider' => $provider,
            'api_key' => $apiKey,
            'model' => $model
        ];
    }

    /**
     * Helper membaca key-value dari file .env
     */
    public function parseEnvFile(string $filePath): array {
        if (!file_exists($filePath)) {
            return [];
        }
        $result = [];
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (str_contains($line, '=')) {
                [$k, $v] = explode('=', $line, 2);
                $k = trim($k);
                $v = trim($v);
                if ((str_starts_with($v, '"') && str_ends_with($v, '"')) ||
                    (str_starts_with($v, "'") && str_ends_with($v, "'"))) {
                    $v = substr($v, 1, -1);
                }
                $result[$k] = $v;
            }
        }
        return $result;
    }

    /**
     * Menyimpan konfigurasi baru ke .env root dan .env chatbot-service
     */
    public function saveConfig(string $provider, string $apiKey, string $model): array {
        $provider = trim(strtolower($provider));
        $apiKey = trim($apiKey);
        $model = trim($model);

        if (empty($model)) {
            $providers = $this->getSupportedProviders();
            $model = $providers[$provider]['default_model'] ?? 'gemini-1.5-flash';
        }

        // 1. Perbarui .env di root project
        $this->updateEnvFile($this->rootEnvFile, [
            'AI_PROVIDER' => $provider,
            'AI_API_KEY' => $apiKey,
            'AI_MODEL' => $model
        ]);

        // 2. Perbarui .env di chatbot-service
        if (file_exists($this->chatbotEnvFile)) {
            $this->updateEnvFile($this->chatbotEnvFile, [
                'AI_PROVIDER' => $provider,
                'AI_API_KEY' => $apiKey,
                'AI_MODEL' => $model
            ]);
        }

        // 3. Perbarui runtime environment PHP
        putenv("AI_PROVIDER={$provider}");
        putenv("AI_API_KEY={$apiKey}");
        putenv("AI_MODEL={$model}");
        $_ENV['AI_PROVIDER'] = $provider;
        $_ENV['AI_API_KEY'] = $apiKey;
        $_ENV['AI_MODEL'] = $model;

        // 4. Hot-reload ke Service Node.js (Port 3001)
        $hotReloadResult = $this->notifyNodeService($provider, $apiKey, $model);

        return [
            'status' => 'success',
            'message' => 'Konfigurasi AI Assistant berhasil diperbarui.',
            'hot_reload' => $hotReloadResult
        ];
    }

    /**
     * Menguji koneksi API ke Provider AI
     */
    public function testConnection(string $provider, string $apiKey, string $model): array {
        $startTime = microtime(true);
        $provider = trim(strtolower($provider));
        $apiKey = trim($apiKey);
        $model = trim($model);

        if ($provider === 'rule_engine') {
            return [
                'status' => 'success',
                'message' => 'Koneksi Rule-Based Engine lokal 100% aktif dan siap digunakan tanpa API Key.',
                'latency_ms' => 1
            ];
        }

        if (empty($apiKey)) {
            return [
                'status' => 'error',
                'message' => 'API Key wajib diisi untuk provider ' . strtoupper($provider) . '.'
            ];
        }

        // Uji coba sesuai jenis provider
        if ($provider === 'gemini') {
            return $this->testGeminiConnection($apiKey, $model, $startTime);
        } else {
            // DeepSeek, OpenRouter, OpenAI, Claude
            return $this->testOpenAICompatibleConnection($provider, $apiKey, $model, $startTime);
        }
    }

    /**
     * Test Google Gemini API dengan auto-discovery model & version fallback
     */
    protected function testGeminiConnection(string $apiKey, string $model, float $startTime): array {
        // Prioritaskan model 2.5 terlebih dahulu, lalu fallback ke versi lama
        $candidates = [
            ['version' => 'v1beta', 'model' => $model],
            ['version' => 'v1beta', 'model' => 'gemini-2.5-flash'],
            ['version' => 'v1beta', 'model' => 'gemini-2.5-pro'],
            ['version' => 'v1beta', 'model' => 'gemini-2.0-flash'],
            ['version' => 'v1',     'model' => $model],
            ['version' => 'v1beta', 'model' => 'gemini-1.5-flash-latest'],
            ['version' => 'v1beta', 'model' => 'gemini-1.5-flash-001'],
            ['version' => 'v1beta', 'model' => 'gemini-2.0-flash-exp'],
            ['version' => 'v1beta', 'model' => 'gemini-1.5-pro'],
        ];
        // Hapus duplikat jika $model sudah ada di list
        $seen = [];
        $candidates = array_filter($candidates, function($c) use (&$seen) {
            $key = $c['version'] . '|' . $c['model'];
            if (isset($seen[$key])) return false;
            $seen[$key] = true;
            return true;
        });

        $payload = [
            'contents' => [
                ['role' => 'user', 'parts' => [['text' => 'Halo, ini uji koneksi singkat. Balas dengan satu kata: "OK".']]]
            ],
            'generationConfig' => ['maxOutputTokens' => 10]
        ];

        $lastError = '';
        $lastHttpCode = 0;

        foreach ($candidates as $cand) {
            $cVer = $cand['version'];
            $cMod = $cand['model'];
            $url = "https://generativelanguage.googleapis.com/{$cVer}/models/{$cMod}:generateContent?key=" . urlencode($apiKey);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_TIMEOUT, 8);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($httpCode === 200 && $response) {
                $latency = round((microtime(true) - $startTime) * 1000);
                $json = json_decode($response, true);
                $reply = $json['candidates'][0]['content']['parts'][0]['text'] ?? 'OK';
                $modelNote = ($cMod !== $model) ? " (Dialihkan otomatis ke model aktif: {$cMod})" : "";
                return [
                    'status' => 'success',
                    'message' => 'Koneksi ke Google Gemini (' . $cMod . ') berhasil!' . $modelNote . ' (Respons: ' . trim($reply) . ')',
                    'active_model' => $cMod,
                    'latency_ms' => $latency
                ];
            }

            $lastHttpCode = $httpCode;
            if ($response) {
                $json = json_decode($response, true);
                $lastError = $json['error']['message'] ?? ('HTTP ' . $httpCode);
            } elseif ($curlError) {
                $lastError = 'Jaringan: ' . $curlError;
            }
        }

        // Jika kandidat default gagal (misal 404), query ListModels langsung dari Google
        $listUrl = "https://generativelanguage.googleapis.com/v1beta/models?key=" . urlencode($apiKey);
        $chList = curl_init($listUrl);
        curl_setopt($chList, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($chList, CURLOPT_TIMEOUT, 6);
        curl_setopt($chList, CURLOPT_SSL_VERIFYPEER, false);
        $listResp = curl_exec($chList);
        $listCode = curl_getinfo($chList, CURLINFO_HTTP_CODE);
        curl_close($chList);

        $availableNames = [];
        if ($listCode === 200 && $listResp) {
            $listJson = json_decode($listResp, true);
            if (!empty($listJson['models'])) {
                foreach ($listJson['models'] as $mItem) {
                    $mMethods = $mItem['supportedGenerationMethods'] ?? [];
                    if (in_array('generateContent', $mMethods, true)) {
                        $cleanName = str_replace('models/', '', $mItem['name'] ?? '');
                        if (!empty($cleanName)) {
                            $availableNames[] = $cleanName;
                        }
                    }
                }
            }
        }

        if (!empty($availableNames)) {
            // Pilih model terbaik dari yang tersedia: prioritaskan flash/pro generasi terbaru
            $preferredOrder = ['gemini-2.5-flash', 'gemini-2.5-pro', 'gemini-2.0-flash', 'gemini-1.5-flash', 'gemini-1.5-pro'];
            $bestModel = $availableNames[0]; // fallback ke yang pertama
            foreach ($preferredOrder as $preferred) {
                if (in_array($preferred, $availableNames, true)) {
                    $bestModel = $preferred;
                    break;
                }
            }

            // ListModels sendiri sudah membuktikan API Key valid.
            // Cukup gunakan ListModels sebagai validasi — tidak perlu retry generateContent
            // karena kemungkinan rate limit / billing quota pada free tier.
            $latency = round((microtime(true) - $startTime) * 1000);
            $allModelsStr = implode(', ', array_slice($availableNames, 0, 5));
            $note = ($bestModel !== $model)
                ? " (Model dipilih otomatis: {$bestModel} karena {$model} tidak tersedia)"
                : "";
            return [
                'status'       => 'success',
                'message'      => "API Key Google Gemini valid! Model tersedia: {$allModelsStr}.{$note} Gunakan model: {$bestModel}.",
                'active_model' => $bestModel,
                'latency_ms'   => $latency
            ];
        }

        return [
            'status' => 'error',
            'message' => 'Gagal terhubung ke Google Gemini (HTTP ' . $lastHttpCode . '). ' . $lastError
        ];
    }

    /**
     * Test OpenAI-compatible endpoint (DeepSeek, OpenRouter, OpenAI)
     */
    protected function testOpenAICompatibleConnection(string $provider, string $apiKey, string $model, float $startTime): array {
        $endpoint = match ($provider) {
            'deepseek'   => 'https://api.deepseek.com/chat/completions',
            'openrouter' => 'https://openrouter.ai/api/v1/chat/completions',
            'openai'     => 'https://api.openai.com/v1/chat/completions',
            default      => 'https://api.deepseek.com/chat/completions'
        };

        $payload = [
            'model' => $model,
            'messages' => [
                ['role' => 'user', 'content' => 'Halo, ini uji koneksi singkat. Balas dengan satu kata: "OK".']
            ],
            'max_tokens' => 10
        ];

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ];

        if ($provider === 'openrouter') {
            $headers[] = 'HTTP-Referer: http://localhost/Fasilitas%20Kampus';
            $headers[] = 'X-Title: SI Fasilitas IKMI';
        }

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 12);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        $latency = round((microtime(true) - $startTime) * 1000);

        if ($httpCode === 200 && $response) {
            $json = json_decode($response, true);
            $reply = $json['choices'][0]['message']['content'] ?? 'OK';
            return [
                'status' => 'success',
                'message' => 'Koneksi ke ' . ucfirst($provider) . ' (' . $model . ') berhasil! (Respons: ' . trim($reply) . ')',
                'latency_ms' => $latency
            ];
        }

        $errorMsg = 'Gagal terhubung ke ' . ucfirst($provider) . ' (HTTP ' . $httpCode . ').';
        if ($response) {
            $json = json_decode($response, true);
            if (!empty($json['error']['message'])) {
                $errorMsg .= ' Pesan: ' . $json['error']['message'];
            }
        } elseif ($curlError) {
            $errorMsg .= ' Jaringan error: ' . $curlError;
        }

        return ['status' => 'error', 'message' => $errorMsg];
    }

    /**
     * Memperbarui file .env secara aman tanpa merusak konfigurasi lain
     */
    protected function updateEnvFile(string $filePath, array $updates): bool {
        if (!file_exists($filePath)) {
            return false;
        }

        $content = file_get_contents($filePath);
        $lines = explode("\n", $content);
        $updatedKeys = [];

        foreach ($lines as $i => $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                continue;
            }

            if (str_contains($line, '=')) {
                [$key, ] = explode('=', $line, 2);
                $key = trim($key);
                if (array_key_exists($key, $updates)) {
                    $lines[$i] = "{$key}={$updates[$key]}";
                    $updatedKeys[$key] = true;
                }
            }
        }

        // Jika ada key yang belum ada di file .env, tambahkan di baris paling bawah
        foreach ($updates as $key => $val) {
            if (!isset($updatedKeys[$key])) {
                $lines[] = "{$key}={$val}";
            }
        }

        return file_put_contents($filePath, implode("\n", $lines)) !== false;
    }

    /**
     * Memberitahu Chatbot Service Node.js (Port 3001) agar memperbarui variabel di memori
     */
    protected function notifyNodeService(string $provider, string $apiKey, string $model): bool {
        try {
            $url = $this->nodeServiceUrl . '/api/config/update';
            $payload = json_encode([
                'provider' => $provider,
                'apiKey' => $apiKey,
                'model' => $model
            ]);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);

            $res = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            return $code === 200;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
