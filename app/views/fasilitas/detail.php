<div class="container-fluid p-0">
    <!-- Breadcrumb & Navigasi -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('fasilitas') ?>" class="text-decoration-none">Data Fasilitas</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($fasilitas['kode_inventaris']) ?></li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($fasilitas['nama_fasilitas']) ?></h4>
            <span class="text-muted small">
                <i class="bi bi-tag me-1"></i><?= htmlspecialchars($fasilitas['nama_kategori']) ?> &bull;
                <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($fasilitas['nama_ruangan']) ?> (<?= htmlspecialchars($fasilitas['nama_gedung']) ?>)
            </span>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="<?= base_url('fasilitas') ?>" class="btn btn-outline-secondary rounded-pill px-3 btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>

            <?php if (Auth::hasRole(['admin', 'petugas_sarpras'])): ?>
                <!-- Tombol Ubah Kondisi Cepat -->
                <button type="button" class="btn btn-outline-warning rounded-pill px-3 btn-sm fw-semibold" 
                        data-bs-toggle="modal" data-bs-target="#modalUbahKondisi">
                    <i class="bi bi-arrow-repeat me-1"></i> Ubah Kondisi
                </button>

                <!-- Tombol Tambah Pemeliharaan -->
                <a href="<?= base_url('pemeliharaan/tambah?fasilitas_id=' . $fasilitas['id']) ?>" 
                   class="btn btn-primary rounded-pill px-3 btn-sm fw-semibold shadow-sm">
                    <i class="bi bi-tools me-1"></i> + Catat Pemeliharaan
                </a>

                <a href="<?= base_url('fasilitas/edit/' . $fasilitas['id']) ?>" class="btn btn-light border rounded-pill px-3 btn-sm">
                    <i class="bi bi-pencil me-1"></i> Edit
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- 1. KARTU PERHITUNGAN SEDERHANA STATISTIK -->
    <div class="row g-3 mb-4">
        <!-- Total Pemeliharaan -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-body p-3 p-xl-4 d-flex align-items-center gap-3">
                    <div class="rounded-4 bg-primary-subtle text-primary p-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                        <i class="bi bi-tools fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Total Pemeliharaan</div>
                        <div class="fs-4 fw-bold text-dark mb-0"><?= number_format($stats['total_pemeliharaan'] ?? 0) ?> <span class="fs-6 fw-normal text-muted">Aktivitas</span></div>
                        <small class="text-success"><i class="bi bi-check2-circle me-1"></i><?= $stats['selesai'] ?? 0 ?> selesai</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Biaya -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-body p-3 p-xl-4 d-flex align-items-center gap-3">
                    <div class="rounded-4 bg-success-subtle text-success p-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                        <i class="bi bi-cash-stack fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Total Biaya</div>
                        <div class="fs-4 fw-bold text-dark mb-0">Rp <?= number_format($stats['total_biaya'] ?? 0, 0, ',', '.') ?></div>
                        <small class="text-muted">Akumulasi perawatan</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Jumlah Kerusakan -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-body p-3 p-xl-4 d-flex align-items-center gap-3">
                    <div class="rounded-4 bg-danger-subtle text-danger p-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                        <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Jumlah Kerusakan</div>
                        <div class="fs-4 fw-bold text-dark mb-0"><?= number_format($stats['jumlah_kerusakan'] ?? 0) ?> <span class="fs-6 fw-normal text-muted">Kasus</span></div>
                        <small class="text-muted"><?= count($riwayat_laporan ?? []) ?> tiket laporan</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kondisi Saat Ini -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-body p-3 p-xl-4 d-flex align-items-center gap-3">
                    <?php 
                    $badgeKondisi = 'bg-secondary';
                    $iconKondisi = 'bi-question-circle';
                    $labelKondisi = ucfirst(str_replace('_', ' ', $fasilitas['kondisi']));
                    if ($fasilitas['kondisi'] === 'baik') {
                        $badgeKondisi = 'bg-success-subtle text-success border border-success-subtle';
                        $iconKondisi = 'bi-check-circle-fill text-success';
                    } elseif ($fasilitas['kondisi'] === 'rusak_ringan') {
                        $badgeKondisi = 'bg-warning-subtle text-warning-emphasis border border-warning-subtle';
                        $iconKondisi = 'bi-exclamation-circle-fill text-warning';
                    } elseif ($fasilitas['kondisi'] === 'rusak_berat') {
                        $badgeKondisi = 'bg-danger-subtle text-danger border border-danger-subtle';
                        $iconKondisi = 'bi-x-circle-fill text-danger';
                    } elseif ($fasilitas['kondisi'] === 'tidak_layak') {
                        $badgeKondisi = 'bg-dark-subtle text-dark border border-dark-subtle';
                        $iconKondisi = 'bi-slash-circle-fill text-dark';
                    }
                    ?>
                    <div class="rounded-4 bg-light p-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                        <i class="bi <?= $iconKondisi ?> fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Kondisi Terkini</div>
                        <div>
                            <span class="badge <?= $badgeKondisi ?> px-3 py-1 rounded-pill fw-semibold mt-1">
                                <?= $labelKondisi ?>
                            </span>
                        </div>
                        <small class="text-muted">Tahun: <?= htmlspecialchars($fasilitas['tahun_pengadaan']) ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. INFORMASI DETAIL FASILITAS -->
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4 overflow-hidden">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
            <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-info-circle me-2 text-primary"></i>Informasi & Spesifikasi Fasilitas</h6>
            <span class="badge bg-light text-dark border px-3 py-1 font-monospace"><?= htmlspecialchars($fasilitas['kode_inventaris']) ?></span>
        </div>
        <div class="card-body p-4">
            <div class="row g-4 align-items-start">
                <!-- Foto Fasilitas -->
                <div class="col-md-4 col-lg-3 text-center">
                    <?php if (!empty($fasilitas['foto'])): ?>
                        <img src="<?= base_url('uploads/fasilitas/' . htmlspecialchars($fasilitas['foto'])) ?>" 
                             alt="<?= htmlspecialchars($fasilitas['nama_fasilitas']) ?>" 
                             class="img-fluid rounded-4 shadow-sm w-100" 
                             style="max-height: 220px; object-fit: cover;">
                    <?php else: ?>
                        <div class="bg-light rounded-4 d-flex flex-column align-items-center justify-content-center p-4 text-muted border border-dashed" style="height: 180px;">
                            <i class="bi bi-camera text-secondary fs-1 mb-2"></i>
                            <span class="small">Foto Belum Tersedia</span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Spesifikasi Teknis -->
                <div class="col-md-8 col-lg-9">
                    <div class="row g-3">
                        <div class="col-sm-6 col-lg-4">
                            <div class="p-3 bg-light rounded-3">
                                <small class="text-muted d-block mb-1">Kode Inventaris</small>
                                <span class="fw-bold text-dark font-monospace"><?= htmlspecialchars($fasilitas['kode_inventaris']) ?></span>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-4">
                            <div class="p-3 bg-light rounded-3">
                                <small class="text-muted d-block mb-1">Kategori Sarpras</small>
                                <span class="fw-semibold text-dark"><?= htmlspecialchars($fasilitas['nama_kategori']) ?></span>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-4">
                            <div class="p-3 bg-light rounded-3">
                                <small class="text-muted d-block mb-1">Lokasi Ruangan</small>
                                <span class="fw-semibold text-dark"><?= htmlspecialchars($fasilitas['nama_ruangan']) ?></span>
                                <small class="text-muted d-block">(<?= htmlspecialchars($fasilitas['nama_gedung']) ?>, Lt. <?= htmlspecialchars($fasilitas['lantai']) ?>)</small>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-4">
                            <div class="p-3 bg-light rounded-3">
                                <small class="text-muted d-block mb-1">Jumlah Unit Tersedia</small>
                                <span class="fw-bold text-dark"><?= htmlspecialchars($fasilitas['jumlah']) ?> Unit</span>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-4">
                            <div class="p-3 bg-light rounded-3">
                                <small class="text-muted d-block mb-1">Tahun Pengadaan</small>
                                <span class="fw-semibold text-dark"><?= htmlspecialchars($fasilitas['tahun_pengadaan']) ?></span>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-4">
                            <div class="p-3 bg-light rounded-3">
                                <small class="text-muted d-block mb-1">Status Fisik</small>
                                <span class="badge <?= $badgeKondisi ?> px-2 py-1 rounded-pill small"><?= $labelKondisi ?></span>
                            </div>
                        </div>
                        <?php if (!empty($fasilitas['keterangan'])): ?>
                        <div class="col-12">
                            <div class="p-3 bg-light rounded-3">
                                <small class="text-muted d-block mb-1">Catatan & Keterangan</small>
                                <p class="mb-0 text-dark small" style="white-space: pre-line;"><?= htmlspecialchars($fasilitas['keterangan']) ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. MODUL RIWAYAT PEMELIHARAAN (TIMELINE & TABLE BOOTSTRAP) -->
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4 overflow-hidden">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
            <div>
                <h5 class="fw-bold mb-1 text-dark">
                    <i class="bi bi-clock-history text-primary me-2"></i>Riwayat Pemeliharaan & Pemeriksaan
                </h5>
                <p class="text-muted small mb-0">Catatan kronologis tindakan perawatan preventif, perbaikan, dan penggantian komponen.</p>
            </div>

            <div class="d-flex align-items-center gap-2">
                <!-- Nav Tabs untuk Toggle Timeline vs Table -->
                <ul class="nav nav-pills nav-fill bg-light p-1 rounded-pill small" id="pemeliharaanTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active rounded-pill px-3 py-1 small fw-semibold" id="tab-timeline-btn" data-bs-toggle="pill" data-bs-target="#tab-timeline" type="button" role="tab">
                            <i class="bi bi-diagram-3 me-1"></i> Timeline
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill px-3 py-1 small fw-semibold" id="tab-table-btn" data-bs-toggle="pill" data-bs-target="#tab-table" type="button" role="tab">
                            <i class="bi bi-table me-1"></i> Tabel Bootstrap
                        </button>
                    </li>
                </ul>

                <?php if (Auth::hasRole(['admin', 'petugas_sarpras'])): ?>
                <a href="<?= base_url('pemeliharaan/tambah?fasilitas_id=' . $fasilitas['id']) ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                    <i class="bi bi-plus-lg me-1"></i> Catat Baru
                </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="card-body p-4">
            <div class="tab-content" id="pemeliharaanTabsContent">
                <!-- FORMAT 1: TIMELINE -->
                <div class="tab-pane fade show active" id="tab-timeline" role="tabpanel">
                    <?php if (empty($riwayat_pemeliharaan)): ?>
                        <div class="text-center py-5">
                            <div class="rounded-circle bg-light d-inline-flex p-4 text-muted mb-3">
                                <i class="bi bi-tools fs-1 text-secondary"></i>
                            </div>
                            <h6 class="fw-bold text-dark">Belum Ada Riwayat Tindakan Pemeliharaan</h6>
                            <p class="text-muted small mb-3">Fasilitas ini belum memiliki catatan servis berkala atau pemeriksaan teknisi.</p>
                            <?php if (Auth::hasRole(['admin', 'petugas_sarpras'])): ?>
                                <a href="<?= base_url('pemeliharaan/tambah?fasilitas_id=' . $fasilitas['id']) ?>" class="btn btn-primary rounded-pill px-4 btn-sm fw-semibold shadow-sm">
                                    <i class="bi bi-tools me-1"></i> Tambah Catatan Pemeliharaan Pertama
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <!-- Vertical Timeline -->
                        <div class="timeline-container position-relative ps-4 ps-md-5 my-2">
                            <!-- Vertical Line -->
                            <div class="position-absolute bg-primary bg-opacity-25" style="top: 0; bottom: 0; left: 18px; width: 3px; border-radius: 2px;"></div>

                            <?php foreach ($riwayat_pemeliharaan as $idx => $p): 
                                $tglDisplay = !empty($p['tanggal']) ? $p['tanggal'] : $p['tanggal_mulai'];
                                $badgeTindakanClass = 'bg-secondary';
                                if ($p['jenis_tindakan'] === 'Perbaikan') $badgeTindakanClass = 'bg-danger text-white';
                                elseif ($p['jenis_tindakan'] === 'Pemeriksaan') $badgeTindakanClass = 'bg-info text-dark';
                                elseif ($p['jenis_tindakan'] === 'Penggantian') $badgeTindakanClass = 'bg-warning text-dark';
                                elseif ($p['jenis_tindakan'] === 'Pemeliharaan Rutin') $badgeTindakanClass = 'bg-success text-white';

                                $badgeSebelum = ($p['kondisi_sebelum'] ?? 'baik') === 'baik' ? 'badge bg-success-subtle text-success' : 'badge bg-warning-subtle text-warning-emphasis';
                                $badgeSesudah = ($p['kondisi_sesudah'] ?? 'baik') === 'baik' ? 'badge bg-success-subtle text-success' : 'badge bg-warning-subtle text-warning-emphasis';
                            ?>
                            <div class="timeline-item position-relative mb-4 pb-2">
                                <!-- Marker Dot -->
                                <div class="position-absolute bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" 
                                     style="left: -37px; top: 0; width: 32px; height: 32px;">
                                    <i class="bi bi-wrench-adjustable small"></i>
                                </div>

                                <div class="card border border-light-subtle rounded-4 shadow-sm bg-white p-3 p-md-4">
                                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-2 mb-2">
                                        <div>
                                            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                                <span class="badge <?= $badgeTindakanClass ?> px-3 py-1 rounded-pill small fw-semibold">
                                                    <?= htmlspecialchars($p['jenis_tindakan']) ?>
                                                </span>
                                                <span class="font-monospace text-primary fw-bold small">
                                                    <?= htmlspecialchars($p['kode_pemeliharaan']) ?>
                                                </span>
                                                <?php if (isset($p['tampil_publik']) && $p['tampil_publik'] == 1): ?>
                                                    <span class="badge bg-light text-muted border small"><i class="bi bi-eye me-1"></i>Publik</span>
                                                <?php else: ?>
                                                    <span class="badge bg-dark text-white small"><i class="bi bi-eye-slash me-1"></i>Internal</span>
                                                <?php endif; ?>
                                            </div>
                                            <h6 class="fw-bold text-dark mb-0"><?= htmlspecialchars($p['deskripsi'] ?? $p['keterangan'] ?? 'Pemeliharaan Fasilitas') ?></h6>
                                        </div>

                                        <div class="text-md-end">
                                            <div class="fw-bold text-dark small"><i class="bi bi-calendar3 me-1 text-primary"></i><?= date('d M Y', strtotime($tglDisplay)) ?></div>
                                            <small class="text-muted">Teknisi: <strong class="text-dark"><?= htmlspecialchars($p['nama_petugas']) ?></strong></small>
                                        </div>
                                    </div>

                                    <!-- Kondisi Sebelum & Sesudah Transition -->
                                    <div class="d-flex align-items-center gap-2 flex-wrap bg-light p-2 rounded-3 my-2 small">
                                        <span class="text-muted"><i class="bi bi-activity me-1"></i>Perubahan Kondisi:</span>
                                        <span class="<?= $badgeSebelum ?>"><?= ucfirst(str_replace('_', ' ', $p['kondisi_sebelum'] ?? 'baik')) ?></span>
                                        <i class="bi bi-arrow-right text-primary"></i>
                                        <span class="<?= $badgeSesudah ?>"><?= ucfirst(str_replace('_', ' ', $p['kondisi_sesudah'] ?? 'baik')) ?></span>
                                    </div>

                                    <?php if (!empty($p['keterangan']) && $p['keterangan'] !== $p['deskripsi']): ?>
                                        <p class="text-muted small mb-2" style="white-space: pre-line;"><?= htmlspecialchars($p['keterangan']) ?></p>
                                    <?php endif; ?>

                                    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                                        <div>
                                            <span class="text-muted small">Biaya Perawatan: </span>
                                            <span class="fw-bold text-success">Rp <?= number_format($p['biaya'], 0, ',', '.') ?></span>
                                        </div>

                                        <div class="d-flex align-items-center gap-2">
                                            <a href="<?= base_url('pemeliharaan/detail/' . $p['id']) ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                                <i class="bi bi-info-circle me-1"></i> Rincian
                                            </a>
                                            <?php if (Auth::hasRole(['admin', 'petugas_sarpras'])): ?>
                                            <a href="<?= base_url('pemeliharaan/edit/' . $p['id']) ?>" class="btn btn-sm btn-light border rounded-pill px-3">
                                                <i class="bi bi-pencil me-1"></i> Edit
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- FORMAT 2: TABEL BOOTSTRAP -->
                <div class="tab-pane fade" id="tab-table" role="tabpanel">
                    <?php if (empty($riwayat_pemeliharaan)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                            Belum ada data pemeliharaan untuk fasilitas ini.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 small">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">Tanggal</th>
                                        <th>Kode</th>
                                        <th>Jenis Tindakan</th>
                                        <th>Perubahan Kondisi</th>
                                        <th>Deskripsi & Petugas</th>
                                        <th>Biaya</th>
                                        <th>Status</th>
                                        <th class="text-end pe-3">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($riwayat_pemeliharaan as $p): 
                                        $tglDisplay = !empty($p['tanggal']) ? $p['tanggal'] : $p['tanggal_mulai'];
                                        $badgeTindakanClass = 'bg-secondary';
                                        if ($p['jenis_tindakan'] === 'Perbaikan') $badgeTindakanClass = 'bg-danger text-white';
                                        elseif ($p['jenis_tindakan'] === 'Pemeriksaan') $badgeTindakanClass = 'bg-info text-dark';
                                        elseif ($p['jenis_tindakan'] === 'Penggantian') $badgeTindakanClass = 'bg-warning text-dark';
                                        elseif ($p['jenis_tindakan'] === 'Pemeliharaan Rutin') $badgeTindakanClass = 'bg-success text-white';

                                        $badgeSebelum = ($p['kondisi_sebelum'] ?? 'baik') === 'baik' ? 'text-success' : 'text-warning';
                                        $badgeSesudah = ($p['kondisi_sesudah'] ?? 'baik') === 'baik' ? 'text-success' : 'text-warning';
                                    ?>
                                    <tr>
                                        <td class="ps-3 fw-semibold text-dark"><?= date('d M Y', strtotime($tglDisplay)) ?></td>
                                        <td>
                                            <a href="<?= base_url('pemeliharaan/detail/' . $p['id']) ?>" class="fw-bold font-monospace text-primary text-decoration-none">
                                                <?= htmlspecialchars($p['kode_pemeliharaan']) ?>
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge <?= $badgeTindakanClass ?> px-2 py-1 rounded-pill">
                                                <?= htmlspecialchars($p['jenis_tindakan']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="small fw-semibold <?= $badgeSebelum ?>"><?= ucfirst(str_replace('_', ' ', $p['kondisi_sebelum'] ?? 'baik')) ?></span>
                                            <i class="bi bi-arrow-right mx-1 text-muted"></i>
                                            <span class="small fw-semibold <?= $badgeSesudah ?>"><?= ucfirst(str_replace('_', ' ', $p['kondisi_sesudah'] ?? 'baik')) ?></span>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark"><?= htmlspecialchars($p['deskripsi'] ?? $p['keterangan'] ?? '-') ?></div>
                                            <small class="text-muted"><i class="bi bi-person me-1"></i><?= htmlspecialchars($p['nama_petugas']) ?></small>
                                        </td>
                                        <td class="fw-bold text-success">
                                            Rp <?= number_format($p['biaya'], 0, ',', '.') ?>
                                        </td>
                                        <td>
                                            <?php if ($p['status'] === 'selesai'): ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill">Selesai</span>
                                            <?php elseif ($p['status'] === 'sedang_dikerjakan'): ?>
                                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 rounded-pill">Dikerjakan</span>
                                            <?php elseif ($p['status'] === 'dijadwalkan'): ?>
                                                <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 rounded-pill">Dijadwalkan</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary px-2 py-1 rounded-pill"><?= ucfirst($p['status']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end pe-3">
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= base_url('pemeliharaan/detail/' . $p['id']) ?>" class="btn btn-outline-secondary btn-sm" title="Rincian">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <?php if (Auth::hasRole(['admin', 'petugas_sarpras'])): ?>
                                                <a href="<?= base_url('pemeliharaan/edit/' . $p['id']) ?>" class="btn btn-outline-secondary btn-sm" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. RIWAYAT TIKET LAPORAN KERUSAKAN TERKAIT -->
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4 overflow-hidden">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
            <div>
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-exclamation-octagon text-danger me-2"></i>Riwayat Tiket Laporan Kerusakan
                </h6>
                <small class="text-muted">Aduan kerusakan yang dilaporkan oleh civitas akademika untuk fasilitas ini.</small>
            </div>
            <span class="badge bg-light text-dark border px-3 py-1"><?= count($riwayat_laporan ?? []) ?> Laporan</span>
        </div>
        <div class="card-body p-0">
            <?php if (empty($riwayat_laporan)): ?>
                <div class="text-center py-4 text-muted small">
                    <i class="bi bi-check-circle text-success fs-3 d-block mb-1"></i>
                    Tidak ada catatan riwayat laporan kerusakan pada fasilitas ini.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Kode Tiket</th>
                                <th>Pelapor</th>
                                <th>Deskripsi Keluhan</th>
                                <th>Prioritas</th>
                                <th>Status Tiket</th>
                                <th>Tanggal</th>
                                <th class="text-end pe-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($riwayat_laporan as $lap): ?>
                            <tr>
                                <td class="ps-3 font-monospace fw-bold text-primary"><?= htmlspecialchars($lap['kode_laporan']) ?></td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($lap['nama_pelapor']) ?></div>
                                </td>
                                <td>
                                    <div class="text-dark"><?= htmlspecialchars($lap['deskripsi']) ?></div>
                                </td>
                                <td>
                                    <?php 
                                    $prioClass = 'bg-secondary';
                                    if ($lap['prioritas'] === 'darurat') $prioClass = 'bg-danger text-white';
                                    elseif ($lap['prioritas'] === 'tinggi') $prioClass = 'bg-warning text-dark';
                                    elseif ($lap['prioritas'] === 'sedang') $prioClass = 'bg-primary text-white';
                                    elseif ($lap['prioritas'] === 'rendah') $prioClass = 'bg-info text-dark';
                                    ?>
                                    <span class="badge <?= $prioClass ?> px-2 py-1 rounded-pill"><?= ucfirst($lap['prioritas']) ?></span>
                                </td>
                                <td>
                                    <?php 
                                    $statusClass = 'bg-secondary';
                                    if ($lap['status'] === 'selesai') $statusClass = 'bg-success-subtle text-success border border-success-subtle';
                                    elseif ($lap['status'] === 'diproses') $statusClass = 'bg-warning-subtle text-warning-emphasis border border-warning-subtle';
                                    elseif ($lap['status'] === 'diverifikasi') $statusClass = 'bg-info-subtle text-info border border-info-subtle';
                                    elseif ($lap['status'] === 'menunggu') $statusClass = 'bg-secondary-subtle text-secondary';
                                    ?>
                                    <span class="badge <?= $statusClass ?> px-2 py-1 rounded-pill"><?= ucfirst($lap['status']) ?></span>
                                </td>
                                <td class="text-muted"><?= date('d M Y', strtotime($lap['created_at'])) ?></td>
                                <td class="text-end pe-3">
                                    <a href="<?= base_url('laporan/detail/' . $lap['id']) ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                                        <i class="bi bi-eye me-1"></i> Detail
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- MODAL CEPAT: UBAH KONDISI FASILITAS (ADMIN & PETUGAS) -->
<?php if (Auth::hasRole(['admin', 'petugas_sarpras'])): ?>
<div class="modal fade" id="modalUbahKondisi" tabindex="-1" aria-labelledby="modalUbahKondisiLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom py-3 px-4">
                <h5 class="modal-title fw-bold" id="modalUbahKondisiLabel">
                    <i class="bi bi-arrow-repeat text-warning me-2"></i>Ubah Kondisi Fasilitas
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('fasilitas/updateKondisi/' . $fasilitas['id']) ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="redirect_back" value="detail">
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">
                        Pembaruan status kondisi kelaikan inventaris fisik untuk <strong><?= htmlspecialchars($fasilitas['nama_fasilitas']) ?></strong> (<?= htmlspecialchars($fasilitas['kode_inventaris']) ?>).
                    </p>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Pilih Kondisi Fisik Baru <span class="text-danger">*</span></label>
                        <select class="form-select" name="kondisi" required>
                            <option value="baik" <?= ($fasilitas['kondisi'] === 'baik') ? 'selected' : '' ?>>Baik (Siap Pakai & Berfungsi Normal)</option>
                            <option value="rusak_ringan" <?= ($fasilitas['kondisi'] === 'rusak_ringan') ? 'selected' : '' ?>>Rusak Ringan (Perlu Pemeriksaan/Perbaikan Ringan)</option>
                            <option value="rusak_berat" <?= ($fasilitas['kondisi'] === 'rusak_berat') ? 'selected' : '' ?>>Rusak Berat (Tidak Berfungsi / Perlu Penggantian)</option>
                            <option value="tidak_layak" <?= ($fasilitas['kondisi'] === 'tidak_layak') ? 'selected' : '' ?>>Tidak Layak Pakai (Afkir/Usang)</option>
                            <option value="hilang" <?= ($fasilitas['kondisi'] === 'hilang') ? 'selected' : '' ?>>Hilang</option>
                        </select>
                    </div>

                    <div class="mb-0">
                        <label class="form-label small fw-semibold">Catatan Alasan Perubahan Kondisi</label>
                        <textarea class="form-control" name="catatan" rows="3" placeholder="Contoh: Pemeriksaan berkala tanggal <?= date('d/m/Y') ?> menemukan kerusakan modul kabel..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top py-2 px-4 bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3 btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 btn-sm fw-semibold">
                        <i class="bi bi-check2-circle me-1"></i> Simpan Pembaruan Kondisi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
