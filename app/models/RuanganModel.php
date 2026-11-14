<?php
/**
 * RuanganModel
 * Menangani transaksi data ruangan & laboratorium pada gedung kampus
 * Menggunakan PDO Prepared Statements untuk pencegahan SQL Injection
 */

class RuanganModel extends Model {
    /**
     * Mengambil daftar ruangan dengan pencarian, filter gedung, filter jenis, dan pagination
     */
    public function getAll($search = null, $gedungId = null, $jenisRuangan = null, $page = 1, $perPage = 10) {
        $page = max(1, (int)$page);
        $perPage = max(1, (int)$perPage);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT r.*, g.nama_gedung, g.kode_gedung, g.lokasi as lokasi_gedung,
                       (SELECT COUNT(*) FROM fasilitas WHERE ruangan_id = r.id) as total_fasilitas 
                FROM ruangan r 
                JOIN gedung g ON r.gedung_id = g.id";

        $conditions = [];
        $params = [];

        if (!empty($search)) {
            $conditions[] = "(r.kode_ruangan LIKE :s1 OR r.nama_ruangan LIKE :s2 OR r.keterangan LIKE :s3 OR g.nama_gedung LIKE :s4)";
            $searchTerm = '%' . trim($search) . '%';
            $params[':s1'] = $searchTerm;
            $params[':s2'] = $searchTerm;
            $params[':s3'] = $searchTerm;
            $params[':s4'] = $searchTerm;
        }

        if (!empty($gedungId)) {
            $conditions[] = "r.gedung_id = :gedung_id";
            $params[':gedung_id'] = (int)$gedungId;
        }

        if (!empty($jenisRuangan)) {
            $conditions[] = "r.jenis_ruangan = :jenis_ruangan";
            $params[':jenis_ruangan'] = trim($jenisRuangan);
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY g.nama_gedung ASC, r.lantai ASC, r.kode_ruangan ASC LIMIT :limit OFFSET :offset";

        $this->db->query($sql);
        foreach ($params as $key => $val) {
            $this->db->bind($key, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $this->db->bind(':limit', $perPage, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);

        return $this->db->resultSet();
    }

    /**
     * Menghitung total data ruangan berdasarkan filter untuk pagination
     */
    public function countAll($search = null, $gedungId = null, $jenisRuangan = null) {
        $sql = "SELECT COUNT(*) as total FROM ruangan r JOIN gedung g ON r.gedung_id = g.id";
        $conditions = [];
        $params = [];

        if (!empty($search)) {
            $conditions[] = "(r.kode_ruangan LIKE :s1 OR r.nama_ruangan LIKE :s2 OR r.keterangan LIKE :s3 OR g.nama_gedung LIKE :s4)";
            $searchTerm = '%' . trim($search) . '%';
            $params[':s1'] = $searchTerm;
            $params[':s2'] = $searchTerm;
            $params[':s3'] = $searchTerm;
            $params[':s4'] = $searchTerm;
        }

        if (!empty($gedungId)) {
            $conditions[] = "r.gedung_id = :gedung_id";
            $params[':gedung_id'] = (int)$gedungId;
        }

        if (!empty($jenisRuangan)) {
            $conditions[] = "r.jenis_ruangan = :jenis_ruangan";
            $params[':jenis_ruangan'] = trim($jenisRuangan);
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $this->db->query($sql);
        foreach ($params as $key => $val) {
            $this->db->bind($key, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }

        $result = $this->db->single();
        return (int)($result['total'] ?? 0);
    }

    /**
     * Mengambil 1 data ruangan beserta relasi nama gedung
     */
    public function findById($id) {
        $sql = "SELECT r.*, g.nama_gedung, g.kode_gedung, g.lokasi as lokasi_gedung,
                       (SELECT COUNT(*) FROM fasilitas WHERE ruangan_id = r.id) as total_fasilitas 
                FROM ruangan r 
                JOIN gedung g ON r.gedung_id = g.id 
                WHERE r.id = :id 
                LIMIT 1";

        $this->db->query($sql);
        $this->db->bind(':id', (int)$id, PDO::PARAM_INT);
        return $this->db->single();
    }

    /**
     * Mencari ruangan berdasarkan kode unik (untuk validasi form)
     */
    public function findByKode($kode, $excludeId = null) {
        $sql = "SELECT * FROM ruangan WHERE kode_ruangan = :kode";
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
     * Menyimpan data ruangan baru
     */
    public function create(array $data) {
        $sql = "INSERT INTO ruangan (gedung_id, kode_ruangan, nama_ruangan, lantai, jenis_ruangan, kapasitas, status, keterangan) 
                VALUES (:gedung_id, :kode_ruangan, :nama_ruangan, :lantai, :jenis_ruangan, :kapasitas, :status, :keterangan)";

        $lantai = max(1, (int)($data['lantai'] ?? 1));
        $kapasitas = max(0, (int)($data['kapasitas'] ?? 0));
        $validJenis = ['Kelas', 'Laboratorium', 'Ruang Dosen', 'Ruang Administrasi', 'Ruang Rapat', 'Ruang Seminar', 'Lainnya'];
        $jenis = in_array($data['jenis_ruangan'] ?? '', $validJenis) ? $data['jenis_ruangan'] : 'Kelas';
        $validStatus = ['tersedia', 'digunakan', 'perbaikan', 'nonaktif'];
        $status = in_array($data['status'] ?? '', $validStatus) ? $data['status'] : 'tersedia';

        $this->db->query($sql);
        $this->db->bind(':gedung_id', (int)$data['gedung_id'], PDO::PARAM_INT);
        $this->db->bind(':kode_ruangan', strtoupper(trim($data['kode_ruangan'])));
        $this->db->bind(':nama_ruangan', trim($data['nama_ruangan']));
        $this->db->bind(':lantai', $lantai, PDO::PARAM_INT);
        $this->db->bind(':jenis_ruangan', $jenis);
        $this->db->bind(':kapasitas', $kapasitas, PDO::PARAM_INT);
        $this->db->bind(':status', $status);
        $this->db->bind(':keterangan', trim($data['keterangan'] ?? ''));

        $this->db->execute();
        return $this->db->lastInsertId();
    }

    /**
     * Memperbarui data ruangan
     */
    public function update($id, array $data) {
        $sql = "UPDATE ruangan 
                SET gedung_id = :gedung_id, 
                    kode_ruangan = :kode_ruangan, 
                    nama_ruangan = :nama_ruangan, 
                    lantai = :lantai, 
                    jenis_ruangan = :jenis_ruangan, 
                    kapasitas = :kapasitas, 
                    status = :status, 
                    keterangan = :keterangan 
                WHERE id = :id";

        $lantai = max(1, (int)($data['lantai'] ?? 1));
        $kapasitas = max(0, (int)($data['kapasitas'] ?? 0));
        $validJenis = ['Kelas', 'Laboratorium', 'Ruang Dosen', 'Ruang Administrasi', 'Ruang Rapat', 'Ruang Seminar', 'Lainnya'];
        $jenis = in_array($data['jenis_ruangan'] ?? '', $validJenis) ? $data['jenis_ruangan'] : 'Kelas';
        $validStatus = ['tersedia', 'digunakan', 'perbaikan', 'nonaktif'];
        $status = in_array($data['status'] ?? '', $validStatus) ? $data['status'] : 'tersedia';

        $this->db->query($sql);
        $this->db->bind(':id', (int)$id, PDO::PARAM_INT);
        $this->db->bind(':gedung_id', (int)$data['gedung_id'], PDO::PARAM_INT);
        $this->db->bind(':kode_ruangan', strtoupper(trim($data['kode_ruangan'])));
        $this->db->bind(':nama_ruangan', trim($data['nama_ruangan']));
        $this->db->bind(':lantai', $lantai, PDO::PARAM_INT);
        $this->db->bind(':jenis_ruangan', $jenis);
        $this->db->bind(':kapasitas', $kapasitas, PDO::PARAM_INT);
        $this->db->bind(':status', $status);
        $this->db->bind(':keterangan', trim($data['keterangan'] ?? ''));

        return $this->db->execute();
    }

    /**
     * Menghapus data ruangan
     */
    public function delete($id) {
        $sql = "DELETE FROM ruangan WHERE id = :id";
        $this->db->query($sql);
        $this->db->bind(':id', (int)$id, PDO::PARAM_INT);
        return $this->db->execute();
    }

    /**
     * Menghitung fasilitas terpasang di ruangan ini (validasi relasi)
     */
    public function countFasilitas($id) {
        $sql = "SELECT COUNT(*) as total FROM fasilitas WHERE ruangan_id = :id";
        $this->db->query($sql);
        $this->db->bind(':id', (int)$id, PDO::PARAM_INT);
        $res = $this->db->single();
        return (int)($res['total'] ?? 0);
    }

    /**
     * Menghitung laporan kerusakan yang menunjuk ke ruangan ini (validasi relasi)
     */
    public function countLaporan($id) {
        $sql = "SELECT COUNT(*) as total FROM laporan_kerusakan WHERE ruangan_id = :id";
        $this->db->query($sql);
        $this->db->bind(':id', (int)$id, PDO::PARAM_INT);
        $res = $this->db->single();
        return (int)($res['total'] ?? 0);
    }

    /**
     * Mengambil daftar fasilitas pada ruangan tertentu (untuk detail ruangan)
     */
    public function getFasilitasByRuangan($id) {
        $sql = "SELECT f.*, k.nama_kategori 
                FROM fasilitas f 
                JOIN kategori_fasilitas k ON f.kategori_id = k.id 
                WHERE f.ruangan_id = :id 
                ORDER BY f.nama ASC";

        $this->db->query($sql);
        $this->db->bind(':id', (int)$id, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    /**
     * Mengambil riwayat laporan kerusakan pada ruangan tertentu (untuk detail ruangan)
     */
    public function getLaporanByRuangan($id) {
        $sql = "SELECT l.*, u.nama_lengkap as pelapor, f.nama as nama_fasilitas 
                FROM laporan_kerusakan l 
                JOIN users u ON l.user_id = u.id 
                LEFT JOIN fasilitas f ON l.fasilitas_id = f.id 
                WHERE l.ruangan_id = :id 
                ORDER BY l.created_at DESC 
                LIMIT 10";

        $this->db->query($sql);
        $this->db->bind(':id', (int)$id, PDO::PARAM_INT);
        return $this->db->resultSet();
    }
}
