<div class="container-fluid p-0">
    <!-- Header Modul -->
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <?php if ($is_pengguna): ?>
                            <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-1 rounded-pill small">
                                <i class="bi bi-eye me-1"></i> Informasi Publik
                            </span>
                        <?php else: ?>
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-1 rounded-pill small">
                                <i class="bi bi-tools me-1"></i> Manajemen Sarpras
                            </span>
                        <?php endif; ?>

                        <?php if (!empty($selected_fasilitas)): ?>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 rounded-pill small">
                                <i class="bi bi-box-seam me-1"></i> Histori: <?= htmlspecialchars($selected_fasilitas['nama_fasilitas']) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <h4 class="fw-bold mb-1 text-dark">Pemeliharaan & Riwayat Fasilitas</h4>
                    <p class="text-muted small mb-0">
                        <?php if ($is_pengguna): ?>
                            Informasi pemeliharaan, servis rutin, dan status kesiapan fasilitas kampus yang dipublikasikan.
                        <?php else: ?>
                            Pencatatan pemeriksaan berkala, tindakan perbaikan darurat, pergantian suku cadang, dan pembaruan kondisi inventaris.
                        <?php endif; ?>
                    </p>
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <?php if (!empty($selected_fasilitas)): ?>
                        <a href="<?= base_url('fasilitas/detail/' . $selected_fasilitas['id']) ?>" class="btn btn-outline-secondary rounded-pill px-3 btn-sm">
                            <i class="bi bi-arrow-left me-1"></i> Kembali ke Detail Fasilitas
                        </a>
                    <?php endif; ?>

                    <?php if (Auth::hasRole(['admin', 'petugas_sarpras'])): ?>
                        <a href="<?= base_url('pemeliharaan/tambah' . (!empty($fasilitas_id) ? '?fasilitas_id=' . $fasilitas_id : '')) ?>" 
                           class="btn btn-primary rounded-pill px-3 py-2 fw-semibold btn-sm shadow-sm">
                            <i class="bi bi-plus-lg me-1"></i> + Catat Pemeliharaan
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- 1. KARTU PERHITUNGAN SEDERHANA STATISTIK -->
    <div class="row g-3 mb-4">
        <!-- Total Pemeliharaan -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-body p-3 p-xl-4 d-flex align-items-center gap-3">
                    <div class="rounded-4 bg-primary-subtle text-primary p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="bi bi-tools fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Total Pemeliharaan</div>
                        <div class="fs-4 fw-bold text-dark mb-0"><?= number_format($stats['total_pemeliharaan']) ?> <span class="fs-6 fw-normal text-muted">Aktivitas</span></div>
                        <small class="text-success"><i class="bi bi-check2-all me-1"></i><?= $stats['selesai'] ?> Selesai, <?= $stats['proses'] ?> Berjalan</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Biaya Pemeliharaan -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-body p-3 p-xl-4 d-flex align-items-center gap-3">
                    <div class="rounded-4 bg-success-subtle text-success p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="bi bi-wallet2 fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Total Biaya</div>
                        <div class="fs-4 fw-bold text-dark mb-0">Rp <?= number_format($stats['total_biaya'], 0, ',', '.') ?></div>
                        <small class="text-muted">Total anggaran terserap</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Jumlah Kasus Kerusakan -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-body p-3 p-xl-4 d-flex align-items-center gap-3">
                    <div class="rounded-4 bg-danger-subtle text-danger p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="bi bi-exclamation-diamond-fill fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Jumlah Kerusakan</div>
                        <div class="fs-4 fw-bold text-dark mb-0"><?= number_format($stats['jumlah_kerusakan']) ?> <span class="fs-6 fw-normal text-muted">Kasus</span></div>
                        <small class="text-danger"><?= $stats['perbaikan'] ?> tindakan perbaikan</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Distribusi Tindakan -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-body p-3 p-xl-4">
                    <div class="text-muted small fw-semibold mb-2">Tindakan Pemeliharaan</div>
                    <div class="d-flex flex-wrap gap-1">
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small" title="Pemeliharaan Rutin">
                            Rutin: <?= $stats['rutin'] ?>
                        </span>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill small" title="Perbaikan">
                            Perbaikan: <?= $stats['perbaikan'] ?>
                        </span>
                        <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill small" title="Pemeriksaan">
                            Periksa: <?= $stats['pemeriksaan'] ?>
                        </span>
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill small" title="Penggantian">
                            Ganti: <?= $stats['penggantian'] ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. FILTER & PENCARIAN -->
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
        <div class="card-body p-3 p-lg-4">
            <form action="<?= base_url('pemeliharaan') ?>" method="GET" class="row g-2 align-items-center">
                <!-- Keyword Pencarian -->
                <div class="col-md-4 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" class="form-control bg-light border-start-0" name="search" 
                               value="<?= htmlspecialchars($search) ?>" placeholder="Cari kode, fasilitas, teknisi...">
                    </div>
                </div>

                <!-- Filter Fasilitas -->
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <select class="form-select form-select-sm" name="fasilitas_id">
                        <option value="">-- Semua Fasilitas --</option>
                        <?php foreach ($fasilitas_list as $f): ?>
                            <option value="<?= $f['id'] ?>" <?= ($fasilitas_id == $f['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($f['nama_fasilitas']) ?> (<?= htmlspecialchars($f['kode_inventaris']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filter Jenis Tindakan -->
                <div class="col-sm-6 col-md-4 col-lg-2">
                    <select class="form-select form-select-sm" name="jenis_tindakan">
                        <option value="">-- Semua Tindakan --</option>
                        <option value="Pemeriksaan" <?= ($jenis_tindakan === 'Pemeriksaan') ? 'selected' : '' ?>>Pemeriksaan</option>
                        <option value="Perbaikan" <?= ($jenis_tindakan === 'Perbaikan') ? 'selected' : '' ?>>Perbaikan</option>
                        <option value="Penggantian" <?= ($jenis_tindakan === 'Penggantian') ? 'selected' : '' ?>>Penggantian</option>
                        <option value="Pemeliharaan Rutin" <?= ($jenis_tindakan === 'Pemeliharaan Rutin') ? 'selected' : '' ?>>Pemeliharaan Rutin</option>
                    </select>
                </div>

                <!-- Filter Status -->
                <div class="col-sm-6 col-md-4 col-lg-2">
                    <select class="form-select form-select-sm" name="status">
                        <option value="">-- Semua Status --</option>
                        <option value="selesai" <?= ($status === 'selesai') ? 'selected' : '' ?>>Selesai</option>
                        <option value="sedang_dikerjakan" <?= ($status === 'sedang_dikerjakan') ? 'selected' : '' ?>>Sedang Dikerjakan</option>
                        <option value="dijadwalkan" <?= ($status === 'dijadwalkan') ? 'selected' : '' ?>>Dijadwalkan</option>
                        <option value="batal" <?= ($status === 'batal') ? 'selected' : '' ?>>Batal</option>
                    </select>
                </div>

                <!-- Tombol Aksi Filter -->
                <div class="col-sm-6 col-md-4 col-lg-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill w-100 fw-semibold">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    <?php if (!empty($search) || !empty($fasilitas_id) || !empty($jenis_tindakan) || !empty($status) || !empty($tanggal_dari) || !empty($tanggal_sampai)): ?>
                        <a href="<?= base_url('pemeliharaan') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3" title="Reset Filter">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>

            <!-- Indikator Filter Fasilitas Aktif -->
            <?php if (!empty($selected_fasilitas)): ?>
                <div class="alert alert-info py-2 px-3 mt-3 mb-0 rounded-3 small d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-info-circle me-1"></i>
                        Menampilkan histori pemeliharaan untuk: <strong><?= htmlspecialchars($selected_fasilitas['nama_fasilitas']) ?></strong> 
                        (Kode: <?= htmlspecialchars($selected_fasilitas['kode_inventaris']) ?> - Ruang: <?= htmlspecialchars($selected_fasilitas['nama_ruangan']) ?>).
                    </div>
                    <div>
                        <a href="<?= base_url('fasilitas/detail/' . $selected_fasilitas['id']) ?>" class="btn btn-sm btn-light border rounded-pill px-2 py-0 me-1">
                            Lihat Detail Fasilitas
                        </a>
                        <a href="<?= base_url('pemeliharaan') ?>" class="text-danger small text-decoration-none">
                            <i class="bi bi-x-circle me-1"></i> Hapus Filter
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 3. TABEL DATA PEMELIHARAAN -->
    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="bi bi-table me-2 text-primary"></i>Daftar Riwayat Aktivitas Pemeliharaan
            </h6>
            <span class="badge bg-light text-dark border px-3 py-1 small">Total <?= number_format($total_rows) ?> Data</span>
        </div>

        <div class="card-body p-0">
            <?php if (empty($pemeliharaan_list)): ?>
                <div class="text-center py-5">
                    <div class="rounded-circle bg-light d-inline-flex p-4 text-muted mb-3">
                        <i class="bi bi-tools fs-1 text-secondary"></i>
                    </div>
                    <h6 class="fw-bold text-dark">Tidak Ada Data Pemeliharaan Ditemukan</h6>
                    <p class="text-muted small mb-3">Tidak ada aktivitas pemeliharaan yang cocok dengan kriteria filter saat ini.</p>
                    <?php if (Auth::hasRole(['admin', 'petugas_sarpras'])): ?>
                        <a href="<?= base_url('pemeliharaan/tambah' . (!empty($fasilitas_id) ? '?fasilitas_id=' . $fasilitas_id : '')) ?>" class="btn btn-primary rounded-pill px-4 btn-sm fw-semibold shadow-sm">
                            <i class="bi bi-plus-lg me-1"></i> Catat Pemeliharaan Baru
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Kode & Tanggal</th>
                                <th>Fasilitas / Ruangan</th>
                                <th>Jenis Tindakan</th>
                                <th>Perubahan Kondisi</th>
                                <th>Deskripsi & Teknisi</th>
                                <th>Biaya</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pemeliharaan_list as $p): 
                                $tglDisplay = !empty($p['tanggal']) ? $p['tanggal'] : $p['tanggal_mulai'];
                                $badgeTindakanClass = 'bg-secondary';
                                if ($p['jenis_tindakan'] === 'Perbaikan') $badgeTindakanClass = 'bg-danger text-white';
                                elseif ($p['jenis_tindakan'] === 'Pemeriksaan') $badgeTindakanClass = 'bg-info text-dark';
                                elseif ($p['jenis_tindakan'] === 'Penggantian') $badgeTindakanClass = 'bg-warning text-dark';
                                elseif ($p['jenis_tindakan'] === 'Pemeliharaan Rutin') $badgeTindakanClass = 'bg-success text-white';

                                $badgeSebelum = ($p['kondisi_sebelum'] ?? 'baik') === 'baik' ? 'badge bg-success-subtle text-success' : 'badge bg-warning-subtle text-warning-emphasis';
                                $badgeSesudah = ($p['kondisi_sesudah'] ?? 'baik') === 'baik' ? 'badge bg-success-subtle text-success' : 'badge bg-warning-subtle text-warning-emphasis';
                            ?>
                            <tr>
                                <td class="ps-4">
                                    <a href="<?= base_url('pemeliharaan/detail/' . $p['id']) ?>" class="fw-bold font-monospace text-primary text-decoration-none">
                                        <?= htmlspecialchars($p['kode_pemeliharaan']) ?>
                                    </a>
                                    <div class="text-muted small mt-1">
                                        <i class="bi bi-calendar3 me-1"></i><?= date('d M Y', strtotime($tglDisplay)) ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">
                                        <a href="<?= base_url('fasilitas/detail/' . $p['fasilitas_id']) ?>" class="text-dark text-decoration-none hover-primary">
                                            <?= htmlspecialchars($p['nama_fasilitas']) ?>
                                        </a>
                                    </div>
                                    <small class="text-muted">
                                        <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($p['nama_ruangan']) ?> (<?= htmlspecialchars($p['nama_gedung']) ?>)
                                    </small>
                                </td>
                                <td>
                                    <span class="badge <?= $badgeTindakanClass ?> px-3 py-1 rounded-pill fw-semibold">
                                        <?= htmlspecialchars($p['jenis_tindakan']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <span class="<?= $badgeSebelum ?>"><?= ucfirst(str_replace('_', ' ', $p['kondisi_sebelum'] ?? 'baik')) ?></span>
                                        <i class="bi bi-arrow-right text-muted small"></i>
                                        <span class="<?= $badgeSesudah ?>"><?= ucfirst(str_replace('_', ' ', $p['kondisi_sesudah'] ?? 'baik')) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark text-truncate" style="max-width: 220px;" title="<?= htmlspecialchars($p['deskripsi'] ?? '') ?>">
                                        <?= htmlspecialchars($p['deskripsi'] ?? $p['keterangan'] ?? '-') ?>
                                    </div>
                                    <small class="text-muted">
                                        <i class="bi bi-person me-1"></i><?= htmlspecialchars($p['nama_petugas']) ?>
                                    </small>
                                </td>
                                <td>
                                    <span class="fw-bold text-success">
                                        Rp <?= number_format($p['biaya'], 0, ',', '.') ?>
                                    </span>
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

                                    <?php if (!$is_pengguna): ?>
                                        <div class="mt-1">
                                            <?php if (isset($p['tampil_publik']) && $p['tampil_publik'] == 1): ?>
                                                <span class="badge bg-light text-muted border" style="font-size: 0.65rem;"><i class="bi bi-eye me-1"></i>Publik</span>
                                            <?php else: ?>
                                                <span class="badge bg-dark-subtle text-dark border" style="font-size: 0.65rem;"><i class="bi bi-eye-slash me-1"></i>Internal</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group btn-group-sm">
                                        <!-- Tombol Detail -->
                                        <a href="<?= base_url('pemeliharaan/detail/' . $p['id']) ?>" 
                                           class="btn btn-outline-secondary" title="Lihat Rincian">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        <?php if (Auth::hasRole(['admin', 'petugas_sarpras'])): ?>
                                            <!-- Tombol Edit -->
                                            <a href="<?= base_url('pemeliharaan/edit/' . $p['id']) ?>" 
                                               class="btn btn-outline-secondary" title="Edit Riwayat">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        <?php endif; ?>

                                        <?php if (Auth::hasRole('admin')): ?>
                                            <!-- Tombol Hapus (Admin) -->
                                            <button type="button" class="btn btn-outline-danger" 
                                                    data-bs-toggle="modal" data-bs-target="#modalHapus<?= $p['id'] ?>" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Modal Konfirmasi Hapus (Admin) -->
                                    <?php if (Auth::hasRole('admin')): ?>
                                    <div class="modal fade" id="modalHapus<?= $p['id'] ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-sm">
                                            <div class="modal-content rounded-4 border-0 shadow">
                                                <div class="modal-body p-4 text-center">
                                                    <div class="rounded-circle bg-danger-subtle text-danger d-inline-flex p-3 mb-3">
                                                        <i class="bi bi-exclamation-triangle-fill fs-3"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-1">Hapus Riwayat?</h6>
                                                    <p class="text-muted small mb-4">
                                                        Catatan pemeliharaan <strong><?= htmlspecialchars($p['kode_pemeliharaan']) ?></strong> akan dihapus permanen.
                                                    </p>
                                                    <div class="d-flex justify-content-center gap-2">
                                                        <button type="button" class="btn btn-light rounded-pill px-3 btn-sm" data-bs-dismiss="modal">Batal</button>
                                                        <a href="<?= base_url('pemeliharaan/hapus/' . $p['id'] . '?csrf_token=' . csrf_token()) ?>" class="btn btn-danger rounded-pill px-3 btn-sm">
                                                            Ya, Hapus
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- 4. PAGINATION -->
                <?php if ($total_pages > 1): ?>
                <div class="card-footer bg-white border-top py-3 px-4 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
                    <small class="text-muted">
                        Menampilkan halaman <strong><?= $page ?></strong> dari <strong><?= $total_pages ?></strong> (Total <?= number_format($total_rows) ?> catatan)
                    </small>

                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm mb-0">
                            <!-- Tombol Sebelumnya -->
                            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= base_url('pemeliharaan?page=' . ($page - 1) . '&search=' . urlencode($search) . '&fasilitas_id=' . $fasilitas_id . '&jenis_tindakan=' . urlencode($jenis_tindakan ?? '') . '&status=' . urlencode($status ?? '')) ?>">
                                    &laquo;
                                </a>
                            </li>

                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <?php if ($i == 1 || $i == $total_pages || ($i >= $page - 1 && $i <= $page + 1)): ?>
                                    <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= base_url('pemeliharaan?page=' . $i . '&search=' . urlencode($search) . '&fasilitas_id=' . $fasilitas_id . '&jenis_tindakan=' . urlencode($jenis_tindakan ?? '') . '&status=' . urlencode($status ?? '')) ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php elseif ($i == $page - 2 || $i == $page + 2): ?>
                                    <li class="page-item disabled"><span class="page-link">&hellip;</span></li>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <!-- Tombol Selanjutnya -->
                            <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= base_url('pemeliharaan?page=' . ($page + 1) . '&search=' . urlencode($search) . '&fasilitas_id=' . $fasilitas_id . '&jenis_tindakan=' . urlencode($jenis_tindakan ?? '') . '&status=' . urlencode($status ?? '')) ?>">
                                    &raquo;
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
