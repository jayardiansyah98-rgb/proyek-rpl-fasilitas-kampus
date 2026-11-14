<div class="container-fluid p-0">
    <!-- Breadcrumb & Header Card -->
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
        <div class="card-body p-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2 small">
                    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Laporan Kerusakan</li>
                </ol>
            </nav>
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-1 rounded-pill small mb-2">
                        <i class="bi bi-exclamation-octagon me-1"></i> Layanan Pengaduan Fasilitas
                    </span>
                    <h4 class="fw-bold mb-1 text-dark">
                        <?= ($role === 'pengguna') ? 'Riwayat Laporan Kerusakan Saya' : 'Daftar Tiket Pengaduan Kerusakan' ?>
                    </h4>
                    <p class="text-muted small mb-0">
                        <?= ($role === 'pengguna') ? 'Pantau status penanganan tiket kerusakan sarana dan prasarana yang telah Anda ajukan.' : 'Kelola, verifikasi, ubah prioritas, dan tangani pengaduan fasilitas dari civitas kampus.' ?>
                    </p>
                </div>
                <div>
                    <a href="<?= base_url('laporan/tambah') ?>" class="btn btn-warning text-dark fw-semibold rounded-pill px-4 py-2 shadow-sm small d-inline-flex align-items-center gap-2">
                        <i class="bi bi-plus-circle-fill fs-5"></i>
                        <span>Buat Laporan Baru</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Status Counters -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-xl-2">
            <a href="<?= base_url('laporan') ?>" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 transition-hover <?= (empty($status)) ? 'border-primary border-2' : '' ?>">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-semibold">Semua Tiket</div>
                            <div class="fs-4 fw-bold text-dark mt-1"><?= $summary['total'] ?></div>
                        </div>
                        <div class="p-2 bg-primary bg-opacity-10 text-primary rounded-3">
                            <i class="bi bi-collection fs-5"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <a href="<?= base_url('laporan?status=menunggu' . (!empty($prioritas) ? '&prioritas=' . urlencode($prioritas) : '') . (!empty($search) ? '&search=' . urlencode($search) : '')) ?>" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 transition-hover <?= ($status === 'menunggu') ? 'border-secondary border-2' : '' ?>">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-semibold">Menunggu</div>
                            <div class="fs-4 fw-bold text-secondary mt-1"><?= $summary['menunggu'] ?></div>
                        </div>
                        <div class="p-2 bg-secondary bg-opacity-10 text-secondary rounded-3">
                            <i class="bi bi-hourglass-split fs-5"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <a href="<?= base_url('laporan?status=diverifikasi' . (!empty($prioritas) ? '&prioritas=' . urlencode($prioritas) : '') . (!empty($search) ? '&search=' . urlencode($search) : '')) ?>" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 transition-hover <?= ($status === 'diverifikasi') ? 'border-info border-2' : '' ?>">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-semibold">Diverifikasi</div>
                            <div class="fs-4 fw-bold text-info mt-1"><?= $summary['diverifikasi'] ?></div>
                        </div>
                        <div class="p-2 bg-info bg-opacity-10 text-info rounded-3">
                            <i class="bi bi-patch-check fs-5"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <a href="<?= base_url('laporan?status=diproses' . (!empty($prioritas) ? '&prioritas=' . urlencode($prioritas) : '') . (!empty($search) ? '&search=' . urlencode($search) : '')) ?>" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 transition-hover <?= ($status === 'diproses') ? 'border-warning border-2' : '' ?>">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-semibold">Diproses</div>
                            <div class="fs-4 fw-bold text-warning mt-1"><?= $summary['diproses'] ?></div>
                        </div>
                        <div class="p-2 bg-warning bg-opacity-10 text-warning rounded-3">
                            <i class="bi bi-tools fs-5"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <a href="<?= base_url('laporan?status=selesai' . (!empty($prioritas) ? '&prioritas=' . urlencode($prioritas) : '') . (!empty($search) ? '&search=' . urlencode($search) : '')) ?>" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 transition-hover <?= ($status === 'selesai') ? 'border-success border-2' : '' ?>">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-semibold">Selesai</div>
                            <div class="fs-4 fw-bold text-success mt-1"><?= $summary['selesai'] ?></div>
                        </div>
                        <div class="p-2 bg-success bg-opacity-10 text-success rounded-3">
                            <i class="bi bi-check-circle-fill fs-5"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <a href="<?= base_url('laporan?status=ditolak' . (!empty($prioritas) ? '&prioritas=' . urlencode($prioritas) : '') . (!empty($search) ? '&search=' . urlencode($search) : '')) ?>" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 transition-hover <?= ($status === 'ditolak') ? 'border-danger border-2' : '' ?>">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-semibold">Ditolak</div>
                            <div class="fs-4 fw-bold text-danger mt-1"><?= $summary['ditolak'] ?></div>
                        </div>
                        <div class="p-2 bg-danger bg-opacity-10 text-danger rounded-3">
                            <i class="bi bi-x-circle-fill fs-5"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Filter & Search Card -->
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
        <div class="card-body p-4">
            <form action="<?= base_url('laporan') ?>" method="GET" class="row g-3 align-items-end">
                <!-- Search Input -->
                <div class="col-lg-4 col-md-6">
                    <label for="search" class="form-label small fw-semibold text-muted mb-1">
                        <i class="bi bi-search me-1"></i>Pencarian Kata Kunci
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" id="search" class="form-control bg-light border-start-0 small" 
                               placeholder="Kode tiket, fasilitas, ruangan, pelapor..." 
                               value="<?= htmlspecialchars($search ?? '') ?>">
                    </div>
                </div>

                <!-- Filter Status -->
                <div class="col-lg-3 col-md-3">
                    <label for="statusFilter" class="form-label small fw-semibold text-muted mb-1">
                        <i class="bi bi-funnel me-1"></i>Status Penanganan
                    </label>
                    <select name="status" id="statusFilter" class="form-select small">
                        <option value="">-- Semua Status --</option>
                        <option value="menunggu" <?= ($status === 'menunggu') ? 'selected' : '' ?>>Menunggu</option>
                        <option value="diverifikasi" <?= ($status === 'diverifikasi') ? 'selected' : '' ?>>Diverifikasi</option>
                        <option value="diproses" <?= ($status === 'diproses') ? 'selected' : '' ?>>Diproses</option>
                        <option value="selesai" <?= ($status === 'selesai') ? 'selected' : '' ?>>Selesai</option>
                        <option value="ditolak" <?= ($status === 'ditolak') ? 'selected' : '' ?>>Ditolak</option>
                    </select>
                </div>

                <!-- Filter Prioritas -->
                <div class="col-lg-3 col-md-3">
                    <label for="prioritasFilter" class="form-label small fw-semibold text-muted mb-1">
                        <i class="bi bi-flag me-1"></i>Tingkat Prioritas
                    </label>
                    <select name="prioritas" id="prioritasFilter" class="form-select small">
                        <option value="">-- Semua Prioritas --</option>
                        <option value="rendah" <?= ($prioritas === 'rendah') ? 'selected' : '' ?>>Rendah</option>
                        <option value="sedang" <?= ($prioritas === 'sedang') ? 'selected' : '' ?>>Sedang</option>
                        <option value="tinggi" <?= ($prioritas === 'tinggi') ? 'selected' : '' ?>>Tinggi</option>
                        <option value="darurat" <?= ($prioritas === 'darurat') ? 'selected' : '' ?>>Darurat</option>
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="col-lg-2 col-md-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-3 py-2 small fw-semibold flex-grow-1 shadow-sm">
                        <i class="bi bi-filter me-1"></i> Terapkan
                    </button>
                    <?php if (!empty($search) || !empty($status) || !empty($prioritas)): ?>
                    <a href="<?= base_url('laporan') ?>" class="btn btn-outline-secondary rounded-pill px-3 py-2 small" title="Reset Filter">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
        <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="bi bi-table text-primary me-2"></i>Daftar Tiket (Total: <?= $total_rows ?> Data)
            </h6>
            <?php if ($total_pages > 1): ?>
            <span class="badge bg-light text-secondary border small">Halaman <?= $page ?> dari <?= $totalPages ?? $total_pages ?></span>
            <?php endif; ?>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" style="width: 140px;">Kode Tiket</th>
                            <th>Fasilitas / Lokasi</th>
                            <th>Deskripsi Masalah</th>
                            <?php if ($role !== 'pengguna'): ?>
                            <th>Pelapor</th>
                            <?php endif; ?>
                            <th>Prioritas</th>
                            <th>Status Penanganan</th>
                            <th>Tanggal</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($laporan_list)): ?>
                            <?php foreach ($laporan_list as $l): ?>
                            <tr>
                                <td class="ps-4">
                                    <a href="<?= base_url('laporan/detail/' . $l['id']) ?>" class="fw-bold font-monospace text-primary text-decoration-none">
                                        <?= htmlspecialchars($l['kode_laporan']) ?>
                                    </a>
                                    <?php if (!empty($l['foto'])): ?>
                                        <span class="badge bg-light text-muted border ms-1" title="Disertai foto bukti">
                                            <i class="bi bi-camera-fill text-primary"></i>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($l['nama_fasilitas']) ?></div>
                                    <small class="text-muted d-block">
                                        <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($l['nama_ruangan']) ?> &bull; <?= htmlspecialchars($l['nama_gedung']) ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="text-dark text-wrap" style="max-width: 260px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                        <?= htmlspecialchars($l['deskripsi']) ?>
                                    </div>
                                    <?php if (!empty($l['catatan_petugas'])): ?>
                                        <div class="text-muted fst-italic mt-1" style="font-size: 0.75rem;">
                                            <i class="bi bi-chat-left-text me-1 text-info"></i><?= htmlspecialchars($l['catatan_petugas']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <?php if ($role !== 'pengguna'): ?>
                                <td>
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($l['pelapor']) ?></div>
                                    <small class="text-secondary"><?= htmlspecialchars($l['nomor_induk'] ?? '') ?> (<?= ucfirst($l['jenis_pengguna'] ?? 'civitas') ?>)</small>
                                </td>
                                <?php endif; ?>
                                <td>
                                    <?php if ($l['prioritas'] === 'darurat'): ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill">
                                            <i class="bi bi-exclamation-triangle-fill me-1"></i>Darurat
                                        </span>
                                    <?php elseif ($l['prioritas'] === 'tinggi'): ?>
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 rounded-pill">
                                            <i class="bi bi-arrow-up-circle-fill me-1"></i>Tinggi
                                        </span>
                                    <?php elseif ($l['prioritas'] === 'sedang'): ?>
                                        <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 rounded-pill">
                                            <i class="bi bi-dash-circle me-1"></i>Sedang
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 rounded-pill">
                                            <i class="bi bi-arrow-down-circle me-1"></i>Rendah
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($l['status'] === 'menunggu'): ?>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-1 rounded-pill">
                                            <i class="bi bi-hourglass me-1"></i>Menunggu
                                        </span>
                                    <?php elseif ($l['status'] === 'diverifikasi'): ?>
                                        <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-1 rounded-pill">
                                            <i class="bi bi-patch-check me-1"></i>Diverifikasi
                                        </span>
                                    <?php elseif ($l['status'] === 'diproses'): ?>
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-1 rounded-pill">
                                            <i class="bi bi-gear-wide-connected me-1"></i>Diproses
                                        </span>
                                    <?php elseif ($l['status'] === 'selesai'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill">
                                            <i class="bi bi-check-circle-fill me-1"></i>Selesai
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1 rounded-pill">
                                            <i class="bi bi-x-circle-fill me-1"></i>Ditolak
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="text-muted small"><?= date('d M Y', strtotime($l['created_at'])) ?></div>
                                    <div class="text-secondary" style="font-size: 0.72rem;"><?= date('H:i', strtotime($l['created_at'])) ?> WIB</div>
                                </td>
                                <td class="text-end pe-4">
    <div class="btn-group btn-group-sm">
        <a href="<?= base_url('laporan/detail/' . $l['id']) ?>"
           class="btn btn-outline-primary rounded-pill px-3 py-1"
           title="Lihat Detail & Timeline">
            <i class="bi bi-eye me-1"></i> Detail
        </a>

        <?php if ($role === 'admin'): ?>
        <form action="<?= base_url('laporan/hapus/' . $l['id']) ?>"
              method="POST"
              class="d-inline"
              onsubmit="return confirm('Apakah Anda yakin ingin menghapus laporan <?= htmlspecialchars($l['kode_laporan'], ENT_QUOTES, 'UTF-8') ?>? Data yang dihapus tidak dapat dikembalikan.');">

            <input type="hidden"
                   name="csrf_token"
                   value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

            <button type="submit"
                    class="btn btn-outline-danger rounded-pill px-3 py-1"
                    title="Hapus Laporan">
                <i class="bi bi-trash me-1"></i> Hapus
            </button>
        </form>
        <?php endif; ?>
    </div>
</td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?= ($role !== 'pengguna') ? '8' : '7' ?>" class="text-center py-5 text-muted">
                                    <div class="mb-3">
                                        <i class="bi bi-inbox fs-1 text-secondary opacity-50"></i>
                                    </div>
                                    <h6 class="fw-semibold text-dark mb-1">Tidak ada tiket laporan kerusakan</h6>
                                    <p class="small text-muted mb-3">
                                        <?= (!empty($search) || !empty($status) || !empty($prioritas)) 
                                            ? 'Tidak ditemukan laporan yang sesuai dengan filter pencarian.' 
                                            : ($role === 'pengguna' ? 'Anda belum pernah mengirim laporan pengaduan fasilitas.' : 'Saat ini belum ada pengaduan kerusakan yang diajukan civitas.') ?>
                                    </p>
                                    <?php if ($role === 'pengguna'): ?>
                                    <a href="<?= base_url('laporan/tambah') ?>" class="btn btn-warning btn-sm rounded-pill px-4 fw-semibold text-dark shadow-sm">
                                        <i class="bi bi-plus-circle me-1"></i> Buat Laporan Baru Sekarang
                                    </a>
                                    <?php elseif (!empty($search) || !empty($status) || !empty($prioritas)): ?>
                                    <a href="<?= base_url('laporan') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i> Bersihkan Filter
                                    </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination Footer -->
        <?php if ($total_pages > 1): ?>
        <div class="card-footer bg-white border-top py-3 px-4">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
                <small class="text-muted">
                    Menampilkan <?= count($laporan_list) ?> dari total <?= $total_rows ?> laporan kerusakan
                </small>
                <nav aria-label="Page navigation">
                    <ul class="pagination pagination-sm mb-0">
                        <!-- Prev -->
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link rounded-start-pill" href="<?= base_url('laporan?page=' . ($page - 1) . (!empty($status) ? '&status=' . urlencode($status) : '') . (!empty($prioritas) ? '&prioritas=' . urlencode($prioritas) : '') . (!empty($search) ? '&search=' . urlencode($search) : '')) ?>">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                        <!-- Numbers -->
                        <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                            <?php if ($p == 1 || $p == $total_pages || ($p >= $page - 1 && $p <= $page + 1)): ?>
                                <li class="page-item <?= ($p == $page) ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= base_url('laporan?page=' . $p . (!empty($status) ? '&status=' . urlencode($status) : '') . (!empty($prioritas) ? '&prioritas=' . urlencode($prioritas) : '') . (!empty($search) ? '&search=' . urlencode($search) : '')) ?>">
                                        <?= $p ?>
                                    </a>
                                </li>
                            <?php elseif ($p == $page - 2 || $p == $page + 2): ?>
                                <li class="page-item disabled"><span class="page-link">&hellip;</span></li>
                            <?php endif; ?>
                        <?php endfor; ?>
                        <!-- Next -->
                        <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                            <a class="page-link rounded-end-pill" href="<?= base_url('laporan?page=' . ($page + 1) . (!empty($status) ? '&status=' . urlencode($status) : '') . (!empty($prioritas) ? '&prioritas=' . urlencode($prioritas) : '') . (!empty($search) ? '&search=' . urlencode($search) : '')) ?>">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
