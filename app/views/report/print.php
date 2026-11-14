<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($report_title) ?> - STMIK IKMI Cirebon</title>

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', Arial, sans-serif;
            color: #1e293b;
            background-color: #f8fafc;
            font-size: 12px;
        }

        .paper-sheet {
            background-color: #fff;
            max-width: 1050px;
            margin: 20px auto;
            padding: 40px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            border-radius: 8px;
        }

        .kop-surat {
            border-bottom: 3px double #0f172a;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .table-print {
            font-size: 11px;
            width: 100%;
        }

        .table-print th {
            background-color: #f1f5f9 !important;
            border: 1px solid #cbd5e1 !important;
            font-weight: 600;
            padding: 8px;
        }

        .table-print td {
            border: 1px solid #e2e8f0;
            padding: 7px 8px;
            vertical-align: middle;
        }

        .ttd-box {
            margin-top: 40px;
            page-break-inside: avoid;
        }

        @media print {
            body {
                background-color: #fff !important;
            }
            .paper-sheet {
                box-shadow: none !important;
                margin: 0 !important;
                padding: 0 !important;
                max-width: 100% !important;
            }
            .no-print {
                display: none !important;
            }
            @page {
                size: A4 landscape;
                margin: 1.5cm;
            }
        }
    </style>
</head>
<body>

    <!-- Floating Action Toolbar (Screen Only) -->
    <div class="no-print bg-dark text-white py-2 px-3 sticky-top shadow-sm d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-file-earmark-pdf fs-5 text-warning"></i>
            <span class="small fw-semibold">Pratinjau Cetak / Ekspor PDF Resmi STMIK IKMI</span>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3" onclick="window.close()">
                <i class="bi bi-x-lg me-1"></i> Tutup
            </button>
            <button type="button" class="btn btn-sm btn-warning rounded-pill px-4 fw-semibold" onclick="window.print()">
                <i class="bi bi-printer-fill me-1"></i> Cetak / Simpan sebagai PDF
            </button>
        </div>
    </div>

    <!-- Lembar Dokumen Kertas -->
    <div class="paper-sheet">
        <!-- KOP SURAT RESMI KAMPUS -->
        <div class="kop-surat d-flex align-items-center gap-4">
            <div class="rounded-3 bg-primary text-white d-flex align-items-center justify-content-center p-3" style="width: 75px; height: 75px;">
                <i class="bi bi-buildings-fill fs-1"></i>
            </div>
            <div class="flex-grow-1 text-center pe-5">
                <h6 class="text-secondary fw-bold mb-0 text-uppercase tracking-wider">Yayasan Pendidikan STMIK IKMI Cirebon</h6>
                <h4 class="fw-bold mb-0 text-primary">BIRO ADMINISTRASI SARANA & PRASARANA (SARPRAS)</h4>
                <p class="mb-0 text-muted small">
                    Kampus Utama: Jl. Perjuangan No. 10B, Karyamulya, Kec. Kesambi, Kota Cirebon, Jawa Barat 45131<br>
                    Website: www.ikmi.ac.id &bull; Surel: sarpras@ikmi.ac.id &bull; Telp: (0231) 490480
                </p>
            </div>
        </div>

        <!-- Judul & Keterangan Laporan -->
        <div class="text-center mb-4">
            <h5 class="fw-bold text-dark text-uppercase mb-1"><?= htmlspecialchars($report_title) ?></h5>
            <div class="text-muted small">
                Dicetak pada: <strong><?= date('d F Y, H:i') ?> WIB</strong> &bull;
                Oleh: <strong><?= htmlspecialchars($user['nama_lengkap']) ?></strong> (<?= ucfirst($user['nama_role']) ?>)
            </div>
            <?php if (!empty($selected_gedung_name) || !empty($selected_ruangan_name) || !empty($filters['kondisi']) || !empty($filters['tanggal_dari'])): ?>
                <div class="badge bg-light text-dark border px-3 py-1 mt-2 small">
                    Parameter Filter:
                    <?= !empty($selected_gedung_name) ? 'Gedung: ' . htmlspecialchars($selected_gedung_name) . ' | ' : '' ?>
                    <?= !empty($selected_ruangan_name) ? 'Ruangan: ' . htmlspecialchars($selected_ruangan_name) . ' | ' : '' ?>
                    <?= !empty($filters['kondisi']) ? 'Kondisi: ' . ucfirst(str_replace('_', ' ', $filters['kondisi'])) . ' | ' : '' ?>
                    <?= !empty($filters['tanggal_dari']) ? 'Periode: ' . date('d/m/Y', strtotime($filters['tanggal_dari'])) . ' s.d ' . date('d/m/Y', strtotime($filters['tanggal_sampai'] ?? date('Y-m-d'))) : '' ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- TABEL DATA CETAK -->
        <div class="table-responsive mb-4">
            <?php if ($type === 'gedung'): ?>
                <table class="table table-print mb-0">
                    <thead>
                        <tr>
                            <th style="width: 40px;" class="text-center">No</th>
                            <th>Kode Gedung</th>
                            <th>Nama Gedung</th>
                            <th>Lokasi & Lantai</th>
                            <th class="text-center">Jumlah Ruangan</th>
                            <th class="text-center">Total Unit Fisik</th>
                            <th class="text-center">Kondisi Baik</th>
                            <th class="text-center">Rusak Ringan</th>
                            <th class="text-center">Rusak Berat/Afkir</th>
                            <th class="text-center">Total Laporan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($report_data as $r): ?>
                        <tr>
                            <td class="text-center"><?= $no++ ?></td>
                            <td class="fw-bold font-monospace"><?= htmlspecialchars($r['kode_gedung']) ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($r['nama_gedung']) ?></td>
                            <td><?= htmlspecialchars($r['lokasi']) ?> (<?= $r['jumlah_lantai'] ?> Lt.)</td>
                            <td class="text-center"><?= $r['total_ruangan'] ?></td>
                            <td class="text-center fw-bold"><?= number_format($r['total_unit_fasilitas']) ?></td>
                            <td class="text-center text-success fw-bold"><?= $r['fasilitas_baik'] ?></td>
                            <td class="text-center text-warning fw-bold"><?= $r['fasilitas_rusak_ringan'] ?></td>
                            <td class="text-center text-danger fw-bold"><?= $r['fasilitas_rusak_berat'] + $r['fasilitas_tidak_layak'] ?></td>
                            <td class="text-center"><?= $r['total_tiket_laporan'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

            <?php elseif ($type === 'ruangan'): ?>
                <table class="table table-print mb-0">
                    <thead>
                        <tr>
                            <th style="width: 40px;" class="text-center">No</th>
                            <th>Kode</th>
                            <th>Nama Ruangan</th>
                            <th>Gedung & Lantai</th>
                            <th>Jenis / Kapasitas</th>
                            <th>Status Ketersediaan</th>
                            <th class="text-center">Total Unit</th>
                            <th class="text-center">Item Baik</th>
                            <th class="text-center">Item Rusak</th>
                            <th class="text-center">Laporan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($report_data as $r): ?>
                        <tr>
                            <td class="text-center"><?= $no++ ?></td>
                            <td class="fw-bold font-monospace"><?= htmlspecialchars($r['kode_ruangan']) ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($r['nama_ruangan']) ?></td>
                            <td><?= htmlspecialchars($r['nama_gedung']) ?>, Lt. <?= $r['lantai'] ?></td>
                            <td><?= htmlspecialchars($r['jenis_ruangan']) ?> (<?= $r['kapasitas'] ?> Kursi)</td>
                            <td><?= ucfirst($r['status_ruangan']) ?></td>
                            <td class="text-center fw-bold"><?= $r['total_unit_fasilitas'] ?></td>
                            <td class="text-center text-success"><?= $r['item_baik'] ?></td>
                            <td class="text-center text-danger"><?= $r['item_rusak'] ?></td>
                            <td class="text-center"><?= $r['total_laporan'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

            <?php elseif ($type === 'kerusakan'): ?>
                <table class="table table-print mb-0">
                    <thead>
                        <tr>
                            <th style="width: 40px;" class="text-center">No</th>
                            <th>Kode Tiket</th>
                            <th>Tgl Lapor</th>
                            <th>Pelapor</th>
                            <th>Fasilitas & Ruangan</th>
                            <th>Deskripsi Keluhan Kerusakan</th>
                            <th>Prioritas</th>
                            <th>Status</th>
                            <th>Catatan Petugas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($report_data as $r): ?>
                        <tr>
                            <td class="text-center"><?= $no++ ?></td>
                            <td class="fw-bold font-monospace"><?= htmlspecialchars($r['kode_laporan']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></td>
                            <td><?= htmlspecialchars($r['nama_pelapor']) ?> (<?= htmlspecialchars($r['nim_nip_pelapor']) ?>)</td>
                            <td>
                                <strong><?= htmlspecialchars($r['nama_fasilitas']) ?></strong><br>
                                <small class="text-muted"><?= htmlspecialchars($r['nama_ruangan']) ?></small>
                            </td>
                            <td><?= htmlspecialchars($r['deskripsi']) ?></td>
                            <td class="fw-bold"><?= ucfirst($r['prioritas']) ?></td>
                            <td class="fw-bold"><?= ucfirst($r['status']) ?></td>
                            <td><?= htmlspecialchars($r['catatan_petugas'] ?? '-') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

            <?php elseif ($type === 'pemeliharaan'): ?>
                <table class="table table-print mb-0">
                    <thead>
                        <tr>
                            <th style="width: 40px;" class="text-center">No</th>
                            <th>Kode Servis</th>
                            <th>Tanggal</th>
                            <th>Fasilitas / Ruangan</th>
                            <th>Teknisi Pelaksana</th>
                            <th>Jenis Tindakan</th>
                            <th>Kondisi Sebelum &rarr; Sesudah</th>
                            <th>Status</th>
                            <th class="text-end">Biaya Servis</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; $totalBiaya = 0; foreach ($report_data as $r): 
                            $tgl = !empty($r['tanggal']) ? $r['tanggal'] : $r['tanggal_mulai'];
                            $totalBiaya += $r['biaya'];
                        ?>
                        <tr>
                            <td class="text-center"><?= $no++ ?></td>
                            <td class="fw-bold font-monospace"><?= htmlspecialchars($r['kode_pemeliharaan']) ?></td>
                            <td><?= date('d/m/Y', strtotime($tgl)) ?></td>
                            <td>
                                <strong><?= htmlspecialchars($r['nama_fasilitas']) ?></strong><br>
                                <small class="text-muted"><?= htmlspecialchars($r['nama_ruangan']) ?></small>
                            </td>
                            <td><?= htmlspecialchars($r['nama_petugas']) ?></td>
                            <td><?= htmlspecialchars($r['jenis_tindakan']) ?></td>
                            <td>
                                <?= ucfirst(str_replace('_', ' ', $r['kondisi_sebelum'] ?? 'baik')) ?> &rarr;
                                <strong><?= ucfirst(str_replace('_', ' ', $r['kondisi_sesudah'] ?? 'baik')) ?></strong>
                            </td>
                            <td><?= ucfirst(str_replace('_', ' ', $r['status'])) ?></td>
                            <td class="text-end fw-bold">Rp <?= number_format($r['biaya'], 0, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="table-light fw-bold">
                            <td colspan="8" class="text-end pe-3">TOTAL ANGGARAN PEMELIHARAAN:</td>
                            <td class="text-end">Rp <?= number_format($totalBiaya, 0, ',', '.') ?></td>
                        </tr>
                    </tbody>
                </table>

            <?php else: ?>
                <!-- Seluruh Fasilitas / Berdasarkan Kondisi -->
                <table class="table table-print mb-0">
                    <thead>
                        <tr>
                            <th style="width: 40px;" class="text-center">No</th>
                            <th>Kode Inventaris</th>
                            <th>Nama Fasilitas</th>
                            <th>Kategori</th>
                            <th>Lokasi Penempatan</th>
                            <th class="text-center">Jumlah</th>
                            <th class="text-center">Kondisi Fisik</th>
                            <th class="text-center">Tahun</th>
                            <th class="text-center">Tiket Kerusakan</th>
                            <th class="text-center">Pemeliharaan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($report_data as $r): ?>
                        <tr>
                            <td class="text-center"><?= $no++ ?></td>
                            <td class="fw-bold font-monospace"><?= htmlspecialchars($r['kode_inventaris']) ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($r['nama_fasilitas']) ?></td>
                            <td><?= htmlspecialchars($r['nama_kategori']) ?></td>
                            <td><?= htmlspecialchars($r['nama_ruangan']) ?> (<?= htmlspecialchars($r['nama_gedung']) ?>)</td>
                            <td class="text-center fw-bold"><?= $r['jumlah'] ?> Unit</td>
                            <td class="text-center fw-semibold"><?= ucfirst(str_replace('_', ' ', $r['kondisi'])) ?></td>
                            <td class="text-center"><?= $r['tahun_pengadaan'] ?></td>
                            <td class="text-center"><?= $r['total_laporan'] ?? $r['active_laporan'] ?? 0 ?></td>
                            <td class="text-center"><?= $r['total_pemeliharaan'] ?? 0 ?> Kali</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- TANDA TANGAN DOKUMEN RESMI -->
        <div class="ttd-box row">
            <div class="col-6">
                <div class="text-center" style="width: 260px;">
                    <p class="mb-1 text-muted small">Mengetahui & Mengesahkan,</p>
                    <p class="fw-bold mb-5">Kepala Biro Sarana & Prasarana</p>
                    <div style="height: 45px;"></div>
                    <p class="fw-bold text-decoration-underline mb-0">Dr. Dadang Sudrajat, M.Kom.</p>
                    <small class="text-muted d-block">NIP: 198508122010011002</small>
                </div>
            </div>
            <div class="col-6 text-end">
                <div class="text-center ms-auto" style="width: 260px;">
                    <p class="mb-1 text-muted small">Cirebon, <?= date('d F Y') ?></p>
                    <p class="fw-bold mb-5">Petugas Pelaksana Sarpras</p>
                    <div style="height: 45px;"></div>
                    <p class="fw-bold text-decoration-underline mb-0"><?= htmlspecialchars($user['nama_lengkap']) ?></p>
                    <small class="text-muted d-block">NIP/NIDN: <?= htmlspecialchars($user['nomor_induk']) ?></small>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
