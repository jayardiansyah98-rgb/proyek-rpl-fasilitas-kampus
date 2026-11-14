<?php
/**
 * AIMaintenanceRecommender
 * Service cerdas untuk menghasilkan rekomendasi prioritas pemeliharaan sarpras.
 * 
 * Aturan Ketat:
 * - AI HANYA memberikan rekomendasi, bukan keputusan otomatis.
 * - JANGAN mengubah status fasilitas secara otomatis berdasarkan AI.
 * - Tampilkan alasan rekomendasi secara terperinci agar Petugas dapat memverifikasi.
 * - Jika data tidak cukup, AI harus mengatakan bahwa data tidak cukup.
 * 
 * Pengelompokan:
 * - PRIORITAS TINGGI
 * - PRIORITAS SEDANG
 * - PRIORITAS RENDAH / PREVENTIF
 */

require_once __DIR__ . '/../../core/Database.php';

class AIMaintenanceRecommender {
    protected $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Menghasilkan rekomendasi prioritas pemeliharaan
     * 
     * @return array
     */
    public function getRecommendations(): array {
        $currentYear = (int)date('Y');

        // Ambil data fasilitas lengkap dengan indikator riwayat
        $facilities = $this->db->query(
            "SELECT f.id, f.kode_inventaris, f.nama, f.jumlah, f.kondisi, f.tahun_pengadaan,
                    r.id as ruangan_id, r.nama_ruangan, r.kode_ruangan, r.lantai,
                    g.nama_gedung, g.kode_gedung,
                    k.nama_kategori,
                    (SELECT COUNT(*) FROM laporan_kerusakan WHERE fasilitas_id = f.id) as total_laporan,
                    (SELECT COUNT(*) FROM laporan_kerusakan WHERE fasilitas_id = f.id AND status NOT IN ('selesai', 'ditolak')) as active_laporan,
                    (SELECT prioritas FROM laporan_kerusakan WHERE fasilitas_id = f.id AND status NOT IN ('selesai', 'ditolak') ORDER BY FIELD(prioritas, 'darurat', 'tinggi', 'sedang', 'rendah') ASC LIMIT 1) as prioritas_tiket,
                    (SELECT deskripsi FROM laporan_kerusakan WHERE fasilitas_id = f.id ORDER BY created_at DESC LIMIT 1) as keluhan_terakhir,
                    (SELECT COUNT(*) FROM pemeliharaan WHERE fasilitas_id = f.id) as total_servis,
                    (SELECT MAX(COALESCE(tanggal, tanggal_mulai)) FROM pemeliharaan WHERE fasilitas_id = f.id) as terakhir_servis
             FROM fasilitas f
             JOIN ruangan r ON f.ruangan_id = r.id
             JOIN gedung g ON r.gedung_id = g.id
             JOIN kategori_fasilitas k ON f.kategori_id = k.id
             ORDER BY f.id ASC"
        )->resultSet();

        if (empty($facilities)) {
            return [
                'cukup_data' => false,
                'pesan_data_kurang' => 'Data fasilitas belum mencukupi untuk menyusun rekomendasi prioritas pemeliharaan.',
                'prioritas_tinggi' => [],
                'prioritas_sedang' => [],
                'prioritas_rendah' => [],
                'total_rekomendasi' => 0
            ];
        }

        $prioritasTinggi = [];
        $prioritasSedang = [];
        $prioritasRendah = [];

        foreach ($facilities as $f) {
            $usia = max(0, $currentYear - (int)$f['tahun_pengadaan']);
            $kondisi = $f['kondisi'];
            $totalLaporan = (int)$f['total_laporan'];
            $activeLaporan = (int)$f['active_laporan'];
            $prioTiket = $f['prioritas_tiket'] ?? '';
            $terakhirServis = !empty($f['terakhir_servis']) ? date('d/m/Y', strtotime($f['terakhir_servis'])) : 'Belum pernah diservis';

            // 1. KLASIFIKASI PRIORITAS TINGGI
            // Kriteria:
            // - Rusak berat / tidak layak
            // - ATAU dilaporkan >= 3 kali
            // - ATAU usia >= 5 tahun dengan kondisi rusak dan ada laporan
            // - ATAU memiliki tiket aktif berprioritas darurat / tinggi
            if ($kondisi === 'rusak_berat' || $kondisi === 'tidak_layak' || $totalLaporan >= 3 || $prioTiket === 'darurat' || ($kondisi === 'rusak_ringan' && $totalLaporan >= 2 && $usia >= 4)) {
                $alasanList = [];
                if ($kondisi === 'rusak_berat') {
                    $alasanList[] = "Rusak berat dan tidak dapat digunakan.";
                } elseif ($kondisi === 'tidak_layak') {
                    $alasanList[] = "Kondisi tidak layak (afkir), berisiko keselamatan atau menghentikan aktivitas kelas.";
                }

                if ($totalLaporan > 0) {
                    $alasanList[] = "Sudah dilaporkan {$totalLaporan} kali oleh civitas akademika.";
                }

                $alasanList[] = "Usia fasilitas {$usia} tahun (Pengadaan {$f['tahun_pengadaan']}).";

                if ($activeLaporan > 0) {
                    $alasanList[] = "Terdapat {$activeLaporan} tiket pengaduan aktif yang sedang menunggu penanganan teknisi.";
                }

                $tindakan = ($kondisi === 'tidak_layak') 
                    ? "Evaluasi penghapusan aset (afkir) dan pengadaan unit pengganti." 
                    : "Pemeriksaan teknis menyeluruh, perbaikan modul/komponen utama segera, atau penggantian unit cadangan.";

                $prioritasTinggi[] = [
                    'id' => (int)$f['id'],
                    'nama' => $f['nama'],
                    'kode_inventaris' => $f['kode_inventaris'],
                    'kondisi' => $kondisi,
                    'label_kondisi' => ucfirst(str_replace('_', ' ', $kondisi)),
                    'jumlah' => (int)$f['jumlah'],
                    'lokasi' => "{$f['nama_ruangan']} ({$f['nama_gedung']})",
                    'tahun_pengadaan' => (int)$f['tahun_pengadaan'],
                    'umur_tahun' => $usia,
                    'total_laporan' => $totalLaporan,
                    'alasan_poin' => $alasanList,
                    'alasan_ringkas' => implode(" • ", $alasanList),
                    'tindakan_disarankan' => $tindakan,
                    'terakhir_servis' => $terakhirServis,
                    'prioritas' => 'TINGGI'
                ];
            }

            // 2. KLASIFIKASI PRIORITAS SEDANG
            // Kriteria:
            // - Rusak ringan
            // - ATAU dilaporkan 1-2 kali
            // - ATAU ada tiket aktif sedang/rendah
            elseif ($kondisi === 'rusak_ringan' || $totalLaporan >= 1 || $activeLaporan > 0) {
                $alasanList = [];
                if ($kondisi === 'rusak_ringan') {
                    $alasanList[] = "Kondisi fisik rusak ringan, penurunan performa alat.";
                }

                if ($totalLaporan > 0) {
                    $alasanList[] = "Dilaporkan {$totalLaporan} kali oleh civitas.";
                }

                $alasanList[] = "Usia fasilitas {$usia} tahun (Pengadaan {$f['tahun_pengadaan']}).";

                $tindakan = "Servis berkala, kalibrasi sistem, penggantian komponen aus ringan, dan pembersihan menyeluruh.";

                $prioritasSedang[] = [
                    'id' => (int)$f['id'],
                    'nama' => $f['nama'],
                    'kode_inventaris' => $f['kode_inventaris'],
                    'kondisi' => $kondisi,
                    'label_kondisi' => ucfirst(str_replace('_', ' ', $kondisi)),
                    'jumlah' => (int)$f['jumlah'],
                    'lokasi' => "{$f['nama_ruangan']} ({$f['nama_gedung']})",
                    'tahun_pengadaan' => (int)$f['tahun_pengadaan'],
                    'umur_tahun' => $usia,
                    'total_laporan' => $totalLaporan,
                    'alasan_poin' => $alasanList,
                    'alasan_ringkas' => implode(" • ", $alasanList),
                    'tindakan_disarankan' => $tindakan,
                    'terakhir_servis' => $terakhirServis,
                    'prioritas' => 'SEDANG'
                ];
            }

            // 3. KLASIFIKASI PRIORITAS RENDAH / PREVENTIF
            // Kriteria:
            // - Kondisi baik tapi usia >= 2-3 tahun, atau aset vital (AC, Proyektor, Switch) yang perlu pencegahan rutin
            elseif ($kondisi === 'baik' && ($usia >= 2 || in_array($f['nama_kategori'], ['Pendingin Ruangan (AC)', 'Komputer & Server', 'Multimedia & Proyektor']))) {
                $alasanList = [
                    "Kondisi saat ini masih baik & operasional.",
                    "Usia aset {$usia} tahun memerlukan inspeksi preventif berkala.",
                    "Servis terakhir: {$terakhirServis}."
                ];

                $tindakan = "Pemeriksaan fungsi berkala (cuci filter AC, cek koneksi kelistrikan, cek thermal paste PC/Proyektor).";

                $prioritasRendah[] = [
                    'id' => (int)$f['id'],
                    'nama' => $f['nama'],
                    'kode_inventaris' => $f['kode_inventaris'],
                    'kondisi' => $kondisi,
                    'label_kondisi' => 'Baik (Preventif)',
                    'jumlah' => (int)$f['jumlah'],
                    'lokasi' => "{$f['nama_ruangan']} ({$f['nama_gedung']})",
                    'tahun_pengadaan' => (int)$f['tahun_pengadaan'],
                    'umur_tahun' => $usia,
                    'total_laporan' => $totalLaporan,
                    'alasan_poin' => $alasanList,
                    'alasan_ringkas' => implode(" • ", $alasanList),
                    'tindakan_disarankan' => $tindakan,
                    'terakhir_servis' => $terakhirServis,
                    'prioritas' => 'RENDAH / PREVENTIF'
                ];
            }
        }

        // Pengecekan apakah ada data rekomendasi
        $totalRekomendasi = count($prioritasTinggi) + count($prioritasSedang) + count($prioritasRendah);

        return [
            'cukup_data' => true,
            'prioritas_tinggi' => $prioritasTinggi,
            'prioritas_sedang' => $prioritasSedang,
            'prioritas_rendah' => $prioritasRendah,
            'total_rekomendasi' => $totalRekomendasi,
            'total_tinggi' => count($prioritasTinggi),
            'total_sedang' => count($prioritasSedang),
            'total_rendah' => count($prioritasRendah)
        ];
    }
}
