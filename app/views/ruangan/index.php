<div class="container-fluid p-0">
    <!-- Breadcrumb & Page Header -->
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
        <div class="card-body p-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2 small">
                    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Data Ruangan</li>
                </ol>
            </nav>
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <h4 class="fw-bold mb-1 text-dark">Master Data Ruangan & Laboratorium</h4>
                    <p class="text-muted small mb-0">Informasi ruang kelas teori, lab komputer, lab AI, ruang dosen, dan ruang rapat kampus.</p>
                </div>
                <!-- Tombol Tambah: Hanya untuk Admin dan Petugas Sarpras -->
                <?php if (Auth::hasRole(['admin', 'petugas_sarpras'])): ?>
                <div>
                    <a href="<?= base_url('ruangan/tambah') ?>" class="btn btn-primary rounded-pill px-3 py-2 fw-semibold small shadow-sm">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Ruangan
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
        <div class="card-body p-3">
            <form method="GET" action="<?= base_url('ruangan') ?>" class="row g-2 align-items-center">
                <!-- Search Keyword -->
                <div class="col-md-4 col-lg-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control bg-light border-start-0" 
                               placeholder="Cari kode atau nama ruangan..." 
                               value="<?= htmlspecialchars($search ?? '') ?>">
                    </div>
                </div>

                <!-- Filter Gedung -->
                <div class="col-md-3 col-lg-3">
                    <select name="gedung_id" class="form-select form-select-sm bg-light">
                        <option value="">-- Semua Gedung Kampus --</option>
                        <?php foreach ($gedung_list as $g): ?>
                            <option value="<?= $g['id'] ?>" <?= ((string)$gedung_id === (string)$g['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($g['nama_gedung']) ?> (<?= htmlspecialchars($g['kode_gedung']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filter Jenis Ruangan -->
                <div class="col-md-3 col-lg-3">
                    <select name="jenis_ruangan" class="form-select form-select-sm bg-light">
                        <option value="">-- Semua Jenis Ruangan --</option>
                        <?php 
                        $jenisOptions = ['Kelas', 'Laboratorium', 'Ruang Dosen', 'Ruang Administrasi', 'Ruang Rapat', 'Ruang Seminar', 'Lainnya'];
                        foreach ($jenisOptions as $opt): 
                        ?>
                            <option value="<?= $opt ?>" <?= ($jenis_ruangan === $opt) ? 'selected' : '' ?>><?= $opt ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Action Button -->
                <div class="col-auto d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    <?php if (!empty($search) || !empty($gedung_id) || !empty($jenis_ruangan)): ?>
                        <a href="<?= base_url('ruangan') ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3" title="Reset Semua Filter">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                        </a>
                    <?php endif; ?>
                </div>

                <?php if (!empty($search) || !empty($gedung_id) || !empty($jenis_ruangan)): ?>
                <div class="col-12 mt-2">
                    <span class="badge bg-primary-subtle text-primary rounded-pill py-2 px-3 small">
                        Ditemukan: <?= $total_rows ?> ruangan sesuai kriteria filter
                    </span>
                </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Data Table Ruangan -->
    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light text-secondary">
                        <tr>
                            <th class="ps-4" style="width: 50px;">No</th>
                            <th style="width: 130px;">Kode Ruangan</th>
                            <th>Nama Ruangan</th>
                            <th>Lokasi Gedung & Lantai</th>
                            <th>Jenis Ruangan</th>
                            <th class="text-center" style="width: 100px;">Kapasitas</th>
                            <th class="text-center" style="width: 130px;">Fasilitas</th>
                            <th class="text-center" style="width: 110px;">Status</th>
                            <th class="text-end pe-4" style="width: 160px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($ruangan_list)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <div class="py-4">
                                    <i class="bi bi-door-closed fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                    <span class="fw-semibold">Tidak ada data ruangan yang cocok dengan filter atau pencarian Anda.</span>
                                    <?php if (!empty($search) || !empty($gedung_id) || !empty($jenis_ruangan)): ?>
                                        <div class="mt-2">
                                            <a href="<?= base_url('ruangan') ?>" class="btn btn-sm btn-outline-primary rounded-pill">Reset Filter</a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php 
                            $startNumber = (($page - 1) * $per_page) + 1;
                            foreach ($ruangan_list as $index => $r): 
                            ?>
                            <tr>
                                <td class="ps-4 text-muted"><?= $startNumber + $index ?></td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary font-monospace px-2 py-1 rounded-pill fw-bold">
                                        <?= htmlspecialchars($r['kode_ruangan']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= base_url('ruangan/detail/' . $r['id']) ?>" class="fw-bold text-dark text-decoration-none">
                                        <?= htmlspecialchars($r['nama_ruangan']) ?>
                                    </a>
                                    <?php if (!empty($r['keterangan'])): ?>
                                        <div class="text-muted small text-truncate" style="max-width: 220px; font-size: 0.75rem;">
                                            <?= htmlspecialchars($r['keterangan']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">
                                        <i class="bi bi-building me-1 text-secondary"></i><?= htmlspecialchars($r['nama_gedung']) ?>
                                    </div>
                                    <div class="text-muted small" style="font-size: 0.75rem;">
                                        <i class="bi bi-layers me-1"></i>Lantai <?= $r['lantai'] ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-1">
                                        <?= htmlspecialchars($r['jenis_ruangan']) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="fw-semibold text-dark"><?= $r['kapasitas'] ?></span>
                                    <small class="text-muted d-block" style="font-size: 0.7rem;">Orang</small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-3 py-1">
                                        <i class="bi bi-box-seam me-1"></i> <?= $r['total_fasilitas'] ?> Unit
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php if ($r['status'] === 'tersedia'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">Tersedia</span>
                                    <?php elseif ($r['status'] === 'digunakan'): ?>
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-1">Digunakan</span>
                                    <?php elseif ($r['status'] === 'perbaikan'): ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1">Perbaikan</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3 py-1">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <!-- Detail: Semua Pengguna -->
                                        <a href="<?= base_url('ruangan/detail/' . $r['id']) ?>" class="btn btn-outline-secondary rounded-pill me-1" title="Lihat Detail Ruangan">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        <!-- Edit: Admin & Petugas Sarpras -->
                                        <?php if (Auth::hasRole(['admin', 'petugas_sarpras'])): ?>
                                        <a href="<?= base_url('ruangan/edit/' . $r['id']) ?>" class="btn btn-outline-primary rounded-pill me-1" title="Ubah Ruangan">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <?php endif; ?>

                                        <!-- Hapus: Khusus Admin dengan validasi relasi -->
                                        <?php if (Auth::hasRole('admin')): ?>
                                        <button type="button" class="btn btn-outline-danger rounded-pill btn-delete-ruangan" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalDeleteRuangan" 
                                                data-id="<?= $r['id'] ?>" 
                                                data-nama="<?= htmlspecialchars($r['nama_ruangan']) ?>"
                                                data-kode="<?= htmlspecialchars($r['kode_ruangan']) ?>"
                                                data-fasilitas="<?= $r['total_fasilitas'] ?>"
                                                title="Hapus Ruangan">
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
            <?php 
            $queryUrl = '';
            if (!empty($search)) $queryUrl .= '&search=' . urlencode($search);
            if (!empty($gedung_id)) $queryUrl .= '&gedung_id=' . urlencode($gedung_id);
            if (!empty($jenis_ruangan)) $queryUrl .= '&jenis_ruangan=' . urlencode($jenis_ruangan);
            ?>
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center p-3 border-top gap-2">
                <div class="text-muted small">
                    Menampilkan <strong><?= count($ruangan_list) ?></strong> dari <strong><?= $total_rows ?></strong> data ruangan (Halaman <?= $page ?> / <?= $total_pages ?>)
                </div>
                <nav aria-label="Pagination Ruangan">
                    <ul class="pagination pagination-sm mb-0">
                        <!-- Prev Link -->
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link rounded-pill me-1" href="<?= base_url('ruangan?page=' . ($page - 1) . $queryUrl) ?>">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>

                        <!-- Page Numbers -->
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= ($i === $page) ? 'active' : '' ?>">
                                <a class="page-link rounded-circle mx-1 text-center" style="width: 32px; height: 32px; line-height: 18px;" 
                                   href="<?= base_url('ruangan?page=' . $i . $queryUrl) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <!-- Next Link -->
                        <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                            <a class="page-link rounded-pill ms-1" href="<?= base_url('ruangan?page=' . ($page + 1) . $queryUrl) ?>">
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

<!-- Modal Konfirmasi Hapus Ruangan (Admin Saja) -->
<?php if (Auth::hasRole('admin')): ?>
<div class="modal fade" id="modalDeleteRuangan" tabindex="-1" aria-labelledby="modalDeleteRuanganLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
            <div class="modal-header border-0 bg-danger bg-opacity-10 text-danger p-4 pb-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                    <h5 class="modal-title fw-bold" id="modalDeleteRuanganLabel">Konfirmasi Hapus Ruangan</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 pt-2">
                <p class="text-muted small mb-2">Anda akan menghapus data ruangan berikut:</p>
                <div class="p-3 bg-light rounded-3 mb-3">
                    <div class="fw-bold text-dark fs-6" id="deleteNamaRuangan">-</div>
                    <div class="small text-muted font-monospace mt-1" id="deleteKodeRuangan">-</div>
                    <div class="small mt-2" id="deleteInfoFasilitas">Memeriksa relasi...</div>
                </div>
                <div class="alert alert-warning small border-0 bg-warning-subtle text-dark py-2 px-3 rounded-3" id="deleteRuanganAlert">
                    <i class="bi bi-shield-exclamation me-1"></i>
                    Penghapusan akan ditolak otomatis jika ruangan masih memiliki fasilitas atau riwayat laporan kerusakan terkait.
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0 d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-light rounded-pill px-4 small" data-bs-dismiss="modal">Batal</button>
                <a href="#" id="btnConfirmDeleteRuangan" class="btn btn-danger rounded-pill px-4 small fw-semibold">
                    <i class="bi bi-trash me-1"></i> Ya, Hapus Ruangan
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const deleteButtons = document.querySelectorAll('.btn-delete-ruangan');
    const deleteNamaRuangan = document.getElementById('deleteNamaRuangan');
    const deleteKodeRuangan = document.getElementById('deleteKodeRuangan');
    const deleteInfoFasilitas = document.getElementById('deleteInfoFasilitas');
    const deleteRuanganAlert = document.getElementById('deleteRuanganAlert');
    const btnConfirmDelete = document.getElementById('btnConfirmDeleteRuangan');

    deleteButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-id');
            const nama = btn.getAttribute('data-nama');
            const kode = btn.getAttribute('data-kode');
            const fasilitas = parseInt(btn.getAttribute('data-fasilitas') || '0', 10);

            deleteNamaRuangan.textContent = nama;
            deleteKodeRuangan.textContent = kode;
            btnConfirmDelete.href = '<?= base_url('ruangan/hapus/') ?>' + id + '?csrf_token=<?= csrf_token() ?>';

            if (fasilitas > 0) {
                deleteInfoFasilitas.innerHTML = '<span class="text-danger fw-semibold"><i class="bi bi-x-circle me-1"></i>Memiliki ' + fasilitas + ' unit fasilitas terpasang.</span>';
                deleteRuanganAlert.classList.remove('bg-warning-subtle', 'text-dark');
                deleteRuanganAlert.classList.add('bg-danger-subtle', 'text-danger');
                deleteRuanganAlert.innerHTML = '<i class="bi bi-exclamation-octagon-fill me-1"></i> <strong>Perhatian:</strong> Ruangan ini masih memiliki fasilitas inventaris. Penghapusan akan otomatis ditolak untuk menjaga integritas data.';
            } else {
                deleteInfoFasilitas.innerHTML = '<span class="text-success"><i class="bi bi-check-circle me-1"></i>Tidak ada fasilitas terpasang (Aman dihapus).</span>';
                deleteRuanganAlert.classList.remove('bg-danger-subtle', 'text-danger');
                deleteRuanganAlert.classList.add('bg-warning-subtle', 'text-dark');
                deleteRuanganAlert.innerHTML = '<i class="bi bi-info-circle me-1"></i> Tindakan ini tidak dapat dibatalkan.';
            }
        });
    });
});
</script>
<?php endif; ?>
