<?php
/**
 * NotificationService — Orkestrator Notifikasi
 * Sistem Informasi Fasilitas STMIK IKMI Cirebon
 *
 * Mengoordinasikan pengiriman notifikasi in-app, WhatsApp (Fonnte), dan Email (SMTP).
 * Prinsip:
 * - Laporan SELALU disimpan dulu sebelum notifikasi.
 * - Kegagalan WA/Email TIDAK membatalkan proses bisnis.
 * - Setiap percobaan dicatat ke notif_logs.
 * - Idempotency key mencegah pengiriman ganda.
 */

require_once __DIR__ . '/WhatsAppService.php';
require_once __DIR__ . '/EmailService.php';
require_once __DIR__ . '/WhatsAppTemplates.php';
require_once __DIR__ . '/EmailTemplates.php';

class NotificationService {

    private Database $db;
    private WhatsAppService $wa;
    private EmailService $email;

    /** Pengaturan dari tabel notif_settings (di-cache per request) */
    private array $settings = [];

    public function __construct(Database $db) {
        $this->db    = $db;
        $this->wa    = new WhatsAppService();
        $this->email = new EmailService();
        $this->loadSettings();
    }

    // ─────────────────────────────────────────────────────────
    // Public Event Methods (dipanggil dari Controller/Model)
    // ─────────────────────────────────────────────────────────

    /**
     * EVENT: Laporan kerusakan baru dibuat
     * - In-app notif ke petugas_sarpras & admin
     * - WA + Email ke pelapor (konfirmasi)
     * - WA + Email ke petugas_sarpras (alert)
     *
     * @param int   $laporanId   ID laporan yang baru disimpan
     * @param array $laporanData Data laporan lengkap (dari findById)
     */
    public function onLaporanBaru(int $laporanId, array $laporanData): void {
        if (!$this->getSetting('notif_enabled', true)) return;
        if (!$this->getSetting('notif_laporan_baru', true)) return;

        $linkDetail = $this->buildDetailUrl('laporan/detail/' . $laporanId);

        // 1. In-app: sudah dibuat oleh LaporanModel::create() — skip, tidak duplikasi
        // 2. In-app ke pelapor: konfirmasi
        $this->createInApp(
            userId       : (int)$laporanData['user_id'],
            judul        : 'Laporan ' . $laporanData['kode_laporan'] . ' Berhasil Dikirim',
            pesan        : 'Laporan kerusakan fasilitas "' . $laporanData['nama_fasilitas'] . '" di ' . $laporanData['nama_ruangan'] . ' telah diterima sistem.',
            tipe         : 'laporan',
            tautan       : 'laporan/detail/' . $laporanId,
            laporanId    : $laporanId,
            idempotencyKey: 'laporan_baru_pelapor_' . $laporanId
        );

        // 3. WA ke pelapor (jika enabled & nomor ada)
        if ($this->getSetting('whatsapp_enabled', false)) {
            $waMsg = WhatsAppTemplates::laporanBaru($laporanData, $linkDetail);
            $this->sendWa($laporanData['telepon_pelapor'] ?? '', $waMsg, $laporanId, 'laporan_baru', (int)$laporanData['user_id'], 'pelapor_laporan_baru_' . $laporanId);
        }

        // 4. Email ke pelapor (jika enabled & email ada)
        if ($this->getSetting('email_enabled', false)) {
            $tmpl = EmailTemplates::laporanBaru($laporanData, $linkDetail);
            $this->sendEmail($laporanData['email_pelapor'] ?? '', $tmpl['subject'], $tmpl['html'], $tmpl['text'], $laporanId, 'laporan_baru', (int)$laporanData['user_id'], 'email_pelapor_laporan_baru_' . $laporanId);
        }

        // 5. Notifikasi ke semua petugas_sarpras & admin
        $penerimaPetugas = $this->getUsersByRoles(['petugas_sarpras', 'admin']);
        foreach ($penerimaPetugas as $petugas) {
            // WA ke petugas
            if ($this->getSetting('whatsapp_enabled', false) && !empty($petugas['telepon'])) {
                $waMsg = WhatsAppTemplates::laporanBaruPetugas($laporanData, $linkDetail);
                $this->sendWa($petugas['telepon'], $waMsg, $laporanId, 'laporan_baru_petugas', (int)$petugas['id'], 'wa_petugas_laporan_baru_' . $laporanId . '_u' . $petugas['id']);
            }
            // Email ke petugas
            if ($this->getSetting('email_enabled', false) && !empty($petugas['email'])) {
                $tmpl = EmailTemplates::laporanBaruPetugas($laporanData, $linkDetail);
                $this->sendEmail($petugas['email'], $tmpl['subject'], $tmpl['html'], $tmpl['text'], $laporanId, 'laporan_baru_petugas', (int)$petugas['id'], 'email_petugas_laporan_baru_' . $laporanId . '_u' . $petugas['id']);
            }
        }

        // 6. Notifikasi ke Kontak Tambahan (jika diatur di Pengaturan Notifikasi)
        if ($this->getSetting('whatsapp_enabled', false)) {
            $waTambahan = $this->getSetting('wa_notif_tambahan', []);
            if (is_array($waTambahan)) {
                foreach ($waTambahan as $idx => $waNum) {
                    if (!empty($waNum)) {
                        $waMsg = WhatsAppTemplates::laporanBaruPetugas($laporanData, $linkDetail);
                        $this->sendWa($waNum, $waMsg, $laporanId, 'laporan_baru_tambahan', null, 'wa_tambahan_laporan_baru_' . $laporanId . '_' . $idx);
                    }
                }
            }
        }
        if ($this->getSetting('email_enabled', false)) {
            $emailTambahan = $this->getSetting('email_notif_tambahan', []);
            if (is_array($emailTambahan)) {
                foreach ($emailTambahan as $idx => $emAddr) {
                    if (!empty($emAddr)) {
                        $tmpl = EmailTemplates::laporanBaruPetugas($laporanData, $linkDetail);
                        $this->sendEmail($emAddr, $tmpl['subject'], $tmpl['html'], $tmpl['text'], $laporanId, 'laporan_baru_tambahan', null, 'email_tambahan_laporan_baru_' . $laporanId . '_' . $idx);
                    }
                }
            }
        }
    }

    /**
     * EVENT: Status laporan berubah
     *
     * @param int    $laporanId    ID laporan
     * @param array  $laporanData  Data laporan lengkap
     * @param string $statusBaru   Status baru (diverifikasi, diproses, selesai, ditolak)
     * @param string $catatan      Catatan petugas (opsional)
     */
    public function onStatusUpdate(int $laporanId, array $laporanData, string $statusBaru, string $catatan = ''): void {
        if (!$this->getSetting('notif_enabled', true)) return;
        if (!$this->getSetting('notif_status_update', true)) return;
        if ($statusBaru === 'selesai' && !$this->getSetting('notif_selesai', true)) return;
        if ($statusBaru === 'ditolak' && !$this->getSetting('notif_ditolak', true)) return;

        $linkDetail = $this->buildDetailUrl('laporan/detail/' . $laporanId);

        $statusLabels = [
            'menunggu'     => 'Menunggu Verifikasi',
            'diverifikasi' => 'Telah Diverifikasi',
            'diproses'     => 'Sedang Diproses',
            'selesai'      => 'Selesai Diperbaiki',
            'ditolak'      => 'Ditolak',
        ];
        $label = $statusLabels[$statusBaru] ?? ucfirst($statusBaru);
        $timestamp = time();

        // 1. In-app: sudah dibuat oleh LaporanModel::updateStatus() — skip duplikasi
        // (tapi kita update idempotency key agar bisa retry WA/Email jika gagal)

        // 2. WA ke pelapor
        if ($this->getSetting('whatsapp_enabled', false)) {
            $waMsg = WhatsAppTemplates::statusUpdate($laporanData, $statusBaru, $catatan, $linkDetail);
            $this->sendWa($laporanData['telepon_pelapor'] ?? '', $waMsg, $laporanId, 'status_update_' . $statusBaru, (int)$laporanData['user_id'], 'wa_status_' . $laporanId . '_' . $statusBaru . '_' . $timestamp);
        }

        // 3. Email ke pelapor
        if ($this->getSetting('email_enabled', false)) {
            $tmpl = EmailTemplates::statusUpdate($laporanData, $statusBaru, $catatan, $linkDetail);
            $this->sendEmail($laporanData['email_pelapor'] ?? '', $tmpl['subject'], $tmpl['html'], $tmpl['text'], $laporanId, 'status_update_' . $statusBaru, (int)$laporanData['user_id'], 'email_status_' . $laporanId . '_' . $statusBaru . '_' . $timestamp);
        }
    }

    /**
     * EVENT: Laporan selesai (alias onStatusUpdate dengan status selesai)
     */
    public function onLaporanSelesai(int $laporanId, array $laporanData, string $catatan = ''): void {
        if (!$this->getSetting('notif_selesai', true)) return;
        $this->onStatusUpdate($laporanId, $laporanData, 'selesai', $catatan);
    }

    // ─────────────────────────────────────────────────────────
    // Retry: Kirim ulang notif yang pending/failed
    // ─────────────────────────────────────────────────────────

    /**
     * Proses retry pengiriman yang pending/failed (maks 3 percobaan)
     * Dipanggil oleh cron atau lazy trigger
     */
    public function processRetry(): int {
        $maxPerBatch = 10;
        $now = date('Y-m-d H:i:s');

        $sql = "SELECT * FROM notif_logs 
                WHERE status IN ('pending','failed') 
                AND percobaan < max_percobaan 
                AND (next_retry_at IS NULL OR next_retry_at <= :now)
                ORDER BY created_at ASC 
                LIMIT :limit";

        $rows = $this->db->query($sql)
            ->bind(':now', $now)
            ->bind(':limit', $maxPerBatch, PDO::PARAM_INT)
            ->resultSet();

        $processed = 0;
        foreach ($rows as $row) {
            $this->retryLog($row);
            $processed++;
        }

        return $processed;
    }

    // ─────────────────────────────────────────────────────────
    // Admin: Test Send
    // ─────────────────────────────────────────────────────────

    public function testWhatsApp(string $nomor, string $pesan): array {
        if (!$this->wa->isConfigured()) {
            return ['success' => false, 'message' => 'Token Fonnte belum dikonfigurasi.'];
        }
        $result = $this->wa->send($nomor, $pesan);
        $this->logDelivery([
            'kanal'       => 'whatsapp',
            'event_type'  => 'test',
            'penerima'    => WhatsAppService::normalizeNumber($nomor),
            'ringkasan'   => mb_substr($pesan, 0, 100),
            'status'      => $result['success'] ? 'sent' : 'failed',
            'ref_id'      => $result['ref_id'],
            'respons'     => !empty($result['response']) ? json_encode($result['response']) : null,
            'gagal'       => $result['error'],
        ]);
        return $result;
    }

    public function testEmail(string $alamat, string $subjek, string $pesan): array {
        if (!$this->email->isConfigured()) {
            return ['success' => false, 'message' => 'Konfigurasi SMTP belum lengkap.'];
        }
        $html = "<p>" . nl2br(htmlspecialchars($pesan)) . "</p>";
        $result = $this->email->send($alamat, $subjek, $html, $pesan);
        $this->logDelivery([
            'kanal'       => 'email',
            'event_type'  => 'test',
            'penerima'    => $alamat,
            'judul'       => $subjek,
            'ringkasan'   => mb_substr($pesan, 0, 100),
            'status'      => $result['success'] ? 'sent' : 'failed',
            'gagal'       => $result['error'],
        ]);
        return $result;
    }

    public function testFonnteConnection(): array {
        return $this->wa->testConnection();
    }

    public function testSmtpConnection(): array {
        return $this->email->testConnection();
    }

    // ─────────────────────────────────────────────────────────
    // Settings
    // ─────────────────────────────────────────────────────────

    public function getSetting(string $key, mixed $default = null): mixed {
        if (!isset($this->settings[$key])) {
            return $default;
        }

        $row  = $this->settings[$key];
        $val  = $row['setting_value'];
        $type = $row['setting_type'];

        return match($type) {
            'boolean' => in_array($val, ['1', 'true', true], true),
            'number'  => is_numeric($val) ? (int)$val : $default,
            'json'    => json_decode($val, true) ?? $default,
            default   => $val,
        };
    }

    public function getAllSettings(): array {
        return $this->settings;
    }

    public function updateSetting(string $key, string $value, int $updatedBy): bool {
        $sql = "UPDATE notif_settings SET setting_value = :val, updated_by = :uid WHERE setting_key = :key";
        return $this->db->query($sql)
            ->bind(':val', $value)
            ->bind(':uid', $updatedBy)
            ->bind(':key', $key)
            ->execute();
    }

    // ─────────────────────────────────────────────────────────
    // Internal Helpers
    // ─────────────────────────────────────────────────────────

    private function loadSettings(): void {
        try {
            $rows = $this->db->query("SELECT * FROM notif_settings")->resultSet();
            foreach ($rows as $row) {
                $this->settings[$row['setting_key']] = $row;
            }
        } catch (Exception $e) {
            // Tabel belum ada — abaikan, sistem tetap berjalan dengan default
            $this->settings = [];
        }
    }

    private function createInApp(int $userId, string $judul, string $pesan, string $tipe, string $tautan, int $laporanId = null, string $idempotencyKey = ''): void {
        // Cegah duplikasi via idempotency_key
        if ($idempotencyKey) {
            $check = $this->db->query("SELECT id FROM notifikasi WHERE idempotency_key = :k LIMIT 1")
                ->bind(':k', $idempotencyKey)
                ->single();
            if ($check) return;
        }

        $sql = "INSERT INTO notifikasi (user_id, laporan_id, judul, pesan, tipe, tautan, is_read, idempotency_key)
                VALUES (:uid, :lid, :judul, :pesan, :tipe, :tautan, 0, :ikey)";
        try {
            $this->db->query($sql)
                ->bind(':uid',   $userId)
                ->bind(':lid',   $laporanId, $laporanId ? PDO::PARAM_INT : PDO::PARAM_NULL)
                ->bind(':judul', mb_substr($judul, 0, 150))
                ->bind(':pesan', $pesan)
                ->bind(':tipe',  $tipe)
                ->bind(':tautan', $tautan)
                ->bind(':ikey',  $idempotencyKey ?: null)
                ->execute();
        } catch (Exception $e) {
            // Abaikan — in-app tidak boleh crash proses bisnis
        }
    }

    private function sendWa(string $nomor, string $pesan, int $laporanId, string $eventType, int $userId = null, string $idempotencyKey = ''): void {
        if (empty($nomor)) return;

        // Idempotency check
        if ($idempotencyKey && $this->isDuplicate($idempotencyKey)) return;

        $logId = $this->logDelivery([
            'user_id'       => $userId,
            'laporan_id'    => $laporanId,
            'kanal'         => 'whatsapp',
            'event_type'    => $eventType,
            'penerima'      => WhatsAppService::normalizeNumber($nomor),
            'ringkasan'     => mb_substr($pesan, 0, 100),
            'status'        => 'processing',
            'idempotency'   => $idempotencyKey,
        ]);

        $result = $this->wa->send($nomor, $pesan);

        $this->updateLog($logId, [
            'status'   => $result['success'] ? 'sent' : 'failed',
            'sent_at'  => $result['success'] ? date('Y-m-d H:i:s') : null,
            'ref_id'   => $result['ref_id'],
            'respons'  => $result['response'] ? json_encode($result['response']) : null,
            'gagal'    => $result['error'],
            'retry_at' => $result['success'] ? null : date('Y-m-d H:i:s', strtotime('+5 minutes')),
        ]);
    }

    private function sendEmail(string $alamat, string $subjek, string $html, string $text, int $laporanId, string $eventType, int $userId = null, string $idempotencyKey = ''): void {
        if (empty($alamat)) return;

        if ($idempotencyKey && $this->isDuplicate($idempotencyKey)) return;

        $logId = $this->logDelivery([
            'user_id'     => $userId,
            'laporan_id'  => $laporanId,
            'kanal'       => 'email',
            'event_type'  => $eventType,
            'penerima'    => $alamat,
            'judul'       => mb_substr($subjek, 0, 200),
            'ringkasan'   => mb_substr(strip_tags($html), 0, 100),
            'status'      => 'processing',
            'idempotency' => $idempotencyKey,
        ]);

        $result = $this->email->send($alamat, $subjek, $html, $text);

        $this->updateLog($logId, [
            'status'   => $result['success'] ? 'sent' : 'failed',
            'sent_at'  => $result['success'] ? date('Y-m-d H:i:s') : null,
            'gagal'    => $result['error'],
            'retry_at' => $result['success'] ? null : date('Y-m-d H:i:s', strtotime('+5 minutes')),
        ]);
    }

    private function logDelivery(array $data): int {
        $sql = "INSERT INTO notif_logs 
                (user_id, laporan_id, kanal, event_type, penerima, judul, ringkasan_pesan, status, ref_id, respons_provider, keterangan_gagal, idempotency_key, percobaan)
                VALUES (:uid, :lid, :kanal, :event, :penerima, :judul, :ringkasan, :status, :ref, :respons, :gagal, :ikey, 1)";
        try {
            $this->db->query($sql)
                ->bind(':uid',      $data['user_id'] ?? null, isset($data['user_id']) ? PDO::PARAM_INT : PDO::PARAM_NULL)
                ->bind(':lid',      $data['laporan_id'] ?? null, isset($data['laporan_id']) ? PDO::PARAM_INT : PDO::PARAM_NULL)
                ->bind(':kanal',    $data['kanal'])
                ->bind(':event',    $data['event_type'])
                ->bind(':penerima', $data['penerima'])
                ->bind(':judul',    $data['judul'] ?? null)
                ->bind(':ringkasan',$data['ringkasan'] ?? null)
                ->bind(':status',   $data['status'] ?? 'pending')
                ->bind(':ref',      $data['ref_id'] ?? null)
                ->bind(':respons',  isset($data['respons']) ? json_encode($data['respons']) : null)
                ->bind(':gagal',    $data['gagal'] ?? null)
                ->bind(':ikey',     $data['idempotency'] ?? null)
                ->execute();
            return (int)$this->db->lastInsertId();
        } catch (Exception $e) {
            return 0;
        }
    }

    private function updateLog(int $logId, array $data): void {
        if (!$logId) return;
        try {
            $sql = "UPDATE notif_logs SET 
                        status = :status,
                        sent_at = :sent_at,
                        next_retry_at = :retry_at,
                        ref_id = :ref,
                        respons_provider = :respons,
                        keterangan_gagal = :gagal,
                        percobaan = percobaan + 1
                    WHERE id = :id";
            $this->db->query($sql)
                ->bind(':status',   $data['status'])
                ->bind(':sent_at',  $data['sent_at'] ?? null)
                ->bind(':retry_at', $data['retry_at'] ?? null)
                ->bind(':ref',      $data['ref_id'] ?? null)
                ->bind(':respons',  $data['respons'] ?? null)
                ->bind(':gagal',    $data['gagal'] ?? null)
                ->bind(':id',       $logId, PDO::PARAM_INT)
                ->execute();
        } catch (Exception $e) {
            // silent
        }
    }

    private function retryLog(array $row): void {
        if ($row['kanal'] === 'whatsapp') {
            $result = $this->wa->send($row['penerima'], $row['ringkasan_pesan'] ?? '');
        } else {
            // Untuk email retry, pesan sudah dalam ringkasan — tidak bisa reconstruct HTML penuh
            // Skip retry email (kirim versi teks)
            $result = $this->email->send($row['penerima'], $row['judul'] ?? 'Notifikasi', '<p>' . htmlspecialchars($row['ringkasan_pesan'] ?? '') . '</p>', $row['ringkasan_pesan'] ?? '');
        }

        $newPercobaan = (int)$row['percobaan'] + 1;
        $maxPercobaan = (int)$row['max_percobaan'];
        $newStatus = $result['success'] ? 'sent' : ($newPercobaan >= $maxPercobaan ? 'failed' : 'pending');
        $nextRetry = (!$result['success'] && $newPercobaan < $maxPercobaan)
            ? date('Y-m-d H:i:s', strtotime('+' . (5 * $newPercobaan) . ' minutes'))
            : null;

        try {
            $sql = "UPDATE notif_logs SET 
                        status = :status, 
                        percobaan = :percobaan, 
                        sent_at = :sent_at,
                        next_retry_at = :retry,
                        ref_id = :ref,
                        keterangan_gagal = :gagal
                    WHERE id = :id";
            $this->db->query($sql)
                ->bind(':status',    $newStatus)
                ->bind(':percobaan', $newPercobaan)
                ->bind(':sent_at',   $result['success'] ? date('Y-m-d H:i:s') : null)
                ->bind(':retry',     $nextRetry)
                ->bind(':ref',       $result['ref_id'] ?? null)
                ->bind(':gagal',     $result['error'] ?? null)
                ->bind(':id',        (int)$row['id'], PDO::PARAM_INT)
                ->execute();
        } catch (Exception $e) {
            // silent
        }
    }

    private function isDuplicate(string $key): bool {
        try {
            $row = $this->db->query("SELECT id FROM notif_logs WHERE idempotency_key = :k AND status IN ('processing','sent') LIMIT 1")
                ->bind(':k', $key)
                ->single();
            return !empty($row);
        } catch (Exception $e) {
            return false;
        }
    }

    private function getUsersByRoles(array $roles): array {
        if (empty($roles)) return [];
        $placeholders = implode(',', array_fill(0, count($roles), '?'));
        $sql = "SELECT u.id, u.nama_lengkap, u.email, u.telepon, r.nama_role
                FROM users u 
                JOIN roles r ON u.role_id = r.id
                WHERE r.nama_role IN ($placeholders) AND u.status = 'aktif'";
        try {
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute($roles);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    private function buildDetailUrl(string $path): string {
        $base = getenv('APP_URL') ?: (defined('BASE_URL') ? BASE_URL : '');
        return rtrim($base, '/') . '/' . ltrim($path, '/');
    }
}

