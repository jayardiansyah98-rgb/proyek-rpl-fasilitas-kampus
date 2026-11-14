<?php
/**
 * EmailService — Pengiriman Email via SMTP (Native PHP cURL/socket, tanpa library eksternal)
 * Sistem Informasi Fasilitas STMIK IKMI Cirebon
 *
 * Menggunakan ekstensi PHP built-in (socket/fsockopen) untuk SMTP TLS/SSL.
 * Konfigurasi HANYA dari environment variable, tidak pernah hardcode.
 * Mendukung Gmail SMTP (port 587 TLS) dan penyedia SMTP lainnya.
 */

class EmailService {

    private string $host;
    private int    $port;
    private string $encryption;
    private string $username;
    private string $password;
    private string $fromAddress;
    private string $fromName;
    private int    $timeout;

    public function __construct() {
        $this->host        = getenv('SMTP_HOST')         ?: 'smtp.gmail.com';
        $this->port        = (int)(getenv('SMTP_PORT')   ?: 587);
        $this->encryption  = strtolower(getenv('SMTP_ENCRYPTION') ?: 'tls');
        $this->username    = getenv('SMTP_USERNAME')     ?: '';
        $this->password    = getenv('SMTP_PASSWORD')     ?: '';
        $this->fromAddress = getenv('MAIL_FROM_ADDRESS') ?: '';
        $this->fromName    = getenv('MAIL_FROM_NAME')    ?: 'Sistem Fasilitas IKMI';
        $this->timeout     = 15;
    }

    /**
     * Apakah konfigurasi SMTP sudah lengkap?
     */
    public function isConfigured(): bool {
        return !empty($this->host)
            && !empty($this->username)
            && !empty($this->password)
            && !empty($this->fromAddress);
    }

    /**
     * Validasi format alamat email
     */
    public static function validateEmail(string $email): bool {
        return filter_var(trim($email), FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Kirim email HTML+Text melalui SMTP
     *
     * @param string $to      Alamat email penerima
     * @param string $subject Subjek email
     * @param string $html    Konten HTML email
     * @param string $text    Konten teks biasa (fallback)
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function send(string $to, string $subject, string $html, string $text = ''): array {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'Konfigurasi SMTP belum lengkap. Periksa environment variable SMTP_*.'];
        }

        if (!self::validateEmail($to)) {
            return ['success' => false, 'error' => 'Alamat email tidak valid: ' . $to];
        }

        if (empty($text)) {
            $text = strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>', '</div>'], "\n", $html));
        }

        try {
            return $this->sendViaSMTP($to, $subject, $html, $text);
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Uji koneksi SMTP (untuk halaman pengaturan admin)
     */
    public function testConnection(): array {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'Konfigurasi SMTP belum lengkap.'];
        }

        try {
            $sock = $this->openSocket();
            $this->readResponse($sock, 220);
            $this->sendCommand($sock, "EHLO " . ($this->host ?: 'localhost'));
            $this->readResponse($sock, 250);

            if ($this->encryption === 'tls') {
                $this->sendCommand($sock, 'STARTTLS');
                $this->readResponse($sock, 220);
                // Upgrade socket ke TLS
                stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                $this->sendCommand($sock, "EHLO " . ($this->host ?: 'localhost'));
                $this->readResponse($sock, 250);
            }

            $this->sendCommand($sock, 'AUTH LOGIN');
            $this->readResponse($sock, 334);
            $this->sendCommand($sock, base64_encode($this->username));
            $this->readResponse($sock, 334);
            $this->sendCommand($sock, base64_encode($this->password));
            $this->readResponse($sock, 235);

            $this->sendCommand($sock, 'QUIT');
            fclose($sock);

            return ['success' => true, 'message' => 'Koneksi SMTP berhasil. Autentikasi diterima oleh ' . $this->host];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // ─────────────────────────────────────────────────────────
    // Private Methods — SMTP Protocol
    // ─────────────────────────────────────────────────────────

    private function sendViaSMTP(string $to, string $subject, string $html, string $text): array {
        $sock = $this->openSocket();

        try {
            $this->readResponse($sock, 220);

            $helo = gethostname() ?: 'localhost';
            $this->sendCommand($sock, "EHLO $helo");
            $this->readResponse($sock, 250);

            if ($this->encryption === 'tls') {
                $this->sendCommand($sock, 'STARTTLS');
                $this->readResponse($sock, 220);
                $ok = stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                if (!$ok) {
                    throw new Exception('Gagal mengaktifkan enkripsi TLS pada koneksi SMTP.');
                }
                $this->sendCommand($sock, "EHLO $helo");
                $this->readResponse($sock, 250);
            }

            // Autentikasi LOGIN
            $this->sendCommand($sock, 'AUTH LOGIN');
            $this->readResponse($sock, 334);
            $this->sendCommand($sock, base64_encode($this->username));
            $this->readResponse($sock, 334);
            $this->sendCommand($sock, base64_encode($this->password));
            $this->readResponse($sock, 235);

            // Envelope
            $this->sendCommand($sock, 'MAIL FROM:<' . $this->fromAddress . '>');
            $this->readResponse($sock, 250);
            $this->sendCommand($sock, 'RCPT TO:<' . $to . '>');
            $this->readResponse($sock, 250);

            // Data
            $this->sendCommand($sock, 'DATA');
            $this->readResponse($sock, 354);

            $boundary = 'IKMI_' . md5(uniqid('', true));
            $headers  = $this->buildHeaders($to, $subject, $boundary);
            $body     = $this->buildBody($html, $text, $boundary);

            fwrite($sock, $headers . "\r\n" . $body . "\r\n.\r\n");
            $this->readResponse($sock, 250);

            $this->sendCommand($sock, 'QUIT');
            fclose($sock);

            return ['success' => true, 'error' => null];

        } catch (Exception $e) {
            @fclose($sock);
            throw $e;
        }
    }

    private function openSocket() {
        $address = ($this->encryption === 'ssl')
            ? 'ssl://' . $this->host . ':' . $this->port
            : 'tcp://' . $this->host . ':' . $this->port;

        $context = stream_context_create([
            'ssl' => [
                'verify_peer'       => true,
                'verify_peer_name'  => true,
                'allow_self_signed' => false,
            ]
        ]);

        $sock = @stream_socket_client($address, $errno, $errstr, $this->timeout, STREAM_CLIENT_CONNECT, $context);

        if (!$sock) {
            throw new Exception("Tidak dapat terhubung ke server SMTP {$this->host}:{$this->port} — $errstr (errno $errno)");
        }

        stream_set_timeout($sock, $this->timeout);
        return $sock;
    }

    private function sendCommand($sock, string $command): void {
        fwrite($sock, $command . "\r\n");
    }

    private function readResponse($sock, int $expectedCode): string {
        $response = '';
        while ($line = fgets($sock, 512)) {
            $response .= $line;
            // Baris terakhir: karakter ke-4 adalah spasi
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }

        $actualCode = (int)substr($response, 0, 3);
        if ($actualCode !== $expectedCode) {
            throw new Exception("SMTP error: diharapkan $expectedCode, diterima $actualCode. Respons: " . trim($response));
        }

        return $response;
    }

    private function buildHeaders(string $to, string $subject, string $boundary): string {
        $fromEncoded = '=?UTF-8?B?' . base64_encode($this->fromName) . '?=';
        $subjectEncoded = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $date = date('r');
        $msgId = '<' . uniqid('ikmi', true) . '@' . ($this->host ?: 'ikmi.ac.id') . '>';

        return implode("\r\n", [
            "From: $fromEncoded <{$this->fromAddress}>",
            "To: $to",
            "Subject: $subjectEncoded",
            "Date: $date",
            "Message-ID: $msgId",
            "MIME-Version: 1.0",
            "Content-Type: multipart/alternative; boundary=\"$boundary\"",
            "X-Mailer: Sistem-Fasilitas-IKMI/1.0",
        ]);
    }

    private function buildBody(string $html, string $text, string $boundary): string {
        $encodedText = chunk_split(base64_encode($text));
        $encodedHtml = chunk_split(base64_encode($html));

        return "--$boundary\r\n"
            . "Content-Type: text/plain; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: base64\r\n\r\n"
            . $encodedText . "\r\n"
            . "--$boundary\r\n"
            . "Content-Type: text/html; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: base64\r\n\r\n"
            . $encodedHtml . "\r\n"
            . "--$boundary--";
    }
}

