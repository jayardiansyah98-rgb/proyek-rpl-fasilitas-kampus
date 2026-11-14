<div class="container-fluid p-0">
    <!-- Breadcrumb & Header -->
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
        <div class="card-body p-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2 small">
                    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('ruangan') ?>" class="text-decoration-none">Data Ruangan</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($ruangan['nama_ruangan']) ?></li>
                </ol>
            </nav>
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary-subtle text-primary p-3 rounded-4 fs-3">
                        <i class="bi bi-door-open"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-primary px-3 py-1 rounded-pill font-monospace"><?= htmlspecialchars($ruangan['kode_ruangan']) ?></span>
                            <?php if ($ruangan['status'] === 'tersedia'): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">Tersedia</span>
                            <?php elseif ($ruangan['status'] === 'digunakan'): ?>
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-1">Digunakan</span>
                            <?php elseif ($ruangan['status'] === 'perbaikan'): ?>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1">Dalam Perbaikan</span>
                            <?php else: ?>
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3 py-1">Nonaktif</span>
                            <?php endif; ?>
                            <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-1">
                                <?= htmlspecialchars($ruangan['jenis_ruangan']) ?>
                            </span>
                        </div>
                        <h4 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($ruangan['nama_ruangan']) ?></h4>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?= base_url('ruangan') ?>" class="btn btn-outline-secondary rounded-pill px-3 py-2 small">
                        <i class="bi bi-arrow-left me-1"></i> Kembali
                    </a>
                    <?php if (Auth::hasRole(['admin', 'petugas_sarpras'])): ?>
                    <a href="<?= base_url('ruangan/edit/' . $ruangan['id']) ?>" class="btn btn-primary rounded-pill px-3 py-2 small fw-semibold">
                        <i class="bi bi-pencil me-1"></i> Edit Ruangan
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Spesifikasi Ruangan & Counter -->
    <div class="row g-4 mb-4">
        <!-- Informasi Teknis -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4 h-100">
                <h6 class="fw-bold text-dark border-bottom pb-3 mb-3">
                    <i class="bi bi-info-circle text-primary me-2"></i>Spesifikasi Teknis & Penempatan
                </h6>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="text-muted small">Kode Ruangan:</div>
                        <div class="fw-bold text-dark font-monospace"><?= htmlspecialchars($ruangan['kode_ruangan']) ?></div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small">Jenis Ruangan:</div>
                        <div class="fw-semibold text-dark"><?= htmlspecialchars($ruangan['jenis_ruangan']) ?></div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small">Gedung Kampus:</div>
                        <div class="fw-semibold text-dark">
                            <a href="<?= base_url('gedung/detail/' . $ruangan['gedung_id']) ?>" class="text-decoration-none">
                                <i class="bi bi-building text-primary me-1"></i><?= htmlspecialchars($ruangan['nama_gedung']) ?> (<?= htmlspecialchars($ruangan['kode_gedung']) ?>)
                            </a>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small">Posisi Lantai:</div>
                        <div class="fw-semibold text-dark"><i class="bi bi-layers text-secondary me-1"></i>Lantai <?= $ruangan['lantai'] ?></div>
                    </div>
                    <div class="col-12">
                        <div class="text-muted small">Alamat / Area Lokasi Gedung:</div>
                        <div class="text-dark small"><i class="bi bi-geo-alt text-danger me-1"></i><?= htmlspecialchars($ruangan['lokasi_gedung'] ?: '-') ?></div>
                    </div>
                    <div class="col-12">
                        <div class="text-muted small">Keterangan / Fungsi:</div>
                        <div class="text-dark bg-light p-3 rounded-3 mt-1 small">
                            <?= nl2br(htmlspecialchars($ruangan['keterangan'] ?: 'Tidak ada catatan tambahan untuk ruangan ini.')) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metric Counter -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4 h-100 d-flex flex-column justify-content-between">
                <div>
                    <h6 class="fw-bold text-dark border-bottom pb-3 mb-3">
                        <i class="bi bi-pie-chart text-primary me-2"></i>Kapasitas & Fasilitas
                    </h6>
                    <div class="d-flex align-items-center gap-3 mb-3 p-3 bg-light rounded-3">
                        <div class="bg-primary text-white p-3 rounded-circle">
                            <i class="bi bi-people fs-4"></i>
                        </div>
                        <div>
                            <div class="fs-4 fw-bold text-dark"><?= $ruangan['kapasitas'] ?> <span class="fs-6 fw-normal text-muted">Orang</span></div>
                            <div class="text-muted small">Daya Tampung Maksimal</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3">
                        <div class="bg-info text-white p-3 rounded-circle">
                            <i class="bi bi-box-seam fs-4"></i>
                        </div>
                        <div>
                            <div class="fs-4 fw-bold text-dark"><?= count($fasilitas_list) ?> <span class="fs-6 fw-normal text-muted">Barang</span></div>
                            <div class="text-muted small">Fasilitas Terpasang</div>
                        </div>
                    </div>
                </div>
                <div class="pt-3 border-top mt-3 text-muted small">
                    <i class="bi bi-calendar3 me-1"></i> Terdata sejak: <?= date('d M Y, H:i', strtotime($ruangan['created_at'])) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Fasilitas Terpasang di Ruangan Ini -->
    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
        <div class="card-header bg-white p-4 border-0 pb-0 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold text-dark mb-1">
                    <i class="bi bi-boxes text-primary me-2"></i>Inventaris & Fasilitas di Ruangan Ini
                </h5>
                <p class="text-muted small mb-0">Daftar sarana dan prasarana yang terpasang dan dapat digunakan civitas akademika.</p>
            </div>
            <span class="badge bg-primary rounded-pill px-3 py-2 small">
                <?= count($fasilitas_list) ?> Jenis Fasilitas Terdata
            </span>
        </div>
        <div class="card-body p-0 mt-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light text-secondary">
                        <tr>
                            <th class="ps-4" style="width: 50px;">No</th>
                            <th style="width: 170px;">Kode Inventaris</th>
                            <th>Nama Fasilitas</th>
                            <th>Kategori</th>
                            <th class="text-center" style="width: 100px;">Jumlah</th>
                            <th class="text-center" style="width: 120px;">Kondisi</th>
                            <th class="text-center" style="width: 110px;">Pengadaan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($fasilitas_list)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                Belum ada fasilitas atau inventaris yang terdata untuk ruangan ini.
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($fasilitas_list as $fIdx => $f): ?>
                            <tr>
                                <td class="ps-4 text-muted"><?= $fIdx + 1 ?></td>
                                <td>
                                    <span class="badge bg-light text-dark border font-monospace px-2 py-1">
                                        <?= htmlspecialchars($f['kode_inventaris']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($f['nama']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($f['keterangan'] ?? '-') ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary"><?= htmlspecialchars($f['nama_kategori']) ?></span>
                                </td>
                                <td class="text-center fw-bold text-dark"><?= $f['jumlah'] ?> Unit</td>
                                <td class="text-center">
                                    <?php if ($f['kondisi'] === 'baik'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1">Baik</span>
                                    <?php elseif ($f['kondisi'] === 'rusak_ringan'): ?>
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2 py-1">Rusak Ringan</span>
                                    <?php elseif ($f['kondisi'] === 'rusak_berat'): ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-1">Rusak Berat</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2 py-1"><?= ucfirst($f['kondisi']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center text-muted"><?= $f['tahun_pengadaan'] ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Riwayat Laporan Kerusakan Terkait Ruangan Ini -->
    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
        <div class="card-header bg-white p-4 border-0 pb-0">
            <h5 class="fw-bold text-dark mb-1">
                <i class="bi bi-exclamation-octagon text-danger me-2"></i>Riwayat Laporan Kerusakan
            </h5>
            <p class="text-muted small mb-0">Tiket keluhan dan pelaporan kerusakan fasilitas yang terjadi pada ruangan ini.</p>
        </div>
        <div class="card-body p-0 mt-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light text-secondary">
                        <tr>
                            <th class="ps-4" style="width: 140px;">Kode Laporan</th>
                            <th>Pelapor</th>
                            <th>Fasilitas</th>
                            <th>Deskripsi Masalah</th>
                            <th class="text-center" style="width: 100px;">Prioritas</th>
                            <th class="text-center" style="width: 110px;">Status</th>
                            <th class="text-end pe-4" style="width: 130px;">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($laporan_list)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="bi bi-shield-check text-success fs-4 d-block mb-1"></i>
                                Tidak ada catatan riwayat kerusakan fasilitas pada ruangan ini.
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($laporan_list as $lap): ?>
                            <tr>
                                <td class="ps-4 font-monospace text-primary fw-semibold"><?= htmlspecialchars($lap['kode_laporan']) ?></td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($lap['pelapor']) ?></div>
                                </td>
                                <td><?= htmlspecialchars($lap['nama_fasilitas'] ?? 'Ruangan Umum') ?></td>
                                <td>
                                    <div class="text-truncate" style="max-width: 250px;">
                                        <?= htmlspecialchars($lap['deskripsi']) ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-1">
                                        <?= ucfirst($lap['prioritas']) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php if ($lap['status'] === 'selesai'): ?>
                                        <span class="badge bg-success">Selesai</span>
                                    <?php elseif ($lap['status'] === 'diproses'): ?>
                                        <span class="badge bg-warning text-dark">Diproses</span>
                                    <?php elseif ($lap['status'] === 'diverifikasi'): ?>
                                        <span class="badge bg-info text-dark">Diverifikasi</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Menunggu</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4 text-muted">
                                    <?= date('d/m/Y', strtotime($lap['created_at'])) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
