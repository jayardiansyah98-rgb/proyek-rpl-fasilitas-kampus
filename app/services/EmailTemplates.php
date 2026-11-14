<?php
/**
 * EmailTemplates — Template HTML Email Profesional
 * Sistem Informasi Fasilitas STMIK IKMI Cirebon
 *
 * Menghasilkan HTML email responsif untuk setiap jenis peristiwa.
 * Data seluruhnya berasal dari parameter, tidak ada data hardcode.
 */

class EmailTemplates {

    /**
     * Wrapper layout email HTML responsif dengan identitas IKMI
     */
    private static function wrap(string $content, string $preheader = ''): string {
        $appName  = defined('APP_NAME') ? APP_NAME : 'Sistem Fasilitas STMIK IKMI Cirebon';
        $baseUrl  = defined('BASE_URL') ? BASE_URL : '#';
        $year     = date('Y');

        return <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{$appName}</title>
    <!--[if mso]><noscript><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml></noscript><![endif]-->
    <style>
        body { margin:0; padding:0; background:#f4f7fb; font-family: 'Segoe UI', Arial, sans-serif; }
        .wrapper { width:100%; background:#f4f7fb; padding:32px 16px; }
        .card { max-width:600px; margin:0 auto; background:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg,#1a3a5c,#2563eb); padding:28px 32px; text-align:center; }
        .header h1 { color:#ffffff; font-size:20px; margin:8px 0 0; font-weight:600; }
        .header .brand { color:#93c5fd; font-size:12px; text-transform:uppercase; letter-spacing:1px; }
        .body { padding:32px; color:#374151; font-size:15px; line-height:1.7; }
        .body h2 { color:#1a3a5c; font-size:18px; margin:0 0 16px; }
        .info-box { background:#f0f6ff; border-left:4px solid #2563eb; border-radius:6px; padding:16px 20px; margin:20px 0; }
        .info-box table { width:100%; border-collapse:collapse; }
        .info-box td { padding:5px 0; font-size:14px; }
        .info-box td:first-child { color:#6b7280; width:40%; font-weight:500; }
        .info-box td:last-child { color:#111827; font-weight:600; }
        .status-badge { display:inline-block; padding:5px 14px; border-radius:20px; font-size:13px; font-weight:600; }
        .status-menunggu { background:#fef9c3; color:#92400e; }
        .status-diverifikasi { background:#dbeafe; color:#1e40af; }
        .status-diproses { background:#ede9fe; color:#5b21b6; }
        .status-selesai { background:#dcfce7; color:#166534; }
        .status-ditolak { background:#fee2e2; color:#991b1b; }
        .btn { display:inline-block; padding:12px 28px; background:#2563eb; color:#ffffff; text-decoration:none; border-radius:8px; font-size:14px; font-weight:600; margin:20px 0; }
        .divider { border:none; border-top:1px solid #e5e7eb; margin:24px 0; }
        .footer { background:#f8fafc; padding:20px 32px; text-align:center; color:#9ca3af; font-size:12px; border-top:1px solid #e5e7eb; }
        .footer a { color:#6b7280; }
        @media (max-width:600px) {
            .body { padding:20px; }
            .info-box td:first-child { width:35%; }
        }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="card">
        <div class="header">
            <div class="brand">STMIK IKMI Cirebon</div>
            <h1>🏫 {$appName}</h1>
        </div>
        <div class="body">
            {$content}
        </div>
        <div class="footer">
            <p>Email ini dikirim otomatis oleh sistem. Jangan balas email ini.</p>
            <p>&copy; {$year} STMIK IKMI Cirebon — <a href="{$baseUrl}">{$appName}</a></p>
        </div>
    </div>
</div>
</body>
</html>
HTML;
    }

    /**
     * Template: Konfirmasi laporan kerusakan berhasil dikirim (untuk pelapor)
     */
    public static function laporanBaru(array $laporan, string $linkDetail = ''): array {
        $kode     = htmlspecialchars($laporan['kode_laporan'] ?? '-');
        $pelapor  = htmlspecialchars($laporan['pelapor'] ?? '-');
        $fasilitas= htmlspecialchars($laporan['nama_fasilitas'] ?? '-');
        $ruangan  = htmlspecialchars($laporan['nama_ruangan'] ?? '-');
        $prioritas= htmlspecialchars(ucfirst($laporan['prioritas'] ?? '-'));
        $tgl      = !empty($laporan['created_at']) ? date('d F Y, H:i', strtotime($laporan['created_at'])) : '-';
        $deskripsi= htmlspecialchars(mb_substr($laporan['deskripsi'] ?? '-', 0, 300));

        $btnLink  = $linkDetail
            ? "<a href=\"" . htmlspecialchars($linkDetail) . "\" class=\"btn\">🔍 Lihat Detail Laporan</a>"
            : '';

        $html = self::wrap("
            <h2>✅ Laporan Kerusakan Berhasil Dikirim</h2>
            <p>Halo, <strong>{$pelapor}</strong>!</p>
            <p>Laporan kerusakan fasilitas Anda telah berhasil diterima oleh sistem. Petugas Sarpras akan segera menindaklanjuti.</p>

            <div class=\"info-box\">
                <table>
                    <tr><td>Nomor Tiket</td><td><strong>{$kode}</strong></td></tr>
                    <tr><td>Fasilitas</td><td>{$fasilitas}</td></tr>
                    <tr><td>Ruangan</td><td>{$ruangan}</td></tr>
                    <tr><td>Prioritas</td><td>{$prioritas}</td></tr>
                    <tr><td>Tanggal Lapor</td><td>{$tgl}</td></tr>
                    <tr><td>Deskripsi</td><td>{$deskripsi}</td></tr>
                </table>
            </div>

            {$btnLink}

            <hr class=\"divider\">
            <p style=\"font-size:13px;color:#6b7280;\">Simpan nomor tiket ini untuk memantau perkembangan penanganan. Anda akan mendapat pemberitahuan ketika status laporan berubah.</p>
        ");

        $subject = "[{$kode}] Laporan Kerusakan Berhasil Dikirim";
        $text    = "Halo {$pelapor},\n\nLaporan kerusakan Anda berhasil dikirim.\nNomor Tiket: {$kode}\nFasilitas: {$fasilitas}\nRuangan: {$ruangan}\nPrioritas: {$prioritas}\nTanggal: {$tgl}\nDeskripsi: {$deskripsi}\n\nPetugas Sarpras akan segera menindaklanjuti.";

        return compact('subject', 'html', 'text');
    }

    /**
     * Template: Notifikasi laporan baru untuk Petugas Sarpras / Admin
     */
    public static function laporanBaruPetugas(array $laporan, string $linkDetail = ''): array {
        $kode     = htmlspecialchars($laporan['kode_laporan'] ?? '-');
        $pelapor  = htmlspecialchars($laporan['pelapor'] ?? '-');
        $fasilitas= htmlspecialchars($laporan['nama_fasilitas'] ?? '-');
        $ruangan  = htmlspecialchars($laporan['nama_ruangan'] ?? '-');
        $gedung   = htmlspecialchars($laporan['nama_gedung'] ?? '-');
        $prioritas= htmlspecialchars(ucfirst($laporan['prioritas'] ?? '-'));
        $tgl      = !empty($laporan['created_at']) ? date('d F Y, H:i', strtotime($laporan['created_at'])) : '-';
        $deskripsi= htmlspecialchars(mb_substr($laporan['deskripsi'] ?? '-', 0, 500));

        $prioritasBadge = match($laporan['prioritas'] ?? '') {
            'darurat' => '<span style="background:#fee2e2;color:#991b1b;padding:3px 10px;border-radius:12px;font-size:12px;font-weight:700;">🚨 DARURAT</span>',
            'tinggi'  => '<span style="background:#fef3c7;color:#92400e;padding:3px 10px;border-radius:12px;font-size:12px;font-weight:700;">⚠️ TINGGI</span>',
            'sedang'  => '<span style="background:#dbeafe;color:#1e40af;padding:3px 10px;border-radius:12px;font-size:12px;font-weight:700;">🔵 SEDANG</span>',
            default   => '<span style="background:#dcfce7;color:#166534;padding:3px 10px;border-radius:12px;font-size:12px;font-weight:700;">🟢 RENDAH</span>',
        };

        $btnLink = $linkDetail
            ? "<a href=\"" . htmlspecialchars($linkDetail) . "\" class=\"btn\">🔧 Tangani Laporan</a>"
            : '';

        $html = self::wrap("
            <h2>🔔 Laporan Kerusakan Baru Masuk</h2>
            <p>Terdapat laporan kerusakan fasilitas baru yang memerlukan penanganan Anda.</p>

            <div class=\"info-box\">
                <table>
                    <tr><td>Nomor Tiket</td><td><strong>{$kode}</strong></td></tr>
                    <tr><td>Pelapor</td><td>{$pelapor}</td></tr>
                    <tr><td>Fasilitas</td><td>{$fasilitas}</td></tr>
                    <tr><td>Ruangan</td><td>{$ruangan}</td></tr>
                    <tr><td>Gedung</td><td>{$gedung}</td></tr>
                    <tr><td>Prioritas</td><td>{$prioritasBadge}</td></tr>
                    <tr><td>Tanggal</td><td>{$tgl}</td></tr>
                    <tr><td>Deskripsi</td><td>{$deskripsi}</td></tr>
                </table>
            </div>

            {$btnLink}
        ");

        $subject = "[{$kode}] Laporan Kerusakan Baru — Prioritas {$prioritas}";
        $text    = "Laporan Kerusakan Baru\nNomor: {$kode}\nPelapor: {$pelapor}\nFasilitas: {$fasilitas}\nRuangan: {$ruangan}/{$gedung}\nPrioritas: {$prioritas}\nTanggal: {$tgl}\nDeskripsi: {$deskripsi}";

        return compact('subject', 'html', 'text');
    }

    /**
     * Template: Notifikasi perubahan status laporan (untuk pelapor)
     */
    public static function statusUpdate(array $laporan, string $statusBaru, string $catatanPetugas = '', string $linkDetail = ''): array {
        $statusLabels = [
            'menunggu'     => 'Menunggu Verifikasi',
            'diverifikasi' => 'Telah Diverifikasi',
            'diproses'     => 'Sedang Diproses',
            'selesai'      => 'Selesai Diperbaiki',
            'ditolak'      => 'Ditolak',
        ];

        $kode     = htmlspecialchars($laporan['kode_laporan'] ?? '-');
        $pelapor  = htmlspecialchars($laporan['pelapor'] ?? '-');
        $fasilitas= htmlspecialchars($laporan['nama_fasilitas'] ?? '-');
        $ruangan  = htmlspecialchars($laporan['nama_ruangan'] ?? '-');
        $label    = htmlspecialchars($statusLabels[$statusBaru] ?? ucfirst($statusBaru));
        $catatan  = $catatanPetugas ? htmlspecialchars($catatanPetugas) : '';
        $tgl      = date('d F Y, H:i');

        $badgeClass = 'status-' . htmlspecialchars($statusBaru);
        $statusBadge = "<span class=\"status-badge {$badgeClass}\">{$label}</span>";

        $catatanHtml = $catatan
            ? "<p><strong>Catatan Petugas:</strong><br><em style=\"color:#374151;\">{$catatan}</em></p>"
            : '';

        $btnLink = $linkDetail
            ? "<a href=\"" . htmlspecialchars($linkDetail) . "\" class=\"btn\">📋 Lihat Detail Tiket</a>"
            : '';

        $html = self::wrap("
            <h2>📬 Status Laporan Diperbarui</h2>
            <p>Halo, <strong>{$pelapor}</strong>!</p>
            <p>Status laporan kerusakan Anda telah diperbarui oleh petugas.</p>

            <div class=\"info-box\">
                <table>
                    <tr><td>Nomor Tiket</td><td><strong>{$kode}</strong></td></tr>
                    <tr><td>Fasilitas</td><td>{$fasilitas}</td></tr>
                    <tr><td>Ruangan</td><td>{$ruangan}</td></tr>
                    <tr><td>Status Baru</td><td>{$statusBadge}</td></tr>
                    <tr><td>Diperbarui</td><td>{$tgl}</td></tr>
                </table>
            </div>

            {$catatanHtml}
            {$btnLink}
        ");

        $subject = "[{$kode}] Status Laporan: {$label}";
        $text    = "Halo {$pelapor},\n\nStatus laporan Anda ({$kode}) diperbarui menjadi: {$label}\nFasilitas: {$fasilitas}\nRuangan: {$ruangan}\nTanggal: {$tgl}" . ($catatan ? "\nCatatan: {$catatan}" : '');

        return compact('subject', 'html', 'text');
    }

    /**
     * Template: Laporan selesai (untuk pelapor)
     */
    public static function laporanSelesai(array $laporan, string $catatanPetugas = '', string $linkDetail = ''): array {
        return self::statusUpdate($laporan, 'selesai', $catatanPetugas, $linkDetail);
    }
}

