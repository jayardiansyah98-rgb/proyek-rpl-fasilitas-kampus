<div class="container-fluid p-0">
    <!-- Breadcrumb & Header Card -->
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
        <div class="card-body p-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2 small">
                    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('laporan') ?>" class="text-decoration-none">Laporan Kerusakan</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($laporan['kode_laporan']) ?></li>
                </ol>
            </nav>
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-3 rounded-4 fs-3 <?= ($laporan['status'] === 'selesai') ? 'bg-success-subtle text-success' : (($laporan['status'] === 'ditolak') ? 'bg-danger-subtle text-danger' : 'bg-warning-subtle text-warning') ?>">
                        <i class="bi <?= ($laporan['status'] === 'selesai') ? 'bi-check2-circle' : (($laporan['status'] === 'ditolak') ? 'bi-x-circle' : 'bi-exclamation-octagon') ?>"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                            <span class="badge bg-primary px-3 py-1 rounded-pill font-monospace fs-6">
                                <?= htmlspecialchars($laporan['kode_laporan']) ?>
                            </span>

                            <!-- Status Badge -->
                            <?php if ($laporan['status'] === 'menunggu'): ?>
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3 py-1">
                                    <i class="bi bi-hourglass me-1"></i>Menunggu Verifikasi
                                </span>
                            <?php elseif ($laporan['status'] === 'diverifikasi'): ?>
                                <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-3 py-1">
                                    <i class="bi bi-patch-check me-1"></i>Diverifikasi
                                </span>
                            <?php elseif ($laporan['status'] === 'diproses'): ?>
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-1">
                                    <i class="bi bi-gear-wide-connected me-1"></i>Sedang Diproses
                                </span>
                            <?php elseif ($laporan['status'] === 'selesai'): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">
                                    <i class="bi bi-check-circle-fill me-1"></i>Selesai Tuntas
                                </span>
                            <?php else: ?>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1">
                                    <i class="bi bi-x-circle-fill me-1"></i>Ditolak
                                </span>
                            <?php endif; ?>

                            <!-- Priority Badge -->
                            <?php if ($laporan['prioritas'] === 'darurat'): ?>
                                <span class="badge bg-danger text-white rounded-pill px-3 py-1">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>Prioritas Darurat
                                </span>
                            <?php elseif ($laporan['prioritas'] === 'tinggi'): ?>
                                <span class="badge bg-warning text-dark rounded-pill px-3 py-1">
                                    <i class="bi bi-arrow-up-circle-fill me-1"></i>Prioritas Tinggi
                                </span>
                            <?php elseif ($laporan['prioritas'] === 'sedang'): ?>
                                <span class="badge bg-info text-dark rounded-pill px-3 py-1">
                                    <i class="bi bi-dash-circle me-1"></i>Prioritas Sedang
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary text-white rounded-pill px-3 py-1">
                                    <i class="bi bi-arrow-down-circle me-1"></i>Prioritas Rendah
                                </span>
                            <?php endif; ?>
                        </div>
                        <h4 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($laporan['nama_fasilitas']) ?></h4>
                        <small class="text-muted">
                            <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($laporan['nama_ruangan']) ?> &bull; <?= htmlspecialchars($laporan['nama_gedung']) ?> (Lantai <?= $laporan['lantai'] ?>)
                        </small>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?= base_url('laporan') ?>" class="btn btn-outline-secondary rounded-pill px-3 py-2 small">
                        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
                    </a>
                    <?php if (Auth::hasRole(['admin', 'petugas_sarpras'])): ?>
                    <a href="#panelAksiPetugas" class="btn btn-primary rounded-pill px-3 py-2 small fw-semibold shadow-sm">
                        <i class="bi bi-pencil-square me-1"></i> Update Status
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Timeline Status Laporan (Visual Stepper) -->
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4 overflow-hidden">
        <div class="card-header bg-white border-0 py-3 px-4">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="bi bi-bezier2 text-primary me-2"></i>Timeline Progres Penanganan
            </h6>
        </div>
        <div class="card-body px-4 py-4">
            <?php
            // Menentukan langkah timeline berdasarkan status
            $currentStatus = $laporan['status'];
            $isRejected = ($currentStatus === 'ditolak');

            if (!$isRejected) {
                $steps = [
                    'menunggu' => [
                        'label' => 'Laporan Dibuat',
                        'desc' => 'Tiket pengaduan masuk ke antrean Sarpras',
                        'icon' => 'bi-send-check',
                        'level' => 1
                    ],
                    'diverifikasi' => [
                        'label' => 'Diverifikasi Sarpras',
                        'desc' => 'Kondisi dicek & diverifikasi petugas',
                        'icon' => 'bi-patch-check',
                        'level' => 2
                    ],
                    'diproses' => [
                        'label' => 'Sedang Diproses',
                        'desc' => 'Teknisi melakukan perbaikan / servis fisik',
                        'icon' => 'bi-tools',
                        'level' => 3
                    ],
                    'selesai' => [
                        'label' => 'Selesai & Berfungsi',
                        'desc' => 'Fasilitas selesai diperbaiki & siap pakai',
                        'icon' => 'bi-check-circle-fill',
                        'level' => 4
                    ],
                ];

                $currentLevel = 1;
                if ($currentStatus === 'diverifikasi') $currentLevel = 2;
                elseif ($currentStatus === 'diproses') $currentLevel = 3;
                elseif ($currentStatus === 'selesai') $currentLevel = 4;
            } else {
                $steps = [
                    'menunggu' => [
                        'label' => 'Laporan Dibuat',
                        'desc' => 'Tiket pengaduan diajukan oleh civitas',
                        'icon' => 'bi-send-check',
                        'level' => 1
                    ],
                    'ditolak' => [
                        'label' => 'Pengaduan Ditolak',
                        'desc' => 'Tidak memenuhi kriteria / duplikasi laporan',
                        'icon' => 'bi-x-circle-fill',
                        'level' => 2
                    ]
                ];
                $currentLevel = 2;
            }
            ?>

            <!-- Stepper Container -->
            <div class="timeline-stepper">
                <div class="row g-3 justify-content-between position-relative">
                    <?php 
                    $totalSteps = count($steps);
                    $stepIndex = 0;
                    foreach ($steps as $key => $s): 
                        $stepIndex++;
                        $isCompleted = (!$isRejected && $currentLevel >= $s['level']) || ($isRejected && $key === 'menunggu');
                        $isActive = ($currentStatus === $key);
                    ?>
                    <div class="col text-center timeline-step-item">
                        <div class="timeline-node mb-2 mx-auto <?= $isActive ? ($isRejected ? 'node-danger active-pulse' : 'node-active active-pulse') : ($isCompleted ? 'node-completed' : 'node-pending') ?>">
                            <i class="bi <?= $s['icon'] ?> fs-5"></i>
                        </div>
                        <div class="fw-bold small <?= $isActive ? ($isRejected ? 'text-danger' : 'text-primary') : ($isCompleted ? 'text-dark' : 'text-muted') ?>">
                            <?= $s['label'] ?>
                        </div>
                        <div class="text-muted d-none d-md-block mt-1" style="font-size: 0.72rem; line-height: 1.2;">
                            <?= $s['desc'] ?>
                        </div>
                        <?php if ($key === 'menunggu'): ?>
                            <div class="badge bg-light text-secondary border mt-1" style="font-size: 0.7rem;">
                                <?= date('d M H:i', strtotime($laporan['created_at'])) ?>
                            </div>
                        <?php elseif ($isActive && $key !== 'menunggu'): ?>
                            <div class="badge bg-light text-primary border mt-1" style="font-size: 0.7rem;">
                                Update: <?= date('d M H:i', strtotime($laporan['updated_at'])) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Detail Grid -->
    <div class="row g-4 mb-4">
        <!-- Kolom Kiri: Deskripsi, Foto Kerusakan, & Catatan Petugas -->
        <div class="col-lg-8">
            <!-- Deskripsi Kerusakan -->
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
                <h6 class="fw-bold text-dark border-bottom pb-3 mb-3">
                    <i class="bi bi-chat-square-dots text-primary me-2"></i>Rincian Pengaduan Kerusakan
                </h6>
                <div class="mb-3">
                    <label class="text-muted small fw-semibold">Uraian Gejala / Masalah:</label>
                    <div class="p-3 bg-light rounded-4 text-dark mt-1" style="white-space: pre-line; line-height: 1.6;">
                        <?= htmlspecialchars($laporan['deskripsi']) ?>
                    </div>
                </div>
                <div class="row g-3 text-muted small">
                    <div class="col-sm-6">
                        <i class="bi bi-clock me-1 text-primary"></i>Waktu Dilaporkan: <strong><?= date('d F Y, H:i', strtotime($laporan['created_at'])) ?> WIB</strong>
                    </div>
                    <div class="col-sm-6">
                        <i class="bi bi-arrow-repeat me-1 text-primary"></i>Pembaruan Terakhir: <strong><?= date('d F Y, H:i', strtotime($laporan['updated_at'])) ?> WIB</strong>
                    </div>
                </div>
            </div>

            <!-- Foto Bukti Kerusakan -->
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
                <h6 class="fw-bold text-dark border-bottom pb-3 mb-3 d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-camera text-primary me-2"></i>Foto Bukti Kerusakan</span>
                    <?php if (!empty($laporan['foto'])): ?>
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small">Foto Terlampir</span>
                    <?php endif; ?>
                </h6>
                <?php if (!empty($laporan['foto'])): ?>
                    <?php $fotoUrl = base_url('uploads/laporan/' . htmlspecialchars($laporan['foto'])); ?>
                    <div class="text-center p-3 bg-light rounded-4 border">
                        <a href="<?= $fotoUrl ?>" target="_blank" data-bs-toggle="modal" data-bs-target="#modalFotoZoom">
                            <img src="<?= $fotoUrl ?>" alt="Foto Bukti Kerusakan" class="img-fluid rounded-3 shadow-sm border" style="max-height: 380px; width: auto; object-fit: contain;">
                        </a>
                        <div class="mt-2">
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalFotoZoom">
                                <i class="bi bi-zoom-in me-1"></i> Perbesar Foto
                            </button>
                            <a href="<?= $fotoUrl ?>" download class="btn btn-sm btn-outline-secondary rounded-pill px-3 ms-1">
                                <i class="bi bi-download me-1"></i> Unduh Berkas
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="p-4 bg-light rounded-4 text-center text-muted">
                        <i class="bi bi-camera-slash fs-2 d-block mb-1 text-secondary opacity-50"></i>
                        <span class="small">Tidak ada foto bukti yang dilampirkan oleh pelapor saat pengaduan diajukan.</span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Catatan Tindak Lanjut Petugas -->
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
                <h6 class="fw-bold text-dark border-bottom pb-3 mb-3">
                    <i class="bi bi-journal-text text-primary me-2"></i>Catatan & Tindak Lanjut Bagian Sarpras
                </h6>
                <?php if (!empty($laporan['catatan_petugas'])): ?>
                    <div class="p-3 rounded-4 border <?= ($laporan['status'] === 'ditolak') ? 'bg-danger-subtle text-danger border-danger-subtle' : 'bg-info-subtle text-dark border-info-subtle' ?>">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi <?= ($laporan['status'] === 'ditolak') ? 'bi-x-octagon-fill text-danger' : 'bi-info-circle-fill text-info' ?> fs-5"></i>
                            <strong class="small"><?= ($laporan['status'] === 'ditolak') ? 'Alasan Penolakan Tiket:' : 'Instruksi / Catatan Petugas:' ?></strong>
                        </div>
                        <div class="small" style="white-space: pre-line; line-height: 1.5;">
                            <?= htmlspecialchars($laporan['catatan_petugas']) ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="p-3 bg-light rounded-4 text-muted small fst-italic text-center">
                        <i class="bi bi-chat-left-dots me-1"></i>Belum ada catatan atau instruksi penanganan dari Petugas Sarpras.
                    </div>
                <?php endif; ?>
            </div>

            <!-- Panel Khusus Petugas Sarpras & Admin: Update Status -->
            <?php if (Auth::hasRole(['admin', 'petugas_sarpras'])): ?>
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4" id="panelAksiPetugas">
                <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                    <div>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill small mb-1">
                            <i class="bi bi-shield-check me-1"></i> Hak Akses Petugas
                        </span>
                        <h6 class="fw-bold text-dark mb-0">Tindakan Petugas: Ubah Status & Prioritas</h6>
                    </div>
                    <?php if (Auth::hasRole('admin')): ?>
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalHapusLaporan">
                        <i class="bi bi-trash3 me-1"></i> Hapus Laporan
                    </button>
                    <?php endif; ?>
                </div>

                <form action="<?= base_url('laporan/updateStatus/' . $laporan['id']) ?>" method="POST">
                    <?= csrf_field() ?>
                    <div class="row g-3">
                        <!-- Status Selection -->
                        <div class="col-md-6">
                            <label for="statusSelect" class="form-label small fw-semibold text-dark">
                                Status Penanganan <span class="text-danger">*</span>
                            </label>
                            <select name="status" id="statusSelect" class="form-select small fw-semibold" required>
                                <option value="menunggu" <?= ($laporan['status'] === 'menunggu') ? 'selected' : '' ?>>Menunggu Verifikasi</option>
                                <option value="diverifikasi" <?= ($laporan['status'] === 'diverifikasi') ? 'selected' : '' ?>>Diverifikasi (Masuk Antrean)</option>
                                <option value="diproses" <?= ($laporan['status'] === 'diproses') ? 'selected' : '' ?>>Diproses (Sedang Dikerjakan)</option>
                                <option value="selesai" <?= ($laporan['status'] === 'selesai') ? 'selected' : '' ?>>Selesai (Perbaikan Tuntas)</option>
                                <option value="ditolak" <?= ($laporan['status'] === 'ditolak') ? 'selected' : '' ?>>Ditolak (Batal / Tidak Valid)</option>
                            </select>
                            <div class="form-text small">Ubah status alur penanganan sesuai tahapan perbaikan fisik.</div>
                        </div>

                        <!-- Priority Selection -->
                        <div class="col-md-6">
                            <label for="prioritasSelect" class="form-label small fw-semibold text-dark">
                                Tingkat Prioritas <span class="text-danger">*</span>
                            </label>
                            <select name="prioritas" id="prioritasSelect" class="form-select small fw-semibold" required>
                                <option value="rendah" <?= ($laporan['prioritas'] === 'rendah') ? 'selected' : '' ?>>Rendah (Kerusakan Ringan)</option>
                                <option value="sedang" <?= ($laporan['prioritas'] === 'sedang') ? 'selected' : '' ?>>Sedang (Fungsi Terganggu)</option>
                                <option value="tinggi" <?= ($laporan['prioritas'] === 'tinggi') ? 'selected' : '' ?>>Tinggi (Mendesak / Mogok)</option>
                                <option value="darurat" <?= ($laporan['prioritas'] === 'darurat') ? 'selected' : '' ?>>Darurat (Bahaya Fatal)</option>
                            </select>
                            <div class="form-text small">Petugas dapat mengkoreksi prioritas berdasarkan urgensi riil.</div>
                        </div>

                        <!-- Catatan Petugas -->
                        <div class="col-12">
                            <label for="catatan_petugas" class="form-label small fw-semibold text-dark">
                                Catatan / Umpan Balik Petugas
                            </label>
                            <textarea name="catatan_petugas" id="catatan_petugas" rows="3" class="form-control small" 
                                      placeholder="Contoh: Telah dijadwalkan pengecekan modul oleh teknisi Budi Santoso besok pagi..."><?= htmlspecialchars($laporan['catatan_petugas'] ?? '') ?></textarea>
                            <div class="form-text small">Catatan ini dapat dibaca oleh pelapor sebagai laporan progres atau alasan bila tiket ditolak.</div>
                        </div>

                        <!-- Submit Button -->
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 small fw-semibold shadow-sm">
                                <i class="bi bi-check2-circle me-1"></i> Simpan Perubahan Status
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <?php endif; ?>
        </div>

        <!-- Kolom Kanan: Identitas Fasilitas, Ruangan, & Pelapor -->
        <div class="col-lg-4">
            <!-- Info Fasilitas & Inventaris -->
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
                <h6 class="fw-bold text-dark border-bottom pb-3 mb-3">
                    <i class="bi bi-box-seam text-primary me-2"></i>Data Inventaris Fasilitas
                </h6>
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="p-3 bg-light rounded-4 text-center" style="width: 60px; height: 60px;">
                        <i class="bi bi-laptop fs-3 text-secondary"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark"><?= htmlspecialchars($laporan['nama_fasilitas']) ?></div>
                        <span class="badge bg-light text-primary border font-monospace mt-1">
                            <?= htmlspecialchars($laporan['kode_inventaris']) ?>
                        </span>
                    </div>
                </div>
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item px-0 d-flex justify-content-between">
                        <span class="text-muted">Kondisi Master:</span>
                        <span class="badge bg-secondary-subtle text-secondary"><?= ucfirst($laporan['kondisi_fasilitas'] ?? 'baik') ?></span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                        <span class="text-muted">Ruangan:</span>
                        <span class="fw-semibold text-dark text-end"><?= htmlspecialchars($laporan['nama_ruangan']) ?></span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                        <span class="text-muted">Gedung Kampus:</span>
                        <span class="fw-semibold text-dark text-end"><?= htmlspecialchars($laporan['nama_gedung']) ?></span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                        <span class="text-muted">Lantai Penempatan:</span>
                        <span class="fw-semibold text-dark">Lantai <?= $laporan['lantai'] ?></span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                        <span class="text-muted">Jenis Ruangan:</span>
                        <span class="text-dark"><?= htmlspecialchars($laporan['jenis_ruangan'] ?? 'Kelas') ?></span>
                    </li>
                </ul>
                <div class="mt-3 text-center">
                    <a href="<?= base_url('ruangan/detail/' . $laporan['ruangan_id']) ?>" class="btn btn-outline-primary btn-sm rounded-pill w-100">
                        <i class="bi bi-door-open me-1"></i> Buka Detail Ruangan
                    </a>
                </div>
            </div>

            <!-- Info Pelapor Civitas -->
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
                <h6 class="fw-bold text-dark border-bottom pb-3 mb-3">
                    <i class="bi bi-person-badge text-primary me-2"></i>Informasi Pelapor
                </h6>
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="user-avatar" style="width: 44px; height: 44px; font-size: 1rem;">
                        <?= strtoupper(substr($laporan['pelapor'] ?? 'U', 0, 1)) ?>
                    </div>
                    <div class="overflow-hidden">
                        <div class="fw-bold text-dark text-truncate"><?= htmlspecialchars($laporan['pelapor']) ?></div>
                        <div class="text-muted small"><?= htmlspecialchars($laporan['nomor_induk'] ?? '-') ?></div>
                    </div>
                </div>
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item px-0 d-flex justify-content-between">
                        <span class="text-muted">Jenis Civitas:</span>
                        <span class="badge bg-primary-subtle text-primary text-uppercase"><?= htmlspecialchars($laporan['jenis_pengguna'] ?? 'civitas') ?></span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                        <span class="text-muted">Email:</span>
                        <span class="text-dark text-truncate" style="max-width: 170px;" title="<?= htmlspecialchars($laporan['email_pelapor'] ?? '-') ?>">
                            <?= htmlspecialchars($laporan['email_pelapor'] ?? '-') ?>
                        </span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                        <span class="text-muted">No. Telepon:</span>
                        <span class="text-dark"><?= htmlspecialchars($laporan['telepon_pelapor'] ?? '-') ?></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Modal Zoom Foto Bukti Kerusakan -->
<?php if (!empty($laporan['foto'])): ?>
<div class="modal fade" id="modalFotoZoom" tabindex="-1" aria-labelledby="modalFotoZoomLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold text-dark" id="modalFotoZoomLabel">
                    <i class="bi bi-camera-fill text-primary me-1"></i> Foto Bukti Kerusakan: <?= htmlspecialchars($laporan['kode_laporan']) ?>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4">
                <img src="<?= base_url('uploads/laporan/' . htmlspecialchars($laporan['foto'])) ?>" 
                     alt="Foto Kerusakan Resolusi Penuh" 
                     class="img-fluid rounded-3 shadow-sm border" style="max-height: 80vh;">
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-between">
                <small class="text-muted"><?= htmlspecialchars($laporan['nama_fasilitas']) ?> &bull; <?= htmlspecialchars($laporan['nama_ruangan']) ?></small>
                <button type="button" class="btn btn-secondary rounded-pill px-4 btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal Konfirmasi Hapus Laporan (Admin Only) -->
<?php if (Auth::hasRole('admin')): ?>
<div class="modal fade" id="modalHapusLaporan" tabindex="-1" aria-labelledby="modalHapusLaporanLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
            <div class="modal-body text-center p-4">
                <div class="mb-3">
                    <span class="badge bg-danger bg-opacity-10 text-danger p-3 rounded-circle fs-3">
                        <i class="bi bi-trash3-fill"></i>
                    </span>
                </div>
                <h5 class="fw-bold mb-2 text-dark" id="modalHapusLaporanLabel">Hapus Tiket Laporan?</h5>
                <p class="text-muted small mb-4">
                    Apakah Anda yakin ingin menghapus tiket <strong><?= htmlspecialchars($laporan['kode_laporan']) ?></strong>? Tindakan ini tidak dapat dibatalkan dan berkas foto fisik akan dihapus.
                </p>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-light px-3 rounded-pill small" data-bs-dismiss="modal">Batal</button>
                    <a href="<?= base_url('laporan/hapus/' . $laporan['id'] . '?csrf_token=' . csrf_token()) ?>" class="btn btn-danger px-4 rounded-pill small fw-semibold">
                        Ya, Hapus
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
/* Stepper Styles */
.timeline-stepper {
    position: relative;
    padding: 10px 0;
}
.timeline-stepper::before {
    content: '';
    position: absolute;
    top: 32px;
    left: 8%;
    right: 8%;
    height: 3px;
    background-color: #e2e8f0;
    z-index: 1;
}
.timeline-step-item {
    position: relative;
    z-index: 2;
}
.timeline-node {
    width: 46px;
    height: 46px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #ffffff;
    transition: all 0.3s ease;
}
.node-completed {
    background-color: #0d6efd;
    color: #ffffff;
    box-shadow: 0 4px 10px rgba(13, 110, 253, 0.25);
}
.node-active {
    background-color: #0d6efd;
    color: #ffffff;
    border: 3px solid #bfdbfe;
    box-shadow: 0 4px 14px rgba(13, 110, 253, 0.35);
}
.node-danger {
    background-color: #dc3545;
    color: #ffffff;
    border: 3px solid #fecaca;
    box-shadow: 0 4px 14px rgba(220, 53, 69, 0.35);
}
.node-pending {
    background-color: #f1f5f9;
    color: #94a3b8;
    border: 2px solid #cbd5e1;
}
.active-pulse {
    animation: pulseGlow 2s infinite;
}
@keyframes pulseGlow {
    0% {
        box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.4);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(13, 110, 253, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(13, 110, 253, 0);
    }
}
</style>
