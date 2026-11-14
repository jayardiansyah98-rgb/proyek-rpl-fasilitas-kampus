<?php
/**
 * AiLogModel
 * Model untuk merekam dan mengambil riwayat interaksi AI Assistant
 * Menyimpan data log: user_id, question, response, created_at, dan metrik performa.
 */

class AiLogModel extends Model {

    /**
     * Simpan log AI ke database
     * 
     * @param array $data [user_id, question, response, fitur, metadata_json, status, waktu_eksekusi_ms]
     * @return bool
     */
    public function record(array $data): bool {
        $userId = !empty($data['user_id']) ? (int)$data['user_id'] : null;
        $question = trim($data['question'] ?? '');
        $response = trim($data['response'] ?? '');
        $fitur = trim($data['fitur'] ?? 'ai_assistant');
        $metadataJson = $data['metadata_json'] ?? null;
        $status = in_array($data['status'] ?? '', ['sukses', 'gagal', 'pending'], true) ? $data['status'] : 'sukses';
        $waktuMs = isset($data['waktu_eksekusi_ms']) ? (int)$data['waktu_eksekusi_ms'] : 0;

        $sql = "INSERT INTO ai_logs 
                (user_id, question, response, fitur, prompt_input, response_output, metadata_json, status, waktu_eksekusi_ms, created_at)
                VALUES 
                (:user_id, :question, :response, :fitur, :prompt_input, :response_output, :metadata_json, :status, :waktu_ms, NOW())";

        $this->db->query($sql);
        $this->db->bind(':user_id', $userId, $userId ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $this->db->bind(':question', $question);
        $this->db->bind(':response', $response);
        $this->db->bind(':fitur', $fitur);
        $this->db->bind(':prompt_input', $question);
        $this->db->bind(':response_output', $response);
        $this->db->bind(':metadata_json', $metadataJson);
        $this->db->bind(':status', $status);
        $this->db->bind(':waktu_ms', $waktuMs, PDO::PARAM_INT);

        return $this->db->execute();
    }

    /**
     * Ambil riwayat percakapan pengguna terkini
     */
    public function getUserChatHistory(int $userId, int $limit = 15): array {
        $sql = "SELECT id, user_id, question, response, created_at, waktu_eksekusi_ms, metadata_json
                FROM ai_logs 
                WHERE user_id = :uid AND fitur = 'ai_assistant'
                ORDER BY created_at DESC 
                LIMIT " . (int)$limit;

        $rows = $this->db->query($sql)->bind(':uid', $userId)->resultSet();
        // Kembalikan dalam urutan kronologis ASC agar pesan lama di atas dan pesan baru di bawah
        return array_reverse($rows);
    }

    /**
     * Ambil seluruh log AI untuk audit Admin
     */
    public function getAllLogs(int $limit = 50): array {
        $sql = "SELECT a.*, u.nama_lengkap, u.email, u.jenis_pengguna
                FROM ai_logs a
                LEFT JOIN users u ON a.user_id = u.id
                ORDER BY a.created_at DESC
                LIMIT " . (int)$limit;

        return $this->db->query($sql)->resultSet();
    }

    /**
     * Hapus riwayat chat untuk pengguna tertentu
     */
    public function clearUserChat(int $userId): bool {
        $sql = "DELETE FROM ai_logs WHERE user_id = :uid AND fitur = 'ai_assistant'";
        return $this->db->query($sql)->bind(':uid', $userId)->execute();
    }
}
