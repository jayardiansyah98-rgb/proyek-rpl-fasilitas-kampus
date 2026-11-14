<div class="container-fluid p-0">
    <!-- Breadcrumb & Header -->
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
        <div class="card-body p-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2 small">
                    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('gedung') ?>" class="text-decoration-none">Data Gedung</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Tambah Gedung</li>
                </ol>
            </nav>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="fw-bold mb-1 text-dark">Tambah Gedung Baru</h4>
                    <p class="text-muted small mb-0">Daftarkan fisik gedung perkuliahan atau fasilitas baru STMIK IKMI Cirebon.</p>
                </div>
                <a href="<?= base_url('gedung') ?>" class="btn btn-outline-secondary rounded-pill px-3 py-2 small">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
                </a>
            </div>
        </div>
    </div>

    <!-- Form Tambah Gedung -->
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
                <?php if (!empty($errors['general'])): ?>
                    <div class="alert alert-danger border-0 bg-danger-subtle text-danger small rounded-3 py-2 px-3 mb-4">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= htmlspecialchars($errors['general']) ?>
                    </div>
                <?php endif; ?>

                <form action="<?= base_url('gedung/tambah') ?>" method="POST">
                    <?= csrf_field() ?>
                    <div class="row g-3">
                        <!-- Kode Gedung -->
                        <div class="col-md-6">
                            <label for="kode_gedung" class="form-label small fw-semibold text-dark">
                                Kode Gedung <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="kode_gedung" id="kode_gedung" 
                                   class="form-control <?= !empty($errors['kode_gedung']) ? 'is-invalid' : '' ?> font-monospace text-uppercase" 
                                   placeholder="Contoh: GD-C" 
                                   value="<?= htmlspecialchars($old['kode_gedung'] ?? '') ?>" required autofocus>
                            <?php if (!empty($errors['kode_gedung'])): ?>
                                <div class="invalid-feedback small"><?= $errors['kode_gedung'] ?></div>
                            <?php else: ?>
                                <div class="form-text small">Kode unik identifikasi gedung (maks. 20 karakter).</div>
                            <?php endif; ?>
                        </div>

                        <!-- Nama Gedung -->
                        <div class="col-md-6">
                            <label for="nama_gedung" class="form-label small fw-semibold text-dark">
                                Nama Gedung <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nama_gedung" id="nama_gedung" 
                                   class="form-control <?= !empty($errors['nama_gedung']) ? 'is-invalid' : '' ?>" 
                                   placeholder="Contoh: Gedung Pascasarjana & Riset Terpadu" 
                                   value="<?= htmlspecialchars($old['nama_gedung'] ?? '') ?>" required>
                            <?php if (!empty($errors['nama_gedung'])): ?>
                                <div class="invalid-feedback small"><?= $errors['nama_gedung'] ?></div>
                            <?php else: ?>
                                <div class="form-text small">Nama resmi penamaan gedung kampus.</div>
                            <?php endif; ?>
                        </div>

                        <!-- Lokasi -->
                        <div class="col-12">
                            <label for="lokasi" class="form-label small fw-semibold text-dark">
                                Lokasi <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-geo-alt text-danger"></i></span>
                                <input type="text" name="lokasi" id="lokasi" 
                                       class="form-control <?= !empty($errors['lokasi']) ? 'is-invalid' : '' ?>" 
                                       placeholder="Contoh: Kampus Utama, Samping Gedung ICT, Jl. Perjuangan No. 10B" 
                                       value="<?= htmlspecialchars($old['lokasi'] ?? '') ?>" required>
                                <?php if (!empty($errors['lokasi'])): ?>
                                    <div class="invalid-feedback small"><?= $errors['lokasi'] ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="form-text small">Deskripsi area lokasi atau alamat spesifik gedung di kawasan kampus.</div>
                        </div>

                        <!-- Jumlah Lantai & Status -->
                        <div class="col-md-6">
                            <label for="jumlah_lantai" class="form-label small fw-semibold text-dark">
                                Jumlah Lantai <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-layers text-primary"></i></span>
                                <input type="number" name="jumlah_lantai" id="jumlah_lantai" min="1" max="50" 
                                       class="form-control" 
                                       value="<?= htmlspecialchars($old['jumlah_lantai'] ?? 1) ?>" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="status" class="form-label small fw-semibold text-dark">
                                Status Gedung <span class="text-danger">*</span>
                            </label>
                            <select name="status" id="status" class="form-select">
                                <option value="aktif" <?= (($old['status'] ?? '') === 'aktif') ? 'selected' : '' ?>>Aktif Digunakan</option>
                                <option value="renovasi" <?= (($old['status'] ?? '') === 'renovasi') ? 'selected' : '' ?>>Dalam Renovasi</option>
                                <option value="nonaktif" <?= (($old['status'] ?? '') === 'nonaktif') ? 'selected' : '' ?>>Nonaktif / Tutup</option>
                            </select>
                        </div>

                        <!-- Keterangan -->
                        <div class="col-12">
                            <label for="keterangan" class="form-label small fw-semibold text-dark">
                                Keterangan
                            </label>
                            <textarea name="keterangan" id="keterangan" rows="3" class="form-control" 
                                      placeholder="Catatan tambahan fungsi operasional gedung..."><?= htmlspecialchars($old['keterangan'] ?? '') ?></textarea>
                            <div class="form-text small">Informasi fasilitas umum, peruntukan lantai, atau kontak penanggung jawab.</div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="col-12 pt-3 border-top mt-4 d-flex justify-content-end gap-2">
                            <a href="<?= base_url('gedung') ?>" class="btn btn-light rounded-pill px-4 small">Batal</a>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 small fw-semibold shadow-sm">
                                <i class="bi bi-check-lg me-1"></i> Simpan Gedung
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
