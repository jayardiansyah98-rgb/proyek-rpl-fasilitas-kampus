<?php
/**
 * GeminiProvider
 * Provider eksternal Google Gemini API dengan abstraksi aman.
 * API key diambil dari environment/konfigurasi tanpa hardcoding di source code.
 */

require_once __DIR__ . '/AIProviderInterface.php';
require_once __DIR__ . '/RuleBasedEngine.php';

class GeminiProvider implements AIProviderInterface {
    protected $apiKey;
    protected $model;
    protected $fallbackEngine;

    public function __construct(?string $apiKey = null, ?string $model = null) {
        $this->apiKey = !empty($apiKey) ? $apiKey : (defined('AI_API_KEY') ? AI_API_KEY : (getenv('AI_API_KEY') ?: ''));
        $this->model = !empty($model) ? $model : (defined('AI_MODEL') ? AI_MODEL : (getenv('AI_MODEL') ?: 'gemini-1.5-flash'));
        $this->fallbackEngine = new RuleBasedEngine();
    }

    public function getName(): string {
        return 'Google Gemini (' . $this->model . ')';
    }

    public function isAvailable(): bool {
        return !empty($this->apiKey) && function_exists('curl_init');
    }

    public function generateAnswer(string $systemPrompt, string $userQuestion, array $contextData): string {
        // Jika API Key tidak tersedia atau cURL mati, gunakan RuleBasedEngine yang 100% akurat
        if (!$this->isAvailable()) {
            return $this->fallbackEngine->generateAnswer($systemPrompt, $userQuestion, $contextData);
        }

        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key=" . urlencode($this->apiKey);

            $payload = [
                'systemInstruction' => [
                    'parts' => [
                        ['text' => $systemPrompt . "\n\nPENTING: Gunakan HANYA data faktual yang diberikan dalam konteks database. Jangan mengarang data atau angka di luar konteks."]
                    ]
                ],
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            [
                                'text' => "DATA FAKTUAL DARI DATABASE:\n" . json_encode($contextData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . 
                                          "\n\nPERTANYAAN PENGGUNA:\n" . $userQuestion . 
                                          "\n\nInstruksi: Susun jawaban ramah, informatif, dan berbasis data di atas menggunakan Markdown."
                            ]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.2, // Rendah untuk kepatuhan fakta
                    'maxOutputTokens' => 1024
                ]
            ];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $rawResponse = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($httpCode === 200 && $rawResponse) {
                $json = json_decode($rawResponse, true);
                $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
                if (!empty(trim($text))) {
                    return trim($text);
                }
            }
        } catch (\Throwable $e) {
            // Fallback halus jika koneksi eksternal bermasalah
        }

        // Fallback ke RuleBasedEngine
        return $this->fallbackEngine->generateAnswer($systemPrompt, $userQuestion, $contextData);
    }
}
