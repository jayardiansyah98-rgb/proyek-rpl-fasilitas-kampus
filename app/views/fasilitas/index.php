<div class="container-fluid p-0">
    <!-- Breadcrumb & Page Header -->
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
        <div class="card-body p-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2 small">
                    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Data Fasilitas</li>
                </ol>
            </nav>
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <h4 class="fw-bold mb-1 text-dark">Manajemen Fasilitas & Inventaris Ruangan</h4>
                    <p class="text-muted small mb-0">Pendataan sarana, prasarana, peralatan komputer, multimedia, dan aset ruangan STMIK IKMI Cirebon.</p>
                </div>
                <!-- Tombol Tambah: Admin & Petugas Sarpras -->
                <?php if (Auth::hasRole(['admin', 'petugas_sarpras'])): ?>
                <div>
                    <a href="<?= base_url('fasilitas/tambah') ?>" class="btn btn-primary rounded-pill px-3 py-2 fw-semibold small shadow-sm">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Fasilitas
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Filter Multi-Dimensi & Search Toolbar -->
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
        <div class="card-body p-3">
            <form method="GET" action="<?= base_url('fasilitas') ?>" id="filterFasilitasForm" class="row g-2 align-items-center">
                <!-- Search Keyword -->
                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control bg-light border-start-0" 
                               placeholder="Cari kode/nama fasilitas..." 
                               value="<?= htmlspecialchars($search ?? '') ?>">
                    </div>
                </div>

                <!-- Filter Gedung -->
                <div class="col-md-2">
                    <select name="gedung_id" id="filterGedung" class="form-select form-select-sm bg-light">
                        <option value="">-- Semua Gedung --</option>
                        <?php foreach ($gedung_list as $g): ?>
                            <option value="<?= $g['id'] ?>" <?= ((string)$gedung_id === (string)$g['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($g['nama_gedung']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filter Ruangan -->
                <div class="col-md-2">
                    <select name="ruangan_id" id="filterRuangan" class="form-select form-select-sm bg-light">
                        <option value="">-- Semua Ruangan --</option>
                        <?php foreach ($ruangan_list as $r): ?>
                            <option value="<?= $r['id'] ?>" <?= ((string)$ruangan_id === (string)$r['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($r['nama_ruangan']) ?> (Lt. <?= $r['lantai'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filter Kategori -->
                <div class="col-md-2">
                    <select name="kategori_id" class="form-select form-select-sm bg-light">
                        <option value="">-- Semua Kategori --</option>
                        <?php foreach ($kategori_list as $k): ?>
                            <option value="<?= $k['id'] ?>" <?= ((string)$kategori_id === (string)$k['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($k['nama_kategori']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filter Kondisi -->
                <div class="col-md-2">
                    <select name="kondisi" class="form-select form-select-sm bg-light">
                        <option value="">-- Semua Kondisi --</option>
                        <option value="baik" <?= ($kondisi === 'baik') ? 'selected' : '' ?>>Baik</option>
                        <option value="rusak_ringan" <?= ($kondisi === 'rusak_ringan') ? 'selected' : '' ?>>Rusak Ringan</option>
                        <option value="rusak_berat" <?= ($kondisi === 'rusak_berat') ? 'selected' : '' ?>>Rusak Berat</option>
                        <option value="tidak_layak" <?= ($kondisi === 'tidak_layak') ? 'selected' : '' ?>>Tidak Layak</option>
                        <option value="hilang" <?= ($kondisi === 'hilang') ? 'selected' : '' ?>>Hilang</option>
                    </select>
                </div>

                <!-- Actions -->
                <div class="col-auto d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    <?php if (!empty($search) || !empty($gedung_id) || !empty($ruangan_id) || !empty($kategori_id) || !empty($kondisi)): ?>
                        <a href="<?= base_url('fasilitas') ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3" title="Reset Semua Filter">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                        </a>
                    <?php endif; ?>
                </div>

                <?php if (!empty($search) || !empty($gedung_id) || !empty($ruangan_id) || !empty($kategori_id) || !empty($kondisi)): ?>
                <div class="col-12 mt-2">
                    <span class="badge bg-primary-subtle text-primary rounded-pill py-2 px-3 small">
                        Ditemukan: <?= $total_rows ?> item fasilitas sesuai kriteria filter
                    </span>
                </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Data Table Fasilitas -->
    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light text-secondary">
                        <tr>
                            <th class="ps-4" style="width: 50px;">No</th>
                            <th style="width: 80px;">Foto</th>
                            <th style="width: 140px;">Kode Inventaris</th>
                            <th>Nama Fasilitas</th>
                            <th>Kategori</th>
                            <th>Ruangan & Gedung</th>
                            <th class="text-center" style="width: 90px;">Jumlah</th>
                            <th class="text-center" style="width: 120px;">Kondisi</th>
                            <th class="text-center" style="width: 80px;">Tahun</th>
                            <th class="text-end pe-4" style="width: 170px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($fasilitas_list)): ?>
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <div class="py-4">
                                    <i class="bi bi-box-seam fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                    <span class="fw-semibold">Tidak ada data fasilitas yang ditemukan.</span>
                                    <?php if (!empty($search) || !empty($gedung_id) || !empty($ruangan_id) || !empty($kategori_id) || !empty($kondisi)): ?>
                                        <div class="mt-2">
                                            <a href="<?= base_url('fasilitas') ?>" class="btn btn-sm btn-outline-primary rounded-pill">Reset Filter</a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php 
                            $startNumber = (($page - 1) * $per_page) + 1;
                            foreach ($fasilitas_list as $index => $f): 
                            ?>
                            <tr>
                                <td class="ps-4 text-muted"><?= $startNumber + $index ?></td>
                                <td>
                                    <?php 
                                    $fotoPath = (defined('UPLOAD_PATH') ? UPLOAD_PATH : (__DIR__ . '/../../../public/uploads')) . '/fasilitas/' . ($f['foto'] ?? '');
                                    if (!empty($f['foto']) && file_exists($fotoPath)): 
                                    ?>
                                        <img src="<?= base_url('uploads/fasilitas/' . $f['foto']) ?>" 
                                             alt="<?= htmlspecialchars($f['nama_fasilitas']) ?>" 
                                             class="rounded-3 shadow-sm object-fit-cover" 
                                             style="width: 48px; height: 48px;">
                                    <?php else: ?>
                                        <div class="bg-light text-secondary rounded-3 d-flex align-items-center justify-content-center border" 
                                             style="width: 48px; height: 48px;">
                                            <i class="bi bi-image fs-5 text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-primary border font-monospace px-2 py-1 rounded-2 fw-bold">
                                        <?= htmlspecialchars($f['kode_inventaris']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= base_url('fasilitas/detail/' . $f['id']) ?>" class="fw-bold text-dark text-decoration-none hover-primary">
                                        <?= htmlspecialchars($f['nama_fasilitas']) ?>
                                    </a>
                                    <?php if (!empty($f['keterangan'])): ?>
                                        <div class="text-muted small text-truncate" style="max-width: 220px; font-size: 0.75rem;">
                                            <?= htmlspecialchars($f['keterangan']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-1">
                                        <?= htmlspecialchars($f['nama_kategori']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">
                                        <i class="bi bi-door-open me-1 text-primary"></i><?= htmlspecialchars($f['nama_ruangan']) ?>
                                    </div>
                                    <div class="text-muted small" style="font-size: 0.75rem;">
                                        <?= htmlspecialchars($f['nama_gedung']) ?> &bull; Lt. <?= $f['lantai'] ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold text-dark"><?= $f['jumlah'] ?></span>
                                    <small class="text-muted d-block" style="font-size: 0.7rem;">Unit</small>
                                </td>
                                <td class="text-center">
                                    <?php if ($f['kondisi'] === 'baik'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1">
                                            <i class="bi bi-check-circle me-1"></i>Baik
                                        </span>
                                    <?php elseif ($f['kondisi'] === 'rusak_ringan'): ?>
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2 py-1">
                                            <i class="bi bi-exclamation-circle me-1"></i>Rusak Ringan
                                        </span>
                                    <?php elseif ($f['kondisi'] === 'rusak_berat'): ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-1">
                                            <i class="bi bi-x-circle me-1"></i>Rusak Berat
                                        </span>
                                    <?php elseif ($f['kondisi'] === 'tidak_layak'): ?>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2 py-1">
                                            Tidak Layak
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-dark-subtle text-dark border border-dark-subtle rounded-pill px-2 py-1">
                                            Hilang
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center text-muted"><?= $f['tahun_pengadaan'] ?></td>
                                <td class="text-end pe-4">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <!-- Detail: Semua Role -->
                                        <a href="<?= base_url('fasilitas/detail/' . $f['id']) ?>" class="btn btn-outline-secondary rounded-pill me-1" title="Lihat Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        <!-- Update Kondisi & Edit: Admin & Petugas Sarpras -->
                                        <?php if (Auth::hasRole(['admin', 'petugas_sarpras'])): ?>
                                        <button type="button" class="btn btn-outline-warning rounded-pill me-1 btn-update-kondisi" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalUpdateKondisi" 
                                                data-id="<?= $f['id'] ?>" 
                                                data-nama="<?= htmlspecialchars($f['nama_fasilitas']) ?>" 
                                                data-kondisi="<?= $f['kondisi'] ?>"
                                                title="Update Kondisi Cepat">
                                            <i class="bi bi-activity"></i>
                                        </button>
                                        <a href="<?= base_url('fasilitas/edit/' . $f['id']) ?>" class="btn btn-outline-primary rounded-pill me-1" title="Ubah Data">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <?php endif; ?>

                                        <!-- Hapus: Khusus Admin -->
                                        <?php if (Auth::hasRole('admin')): ?>
                                        <button type="button" class="btn btn-outline-danger rounded-pill btn-delete-fasilitas" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalDeleteFasilitas" 
                                                data-id="<?= $f['id'] ?>" 
                                                data-nama="<?= htmlspecialchars($f['nama_fasilitas']) ?>" 
                                                data-kode="<?= htmlspecialchars($f['kode_inventaris']) ?>"
                                                title="Hapus Fasilitas">
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
            if (!empty($ruangan_id)) $queryUrl .= '&ruangan_id=' . urlencode($ruangan_id);
            if (!empty($kategori_id)) $queryUrl .= '&kategori_id=' . urlencode($kategori_id);
            if (!empty($kondisi)) $queryUrl .= '&kondisi=' . urlencode($kondisi);
            ?>
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center p-3 border-top gap-2">
                <div class="text-muted small">
                    Menampilkan <strong><?= count($fasilitas_list) ?></strong> dari <strong><?= $total_rows ?></strong> item fasilitas (Halaman <?= $page ?> / <?= $total_pages ?>)
                </div>
                <nav aria-label="Pagination Fasilitas">
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link rounded-pill me-1" href="<?= base_url('fasilitas?page=' . ($page - 1) . $queryUrl) ?>">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= ($i === $page) ? 'active' : '' ?>">
                                <a class="page-link rounded-circle mx-1 text-center" style="width: 32px; height: 32px; line-height: 18px;" 
                                   href="<?= base_url('fasilitas?page=' . $i . $queryUrl) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                            <a class="page-link rounded-pill ms-1" href="<?= base_url('fasilitas?page=' . ($page + 1) . $queryUrl) ?>">
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

<!-- Modal Update Kondisi Cepat (Admin & Petugas Sarpras) -->
<?php if (Auth::hasRole(['admin', 'petugas_sarpras'])): ?>
<div class="modal fade" id="modalUpdateKondisi" tabindex="-1" aria-labelledby="modalUpdateKondisiLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
            <form id="formUpdateKondisi" method="POST" action="">
                <?= csrf_field() ?>
                <div class="modal-header border-0 bg-warning bg-opacity-10 text-dark p-4 pb-2">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-activity text-warning fs-4"></i>
                        <h5 class="modal-title fw-bold" id="modalUpdateKondisiLabel">Update Kondisi Fasilitas</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 pt-2">
                    <p class="text-muted small mb-2">Pembaruan kondisi fisik dan kelayakan operasional fasilitas:</p>
                    <div class="p-3 bg-light rounded-3 mb-3">
                        <div class="fw-bold text-dark fs-6" id="updateKondisiNamaFasilitas">-</div>
                    </div>
                    <div class="mb-3">
                        <label for="selectModalKondisi" class="form-label small fw-semibold">Status Kondisi Baru <span class="text-danger">*</span></label>
                        <select name="kondisi" id="selectModalKondisi" class="form-select" required>
                            <option value="baik">Baik (Berfungsi Normal)</option>
                            <option value="rusak_ringan">Rusak Ringan (Perlu Perawatan/Pembersihan)</option>
                            <option value="rusak_berat">Rusak Berat (Tidak Berfungsi / Perlu Perbaikan)</option>
                            <option value="tidak_layak">Tidak Layak (Masa Pakai Habis / Afkir)</option>
                            <option value="hilang">Hilang (Tidak Ditemukan Saat Pengecekan)</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label for="catatanKondisi" class="form-label small fw-semibold">Catatan Pemeriksaan</label>
                        <textarea name="catatan" id="catatanKondisi" class="form-control small" rows="2" 
                                  placeholder="Contoh: Terjadi korsleting kabel, kipas tidak berputar..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-4 small" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 small fw-semibold text-dark">
                        <i class="bi bi-check-lg me-1"></i> Simpan Perubahan Kondisi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal Konfirmasi Hapus Fasilitas (Khusus Admin) -->
<?php if (Auth::hasRole('admin')): ?>
<div class="modal fade" id="modalDeleteFasilitas" tabindex="-1" aria-labelledby="modalDeleteFasilitasLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
            <div class="modal-header border-0 bg-danger bg-opacity-10 text-danger p-4 pb-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                    <h5 class="modal-title fw-bold" id="modalDeleteFasilitasLabel">Konfirmasi Hapus Fasilitas</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 pt-2">
                <p class="text-muted small mb-2">Anda akan menghapus inventaris fasilitas berikut:</p>
                <div class="p-3 bg-light rounded-3 mb-3">
                    <div class="fw-bold text-dark fs-6" id="deleteNamaFasilitas">-</div>
                    <div class="small text-muted font-monospace mt-1" id="deleteKodeInventaris">-</div>
                </div>
                <div class="alert alert-warning small border-0 bg-warning-subtle text-dark py-2 px-3 rounded-3">
                    <i class="bi bi-shield-exclamation me-1"></i>
                    Sistem secara otomatis menolak penghapusan jika fasilitas ini memiliki riwayat pemeliharaan atau tiket pelaporan kerusakan.
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0 d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-light rounded-pill px-4 small" data-bs-dismiss="modal">Batal</button>
                <a href="#" id="btnConfirmDeleteFasilitas" class="btn btn-danger rounded-pill px-4 small fw-semibold">
                    <i class="bi bi-trash me-1"></i> Ya, Hapus Fasilitas
                </a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Dynamic Ruangan Dropdown based on Gedung Selection
    const filterGedung = document.getElementById('filterGedung');
    const filterRuangan = document.getElementById('filterRuangan');

    if (filterGedung && filterRuangan) {
        filterGedung.addEventListener('change', () => {
            const gedungId = filterGedung.value;
            fetch('<?= base_url('fasilitas/ajaxRuangan?gedung_id=') ?>' + encodeURIComponent(gedungId))
                .then(res => res.json())
                .then(data => {
                    filterRuangan.innerHTML = '<option value="">-- Semua Ruangan --</option>';
                    data.forEach(r => {
                        filterRuangan.innerHTML += `<option value="${r.id}">${r.nama_ruangan} (Lt. ${r.lantai})</option>`;
                    });
                })
                .catch(err => console.error('Error fetching rooms:', err));
        });
    }

    // 2. Modal Update Kondisi Binding
    const updateButtons = document.querySelectorAll('.btn-update-kondisi');
    const formUpdateKondisi = document.getElementById('formUpdateKondisi');
    const updateKondisiNamaFasilitas = document.getElementById('updateKondisiNamaFasilitas');
    const selectModalKondisi = document.getElementById('selectModalKondisi');

    updateButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-id');
            const nama = btn.getAttribute('data-nama');
            const kondisi = btn.getAttribute('data-kondisi');

            if (formUpdateKondisi) formUpdateKondisi.action = '<?= base_url('fasilitas/updateKondisi/') ?>' + id;
            if (updateKondisiNamaFasilitas) updateKondisiNamaFasilitas.textContent = nama;
            if (selectModalKondisi) selectModalKondisi.value = kondisi;
        });
    });

    // 3. Modal Delete Binding
    const deleteButtons = document.querySelectorAll('.btn-delete-fasilitas');
    const deleteNamaFasilitas = document.getElementById('deleteNamaFasilitas');
    const deleteKodeInventaris = document.getElementById('deleteKodeInventaris');
    const btnConfirmDeleteFasilitas = document.getElementById('btnConfirmDeleteFasilitas');

    deleteButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-id');
            const nama = btn.getAttribute('data-nama');
            const kode = btn.getAttribute('data-kode');

            if (deleteNamaFasilitas) deleteNamaFasilitas.textContent = nama;
            if (deleteKodeInventaris) deleteKodeInventaris.textContent = kode;
            if (btnConfirmDeleteFasilitas) btnConfirmDeleteFasilitas.href = '<?= base_url('fasilitas/hapus/') ?>' + id + '?csrf_token=<?= csrf_token() ?>';
        });
    });
});
</script>
