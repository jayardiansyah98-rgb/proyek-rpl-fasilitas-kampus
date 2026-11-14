<div class="container-fluid p-0">
    <!-- Breadcrumb & Header -->
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
        <div class="card-body p-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2 small">
                    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('ruangan') ?>" class="text-decoration-none">Data Ruangan</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Ruangan</li>
                </ol>
            </nav>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="fw-bold mb-1 text-dark">Ubah Data Ruangan: <?= htmlspecialchars($ruangan['nama_ruangan']) ?></h4>
                    <p class="text-muted small mb-0">Perbarui spesifikasi ruangan, perubahan kapasitas, atau status operasional.</p>
                </div>
                <a href="<?= base_url('ruangan') ?>" class="btn btn-outline-secondary rounded-pill px-3 py-2 small">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
                </a>
            </div>
        </div>
    </div>

    <!-- Form Edit Ruangan -->
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
                <?php if (!empty($errors['general'])): ?>
                    <div class="alert alert-danger border-0 bg-danger-subtle text-danger small rounded-3 py-2 px-3 mb-4">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= htmlspecialchars($errors['general']) ?>
                    </div>
                <?php endif; ?>

                <form action="<?= base_url('ruangan/edit/' . $ruangan['id']) ?>" method="POST">
                    <?= csrf_field() ?>
                    <div class="row g-3">
                        <!-- Kode Ruangan -->
                        <div class="col-md-6">
                            <label for="kode_ruangan" class="form-label small fw-semibold text-dark">
                                Kode Ruangan <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="kode_ruangan" id="kode_ruangan" 
                                   class="form-control <?= !empty($errors['kode_ruangan']) ? 'is-invalid' : '' ?> font-monospace text-uppercase" 
                                   value="<?= htmlspecialchars($old['kode_ruangan'] ?? '') ?>" required autofocus>
                            <?php if (!empty($errors['kode_ruangan'])): ?>
                                <div class="invalid-feedback small"><?= $errors['kode_ruangan'] ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Nama Ruangan -->
                        <div class="col-md-6">
                            <label for="nama_ruangan" class="form-label small fw-semibold text-dark">
                                Nama Ruangan <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nama_ruangan" id="nama_ruangan" 
                                   class="form-control <?= !empty($errors['nama_ruangan']) ? 'is-invalid' : '' ?>" 
                                   value="<?= htmlspecialchars($old['nama_ruangan'] ?? '') ?>" required>
                            <?php if (!empty($errors['nama_ruangan'])): ?>
                                <div class="invalid-feedback small"><?= $errors['nama_ruangan'] ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Gedung Lokasi -->
                        <div class="col-md-8">
                            <label for="gedung_id" class="form-label small fw-semibold text-dark">
                                Lokasi Gedung <span class="text-danger">*</span>
                            </label>
                            <select name="gedung_id" id="gedung_id" class="form-select <?= !empty($errors['gedung_id']) ? 'is-invalid' : '' ?>" required>
                                <option value="">-- Pilih Gedung Kampus --</option>
                                <?php foreach ($gedung_list as $g): ?>
                                    <option value="<?= $g['id'] ?>" <?= ((string)($old['gedung_id'] ?? '') === (string)$g['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($g['nama_gedung']) ?> (<?= htmlspecialchars($g['kode_gedung']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (!empty($errors['gedung_id'])): ?>
                                <div class="invalid-feedback small"><?= $errors['gedung_id'] ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Lantai -->
                        <div class="col-md-4">
                            <label for="lantai" class="form-label small fw-semibold text-dark">
                                Posisi Lantai <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">Lt.</span>
                                <input type="number" name="lantai" id="lantai" min="1" max="50" class="form-control" 
                                       value="<?= htmlspecialchars($old['lantai'] ?? 1) ?>" required>
                            </div>
                        </div>

                        <!-- Jenis Ruangan -->
                        <div class="col-md-4">
                            <label for="jenis_ruangan" class="form-label small fw-semibold text-dark">
                                Jenis Ruangan <span class="text-danger">*</span>
                            </label>
                            <select name="jenis_ruangan" id="jenis_ruangan" class="form-select <?= !empty($errors['jenis_ruangan']) ? 'is-invalid' : '' ?>" required>
                                <?php 
                                $jenisList = ['Kelas', 'Laboratorium', 'Ruang Dosen', 'Ruang Administrasi', 'Ruang Rapat', 'Ruang Seminar', 'Lainnya'];
                                foreach ($jenisList as $j): 
                                ?>
                                    <option value="<?= $j ?>" <?= (($old['jenis_ruangan'] ?? '') === $j) ? 'selected' : '' ?>><?= $j ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (!empty($errors['jenis_ruangan'])): ?>
                                <div class="invalid-feedback small"><?= $errors['jenis_ruangan'] ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Kapasitas -->
                        <div class="col-md-4">
                            <label for="kapasitas" class="form-label small fw-semibold text-dark">
                                Kapasitas (Orang) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" name="kapasitas" id="kapasitas" min="0" max="1000" class="form-control" 
                                       value="<?= htmlspecialchars($old['kapasitas'] ?? 0) ?>" required>
                                <span class="input-group-text bg-light">Orang</span>
                            </div>
                        </div>

                        <!-- Status Ruangan -->
                        <div class="col-md-4">
                            <label for="status" class="form-label small fw-semibold text-dark">
                                Status Ruangan <span class="text-danger">*</span>
                            </label>
                            <select name="status" id="status" class="form-select <?= !empty($errors['status']) ? 'is-invalid' : '' ?>" required>
                                <option value="tersedia" <?= (($old['status'] ?? '') === 'tersedia') ? 'selected' : '' ?>>Tersedia / Siap Pakai</option>
                                <option value="digunakan" <?= (($old['status'] ?? '') === 'digunakan') ? 'selected' : '' ?>>Sedang Digunakan</option>
                                <option value="perbaikan" <?= (($old['status'] ?? '') === 'perbaikan') ? 'selected' : '' ?>>Dalam Perbaikan</option>
                                <option value="nonaktif" <?= (($old['status'] ?? '') === 'nonaktif') ? 'selected' : '' ?>>Nonaktif</option>
                            </select>
                            <?php if (!empty($errors['status'])): ?>
                                <div class="invalid-feedback small"><?= $errors['status'] ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Keterangan -->
                        <div class="col-12">
                            <label for="keterangan" class="form-label small fw-semibold text-dark">
                                Keterangan / Deskripsi Fasilitas
                            </label>
                            <textarea name="keterangan" id="keterangan" rows="3" class="form-control"><?= htmlspecialchars($old['keterangan'] ?? '') ?></textarea>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="col-12 pt-3 border-top mt-4 d-flex justify-content-end gap-2">
                            <a href="<?= base_url('ruangan') ?>" class="btn btn-light rounded-pill px-4 small">Batal</a>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 small fw-semibold shadow-sm">
                                <i class="bi bi-check-lg me-1"></i> Perbarui Ruangan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
