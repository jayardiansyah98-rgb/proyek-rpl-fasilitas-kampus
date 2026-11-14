<div class="container-fluid p-0">
    <!-- Breadcrumb & Header -->
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
        <div class="card-body p-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2 small">
                    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('fasilitas') ?>" class="text-decoration-none">Data Fasilitas</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Fasilitas</li>
                </ol>
            </nav>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="fw-bold mb-1 text-dark">Edit Fasilitas: <?= htmlspecialchars($fasilitas['nama_fasilitas']) ?></h4>
                    <p class="text-muted small mb-0">Perbarui rincian inventaris, kondisi fisik, atau mutasi ruangan fasilitas.</p>
                </div>
                <a href="<?= base_url('fasilitas/detail/' . $fasilitas['id']) ?>" class="btn btn-outline-secondary rounded-pill px-3 py-2 small">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Detail
                </a>
            </div>
        </div>
    </div>

    <!-- Form Edit Fasilitas -->
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
                <?php if (!empty($errors['general'])): ?>
                    <div class="alert alert-danger border-0 bg-danger-subtle text-danger small rounded-3 py-2 px-3 mb-4">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= htmlspecialchars($errors['general']) ?>
                    </div>
                <?php endif; ?>

                <form action="<?= base_url('fasilitas/edit/' . $fasilitas['id']) ?>" method="POST" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <div class="row g-3">
                        <!-- Kode Inventaris -->
                        <div class="col-md-6">
                            <label for="kode_inventaris" class="form-label small fw-semibold text-dark">
                                Kode Inventaris <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="kode_inventaris" id="kode_inventaris" 
                                   class="form-control <?= !empty($errors['kode_inventaris']) ? 'is-invalid' : '' ?> font-monospace text-uppercase" 
                                   value="<?= htmlspecialchars($old['kode_inventaris'] ?? '') ?>" required>
                            <?php if (!empty($errors['kode_inventaris'])): ?>
                                <div class="invalid-feedback small"><?= $errors['kode_inventaris'] ?></div>
                            <?php else: ?>
                                <div class="form-text small">Nomor inventaris unik sarana kampus (maks. 50 karakter).</div>
                            <?php endif; ?>
                        </div>

                        <!-- Nama Fasilitas -->
                        <div class="col-md-6">
                            <label for="nama_fasilitas" class="form-label small fw-semibold text-dark">
                                Nama Fasilitas / Perangkat <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nama_fasilitas" id="nama_fasilitas" 
                                   class="form-control <?= !empty($errors['nama_fasilitas']) ? 'is-invalid' : '' ?>" 
                                   value="<?= htmlspecialchars($old['nama_fasilitas'] ?? '') ?>" required>
                            <?php if (!empty($errors['nama_fasilitas'])): ?>
                                <div class="invalid-feedback small"><?= $errors['nama_fasilitas'] ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Kategori Fasilitas -->
                        <div class="col-md-4">
                            <label for="kategori_id" class="form-label small fw-semibold text-dark">
                                Kategori Fasilitas <span class="text-danger">*</span>
                            </label>
                            <select name="kategori_id" id="kategori_id" class="form-select <?= !empty($errors['kategori_id']) ? 'is-invalid' : '' ?>" required>
                                <option value="">-- Pilih Kategori --</option>
                                <?php foreach ($kategori_list as $k): ?>
                                    <option value="<?= $k['id'] ?>" <?= ((string)($old['kategori_id'] ?? '') === (string)$k['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($k['nama_kategori']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (!empty($errors['kategori_id'])): ?>
                                <div class="invalid-feedback small"><?= $errors['kategori_id'] ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Gedung Penempatan -->
                        <div class="col-md-4">
                            <label for="gedung_select" class="form-label small fw-semibold text-dark">
                                Gedung Penempatan <span class="text-danger">*</span>
                            </label>
                            <select name="gedung_id" id="gedung_select" class="form-select">
                                <option value="">-- Pilih Gedung --</option>
                                <?php foreach ($gedung_list as $g): ?>
                                    <option value="<?= $g['id'] ?>" <?= ((string)($old['gedung_id'] ?? '') === (string)$g['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($g['nama_gedung']) ?> (<?= htmlspecialchars($g['kode_gedung']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Ruangan Penempatan -->
                        <div class="col-md-4">
                            <label for="ruangan_id" class="form-label small fw-semibold text-dark">
                                Ruangan Penempatan <span class="text-danger">*</span>
                            </label>
                            <select name="ruangan_id" id="ruangan_id" class="form-select <?= !empty($errors['ruangan_id']) ? 'is-invalid' : '' ?>" required>
                                <option value="">-- Pilih Ruangan --</option>
                                <?php foreach ($ruangan_list as $r): ?>
                                    <option value="<?= $r['id'] ?>" <?= ((string)($old['ruangan_id'] ?? '') === (string)$r['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($r['nama_ruangan']) ?> (Lt. <?= $r['lantai'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (!empty($errors['ruangan_id'])): ?>
                                <div class="invalid-feedback small"><?= $errors['ruangan_id'] ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Jumlah Unit & Kondisi & Tahun Pengadaan -->
                        <div class="col-md-4">
                            <label for="jumlah" class="form-label small fw-semibold text-dark">
                                Jumlah Unit <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" name="jumlah" id="jumlah" min="1" max="10000" class="form-control" 
                                       value="<?= htmlspecialchars($old['jumlah'] ?? 1) ?>" required>
                                <span class="input-group-text bg-light">Unit</span>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="kondisi" class="form-label small fw-semibold text-dark">
                                Kondisi Fisik <span class="text-danger">*</span>
                            </label>
                            <select name="kondisi" id="kondisi" class="form-select <?= !empty($errors['kondisi']) ? 'is-invalid' : '' ?>" required>
                                <option value="baik" <?= (($old['kondisi'] ?? '') === 'baik') ? 'selected' : '' ?>>Baik (Berfungsi Normal)</option>
                                <option value="rusak_ringan" <?= (($old['kondisi'] ?? '') === 'rusak_ringan') ? 'selected' : '' ?>>Rusak Ringan</option>
                                <option value="rusak_berat" <?= (($old['kondisi'] ?? '') === 'rusak_berat') ? 'selected' : '' ?>>Rusak Berat</option>
                                <option value="tidak_layak" <?= (($old['kondisi'] ?? '') === 'tidak_layak') ? 'selected' : '' ?>>Tidak Layak Pakai</option>
                                <option value="hilang" <?= (($old['kondisi'] ?? '') === 'hilang') ? 'selected' : '' ?>>Hilang</option>
                            </select>
                            <?php if (!empty($errors['kondisi'])): ?>
                                <div class="invalid-feedback small"><?= $errors['kondisi'] ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-4">
                            <label for="tahun_pengadaan" class="form-label small fw-semibold text-dark">
                                Tahun Pengadaan <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="tahun_pengadaan" id="tahun_pengadaan" 
                                   min="1990" max="<?= date('Y') + 1 ?>" 
                                   class="form-control <?= !empty($errors['tahun_pengadaan']) ? 'is-invalid' : '' ?>" 
                                   value="<?= htmlspecialchars($old['tahun_pengadaan'] ?? date('Y')) ?>" required>
                            <?php if (!empty($errors['tahun_pengadaan'])): ?>
                                <div class="invalid-feedback small"><?= $errors['tahun_pengadaan'] ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Upload Foto Aman -->
                        <div class="col-12">
                            <label for="fotoInput" class="form-label small fw-semibold text-dark">
                                Foto Fasilitas / Inventaris (Biarkan kosong jika tidak ingin mengganti)
                            </label>
                            <div class="row align-items-center g-3">
                                <div class="col-md-8">
                                    <input type="file" name="foto" id="fotoInput" 
                                           class="form-control <?= !empty($errors['foto']) ? 'is-invalid' : '' ?>" 
                                           accept="image/jpeg,image/png,image/webp">
                                    <?php if (!empty($errors['foto'])): ?>
                                        <div class="invalid-feedback small"><?= $errors['foto'] ?></div>
                                    <?php else: ?>
                                        <div class="form-text small">
                                            <i class="bi bi-info-circle me-1"></i>Format yang diizinkan: <strong>JPG, JPEG, PNG, WEBP</strong>. Maksimal <strong>2 MB</strong>.
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-4 text-center">
                                    <div id="imagePreviewContainer" class="<?= empty($old['current_foto']) ? 'd-none' : '' ?>">
                                        <img id="imagePreview" 
                                             src="<?= !empty($old['current_foto']) ? base_url('uploads/' . $old['current_foto']) : '#' ?>" 
                                             alt="Preview Foto" 
                                             class="img-thumbnail rounded-3 shadow-sm" 
                                             style="max-height: 100px; max-width: 140px; object-fit: cover;">
                                        <div class="small text-muted mt-1">Pratinjau Foto</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Keterangan -->
                        <div class="col-12">
                            <label for="keterangan" class="form-label small fw-semibold text-dark">
                                Keterangan / Spesifikasi Tambahan
                            </label>
                            <textarea name="keterangan" id="keterangan" rows="3" class="form-control" 
                                      placeholder="Catatan serial number (S/N), merek/tipe, vendor pengadaan, garansi..."><?= htmlspecialchars($old['keterangan'] ?? '') ?></textarea>
                            <div class="form-text small">Rincian spesifikasi teknis dan riwayat pengadaan fasilitas.</div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="col-12 pt-3 border-top mt-4 d-flex justify-content-end gap-2">
                            <a href="<?= base_url('fasilitas/detail/' . $fasilitas['id']) ?>" class="btn btn-light rounded-pill px-4 small">Batal</a>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 small fw-semibold shadow-sm">
                                <i class="bi bi-check-lg me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Chained Dropdown Gedung -> Ruangan
    const gedungSelect = document.getElementById('gedung_select');
    const ruanganSelect = document.getElementById('ruangan_id');

    if (gedungSelect && ruanganSelect) {
        gedungSelect.addEventListener('change', () => {
            const gedungId = gedungSelect.value;
            fetch('<?= base_url('fasilitas/ajaxRuangan?gedung_id=') ?>' + encodeURIComponent(gedungId))
                .then(res => res.json())
                .then(data => {
                    ruanganSelect.innerHTML = '<option value="">-- Pilih Ruangan --</option>';
                    data.forEach(r => {
                        ruanganSelect.innerHTML += `<option value="${r.id}">${r.nama_ruangan} (Lt. ${r.lantai})</option>`;
                    });
                })
                .catch(err => console.error('Error fetching rooms:', err));
        });
    }

    // 2. Image Live Preview
    const fotoInput = document.getElementById('fotoInput');
    const previewContainer = document.getElementById('imagePreviewContainer');
    const previewImg = document.getElementById('imagePreview');

    if (fotoInput && previewContainer && previewImg) {
        fotoInput.addEventListener('change', () => {
            const file = fotoInput.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    previewImg.src = e.target.result;
                    previewContainer.classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            }
        });
    }
});
</script>
