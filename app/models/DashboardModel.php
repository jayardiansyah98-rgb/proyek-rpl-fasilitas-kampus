<?php
/**
 * DashboardModel
 * Model untuk agregasi metrik, statistik visualisasi grafik,
 * dan data ringkasan dashboard civitas akademika STMIK IKMI Cirebon.
 */

class DashboardModel extends Model {

    /**
     * Statistik ringkasan untuk landing page publik (HomeController)
     */
    public function getLandingStats(): array {
        $totalGedung = (int)($this->db->query("SELECT COUNT(*) as total FROM gedung WHERE status = 'aktif'")->single()['total'] ?? 0);
        $totalRuangan = (int)($this->db->query("SELECT COUNT(*) as total FROM ruangan WHERE status = 'tersedia'")->single()['total'] ?? 0);
        $fasilitasRow = $this->db->query("SELECT COUNT(*) as total, COALESCE(SUM(jumlah), 0) as total_unit FROM fasilitas")->single();
        $totalLaporan = (int)($this->db->query("SELECT COUNT(*) as total FROM laporan_kerusakan")->single()['total'] ?? 0);

        return [
            'gedung' => $totalGedung,
            'ruangan' => $totalRuangan,
            'fasilitas_item' => (int)($fasilitasRow['total'] ?? 0),
            'fasilitas_unit' => (int)($fasilitasRow['total_unit'] ?? 0),
            'laporan' => $totalLaporan
        ];
    }

    /**
     * Mengambil 10+ metrik statistik untuk Dashboard Admin & Petugas Sarpras
     */
    public function getAdminStats(?int $petugasId = null): array {
        $stats = [
            'total_gedung' => (int)($this->db->query("SELECT COUNT(*) as c FROM gedung")->single()['c'] ?? 0),
            'total_ruangan' => (int)($this->db->query("SELECT COUNT(*) as c FROM ruangan")->single()['c'] ?? 0),
            'total_fasilitas' => (int)($this->db->query("SELECT COUNT(*) as c FROM fasilitas")->single()['c'] ?? 0),
            'total_unit' => (int)($this->db->query("SELECT COALESCE(SUM(jumlah), 0) as s FROM fasilitas")->single()['s'] ?? 0),
            'fasilitas_baik' => (int)($this->db->query("SELECT COUNT(*) as c FROM fasilitas WHERE kondisi = 'baik'")->single()['c'] ?? 0),
            'rusak_ringan' => (int)($this->db->query("SELECT COUNT(*) as c FROM fasilitas WHERE kondisi = 'rusak_ringan'")->single()['c'] ?? 0),
            'rusak_berat' => (int)($this->db->query("SELECT COUNT(*) as c FROM fasilitas WHERE kondisi = 'rusak_berat'")->single()['c'] ?? 0),
            'tidak_layak' => (int)($this->db->query("SELECT COUNT(*) as c FROM fasilitas WHERE kondisi = 'tidak_layak'")->single()['c'] ?? 0),
            'laporan_menunggu' => (int)($this->db->query("SELECT COUNT(*) as c FROM laporan_kerusakan WHERE status = 'menunggu'")->single()['c'] ?? 0),
            'laporan_diproses' => (int)($this->db->query("SELECT COUNT(*) as c FROM laporan_kerusakan WHERE status IN ('diverifikasi', 'diproses')")->single()['c'] ?? 0),
            'laporan_selesai' => (int)($this->db->query("SELECT COUNT(*) as c FROM laporan_kerusakan WHERE status = 'selesai'")->single()['c'] ?? 0),
            'tugas_saya' => 0,
            'pemeliharaan_selesai' => (int)($this->db->query("SELECT COUNT(*) as c FROM pemeliharaan WHERE status = 'selesai'")->single()['c'] ?? 0)
        ];

        if ($petugasId !== null && $petugasId > 0) {
            $stats['tugas_saya'] = (int)($this->db->query(
                "SELECT COUNT(*) as c FROM pemeliharaan WHERE petugas_id = :pid AND status IN ('dijadwalkan', 'sedang_dikerjakan')"
            )->bind(':pid', $petugasId)->single()['c'] ?? 0);
        }

        return $stats;
    }

    /**
     * Mengambil distribusi kondisi fasilitas untuk grafik Chart.js
     */
    public function getDistribusiKondisi(): array {
        $kondisiRows = $this->db->query("SELECT kondisi, COUNT(*) as jumlah FROM fasilitas GROUP BY kondisi")->resultSet();
        $distribusiKondisi = [
            'baik' => 0,
            'rusak_ringan' => 0,
            'rusak_berat' => 0,
            'tidak_layak' => 0,
            'hilang' => 0
        ];
        foreach ($kondisiRows as $kr) {
            if (isset($distribusiKondisi[$kr['kondisi']])) {
                $distribusiKondisi[$kr['kondisi']] = (int)$kr['jumlah'];
            }
        }
        return $distribusiKondisi;
    }

    /**
     * Mengambil jumlah fasilitas dan unit per gedung untuk Bar Chart
     */
    public function getFasilitasPerGedung(): array {
        $sql = "SELECT g.id, g.nama_gedung, g.kode_gedung, 
                       COUNT(f.id) as total_fasilitas, 
                       COALESCE(SUM(f.jumlah), 0) as total_unit 
                FROM gedung g 
                LEFT JOIN ruangan r ON g.id = r.gedung_id 
                LEFT JOIN fasilitas f ON r.id = f.ruangan_id 
                GROUP BY g.id, g.nama_gedung, g.kode_gedung 
                ORDER BY total_fasilitas DESC";
        return $this->db->query($sql)->resultSet();
    }

    /**
     * Mengambil daftar laporan kerusakan terbaru
     */
    public function getLaporanTerbaru(int $limit = 5): array {
        $sql = "SELECT l.*, u.nama_lengkap as pelapor, f.nama as nama_fasilitas, f.kode_inventaris,
                       r.nama_ruangan, g.nama_gedung 
                FROM laporan_kerusakan l
                JOIN users u ON l.user_id = u.id
                JOIN fasilitas f ON l.fasilitas_id = f.id
                JOIN ruangan r ON l.ruangan_id = r.id
                JOIN gedung g ON r.gedung_id = g.id
                ORDER BY l.created_at DESC 
                LIMIT " . (int)$limit;
        return $this->db->query($sql)->resultSet();
    }

    /**
     * Mengambil daftar fasilitas yang paling sering dilaporkan atau diperbaiki
     */
    public function getFasilitasSeringRusak(int $limit = 5): array {
        $sql = "SELECT f.id, f.kode_inventaris, f.nama as nama_fasilitas, f.kondisi, 
                       r.nama_ruangan, g.nama_gedung,
                       COUNT(l.id) as total_laporan,
                       (SELECT COUNT(*) FROM pemeliharaan WHERE fasilitas_id = f.id AND jenis_tindakan = 'Perbaikan') as total_perbaikan
                FROM fasilitas f
                JOIN ruangan r ON f.ruangan_id = r.id
                JOIN gedung g ON r.gedung_id = g.id
                LEFT JOIN laporan_kerusakan l ON f.id = l.fasilitas_id
                GROUP BY f.id, f.kode_inventaris, f.nama, f.kondisi, r.nama_ruangan, g.nama_gedung
                ORDER BY (COUNT(l.id) + (SELECT COUNT(*) FROM pemeliharaan WHERE fasilitas_id = f.id AND jenis_tindakan = 'Perbaikan')) DESC, f.nama ASC
                LIMIT " . (int)$limit;
        return $this->db->query($sql)->resultSet();
    }

    /**
     * Mengambil fasilitas yang memerlukan perhatian (kondisi rusak / ada laporan pending)
     */
    public function getFasilitasPerluPerhatian(int $limit = 6): array {
        $sql = "SELECT f.id, f.kode_inventaris, f.nama as nama_fasilitas, f.kondisi, f.jumlah,
                       r.nama_ruangan, g.nama_gedung,
                       (SELECT COUNT(*) FROM laporan_kerusakan WHERE fasilitas_id = f.id AND status NOT IN ('selesai', 'ditolak')) as active_reports
                FROM fasilitas f
                JOIN ruangan r ON f.ruangan_id = r.id
                JOIN gedung g ON r.gedung_id = g.id
                WHERE f.kondisi IN ('rusak_ringan', 'rusak_berat', 'tidak_layak', 'hilang')
                   OR f.id IN (SELECT fasilitas_id FROM laporan_kerusakan WHERE status NOT IN ('selesai', 'ditolak'))
                ORDER BY 
                   CASE f.kondisi 
                      WHEN 'rusak_berat' THEN 1 
                      WHEN 'tidak_layak' THEN 2 
                      WHEN 'rusak_ringan' THEN 3 
                      WHEN 'hilang' THEN 4 
                      ELSE 5 
                   END ASC,
                   active_reports DESC
                LIMIT " . (int)$limit;
        return $this->db->query($sql)->resultSet();
    }

    /**
     * Mengambil daftar tugas pemeliharaan terjadwal khusus untuk petugas tertentu
     */
    public function getAssignedTasks(int $petugasId, int $limit = 5): array {
        $sql = "SELECT p.*, f.nama as nama_fasilitas, r.nama_ruangan, g.nama_gedung 
                FROM pemeliharaan p
                JOIN fasilitas f ON p.fasilitas_id = f.id
                JOIN ruangan r ON f.ruangan_id = r.id
                JOIN gedung g ON r.gedung_id = g.id
                WHERE p.petugas_id = :petugas_id 
                ORDER BY COALESCE(p.tanggal, p.tanggal_mulai) ASC 
                LIMIT " . (int)$limit;
        return $this->db->query($sql)->bind(':petugas_id', $petugasId)->resultSet();
    }

    /**
     * Statistik ringkasan untuk Dashboard Pengguna (Dosen / Mahasiswa)
     */
    public function getPenggunaStats(int $userId): array {
        return [
            'jumlah_ruangan' => (int)($this->db->query("SELECT COUNT(*) as c FROM ruangan WHERE status = 'tersedia'")->single()['c'] ?? 0),
            'total_ruangan_kampus' => (int)($this->db->query("SELECT COUNT(*) as c FROM ruangan")->single()['c'] ?? 0),
            'jumlah_fasilitas' => (int)($this->db->query("SELECT COUNT(*) as c FROM fasilitas")->single()['c'] ?? 0),
            'fasilitas_layak' => (int)($this->db->query("SELECT COUNT(*) as c FROM fasilitas WHERE kondisi = 'baik'")->single()['c'] ?? 0),
            'laporan_saya' => (int)($this->db->query("SELECT COUNT(*) as c FROM laporan_kerusakan WHERE user_id = :uid")->bind(':uid', $userId)->single()['c'] ?? 0),
            'laporan_diproses' => (int)($this->db->query("SELECT COUNT(*) as c FROM laporan_kerusakan WHERE user_id = :uid AND status IN ('menunggu', 'diverifikasi', 'diproses')")->bind(':uid', $userId)->single()['c'] ?? 0),
            'laporan_selesai' => (int)($this->db->query("SELECT COUNT(*) as c FROM laporan_kerusakan WHERE user_id = :uid AND status = 'selesai'")->bind(':uid', $userId)->single()['c'] ?? 0)
        ];
    }

    /**
     * Riwayat laporan kerusakan yang diajukan oleh pengguna tertentu
     */
    public function getUserReports(int $userId, int $limit = 5): array {
        $sql = "SELECT l.*, f.nama as nama_fasilitas, r.nama_ruangan, g.nama_gedung 
                FROM laporan_kerusakan l
                JOIN fasilitas f ON l.fasilitas_id = f.id
                JOIN ruangan r ON l.ruangan_id = r.id
                JOIN gedung g ON r.gedung_id = g.id
                WHERE l.user_id = :uid 
                ORDER BY l.created_at DESC 
                LIMIT " . (int)$limit;
        return $this->db->query($sql)->bind(':uid', $userId)->resultSet();
    }

    /**
     * Daftar ruangan yang berstatus tersedia beserta total fasilitasnya
     */
    public function getAvailableRooms(int $limit = 6): array {
        $sql = "SELECT r.*, g.nama_gedung, 
                       (SELECT COUNT(*) FROM fasilitas WHERE ruangan_id = r.id) as total_fasilitas 
                FROM ruangan r
                JOIN gedung g ON r.gedung_id = g.id
                WHERE r.status = 'tersedia'
                ORDER BY g.nama_gedung ASC, r.nama_ruangan ASC 
                LIMIT " . (int)$limit;
        return $this->db->query($sql)->resultSet();
    }

    /**
     * Catatan pemeliharaan publik terbaru yang telah selesai
     */
    public function getPublicMaintenance(int $limit = 4): array {
        $sql = "SELECT p.*, f.nama as nama_fasilitas, r.nama_ruangan, u.nama_lengkap as nama_petugas
                FROM pemeliharaan p
                JOIN fasilitas f ON p.fasilitas_id = f.id
                JOIN ruangan r ON f.ruangan_id = r.id
                JOIN users u ON p.petugas_id = u.id
                WHERE p.tampil_publik = 1 AND p.status = 'selesai'
                ORDER BY COALESCE(p.tanggal, p.tanggal_mulai) DESC 
                LIMIT " . (int)$limit;
        return $this->db->query($sql)->resultSet();
    }
}
