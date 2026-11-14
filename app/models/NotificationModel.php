<?php
/**
 * NotificationModel — Operasi Database untuk Notification Center
 * Sistem Informasi Fasilitas STMIK IKMI Cirebon
 */

class NotificationModel extends Model {

    /**
     * Ambil notifikasi in-app milik user tertentu (dengan pagination)
     */
    public function getByUser(int $userId, int $page = 1, int $perPage = 20, ?string $tipe = null): array {
        $page   = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $sql    = "SELECT * FROM notifikasi WHERE user_id = :uid";
        $params = [':uid' => $userId];

        if ($tipe) {
            $sql .= " AND tipe = :tipe";
            $params[':tipe'] = $tipe;
        }

        $sql .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";

        $this->db->query($sql);
        foreach ($params as $key => $val) {
            $this->db->bind($key, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $this->db->bind(':limit',  $perPage, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset,  PDO::PARAM_INT);

        return $this->db->resultSet();
    }

    /**
     * Hitung total notifikasi user
     */
    public function countByUser(int $userId, ?string $tipe = null): int {
        $sql = "SELECT COUNT(*) as total FROM notifikasi WHERE user_id = :uid";
        if ($tipe) $sql .= " AND tipe = :tipe";

        $this->db->query($sql)->bind(':uid', $userId, PDO::PARAM_INT);
        if ($tipe) $this->db->bind(':tipe', $tipe);

        $row = $this->db->single();
        return (int)($row['total'] ?? 0);
    }

    /**
     * Hitung notifikasi belum dibaca
     */
    public function countUnread(int $userId): int {
        $row = $this->db->query("SELECT COUNT(*) as total FROM notifikasi WHERE user_id = :uid AND is_read = 0")
            ->bind(':uid', $userId, PDO::PARAM_INT)
            ->single();
        return (int)($row['total'] ?? 0);
    }

    /**
     * Ambil N notifikasi terbaru (untuk dropdown bell)
     */
    public function getLatest(int $userId, int $limit = 8): array {
        return $this->db->query("SELECT * FROM notifikasi WHERE user_id = :uid ORDER BY created_at DESC LIMIT :lim")
            ->bind(':uid', $userId, PDO::PARAM_INT)
            ->bind(':lim', $limit,  PDO::PARAM_INT)
            ->resultSet();
    }

    /**
     * Tandai satu notifikasi sebagai sudah dibaca
     */
    public function markRead(int $notifId, int $userId): bool {
        return $this->db->query("UPDATE notifikasi SET is_read = 1, read_at = NOW() WHERE id = :id AND user_id = :uid")
            ->bind(':id',  $notifId, PDO::PARAM_INT)
            ->bind(':uid', $userId,  PDO::PARAM_INT)
            ->execute();
    }

    /**
     * Tandai semua notifikasi user sebagai sudah dibaca
     */
    public function markAllRead(int $userId): bool {
        return $this->db->query("UPDATE notifikasi SET is_read = 1, read_at = NOW() WHERE user_id = :uid AND is_read = 0")
            ->bind(':uid', $userId, PDO::PARAM_INT)
            ->execute();
    }

    /**
     * Hapus notifikasi (hanya milik user bersangkutan)
     */
    public function delete(int $notifId, int $userId): bool {
        return $this->db->query("DELETE FROM notifikasi WHERE id = :id AND user_id = :uid")
            ->bind(':id',  $notifId, PDO::PARAM_INT)
            ->bind(':uid', $userId,  PDO::PARAM_INT)
            ->execute();
    }

    // ─────────────────────────────────────────────────────────
    // Log Pengiriman WA & Email (notif_logs)
    // ─────────────────────────────────────────────────────────

    /**
     * Ambil riwayat pengiriman WA/Email (untuk halaman admin)
     */
    public function getLogs(int $page = 1, int $perPage = 30, ?string $kanal = null, ?string $status = null): array {
        $page   = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $params = [];

        $sql = "SELECT nl.*, u.nama_lengkap, u.nomor_induk, lk.kode_laporan
                FROM notif_logs nl
                LEFT JOIN users u ON nl.user_id = u.id
                LEFT JOIN laporan_kerusakan lk ON nl.laporan_id = lk.id";

        $where = [];
        if ($kanal) { $where[] = "nl.kanal = :kanal"; $params[':kanal'] = $kanal; }
        if ($status) { $where[] = "nl.status = :status"; $params[':status'] = $status; }

        if ($where) $sql .= " WHERE " . implode(" AND ", $where);
        $sql .= " ORDER BY nl.created_at DESC LIMIT :limit OFFSET :offset";

        $this->db->query($sql);
        foreach ($params as $k => $v) $this->db->bind($k, $v);
        $this->db->bind(':limit',  $perPage, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset,  PDO::PARAM_INT);

        return $this->db->resultSet();
    }

    /**
     * Hitung total log pengiriman
     */
    public function countLogs(?string $kanal = null, ?string $status = null): int {
        $sql = "SELECT COUNT(*) as total FROM notif_logs";
        $where = [];
        $params = [];
        if ($kanal)  { $where[] = "kanal = :kanal";   $params[':kanal']  = $kanal; }
        if ($status) { $where[] = "status = :status";  $params[':status'] = $status; }
        if ($where)  $sql .= " WHERE " . implode(" AND ", $where);

        $this->db->query($sql);
        foreach ($params as $k => $v) $this->db->bind($k, $v);
        $row = $this->db->single();
        return (int)($row['total'] ?? 0);
    }

    /**
     * Statistik ringkasan log (untuk dashboard admin)
     */
    public function getLogStats(): array {
        $sql = "SELECT 
                    kanal,
                    COUNT(*) as total,
                    SUM(CASE WHEN status='sent'    THEN 1 ELSE 0 END) as sent,
                    SUM(CASE WHEN status='failed'  THEN 1 ELSE 0 END) as failed,
                    SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) as pending
                FROM notif_logs
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY kanal";
        return $this->db->query($sql)->resultSet();
    }

    /**
     * Ambil pengaturan notifikasi dari notif_settings
     */
    public function getSettings(): array {
        try {
            return $this->db->query("SELECT * FROM notif_settings ORDER BY id ASC")->resultSet();
        } catch (Exception $e) {
            return [];
        }
    }
}

