<div class="container-fluid p-0">
    <!-- Breadcrumb & Page Header -->
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
        <div class="card-body p-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2 small">
                    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Data Gedung</li>
                </ol>
            </nav>
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <h4 class="fw-bold mb-1 text-dark">Master Data Gedung Kampus</h4>
                    <p class="text-muted small mb-0">Kelola informasi gedung perkuliahan, laboratorium, dan rektorat STMIK IKMI Cirebon.</p>
                </div>
                <?php if (Auth::hasRole('admin')): ?>
                <div>
                    <a href="<?= base_url('gedung/tambah') ?>" class="btn btn-primary rounded-pill px-3 py-2 fw-semibold small shadow-sm">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Gedung
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
        <div class="card-body p-3">
            <form method="GET" action="<?= base_url('gedung') ?>" class="row g-2 align-items-center">
                <div class="col-md-5 col-lg-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control bg-light border-start-0" 
                               placeholder="Cari kode, nama, lokasi..." 
                               value="<?= htmlspecialchars($search ?? '') ?>">
                        <?php if (!empty($search)): ?>
                            <a href="<?= base_url('gedung') ?>" class="btn btn-outline-secondary" title="Reset Pencarian">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3">
                        <i class="bi bi-funnel me-1"></i> Terapkan
                    </button>
                </div>
                <?php if (!empty($search)): ?>
                <div class="col-auto">
                    <span class="badge bg-primary-subtle text-primary rounded-pill py-2 px-3 small">
                        Hasil pencarian: "<?= htmlspecialchars($search) ?>" (<?= $total_rows ?> gedung)
                    </span>
                </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Data Table Gedung -->
    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light text-secondary">
                        <tr>
                            <th class="ps-4" style="width: 60px;">No</th>
                            <th style="width: 130px;">Kode Gedung</th>
                            <th>Nama Gedung</th>
                            <th>Lokasi</th>
                            <th>Keterangan</th>
                            <th class="text-center" style="width: 140px;">Ruangan</th>
                            <th class="text-center" style="width: 110px;">Status</th>
                            <th class="text-end pe-4" style="width: 180px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($gedung_list)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <div class="py-4">
                                    <i class="bi bi-building-exclamation fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                    <span class="fw-semibold">Tidak ada data gedung yang ditemukan.</span>
                                    <?php if (!empty($search)): ?>
                                        <p class="small text-muted mt-1">Coba gunakan kata kunci pencarian yang berbeda.</p>
                                        <a href="<?= base_url('gedung') ?>" class="btn btn-sm btn-outline-primary rounded-pill mt-2">Reset Filter</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php 
                            $startNumber = (($page - 1) * $per_page) + 1;
                            foreach ($gedung_list as $index => $g): 
                            ?>
                            <tr>
                                <td class="ps-4 text-muted"><?= $startNumber + $index ?></td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary font-monospace px-2 py-1 rounded-pill fw-bold">
                                        <?= htmlspecialchars($g['kode_gedung']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= base_url('gedung/detail/' . $g['id']) ?>" class="fw-bold text-dark text-decoration-none hover-primary">
                                        <?= htmlspecialchars($g['nama_gedung']) ?>
                                    </a>
                                    <div class="text-muted small" style="font-size: 0.75rem;">
                                        <i class="bi bi-layers me-1"></i><?= $g['jumlah_lantai'] ?> Lantai
                                    </div>
                                </td>
                                <td>
                                    <div class="text-dark small"><i class="bi bi-geo-alt text-danger me-1"></i><?= htmlspecialchars($g['lokasi'] ?: '-') ?></div>
                                </td>
                                <td>
                                    <small class="text-muted text-truncate d-inline-block" style="max-width: 250px;">
                                        <?= htmlspecialchars($g['keterangan'] ?: $g['deskripsi'] ?: '-') ?>
                                    </small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-3 py-1">
                                        <i class="bi bi-door-open me-1"></i> <?= $g['total_ruangan'] ?> Ruangan
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php if ($g['status'] === 'aktif'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">Aktif</span>
                                    <?php elseif ($g['status'] === 'renovasi'): ?>
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-1">Renovasi</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3 py-1">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="<?= base_url('gedung/detail/' . $g['id']) ?>" class="btn btn-outline-secondary rounded-pill me-1" title="Lihat Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <?php if (Auth::hasRole('admin')): ?>
                                        <a href="<?= base_url('gedung/edit/' . $g['id']) ?>" class="btn btn-outline-primary rounded-pill me-1" title="Ubah Gedung">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger rounded-pill btn-delete-gedung" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalDeleteGedung" 
                                                data-id="<?= $g['id'] ?>" 
                                                data-nama="<?= htmlspecialchars($g['nama_gedung']) ?>"
                                                data-ruangan="<?= $g['total_ruangan'] ?>"
                                                title="Hapus Gedung">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Simple Pagination Component -->
            <?php if ($total_pages > 1): ?>
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center p-3 border-top gap-2">
                <div class="text-muted small">
                    Menampilkan <strong><?= count($gedung_list) ?></strong> dari <strong><?= $total_rows ?></strong> data gedung (Halaman <?= $page ?> / <?= $total_pages ?>)
                </div>
                <nav aria-label="Pagination Gedung">
                    <ul class="pagination pagination-sm mb-0">
                        <!-- Prev Link -->
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link rounded-pill me-1" href="<?= base_url('gedung?page=' . ($page - 1) . (!empty($search) ? '&search=' . urlencode($search) : '')) ?>">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>

                        <!-- Page Numbers -->
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= ($i === $page) ? 'active' : '' ?>">
                                <a class="page-link rounded-circle mx-1 text-center" style="width: 32px; height: 32px; line-height: 18px;" 
                                   href="<?= base_url('gedung?page=' . $i . (!empty($search) ? '&search=' . urlencode($search) : '')) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <!-- Next Link -->
                        <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                            <a class="page-link rounded-pill ms-1" href="<?= base_url('gedung?page=' . ($page + 1) . (!empty($search) ? '&search=' . urlencode($search) : '')) ?>">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus Gedung -->
<div class="modal fade" id="modalDeleteGedung" tabindex="-1" aria-labelledby="modalDeleteGedungLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
            <div class="modal-header border-0 bg-danger bg-opacity-10 text-danger p-4 pb-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                    <h5 class="modal-title fw-bold" id="modalDeleteGedungLabel">Konfirmasi Hapus Gedung</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 pt-2">
                <p class="text-muted small mb-2">Anda akan menghapus data gedung berikut:</p>
                <div class="p-3 bg-light rounded-3 mb-3">
                    <div class="fw-bold text-dark fs-6" id="deleteNamaGedung">-</div>
                    <div class="small text-muted mt-1" id="deleteInfoRuangan">Memeriksa relasi ruangan...</div>
                </div>
                <div class="alert alert-warning small border-0 bg-warning-subtle text-dark py-2 px-3 rounded-3" id="deleteWarningAlert">
                    <i class="bi bi-shield-exclamation me-1"></i>
                    Penghapusan akan ditolak secara otomatis oleh sistem jika gedung ini masih menampung ruangan aktif.
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0 d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-light rounded-pill px-4 small" data-bs-dismiss="modal">Batal</button>
                <a href="#" id="btnConfirmDeleteGedung" class="btn btn-danger rounded-pill px-4 small fw-semibold">
                    <i class="bi bi-trash me-1"></i> Ya, Hapus Gedung
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const deleteButtons = document.querySelectorAll('.btn-delete-gedung');
    const deleteNamaGedung = document.getElementById('deleteNamaGedung');
    const deleteInfoRuangan = document.getElementById('deleteInfoRuangan');
    const deleteWarningAlert = document.getElementById('deleteWarningAlert');
    const btnConfirmDelete = document.getElementById('btnConfirmDeleteGedung');

    deleteButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-id');
            const nama = btn.getAttribute('data-nama');
            const ruangan = parseInt(btn.getAttribute('data-ruangan') || '0', 10);

            deleteNamaGedung.textContent = nama;
            btnConfirmDelete.href = '<?= base_url('gedung/hapus/') ?>' + id + '?csrf_token=<?= csrf_token() ?>';

            if (ruangan > 0) {
                deleteInfoRuangan.innerHTML = '<span class="text-danger fw-semibold"><i class="bi bi-x-circle me-1"></i>Memiliki ' + ruangan + ' ruangan terdaftar.</span>';
                deleteWarningAlert.classList.remove('bg-warning-subtle', 'text-dark');
                deleteWarningAlert.classList.add('bg-danger-subtle', 'text-danger');
                deleteWarningAlert.innerHTML = '<i class="bi bi-exclamation-octagon-fill me-1"></i> <strong>Perhatian:</strong> Gedung ini memiliki ' + ruangan + ' ruangan terkait. Penghapusan akan otomatis dibatalkan untuk menjaga integritas database.';
            } else {
                deleteInfoRuangan.innerHTML = '<span class="text-success"><i class="bi bi-check-circle me-1"></i>Tidak ada ruangan yang terhubung (Aman dihapus).</span>';
                deleteWarningAlert.classList.remove('bg-danger-subtle', 'text-danger');
                deleteWarningAlert.classList.add('bg-warning-subtle', 'text-dark');
                deleteWarningAlert.innerHTML = '<i class="bi bi-info-circle me-1"></i> Tindakan ini tidak dapat dibatalkan.';
            }
        });
    });
});
</script>
