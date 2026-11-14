<?php
/**
 * RuleBasedEngine
 * Engine AI berbasis aturan dan fakta database lokal (Zero-Hallucination).
 * Bekerja 100% offline tanpa ketergantungan API pihak ketiga, menjamin kepatuhan data faktual.
 */

require_once __DIR__ . '/AIProviderInterface.php';

class RuleBasedEngine implements AIProviderInterface {

    public function getName(): string {
        return 'IKMI Deterministic Grounding Engine (Offline)';
    }

    public function isAvailable(): bool {
        return true;
    }

    public function generateAnswer(string $systemPrompt, string $userQuestion, array $contextData): string {
        $intent = $contextData['intent'] ?? 'GENERAL_QUERY';
        $data = $contextData['data'] ?? [];

        switch ($intent) {
            case 'COUNT_ROOM_TOTAL':
                return (string)($data['total_ruangan'] ?? 0);

            case 'COUNT_ROOM_AVAILABLE':
                return (string)($data['ruangan_tersedia'] ?? 0);

            case 'COUNT_ROOM_REPAIR':
                return (string)($data['ruangan_perbaikan'] ?? 0);

            case 'COUNT_FACILITY_UNITS':
                return (string)($data['total_unit_fasilitas'] ?? 0);

            case 'COUNT_FACILITY_TYPES':
                return (string)($data['total_item_fasilitas'] ?? 0);

            case 'AVAILABLE_FACILITY_LIST':
                return $this->formatAvailableFacilityList($data);

            case 'AVAILABLE_ROOM_LIST':
                return $this->formatAvailableRoomList($data);

            case 'ALL_ROOM_LIST':
                return $this->formatAllRoomList($data);

            case 'COUNT_CONDITION':
                return $this->formatCountCondition($data, $userQuestion);

            case 'MOST_DAMAGED':
                return $this->formatMostDamaged($data);

            case 'FACILITIES_BY_BUILDING':
                return $this->formatFacilitiesByBuilding($data, $contextData['entity'] ?? '');

            case 'ROOM_MOST_FACILITIES':
                return $this->formatRoomMostFacilities($data);

            case 'PRIORITY_REPAIR':
                return $this->formatPriorityRepair($data);

            case 'FACILITIES_IN_ROOM':
                return $this->formatFacilitiesInRoom($data, $contextData['entity'] ?? '');

            case 'RECOMMEND_MAINTENANCE':
                return $this->formatRecommendMaintenance($data);

            case 'CAMPUS_STATS':
            default:
                return $this->formatCampusStats($data);
        }
    }

    /**
     * Format rincian daftar ruangan yang tersedia
     */
    private function formatAvailableRoomList(array $rooms): string {
        if (empty($rooms)) {
            return "Saat ini belum ada ruangan yang berstatus tersedia di database.";
        }
        $res = "Berikut daftar ruangan yang tersedia di kampus STMIK IKMI Cirebon (total " . count($rooms) . " ruangan):\n\n";
        foreach ($rooms as $idx => $r) {
            $num = $idx + 1;
            $res .= "{$num}. **{$r['nama_ruangan']}** ({$r['nama_gedung']}, Lt. {$r['lantai']})\n" .
                    "   • Jenis: {$r['jenis_ruangan']} | Kapasitas: {$r['kapasitas']} orang\n" .
                    "   • Status: *{$r['status']}* ({$r['total_item']} jenis fasilitas, {$r['total_unit']} unit)\n\n";
        }
        return trim($res);
    }

    /**
     * Format daftar seluruh ruangan di kampus
     */
    private function formatAllRoomList(array $rooms): string {
        if (empty($rooms)) {
            return "Belum ada data ruangan yang terdata di sistem.";
        }
        $res = "Berikut daftar ruangan yang terdata di kampus STMIK IKMI Cirebon (total " . count($rooms) . " ruangan):\n\n";
        foreach ($rooms as $idx => $r) {
            $num = $idx + 1;
            $res .= "{$num}. **{$r['nama_ruangan']}** ({$r['nama_gedung']}, Lt. {$r['lantai']})\n" .
                    "   • Jenis: {$r['jenis_ruangan']} | Status: *{$r['status']}*\n" .
                    "   • Inventaris: {$r['total_item']} jenis fasilitas ({$r['total_unit']} unit)\n\n";
        }
        return trim($res);
    }

    /**
     * Format rincian penjelasan fasilitas yang tersedia
     */
    private function formatAvailableFacilityList(array $facilities): string {
        if (empty($facilities)) {
            return "Saat ini belum ada data fasilitas yang berstatus tersedia di database.";
        }
        $totalUnit = array_sum(array_column($facilities, 'jumlah'));
        $res = "Berikut rincian penjelasan fasilitas yang tersedia di kampus STMIK IKMI Cirebon (total " . count($facilities) . " jenis inventaris, akumulasi {$totalUnit} unit fisik):\n\n";
        foreach ($facilities as $idx => $f) {
            $num = $idx + 1;
            $kondisi = ucwords(str_replace('_', ' ', $f['kondisi']));
            $res .= "{$num}. **{$f['nama']}** ({$f['jumlah']} unit, kondisi {$kondisi})\n" .
                    "   • Lokasi: {$f['nama_ruangan']} ({$f['nama_gedung']}, Lt. {$f['lantai']})\n";
            $cleanKet = !empty($f['keterangan']) ? trim(explode("\n", $f['keterangan'])[0]) : '';
            if (!empty($cleanKet)) {
                $res .= "   • Keterangan: {$cleanKet}\n";
            }
            $res .= "\n";
        }
        return trim($res);
    }

    /**
     * 1. Format jawaban hitung kondisi fasilitas
     */
    private function formatCountCondition(array $data, string $q): string {
        $label = $data['label_kondisi'] ?? 'Kondisi';
        $totalItem = $data['total_item'] ?? 0;
        $totalUnit = $data['total_unit'] ?? 0;
        $items = $data['daftar_fasilitas'] ?? [];

        $isCountOnly = preg_match('/(berapa|jumlah|total|ada berapa)/i', $q) &&
                       !preg_match('/(daftar|jelaskan|sebutkan|rincian|detail|tampilkan|apa saja)/i', $q);
        if ($isCountOnly) {
            return (string)$totalItem;
        }

        if ($totalItem === 0) {
            return "Berdasarkan data terkini di database sarana dan prasarana kampus STMIK IKMI Cirebon:\n\n" .
                   "Saat ini **tidak terdapat fasilitas dengan kondisi {$label}** (0 item / 0 unit).\n\n" .
                   "Seluruh fasilitas dalam master data berada dalam kondisi operasional lainnya (misalnya berkondisi *Baik* atau *Rusak Ringan*).";
        }

        $res = "Berdasarkan data terkini di database kampus STMIK IKMI Cirebon:\n\n" .
               "Terdapat **{$totalItem} jenis fasilitas** (total **{$totalUnit} unit**) yang berstatus **{$label}**.\n\n" .
               "Rincian fasilitas tersebut:\n";

        foreach ($items as $idx => $it) {
            $num = $idx + 1;
            $res .= "{$num}. **{$it['nama']}** ({$it['jumlah']} unit) — *{$it['kode_inventaris']}*\n" .
                    "   Lokasi: Ruang {$it['nama_ruangan']} ({$it['nama_gedung']})\n";
        }

        $res .= "\n*Rekomendasi:* Segera koordinasikan dengan tim teknisi Sarpras untuk evaluasi perbaikan atau penggantian unit.";
        return $res;
    }

    /**
     * 2. Format jawaban fasilitas paling banyak mengalami kerusakan
     */
    private function formatMostDamaged(array $data): string {
        $items = $data['fasilitas'] ?? [];

        if (empty($items)) {
            return "Berdasarkan data sistem, saat ini belum tercatat adanya fasilitas dengan riwayat kerusakan berulang. Seluruh sarana terpantau dalam kondisi prima.";
        }

        $top = $items[0];
        $res = "Berdasarkan riwayat tiket pengaduan kerusakan dan catatan servis pemeliharaan di database:\n\n" .
               "Fasilitas yang **paling banyak mengalami kerusakan** adalah **{$top['nama']}** (Kode: `{$top['kode_inventaris']}`) " .
               "yang berlokasi di **{$top['nama_ruangan']} ({$top['nama_gedung']})** dengan akumulasi **{$top['total_kasus']} insiden** " .
               "({$top['total_laporan']} laporan kerusakan & {$top['total_perbaikan']} tindakan servis perbaikan).\n\n" .
               "Daftar fasilitas dengan frekuensi kerusakan tertinggi:\n";

        foreach ($items as $idx => $it) {
            $num = $idx + 1;
            $statusKondisi = ucfirst(str_replace('_', ' ', $it['kondisi']));
            $res .= "{$num}. **{$it['nama']}** — {$it['total_kasus']} Kasus ({$it['total_laporan']} Laporan, {$it['total_perbaikan']} Servis)\n" .
                    "   • Lokasi: {$it['nama_ruangan']} ({$it['nama_gedung']})\n" .
                    "   • Kondisi saat ini: **{$statusKondisi}**\n";
        }

        $res .= "\n*Catatan Analitik:* Fasilitas di atas disarankan untuk dilakukan audit menyeluruh guna mengevaluasi apakah memerlukan penggantian komponen utama atau peremajaan unit baru.";
        return $res;
    }

    /**
     * 3. Format fasilitas di gedung tertentu
     */
    private function formatFacilitiesByBuilding(array $data, string $entity): string {
        if (empty($data['found'])) {
            $res = "Gedung dengan kata kunci **'{$entity}'** tidak ditemukan secara spesifik.\n\n" .
                   "Berikut ringkasan fasilitas pada gedung-gedung yang terdata di STMIK IKMI Cirebon:\n";
            foreach ($data['daftar_semua_gedung'] ?? [] as $g) {
                $res .= "• **{$g['nama_gedung']} ({$g['kode_gedung']})**: {$g['total_ruangan']} Ruangan, {$g['total_item']} Jenis Fasilitas ({$g['total_unit']} Unit)\n";
            }
            return $res;
        }

        $g = $data['gedung'];
        $s = $data['stats'];
        $ruangans = $data['ruangan'] ?? [];

        $res = "Berdasarkan data inventaris di **{$g['nama_gedung']} ({$g['kode_gedung']})**:\n\n" .
               "• **Total Fasilitas:** **{$s['total_item']} jenis inventaris** (akumulasi **{$s['total_unit']} unit**)\n" .
               "• **Kondisi Fisik:**\n" .
               "   - Kondisi Baik: **{$s['item_baik']} item**\n" .
               "   - Rusak Ringan: **{$s['item_rusak_ringan']} item**\n" .
               "   - Rusak Berat: **{$s['item_rusak_berat']} item**\n" .
               "   - Tidak Layak: **{$s['item_tidak_layak']} item**\n" .
               "• **Jumlah Ruangan:** {$s['total_ruangan']} ruangan\n\n" .
               "Sebaran fasilitas per ruangan di gedung ini:\n";

        foreach ($ruangans as $idx => $r) {
            $num = $idx + 1;
            $res .= "{$num}. **{$r['nama_ruangan']}** (Lantai {$r['lantai']}): {$r['total_item']} jenis ({$r['total_unit']} unit)\n";
        }

        return $res;
    }

    /**
     * 4. Format ruangan dengan fasilitas paling lengkap
     */
    private function formatRoomMostFacilities(array $data): string {
        $rooms = $data['ruangan'] ?? [];

        if (empty($rooms)) {
            return "Belum ada data inventaris ruangan yang terdaftar di sistem.";
        }

        $top = $rooms[0];
        $res = "Berdasarkan jumlah inventaris yang tercatat di database kampus STMIK IKMI Cirebon:\n\n" .
               "Ruangan yang memiliki **fasilitas paling lengkap dan terbanyak** adalah **{$top['nama_ruangan']}** " .
               "di **{$top['nama_gedung']} (Lantai {$top['lantai']})** dengan kelengkapan:\n" .
               "• **{$top['total_unit_fasilitas']} unit sarpras** dari **{$top['total_jenis_fasilitas']} jenis fasilitas**\n" .
               "• Kondisi prima: {$top['fasilitas_baik']} jenis siap pakai\n\n" .
               "Berikut peringkat ruangan dengan fasilitas terlengkap:\n";

        foreach ($rooms as $idx => $r) {
            $num = $idx + 1;
            $res .= "{$num}. **{$r['nama_ruangan']}** ({$r['nama_gedung']})\n" .
                    "   • Total: **{$r['total_unit_fasilitas']} Unit** ({$r['total_jenis_fasilitas']} Jenis Aset)\n" .
                    "   • Jenis Ruang: {$r['jenis_ruangan']} | Lantai: {$r['lantai']}\n";
        }

        return $res;
    }

    /**
     * 5. Format fasilitas prioritas perbaikan
     */
    private function formatPriorityRepair(array $data): string {
        $items = $data['fasilitas'] ?? [];

        if (empty($items)) {
            return "Kabar baik! Berdasarkan audit database, saat ini **tidak ada fasilitas berkondisi rusak berat atau tiket darurat yang mendesak**. Seluruh sarana prasarana terpantau dalam kelaikan normal.";
        }

        $res = "Berdasarkan analisis tingkat kerusakan dan status laporan kerusakan civitas, berikut fasilitas yang **perlu diprioritaskan untuk diperbaiki**:\n\n";

        foreach ($items as $idx => $it) {
            $num = $idx + 1;
            $statusKondisi = strtoupper(str_replace('_', ' ', $it['kondisi']));
            $tiket = $it['tiket_aktif'] > 0 ? " ({$it['tiket_aktif']} Tiket Aduan Aktif)" : "";
            
            $res .= "{$num}. **{$it['nama']}** [{$statusKondisi}]{$tiket}\n" .
                    "   • Kode: `{$it['kode_inventaris']}` ({$it['jumlah']} unit)\n" .
                    "   • Lokasi: **{$it['nama_ruangan']}** ({$it['nama_gedung']})\n";
        }

        $res .= "\n*Tindakan yang direkomendasikan:* Petugas Sarpras disarankan segera menerbitkan Surat Perintah Kerja (SPK) pemeliharaan untuk menindaklanjuti fasilitas dengan prioritas teratas.";
        return $res;
    }

    /**
     * 6. Format kondisi fasilitas di ruangan tertentu
     */
    private function formatFacilitiesInRoom(array $data, string $entity): string {
        if (empty($data['found'])) {
            $res = "Ruangan dengan nama atau kata kunci **'{$entity}'** tidak ditemukan secara presisi.\n\n" .
                   "Berikut beberapa ruangan yang tersedia di database:\n";
            foreach ($data['daftar_ruangan_tersedia'] ?? [] as $r) {
                $res .= "• **{$r['nama_ruangan']}** ({$r['nama_gedung']} - Kode: `{$r['kode_ruangan']}`)\n";
            }
            $res .= "\nSilakan coba lagi dengan menyebutkan nama ruangan di atas.";
            return $res;
        }

        $r = $data['ruangan'];
        $s = $data['stats'];
        $items = $data['fasilitas'] ?? [];

        $res = "Kondisi fasilitas di **{$r['nama_ruangan']}** ({$r['nama_gedung']}, Lantai {$r['lantai']}):\n\n" .
               "• **Total Fasilitas:** {$s['total_item']} jenis ({$s['total_unit']} unit)\n" .
               "• **Ringkasan Kondisi:** Baik: **{$s['baik']}**, Rusak Ringan: **{$s['rusak_ringan']}**, Rusak Berat: **{$s['rusak_berat']}**\n\n" .
               "Daftar inventaris sarpras di ruangan ini:\n";

        foreach ($items as $idx => $it) {
            $num = $idx + 1;
            $kondisiBadge = ucfirst(str_replace('_', ' ', $it['kondisi']));
            $ket = $it['active_laporan'] > 0 ? " *(Ada laporan kerusakan aktif)*" : "";
            $res .= "{$num}. **{$it['nama']}** — {$it['jumlah']} unit [Kondisi: {$kondisiBadge}]{$ket}\n" .
                    "   • Kategori: {$it['nama_kategori']} | Kode: `{$it['kode_inventaris']}`\n";
        }

        return $res;
    }

    /**
     * 7. Format rekomendasi pemeliharaan
     */
    private function formatRecommendMaintenance(array $data): string {
        $items = $data['items'] ?? [];

        if (empty($items)) {
            return "Berdasarkan evaluasi sistem pemeliharaan berkala, seluruh fasilitas terpantau dalam jadwal servis yang terkendali. Tidak ada anomali perawatan yang mendesak.";
        }

        $res = "Berdasarkan audit usia pakai inventaris dan catatan keluhan sarana prasarana, berikut **rekomendasi tindakan pemeliharaan yang perlu dijadwalkan**:\n\n";

        foreach ($items as $idx => $it) {
            $num = $idx + 1;
            $kondisi = ucfirst(str_replace('_', ' ', $it['kondisi']));
            $servisTerakhir = !empty($it['pemeliharaan_terakhir']) ? date('d/m/Y', strtotime($it['pemeliharaan_terakhir'])) : 'Belum pernah diservis';
            
            $res .= "{$num}. **{$it['nama']}** ({$it['nama_ruangan']}, {$it['nama_gedung']})\n" .
                    "   • Kondisi: **{$kondisi}** | Servis Terakhir: {$servisTerakhir}\n" .
                    "   • Rekomendasi: Lakukan pemeriksaan menyeluruh dan pemeliharaan kuratif/preventif.\n";
        }

        return $res;
    }

    /**
     * 8. Format statistik kampus umum
     */
    private function formatCampusStats(array $data): string {
        $res = "Halo! Saya adalah **AI Assistant Sarpras STMIK IKMI Cirebon**.\n\n" .
               "Berikut ringkasan data sarana dan prasarana kampus terkini dari database:\n\n" .
               "• **Total Gedung:** {$data['total_gedung']} Gedung aktif\n" .
               "• **Total Ruangan:** {$data['total_ruangan']} Ruangan (Kelas, Lab, Kantor)\n" .
               "• **Total Fasilitas:** {$data['total_item_fasilitas']} Jenis Inventaris ({$data['total_unit_fasilitas']} Unit)\n" .
               "• **Kondisi Fisik:** Baik: **{$data['kondisi_baik']}**, Rusak Ringan: **{$data['rusak_ringan']}**, Rusak Berat: **{$data['rusak_berat']}**, Afkir: **{$data['tidak_layak']}**\n" .
               "• **Tiket Laporan:** Menunggu: {$data['laporan_menunggu']}, Diproses: {$data['laporan_diproses']}, Selesai: {$data['laporan_selesai']}\n\n" .
               "Anda dapat menanyakan hal-hal seperti:\n" .
               "1. *'Berapa jumlah fasilitas yang rusak berat?'*\n" .
               "2. *'Fasilitas apa yang paling banyak mengalami kerusakan?'*\n" .
               "3. *'Berapa jumlah fasilitas di Gedung A?'*\n" .
               "4. *'Ruangan mana yang memiliki fasilitas paling lengkap?'*\n" .
               "5. *'Fasilitas apa yang perlu diprioritaskan untuk diperbaiki?'*\n" .
               "6. *'Tampilkan kondisi fasilitas di Laboratorium Komputer 1.'*";

        return $res;
    }
}
