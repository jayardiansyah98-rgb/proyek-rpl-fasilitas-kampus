<?php
/**
 * GedungModel
 * Menangani transaksi data fisik gedung kampus STMIK IKMI Cirebon
 * Menggunakan PDO Prepared Statements untuk keamanan query
 */

class GedungModel extends Model {
    /**
     * Mengambil daftar gedung dengan pencarian dan pagination
     */
    public function getAll($search = null, $page = 1, $perPage = 10) {
        $page = max(1, (int)$page);
        $perPage = max(1, (int)$perPage);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT g.*, 
                       (SELECT COUNT(*) FROM ruangan WHERE gedung_id = g.id) as total_ruangan 
                FROM gedung g";

        $params = [];
        if (!empty($search)) {
            $sql .= " WHERE (g.kode_gedung LIKE :s1 OR g.nama_gedung LIKE :s2 OR g.lokasi LIKE :s3 OR g.keterangan LIKE :s4)";
            $searchTerm = '%' . trim($search) . '%';
            $params[':s1'] = $searchTerm;
            $params[':s2'] = $searchTerm;
            $params[':s3'] = $searchTerm;
            $params[':s4'] = $searchTerm;
        }

        $sql .= " ORDER BY g.kode_gedung ASC LIMIT :limit OFFSET :offset";

        $this->db->query($sql);
        foreach ($params as $key => $val) {
            $this->db->bind($key, $val, PDO::PARAM_STR);
        }
        $this->db->bind(':limit', $perPage, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);

        return $this->db->resultSet();
    }

    /**
     * Menghitung total data gedung berdasarkan filter pencarian
     */
    public function countAll($search = null) {
        $sql = "SELECT COUNT(*) as total FROM gedung g";
        $params = [];

        if (!empty($search)) {
            $sql .= " WHERE (g.kode_gedung LIKE :s1 OR g.nama_gedung LIKE :s2 OR g.lokasi LIKE :s3 OR g.keterangan LIKE :s4)";
            $searchTerm = '%' . trim($search) . '%';
            $params[':s1'] = $searchTerm;
            $params[':s2'] = $searchTerm;
            $params[':s3'] = $searchTerm;
            $params[':s4'] = $searchTerm;
        }

        $this->db->query($sql);
        foreach ($params as $key => $val) {
            $this->db->bind($key, $val, PDO::PARAM_STR);
        }

        $result = $this->db->single();
        return (int)($result['total'] ?? 0);
    }

    /**
     * Mengambil seluruh gedung aktif untuk dropdown pilihan
     */
    public function getAllActive() {
        $sql = "SELECT id, kode_gedung, nama_gedung, lokasi, jumlah_lantai, status 
                FROM gedung 
                ORDER BY nama_gedung ASC";
        return $this->db->query($sql)->resultSet();
    }

    /**
     * Mengambil 1 data gedung berdasarkan ID
     */
    public function findById($id) {
        $sql = "SELECT g.*, 
                       (SELECT COUNT(*) FROM ruangan WHERE gedung_id = g.id) as total_ruangan 
                FROM gedung g 
                WHERE g.id = :id 
                LIMIT 1";

        $this->db->query($sql);
        $this->db->bind(':id', (int)$id, PDO::PARAM_INT);
        return $this->db->single();
    }

    /**
     * Mencari gedung berdasarkan kode unik (untuk validasi form)
     */
    public function findByKode($kode, $excludeId = null) {
        $sql = "SELECT * FROM gedung WHERE kode_gedung = :kode";
        if ($excludeId !== null) {
            $sql .= " AND id != :exclude_id";
        }
        $sql .= " LIMIT 1";

        $this->db->query($sql);
        $this->db->bind(':kode', trim($kode));
        if ($excludeId !== null) {
            $this->db->bind(':exclude_id', (int)$excludeId, PDO::PARAM_INT);
        }

        return $this->db->single();
    }

    /**
     * Menyimpan data gedung baru
     */
    public function create(array $data) {
        $sql = "INSERT INTO gedung (kode_gedung, nama_gedung, lokasi, keterangan, deskripsi, jumlah_lantai, status) 
                VALUES (:kode_gedung, :nama_gedung, :lokasi, :keterangan, :deskripsi, :jumlah_lantai, :status)";

        $keterangan = $data['keterangan'] ?? null;
        $deskripsi = $data['deskripsi'] ?? $keterangan;
        $jumlahLantai = isset($data['jumlah_lantai']) ? (int)$data['jumlah_lantai'] : 1;
        $status = in_array($data['status'] ?? '', ['aktif', 'renovasi', 'nonaktif']) ? $data['status'] : 'aktif';

        $this->db->query($sql);
        $this->db->bind(':kode_gedung', strtoupper(trim($data['kode_gedung'])));
        $this->db->bind(':nama_gedung', trim($data['nama_gedung']));
        $this->db->bind(':lokasi', trim($data['lokasi'] ?? ''));
        $this->db->bind(':keterangan', $keterangan);
        $this->db->bind(':deskripsi', $deskripsi);
        $this->db->bind(':jumlah_lantai', $jumlahLantai, PDO::PARAM_INT);
        $this->db->bind(':status', $status);

        $this->db->execute();
        return $this->db->lastInsertId();
    }

    /**
     * Memperbarui data gedung
     */
    public function update($id, array $data) {
        $sql = "UPDATE gedung 
                SET kode_gedung = :kode_gedung, 
                    nama_gedung = :nama_gedung, 
                    lokasi = :lokasi, 
                    keterangan = :keterangan, 
                    deskripsi = :deskripsi, 
                    jumlah_lantai = :jumlah_lantai, 
                    status = :status 
                WHERE id = :id";

        $keterangan = $data['keterangan'] ?? null;
        $deskripsi = $data['deskripsi'] ?? $keterangan;
        $jumlahLantai = isset($data['jumlah_lantai']) ? (int)$data['jumlah_lantai'] : 1;
        $status = in_array($data['status'] ?? '', ['aktif', 'renovasi', 'nonaktif']) ? $data['status'] : 'aktif';

        $this->db->query($sql);
        $this->db->bind(':id', (int)$id, PDO::PARAM_INT);
        $this->db->bind(':kode_gedung', strtoupper(trim($data['kode_gedung'])));
        $this->db->bind(':nama_gedung', trim($data['nama_gedung']));
        $this->db->bind(':lokasi', trim($data['lokasi'] ?? ''));
        $this->db->bind(':keterangan', $keterangan);
        $this->db->bind(':deskripsi', $deskripsi);
        $this->db->bind(':jumlah_lantai', $jumlahLantai, PDO::PARAM_INT);
        $this->db->bind(':status', $status);

        return $this->db->execute();
    }

    /**
     * Menghapus data gedung
     */
    public function delete($id) {
        $sql = "DELETE FROM gedung WHERE id = :id";
        $this->db->query($sql);
        $this->db->bind(':id', (int)$id, PDO::PARAM_INT);
        return $this->db->execute();
    }

    /**
     * Menghitung total ruangan pada suatu gedung (validasi relasi)
     */
    public function countRuangan($id) {
        $sql = "SELECT COUNT(*) as total FROM ruangan WHERE gedung_id = :id";
        $this->db->query($sql);
        $this->db->bind(':id', (int)$id, PDO::PARAM_INT);
        $res = $this->db->single();
        return (int)($res['total'] ?? 0);
    }

    /**
     * Mengambil daftar seluruh ruangan pada gedung tertentu (untuk halaman detail gedung)
     */
    public function getRuanganByGedung($id) {
        $sql = "SELECT r.*, 
                       (SELECT COUNT(*) FROM fasilitas WHERE ruangan_id = r.id) as total_fasilitas 
                FROM ruangan r 
                WHERE r.gedung_id = :id 
                ORDER BY r.lantai ASC, r.kode_ruangan ASC";

        $this->db->query($sql);
        $this->db->bind(':id', (int)$id, PDO::PARAM_INT);
        return $this->db->resultSet();
    }
}
