<?php
/**
 * PemeliharaanModel
 * Menangani transaksi tindakan pemeliharaan, perawatan rutin, dan perbaikan fasilitas
 * Kampus STMIK IKMI Cirebon. Menggunakan PDO Prepared Statements.
 */

class PemeliharaanModel extends Model {

    /**
     * Generate Kode Pemeliharaan Unik Terurut Otomatis (Format: MT-YYYYMM-XXX)
     */
    public function generateKodePemeliharaan() {
        $prefix = 'MT-' . date('Ym') . '-';
        $sql = "SELECT kode_pemeliharaan FROM pemeliharaan 
                WHERE kode_pemeliharaan LIKE :prefix 
                ORDER BY id DESC LIMIT 1";
        
        $this->db->query($sql);
        $this->db->bind(':prefix', $prefix . '%');
        $lastRow = $this->db->single();

        if ($lastRow && !empty($lastRow['kode_pemeliharaan'])) {
            $lastNumber = (int)substr($lastRow['kode_pemeliharaan'], -3);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Mengambil daftar pemeliharaan dengan filter lengkap, paginasi, dan proteksi publik
     */
    public function getAll(
        $search = null, 
        $fasilitasId = null, 
        $ruanganId = null, 
        $jenisTindakan = null, 
        $status = null, 
        $tanggalDari = null, 
        $tanggalSampai = null, 
        $onlyPublic = false, 
        $page = 1, 
        $perPage = 10
    ) {
        $page = max(1, (int)$page);
        $perPage = max(1, (int)$perPage);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT p.*, 
                       f.nama as nama_fasilitas, f.kode_inventaris, f.kondisi as kondisi_fasilitas_sekarang, f.foto as foto_fasilitas,
                       r.id as ruangan_id, r.nama_ruangan, r.kode_ruangan, r.lantai,
                       g.id as gedung_id, g.nama_gedung, g.kode_gedung,
                       u.nama_lengkap as nama_petugas, u.nomor_induk as nip_petugas,
                       l.kode_laporan
                FROM pemeliharaan p
                JOIN fasilitas f ON p.fasilitas_id = f.id
                JOIN ruangan r ON f.ruangan_id = r.id
                JOIN gedung g ON r.gedung_id = g.id
                JOIN users u ON p.petugas_id = u.id
                LEFT JOIN laporan_kerusakan l ON p.laporan_id = l.id";

        $conditions = [];
        $params = [];

        if ($onlyPublic) {
            $conditions[] = "p.tampil_publik = 1";
        }

        if (!empty($fasilitasId)) {
            $conditions[] = "p.fasilitas_id = :fasilitas_id";
            $params[':fasilitas_id'] = (int)$fasilitasId;
        }

        if (!empty($ruanganId)) {
            $conditions[] = "f.ruangan_id = :ruangan_id";
            $params[':ruangan_id'] = (int)$ruanganId;
        }

        if (!empty($jenisTindakan)) {
            $conditions[] = "p.jenis_tindakan = :jenis_tindakan";
            $params[':jenis_tindakan'] = trim($jenisTindakan);
        }

        if (!empty($status)) {
            $conditions[] = "p.status = :status";
            $params[':status'] = trim($status);
        }

        if (!empty($tanggalDari)) {
            $conditions[] = "COALESCE(p.tanggal, p.tanggal_mulai) >= :tgl_dari";
            $params[':tgl_dari'] = trim($tanggalDari);
        }

        if (!empty($tanggalSampai)) {
            $conditions[] = "COALESCE(p.tanggal, p.tanggal_mulai) <= :tgl_sampai";
            $params[':tgl_sampai'] = trim($tanggalSampai);
        }

        if (!empty($search)) {
            $conditions[] = "(p.kode_pemeliharaan LIKE :s1 
                              OR f.nama LIKE :s2 
                              OR f.kode_inventaris LIKE :s3 
                              OR r.nama_ruangan LIKE :s4 
                              OR g.nama_gedung LIKE :s5 
                              OR p.deskripsi LIKE :s6 
                              OR p.keterangan LIKE :s7 
                              OR u.nama_lengkap LIKE :s8)";
            $searchTerm = '%' . trim($search) . '%';
            $params[':s1'] = $searchTerm;
            $params[':s2'] = $searchTerm;
            $params[':s3'] = $searchTerm;
            $params[':s4'] = $searchTerm;
            $params[':s5'] = $searchTerm;
            $params[':s6'] = $searchTerm;
            $params[':s7'] = $searchTerm;
            $params[':s8'] = $searchTerm;
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY COALESCE(p.tanggal, p.tanggal_mulai) DESC, p.id DESC LIMIT :limit OFFSET :offset";

        $this->db->query($sql);
        foreach ($params as $key => $val) {
            $this->db->bind($key, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $this->db->bind(':limit', $perPage, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);

        return $this->db->resultSet();
    }

    /**
     * Menghitung total data pemeliharaan untuk filter aktif
     */
    public function countAll(
        $search = null, 
        $fasilitasId = null, 
        $ruanganId = null, 
        $jenisTindakan = null, 
        $status = null, 
        $tanggalDari = null, 
        $tanggalSampai = null, 
        $onlyPublic = false
    ) {
        $sql = "SELECT COUNT(*) as total 
                FROM pemeliharaan p
                JOIN fasilitas f ON p.fasilitas_id = f.id
                JOIN ruangan r ON f.ruangan_id = r.id
                JOIN gedung g ON r.gedung_id = g.id
                JOIN users u ON p.petugas_id = u.id";

        $conditions = [];
        $params = [];

        if ($onlyPublic) {
            $conditions[] = "p.tampil_publik = 1";
        }

        if (!empty($fasilitasId)) {
            $conditions[] = "p.fasilitas_id = :fasilitas_id";
            $params[':fasilitas_id'] = (int)$fasilitasId;
        }

        if (!empty($ruanganId)) {
            $conditions[] = "f.ruangan_id = :ruangan_id";
            $params[':ruangan_id'] = (int)$ruanganId;
        }

        if (!empty($jenisTindakan)) {
            $conditions[] = "p.jenis_tindakan = :jenis_tindakan";
            $params[':jenis_tindakan'] = trim($jenisTindakan);
        }

        if (!empty($status)) {
            $conditions[] = "p.status = :status";
            $params[':status'] = trim($status);
        }

        if (!empty($tanggalDari)) {
            $conditions[] = "COALESCE(p.tanggal, p.tanggal_mulai) >= :tgl_dari";
            $params[':tgl_dari'] = trim($tanggalDari);
        }

        if (!empty($tanggalSampai)) {
            $conditions[] = "COALESCE(p.tanggal, p.tanggal_mulai) <= :tgl_sampai";
            $params[':tgl_sampai'] = trim($tanggalSampai);
        }

        if (!empty($search)) {
            $conditions[] = "(p.kode_pemeliharaan LIKE :s1 
                              OR f.nama LIKE :s2 
                              OR f.kode_inventaris LIKE :s3 
                              OR r.nama_ruangan LIKE :s4 
                              OR g.nama_gedung LIKE :s5 
                              OR p.deskripsi LIKE :s6 
                              OR p.keterangan LIKE :s7 
                              OR u.nama_lengkap LIKE :s8)";
            $searchTerm = '%' . trim($search) . '%';
            $params[':s1'] = $searchTerm;
            $params[':s2'] = $searchTerm;
            $params[':s3'] = $searchTerm;
            $params[':s4'] = $searchTerm;
            $params[':s5'] = $searchTerm;
            $params[':s6'] = $searchTerm;
            $params[':s7'] = $searchTerm;
            $params[':s8'] = $searchTerm;
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
     * Mengambil 1 data pemeliharaan detail berdasarkan ID
     */
    public function findById($id, $onlyPublic = false) {
        $sql = "SELECT p.*, 
                       f.nama as nama_fasilitas, f.kode_inventaris, f.kondisi as kondisi_fasilitas_sekarang, f.foto as foto_fasilitas,
                       f.kategori_id, k.nama_kategori,
                       r.id as ruangan_id, r.nama_ruangan, r.kode_ruangan, r.lantai,
                       g.id as gedung_id, g.nama_gedung, g.kode_gedung, g.lokasi as lokasi_gedung,
                       u.nama_lengkap as nama_petugas, u.nomor_induk as nip_petugas, u.email as email_petugas,
                       l.kode_laporan, l.deskripsi as deskripsi_laporan, l.prioritas as prioritas_laporan, l.status as status_laporan
                FROM pemeliharaan p
                JOIN fasilitas f ON p.fasilitas_id = f.id
                JOIN kategori_fasilitas k ON f.kategori_id = k.id
                JOIN ruangan r ON f.ruangan_id = r.id
                JOIN gedung g ON r.gedung_id = g.id
                JOIN users u ON p.petugas_id = u.id
                LEFT JOIN laporan_kerusakan l ON p.laporan_id = l.id
                WHERE p.id = :id";

        if ($onlyPublic) {
            $sql .= " AND p.tampil_publik = 1";
        }

        $sql .= " LIMIT 1";

        $this->db->query($sql);
        $this->db->bind(':id', (int)$id, PDO::PARAM_INT);
        return $this->db->single();
    }

    /**
     * Mengambil riwayat pemeliharaan spesifik untuk satu fasilitas
     */
    public function getHistoriByFasilitas($fasilitasId, $onlyPublic = false) {
        $sql = "SELECT p.*, u.nama_lengkap as nama_petugas, l.kode_laporan 
                FROM pemeliharaan p
                JOIN users u ON p.petugas_id = u.id
                LEFT JOIN laporan_kerusakan l ON p.laporan_id = l.id
                WHERE p.fasilitas_id = :fid";

        if ($onlyPublic) {
            $sql .= " AND p.tampil_publik = 1";
        }

        $sql .= " ORDER BY COALESCE(p.tanggal, p.tanggal_mulai) DESC, p.id DESC";

        $this->db->query($sql);
        $this->db->bind(':fid', (int)$fasilitasId, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    /**
     * Perhitungan Statistik Sederhana:
     * - Total pemeliharaan
     * - Total biaya
     * - Jumlah kerusakan
     */
    public function getStats($fasilitasId = null, $onlyPublic = false) {
        $conditions = [];
        $params = [];

        if (!empty($fasilitasId)) {
            $conditions[] = "p.fasilitas_id = :fid";
            $params[':fid'] = (int)$fasilitasId;
        }

        if ($onlyPublic) {
            $conditions[] = "p.tampil_publik = 1";
        }

        $whereClause = !empty($conditions) ? " WHERE " . implode(" AND ", $conditions) : "";

        // 1. Total pemeliharaan & total biaya
        $sql = "SELECT 
                    COUNT(*) as total_pemeliharaan,
                    COALESCE(SUM(p.biaya), 0) as total_biaya,
                    COUNT(CASE WHEN p.status = 'selesai' THEN 1 END) as selesai,
                    COUNT(CASE WHEN p.status IN ('dijadwalkan', 'sedang_dikerjakan') THEN 1 END) as proses,
                    COUNT(CASE WHEN p.jenis_tindakan = 'Perbaikan' THEN 1 END) as perbaikan,
                    COUNT(CASE WHEN p.jenis_tindakan = 'Pemeriksaan' THEN 1 END) as pemeriksaan,
                    COUNT(CASE WHEN p.jenis_tindakan = 'Penggantian' THEN 1 END) as penggantian,
                    COUNT(CASE WHEN p.jenis_tindakan = 'Pemeliharaan Rutin' THEN 1 END) as rutin
                FROM pemeliharaan p" . $whereClause;

        $this->db->query($sql);
        foreach ($params as $k => $v) {
            $this->db->bind($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $mainStats = $this->db->single();

        // 2. Jumlah Kerusakan:
        // Dihitung dari jumlah laporan kerusakan pada fasilitas terkait + tindakan perbaikan langsung
        if (!empty($fasilitasId)) {
            $sqlKerusakan = "SELECT 
                                (SELECT COUNT(*) FROM laporan_kerusakan WHERE fasilitas_id = :fid1) +
                                (SELECT COUNT(*) FROM pemeliharaan 
                                 WHERE fasilitas_id = :fid2 
                                   AND (jenis_tindakan = 'Perbaikan' OR kondisi_sebelum IN ('rusak_ringan', 'rusak_berat', 'tidak_layak'))
                                   AND laporan_id IS NULL) as jml_kerusakan";
            $this->db->query($sqlKerusakan);
            $this->db->bind(':fid1', (int)$fasilitasId, PDO::PARAM_INT);
            $this->db->bind(':fid2', (int)$fasilitasId, PDO::PARAM_INT);
            $resKerusakan = $this->db->single();
            $jumlahKerusakan = (int)($resKerusakan['jml_kerusakan'] ?? 0);
        } else {
            $sqlKerusakan = "SELECT 
                                (SELECT COUNT(*) FROM laporan_kerusakan) +
                                (SELECT COUNT(*) FROM pemeliharaan 
                                 WHERE (jenis_tindakan = 'Perbaikan' OR kondisi_sebelum IN ('rusak_ringan', 'rusak_berat', 'tidak_layak'))
                                   AND laporan_id IS NULL) as jml_kerusakan";
            $this->db->query($sqlKerusakan);
            $resKerusakan = $this->db->single();
            $jumlahKerusakan = (int)($resKerusakan['jml_kerusakan'] ?? 0);
        }

        return [
            'total_pemeliharaan' => (int)($mainStats['total_pemeliharaan'] ?? 0),
            'total_biaya' => (float)($mainStats['total_biaya'] ?? 0),
            'jumlah_kerusakan' => $jumlahKerusakan,
            'selesai' => (int)($mainStats['selesai'] ?? 0),
            'proses' => (int)($mainStats['proses'] ?? 0),
            'perbaikan' => (int)($mainStats['perbaikan'] ?? 0),
            'pemeriksaan' => (int)($mainStats['pemeriksaan'] ?? 0),
            'penggantian' => (int)($mainStats['penggantian'] ?? 0),
            'rutin' => (int)($mainStats['rutin'] ?? 0),
        ];
    }

    /**
     * Menyimpan riwayat pemeliharaan baru
     * Mendukung pembaruan kondisi fisik fasilitas secara otomatis
     */
    public function create(array $data) {
        $kodePemeliharaan = $data['kode_pemeliharaan'] ?? $this->generateKodePemeliharaan();
        $tanggal = $data['tanggal'] ?? date('Y-m-d');
        $tanggalMulai = $tanggal;
        $tanggalSelesai = (!empty($data['status']) && $data['status'] === 'selesai') ? $tanggal : ($data['tanggal_selesai'] ?? null);

        $sql = "INSERT INTO pemeliharaan 
                (kode_pemeliharaan, fasilitas_id, laporan_id, petugas_id, tanggal, tanggal_mulai, tanggal_selesai, 
                 jenis_tindakan, kondisi_sebelum, kondisi_sesudah, deskripsi, biaya, keterangan, status, tampil_publik)
                VALUES 
                (:kode, :fasilitas_id, :laporan_id, :petugas_id, :tanggal, :tanggal_mulai, :tanggal_selesai,
                 :jenis_tindakan, :kondisi_sebelum, :kondisi_sesudah, :deskripsi, :biaya, :keterangan, :status, :tampil_publik)";

        $this->db->query($sql);
        $this->db->bind(':kode', $kodePemeliharaan);
        $this->db->bind(':fasilitas_id', (int)$data['fasilitas_id'], PDO::PARAM_INT);
        $this->db->bind(':laporan_id', !empty($data['laporan_id']) ? (int)$data['laporan_id'] : null, !empty($data['laporan_id']) ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $this->db->bind(':petugas_id', (int)$data['petugas_id'], PDO::PARAM_INT);
        $this->db->bind(':tanggal', $tanggal);
        $this->db->bind(':tanggal_mulai', $tanggalMulai);
        $this->db->bind(':tanggal_selesai', $tanggalSelesai);
        $this->db->bind(':jenis_tindakan', $data['jenis_tindakan']);
        $this->db->bind(':kondisi_sebelum', $data['kondisi_sebelum']);
        $this->db->bind(':kondisi_sesudah', $data['kondisi_sesudah']);
        $this->db->bind(':deskripsi', $data['deskripsi']);
        $this->db->bind(':biaya', (float)($data['biaya'] ?? 0));
        $this->db->bind(':keterangan', $data['keterangan'] ?? null);
        $this->db->bind(':status', $data['status'] ?? 'selesai');
        $this->db->bind(':tampil_publik', isset($data['tampil_publik']) ? (int)$data['tampil_publik'] : 1, PDO::PARAM_INT);

        $this->db->execute();
        $insertId = $this->db->lastInsertId();

        // 1. Pembaruan Kondisi Fasilitas Setelah Pemeliharaan jika diminta
        if (!empty($data['update_fasilitas_kondisi'])) {
            $this->updateKondisiFasilitas($data['fasilitas_id'], $data['kondisi_sesudah'], 'Diperbarui otomatis dari catatan pemeliharaan ' . $kodePemeliharaan);
        }

        // 2. Jika terhubung ke tiket laporan kerusakan, update status tiket secara sinkron
        if (!empty($data['laporan_id'])) {
            $laporanStatus = ($data['status'] === 'selesai') ? 'selesai' : 'diproses';
            $catatanTambahan = "Tindakan " . $data['jenis_tindakan'] . " dicatat dengan kode " . $kodePemeliharaan . ". Kondisi akhir: " . $data['kondisi_sesudah'];
            
            $sqlLap = "UPDATE laporan_kerusakan 
                       SET status = :status, catatan_petugas = CONCAT(COALESCE(catatan_petugas, ''), '\n', :catatan)
                       WHERE id = :lid";
            $this->db->query($sqlLap);
            $this->db->bind(':status', $laporanStatus);
            $this->db->bind(':catatan', $catatanTambahan);
            $this->db->bind(':lid', (int)$data['laporan_id'], PDO::PARAM_INT);
            $this->db->execute();
        }

        return $insertId;
    }

    /**
     * Memperbarui data riwayat pemeliharaan
     */
    public function update($id, array $data) {
        $tanggal = $data['tanggal'] ?? date('Y-m-d');
        $tanggalMulai = $tanggal;
        $tanggalSelesai = (!empty($data['status']) && $data['status'] === 'selesai') ? $tanggal : ($data['tanggal_selesai'] ?? null);

        $sql = "UPDATE pemeliharaan SET 
                    fasilitas_id = :fasilitas_id,
                    laporan_id = :laporan_id,
                    petugas_id = :petugas_id,
                    tanggal = :tanggal,
                    tanggal_mulai = :tanggal_mulai,
                    tanggal_selesai = :tanggal_selesai,
                    jenis_tindakan = :jenis_tindakan,
                    kondisi_sebelum = :kondisi_sebelum,
                    kondisi_sesudah = :kondisi_sesudah,
                    deskripsi = :deskripsi,
                    biaya = :biaya,
                    keterangan = :keterangan,
                    status = :status,
                    tampil_publik = :tampil_publik
                WHERE id = :id";

        $this->db->query($sql);
        $this->db->bind(':fasilitas_id', (int)$data['fasilitas_id'], PDO::PARAM_INT);
        $this->db->bind(':laporan_id', !empty($data['laporan_id']) ? (int)$data['laporan_id'] : null, !empty($data['laporan_id']) ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $this->db->bind(':petugas_id', (int)$data['petugas_id'], PDO::PARAM_INT);
        $this->db->bind(':tanggal', $tanggal);
        $this->db->bind(':tanggal_mulai', $tanggalMulai);
        $this->db->bind(':tanggal_selesai', $tanggalSelesai);
        $this->db->bind(':jenis_tindakan', $data['jenis_tindakan']);
        $this->db->bind(':kondisi_sebelum', $data['kondisi_sebelum']);
        $this->db->bind(':kondisi_sesudah', $data['kondisi_sesudah']);
        $this->db->bind(':deskripsi', $data['deskripsi']);
        $this->db->bind(':biaya', (float)($data['biaya'] ?? 0));
        $this->db->bind(':keterangan', $data['keterangan'] ?? null);
        $this->db->bind(':status', $data['status'] ?? 'selesai');
        $this->db->bind(':tampil_publik', isset($data['tampil_publik']) ? (int)$data['tampil_publik'] : 1, PDO::PARAM_INT);
        $this->db->bind(':id', (int)$id, PDO::PARAM_INT);

        $executed = $this->db->execute();

        // Pembaruan Kondisi Fasilitas Setelah Pemeliharaan jika diminta
        if (!empty($data['update_fasilitas_kondisi'])) {
            $this->updateKondisiFasilitas($data['fasilitas_id'], $data['kondisi_sesudah'], 'Diperbarui dari revisi pemeliharaan ID #' . $id);
        }

        return $executed;
    }

    /**
     * Memperbarui status kondisi fasilitas secara langsung
     */
    public function updateKondisiFasilitas($fasilitasId, $kondisi, $catatan = '') {
        $validKondisi = ['baik', 'rusak_ringan', 'rusak_berat', 'tidak_layak', 'hilang'];
        if (!in_array($kondisi, $validKondisi)) {
            return false;
        }

        if (!empty($catatan)) {
            $catatanEntry = "\n[" . date('Y-m-d H:i:s') . "] Kondisi diubah: " . $kondisi . " - " . trim($catatan);
            $sql = "UPDATE fasilitas 
                    SET kondisi = :kondisi, 
                        keterangan = CONCAT(COALESCE(keterangan, ''), :catatan) 
                    WHERE id = :id";
            $this->db->query($sql);
            $this->db->bind(':id', (int)$fasilitasId, PDO::PARAM_INT);
            $this->db->bind(':kondisi', $kondisi);
            $this->db->bind(':catatan', $catatanEntry);
        } else {
            $sql = "UPDATE fasilitas SET kondisi = :kondisi WHERE id = :id";
            $this->db->query($sql);
            $this->db->bind(':id', (int)$fasilitasId, PDO::PARAM_INT);
            $this->db->bind(':kondisi', $kondisi);
        }

        return $this->db->execute();
    }

    /**
     * Menghapus catatan pemeliharaan
     */
    public function delete($id) {
        $sql = "DELETE FROM pemeliharaan WHERE id = :id";
        $this->db->query($sql);
        $this->db->bind(':id', (int)$id, PDO::PARAM_INT);
        return $this->db->execute();
    }
}
