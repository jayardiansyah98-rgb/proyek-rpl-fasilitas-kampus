<?php
/**
 * ReportModel
 * Model untuk modul Reporting & Rekapitulasi Eksekutif Sarana dan Prasarana
 * Kampus STMIK IKMI Cirebon.
 */

class ReportModel extends Model {

    /**
     * 1. Laporan Seluruh Fasilitas
     */
    public function getLaporanFasilitas(array $filters = []) {
        $sql = "SELECT f.*, 
                       f.nama as nama_fasilitas,
                       k.nama_kategori,
                       r.nama_ruangan, r.kode_ruangan, r.lantai,
                       g.nama_gedung, g.kode_gedung,
                       (SELECT COUNT(*) FROM laporan_kerusakan WHERE fasilitas_id = f.id) as total_laporan,
                       (SELECT COUNT(*) FROM pemeliharaan WHERE fasilitas_id = f.id) as total_pemeliharaan,
                       (SELECT COALESCE(SUM(biaya), 0) FROM pemeliharaan WHERE fasilitas_id = f.id) as total_biaya_pemeliharaan
                FROM fasilitas f
                JOIN kategori_fasilitas k ON f.kategori_id = k.id
                JOIN ruangan r ON f.ruangan_id = r.id
                JOIN gedung g ON r.gedung_id = g.id";

        $conditions = [];
        $params = [];

        if (!empty($filters['gedung_id'])) {
            $conditions[] = "r.gedung_id = :gedung_id";
            $params[':gedung_id'] = (int)$filters['gedung_id'];
        }

        if (!empty($filters['ruangan_id'])) {
            $conditions[] = "f.ruangan_id = :ruangan_id";
            $params[':ruangan_id'] = (int)$filters['ruangan_id'];
        }

        if (!empty($filters['kondisi'])) {
            $conditions[] = "f.kondisi = :kondisi";
            $params[':kondisi'] = trim($filters['kondisi']);
        }

        if (!empty($filters['search'])) {
            $conditions[] = "(f.nama LIKE :s1 OR f.kode_inventaris LIKE :s2 OR r.nama_ruangan LIKE :s3 OR g.nama_gedung LIKE :s4)";
            $st = '%' . trim($filters['search']) . '%';
            $params[':s1'] = $st;
            $params[':s2'] = $st;
            $params[':s3'] = $st;
            $params[':s4'] = $st;
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY g.nama_gedung ASC, r.nama_ruangan ASC, f.nama ASC";

        $this->db->query($sql);
        foreach ($params as $k => $v) {
            $this->db->bind($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }

        return $this->db->resultSet();
    }

    /**
     * 2. Laporan Per Gedung
     */
    public function getLaporanPerGedung(array $filters = []) {
        $sql = "SELECT g.id, g.kode_gedung, g.nama_gedung, g.lokasi, g.jumlah_lantai, g.status,
                       COUNT(DISTINCT r.id) as total_ruangan,
                       COUNT(DISTINCT f.id) as total_jenis_fasilitas,
                       COALESCE(SUM(f.jumlah), 0) as total_unit_fasilitas,
                       COUNT(DISTINCT CASE WHEN f.kondisi = 'baik' THEN f.id END) as fasilitas_baik,
                       COUNT(DISTINCT CASE WHEN f.kondisi = 'rusak_ringan' THEN f.id END) as fasilitas_rusak_ringan,
                       COUNT(DISTINCT CASE WHEN f.kondisi = 'rusak_berat' THEN f.id END) as fasilitas_rusak_berat,
                       COUNT(DISTINCT CASE WHEN f.kondisi = 'tidak_layak' THEN f.id END) as fasilitas_tidak_layak,
                       COUNT(DISTINCT l.id) as total_tiket_laporan
                FROM gedung g
                LEFT JOIN ruangan r ON g.id = r.gedung_id
                LEFT JOIN fasilitas f ON r.id = f.ruangan_id
                LEFT JOIN laporan_kerusakan l ON f.id = l.fasilitas_id";

        $conditions = [];
        $params = [];

        if (!empty($filters['gedung_id'])) {
            $conditions[] = "g.id = :gedung_id";
            $params[':gedung_id'] = (int)$filters['gedung_id'];
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " GROUP BY g.id, g.kode_gedung, g.nama_gedung, g.lokasi, g.jumlah_lantai, g.status
                  ORDER BY g.kode_gedung ASC";

        $this->db->query($sql);
        foreach ($params as $k => $v) {
            $this->db->bind($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }

        return $this->db->resultSet();
    }

    /**
     * 3. Laporan Per Ruangan
     */
    public function getLaporanPerRuangan(array $filters = []) {
        $sql = "SELECT r.id, r.kode_ruangan, r.nama_ruangan, r.lantai, r.jenis_ruangan, r.kapasitas, r.status as status_ruangan,
                       g.nama_gedung, g.kode_gedung,
                       COUNT(f.id) as total_jenis_fasilitas,
                       COALESCE(SUM(f.jumlah), 0) as total_unit_fasilitas,
                       COUNT(CASE WHEN f.kondisi = 'baik' THEN 1 END) as item_baik,
                       COUNT(CASE WHEN f.kondisi IN ('rusak_ringan', 'rusak_berat', 'tidak_layak') THEN 1 END) as item_rusak,
                       (SELECT COUNT(*) FROM laporan_kerusakan WHERE ruangan_id = r.id) as total_laporan
                FROM ruangan r
                JOIN gedung g ON r.gedung_id = g.id
                LEFT JOIN fasilitas f ON r.id = f.ruangan_id";

        $conditions = [];
        $params = [];

        if (!empty($filters['gedung_id'])) {
            $conditions[] = "r.gedung_id = :gedung_id";
            $params[':gedung_id'] = (int)$filters['gedung_id'];
        }

        if (!empty($filters['ruangan_id'])) {
            $conditions[] = "r.id = :ruangan_id";
            $params[':ruangan_id'] = (int)$filters['ruangan_id'];
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " GROUP BY r.id, r.kode_ruangan, r.nama_ruangan, r.lantai, r.jenis_ruangan, r.kapasitas, r.status, g.nama_gedung, g.kode_gedung
                  ORDER BY g.nama_gedung ASC, r.lantai ASC, r.nama_ruangan ASC";

        $this->db->query($sql);
        foreach ($params as $k => $v) {
            $this->db->bind($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }

        return $this->db->resultSet();
    }

    /**
     * 4. Laporan Berdasarkan Kondisi Fasilitas
     */
    public function getLaporanKondisi(array $filters = []) {
        $sql = "SELECT f.id, f.kode_inventaris, f.nama as nama_fasilitas, f.jumlah, f.kondisi, f.tahun_pengadaan,
                       k.nama_kategori,
                       r.nama_ruangan, r.kode_ruangan, r.lantai,
                       g.nama_gedung, g.kode_gedung,
                       (SELECT COUNT(*) FROM laporan_kerusakan WHERE fasilitas_id = f.id AND status NOT IN ('selesai', 'ditolak')) as active_laporan,
                       (SELECT COUNT(*) FROM pemeliharaan WHERE fasilitas_id = f.id) as total_pemeliharaan
                FROM fasilitas f
                JOIN kategori_fasilitas k ON f.kategori_id = k.id
                JOIN ruangan r ON f.ruangan_id = r.id
                JOIN gedung g ON r.gedung_id = g.id";

        $conditions = [];
        $params = [];

        if (!empty($filters['kondisi'])) {
            $conditions[] = "f.kondisi = :kondisi";
            $params[':kondisi'] = trim($filters['kondisi']);
        }

        if (!empty($filters['gedung_id'])) {
            $conditions[] = "r.gedung_id = :gedung_id";
            $params[':gedung_id'] = (int)$filters['gedung_id'];
        }

        if (!empty($filters['ruangan_id'])) {
            $conditions[] = "f.ruangan_id = :ruangan_id";
            $params[':ruangan_id'] = (int)$filters['ruangan_id'];
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        // Urutkan dari yang rusak berat ke baik
        $sql .= " ORDER BY 
                  CASE f.kondisi 
                    WHEN 'rusak_berat' THEN 1 
                    WHEN 'tidak_layak' THEN 2 
                    WHEN 'rusak_ringan' THEN 3 
                    WHEN 'hilang' THEN 4 
                    ELSE 5 
                  END ASC, g.nama_gedung ASC, r.nama_ruangan ASC";

        $this->db->query($sql);
        foreach ($params as $k => $v) {
            $this->db->bind($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }

        return $this->db->resultSet();
    }

    /**
     * 5. Laporan Kerusakan
     */
    public function getLaporanKerusakan(array $filters = []) {
        $sql = "SELECT l.*, 
                       f.nama as nama_fasilitas, f.kode_inventaris, f.kondisi as kondisi_fasilitas_now,
                       r.nama_ruangan, r.kode_ruangan,
                       g.nama_gedung, g.kode_gedung,
                       u.nama_lengkap as nama_pelapor, u.nomor_induk as nim_nip_pelapor, u.jenis_pengguna
                FROM laporan_kerusakan l
                JOIN fasilitas f ON l.fasilitas_id = f.id
                JOIN ruangan r ON l.ruangan_id = r.id
                JOIN gedung g ON r.gedung_id = g.id
                JOIN users u ON l.user_id = u.id";

        $conditions = [];
        $params = [];

        if (!empty($filters['gedung_id'])) {
            $conditions[] = "r.gedung_id = :gedung_id";
            $params[':gedung_id'] = (int)$filters['gedung_id'];
        }

        if (!empty($filters['ruangan_id'])) {
            $conditions[] = "l.ruangan_id = :ruangan_id";
            $params[':ruangan_id'] = (int)$filters['ruangan_id'];
        }

        if (!empty($filters['status'])) {
            $conditions[] = "l.status = :status";
            $params[':status'] = trim($filters['status']);
        }

        if (!empty($filters['prioritas'])) {
            $conditions[] = "l.prioritas = :prioritas";
            $params[':prioritas'] = trim($filters['prioritas']);
        }

        if (!empty($filters['tanggal_dari'])) {
            $conditions[] = "DATE(l.created_at) >= :tgl_dari";
            $params[':tgl_dari'] = trim($filters['tanggal_dari']);
        }

        if (!empty($filters['tanggal_sampai'])) {
            $conditions[] = "DATE(l.created_at) <= :tgl_sampai";
            $params[':tgl_sampai'] = trim($filters['tanggal_sampai']);
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY l.created_at DESC";

        $this->db->query($sql);
        foreach ($params as $k => $v) {
            $this->db->bind($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }

        return $this->db->resultSet();
    }

    /**
     * 6. Laporan Pemeliharaan
     */
    public function getLaporanPemeliharaan(array $filters = []) {
        $sql = "SELECT p.*, 
                       f.nama as nama_fasilitas, f.kode_inventaris,
                       r.nama_ruangan, r.kode_ruangan,
                       g.nama_gedung, g.kode_gedung,
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

        if (!empty($filters['gedung_id'])) {
            $conditions[] = "r.gedung_id = :gedung_id";
            $params[':gedung_id'] = (int)$filters['gedung_id'];
        }

        if (!empty($filters['ruangan_id'])) {
            $conditions[] = "f.ruangan_id = :ruangan_id";
            $params[':ruangan_id'] = (int)$filters['ruangan_id'];
        }

        if (!empty($filters['kondisi'])) {
            $conditions[] = "(p.kondisi_sesudah = :k_sesudah OR p.kondisi_sebelum = :k_sebelum)";
            $params[':k_sesudah'] = trim($filters['kondisi']);
            $params[':k_sebelum'] = trim($filters['kondisi']);
        }

        if (!empty($filters['status'])) {
            $conditions[] = "p.status = :status";
            $params[':status'] = trim($filters['status']);
        }

        if (!empty($filters['jenis_tindakan'])) {
            $conditions[] = "p.jenis_tindakan = :jenis_tindakan";
            $params[':jenis_tindakan'] = trim($filters['jenis_tindakan']);
        }

        if (!empty($filters['tanggal_dari'])) {
            $conditions[] = "COALESCE(p.tanggal, p.tanggal_mulai) >= :tgl_dari";
            $params[':tgl_dari'] = trim($filters['tanggal_dari']);
        }

        if (!empty($filters['tanggal_sampai'])) {
            $conditions[] = "COALESCE(p.tanggal, p.tanggal_mulai) <= :tgl_sampai";
            $params[':tgl_sampai'] = trim($filters['tanggal_sampai']);
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY COALESCE(p.tanggal, p.tanggal_mulai) DESC, p.id DESC";

        $this->db->query($sql);
        foreach ($params as $k => $v) {
            $this->db->bind($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }

        return $this->db->resultSet();
    }
}
