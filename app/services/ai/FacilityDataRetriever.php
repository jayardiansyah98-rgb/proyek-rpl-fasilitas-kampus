<?php
/**
 * FacilityDataRetriever
 * Lapisan Whitelist Query Terkendali untuk AI Assistant.
 * Keamanan Ketat:
 * - TIDAK menjalankan arbitrary SQL.
 * - TIDAK menerima query SQL mentah dari AI.
 * - HANYA menjalankan fungsi parameterized query yang telah diizinkan dan diverifikasi.
 */

require_once __DIR__ . '/../../core/Database.php';

class FacilityDataRetriever {
    protected $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * 1. Hitung fasilitas berdasarkan kondisi tertentu (baik, rusak_ringan, rusak_berat, tidak_layak)
     */
    public function countFacilitiesByCondition(string $kondisi): array {
        // Sanitasi kondisi yang diizinkan
        $validKondisi = ['baik', 'rusak_ringan', 'rusak_berat', 'tidak_layak', 'hilang'];
        $kondisi = strtolower(trim($kondisi));
        if (!in_array($kondisi, $validKondisi, true)) {
            $kondisi = 'rusak_berat';
        }

        $summary = $this->db->query(
            "SELECT COUNT(*) as total_item, 
                    COALESCE(SUM(jumlah), 0) as total_unit 
             FROM fasilitas 
             WHERE kondisi = :kondisi"
        )->bind(':kondisi', $kondisi)->single();

        $items = $this->db->query(
            "SELECT f.id, f.kode_inventaris, f.nama, f.jumlah, f.kondisi,
                    r.nama_ruangan, g.nama_gedung, k.nama_kategori
             FROM fasilitas f
             JOIN ruangan r ON f.ruangan_id = r.id
             JOIN gedung g ON r.gedung_id = g.id
             JOIN kategori_fasilitas k ON f.kategori_id = k.id
             WHERE f.kondisi = :kondisi
             ORDER BY f.nama ASC LIMIT 10"
        )->bind(':kondisi', $kondisi)->resultSet();

        return [
            'kondisi' => $kondisi,
            'label_kondisi' => ucfirst(str_replace('_', ' ', $kondisi)),
            'total_item' => (int)($summary['total_item'] ?? 0),
            'total_unit' => (int)($summary['total_unit'] ?? 0),
            'daftar_fasilitas' => $items
        ];
    }

    /**
     * 2. Ambil fasilitas yang paling banyak mengalami kerusakan & tiket aduan
     */
    public function getMostDamagedFacilities(int $limit = 5): array {
        $limit = max(1, min(20, $limit));

        $rows = $this->db->query(
            "SELECT f.id, f.kode_inventaris, f.nama, f.kondisi, f.jumlah,
                    r.nama_ruangan, g.nama_gedung,
                    COUNT(l.id) as total_laporan,
                    (SELECT COUNT(*) FROM pemeliharaan WHERE fasilitas_id = f.id AND jenis_tindakan = 'Perbaikan') as total_perbaikan,
                    (COUNT(l.id) + (SELECT COUNT(*) FROM pemeliharaan WHERE fasilitas_id = f.id AND jenis_tindakan = 'Perbaikan')) as total_kasus
             FROM fasilitas f
             JOIN ruangan r ON f.ruangan_id = r.id
             JOIN gedung g ON r.gedung_id = g.id
             LEFT JOIN laporan_kerusakan l ON f.id = l.fasilitas_id
             GROUP BY f.id, f.kode_inventaris, f.nama, f.kondisi, f.jumlah, r.nama_ruangan, g.nama_gedung
             ORDER BY total_kasus DESC, f.nama ASC
             LIMIT " . (int)$limit
        )->resultSet();

        return [
            'total_data' => count($rows),
            'fasilitas' => $rows
        ];
    }

    /**
     * 3. Ambil jumlah fasilitas dan unit di gedung tertentu
     */
    public function getFacilitiesByBuilding(string $buildingQuery): array {
        $buildingQuery = trim($buildingQuery);

        // Cari gedung yang paling cocok (berdasarkan kode gedung atau nama)
        $gedung = $this->db->query(
            "SELECT * FROM gedung 
             WHERE nama_gedung LIKE :q1 OR kode_gedung LIKE :q2
             ORDER BY CASE 
                WHEN kode_gedung = :exact THEN 1 
                WHEN nama_gedung LIKE :starts THEN 2 
                ELSE 3 
             END ASC LIMIT 1"
        )->bind(':q1', '%' . $buildingQuery . '%')
         ->bind(':q2', '%' . $buildingQuery . '%')
         ->bind(':exact', $buildingQuery)
         ->bind(':starts', $buildingQuery . '%')
         ->single();

        if (!$gedung) {
            // Jika tidak cocok spesifik, kembalikan rekapitulasi seluruh gedung
            $allGedung = $this->db->query(
                "SELECT g.id, g.kode_gedung, g.nama_gedung,
                        COUNT(DISTINCT r.id) as total_ruangan,
                        COUNT(DISTINCT f.id) as total_item,
                        COALESCE(SUM(f.jumlah), 0) as total_unit
                 FROM gedung g
                 LEFT JOIN ruangan r ON g.id = r.gedung_id
                 LEFT JOIN fasilitas f ON r.id = f.ruangan_id
                 GROUP BY g.id, g.kode_gedung, g.nama_gedung
                 ORDER BY g.kode_gedung ASC"
            )->resultSet();

            return [
                'found' => false,
                'query' => $buildingQuery,
                'daftar_semua_gedung' => $allGedung
            ];
        }

        // Ambil metrik gedung yang ditemukan
        $stats = $this->db->query(
            "SELECT COUNT(DISTINCT r.id) as total_ruangan,
                    COUNT(DISTINCT f.id) as total_item,
                    COALESCE(SUM(f.jumlah), 0) as total_unit,
                    COUNT(DISTINCT CASE WHEN f.kondisi = 'baik' THEN f.id END) as item_baik,
                    COUNT(DISTINCT CASE WHEN f.kondisi = 'rusak_ringan' THEN f.id END) as item_rusak_ringan,
                    COUNT(DISTINCT CASE WHEN f.kondisi = 'rusak_berat' THEN f.id END) as item_rusak_berat,
                    COUNT(DISTINCT CASE WHEN f.kondisi = 'tidak_layak' THEN f.id END) as item_tidak_layak
             FROM gedung g
             LEFT JOIN ruangan r ON g.id = r.gedung_id
             LEFT JOIN fasilitas f ON r.id = f.ruangan_id
             WHERE g.id = :gid"
        )->bind(':gid', $gedung['id'])->single();

        // Ambil rincian per ruangan di gedung tersebut
        $ruanganList = $this->db->query(
            "SELECT r.id, r.kode_ruangan, r.nama_ruangan, r.lantai,
                    COUNT(f.id) as total_item,
                    COALESCE(SUM(f.jumlah), 0) as total_unit
             FROM ruangan r
             LEFT JOIN fasilitas f ON r.id = f.ruangan_id
             WHERE r.gedung_id = :gid
             GROUP BY r.id, r.kode_ruangan, r.nama_ruangan, r.lantai
             ORDER BY r.lantai ASC, r.nama_ruangan ASC"
        )->bind(':gid', $gedung['id'])->resultSet();

        return [
            'found' => true,
            'gedung' => $gedung,
            'stats' => $stats,
            'ruangan' => $ruanganList
        ];
    }

    /**
     * 4. Cari ruangan yang memiliki fasilitas paling lengkap / terbanyak
     */
    public function getRoomWithMostFacilities(int $limit = 5): array {
        $limit = max(1, min(20, $limit));

        $rows = $this->db->query(
            "SELECT r.id, r.kode_ruangan, r.nama_ruangan, r.lantai, r.jenis_ruangan,
                    g.nama_gedung, g.kode_gedung,
                    COUNT(f.id) as total_jenis_fasilitas,
                    COALESCE(SUM(f.jumlah), 0) as total_unit_fasilitas,
                    COUNT(CASE WHEN f.kondisi = 'baik' THEN 1 END) as fasilitas_baik,
                    COUNT(CASE WHEN f.kondisi != 'baik' THEN 1 END) as fasilitas_bermasalah
             FROM ruangan r
             JOIN gedung g ON r.gedung_id = g.id
             LEFT JOIN fasilitas f ON r.id = f.ruangan_id
             GROUP BY r.id, r.kode_ruangan, r.nama_ruangan, r.lantai, r.jenis_ruangan, g.nama_gedung, g.kode_gedung
             ORDER BY total_unit_fasilitas DESC, total_jenis_fasilitas DESC
             LIMIT " . (int)$limit
        )->resultSet();

        return [
            'total_ruangan' => count($rows),
            'ruangan' => $rows
        ];
    }

    /**
     * 5. Fasilitas yang perlu diprioritaskan untuk diperbaiki
     */
    public function getPriorityRepairFacilities(int $limit = 6): array {
        $limit = max(1, min(20, $limit));

        $rows = $this->db->query(
            "SELECT f.id, f.kode_inventaris, f.nama, f.kondisi, f.jumlah,
                    r.nama_ruangan, g.nama_gedung,
                    (SELECT COUNT(*) FROM laporan_kerusakan WHERE fasilitas_id = f.id AND status NOT IN ('selesai', 'ditolak')) as tiket_aktif,
                    (SELECT prioritas FROM laporan_kerusakan WHERE fasilitas_id = f.id AND status NOT IN ('selesai', 'ditolak') ORDER BY FIELD(prioritas, 'darurat', 'tinggi', 'sedang', 'rendah') ASC LIMIT 1) as prioritas_tertinggi
             FROM fasilitas f
             JOIN ruangan r ON f.ruangan_id = r.id
             JOIN gedung g ON r.gedung_id = g.id
             WHERE f.kondisi IN ('rusak_berat', 'tidak_layak', 'rusak_ringan', 'hilang')
                OR f.id IN (SELECT fasilitas_id FROM laporan_kerusakan WHERE status NOT IN ('selesai', 'ditolak'))
             ORDER BY 
                CASE 
                   WHEN f.kondisi = 'rusak_berat' THEN 1
                   WHEN f.kondisi = 'tidak_layak' THEN 2
                   WHEN f.kondisi = 'rusak_ringan' THEN 3
                   ELSE 4
                END ASC,
                tiket_aktif DESC,
                f.nama ASC
             LIMIT " . (int)$limit
        )->resultSet();

        return [
            'total_prioritas' => count($rows),
            'fasilitas' => $rows
        ];
    }

    /**
     * 6. Kondisi fasilitas di ruangan spesifik (misal: "Laboratorium Komputer 1", "Lab AI", dsb.)
     */
    public function getFacilitiesInRoom(string $roomQuery): array {
        $roomQuery = trim($roomQuery);

        // Cari ruangan yang cocok
        $ruangan = $this->db->query(
            "SELECT r.*, g.nama_gedung, g.kode_gedung 
             FROM ruangan r
             JOIN gedung g ON r.gedung_id = g.id
             WHERE r.nama_ruangan LIKE :q1 
                OR r.kode_ruangan LIKE :q2
             ORDER BY CASE 
                WHEN r.kode_ruangan = :exact THEN 1
                WHEN r.nama_ruangan LIKE :starts THEN 2
                ELSE 3
             END ASC LIMIT 1"
        )->bind(':q1', '%' . $roomQuery . '%')
         ->bind(':q2', '%' . $roomQuery . '%')
         ->bind(':exact', $roomQuery)
         ->bind(':starts', $roomQuery . '%')
         ->single();

        if (!$ruangan) {
            // Ambil daftar ruangan yang tersedia sebagai saran
            $daftarRuangan = $this->db->query(
                "SELECT r.id, r.kode_ruangan, r.nama_ruangan, g.nama_gedung 
                 FROM ruangan r 
                 JOIN gedung g ON r.gedung_id = g.id 
                 ORDER BY r.nama_ruangan ASC LIMIT 10"
            )->resultSet();

            return [
                'found' => false,
                'query' => $roomQuery,
                'daftar_ruangan_tersedia' => $daftarRuangan
            ];
        }

        // Ambil daftar fasilitas di ruangan tersebut
        $items = $this->db->query(
            "SELECT f.id, f.kode_inventaris, f.nama, f.jumlah, f.kondisi, f.tahun_pengadaan,
                    k.nama_kategori,
                    (SELECT COUNT(*) FROM laporan_kerusakan WHERE fasilitas_id = f.id AND status NOT IN ('selesai', 'ditolak')) as active_laporan
             FROM fasilitas f
             JOIN kategori_fasilitas k ON f.kategori_id = k.id
             WHERE f.ruangan_id = :rid
             ORDER BY CASE f.kondisi WHEN 'rusak_berat' THEN 1 WHEN 'rusak_ringan' THEN 2 ELSE 3 END ASC, f.nama ASC"
        )->bind(':rid', $ruangan['id'])->resultSet();

        $stats = [
            'total_item' => count($items),
            'total_unit' => array_sum(array_column($items, 'jumlah')),
            'baik' => count(array_filter($items, fn($i) => $i['kondisi'] === 'baik')),
            'rusak_ringan' => count(array_filter($items, fn($i) => $i['kondisi'] === 'rusak_ringan')),
            'rusak_berat' => count(array_filter($items, fn($i) => $i['kondisi'] === 'rusak_berat')),
            'tidak_layak' => count(array_filter($items, fn($i) => $i['kondisi'] === 'tidak_layak'))
        ];

        return [
            'found' => true,
            'ruangan' => $ruangan,
            'stats' => $stats,
            'fasilitas' => $items
        ];
    }

    /**
     * 7. Statistik Ringkasan Seluruh Kampus
     */
    public function getTotalFacilityStats(): array {
        return [
            'total_gedung' => (int)($this->db->query("SELECT COUNT(*) as c FROM gedung WHERE status = 'aktif'")->single()['c'] ?? 0),
            'total_ruangan' => (int)($this->db->query("SELECT COUNT(*) as c FROM ruangan")->single()['c'] ?? 0),
            'ruangan_tersedia' => (int)($this->db->query("SELECT COUNT(*) as c FROM ruangan WHERE status = 'tersedia'")->single()['c'] ?? 0),
            'ruangan_perbaikan' => (int)($this->db->query("SELECT COUNT(*) as c FROM ruangan WHERE status = 'perbaikan'")->single()['c'] ?? 0),
            'ruangan_digunakan' => (int)($this->db->query("SELECT COUNT(*) as c FROM ruangan WHERE status = 'digunakan'")->single()['c'] ?? 0),
            'total_item_fasilitas' => (int)($this->db->query("SELECT COUNT(*) as c FROM fasilitas")->single()['c'] ?? 0),
            'total_unit_fasilitas' => (int)($this->db->query("SELECT COALESCE(SUM(jumlah), 0) as s FROM fasilitas")->single()['s'] ?? 0),
            'kondisi_baik' => (int)($this->db->query("SELECT COUNT(*) as c FROM fasilitas WHERE kondisi = 'baik'")->single()['c'] ?? 0),
            'rusak_ringan' => (int)($this->db->query("SELECT COUNT(*) as c FROM fasilitas WHERE kondisi = 'rusak_ringan'")->single()['c'] ?? 0),
            'rusak_berat' => (int)($this->db->query("SELECT COUNT(*) as c FROM fasilitas WHERE kondisi = 'rusak_berat'")->single()['c'] ?? 0),
            'tidak_layak' => (int)($this->db->query("SELECT COUNT(*) as c FROM fasilitas WHERE kondisi = 'tidak_layak'")->single()['c'] ?? 0),
            'laporan_menunggu' => (int)($this->db->query("SELECT COUNT(*) as c FROM laporan_kerusakan WHERE status = 'menunggu'")->single()['c'] ?? 0),
            'laporan_diproses' => (int)($this->db->query("SELECT COUNT(*) as c FROM laporan_kerusakan WHERE status IN ('diverifikasi', 'diproses')")->single()['c'] ?? 0),
            'laporan_selesai' => (int)($this->db->query("SELECT COUNT(*) as c FROM laporan_kerusakan WHERE status = 'selesai'")->single()['c'] ?? 0),
            'total_biaya_pemeliharaan' => (float)($this->db->query("SELECT COALESCE(SUM(biaya), 0) as s FROM pemeliharaan")->single()['s'] ?? 0)
        ];
    }

    /**
     * Ambil daftar ruangan yang berstatus tersedia
     */
    public function getAvailableRooms(): array {
        return $this->db->query(
            "SELECT r.id, r.kode_ruangan, r.nama_ruangan, r.lantai, r.jenis_ruangan, r.kapasitas, r.status, g.nama_gedung,
                    COUNT(f.id) as total_item,
                    COALESCE(SUM(f.jumlah), 0) as total_unit
             FROM ruangan r
             JOIN gedung g ON r.gedung_id = g.id
             LEFT JOIN fasilitas f ON r.id = f.ruangan_id
             WHERE r.status = 'tersedia'
             GROUP BY r.id, r.kode_ruangan, r.nama_ruangan, r.lantai, r.jenis_ruangan, r.kapasitas, r.status, g.nama_gedung
             ORDER BY r.nama_ruangan ASC"
        )->resultSet();
    }

    /**
     * Ambil seluruh daftar ruangan
     */
    public function getAllRooms(): array {
        return $this->db->query(
            "SELECT r.id, r.kode_ruangan, r.nama_ruangan, r.lantai, r.jenis_ruangan, r.kapasitas, r.status, g.nama_gedung,
                    COUNT(f.id) as total_item,
                    COALESCE(SUM(f.jumlah), 0) as total_unit
             FROM ruangan r
             JOIN gedung g ON r.gedung_id = g.id
             LEFT JOIN fasilitas f ON r.id = f.ruangan_id
             GROUP BY r.id, r.kode_ruangan, r.nama_ruangan, r.lantai, r.jenis_ruangan, r.kapasitas, r.status, g.nama_gedung
             ORDER BY r.nama_ruangan ASC"
        )->resultSet();
    }


    /**
     * 8. Rekomendasi Pemeliharaan Rutin / Terjadwal
     */
    public function recommendMaintenance(): array {
        // Fasilitas yang rusak ringan atau belum pernah diservis > 6 bulan
        $items = $this->db->query(
            "SELECT f.id, f.kode_inventaris, f.nama, f.kondisi, f.tahun_pengadaan,
                    r.nama_ruangan, g.nama_gedung, k.nama_kategori,
                    (SELECT MAX(COALESCE(tanggal, tanggal_mulai)) FROM pemeliharaan WHERE fasilitas_id = f.id) as pemeliharaan_terakhir,
                    (SELECT COUNT(*) FROM laporan_kerusakan WHERE fasilitas_id = f.id AND status NOT IN ('selesai', 'ditolak')) as active_reports
             FROM fasilitas f
             JOIN ruangan r ON f.ruangan_id = r.id
             JOIN gedung g ON r.gedung_id = g.id
             JOIN kategori_fasilitas k ON f.kategori_id = k.id
             WHERE f.kondisi IN ('rusak_ringan', 'rusak_berat')
                OR f.id IN (SELECT fasilitas_id FROM laporan_kerusakan WHERE status = 'menunggu')
             ORDER BY 
                CASE f.kondisi WHEN 'rusak_berat' THEN 1 WHEN 'rusak_ringan' THEN 2 ELSE 3 END ASC,
                active_reports DESC
             LIMIT 6"
        )->resultSet();

        return [
            'total_rekomendasi' => count($items),
            'items' => $items
        ];
    }

    /**
     * 9. Analisis Mendalam Fasilitas Tertentu
     */
    public function analyzeFacilityDetail(int $facilityId): ?array {
        $facility = $this->db->query(
            "SELECT f.*, k.nama_kategori, r.nama_ruangan, r.kode_ruangan, r.lantai,
                    g.nama_gedung, g.kode_gedung
             FROM fasilitas f
             JOIN kategori_fasilitas k ON f.kategori_id = k.id
             JOIN ruangan r ON f.ruangan_id = r.id
             JOIN gedung g ON r.gedung_id = g.id
             WHERE f.id = :id"
        )->bind(':id', $facilityId)->single();

        if (!$facility) {
            return null;
        }

        $laporan = $this->db->query(
            "SELECT l.*, u.nama_lengkap as pelapor
             FROM laporan_kerusakan l
             JOIN users u ON l.user_id = u.id
             WHERE l.fasilitas_id = :fid
             ORDER BY l.created_at DESC LIMIT 5"
        )->bind(':fid', $facilityId)->resultSet();

        $pemeliharaan = $this->db->query(
            "SELECT p.*, u.nama_lengkap as teknisi
             FROM pemeliharaan p
             JOIN users u ON p.petugas_id = u.id
             WHERE p.fasilitas_id = :fid
             ORDER BY COALESCE(p.tanggal, p.tanggal_mulai) DESC LIMIT 5"
        )->bind(':fid', $facilityId)->resultSet();

        $totalBiaya = (float)($this->db->query(
            "SELECT COALESCE(SUM(biaya), 0) as s FROM pemeliharaan WHERE fasilitas_id = :fid"
        )->bind(':fid', $facilityId)->single()['s'] ?? 0);

        return [
            'fasilitas' => $facility,
            'total_laporan' => count($laporan),
            'riwayat_laporan' => $laporan,
            'total_pemeliharaan' => count($pemeliharaan),
            'riwayat_pemeliharaan' => $pemeliharaan,
            'total_biaya_perbaikan' => $totalBiaya
        ];
    }

    /**
     * Ambil daftar fasilitas yang berada di ruangan yang berstatus tersedia
     */
    public function getAvailableFacilities(): array {
        return $this->db->query(
            "SELECT f.id, f.kode_inventaris, f.nama, f.jumlah, f.kondisi, f.keterangan,
                    r.nama_ruangan, r.kode_ruangan, r.lantai, g.nama_gedung
             FROM fasilitas f
             JOIN ruangan r ON f.ruangan_id = r.id
             JOIN gedung g ON r.gedung_id = g.id
             WHERE r.status = 'tersedia'
             ORDER BY g.nama_gedung ASC, r.nama_ruangan ASC, f.nama ASC"
        )->resultSet();
    }
}
