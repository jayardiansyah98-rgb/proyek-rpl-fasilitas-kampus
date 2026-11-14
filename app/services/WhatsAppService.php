<?php
/**
 * WhatsAppService — Integrasi API Fonnte
 * Sistem Informasi Fasilitas STMIK IKMI Cirebon
 *
 * Mengirim pesan WhatsApp nyata melalui API Fonnte.
 * Token HANYA dibaca dari environment variable, tidak pernah dari frontend.
 * Seluruh percobaan dicatat di tabel notif_logs.
 */

class WhatsAppService {

    /** URL endpoint API Fonnte (dari dokumentasi resmi https://fonnte.com/docs) */
    private string $apiUrl;

    /** Token Fonnte dari .env / getenv() */
    private string $token;

    /** Timeout HTTP request (detik) */
    private int $timeout;

    public function __construct() {
        $this->apiUrl  = getenv('FONNTE_API_URL') ?: 'https://api.fonnte.com/send';
        $this->token   = getenv('FONNTE_TOKEN') ?: '';
        $this->timeout = (int)(getenv('FONNTE_TIMEOUT') ?: 15);
    }

    /**
     * Validasi konfigurasi: apakah token tersedia?
     */
    public function isConfigured(): bool {
        return !empty($this->token);
    }

    /**
     * Validasi format nomor WhatsApp internasional (62xxxxxxxxxx)
     */
    public static function validateNumber(string $number): bool {
        $cleaned = preg_replace('/\D/', '', $number);
        // Harus dimulai dengan kode negara (misal 62 untuk Indonesia), minimal 10 digit
        return strlen($cleaned) >= 10 && strlen($cleaned) <= 15 && preg_match('/^[1-9]\d{9,14}$/', $cleaned);
    }

    /**
     * Normalisasi nomor ke format internasional tanpa tanda +
     */
    public static function normalizeNumber(string $number): string {
        $cleaned = preg_replace('/\D/', '', $number);
        // Konversi 08xx → 628xx
        if (str_starts_with($cleaned, '0')) {
            $cleaned = '62' . substr($cleaned, 1);
        }
        return $cleaned;
    }

    /**
     * Kirim pesan WhatsApp melalui API Fonnte
     *
     * @param string $to     Nomor tujuan (akan dinormalisasi)
     * @param string $message Isi pesan teks
     * @return array ['success' => bool, 'ref_id' => string|null, 'response' => array, 'error' => string|null]
     */
    public function send(string $to, string $message): array {
        if (!$this->isConfigured()) {
            return [
                'success'  => false,
                'ref_id'   => null,
                'response' => [],
                'error'    => 'Token Fonnte belum dikonfigurasi di environment variable FONNTE_TOKEN.'
            ];
        }

        $normalized = self::normalizeNumber($to);

        if (!self::validateNumber($normalized)) {
            return [
                'success'  => false,
                'ref_id'   => null,
                'response' => [],
                'error'    => 'Nomor WhatsApp tidak valid: ' . $to
            ];
        }

        // Batasi panjang pesan (WhatsApp maks ~4096 karakter)
        if (strlen($message) > 4000) {
            $message = substr($message, 0, 3997) . '...';
        }

        $postData = [
            'target'  => $normalized,
            'message' => $message,
            'typing'  => false,
            'delay'   => 0,
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $this->apiUrl,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($postData),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 8,          // Max 8 detik — Fonnte biasanya respond < 3 detik
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_HTTPHEADER     => [
                'Authorization: ' . $this->token,
                'Content-Type: application/x-www-form-urlencoded',
            ],
            CURLOPT_SSL_VERIFYPEER => false,      // Nonaktifkan SSL verify (localhost dev)
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FOLLOWLOCATION => false,
        ]);

        $rawResponse = curl_exec($ch);
        $httpCode    = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError   = curl_error($ch);
        curl_close($ch);

        // Gagal koneksi cURL
        if ($rawResponse === false || !empty($curlError)) {
            return [
                'success'  => false,
                'ref_id'   => null,
                'response' => [],
                'error'    => 'Gagal menghubungi API Fonnte: ' . $curlError
            ];
        }

        $decoded = json_decode($rawResponse, true);

        // Sanitasi respons — hapus data sensitif sebelum disimpan ke DB
        $sanitizedResponse = $this->sanitizeResponse($decoded);

        // Evaluasi respons Fonnte
        // Fonnte mengembalikan {"status": true, ...} jika berhasil
        $success = ($httpCode === 200)
            && is_array($decoded)
            && !empty($decoded['status'])
            && $decoded['status'] === true;

        $refId = $decoded['id'] ?? $decoded['message_id'] ?? null;
        $error = null;

        if (!$success) {
    if ($httpCode === 401) {
        $error = 'Token Fonnte tidak valid atau tidak memiliki izin.';
    } elseif ($httpCode === 422) {
        $error = 'Nomor WhatsApp tidak terdaftar atau tidak valid: ' . $normalized;
    } elseif ($httpCode >= 500) {
        $error = 'Server Fonnte mengalami gangguan (HTTP ' . $httpCode . ').';
    } else {
        $reason = $decoded['reason'] ?? $decoded['message'] ?? ('HTTP ' . $httpCode);

        if (is_array($reason)) {
            $reason = json_encode($reason, JSON_UNESCAPED_UNICODE);
        } elseif (!is_scalar($reason)) {
            $reason = 'Respons API tidak diketahui.';
        }

        $error = 'Pengiriman gagal: ' . $reason;
    }
}

        return [
            'success'  => $success,
            'ref_id' => is_scalar($refId) && $refId !== ''
    ? (string)$refId
    : null,
            'response' => $sanitizedResponse,
            'error'    => $error
        ];
    }

    /**
     * Uji koneksi ke API Fonnte (untuk halaman pengaturan admin)
     * Menggunakan endpoint GET /device — endpoint resmi Fonnte untuk cek status perangkat
     */
    public function testConnection(): array {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'Token Fonnte belum dikonfigurasi.'];
        }

        // Endpoint resmi Fonnte: POST https://api.fonnte.com/device
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => 'https://api.fonnte.com/device',
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => '',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_HTTPHEADER     => [
                'Authorization: ' . $this->token,
            ],
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $rawResponse = curl_exec($ch);
        $httpCode    = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError   = curl_error($ch);
        curl_close($ch);

        if ($rawResponse === false || !empty($curlError)) {
            return ['success' => false, 'message' => 'Tidak dapat terhubung ke server Fonnte: ' . $curlError];
        }

        $decoded = json_decode($rawResponse, true);

        if ($httpCode === 200 && is_array($decoded)) {
            // Fonnte /device mengembalikan array perangkat atau object dengan status
            if (!empty($decoded['status']) && $decoded['status'] === true) {
                $name   = $decoded['name']   ?? $decoded['device'] ?? '';
                $status = $decoded['status_device'] ?? $decoded['device_status'] ?? 'connected';
                return [
                    'success' => true,
                    'message' => 'Koneksi berhasil.' . ($name ? ' Perangkat: ' . htmlspecialchars((string)$name) : '') . ' Status: ' . $status
                ];
            }
            // Fonnte /device kadang return array of devices
            if (isset($decoded[0]) && is_array($decoded[0])) {
                $dev    = $decoded[0];
                $name   = $dev['name']   ?? $dev['device'] ?? 'Perangkat';
                $status = $dev['status'] ?? 'connected';
                return [
                    'success' => true,
                    'message' => 'Koneksi berhasil. Perangkat: ' . htmlspecialchars((string)$name) . ' (' . $status . ')'
                ];
            }
            // Fallback: HTTP 200 sudah cukup buktikan token valid
            return [
                'success' => true,
                'message' => 'Token Fonnte valid dan terhubung ke server.'
            ];
        }

        if ($httpCode === 401) {
            return ['success' => false, 'message' => 'Token tidak valid atau sudah kedaluwarsa.'];
        }

        // Fallback: coba kirim pesan dummy ke nomor test untuk validasi token
        // (lebih reliable daripada endpoint khusus)
        return ['success' => false, 'message' => 'Respons tidak dikenali dari Fonnte (HTTP ' . $httpCode . '). Pastikan token benar.'];
    }

    /**
     * Hapus data sensitif dari respons API sebelum disimpan ke database
     */
    private function sanitizeResponse(?array $response): array {
        if (!$response) return [];
        // Hapus field yang mungkin mengandung token atau data sensitif
        $blacklist = ['token', 'key', 'secret', 'password', 'auth'];
        foreach ($blacklist as $key) {
            unset($response[$key]);
        }
        return $response;
    }
}

