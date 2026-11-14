<?php
/**
 * OpenAICompatibleProvider
 * Provider untuk model AI yang mendukung spesifikasi standar OpenAI chat completions:
 * - DeepSeek (https://api.deepseek.com/chat/completions)
 * - OpenRouter (https://openrouter.ai/api/v1/chat/completions) - Mendukung Claude, DeepSeek, LLaMA
 * - OpenAI (https://api.openai.com/v1/chat/completions)
 * - Anthropic Claude via OpenRouter atau kompatibilitas REST
 */

require_once __DIR__ . '/AIProviderInterface.php';
require_once __DIR__ . '/RuleBasedEngine.php';

class OpenAICompatibleProvider implements AIProviderInterface {
    protected string $apiKey;
    protected string $model;
    protected string $endpoint;
    protected string $providerName;
    protected RuleBasedEngine $fallbackEngine;

    public function __construct(string $provider = 'deepseek', ?string $apiKey = null, ?string $model = null) {
        $this->fallbackEngine = new RuleBasedEngine();
        
        // Resolusi API Key & Model
        $this->apiKey = $apiKey ?? (defined('AI_API_KEY') ? AI_API_KEY : (getenv('AI_API_KEY') ?: ''));
        $this->model  = $model  ?? (defined('AI_MODEL') ? AI_MODEL : (getenv('AI_MODEL') ?: 'deepseek-chat'));

        // Resolusi Endpoint berdasarkan Provider
        switch (strtolower($provider)) {
            case 'deepseek':
                $this->endpoint = 'https://api.deepseek.com/chat/completions';
                $this->providerName = 'DeepSeek (' . $this->model . ')';
                break;
            case 'openrouter':
                $this->endpoint = 'https://openrouter.ai/api/v1/chat/completions';
                $this->providerName = 'OpenRouter (' . $this->model . ')';
                break;
            case 'claude':
                // Jika Claude dipanggil via direct Anthropic endpoint atau OpenRouter
                $this->endpoint = 'https://openrouter.ai/api/v1/chat/completions';
                $this->providerName = 'Anthropic Claude (' . $this->model . ')';
                break;
            case 'openai':
                $this->endpoint = 'https://api.openai.com/v1/chat/completions';
                $this->providerName = 'OpenAI (' . $this->model . ')';
                break;
            case 'nvidia':
                $this->endpoint = 'https://integrate.api.nvidia.com/v1/chat/completions';
                $this->providerName = 'NVIDIA NIM (' . $this->model . ')';
                break;
            default:
                $this->endpoint = 'https://api.deepseek.com/chat/completions';
                $this->providerName = ucfirst($provider) . ' (' . $this->model . ')';
                break;
        }
    }

    public function getName(): string {
        return $this->providerName;
    }

    public function isAvailable(): bool {
        return !empty($this->apiKey) && function_exists('curl_init');
    }

    public function generateAnswer(string $systemPrompt, string $userQuestion, array $contextData): string {
        if (!$this->isAvailable()) {
            return $this->fallbackEngine->generateAnswer($systemPrompt, $userQuestion, $contextData);
        }

        try {
            $contextString = "DATA FAKTUAL DARI DATABASE KAMPUS:\n" . 
                             json_encode($contextData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

            $messages = [
                [
                    'role' => 'system',
                    'content' => $systemPrompt . "\n\nPENTING: Gunakan HANYA data faktual yang diberikan dalam konteks database kampus. Jangan mengarang data atau angka di luar konteks."
                ],
                [
                    'role' => 'user',
                    'content' => $contextString . "\n\nPERTANYAAN PENGGUNA:\n" . $userQuestion . "\n\nInstruksi: Susun jawaban ramah, informatif, dan berbasis data di atas menggunakan Markdown."
                ]
            ];

            $payload = [
                'model' => $this->model,
                'messages' => $messages,
                'temperature' => 0.2,
                'max_tokens' => 1024
            ];

            $headers = [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey
            ];

            // Header tambahan khusus OpenRouter jika digunakan
            if (str_contains($this->endpoint, 'openrouter.ai')) {
                $headers[] = 'HTTP-Referer: http://localhost/Fasilitas%20Kampus';
                $headers[] = 'X-Title: SI Fasilitas IKMI';
            }

            $ch = curl_init($this->endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_TIMEOUT, 12);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $rawResponse = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200 && $rawResponse) {
                $json = json_decode($rawResponse, true);
                $content = $json['choices'][0]['message']['content'] ?? '';
                if (!empty(trim($content))) {
                    return trim($content);
                }
            }
        } catch (\Throwable $e) {
            // Log silent, fallback otomatis
        }

        // Fallback ke RuleBasedEngine agar jawaban tetap akurat dan tidak crash
        return $this->fallbackEngine->generateAnswer($systemPrompt, $userQuestion, $contextData);
    }
}

