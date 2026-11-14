<?php
/**
 * FasilitasModel
 * Menangani transaksi master inventaris sarana dan prasarana kampus STMIK IKMI Cirebon
 * Menggunakan PDO Prepared Statements untuk keamanan query
 */

class FasilitasModel extends Model {
    /**
     * Mengambil daftar fasilitas dengan filter multi-dimensi (gedung, ruangan, kategori, kondisi), pencarian, dan pagination
     */
    public function getAll($search = null, $gedungId = null, $ruanganId = null, $kategoriId = null, $kondisi = null, $page = 1, $perPage = 10) {
        $page = max(1, (int)$page);
        $perPage = max(1, (int)$perPage);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT f.*, f.nama as nama_fasilitas, 
                       k.nama_kategori, 
                       r.nama_ruangan, r.kode_ruangan, r.lantai, 
                       g.id as gedung_id, g.nama_gedung, g.kode_gedung, g.lokasi as lokasi_gedung 
                FROM fasilitas f
                JOIN kategori_fasilitas k ON f.kategori_id = k.id
                JOIN ruangan r ON f.ruangan_id = r.id
                JOIN gedung g ON r.gedung_id = g.id";

        $conditions = [];
        $params = [];

        if (!empty($search)) {
            $conditions[] = "(f.kode_inventaris LIKE :s1 OR f.nama LIKE :s2 OR f.keterangan LIKE :s3)";
            $searchTerm = '%' . trim($search) . '%';
            $params[':s1'] = $searchTerm;
            $params[':s2'] = $searchTerm;
            $params[':s3'] = $searchTerm;
        }

        if (!empty($gedungId)) {
            $conditions[] = "r.gedung_id = :gedung_id";
            $params[':gedung_id'] = (int)$gedungId;
        }

        if (!empty($ruanganId)) {
            $conditions[] = "f.ruangan_id = :ruangan_id";
            $params[':ruangan_id'] = (int)$ruanganId;
        }

        if (!empty($kategoriId)) {
            $conditions[] = "f.kategori_id = :kategori_id";
            $params[':kategori_id'] = (int)$kategoriId;
        }

        if (!empty($kondisi)) {
            $conditions[] = "f.kondisi = :kondisi";
            $params[':kondisi'] = trim($kondisi);
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY f.id DESC LIMIT :limit OFFSET :offset";

        $this->db->query($sql);
        foreach ($params as $key => $val) {
            $this->db->bind($key, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $this->db->bind(':limit', $perPage, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);

        return $this->db->resultSet();
    }

    /**
     * Menghitung total data fasilitas berdasarkan kriteria pencarian & filter
     */
    public function countAll($search = null, $gedungId = null, $ruanganId = null, $kategoriId = null, $kondisi = null) {
        $sql = "SELECT COUNT(*) as total 
                FROM fasilitas f
                JOIN kategori_fasilitas k ON f.kategori_id = k.id
                JOIN ruangan r ON f.ruangan_id = r.id
                JOIN gedung g ON r.gedung_id = g.id";

        $conditions = [];
        $params = [];

        if (!empty($search)) {
            $conditions[] = "(f.kode_inventaris LIKE :s1 OR f.nama LIKE :s2 OR f.keterangan LIKE :s3)";
            $searchTerm = '%' . trim($search) . '%';
            $params[':s1'] = $searchTerm;
            $params[':s2'] = $searchTerm;
            $params[':s3'] = $searchTerm;
        }

        if (!empty($gedungId)) {
            $conditions[] = "r.gedung_id = :gedung_id";
            $params[':gedung_id'] = (int)$gedungId;
        }

        if (!empty($ruanganId)) {
            $conditions[] = "f.ruangan_id = :ruangan_id";
            $params[':ruangan_id'] = (int)$ruanganId;
        }

        if (!empty($kategoriId)) {
            $conditions[] = "f.kategori_id = :kategori_id";
            $params[':kategori_id'] = (int)$kategoriId;
        }

        if (!empty($kondisi)) {
            $conditions[] = "f.kondisi = :kondisi";
            $params[':kondisi'] = trim($kondisi);
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $this->db->query($sql);
        foreach ($params as $key => $val) {
            $this->db->bind($key, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }

        $res = $this->db->single();
        return (int)($res['total'] ?? 0);
    }

    /**
     * Mengambil 1 data fasilitas lengkap dengan relasi kategori, ruangan, dan gedung
     */
    public function findById($id) {
        $sql = "SELECT f.*, f.nama as nama_fasilitas, 
                       k.nama_kategori, 
                       r.nama_ruangan, r.kode_ruangan, r.lantai, 
                       g.id as gedung_id, g.nama_gedung, g.kode_gedung, g.lokasi as lokasi_gedung 
                FROM fasilitas f
                JOIN kategori_fasilitas k ON f.kategori_id = k.id
                JOIN ruangan r ON f.ruangan_id = r.id
                JOIN gedung g ON r.gedung_id = g.id
                WHERE f.id = :id 
                LIMIT 1";

        $this->db->query($sql);
        $this->db->bind(':id', (int)$id, PDO::PARAM_INT);
        return $this->db->single();
    }

    /**
     * Mencari fasilitas berdasarkan kode inventaris (untuk validasi form unik)
     */
    public function findByKode($kode, $excludeId = null) {
        $sql = "SELECT * FROM fasilitas WHERE kode_inventaris = :kode";
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
     * Menyimpan data fasilitas baru
     */
    public function create(array $data) {
        $sql = "INSERT INTO fasilitas (kode_inventaris, nama, kategori_id, ruangan_id, jumlah, kondisi, tahun_pengadaan, foto, keterangan) 
                VALUES (:kode_inventaris, :nama, :kategori_id, :ruangan_id, :jumlah, :kondisi, :tahun_pengadaan, :foto, :keterangan)";

        $namaFasilitas = trim($data['nama_fasilitas'] ?? $data['nama'] ?? '');
        $jumlah = max(1, (int)($data['jumlah'] ?? 1));
        $validKondisi = ['baik', 'rusak_ringan', 'rusak_berat', 'tidak_layak', 'hilang'];
        $kondisi = in_array($data['kondisi'] ?? '', $validKondisi) ? $data['kondisi'] : 'baik';
        $tahun = (int)($data['tahun_pengadaan'] ?? date('Y'));

        $this->db->query($sql);
        $this->db->bind(':kode_inventaris', strtoupper(trim($data['kode_inventaris'])));
        $this->db->bind(':nama', $namaFasilitas);
        $this->db->bind(':kategori_id', (int)$data['kategori_id'], PDO::PARAM_INT);
        $this->db->bind(':ruangan_id', (int)$data['ruangan_id'], PDO::PARAM_INT);
        $this->db->bind(':jumlah', $jumlah, PDO::PARAM_INT);
        $this->db->bind(':kondisi', $kondisi);
        $this->db->bind(':tahun_pengadaan', $tahun, PDO::PARAM_INT);
        $this->db->bind(':foto', $data['foto'] ?? null);
        $this->db->bind(':keterangan', trim($data['keterangan'] ?? ''));

        $this->db->execute();
        return $this->db->lastInsertId();
    }

    /**
     * Memperbarui data fasilitas
     */
    public function update($id, array $data) {
        $sql = "UPDATE fasilitas 
                SET kode_inventaris = :kode_inventaris, 
                    nama = :nama, 
                    kategori_id = :kategori_id, 
                    ruangan_id = :ruangan_id, 
                    jumlah = :jumlah, 
                    kondisi = :kondisi, 
                    tahun_pengadaan = :tahun_pengadaan, 
                    foto = :foto, 
                    keterangan = :keterangan 
                WHERE id = :id";

        $namaFasilitas = trim($data['nama_fasilitas'] ?? $data['nama'] ?? '');
        $jumlah = max(1, (int)($data['jumlah'] ?? 1));
        $validKondisi = ['baik', 'rusak_ringan', 'rusak_berat', 'tidak_layak', 'hilang'];
        $kondisi = in_array($data['kondisi'] ?? '', $validKondisi) ? $data['kondisi'] : 'baik';
        $tahun = (int)($data['tahun_pengadaan'] ?? date('Y'));

        $this->db->query($sql);
        $this->db->bind(':id', (int)$id, PDO::PARAM_INT);
        $this->db->bind(':kode_inventaris', strtoupper(trim($data['kode_inventaris'])));
        $this->db->bind(':nama', $namaFasilitas);
        $this->db->bind(':kategori_id', (int)$data['kategori_id'], PDO::PARAM_INT);
        $this->db->bind(':ruangan_id', (int)$data['ruangan_id'], PDO::PARAM_INT);
        $this->db->bind(':jumlah', $jumlah, PDO::PARAM_INT);
        $this->db->bind(':kondisi', $kondisi);
        $this->db->bind(':tahun_pengadaan', $tahun, PDO::PARAM_INT);
        $this->db->bind(':foto', $data['foto'] ?? null);
        $this->db->bind(':keterangan', trim($data['keterangan'] ?? ''));

        return $this->db->execute();
    }

    /**
     * Aksi cepat pembaruan kondisi fisik fasilitas (Petugas Sarpras / Admin)
     */
    public function updateKondisi($id, $kondisi, $catatan = null) {
        $validKondisi = ['baik', 'rusak_ringan', 'rusak_berat', 'tidak_layak', 'hilang'];
        if (!in_array($kondisi, $validKondisi)) {
            return false;
        }

        if (!empty($catatan)) {
            $catatanEntry = "\n[" . date('Y-m-d H:i:s') . "] Kondisi diubah: " . $kondisi . " - " . trim($catatan);
            $sql = "UPDATE fasilitas SET kondisi = :kondisi, keterangan = CONCAT(COALESCE(keterangan, ''), :catatan) WHERE id = :id";
            $this->db->query($sql);
            $this->db->bind(':id', (int)$id, PDO::PARAM_INT);
            $this->db->bind(':kondisi', $kondisi);
            $this->db->bind(':catatan', $catatanEntry);
        } else {
            $sql = "UPDATE fasilitas SET kondisi = :kondisi WHERE id = :id";
            $this->db->query($sql);
            $this->db->bind(':id', (int)$id, PDO::PARAM_INT);
            $this->db->bind(':kondisi', $kondisi);
        }

        return $this->db->execute();
    }

    /**
     * Menghapus fasilitas dari database
     */
    public function delete($id) {
        $sql = "DELETE FROM fasilitas WHERE id = :id";
        $this->db->query($sql);
        $this->db->bind(':id', (int)$id, PDO::PARAM_INT);
        return $this->db->execute();
    }

    /**
     * Menghitung total pemeliharaan yang terhubung ke fasilitas ini (validasi relasi)
     */
    public function countPemeliharaan($id) {
        $sql = "SELECT COUNT(*) as total FROM pemeliharaan WHERE fasilitas_id = :id";
        $this->db->query($sql);
        $this->db->bind(':id', (int)$id, PDO::PARAM_INT);
        $res = $this->db->single();
        return (int)($res['total'] ?? 0);
    }

    /**
     * Menghitung total laporan kerusakan yang terhubung ke fasilitas ini (validasi relasi)
     */
    public function countLaporan($id) {
        $sql = "SELECT COUNT(*) as total FROM laporan_kerusakan WHERE fasilitas_id = :id";
        $this->db->query($sql);
        $this->db->bind(':id', (int)$id, PDO::PARAM_INT);
        $res = $this->db->single();
        return (int)($res['total'] ?? 0);
    }

    /**
     * Mengambil daftar riwayat pemeliharaan pada fasilitas ini (untuk detail fasilitas)
     * Jika $onlyPublic bernilai true (Dosen/Mahasiswa), hanya tampilkan pemeliharaan bertanda tampil_publik
     */
    public function getRiwayatPemeliharaan($id, $onlyPublic = false) {
        $sql = "SELECT p.*, u.nama_lengkap as nama_petugas, l.kode_laporan 
                FROM pemeliharaan p 
                JOIN users u ON p.petugas_id = u.id 
                LEFT JOIN laporan_kerusakan l ON p.laporan_id = l.id
                WHERE p.fasilitas_id = :id";

        if ($onlyPublic) {
            $sql .= " AND p.tampil_publik = 1";
        }

        $sql .= " ORDER BY COALESCE(p.tanggal, p.tanggal_mulai) DESC, p.id DESC";

        $this->db->query($sql);
        $this->db->bind(':id', (int)$id, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    /**
     * Menghitung ringkasan statistik pemeliharaan dan kerusakan fasilitas:
     * - Total pemeliharaan
     * - Total biaya
     * - Jumlah kerusakan
     */
    public function getStatistikFasilitas($id, $onlyPublic = false) {
        $sqlP = "SELECT 
                    COUNT(*) as total_pemeliharaan,
                    COALESCE(SUM(p.biaya), 0) as total_biaya,
                    COUNT(CASE WHEN p.status = 'selesai' THEN 1 END) as selesai,
                    COUNT(CASE WHEN p.status IN ('dijadwalkan', 'sedang_dikerjakan') THEN 1 END) as proses
                 FROM pemeliharaan p 
                 WHERE p.fasilitas_id = :id";

        if ($onlyPublic) {
            $sqlP .= " AND p.tampil_publik = 1";
        }

        $this->db->query($sqlP);
        $this->db->bind(':id', (int)$id, PDO::PARAM_INT);
        $pStats = $this->db->single();

        // Jumlah kerusakan: tiket laporan kerusakan + pemeliharaan akibat kerusakan langsung
        $sqlK = "SELECT 
                    (SELECT COUNT(*) FROM laporan_kerusakan WHERE fasilitas_id = :fid1) +
                    (SELECT COUNT(*) FROM pemeliharaan 
                     WHERE fasilitas_id = :fid2 
                       AND (jenis_tindakan = 'Perbaikan' OR kondisi_sebelum IN ('rusak_ringan', 'rusak_berat', 'tidak_layak'))
                       AND laporan_id IS NULL) as total_kerusakan";
        $this->db->query($sqlK);
        $this->db->bind(':fid1', (int)$id, PDO::PARAM_INT);
        $this->db->bind(':fid2', (int)$id, PDO::PARAM_INT);
        $kStats = $this->db->single();

        return [
            'total_pemeliharaan' => (int)($pStats['total_pemeliharaan'] ?? 0),
            'total_biaya' => (float)($pStats['total_biaya'] ?? 0),
            'jumlah_kerusakan' => (int)($kStats['total_kerusakan'] ?? 0),
            'selesai' => (int)($pStats['selesai'] ?? 0),
            'proses' => (int)($pStats['proses'] ?? 0),
        ];
    }

    /**
     * Mengambil daftar riwayat laporan kerusakan pada fasilitas ini (untuk detail fasilitas)
     */
    public function getRiwayatLaporan($id) {
        $sql = "SELECT l.*, u.nama_lengkap as nama_pelapor 
                FROM laporan_kerusakan l 
                JOIN users u ON l.user_id = u.id 
                WHERE l.fasilitas_id = :id 
                ORDER BY l.created_at DESC";

        $this->db->query($sql);
        $this->db->bind(':id', (int)$id, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    /**
     * Mengambil seluruh ruangan yang berada di gedung tertentu (untuk dropdown berantai)
     */
    public function getRuanganByGedung($gedungId = null) {
        $sql = "SELECT r.id, r.kode_ruangan, r.nama_ruangan, r.lantai, g.nama_gedung 
                FROM ruangan r 
                JOIN gedung g ON r.gedung_id = g.id";
        if (!empty($gedungId)) {
            $sql .= " WHERE r.gedung_id = :gedung_id";
        }
        $sql .= " ORDER BY g.nama_gedung ASC, r.lantai ASC, r.nama_ruangan ASC";

        $this->db->query($sql);
        if (!empty($gedungId)) {
            $this->db->bind(':gedung_id', (int)$gedungId, PDO::PARAM_INT);
        }
        return $this->db->resultSet();
    }
}
