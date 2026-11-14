<?php
/**
 * WhatsAppTemplates — Template pesan WhatsApp (teks biasa)
 * Sistem Informasi Fasilitas STMIK IKMI Cirebon
 *
 * Seluruh data dari parameter. Tidak ada data hardcode atau fiktif.
 */

class WhatsAppTemplates {

    /**
     * Untuk pelapor: konfirmasi laporan berhasil dibuat
     */
    public static function laporanBaru(array $laporan, string $linkDetail = ''): string {
        $kode      = $laporan['kode_laporan'] ?? '-';
        $fasilitas = $laporan['nama_fasilitas'] ?? '-';
        $ruangan   = $laporan['nama_ruangan'] ?? '-';
        $prioritas = ucfirst($laporan['prioritas'] ?? '-');
        $deskripsi = mb_substr($laporan['deskripsi'] ?? '-', 0, 200);
        $tgl       = !empty($laporan['created_at']) ? date('d/m/Y H:i', strtotime($laporan['created_at'])) : date('d/m/Y H:i');

        $msg = "✅ *Laporan Kerusakan Diterima*\n";
        $msg .= "Sistem Informasi Fasilitas STMIK IKMI Cirebon\n\n";
        $msg .= "📋 *Nomor Tiket:* {$kode}\n";
        $msg .= "🔧 *Fasilitas:* {$fasilitas}\n";
        $msg .= "📍 *Ruangan:* {$ruangan}\n";
        $msg .= "⚡ *Prioritas:* {$prioritas}\n";
        $msg .= "📅 *Tanggal:* {$tgl}\n";
        $msg .= "📝 *Deskripsi:* {$deskripsi}\n\n";
        $msg .= "Laporan Anda telah diterima. Petugas Sarpras akan segera menindaklanjuti.";
        if ($linkDetail) {
            $msg .= "\n\n🔗 Detail: {$linkDetail}";
        }
        return $msg;
    }

    /**
     * Untuk Petugas Sarpras: notifikasi laporan baru masuk
     */
    public static function laporanBaruPetugas(array $laporan, string $linkDetail = ''): string {
        $kode      = $laporan['kode_laporan'] ?? '-';
        $pelapor   = $laporan['pelapor'] ?? '-';
        $fasilitas = $laporan['nama_fasilitas'] ?? '-';
        $ruangan   = $laporan['nama_ruangan'] ?? '-';
        $gedung    = $laporan['nama_gedung'] ?? '-';
        $prioritas = strtoupper($laporan['prioritas'] ?? '-');
        $deskripsi = mb_substr($laporan['deskripsi'] ?? '-', 0, 300);
        $tgl       = !empty($laporan['created_at']) ? date('d/m/Y H:i', strtotime($laporan['created_at'])) : date('d/m/Y H:i');

        $prioritasEmoji = match(strtolower($laporan['prioritas'] ?? '')) {
            'darurat' => '🚨',
            'tinggi'  => '⚠️',
            'sedang'  => '🔵',
            default   => '🟢',
        };

        $msg = "🔔 *LAPORAN KERUSAKAN BARU*\n";
        $msg .= "Sistem Informasi Fasilitas STMIK IKMI Cirebon\n\n";
        $msg .= "📋 *Nomor Tiket:* {$kode}\n";
        $msg .= "👤 *Pelapor:* {$pelapor}\n";
        $msg .= "🔧 *Fasilitas:* {$fasilitas}\n";
        $msg .= "📍 *Ruangan:* {$ruangan} — {$gedung}\n";
        $msg .= "{$prioritasEmoji} *Prioritas:* {$prioritas}\n";
        $msg .= "📅 *Tanggal:* {$tgl}\n";
        $msg .= "📝 *Deskripsi:*\n{$deskripsi}\n\n";
        $msg .= "Silakan segera tangani laporan ini melalui sistem.";
        if ($linkDetail) {
            $msg .= "\n\n🔗 Detail: {$linkDetail}";
        }
        return $msg;
    }

    /**
     * Untuk pelapor: perubahan status laporan
     */
    public static function statusUpdate(array $laporan, string $statusBaru, string $catatanPetugas = '', string $linkDetail = ''): string {
        $statusLabels = [
            'menunggu'     => 'Menunggu Verifikasi',
            'diverifikasi' => 'Telah Diverifikasi ✔️',
            'diproses'     => 'Sedang Diproses 🔧',
            'selesai'      => 'Selesai Diperbaiki ✅',
            'ditolak'      => 'Ditolak ❌',
        ];

        $kode      = $laporan['kode_laporan'] ?? '-';
        $fasilitas = $laporan['nama_fasilitas'] ?? '-';
        $ruangan   = $laporan['nama_ruangan'] ?? '-';
        $label     = $statusLabels[$statusBaru] ?? ucfirst($statusBaru);
        $tgl       = date('d/m/Y H:i');

        $msg = "📬 *Update Status Laporan*\n";
        $msg .= "Sistem Informasi Fasilitas STMIK IKMI Cirebon\n\n";
        $msg .= "📋 *Nomor Tiket:* {$kode}\n";
        $msg .= "🔧 *Fasilitas:* {$fasilitas}\n";
        $msg .= "📍 *Ruangan:* {$ruangan}\n";
        $msg .= "📊 *Status Terbaru:* {$label}\n";
        $msg .= "🕐 *Diperbarui:* {$tgl}";

        if (!empty($catatanPetugas)) {
            $msg .= "\n💬 *Catatan Petugas:*\n" . mb_substr($catatanPetugas, 0, 300);
        }

        if ($linkDetail) {
            $msg .= "\n\n🔗 Detail: {$linkDetail}";
        }

        return $msg;
    }

    /**
     * Untuk pelapor: laporan selesai
     */
    public static function laporanSelesai(array $laporan, string $catatanPetugas = '', string $linkDetail = ''): string {
        return self::statusUpdate($laporan, 'selesai', $catatanPetugas, $linkDetail);
    }
}

