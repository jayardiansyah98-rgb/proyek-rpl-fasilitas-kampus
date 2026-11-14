<div class="container-fluid p-0">
    <!-- Breadcrumb & Header -->
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
        <div class="card-body p-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2 small">
                    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('laporan') ?>" class="text-decoration-none">Laporan Kerusakan</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Buat Pengaduan</li>
                </ol>
            </nav>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="fw-bold mb-1 text-dark">Form Pengaduan Kerusakan Fasilitas</h4>
                    <p class="text-muted small mb-0">Laporkan kendala fisik atau malfungsi sarana kampus untuk segera ditindaklanjuti oleh Bagian Sarpras.</p>
                </div>
                <a href="<?= base_url('laporan') ?>" class="btn btn-outline-secondary rounded-pill px-3 py-2 small">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Form Container -->
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
                <?php if (!empty($errors['general'])): ?>
                    <div class="alert alert-danger border-0 bg-danger-subtle text-danger small rounded-3 py-2 px-3 mb-4">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= htmlspecialchars($errors['general']) ?>
                    </div>
                <?php endif; ?>

                <form action="<?= base_url('laporan/tambah') ?>" method="POST" enctype="multipart/form-data" id="formLaporan">
                    <?= csrf_field() ?>
                    <div class="row g-4">
                        <!-- 1. Lokasi Ruangan & Gedung -->
                        <div class="col-md-6">
                            <label for="ruangan_id" class="form-label small fw-semibold text-dark">
                                <i class="bi bi-geo-alt-fill text-danger me-1"></i>Ruangan Lokasi <span class="text-danger">*</span>
                            </label>
                            <select name="ruangan_id" id="ruangan_id" class="form-select <?= !empty($errors['ruangan_id']) ? 'is-invalid' : '' ?>" required>
                                <option value="">-- Pilih Ruangan / Lab --</option>
                                <?php foreach ($ruangan_list as $r): ?>
                                    <option value="<?= $r['id'] ?>" <?= ((string)($old['ruangan_id'] ?? '') === (string)$r['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($r['nama_gedung']) ?> &bull; <?= htmlspecialchars($r['nama_ruangan']) ?> (Lt. <?= $r['lantai'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (!empty($errors['ruangan_id'])): ?>
                                <div class="invalid-feedback small"><?= $errors['ruangan_id'] ?></div>
                            <?php else: ?>
                                <div class="form-text small">Pilih gedung dan ruangan tempat fasilitas berada.</div>
                            <?php endif; ?>
                        </div>

                        <!-- 2. Fasilitas Yang Rusak -->
                        <div class="col-md-6">
                            <label for="fasilitas_id" class="form-label small fw-semibold text-dark">
                                <i class="bi bi-box-seam-fill text-primary me-1"></i>Fasilitas / Barang Yang Rusak <span class="text-danger">*</span>
                            </label>
                            <select name="fasilitas_id" id="fasilitas_id" class="form-select <?= !empty($errors['fasilitas_id']) ? 'is-invalid' : '' ?>" required>
                                <option value="">-- Pilih Ruangan Dahulu --</option>
                                <?php if (!empty($fasilitas_ruangan)): ?>
                                    <?php foreach ($fasilitas_ruangan as $f): ?>
                                        <option value="<?= $f['id'] ?>" <?= ((string)($old['fasilitas_id'] ?? '') === (string)$f['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($f['nama']) ?> (<?= htmlspecialchars($f['kode_inventaris']) ?>) - <?= ucfirst($f['kondisi']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <?php if (!empty($errors['fasilitas_id'])): ?>
                                <div class="invalid-feedback small"><?= $errors['fasilitas_id'] ?></div>
                            <?php else: ?>
                                <div class="form-text small" id="fasilitas_loading">Daftar sarana akan muncul otomatis sesuai ruangan terpilih.</div>
                            <?php endif; ?>
                        </div>

                        <!-- 3. Tingkat Prioritas (Visual Selection Cards) -->
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-dark mb-2">
                                <i class="bi bi-flag-fill text-warning me-1"></i>Tingkat Prioritas Masalah <span class="text-danger">*</span>
                            </label>
                            <div class="row g-3">
                                <!-- Rendah -->
                                <div class="col-sm-6 col-md-3">
                                    <label class="card h-100 border rounded-4 p-3 cursor-pointer priority-card" for="prio_rendah" id="card_rendah">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <input class="form-check-input mt-0" type="radio" name="prioritas" id="prio_rendah" value="rendah" <?= (($old['prioritas'] ?? '') === 'rendah') ? 'checked' : '' ?>>
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 rounded-pill small fw-semibold">Rendah</span>
                                        </div>
                                        <div class="text-dark small fw-semibold">Kerusakan Minor</div>
                                        <div class="text-muted" style="font-size: 0.75rem;">Fasilitas masih dapat dipakai, tidak mengganggu kegiatan utama.</div>
                                    </label>
                                </div>

                                <!-- Sedang -->
                                <div class="col-sm-6 col-md-3">
                                    <label class="card h-100 border rounded-4 p-3 cursor-pointer priority-card" for="prio_sedang" id="card_sedang">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <input class="form-check-input mt-0" type="radio" name="prioritas" id="prio_sedang" value="sedang" <?= (($old['prioritas'] ?? 'sedang') === 'sedang') ? 'checked' : '' ?>>
                                            <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 rounded-pill small fw-semibold">Sedang</span>
                                        </div>
                                        <div class="text-dark small fw-semibold">Fungsi Terganggu</div>
                                        <div class="text-muted" style="font-size: 0.75rem;">Fasilitas mengalami malfungsi sebagian, butuh perbaikan terjadwal.</div>
                                    </label>
                                </div>

                                <!-- Tinggi -->
                                <div class="col-sm-6 col-md-3">
                                    <label class="card h-100 border rounded-4 p-3 cursor-pointer priority-card" for="prio_tinggi" id="card_tinggi">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <input class="form-check-input mt-0" type="radio" name="prioritas" id="prio_tinggi" value="tinggi" <?= (($old['prioritas'] ?? '') === 'tinggi') ? 'checked' : '' ?>>
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 rounded-pill small fw-semibold">Tinggi</span>
                                        </div>
                                        <div class="text-dark small fw-semibold">Mendesak / Mogok</div>
                                        <div class="text-muted" style="font-size: 0.75rem;">Menghentikan jalannya praktikum perkuliahan atau tugas kerja dosen.</div>
                                    </label>
                                </div>

                                <!-- Darurat -->
                                <div class="col-sm-6 col-md-3">
                                    <label class="card h-100 border rounded-4 p-3 cursor-pointer priority-card" for="prio_darurat" id="card_darurat">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <input class="form-check-input mt-0" type="radio" name="prioritas" id="prio_darurat" value="darurat" <?= (($old['prioritas'] ?? '') === 'darurat') ? 'checked' : '' ?>>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill small fw-semibold">Darurat</span>
                                        </div>
                                        <div class="text-dark small fw-semibold">Bahaya Fisik / Fatal</div>
                                        <div class="text-muted" style="font-size: 0.75rem;">Berisiko kebakaran, korsleting listrik, pipa bocor, atau darurat keselamatan.</div>
                                    </label>
                                </div>
                            </div>
                            <?php if (!empty($errors['prioritas'])): ?>
                                <div class="text-danger small mt-1"><?= $errors['prioritas'] ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- 4. Deskripsi Kerusakan -->
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="deskripsi" class="form-label small fw-semibold text-dark mb-0">
                                    <i class="bi bi-card-text text-primary me-1"></i>Deskripsi Rincian Kerusakan <span class="text-danger">*</span>
                                </label>
                                <span class="text-muted small" id="charCount">0 / 2000</span>
                            </div>
                            <textarea name="deskripsi" id="deskripsi" rows="4" 
                                      class="form-control <?= !empty($errors['deskripsi']) ? 'is-invalid' : '' ?>" 
                                      placeholder="Jelaskan secara spesifik gejala kerusakan, kronologi kejadian, letak fisik, atau kode error yang muncul..." 
                                      maxlength="2000" required><?= htmlspecialchars($old['deskripsi'] ?? '') ?></textarea>
                            <?php if (!empty($errors['deskripsi'])): ?>
                                <div class="invalid-feedback small"><?= $errors['deskripsi'] ?></div>
                            <?php else: ?>
                                <div class="form-text small">Berikan informasi yang akurat agar teknisi dapat menyiapkan peralatan dan suku cadang yang tepat.</div>
                            <?php endif; ?>
                        </div>

                        <!-- 5. Upload Foto Kerusakan -->
                        <div class="col-12">
                            <label for="fotoKerusakanInput" class="form-label small fw-semibold text-dark">
                                <i class="bi bi-camera-fill text-primary me-1"></i>Foto Bukti Kerusakan (Opsional)
                            </label>
                            <div class="row align-items-center g-3">
                                <div class="col-md-8">
                                    <input type="file" name="foto" id="fotoKerusakanInput" 
                                           class="form-control <?= !empty($errors['foto']) ? 'is-invalid' : '' ?>" 
                                           accept="image/jpeg,image/png,image/webp">
                                    <?php if (!empty($errors['foto'])): ?>
                                        <div class="invalid-feedback small"><?= $errors['foto'] ?></div>
                                    <?php else: ?>
                                        <div class="form-text small">
                                            <i class="bi bi-info-circle me-1"></i>Format yang didukung: <strong>JPG, JPEG, PNG, WEBP</strong>. Maksimal <strong>2 MB</strong>.
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-4 text-center">
                                    <div id="imagePreviewContainer" class="d-none">
                                        <img id="imagePreview" src="#" alt="Pratinjau Foto Kerusakan" 
                                             class="img-thumbnail rounded-3 shadow-sm border" 
                                             style="max-height: 110px; max-width: 150px; object-fit: cover;">
                                        <div class="small text-muted mt-1" style="font-size: 0.72rem;">Pratinjau Foto Kerusakan</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 6. Pelapor Identity Preview -->
                        <div class="col-12">
                            <div class="p-3 bg-light rounded-4 border d-flex align-items-center gap-3">
                                <div class="user-avatar" style="width: 36px; height: 36px; font-size: 0.85rem;">
                                    <?= strtoupper(substr($user['nama_lengkap'] ?? 'U', 0, 1)) ?>
                                </div>
                                <div class="overflow-hidden">
                                    <div class="small fw-bold text-dark">
                                        Pelapor: <?= htmlspecialchars($user['nama_lengkap'] ?? '') ?>
                                        <span class="badge bg-secondary-subtle text-secondary ms-1 small">
                                            <?= htmlspecialchars($user['nomor_induk'] ?? '') ?> (<?= ucfirst($user['jenis_pengguna'] ?? 'civitas') ?>)
                                        </span>
                                    </div>
                                    <div class="text-muted small" style="font-size: 0.75rem;">
                                        Tiket pengaduan ini akan didaftarkan atas nama Anda dan diteruskan secara instan ke Petugas Sarpras STMIK IKMI.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="col-12 pt-3 border-top mt-3 d-flex justify-content-end gap-2">
                            <a href="<?= base_url('laporan') ?>" class="btn btn-light rounded-pill px-4 small">Batal</a>
                            <button type="submit" class="btn btn-warning text-dark fw-semibold rounded-pill px-4 small shadow-sm d-inline-flex align-items-center gap-2">
                                <i class="bi bi-send-fill"></i>
                                <span>Kirim Laporan Kerusakan</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.priority-card {
    transition: all 0.2s ease;
    border: 1px solid #e2e8f0;
}
.priority-card:hover {
    border-color: #cbd5e1;
    background-color: #f8fafc;
}
.priority-card.active-priority {
    border-color: #0d6efd !important;
    background-color: #f0f7ff !important;
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.08);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Chained Dropdown: Ruangan -> Fasilitas
    const ruanganSelect = document.getElementById('ruangan_id');
    const fasilitasSelect = document.getElementById('fasilitas_id');
    const fasilitasLoading = document.getElementById('fasilitas_loading');

    if (ruanganSelect && fasilitasSelect) {
        ruanganSelect.addEventListener('change', () => {
            const ruanganId = ruanganSelect.value;
            if (!ruanganId) {
                fasilitasSelect.innerHTML = '<option value="">-- Pilih Ruangan Dahulu --</option>';
                return;
            }

            if (fasilitasLoading) {
                fasilitasLoading.textContent = 'Memuat daftar fasilitas di ruangan terpilih...';
            }

            fetch('<?= base_url('laporan/ajaxFasilitas?ruangan_id=') ?>' + encodeURIComponent(ruanganId))
                .then(res => res.json())
                .then(data => {
                    fasilitasSelect.innerHTML = '<option value="">-- Pilih Fasilitas / Perangkat --</option>';
                    if (data.length === 0) {
                        fasilitasSelect.innerHTML += '<option value="" disabled>Tidak ada inventaris tercatat di ruangan ini</option>';
                        if (fasilitasLoading) fasilitasLoading.textContent = 'Tidak ada inventaris tercatat di ruangan ini.';
                    } else {
                        data.forEach(f => {
                            fasilitasSelect.innerHTML += `<option value="${f.id}">${f.nama} (${f.kode_inventaris}) - Kondisi: ${f.kondisi}</option>`;
                        });
                        if (fasilitasLoading) fasilitasLoading.textContent = `Ditemukan ${data.length} fasilitas terdaftar di ruangan ini.`;
                    }
                })
                .catch(err => {
                    console.error('Error fetching facilities:', err);
                    if (fasilitasLoading) fasilitasLoading.textContent = 'Gagal memuat fasilitas ruangan.';
                });
        });
    }

    // 2. Live Character Counter for Deskripsi
    const deskripsiInput = document.getElementById('deskripsi');
    const charCount = document.getElementById('charCount');
    if (deskripsiInput && charCount) {
        function updateCharCount() {
            const len = deskripsiInput.value.length;
            charCount.textContent = len + ' / 2000';
            if (len >= 1900) {
                charCount.classList.add('text-danger');
            } else {
                charCount.classList.remove('text-danger');
            }
        }
        deskripsiInput.addEventListener('input', updateCharCount);
        updateCharCount();
    }

    // 3. Highlight Priority Card on Click
    const priorityRadios = document.querySelectorAll('input[name="prioritas"]');
    function updatePriorityStyles() {
        document.querySelectorAll('.priority-card').forEach(c => c.classList.remove('active-priority'));
        priorityRadios.forEach(radio => {
            if (radio.checked) {
                const parentCard = radio.closest('.priority-card');
                if (parentCard) parentCard.classList.add('active-priority');
            }
        });
    }
    priorityRadios.forEach(radio => {
        radio.addEventListener('change', updatePriorityStyles);
    });
    updatePriorityStyles();

    // 4. Live Photo Preview
    const fotoInput = document.getElementById('fotoKerusakanInput');
    const previewContainer = document.getElementById('imagePreviewContainer');
    const previewImg = document.getElementById('imagePreview');

    if (fotoInput && previewContainer && previewImg) {
        fotoInput.addEventListener('change', () => {
            const file = fotoInput.files[0];
            if (file) {
                // Client-side quick size validation
                if (file.size > 2 * 1024 * 1024) {
                    alert('Peringatan: Ukuran berkas melebihi batas 2 MB (' + (file.size / (1024 * 1024)).toFixed(2) + ' MB). Silakan pilih foto lain yang lebih kecil.');
                    fotoInput.value = '';
                    previewContainer.classList.add('d-none');
                    return;
                }
                const reader = new FileReader();
                reader.onload = (e) => {
                    previewImg.src = e.target.result;
                    previewContainer.classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            } else {
                previewContainer.classList.add('d-none');
            }
        });
    }
});
</script>
