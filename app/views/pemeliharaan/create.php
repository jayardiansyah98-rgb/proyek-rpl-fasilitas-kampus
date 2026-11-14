<div class="container-fluid p-0">
    <!-- Breadcrumb & Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('pemeliharaan') ?>" class="text-decoration-none">Pemeliharaan</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Catat Aktivitas Baru</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0 text-dark">Catat Aktivitas Pemeliharaan Fasilitas</h4>
            <p class="text-muted small mb-0">Input laporan pemeriksaan teknis, tindakan perbaikan, atau servis rutin sarana kampus.</p>
        </div>

        <div>
            <a href="<?= base_url('pemeliharaan') ?>" class="btn btn-outline-secondary rounded-pill px-3 btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Riwayat
            </a>
        </div>
    </div>

    <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-danger rounded-4 border-0 small py-2 px-3 mb-4">
            <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= htmlspecialchars($errors['general']) ?>
        </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-9">
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-tools text-primary me-2"></i>Formulir Data Pemeliharaan & Perbaikan
                    </h6>
                </div>

                <form action="<?= base_url('pemeliharaan/tambah') ?>" method="POST" class="p-4">
                    <?= csrf_field() ?>
                    <?php if (!empty($old['laporan_id'])): ?>
                        <input type="hidden" name="laporan_id" value="<?= htmlspecialchars($old['laporan_id']) ?>">
                        <div class="alert alert-warning py-2 px-3 rounded-3 small mb-4 d-flex align-items-center gap-2">
                            <i class="bi bi-link-45deg fs-5"></i>
                            <div>
                                Tindakan ini terhubung langsung dengan <strong>Tiket Laporan Kerusakan #<?= htmlspecialchars($old['laporan_id']) ?></strong>.
                                Status tiket akan diperbarui secara otomatis.
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="row g-3">
                        <!-- 1. Pemilihan Fasilitas -->
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold text-dark">
                                Fasilitas Sarpras <span class="text-danger">*</span>
                            </label>
                            <select class="form-select <?= !empty($errors['fasilitas_id']) ? 'is-invalid' : '' ?>" 
                                    name="fasilitas_id" id="selectFasilitas" required>
                                <option value="">-- Pilih Fasilitas yang Diperiksa/Dipelihara --</option>
                                <?php foreach ($fasilitas_list as $f): ?>
                                    <option value="<?= $f['id'] ?>" 
                                            data-kondisi="<?= htmlspecialchars($f['kondisi']) ?>"
                                            <?= ($old['fasilitas_id'] == $f['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($f['nama_fasilitas']) ?> (<?= htmlspecialchars($f['kode_inventaris']) ?>) - Ruang: <?= htmlspecialchars($f['nama_ruangan']) ?> [Kondisi: <?= ucfirst(str_replace('_', ' ', $f['kondisi'])) ?>]
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (!empty($errors['fasilitas_id'])): ?>
                                <div class="invalid-feedback small"><?= $errors['fasilitas_id'] ?></div>
                            <?php endif; ?>
                            <small class="text-muted" style="font-size: 0.75rem;">Pilih barang/fasilitas yang menjadi objek tindakan teknisi.</small>
                        </div>

                        <!-- 2. Tanggal Pemeliharaan -->
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-dark">
                                Tanggal Tindakan <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control <?= !empty($errors['tanggal']) ? 'is-invalid' : '' ?>" 
                                   name="tanggal" value="<?= htmlspecialchars($old['tanggal']) ?>" required>
                            <?php if (!empty($errors['tanggal'])): ?>
                                <div class="invalid-feedback small"><?= $errors['tanggal'] ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- 3. Jenis Tindakan -->
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">
                                Jenis Tindakan <span class="text-danger">*</span>
                            </label>
                            <select class="form-select <?= !empty($errors['jenis_tindakan']) ? 'is-invalid' : '' ?>" 
                                    name="jenis_tindakan" required>
                                <option value="Pemeliharaan Rutin" <?= ($old['jenis_tindakan'] === 'Pemeliharaan Rutin') ? 'selected' : '' ?>>Pemeliharaan Rutin (Pembersihan, Cuci AC, Cek Berkala)</option>
                                <option value="Pemeriksaan" <?= ($old['jenis_tindakan'] === 'Pemeriksaan') ? 'selected' : '' ?>>Pemeriksaan (Inspeksi Fisik, Pengujian Fungsi, Diagnostik)</option>
                                <option value="Perbaikan" <?= ($old['jenis_tindakan'] === 'Perbaikan') ? 'selected' : '' ?>>Perbaikan (Reparasi Kerusakan, Pematrian, Instal Ulang)</option>
                                <option value="Penggantian" <?= ($old['jenis_tindakan'] === 'Penggantian') ? 'selected' : '' ?>>Penggantian (Ganti Suku Cadang, Penggantian Unit)</option>
                            </select>
                            <?php if (!empty($errors['jenis_tindakan'])): ?>
                                <div class="invalid-feedback small"><?= $errors['jenis_tindakan'] ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- 4. Petugas Teknisi -->
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">
                                Petugas Pelaksana / Teknisi <span class="text-danger">*</span>
                            </label>
                            <?php if (Auth::hasRole('admin')): ?>
                                <select class="form-select" name="petugas_id" required>
                                    <?php foreach ($petugas_list as $pet): ?>
                                        <option value="<?= $pet['id'] ?>" <?= ($old['petugas_id'] == $pet['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($pet['nama_lengkap']) ?> (<?= htmlspecialchars($pet['nomor_induk']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php else: ?>
                                <input type="text" class="form-control bg-light" value="<?= htmlspecialchars(Auth::user()['nama_lengkap']) ?>" readonly>
                                <input type="hidden" name="petugas_id" value="<?= Auth::id() ?>">
                            <?php endif; ?>
                        </div>

                        <!-- 5. Perubahan Kondisi Fisik -->
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">
                                Kondisi Sebelum Pemeliharaan <span class="text-danger">*</span>
                            </label>
                            <select class="form-select <?= !empty($errors['kondisi_sebelum']) ? 'is-invalid' : '' ?>" 
                                    name="kondisi_sebelum" id="kondisiSebelum" required>
                                <option value="baik" <?= ($old['kondisi_sebelum'] === 'baik') ? 'selected' : '' ?>>Baik (Normal)</option>
                                <option value="rusak_ringan" <?= ($old['kondisi_sebelum'] === 'rusak_ringan') ? 'selected' : '' ?>>Rusak Ringan</option>
                                <option value="rusak_berat" <?= ($old['kondisi_sebelum'] === 'rusak_berat') ? 'selected' : '' ?>>Rusak Berat</option>
                                <option value="tidak_layak" <?= ($old['kondisi_sebelum'] === 'tidak_layak') ? 'selected' : '' ?>>Tidak Layak Pakai</option>
                                <option value="hilang" <?= ($old['kondisi_sebelum'] === 'hilang') ? 'selected' : '' ?>>Hilang</option>
                            </select>
                            <?php if (!empty($errors['kondisi_sebelum'])): ?>
                                <div class="invalid-feedback small"><?= $errors['kondisi_sebelum'] ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">
                                Kondisi Sesudah Pemeliharaan <span class="text-danger">*</span>
                            </label>
                            <select class="form-select <?= !empty($errors['kondisi_sesudah']) ? 'is-invalid' : '' ?>" 
                                    name="kondisi_sesudah" id="kondisiSesudah" required>
                                <option value="baik" <?= ($old['kondisi_sesudah'] === 'baik') ? 'selected' : '' ?>>Baik (Siap Pakai & Berfungsi Normal)</option>
                                <option value="rusak_ringan" <?= ($old['kondisi_sesudah'] === 'rusak_ringan') ? 'selected' : '' ?>>Rusak Ringan (Masih Perlu Penanganan Lanjutan)</option>
                                <option value="rusak_berat" <?= ($old['kondisi_sesudah'] === 'rusak_berat') ? 'selected' : '' ?>>Rusak Berat (Belum Berhasil Diperbaiki)</option>
                                <option value="tidak_layak" <?= ($old['kondisi_sesudah'] === 'tidak_layak') ? 'selected' : '' ?>>Tidak Layak Pakai (Afkir/Usang)</option>
                                <option value="hilang" <?= ($old['kondisi_sesudah'] === 'hilang') ? 'selected' : '' ?>>Hilang</option>
                            </select>
                            <?php if (!empty($errors['kondisi_sesudah'])): ?>
                                <div class="invalid-feedback small"><?= $errors['kondisi_sesudah'] ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Checkbox Ubah Kondisi Fasilitas -->
                        <div class="col-12">
                            <div class="form-check form-switch p-3 bg-light rounded-3 ms-0">
                                <input class="form-check-input ms-0 me-3" type="checkbox" role="switch" 
                                       id="ubahKondisiCheck" name="ubah_kondisi_fasilitas" value="1" 
                                       <?= ($old['ubah_kondisi_fasilitas'] === '1') ? 'checked' : '' ?>>
                                <label class="form-check-label small fw-semibold text-dark" for="ubahKondisiCheck">
                                    Perbarui status kelaikan fasilitas secara otomatis
                                    <span class="d-block text-muted fw-normal" style="font-size: 0.75rem;">
                                        Jika dicentang, kolom <em>kondisi</em> pada data inventaris fasilitas akan disinkronkan dengan <strong>Kondisi Sesudah Pemeliharaan</strong>.
                                    </span>
                                </label>
                            </div>
                        </div>

                        <!-- 6. Deskripsi Tindakan -->
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-dark">
                                Deskripsi Tindakan Pemeliharaan <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control <?= !empty($errors['deskripsi']) ? 'is-invalid' : '' ?>" 
                                      name="deskripsi" rows="3" required 
                                      placeholder="Jelaskan secara ringkas tindakan teknis yang dilakukan, bagian yang diperbaiki/dibersihkan, atau hasil inspeksi..."><?= htmlspecialchars($old['deskripsi']) ?></textarea>
                            <?php if (!empty($errors['deskripsi'])): ?>
                                <div class="invalid-feedback small"><?= $errors['deskripsi'] ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- 7. Biaya & Status -->
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">
                                Biaya Pemeliharaan (Rp)
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted small">Rp</span>
                                <input type="number" step="1000" min="0" 
                                       class="form-control <?= !empty($errors['biaya']) ? 'is-invalid' : '' ?>" 
                                       name="biaya" value="<?= htmlspecialchars($old['biaya']) ?>" placeholder="0">
                            </div>
                            <?php if (!empty($errors['biaya'])): ?>
                                <div class="invalid-feedback d-block small"><?= $errors['biaya'] ?></div>
                            <?php endif; ?>
                            <small class="text-muted" style="font-size: 0.75rem;">Biaya suku cadang, jasa servis luar, freon, atau komponen pengganti (isi 0 jika tanpa biaya).</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">
                                Status Pelaksanaan <span class="text-danger">*</span>
                            </label>
                            <select class="form-select <?= !empty($errors['status']) ? 'is-invalid' : '' ?>" 
                                    name="status" required>
                                <option value="selesai" <?= ($old['status'] === 'selesai') ? 'selected' : '' ?>>Selesai Dikerjakan</option>
                                <option value="sedang_dikerjakan" <?= ($old['status'] === 'sedang_dikerjakan') ? 'selected' : '' ?>>Sedang Dikerjakan (In Progress)</option>
                                <option value="dijadwalkan" <?= ($old['status'] === 'dijadwalkan') ? 'selected' : '' ?>>Dijadwalkan (Menunggu Eksekusi)</option>
                                <option value="batal" <?= ($old['status'] === 'batal') ? 'selected' : '' ?>>Dibatalkan</option>
                            </select>
                            <?php if (!empty($errors['status'])): ?>
                                <div class="invalid-feedback small"><?= $errors['status'] ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- 8. Keterangan Tambahan -->
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-dark">
                                Keterangan Tambahan / Catatan Internal
                            </label>
                            <textarea class="form-control" name="keterangan" rows="2" 
                                      placeholder="Catatan tambahan teknisi, vendor pihak ketiga, masa garansi servis, nomor nota/faktur..."><?= htmlspecialchars($old['keterangan']) ?></textarea>
                        </div>

                        <!-- 9. Tampil untuk Publik (Dosen & Mahasiswa) -->
                        <div class="col-12">
                            <div class="form-check form-switch p-3 bg-light rounded-3 ms-0">
                                <input class="form-check-input ms-0 me-3" type="checkbox" role="switch" 
                                       id="tampilPublikCheck" name="tampil_publik" value="1" 
                                       <?= ($old['tampil_publik'] == 1) ? 'checked' : '' ?>>
                                <label class="form-check-label small fw-semibold text-dark" for="tampilPublikCheck">
                                    Tampilkan informasi pemeliharaan ini untuk publik (Civitas / Dosen & Mahasiswa)
                                    <span class="d-block text-muted fw-normal" style="font-size: 0.75rem;">
                                        Jika diaktifkan, catatan ini akan terlihat pada riwayat pemeliharaan di katalog fasilitas mahasiswa & dosen. Jika dinonaktifkan, catatan hanya dapat diakses internal tim Sarpras.
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Aksi Simpan -->
                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="<?= base_url('pemeliharaan') ?>" class="btn btn-outline-secondary rounded-pill px-4 btn-sm">Batal</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 btn-sm fw-semibold shadow-sm">
                            <i class="bi bi-check-lg me-1"></i> Simpan Catatan Pemeliharaan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectFasilitas = document.getElementById('selectFasilitas');
    const kondisiSebelum = document.getElementById('kondisiSebelum');
    const kondisiSesudah = document.getElementById('kondisiSesudah');

    if (selectFasilitas) {
        selectFasilitas.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const currentKondisi = selectedOption.getAttribute('data-kondisi');
            if (currentKondisi && kondisiSebelum) {
                kondisiSebelum.value = currentKondisi;
                if (currentKondisi !== 'baik') {
                    kondisiSesudah.value = 'baik';
                }
            }
        });
    }
});
</script>
