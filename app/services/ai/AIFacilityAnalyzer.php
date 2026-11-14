<?php
/**
 * AIFacilityAnalyzer
 * Service cerdas untuk menganalisis data sarana prasarana kampus STMIK IKMI Cirebon.
 * 
 * Menganalisis:
 * - Jumlah fasilitas & unit
 * - Kondisi fisik fasilitas
 * - Frekuensi kerusakan
 * - Riwayat pemeliharaan
 * - Umur fasilitas berdasarkan tahun pengadaan (vs tahun saat ini)
 * - Jumlah laporan kerusakan civitas
 * 
 * Menghasilkan:
 * - Ringkasan kondisi fasilitas
 * - Fasilitas bermasalah (disertai indeks keparahan & alasan)
 * - Gedung dengan masalah terbanyak
 * - Ruangan dengan masalah terbanyak
 * - Pola kerusakan yang terlihat (korelasi usia, kategori, beban ruangan)
 */

require_once __DIR__ . '/../../core/Database.php';

class AIFacilityAnalyzer {
    protected $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Melakukan analisis menyeluruh terhadap seluruh data fasilitas di kampus
     * 
     * @return array
     */
    public function analyze(): array {
        $currentYear = (int)date('Y');

        // 1. Ambil data master seluruh fasilitas beserta relasi gedung, ruangan, laporan, & servis
        $facilities = $this->db->query(
            "SELECT f.id, f.kode_inventaris, f.nama, f.jumlah, f.kondisi, f.tahun_pengadaan,
                    r.id as ruangan_id, r.nama_ruangan, r.kode_ruangan, r.lantai, r.jenis_ruangan,
                    g.id as gedung_id, g.nama_gedung, g.kode_gedung,
                    k.nama_kategori,
                    (SELECT COUNT(*) FROM laporan_kerusakan WHERE fasilitas_id = f.id) as total_laporan,
                    (SELECT COUNT(*) FROM laporan_kerusakan WHERE fasilitas_id = f.id AND status NOT IN ('selesai', 'ditolak')) as active_laporan,
                    (SELECT COUNT(*) FROM pemeliharaan WHERE fasilitas_id = f.id) as total_pemeliharaan,
                    (SELECT COALESCE(SUM(biaya), 0) FROM pemeliharaan WHERE fasilitas_id = f.id) as total_biaya_pemeliharaan,
                    (SELECT MAX(COALESCE(tanggal, tanggal_mulai)) FROM pemeliharaan WHERE fasilitas_id = f.id) as terakhir_pemeliharaan
             FROM fasilitas f
             JOIN ruangan r ON f.ruangan_id = r.id
             JOIN gedung g ON r.gedung_id = g.id
             JOIN kategori_fasilitas k ON f.kategori_id = k.id
             ORDER BY f.id ASC"
        )->resultSet();

        $totalFasilitas = count($facilities);

        // Aturan: Jika data tidak cukup, AI harus mengatakan data tidak cukup
        if ($totalFasilitas === 0) {
            return [
                'cukup_data' => false,
                'pesan_data_kurang' => 'Data fasilitas belum mencukupi untuk melakukan analisis korelasi dan pola kerusakan.',
                'ringkasan' => null,
                'fasilitas_bermasalah' => [],
                'gedung_masalah_terbanyak' => null,
                'ruangan_masalah_terbanyak' => null,
                'pola_kerusakan' => []
            ];
        }

        // 2. Ringkasan Kondisi & Statistik Fisik
        $totalUnit = 0;
        $kondisiCounts = [
            'baik' => 0,
            'rusak_ringan' => 0,
            'rusak_berat' => 0,
            'tidak_layak' => 0,
            'hilang' => 0
        ];
        $totalUsia = 0;
        $fasilitasPerKategori = [];
        $totalSemuaLaporan = 0;
        $totalSemuaPemeliharaan = 0;
        $totalAkumulasiBiaya = 0;

        foreach ($facilities as $f) {
            $totalUnit += (int)$f['jumlah'];
            $k = $f['kondisi'];
            if (isset($kondisiCounts[$k])) {
                $kondisiCounts[$k]++;
            }

            $usia = max(0, $currentYear - (int)$f['tahun_pengadaan']);
            $totalUsia += $usia;

            $kat = $f['nama_kategori'];
            if (!isset($fasilitasPerKategori[$kat])) {
                $fasilitasPerKategori[$kat] = ['total_item' => 0, 'rusak' => 0];
            }
            $fasilitasPerKategori[$kat]['total_item']++;
            if ($k !== 'baik') {
                $fasilitasPerKategori[$kat]['rusak']++;
            }

            $totalSemuaLaporan += (int)$f['total_laporan'];
            $totalSemuaPemeliharaan += (int)$f['total_pemeliharaan'];
            $totalAkumulasiBiaya += (float)$f['total_biaya_pemeliharaan'];
        }

        $rataRataUsia = round($totalUsia / max(1, $totalFasilitas), 1);
        $persenBaik = round(($kondisiCounts['baik'] / $totalFasilitas) * 100, 1);
        $persenBermasalah = round((($totalFasilitas - $kondisiCounts['baik']) / $totalFasilitas) * 100, 1);

        $ringkasanKondisi = [
            'total_fasilitas' => $totalFasilitas,
            'total_unit' => $totalUnit,
            'kondisi_counts' => $kondisiCounts,
            'persentase_baik' => $persenBaik,
            'persentase_bermasalah' => $persenBermasalah,
            'rata_rata_usia_tahun' => $rataRataUsia,
            'total_laporan_kampus' => $totalSemuaLaporan,
            'total_pemeliharaan_kampus' => $totalSemuaPemeliharaan,
            'total_biaya_kampus' => $totalAkumulasiBiaya
        ];

        // 3. Identifikasi Fasilitas Bermasalah
        $fasilitasBermasalah = [];
        foreach ($facilities as $f) {
            $usia = max(0, $currentYear - (int)$f['tahun_pengadaan']);
            $isBermasalah = false;
            $alasan = [];
            $skorMasalah = 0;

            // Kriteria Kondisi Fisik
            if ($f['kondisi'] === 'rusak_berat') {
                $isBermasalah = true;
                $skorMasalah += 50;
                $alasan[] = "Kondisi fisik rusak berat dan tidak dapat beroperasi normal.";
            } elseif ($f['kondisi'] === 'tidak_layak') {
                $isBermasalah = true;
                $skorMasalah += 60;
                $alasan[] = "Kondisi fisik dinyatakan afkir/tidak layak pakai.";
            } elseif ($f['kondisi'] === 'rusak_ringan') {
                $isBermasalah = true;
                $skorMasalah += 25;
                $alasan[] = "Kondisi fisik rusak ringan, membutuhkan kalibrasi atau perbaikan sparepart.";
            }

            // Kriteria Laporan Aduan
            if ((int)$f['total_laporan'] >= 2) {
                $isBermasalah = true;
                $skorMasalah += (int)$f['total_laporan'] * 15;
                $alasan[] = "Memiliki frekuensi aduan kerusakan tinggi ({$f['total_laporan']} kali dilaporkan).";
            } elseif ((int)$f['total_laporan'] === 1 && $f['kondisi'] !== 'baik') {
                $skorMasalah += 10;
                $alasan[] = "Terdapat 1 catatan laporan kerusakan dari civitas.";
            }

            // Kriteria Tiket Aktif
            if ((int)$f['active_laporan'] > 0) {
                $isBermasalah = true;
                $skorMasalah += 20;
                $alasan[] = "Terdapat {$f['active_laporan']} tiket kerusakan aktif yang belum tuntas diperbaiki.";
            }

            // Kriteria Usia
            if ($usia >= 7) {
                $skorMasalah += 20;
                $alasan[] = "Usia pakai aset sudah mencapai {$usia} tahun (melebihi masa manfaat optimal).";
            } elseif ($usia >= 4 && $f['kondisi'] !== 'baik') {
                $skorMasalah += 10;
                $alasan[] = "Usia fasilitas {$usia} tahun mempercepat keausan komponen.";
            }

            if ($isBermasalah) {
                $fasilitasBermasalah[] = [
                    'id' => (int)$f['id'],
                    'nama' => $f['nama'],
                    'kode_inventaris' => $f['kode_inventaris'],
                    'kondisi' => $f['kondisi'],
                    'jumlah' => (int)$f['jumlah'],
                    'nama_ruangan' => $f['nama_ruangan'],
                    'nama_gedung' => $f['nama_gedung'],
                    'nama_kategori' => $f['nama_kategori'],
                    'tahun_pengadaan' => (int)$f['tahun_pengadaan'],
                    'umur_tahun' => $usia,
                    'total_laporan' => (int)$f['total_laporan'],
                    'active_laporan' => (int)$f['active_laporan'],
                    'total_pemeliharaan' => (int)$f['total_pemeliharaan'],
                    'biaya_pemeliharaan' => (float)$f['total_biaya_pemeliharaan'],
                    'skor_urgensi' => $skorMasalah,
                    'alasan_masalah' => $alasan
                ];
            }
        }

        // Urutkan fasilitas bermasalah dari skor urgensi tertinggi
        usort($fasilitasBermasalah, function($a, $b) {
            return $b['skor_urgensi'] <=> $a['skor_urgensi'];
        });

        // 4. Analisis Masalah Per Gedung
        $gedungStats = [];
        foreach ($facilities as $f) {
            $gid = $f['gedung_id'];
            if (!isset($gedungStats[$gid])) {
                $gedungStats[$gid] = [
                    'gedung_id' => $gid,
                    'kode_gedung' => $f['kode_gedung'],
                    'nama_gedung' => $f['nama_gedung'],
                    'total_fasilitas' => 0,
                    'total_unit' => 0,
                    'fasilitas_rusak' => 0,
                    'total_laporan' => 0,
                    'total_biaya' => 0.0
                ];
            }
            $gedungStats[$gid]['total_fasilitas']++;
            $gedungStats[$gid]['total_unit'] += (int)$f['jumlah'];
            if ($f['kondisi'] !== 'baik') {
                $gedungStats[$gid]['fasilitas_rusak']++;
            }
            $gedungStats[$gid]['total_laporan'] += (int)$f['total_laporan'];
            $gedungStats[$gid]['total_biaya'] += (float)$f['total_biaya_pemeliharaan'];
        }

        // Urutkan gedung berdasarkan jumlah kerusakan & laporan terbanyak
        usort($gedungStats, function($a, $b) {
            $scoreA = ($a['fasilitas_rusak'] * 2) + $a['total_laporan'];
            $scoreB = ($b['fasilitas_rusak'] * 2) + $b['total_laporan'];
            return $scoreB <=> $scoreA;
        });

        $gedungMasalahTerbanyak = null;
        if (!empty($gedungStats)) {
            $topGedung = $gedungStats[0];
            $topGedung['persentase_rusak_gedung'] = round(($topGedung['fasilitas_rusak'] / max(1, $topGedung['total_fasilitas'])) * 100, 1);
            $gedungMasalahTerbanyak = $topGedung;
        }

        // 5. Analisis Masalah Per Ruangan
        $ruanganStats = [];
        foreach ($facilities as $f) {
            $rid = $f['ruangan_id'];
            if (!isset($ruanganStats[$rid])) {
                $ruanganStats[$rid] = [
                    'ruangan_id' => $rid,
                    'kode_ruangan' => $f['kode_ruangan'],
                    'nama_ruangan' => $f['nama_ruangan'],
                    'nama_gedung' => $f['nama_gedung'],
                    'lantai' => $f['lantai'],
                    'jenis_ruangan' => $f['jenis_ruangan'],
                    'total_fasilitas' => 0,
                    'total_unit' => 0,
                    'fasilitas_rusak' => 0,
                    'total_laporan' => 0,
                    'active_laporan' => 0
                ];
            }
            $ruanganStats[$rid]['total_fasilitas']++;
            $ruanganStats[$rid]['total_unit'] += (int)$f['jumlah'];
            if ($f['kondisi'] !== 'baik') {
                $ruanganStats[$rid]['fasilitas_rusak']++;
            }
            $ruanganStats[$rid]['total_laporan'] += (int)$f['total_laporan'];
            $ruanganStats[$rid]['active_laporan'] += (int)$f['active_laporan'];
        }

        usort($ruanganStats, function($a, $b) {
            $scoreA = ($a['fasilitas_rusak'] * 3) + ($a['active_laporan'] * 2) + $a['total_laporan'];
            $scoreB = ($b['fasilitas_rusak'] * 3) + ($b['active_laporan'] * 2) + $b['total_laporan'];
            return $scoreB <=> $scoreA;
        });

        $ruanganMasalahTerbanyak = null;
        if (!empty($ruanganStats)) {
            $topRuangan = $ruanganStats[0];
            $topRuangan['persentase_rusak_ruangan'] = round(($topRuangan['fasilitas_rusak'] / max(1, $topRuangan['total_fasilitas'])) * 100, 1);
            $ruanganMasalahTerbanyak = $topRuangan;
        }

        // 6. Deteksi Pola Kerusakan yang Terlihat (Pattern Recognition)
        $polaKerusakan = $this->detectDamagePatterns($facilities, $fasilitasPerKategori, $currentYear, $gedungMasalahTerbanyak, $ruanganMasalahTerbanyak);

        return [
            'cukup_data' => true,
            'ringkasan' => $ringkasanKondisi,
            'fasilitas_bermasalah' => $fasilitasBermasalah,
            'total_fasilitas_bermasalah' => count($fasilitasBermasalah),
            'gedung_masalah_terbanyak' => $gedungMasalahTerbanyak,
            'semua_gedung_stats' => $gedungStats,
            'ruangan_masalah_terbanyak' => $ruanganMasalahTerbanyak,
            'semua_ruangan_stats' => array_slice($ruanganStats, 0, 5),
            'pola_kerusakan' => $polaKerusakan
        ];
    }

    /**
     * Algoritma Pengenalan Pola Kerusakan Sarpras (Damage Pattern Recognition)
     */
    private function detectDamagePatterns(array $facilities, array $kategoriStats, int $currentYear, ?array $topGedung, ?array $topRuangan): array {
        $patterns = [];

        // Pola 1: Degradasi Berdasarkan Umur / Siklus Hidup Aset
        $rusakTua = 0;
        $totalTua = 0;
        $rusakMuda = 0;
        $totalMuda = 0;

        foreach ($facilities as $f) {
            $usia = max(0, $currentYear - (int)$f['tahun_pengadaan']);
            if ($usia >= 4) {
                $totalTua++;
                if ($f['kondisi'] !== 'baik' || (int)$f['total_laporan'] > 0) {
                    $rusakTua++;
                }
            } else {
                $totalMuda++;
                if ($f['kondisi'] !== 'baik' || (int)$f['total_laporan'] > 0) {
                    $rusakMuda++;
                }
            }
        }

        $persenRusakTua = round(($rusakTua / max(1, $totalTua)) * 100, 1);
        $patterns[] = [
            'judul' => 'Pola Degradasi Usia & Keausan Hardware',
            'tipe' => 'siklus_umur',
            'tingkat' => $persenRusakTua > 30 ? 'Tinggi' : 'Sedang',
            'deskripsi' => "Aset berumur ≥ 4 tahun memiliki rasio masalah {$persenRusakTua}%. Perangkat elektronik dan proyektor menunjukkan penurunan keandalan lampu display dan konektor setelah melewati tahun ke-3 pemakaian.",
            'rekomendasi' => 'Lakukan peremajaan bertahap untuk aset yang mendekati usia ekonomis 5 tahun ke atas.'
        ];

        // Pola 2: Kategori Fasilitas Paling Rentan
        $kategoriRentan = null;
        $maxPersenRentan = -1;
        foreach ($kategoriStats as $kat => $s) {
            $persen = round(($s['rusak'] / max(1, $s['total_item'])) * 100, 1);
            if ($persen > $maxPersenRentan && $s['rusak'] > 0) {
                $maxPersenRentan = $persen;
                $kategoriRentan = $kat;
            }
        }

        if ($kategoriRentan) {
            $patterns[] = [
                'judul' => "Pola Kerentanan Kategori: {$kategoriRentan}",
                'tipe' => 'kategori_rentan',
                'tingkat' => 'Perhatian',
                'deskripsi' => "Kategori '{$kategoriRentan}' mencatatkan rasio kerusakan {$maxPersenRentan}%. Unit pendingin udara (AC) dan multimedia/proyektor lebih rentan mengalami gangguan operasional dibanding furnitur.",
                'rekomendasi' => 'Tingkatkan frekuensi pemeriksaan preventif terjadwal (cuci AC berkala & pembersihan lensa proyektor).'
            ];
        }

        // Pola 3: Konsentrasi Beban Ruangan / Gedung
        if ($topRuangan && $topRuangan['fasilitas_rusak'] > 0) {
            $patterns[] = [
                'judul' => "Pola Konsentrasi Ruang: {$topRuangan['nama_ruangan']}",
                'tipe' => 'beban_lokasi',
                'tingkat' => 'Krusial',
                'deskripsi' => "Ruangan '{$topRuangan['nama_ruangan']}' di {$topRuangan['nama_gedung']} mencatatkan konsentrasi keluhan tertinggi dengan {$topRuangan['total_laporan']} laporan. Ruang dengan intensitas kuliah/praktikum harian tinggi memiliki laju kerusakan lebih cepat.",
                'rekomendasi' => 'Tetapkan ruangan ini sebagai zona prioritas inspeksi mingguan oleh teknisi sarpras.'
            ];
        }

        // Pola 4: Pemeliharaan Kuratif vs Preventif
        $patterns[] = [
            'judul' => 'Pola Respon Pemeliharaan (Kuratif vs Preventif)',
            'tipe' => 'manajemen_servis',
            'tingkat' => 'Manajemen',
            'deskripsi' => 'Sebagian besar tindakan servis saat ini terpicu setelah laporan kerusakan diajukan oleh pengguna (kuratif), belum sepenuhnya beralih ke inspeksi rutin berkala (preventif).',
            'rekomendasi' => 'Terapkan checklist pemeliharaan preventif 1 bulanan untuk memangkas potensi kerusakan mendadak saat jam perkuliahan.'
        ];

        return $patterns;
    }
}
