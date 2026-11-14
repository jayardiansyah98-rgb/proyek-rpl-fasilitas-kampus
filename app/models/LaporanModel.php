<?php
/**
 * LaporanModel
 * Menangani transaksi tiket pelaporan kerusakan fasilitas kampus STMIK IKMI Cirebon
 * Seluruh query menggunakan PDO Prepared Statements untuk pencegahan SQL Injection
 */

class LaporanModel extends Model {

    /**
     * Mengambil daftar laporan kerusakan dengan filter status, prioritas, pencarian, dan pagination
     * Jika $userId ditentukan, data dibatasi hanya untuk user tersebut (data scoping Pengguna)
     */
    public function getAll($userId = null, $status = null, $prioritas = null, $search = null, $page = 1, $perPage = 10) {
        $page = max(1, (int)$page);
        $perPage = max(1, (int)$perPage);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT l.*, 
                       f.nama as nama_fasilitas, f.kode_inventaris, f.kondisi as kondisi_fasilitas, f.foto as foto_fasilitas,
                       r.nama_ruangan, r.kode_ruangan, r.lantai,
                       g.id as gedung_id, g.nama_gedung, g.kode_gedung,
                       u.nama_lengkap as pelapor, u.nomor_induk, u.jenis_pengguna, u.email as email_pelapor
                FROM laporan_kerusakan l
                JOIN fasilitas f ON l.fasilitas_id = f.id
                JOIN ruangan r ON l.ruangan_id = r.id
                JOIN gedung g ON r.gedung_id = g.id
                JOIN users u ON l.user_id = u.id";

        $conditions = [];
        $params = [];

        if (!empty($userId)) {
            $conditions[] = "l.user_id = :uid";
            $params[':uid'] = (int)$userId;
        }

        if (!empty($status)) {
            $conditions[] = "l.status = :status";
            $params[':status'] = trim($status);
        }

        if (!empty($prioritas)) {
            $conditions[] = "l.prioritas = :prioritas";
            $params[':prioritas'] = trim($prioritas);
        }

        if (!empty($search)) {
            $conditions[] = "(l.kode_laporan LIKE :s1 OR f.nama LIKE :s2 OR f.kode_inventaris LIKE :s3 OR r.nama_ruangan LIKE :s4 OR g.nama_gedung LIKE :s5 OR l.deskripsi LIKE :s6 OR u.nama_lengkap LIKE :s7)";
            $searchTerm = '%' . trim($search) . '%';
            $params[':s1'] = $searchTerm;
            $params[':s2'] = $searchTerm;
            $params[':s3'] = $searchTerm;
            $params[':s4'] = $searchTerm;
            $params[':s5'] = $searchTerm;
            $params[':s6'] = $searchTerm;
            $params[':s7'] = $searchTerm;
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY l.created_at DESC LIMIT :limit OFFSET :offset";

        $this->db->query($sql);
        foreach ($params as $key => $val) {
            $this->db->bind($key, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $this->db->bind(':limit', $perPage, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);

        return $this->db->resultSet();
    }

    /**
     * Menghitung total data laporan kerusakan sesuai filter pencarian
     */
    public function countAll($userId = null, $status = null, $prioritas = null, $search = null) {
        $sql = "SELECT COUNT(*) as total
                FROM laporan_kerusakan l
                JOIN fasilitas f ON l.fasilitas_id = f.id
                JOIN ruangan r ON l.ruangan_id = r.id
                JOIN gedung g ON r.gedung_id = g.id
                JOIN users u ON l.user_id = u.id";

        $conditions = [];
        $params = [];

        if (!empty($userId)) {
            $conditions[] = "l.user_id = :uid";
            $params[':uid'] = (int)$userId;
        }

        if (!empty($status)) {
            $conditions[] = "l.status = :status";
            $params[':status'] = trim($status);
        }

        if (!empty($prioritas)) {
            $conditions[] = "l.prioritas = :prioritas";
            $params[':prioritas'] = trim($prioritas);
        }

        if (!empty($search)) {
            $conditions[] = "(l.kode_laporan LIKE :s1 OR f.nama LIKE :s2 OR f.kode_inventaris LIKE :s3 OR r.nama_ruangan LIKE :s4 OR g.nama_gedung LIKE :s5 OR l.deskripsi LIKE :s6 OR u.nama_lengkap LIKE :s7)";
            $searchTerm = '%' . trim($search) . '%';
            $params[':s1'] = $searchTerm;
            $params[':s2'] = $searchTerm;
            $params[':s3'] = $searchTerm;
            $params[':s4'] = $searchTerm;
            $params[':s5'] = $searchTerm;
            $params[':s6'] = $searchTerm;
            $params[':s7'] = $searchTerm;
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $this->db->query($sql);
        foreach ($params as $key => $val) {
            $this->db->bind($key, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }

        $row = $this->db->single();
        return $row ? (int)$row['total'] : 0;
    }

    /**
     * Mengambil rincian lengkap 1 tiket laporan kerusakan
     * @param int $id
     * @param int|null $userId Jika disertakan, memastikan laporan milik user bersangkutan (Data Scoping)
     */
    public function findById($id, $userId = null) {
        $sql = "SELECT l.*, 
                       f.nama as nama_fasilitas, f.kode_inventaris, f.kondisi as kondisi_fasilitas, f.foto as foto_fasilitas, f.keterangan as ket_fasilitas,
                       r.id as ruangan_id, r.nama_ruangan, r.kode_ruangan, r.lantai, r.jenis_ruangan,
                       g.id as gedung_id, g.nama_gedung, g.kode_gedung, g.lokasi as lokasi_gedung,
                       u.id as user_id, u.nama_lengkap as pelapor, u.nomor_induk, u.jenis_pengguna, u.email as email_pelapor, u.telepon as telepon_pelapor
                FROM laporan_kerusakan l
                JOIN fasilitas f ON l.fasilitas_id = f.id
                JOIN ruangan r ON l.ruangan_id = r.id
                JOIN gedung g ON r.gedung_id = g.id
                JOIN users u ON l.user_id = u.id
                WHERE l.id = :id";

        if (!empty($userId)) {
            $sql .= " AND l.user_id = :uid";
        }

        $this->db->query($sql)->bind(':id', (int)$id);
        if (!empty($userId)) {
            $this->db->bind(':uid', (int)$userId);
        }

        return $this->db->single();
    }

    /**
     * Membuat kode laporan baru otomatis berformat LAP-YYYYMM-XXX
     */
    public function generateKodeLaporan() {
        $prefix = 'LAP-' . date('Ym') . '-';
        $sql = "SELECT kode_laporan FROM laporan_kerusakan WHERE kode_laporan LIKE :prefix ORDER BY id DESC LIMIT 1";
        $last = $this->db->query($sql)->bind(':prefix', $prefix . '%')->single();

        if ($last && !empty($last['kode_laporan'])) {
            $parts = explode('-', $last['kode_laporan']);
            $lastNum = (int)end($parts);
            $newNum = str_pad($lastNum + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNum = '001';
        }

        return $prefix . $newNum;
    }

    /**
     * Menyimpan tiket laporan kerusakan baru ke database
     */
    public function create(array $data) {
        $sql = "INSERT INTO laporan_kerusakan 
                (kode_laporan, user_id, fasilitas_id, ruangan_id, deskripsi, foto, prioritas, status, catatan_petugas) 
                VALUES 
                (:kode, :uid, :fid, :rid, :deskripsi, :foto, :prioritas, :status, :catatan)";

        $status = $data['status'] ?? 'menunggu';
        $catatan = $data['catatan_petugas'] ?? null;

        $this->db->query($sql)
            ->bind(':kode', $data['kode_laporan'])
            ->bind(':uid', (int)$data['user_id'])
            ->bind(':fid', (int)$data['fasilitas_id'])
            ->bind(':rid', (int)$data['ruangan_id'])
            ->bind(':deskripsi', trim($data['deskripsi']))
            ->bind(':foto', $data['foto'] ?? null)
            ->bind(':prioritas', $data['prioritas'])
            ->bind(':status', $status)
            ->bind(':catatan', $catatan)
            ->execute();

        $newId = (int)$this->db->lastInsertId();

        // Buat notifikasi otomatis ke petugas sarpras
        try {
            $sqlPetugas = "SELECT id FROM users WHERE role_id = 2 AND status = 'aktif'";
            $petugasList = $this->db->query($sqlPetugas)->resultSet();
            foreach ($petugasList as $petugas) {
                $sqlNotif = "INSERT INTO notifikasi (user_id, laporan_id, judul, pesan, tipe, tautan, is_read) 
                             VALUES (:uid, :lid, :judul, :pesan, 'laporan', :tautan, 0)";
                $this->db->query($sqlNotif)
                    ->bind(':uid', $petugas['id'])
                    ->bind(':lid', $newId, PDO::PARAM_INT)
                    ->bind(':judul', 'Laporan Kerusakan Baru: ' . $data['kode_laporan'])
                    ->bind(':pesan', 'Terdapat laporan kerusakan fasilitas baru yang memerlukan verifikasi.')
                    ->bind(':tautan', 'laporan/detail/' . $newId)
                    ->execute();
            }
        } catch (Exception $e) {
            // Abaikan kesalahan notifikasi agar proses simpan utama tetap sukses
        }

        return $newId;
    }

    /**
     * Memperbarui status, prioritas, dan catatan petugas (Khusus Petugas Sarpras & Admin)
     */
    public function updateStatus($id, $status, $prioritas = null, $catatanPetugas = null) {
        // Ambil data laporan lama untuk notifikasi
        $laporan = $this->findById($id);
        if (!$laporan) {
            return false;
        }

        $fields = ["status = :status"];
        $params = [
            ':id' => (int)$id,
            ':status' => $status
        ];

        if (!empty($prioritas)) {
            $fields[] = "prioritas = :prioritas";
            $params[':prioritas'] = $prioritas;
        }

        if ($catatanPetugas !== null) {
            $fields[] = "catatan_petugas = :catatan";
            $params[':catatan'] = trim($catatanPetugas);
        }

        $sql = "UPDATE laporan_kerusakan SET " . implode(", ", $fields) . " WHERE id = :id";
        $stmt = $this->db->query($sql);
        foreach ($params as $key => $val) {
            $stmt->bind($key, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();

        // Buat notifikasi otomatis ke pelapor jika status berubah
        try {
            $statusLabels = [
                'menunggu' => 'Menunggu Verifikasi',
                'diverifikasi' => 'Telah Diverifikasi',
                'diproses' => 'Sedang Diproses Perbaikan',
                'selesai' => 'Selesai Diperbaiki',
                'ditolak' => 'Ditolak'
            ];
            $label = $statusLabels[$status] ?? ucfirst($status);

            $sqlNotif = "INSERT INTO notifikasi (user_id, laporan_id, judul, pesan, tipe, tautan, is_read) 
                         VALUES (:uid, :lid, :judul, :pesan, 'laporan', :tautan, 0)";
            $this->db->query($sqlNotif)
                ->bind(':uid', $laporan['user_id'])
                ->bind(':lid', (int)$id, PDO::PARAM_INT)
                ->bind(':judul', 'Update Tiket ' . $laporan['kode_laporan'] . ': ' . $label)
                ->bind(':pesan', 'Status laporan fasilitas Anda telah diperbarui menjadi "' . $label . '".' . (!empty($catatanPetugas) ? ' Catatan: ' . $catatanPetugas : ''))
                ->bind(':tautan', 'laporan/detail/' . $id)
                ->execute();
        } catch (Exception $e) {
            // Silently ignore notification failure
        }

        return true;
    }

    /**
     * Menghapus tiket laporan kerusakan (Khusus Admin)
     */
    public function delete($id) {
        $laporan = $this->findById($id);
        if (!$laporan) {
            return false;
        }

        // Hapus foto bukti jika ada
        if (!empty($laporan['foto'])) {
            $uploadDir = (defined('UPLOAD_PATH') ? UPLOAD_PATH : (__DIR__ . '/../../public/uploads')) . '/laporan/';
            $filePath = $uploadDir . basename($laporan['foto']);
            if (file_exists($filePath) && is_file($filePath)) {
                @unlink($filePath);
            }
        }

        $sql = "DELETE FROM laporan_kerusakan WHERE id = :id";
        return $this->db->query($sql)->bind(':id', (int)$id)->execute();
    }

    /**
     * Ringkasan agregasi jumlah laporan per status
     */
    public function getStatusSummary($userId = null) {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'menunggu' THEN 1 ELSE 0 END) as menunggu,
                    SUM(CASE WHEN status = 'diverifikasi' THEN 1 ELSE 0 END) as diverifikasi,
                    SUM(CASE WHEN status = 'diproses' THEN 1 ELSE 0 END) as diproses,
                    SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as selesai,
                    SUM(CASE WHEN status = 'ditolak' THEN 1 ELSE 0 END) as ditolak
                FROM laporan_kerusakan";

        if (!empty($userId)) {
            $sql .= " WHERE user_id = :uid";
            $row = $this->db->query($sql)->bind(':uid', (int)$userId)->single();
        } else {
            $row = $this->db->query($sql)->single();
        }

        return [
            'total' => (int)($row['total'] ?? 0),
            'menunggu' => (int)($row['menunggu'] ?? 0),
            'diverifikasi' => (int)($row['diverifikasi'] ?? 0),
            'diproses' => (int)($row['diproses'] ?? 0),
            'selesai' => (int)($row['selesai'] ?? 0),
            'ditolak' => (int)($row['ditolak'] ?? 0)
        ];
    }

    /**
     * Mengambil daftar seluruh ruangan untuk pilihan dropdown form
     */
    public function getRuanganList() {
        $sql = "SELECT r.id, r.nama_ruangan, r.kode_ruangan, r.lantai, g.nama_gedung, g.kode_gedung 
                FROM ruangan r
                JOIN gedung g ON r.gedung_id = g.id
                WHERE r.status != 'nonaktif'
                ORDER BY g.nama_gedung ASC, r.lantai ASC, r.nama_ruangan ASC";
        return $this->db->query($sql)->resultSet();
    }

    /**
     * Mengambil daftar fasilitas pada ruangan tertentu untuk AJAX chained dropdown
     */
    public function getFasilitasByRuangan($ruanganId) {
        $sql = "SELECT f.id, f.nama, f.kode_inventaris, f.kondisi, k.nama_kategori 
                FROM fasilitas f
                JOIN kategori_fasilitas k ON f.kategori_id = k.id
                WHERE f.ruangan_id = :rid
                ORDER BY f.nama ASC";
        return $this->db->query($sql)->bind(':rid', (int)$ruanganId)->resultSet();
    }
}
