<div class="container-fluid p-0">
    <!-- Breadcrumb & Header -->
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
        <div class="card-body p-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2 small">
                    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('gedung') ?>" class="text-decoration-none">Data Gedung</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($gedung['nama_gedung']) ?></li>
                </ol>
            </nav>
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary-subtle text-primary p-3 rounded-4 fs-3">
                        <i class="bi bi-building"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-primary px-3 py-1 rounded-pill font-monospace"><?= htmlspecialchars($gedung['kode_gedung']) ?></span>
                            <?php if ($gedung['status'] === 'aktif'): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">Aktif</span>
                            <?php elseif ($gedung['status'] === 'renovasi'): ?>
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-1">Renovasi</span>
                            <?php else: ?>
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3 py-1">Nonaktif</span>
                            <?php endif; ?>
                        </div>
                        <h4 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($gedung['nama_gedung']) ?></h4>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?= base_url('gedung') ?>" class="btn btn-outline-secondary rounded-pill px-3 py-2 small">
                        <i class="bi bi-arrow-left me-1"></i> Kembali
                    </a>
                    <?php if (Auth::hasRole('admin')): ?>
                    <a href="<?= base_url('gedung/edit/' . $gedung['id']) ?>" class="btn btn-primary rounded-pill px-3 py-2 small fw-semibold">
                        <i class="bi bi-pencil me-1"></i> Edit Gedung
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Informasi Gedung & Ringkasan -->
    <div class="row g-4 mb-4">
        <!-- Detail Info Card -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4 h-100">
                <h6 class="fw-bold text-dark border-bottom pb-3 mb-3">
                    <i class="bi bi-info-circle text-primary me-2"></i>Informasi Fisik Gedung
                </h6>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="text-muted small">Kode Gedung:</div>
                        <div class="fw-bold text-dark font-monospace"><?= htmlspecialchars($gedung['kode_gedung']) ?></div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small">Nama Gedung:</div>
                        <div class="fw-bold text-dark"><?= htmlspecialchars($gedung['nama_gedung']) ?></div>
                    </div>
                    <div class="col-12">
                        <div class="text-muted small">Lokasi / Alamat:</div>
                        <div class="fw-semibold text-dark"><i class="bi bi-geo-alt text-danger me-1"></i><?= htmlspecialchars($gedung['lokasi'] ?: '-') ?></div>
                    </div>
                    <div class="col-12">
                        <div class="text-muted small">Keterangan / Deskripsi:</div>
                        <div class="text-dark bg-light p-3 rounded-3 mt-1 small">
                            <?= nl2br(htmlspecialchars($gedung['keterangan'] ?: $gedung['deskripsi'] ?: 'Tidak ada keterangan tambahan.')) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metric Counter Card -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4 h-100 d-flex flex-column justify-content-between">
                <div>
                    <h6 class="fw-bold text-dark border-bottom pb-3 mb-3">
                        <i class="bi bi-graph-up text-primary me-2"></i>Kapasitas & Struktur
                    </h6>
                    <div class="d-flex align-items-center gap-3 mb-3 p-3 bg-light rounded-3">
                        <div class="bg-primary text-white p-3 rounded-circle">
                            <i class="bi bi-layers fs-4"></i>
                        </div>
                        <div>
                            <div class="fs-4 fw-bold text-dark"><?= $gedung['jumlah_lantai'] ?></div>
                            <div class="text-muted small">Total Lantai Fisik</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3">
                        <div class="bg-success text-white p-3 rounded-circle">
                            <i class="bi bi-door-open fs-4"></i>
                        </div>
                        <div>
                            <div class="fs-4 fw-bold text-dark"><?= count($ruangan_list) ?></div>
                            <div class="text-muted small">Ruangan Terdaftar</div>
                        </div>
                    </div>
                </div>
                <?php if (Auth::hasRole(['admin', 'petugas_sarpras'])): ?>
                <div class="pt-3 border-top mt-3">
                    <a href="<?= base_url('ruangan/tambah') ?>" class="btn btn-outline-primary w-100 rounded-pill small py-2">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Ruangan Baru
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Daftar Ruangan di Gedung Ini -->
    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
        <div class="card-header bg-white p-4 border-0 pb-0 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold text-dark mb-1">Daftar Ruangan di <?= htmlspecialchars($gedung['nama_gedung']) ?></h5>
                <p class="text-muted small mb-0">Total <?= count($ruangan_list) ?> unit ruangan dan lab yang berada pada gedung ini.</p>
            </div>
            <a href="<?= base_url('ruangan?gedung_id=' . $gedung['id']) ?>" class="btn btn-sm btn-light border rounded-pill px-3">
                <i class="bi bi-box-arrow-up-right me-1"></i> Buka di Modul Ruangan
            </a>
        </div>
        <div class="card-body p-0 mt-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light text-secondary">
                        <tr>
                            <th class="ps-4" style="width: 60px;">No</th>
                            <th>Kode Ruangan</th>
                            <th>Nama Ruangan</th>
                            <th>Lantai</th>
                            <th>Jenis Ruangan</th>
                            <th>Kapasitas</th>
                            <th>Fasilitas</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($ruangan_list)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                Belum ada data ruangan yang didaftarkan untuk gedung ini.
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($ruangan_list as $idx => $r): ?>
                            <tr>
                                <td class="ps-4 text-muted"><?= $idx + 1 ?></td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary font-monospace px-2 py-1 rounded-pill fw-bold">
                                        <?= htmlspecialchars($r['kode_ruangan']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= base_url('ruangan/detail/' . $r['id']) ?>" class="fw-bold text-dark text-decoration-none">
                                        <?= htmlspecialchars($r['nama_ruangan']) ?>
                                    </a>
                                </td>
                                <td>Lantai <?= $r['lantai'] ?></td>
                                <td><span class="badge bg-secondary-subtle text-secondary"><?= htmlspecialchars($r['jenis_ruangan']) ?></span></td>
                                <td><?= $r['kapasitas'] ?> Orang</td>
                                <td>
                                    <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill">
                                        <?= $r['total_fasilitas'] ?> Unit
                                    </span>
                                </td>
                                <td>
                                    <?php if ($r['status'] === 'tersedia'): ?>
                                        <span class="badge bg-success">Tersedia</span>
                                    <?php elseif ($r['status'] === 'digunakan'): ?>
                                        <span class="badge bg-warning text-dark">Digunakan</span>
                                    <?php elseif ($r['status'] === 'perbaikan'): ?>
                                        <span class="badge bg-danger">Perbaikan</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="<?= base_url('ruangan/detail/' . $r['id']) ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                        Lihat <i class="bi bi-chevron-right ms-1"></i>
                                    </a>
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
