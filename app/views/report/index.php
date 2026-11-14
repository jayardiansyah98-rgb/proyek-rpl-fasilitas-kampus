<div class="container-fluid p-0">
    <!-- Header Modul -->
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 rounded-pill small mb-2">
                        <i class="bi bi-file-earmark-bar-graph me-1"></i> Rekapitulasi & Reporting
                    </span>
                    <h4 class="fw-bold mb-1 text-dark">Pusat Laporan & Rekapitulasi Sarpras</h4>
                    <p class="text-muted small mb-0">
                        Ekstraksi data inventaris, monitoring kelaikan aset per gedung/ruangan, audit tiket kerusakan, dan rekapitulasi biaya pemeliharaan.
                    </p>
                </div>

                <!-- Tombol Cetak & Export -->
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <?php
                    // Bangun query string filter untuk print & export
                    $queryString = http_build_query(array_merge(['type' => $type], array_filter($filters)));
                    ?>
                    <a href="<?= base_url('report/cetak?' . $queryString) ?>" target="_blank" 
                       class="btn btn-outline-secondary rounded-pill px-3 btn-sm fw-semibold">
                        <i class="bi bi-printer me-1"></i> Cetak Dokumen
                    </a>

                    <a href="<?= base_url('report/exportCsv?' . $queryString) ?>" 
                       class="btn btn-success rounded-pill px-3 btn-sm fw-semibold shadow-sm">
                        <i class="bi bi-file-earmark-excel me-1"></i> Export CSV (Excel)
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 1. TAB KATEGORI LAPORAN -->
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4 overflow-hidden">
        <div class="card-body p-2 bg-light border-bottom">
            <ul class="nav nav-pills nav-fill gap-1" id="reportTabs">
                <li class="nav-item">
                    <a class="nav-link rounded-pill py-2 px-3 small fw-semibold <?= ($type === 'fasilitas') ? 'active bg-primary text-white shadow-sm' : 'text-dark' ?>" 
                       href="<?= base_url('report?type=fasilitas') ?>">
                        <i class="bi bi-box-seam me-1"></i> Seluruh Fasilitas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill py-2 px-3 small fw-semibold <?= ($type === 'gedung') ? 'active bg-primary text-white shadow-sm' : 'text-dark' ?>" 
                       href="<?= base_url('report?type=gedung') ?>">
                        <i class="bi bi-building me-1"></i> Per Gedung
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill py-2 px-3 small fw-semibold <?= ($type === 'ruangan') ? 'active bg-primary text-white shadow-sm' : 'text-dark' ?>" 
                       href="<?= base_url('report?type=ruangan') ?>">
                        <i class="bi bi-door-open me-1"></i> Per Ruangan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill py-2 px-3 small fw-semibold <?= ($type === 'kondisi') ? 'active bg-primary text-white shadow-sm' : 'text-dark' ?>" 
                       href="<?= base_url('report?type=kondisi') ?>">
                        <i class="bi bi-shield-check me-1"></i> Berdasarkan Kondisi
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill py-2 px-3 small fw-semibold <?= ($type === 'kerusakan') ? 'active bg-primary text-white shadow-sm' : 'text-dark' ?>" 
                       href="<?= base_url('report?type=kerusakan') ?>">
                        <i class="bi bi-exclamation-octagon me-1"></i> Laporan Kerusakan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill py-2 px-3 small fw-semibold <?= ($type === 'pemeliharaan') ? 'active bg-primary text-white shadow-sm' : 'text-dark' ?>" 
                       href="<?= base_url('report?type=pemeliharaan') ?>">
                        <i class="bi bi-tools me-1"></i> Laporan Pemeliharaan
                    </a>
                </li>
            </ul>
        </div>

        <!-- 2. FILTER MULTI-DIMENSI -->
        <div class="card-body p-3 p-lg-4">
            <form action="<?= base_url('report') ?>" method="GET" class="row g-2 align-items-center">
                <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">

                <!-- Filter Tanggal -->
                <div class="col-sm-6 col-md-3">
                    <label class="form-label text-muted small mb-1 fw-semibold">Tanggal Dari</label>
                    <input type="date" class="form-control form-control-sm" name="tanggal_dari" 
                           value="<?= htmlspecialchars($filters['tanggal_dari'] ?? '') ?>">
                </div>

                <div class="col-sm-6 col-md-3">
                    <label class="form-label text-muted small mb-1 fw-semibold">Tanggal Sampai</label>
                    <input type="date" class="form-control form-control-sm" name="tanggal_sampai" 
                           value="<?= htmlspecialchars($filters['tanggal_sampai'] ?? '') ?>">
                </div>

                <!-- Filter Gedung -->
                <div class="col-sm-6 col-md-3">
                    <label class="form-label text-muted small mb-1 fw-semibold">Pilih Gedung</label>
                    <select class="form-select form-select-sm" name="gedung_id" id="reportSelectGedung">
                        <option value="">-- Semua Gedung --</option>
                        <?php foreach ($gedung_list as $g): ?>
                            <option value="<?= $g['id'] ?>" <?= ($filters['gedung_id'] == $g['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($g['nama_gedung']) ?> (<?= htmlspecialchars($g['kode_gedung']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filter Ruangan -->
                <div class="col-sm-6 col-md-3">
                    <label class="form-label text-muted small mb-1 fw-semibold">Pilih Ruangan</label>
                    <select class="form-select form-select-sm" name="ruangan_id">
                        <option value="">-- Semua Ruangan --</option>
                        <?php foreach ($ruangan_list as $r): ?>
                            <option value="<?= $r['id'] ?>" <?= ($filters['ruangan_id'] == $r['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($r['nama_ruangan']) ?> (<?= htmlspecialchars($r['kode_ruangan']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filter Kondisi Fisik -->
                <div class="col-sm-6 col-md-3">
                    <label class="form-label text-muted small mb-1 fw-semibold">Kondisi Fisik</label>
                    <select class="form-select form-select-sm" name="kondisi">
                        <option value="">-- Semua Kondisi --</option>
                        <option value="baik" <?= ($filters['kondisi'] === 'baik') ? 'selected' : '' ?>>Baik</option>
                        <option value="rusak_ringan" <?= ($filters['kondisi'] === 'rusak_ringan') ? 'selected' : '' ?>>Rusak Ringan</option>
                        <option value="rusak_berat" <?= ($filters['kondisi'] === 'rusak_berat') ? 'selected' : '' ?>>Rusak Berat</option>
                        <option value="tidak_layak" <?= ($filters['kondisi'] === 'tidak_layak') ? 'selected' : '' ?>>Tidak Layak Pakai</option>
                        <option value="hilang" <?= ($filters['kondisi'] === 'hilang') ? 'selected' : '' ?>>Hilang</option>
                    </select>
                </div>

                <!-- Filter Status -->
                <div class="col-sm-6 col-md-3">
                    <label class="form-label text-muted small mb-1 fw-semibold">Status Tiket / Servis</label>
                    <select class="form-select form-select-sm" name="status">
                        <option value="">-- Semua Status --</option>
                        <option value="menunggu" <?= ($filters['status'] === 'menunggu') ? 'selected' : '' ?>>Menunggu Verifikasi</option>
                        <option value="diproses" <?= ($filters['status'] === 'diproses') ? 'selected' : '' ?>>Sedang Diproses</option>
                        <option value="selesai" <?= ($filters['status'] === 'selesai') ? 'selected' : '' ?>>Selesai</option>
                        <option value="dijadwalkan" <?= ($filters['status'] === 'dijadwalkan') ? 'selected' : '' ?>>Dijadwalkan</option>
                        <option value="sedang_dikerjakan" <?= ($filters['status'] === 'sedang_dikerjakan') ? 'selected' : '' ?>>Sedang Dikerjakan</option>
                    </select>
                </div>

                <!-- Filter Pencarian Keyword -->
                <div class="col-sm-6 col-md-4">
                    <label class="form-label text-muted small mb-1 fw-semibold">Pencarian Kata Kunci</label>
                    <input type="text" class="form-control form-control-sm" name="search" 
                           value="<?= htmlspecialchars($filters['search'] ?? '') ?>" placeholder="Cari nama, kode, nomor...">
                </div>

                <!-- Tombol Filter -->
                <div class="col-sm-6 col-md-2 mt-auto">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm rounded-pill w-100 fw-semibold">
                            <i class="bi bi-funnel me-1"></i> Terapkan
                        </button>
                        <a href="<?= base_url('report?type=' . $type) ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3" title="Reset Filter">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- 3. KONTEN TABEL LAPORAN SESUAI TIPE -->
    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="bi bi-table text-primary me-2"></i><?= htmlspecialchars($report_title) ?>
            </h6>
            <span class="badge bg-light text-dark border px-3 py-1 font-monospace">
                Total <?= count($report_data) ?> Baris Data
            </span>
        </div>

        <div class="card-body p-0">
            <?php if (empty($report_data)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                    <h6>Tidak ada data yang memenuhi kriteria filter.</h6>
                    <small>Coba sesuaikan pilihan filter tanggal, gedung, atau kondisi fasilitas di atas.</small>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <!-- TIPE 1: SELURUH FASILITAS -->
                    <?php if ($type === 'fasilitas'): ?>
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">No</th>
                                    <th>Kode Inventaris</th>
                                    <th>Nama Fasilitas</th>
                                    <th>Kategori</th>
                                    <th>Lokasi (Gedung & Ruang)</th>
                                    <th>Jumlah</th>
                                    <th>Kondisi</th>
                                    <th>Tahun</th>
                                    <th>Tiket Kerusakan</th>
                                    <th>Pemeliharaan</th>
                                    <th class="pe-4 text-end">Total Biaya</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($report_data as $row): 
                                    $badgeK = ($row['kondisi'] === 'baik') ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle';
                                    if ($row['kondisi'] === 'rusak_berat') $badgeK = 'bg-danger-subtle text-danger border border-danger-subtle';
                                ?>
                                <tr>
                                    <td class="ps-4 text-muted"><?= $no++ ?></td>
                                    <td class="fw-bold font-monospace text-primary"><?= htmlspecialchars($row['kode_inventaris']) ?></td>
                                    <td class="fw-semibold text-dark"><?= htmlspecialchars($row['nama_fasilitas']) ?></td>
                                    <td><?= htmlspecialchars($row['nama_kategori']) ?></td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= htmlspecialchars($row['nama_ruangan']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($row['nama_gedung']) ?></small>
                                    </td>
                                    <td class="fw-bold"><?= $row['jumlah'] ?> Unit</td>
                                    <td>
                                        <span class="badge <?= $badgeK ?> px-2 py-1 rounded-pill">
                                            <?= ucfirst(str_replace('_', ' ', $row['kondisi'])) ?>
                                        </span>
                                    </td>
                                    <td><?= $row['tahun_pengadaan'] ?></td>
                                    <td><?= $row['total_laporan'] ?> Kasus</td>
                                    <td><?= $row['total_pemeliharaan'] ?> Kali</td>
                                    <td class="pe-4 text-end fw-bold text-success">
                                        Rp <?= number_format($row['total_biaya_pemeliharaan'], 0, ',', '.') ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                    <!-- TIPE 2: LAPORAN PER GEDUNG -->
                    <?php elseif ($type === 'gedung'): ?>
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">No</th>
                                    <th>Kode Gedung</th>
                                    <th>Nama Gedung</th>
                                    <th>Lokasi & Lantai</th>
                                    <th>Total Ruangan</th>
                                    <th>Total Jenis Sarpras</th>
                                    <th>Total Unit Fisik</th>
                                    <th>Kondisi Baik</th>
                                    <th>Rusak Ringan</th>
                                    <th>Rusak Berat / Afkir</th>
                                    <th class="pe-4 text-end">Tiket Kerusakan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($report_data as $row): ?>
                                <tr>
                                    <td class="ps-4 text-muted"><?= $no++ ?></td>
                                    <td class="fw-bold font-monospace text-primary"><?= htmlspecialchars($row['kode_gedung']) ?></td>
                                    <td class="fw-bold text-dark fs-6"><?= htmlspecialchars($row['nama_gedung']) ?></td>
                                    <td><?= htmlspecialchars($row['lokasi']) ?> (<?= $row['jumlah_lantai'] ?> Lantai)</td>
                                    <td class="fw-bold"><?= $row['total_ruangan'] ?> Ruang</td>
                                    <td><?= $row['total_jenis_fasilitas'] ?> Jenis</td>
                                    <td class="fw-bold text-primary"><?= number_format($row['total_unit_fasilitas']) ?> Unit</td>
                                    <td><span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill"><?= $row['fasilitas_baik'] ?> Item</span></td>
                                    <td><span class="badge bg-warning-subtle text-warning-emphasis px-2 py-1 rounded-pill"><?= $row['fasilitas_rusak_ringan'] ?> Item</span></td>
                                    <td><span class="badge bg-danger-subtle text-danger px-2 py-1 rounded-pill"><?= $row['fasilitas_rusak_berat'] + $row['fasilitas_tidak_layak'] ?> Item</span></td>
                                    <td class="pe-4 text-end fw-bold text-danger"><?= $row['total_tiket_laporan'] ?> Kasus</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                    <!-- TIPE 3: LAPORAN PER RUANGAN -->
                    <?php elseif ($type === 'ruangan'): ?>
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">No</th>
                                    <th>Kode Ruang</th>
                                    <th>Nama Ruangan</th>
                                    <th>Gedung & Lantai</th>
                                    <th>Jenis & Kapasitas</th>
                                    <th>Status Ruangan</th>
                                    <th>Jenis Fasilitas</th>
                                    <th>Total Unit</th>
                                    <th>Fasilitas Baik</th>
                                    <th>Fasilitas Rusak</th>
                                    <th class="pe-4 text-end">Total Tiket</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($report_data as $row): ?>
                                <tr>
                                    <td class="ps-4 text-muted"><?= $no++ ?></td>
                                    <td class="fw-bold font-monospace text-primary"><?= htmlspecialchars($row['kode_ruangan']) ?></td>
                                    <td class="fw-bold text-dark"><?= htmlspecialchars($row['nama_ruangan']) ?></td>
                                    <td><?= htmlspecialchars($row['nama_gedung']) ?>, Lt. <?= $row['lantai'] ?></td>
                                    <td><?= htmlspecialchars($row['jenis_ruangan']) ?> (<?= $row['kapasitas'] ?> Kursi)</td>
                                    <td>
                                        <span class="badge bg-light text-dark border px-2 py-1 rounded-pill"><?= ucfirst($row['status_ruangan']) ?></span>
                                    </td>
                                    <td><?= $row['total_jenis_fasilitas'] ?> Jenis</td>
                                    <td class="fw-bold"><?= $row['total_unit_fasilitas'] ?> Unit</td>
                                    <td><span class="text-success fw-bold"><?= $row['item_baik'] ?> Baik</span></td>
                                    <td><span class="text-danger fw-bold"><?= $row['item_rusak'] ?> Rusak</span></td>
                                    <td class="pe-4 text-end fw-bold text-danger"><?= $row['total_laporan'] ?> Tiket</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                    <!-- TIPE 4: LAPORAN BERDASARKAN KONDISI -->
                    <?php elseif ($type === 'kondisi'): ?>
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">No</th>
                                    <th>Kondisi Fisik</th>
                                    <th>Kode Inventaris</th>
                                    <th>Nama Fasilitas</th>
                                    <th>Kategori</th>
                                    <th>Ruangan & Gedung</th>
                                    <th>Jumlah</th>
                                    <th>Tahun Pengadaan</th>
                                    <th>Tiket Kerusakan Aktif</th>
                                    <th class="pe-4 text-end">Riwayat Pemeliharaan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($report_data as $row): 
                                    $badgeK = ($row['kondisi'] === 'baik') ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle';
                                    if ($row['kondisi'] === 'rusak_berat') $badgeK = 'bg-danger-subtle text-danger border border-danger-subtle';
                                    elseif ($row['kondisi'] === 'tidak_layak') $badgeK = 'bg-dark-subtle text-dark border border-dark-subtle';
                                ?>
                                <tr>
                                    <td class="ps-4 text-muted"><?= $no++ ?></td>
                                    <td>
                                        <span class="badge <?= $badgeK ?> px-3 py-1 rounded-pill fw-semibold">
                                            <?= ucfirst(str_replace('_', ' ', $row['kondisi'])) ?>
                                        </span>
                                    </td>
                                    <td class="fw-bold font-monospace text-primary"><?= htmlspecialchars($row['kode_inventaris']) ?></td>
                                    <td class="fw-semibold text-dark"><?= htmlspecialchars($row['nama_fasilitas']) ?></td>
                                    <td><?= htmlspecialchars($row['nama_kategori']) ?></td>
                                    <td><?= htmlspecialchars($row['nama_ruangan']) ?> (<?= htmlspecialchars($row['nama_gedung']) ?>)</td>
                                    <td class="fw-bold"><?= $row['jumlah'] ?> Unit</td>
                                    <td><?= $row['tahun_pengadaan'] ?></td>
                                    <td>
                                        <?php if ($row['active_laporan'] > 0): ?>
                                            <span class="badge bg-danger px-2 py-1 rounded-pill"><?= $row['active_laporan'] ?> Tiket Aktif</span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="pe-4 text-end"><?= $row['total_pemeliharaan'] ?> Kali</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                    <!-- TIPE 5: LAPORAN KERUSAKAN -->
                    <?php elseif ($type === 'kerusakan'): ?>
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">No</th>
                                    <th>Kode Tiket</th>
                                    <th>Tanggal Lapor</th>
                                    <th>Nama Pelapor</th>
                                    <th>Fasilitas / Ruangan</th>
                                    <th>Keluhan / Kerusakan</th>
                                    <th>Prioritas</th>
                                    <th>Status</th>
                                    <th class="pe-4 text-end">Tindakan Petugas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($report_data as $row): 
                                    $prioClass = ($row['prioritas'] === 'darurat') ? 'bg-danger text-white' : (($row['prioritas'] === 'tinggi') ? 'bg-warning text-dark' : 'bg-secondary text-white');
                                    $statClass = ($row['status'] === 'selesai') ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle';
                                ?>
                                <tr>
                                    <td class="ps-4 text-muted"><?= $no++ ?></td>
                                    <td class="fw-bold font-monospace text-primary"><?= htmlspecialchars($row['kode_laporan']) ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= htmlspecialchars($row['nama_pelapor']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($row['nim_nip_pelapor']) ?> (<?= ucfirst($row['jenis_pengguna']) ?>)</small>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= htmlspecialchars($row['nama_fasilitas']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($row['nama_ruangan']) ?> (<?= htmlspecialchars($row['nama_gedung']) ?>)</small>
                                    </td>
                                    <td>
                                        <div class="text-dark" style="max-width: 250px;"><?= htmlspecialchars($row['deskripsi']) ?></div>
                                    </td>
                                    <td>
                                        <span class="badge <?= $prioClass ?> px-2 py-1 rounded-pill"><?= ucfirst($row['prioritas']) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge <?= $statClass ?> px-2 py-1 rounded-pill"><?= ucfirst($row['status']) ?></span>
                                    </td>
                                    <td class="pe-4 text-end text-muted">
                                        <?= htmlspecialchars($row['catatan_petugas'] ?? '-') ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                    <!-- TIPE 6: LAPORAN PEMELIHARAAN -->
                    <?php elseif ($type === 'pemeliharaan'): ?>
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">No</th>
                                    <th>Kode Servis</th>
                                    <th>Tanggal</th>
                                    <th>Fasilitas / Ruangan</th>
                                    <th>Teknisi Pelaksana</th>
                                    <th>Jenis Tindakan</th>
                                    <th>Perubahan Kondisi</th>
                                    <th>Status</th>
                                    <th>Deskripsi Pekerjaan</th>
                                    <th class="pe-4 text-end">Biaya Servis</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($report_data as $row): 
                                    $tgl = !empty($row['tanggal']) ? $row['tanggal'] : $row['tanggal_mulai'];
                                    $statP = ($row['status'] === 'selesai') ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle';
                                ?>
                                <tr>
                                    <td class="ps-4 text-muted"><?= $no++ ?></td>
                                    <td class="fw-bold font-monospace text-primary"><?= htmlspecialchars($row['kode_pemeliharaan']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($tgl)) ?></td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= htmlspecialchars($row['nama_fasilitas']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($row['nama_ruangan']) ?></small>
                                    </td>
                                    <td class="fw-semibold text-dark"><?= htmlspecialchars($row['nama_petugas']) ?></td>
                                    <td>
                                        <span class="badge bg-secondary px-2 py-1 rounded-pill"><?= htmlspecialchars($row['jenis_tindakan']) ?></span>
                                    </td>
                                    <td>
                                        <span class="text-muted"><?= ucfirst(str_replace('_', ' ', $row['kondisi_sebelum'] ?? 'baik')) ?></span>
                                        <i class="bi bi-arrow-right mx-1 text-primary"></i>
                                        <span class="fw-bold text-success"><?= ucfirst(str_replace('_', ' ', $row['kondisi_sesudah'] ?? 'baik')) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge <?= $statP ?> px-2 py-1 rounded-pill"><?= ucfirst(str_replace('_', ' ', $row['status'])) ?></span>
                                    </td>
                                    <td>
                                        <div class="text-dark" style="max-width: 200px;"><?= htmlspecialchars($row['deskripsi'] ?? $row['keterangan'] ?? '-') ?></div>
                                    </td>
                                    <td class="pe-4 text-end fw-bold text-success">
                                        Rp <?= number_format($row['biaya'], 0, ',', '.') ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
