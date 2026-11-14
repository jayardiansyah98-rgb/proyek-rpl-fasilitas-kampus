<?php
/**
 * IntentResolver
 * Menganalisis pertanyaan bahasa natural pengguna, mengekstrak entitas penting
 * (kondisi, nama gedung, nama ruangan), dan memetakan ke fungsi query database yang diizinkan.
 */

class IntentResolver {

    /**
     * Selesaikan intent dan ekstrak parameter dari pertanyaan
     * 
     * @param string $question
     * @return array [intent, handler_method, params, description]
     */
    public function resolve(string $question): array {
        $q = strtolower(trim($question));

        // 1. Cek Intent: Kondisi Spesifik (Rusak Berat, Rusak Ringan, Tidak Layak, Baik)
        if (preg_match('/(rusak\s*berat|kondisi\s*rusak\s*berat|patah|ancur)/i', $q)) {
            return [
                'intent' => 'COUNT_CONDITION',
                'method' => 'countFacilitiesByCondition',
                'params' => ['rusak_berat'],
                'entity' => 'rusak_berat',
                'description' => 'Menghitung fasilitas dengan kondisi Rusak Berat'
            ];
        }

        if (preg_match('/(rusak\s*ringan|agak\s*rusak|servis\s*ringan|redup)/i', $q)) {
            return [
                'intent' => 'COUNT_CONDITION',
                'method' => 'countFacilitiesByCondition',
                'params' => ['rusak_ringan'],
                'entity' => 'rusak_ringan',
                'description' => 'Menghitung fasilitas dengan kondisi Rusak Ringan'
            ];
        }

        if (preg_match('/(tidak\s*layak|afkir|kadaluwarsa|hancur|buang)/i', $q)) {
            return [
                'intent' => 'COUNT_CONDITION',
                'method' => 'countFacilitiesByCondition',
                'params' => ['tidak_layak'],
                'entity' => 'tidak_layak',
                'description' => 'Menghitung fasilitas dengan kondisi Tidak Layak Pakai'
            ];
        }

        if (preg_match('/(kondisi\s*baik|fasilitas\s*baik|layak\s*pakai|berfungsi\s*normal|siap\s*pakai)/i', $q) && !preg_match('/(semua|gedung|ruangan)/i', $q)) {
            return [
                'intent' => 'COUNT_CONDITION',
                'method' => 'countFacilitiesByCondition',
                'params' => ['baik'],
                'entity' => 'baik',
                'description' => 'Menghitung fasilitas dengan kondisi Baik'
            ];
        }

        // 2. Cek Intent: Fasilitas yang Sering / Paling Banyak Rusak
        if (preg_match('/(sering\s*rusak|paling\s*(banyak|sering)\s*(mengalami\s*)?kerusakan|kerusakan\s*terbanyak|langganan\s*rusak|rekor\s*rusak)/i', $q)) {
            return [
                'intent' => 'MOST_DAMAGED',
                'method' => 'getMostDamagedFacilities',
                'params' => [5],
                'entity' => null,
                'description' => 'Mencari fasilitas dengan frekuensi kerusakan terbanyak'
            ];
        }

        // 3. Cek Intent: Ruangan dengan Fasilitas Paling Lengkap / Terbanyak
        if (preg_match('/(ruangan|ruang|lab).*(paling\s*lengkap|terbanyak|paling\s*banyak|terlengkap)/i', $q) ||
            preg_match('/(paling\s*lengkap|terlengkap).*(fasilitas|inventaris)/i', $q)) {
            return [
                'intent' => 'ROOM_MOST_FACILITIES',
                'method' => 'getRoomWithMostFacilities',
                'params' => [5],
                'entity' => null,
                'description' => 'Mencari ruangan dengan inventaris fasilitas terlengkap'
            ];
        }

        // 4. Cek Intent: Prioritas Perbaikan
        if (preg_match('/(prioritas|prioritaskan|urgensi|darurat|harus\s*segera|perlu\s*diperbaiki|diservis\s*duluan)/i', $q)) {
            return [
                'intent' => 'PRIORITY_REPAIR',
                'method' => 'getPriorityRepairFacilities',
                'params' => [6],
                'entity' => null,
                'description' => 'Mencari fasilitas yang perlu diprioritaskan untuk diperbaiki'
            ];
        }

        // 5. Cek Intent: Fasilitas di Gedung Tertentu
        if (preg_match('/(gedung\s*([a-z0-9\-]+)|rektorat|akademik|ict|laboratorium\s*terpadu)/i', $q, $matches)) {
            $buildingName = $matches[0];
            // Normalisasi ekstraksi nama gedung
            if (stripos($q, 'gedung a') !== false || stripos($q, 'gd-a') !== false) {
                $buildingName = 'GD-A';
            } elseif (stripos($q, 'gedung b') !== false || stripos($q, 'gd-b') !== false) {
                $buildingName = 'GD-B';
            } elseif (stripos($q, 'rektorat') !== false) {
                $buildingName = 'Rektorat';
            } elseif (stripos($q, 'ict') !== false) {
                $buildingName = 'ICT';
            }

            return [
                'intent' => 'FACILITIES_BY_BUILDING',
                'method' => 'getFacilitiesByBuilding',
                'params' => [$buildingName],
                'entity' => $buildingName,
                'description' => "Menghitung fasilitas di $buildingName"
            ];
        }

        // 5. Cek Intent: Ruangan yang Sedang Diperbaiki / Dalam Pemeliharaan
        if (preg_match('/(ruang|ruangan|lab).*(perbaikan|diperbaiki|pemeliharaan)/i', $q) ||
            preg_match('/(perbaikan|diperbaiki|pemeliharaan).*(ruang|ruangan|lab)/i', $q)) {
            return [
                'intent' => 'COUNT_ROOM_REPAIR',
                'method' => 'getTotalFacilityStats',
                'params' => [],
                'entity' => null,
                'description' => 'Menghitung ruangan yang sedang diperbaiki'
            ];
        }

        // 6. Cek Intent: Fasilitas yang Tersedia (Penjelasan / Rincian Daftar)
        if (preg_match('/(daftar|jelaskan|sebutkan|rincian|list|apa saja).*(fasilitas|sarana|inventaris).*(tersedia)/i', $q) ||
            preg_match('/(fasilitas|sarana|inventaris).*(tersedia).*(daftar|jelaskan|sebutkan|rincian|list|apa saja)/i', $q) ||
            preg_match('/jelaskan fasilitas/i', $q) ||
            preg_match('/fasilitas yang tersedia/i', $q)) {
            return [
                'intent' => 'AVAILABLE_FACILITY_LIST',
                'method' => 'getAvailableFacilities',
                'params' => [],
                'entity' => null,
                'description' => 'Menampilkan rincian penjelasan fasilitas yang tersedia'
            ];
        }

        // 7. Cek Intent: Ruangan Tersedia (Rincian Daftar atau Hitung Jumlah)
        if (preg_match('/(daftar|jelaskan|sebutkan|rincian|list|apa saja).*(ruangan|ruang).*(tersedia)/i', $q) ||
            preg_match('/(ruangan|ruang).*(tersedia).*(daftar|jelaskan|sebutkan|rincian|list|apa saja)/i', $q) ||
            preg_match('/daftar ruangan yang tersedia/i', $q)) {
            return [
                'intent' => 'AVAILABLE_ROOM_LIST',
                'method' => 'getAvailableRooms',
                'params' => [],
                'entity' => null,
                'description' => 'Menampilkan rincian daftar ruangan yang tersedia'
            ];
        }

        if (preg_match('/(berapa|jumlah|total|ada berapa).*(ruangan|ruang).*(tersedia)/i', $q) ||
            preg_match('/(ruangan|ruang).*(tersedia)/i', $q)) {
            return [
                'intent' => 'COUNT_ROOM_AVAILABLE',
                'method' => 'getTotalFacilityStats',
                'params' => [],
                'entity' => null,
                'description' => 'Menghitung jumlah ruangan yang tersedia'
            ];
        }

        // 8. Cek Intent: Jenis Fasilitas
        if (preg_match('/(jenis fasilitas|macam fasilitas|jenis inventaris)/i', $q)) {
            return [
                'intent' => 'COUNT_FACILITY_TYPES',
                'method' => 'getTotalFacilityStats',
                'params' => [],
                'entity' => null,
                'description' => 'Menghitung total jenis fasilitas terdata'
            ];
        }

        // 9. Cek Intent: Total Fasilitas (Unit Fisik)
        if (preg_match('/(total fasilitas|jumlah fasilitas|unit fasilitas|fasilitas yang terdata|fasilitas di kampus|fasilitas kampus|berapa fasilitas|ada berapa fasilitas)/i', $q) && !preg_match('/(jenis|kategori|macam)/i', $q)) {
            return [
                'intent' => 'COUNT_FACILITY_UNITS',
                'method' => 'getTotalFacilityStats',
                'params' => [],
                'entity' => null,
                'description' => 'Menghitung total akumulasi unit fasilitas fisik'
            ];
        }

        // 10. Cek Intent: Jumlah Ruangan Kampus & Daftar Semua Ruangan
        if (preg_match('/(daftar|jelaskan|sebutkan|rincian|list).*(ruangan|ruang)/i', $q)) {
            return [
                'intent' => 'ALL_ROOM_LIST',
                'method' => 'getAllRooms',
                'params' => [],
                'entity' => null,
                'description' => 'Menampilkan daftar seluruh ruangan di kampus'
            ];
        }

        if (preg_match('/(jumlah ruangan|berapa ruangan|total ruangan|ada berapa ruangan|ruangan di kampus|ruangan yang terdata|ruangan kampus)/i', $q)) {
            return [
                'intent' => 'COUNT_ROOM_TOTAL',
                'method' => 'getTotalFacilityStats',
                'params' => [],
                'entity' => null,
                'description' => 'Menghitung total ruangan di kampus'
            ];
        }

        // 10. Cek Intent: Kondisi Fasilitas di Ruangan Tertentu (Spesifik)
        if (preg_match('/(laboratorium|lab|ruang\s*kuliah|ruang\s*dosen|ruangan|rk\-|lab\-)/i', $q) && preg_match('/(kondisi|fasilitas|apa saja|tampilkan|alat|inventaris)/i', $q)) {
            $roomQuery = $this->extractRoomQuery($q);
            return [
                'intent' => 'FACILITIES_IN_ROOM',
                'method' => 'getFacilitiesInRoom',
                'params' => [$roomQuery],
                'entity' => $roomQuery,
                'description' => "Menampilkan kondisi fasilitas di ruangan $roomQuery"
            ];
        }

        // 7. Cek Intent: Rekomendasi Pemeliharaan
        if (preg_match('/(rekomendasi|rekomendasikan|saran).*(pemeliharaan|perawatan|servis)/i', $q) ||
            preg_match('/(jadwal|rencana).*(pemeliharaan|servis)/i', $q)) {
            return [
                'intent' => 'RECOMMEND_MAINTENANCE',
                'method' => 'recommendMaintenance',
                'params' => [],
                'entity' => null,
                'description' => 'Menghasilkan rekomendasi pemeliharaan fasilitas'
            ];
        }

        // 8. Cek Intent: Total / Rekapitulasi Kampus
        if (preg_match('/(total|jumlah|semua|rekapitulasi|ringkasan).*(gedung|ruangan|fasilitas|aset|kampus)/i', $q) ||
            preg_match('/(berapa\s*banyak|berapa\s*total)/i', $q)) {
            return [
                'intent' => 'CAMPUS_STATS',
                'method' => 'getTotalFacilityStats',
                'params' => [],
                'entity' => null,
                'description' => 'Mengambil statistik dan ringkasan fasilitas kampus'
            ];
        }

        // 8b. Cek Intent: Identitas Model AI
        if (preg_match('/(model apa|pakai ai apa|menggunakan model|pakai model|deepseek|gemini|kamu siapa|siapa anda|teknologi ai|versi ai)/i', $q)) {
            return [
                'intent' => 'IDENTITY_AI_MODEL',
                'method' => 'getTotalFacilityStats',
                'params' => [],
                'entity' => null,
                'description' => 'Pertanyaan mengenai identitas asisten dan model AI yang digunakan'
            ];
        }

        // 9. Fallback Intent: Panduan & Pencarian Umum
        return [
            'intent' => 'GENERAL_QUERY',
            'method' => 'getTotalFacilityStats',
            'params' => [],
            'entity' => null,
            'description' => 'Pertanyaan umum / bantuan navigasi data sarpras'
        ];
    }

    /**
     * Ekstraksi nama ruangan dari input teks
     */
    private function extractRoomQuery(string $q): string {
        if (stripos($q, 'lab ai') !== false || stripos($q, 'data science') !== false) {
            return 'Laboratorium AI & Data Science';
        }
        if (stripos($q, 'lab net') !== false || stripos($q, 'jaringan') !== false || stripos($q, 'cyber') !== false) {
            return 'Laboratorium Jaringan & Cyber Security';
        }
        if (stripos($q, '201') !== false || stripos($q, 'teori') !== false) {
            return 'Ruang Kuliah Teori 201';
        }
        if (stripos($q, 'dosen') !== false) {
            return 'Ruang Dosen & Konsultasi Akademik';
        }
        if (stripos($q, 'komputer 1') !== false || stripos($q, 'komputer') !== false) {
            // Mapping cerdas untuk "Laboratorium Komputer 1" ke Lab AI / Komputer kampus
            return 'Laboratorium AI & Data Science';
        }

        // Bersihkan stopwords untuk pencarian
        $cleaned = preg_replace('/(tampilkan|bagaimana|kondisi|fasilitas|di|ada|apa|saja|pada|ruangan|ruang)/i', '', $q);
        return trim($cleaned) ?: 'Laboratorium';
    }
}
