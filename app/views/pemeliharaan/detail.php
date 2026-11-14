<div class="container-fluid p-0">
    <!-- Breadcrumb & Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('pemeliharaan') ?>" class="text-decoration-none">Pemeliharaan</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($pemeliharaan['kode_pemeliharaan']) ?></li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0 text-dark">Rincian Dokumen Pemeliharaan</h4>
            <span class="text-muted small">
                Kode: <strong class="font-monospace text-primary"><?= htmlspecialchars($pemeliharaan['kode_pemeliharaan']) ?></strong> &bull;
                Tanggal: <?= date('d F Y', strtotime(!empty($pemeliharaan['tanggal']) ? $pemeliharaan['tanggal'] : $pemeliharaan['tanggal_mulai'])) ?>
            </span>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="<?= base_url('pemeliharaan') ?>" class="btn btn-outline-secondary rounded-pill px-3 btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
            </a>

            <button type="button" class="btn btn-outline-secondary rounded-pill px-3 btn-sm" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Cetak Dokumen
            </button>

            <?php if (Auth::hasRole(['admin', 'petugas_sarpras'])): ?>
                <a href="<?= base_url('pemeliharaan/edit/' . $pemeliharaan['id']) ?>" class="btn btn-primary rounded-pill px-3 btn-sm fw-semibold shadow-sm">
                    <i class="bi bi-pencil me-1"></i> Edit Data
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-4">
        <!-- Kolom Kiri: Rincian Tindakan Pemeliharaan -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 bg-white mb-4 overflow-hidden">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-tools text-primary me-2"></i>Laporan Aktivitas Pemeliharaan
                    </h6>
                    <div>
                        <?php 
                        $statusBadgeClass = 'bg-secondary';
                        if ($pemeliharaan['status'] === 'selesai') $statusBadgeClass = 'bg-success-subtle text-success border border-success-subtle';
                        elseif ($pemeliharaan['status'] === 'sedang_dikerjakan') $statusBadgeClass = 'bg-warning-subtle text-warning-emphasis border border-warning-subtle';
                        elseif ($pemeliharaan['status'] === 'dijadwalkan') $statusBadgeClass = 'bg-info-subtle text-info border border-info-subtle';
                        ?>
                        <span class="badge <?= $statusBadgeClass ?> px-3 py-1 rounded-pill fw-semibold">
                            Status: <?= ucfirst(str_replace('_', ' ', $pemeliharaan['status'])) ?>
                        </span>
                    </div>
                </div>

                <div class="card-body p-4">
                    <!-- Widget Transisi Kondisi Fisik -->
                    <div class="p-3 bg-light rounded-4 mb-4 border">
                        <div class="text-muted small fw-semibold mb-2">Evaluasi Perubahan Kondisi Fisik:</div>
                        <div class="d-flex align-items-center justify-content-around flex-wrap gap-3 text-center">
                            <div>
                                <span class="text-muted small d-block mb-1">Kondisi Sebelum</span>
                                <?php 
                                $badgeSeb = ($pemeliharaan['kondisi_sebelum'] === 'baik') ? 'bg-success text-white' : 'bg-warning text-dark';
                                ?>
                                <span class="badge <?= $badgeSeb ?> px-3 py-2 rounded-pill fs-6">
                                    <?= ucfirst(str_replace('_', ' ', $pemeliharaan['kondisi_sebelum'] ?? 'baik')) ?>
                                </span>
                            </div>

                            <div class="text-primary d-flex flex-column align-items-center">
                                <i class="bi bi-arrow-right fs-3"></i>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0 small">
                                    <?= htmlspecialchars($pemeliharaan['jenis_tindakan']) ?>
                                </span>
                            </div>

                            <div>
                                <span class="text-muted small d-block mb-1">Kondisi Sesudah</span>
                                <?php 
                                $badgeSes = ($pemeliharaan['kondisi_sesudah'] === 'baik') ? 'bg-success text-white' : 'bg-warning text-dark';
                                ?>
                                <span class="badge <?= $badgeSes ?> px-3 py-2 rounded-pill fs-6">
                                    <?= ucfirst(str_replace('_', ' ', $pemeliharaan['kondisi_sesudah'] ?? 'baik')) ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Informasi Spesifik -->
                    <div class="row g-3 mb-4">
                        <div class="col-sm-6 col-md-4">
                            <small class="text-muted d-block">Jenis Tindakan</small>
                            <span class="fw-bold text-dark fs-6"><?= htmlspecialchars($pemeliharaan['jenis_tindakan']) ?></span>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <small class="text-muted d-block">Tanggal Pelaksanaan</small>
                            <span class="fw-bold text-dark fs-6">
                                <?= date('d M Y', strtotime(!empty($pemeliharaan['tanggal']) ? $pemeliharaan['tanggal'] : $pemeliharaan['tanggal_mulai'])) ?>
                            </span>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <small class="text-muted d-block">Biaya Pemeliharaan</small>
                            <span class="fw-bold text-success fs-5">
                                Rp <?= number_format($pemeliharaan['biaya'], 0, ',', '.') ?>
                            </span>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <small class="text-muted d-block">Teknisi Pelaksana</small>
                            <span class="fw-semibold text-dark"><?= htmlspecialchars($pemeliharaan['nama_petugas']) ?></span>
                            <small class="text-muted d-block">(<?= htmlspecialchars($pemeliharaan['nip_petugas']) ?>)</small>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <small class="text-muted d-block">Aksesibilitas Data</small>
                            <?php if ($pemeliharaan['tampil_publik'] == 1): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill small">
                                    <i class="bi bi-eye me-1"></i> Ditampilkan untuk Publik
                                </span>
                            <?php else: ?>
                                <span class="badge bg-dark-subtle text-dark border border-dark-subtle px-2 py-1 rounded-pill small">
                                    <i class="bi bi-eye-slash me-1"></i> Khusus Internal Sarpras
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <small class="text-muted d-block">Waktu Pencatatan</small>
                            <span class="text-muted small"><?= date('d/m/Y H:i', strtotime($pemeliharaan['created_at'])) ?></span>
                        </div>
                    </div>

                    <!-- Deskripsi Tindakan -->
                    <div class="mb-4">
                        <label class="fw-bold text-dark small d-block mb-1">Deskripsi Pekerjaan / Tindakan Teknis:</label>
                        <div class="p-3 bg-light rounded-3 text-dark small" style="white-space: pre-line;">
                            <?= htmlspecialchars($pemeliharaan['deskripsi'] ?? $pemeliharaan['keterangan'] ?? '-') ?>
                        </div>
                    </div>

                    <!-- Keterangan Tambahan / Internal -->
                    <?php if (!empty($pemeliharaan['keterangan']) && $pemeliharaan['keterangan'] !== $pemeliharaan['deskripsi']): ?>
                    <div class="mb-4">
                        <label class="fw-bold text-dark small d-block mb-1">Catatan Tambahan / Keterangan Logistik:</label>
                        <div class="p-3 bg-light rounded-3 text-dark small" style="white-space: pre-line;">
                            <?= htmlspecialchars($pemeliharaan['keterangan']) ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Tiket Terhubung jika ada -->
                    <?php if (!empty($pemeliharaan['kode_laporan'])): ?>
                    <div class="alert alert-warning rounded-3 small p-3 mb-0 d-flex align-items-center justify-content-between">
                        <div>
                            <i class="bi bi-link-45deg fs-5 me-1"></i>
                            Tindakan perbaikan ini merespons <strong>Tiket Laporan Kerusakan <?= htmlspecialchars($pemeliharaan['kode_laporan']) ?></strong>.
                        </div>
                        <a href="<?= base_url('laporan/detail/' . $pemeliharaan['laporan_id']) ?>" class="btn btn-sm btn-warning rounded-pill px-3 fw-semibold">
                            Lihat Tiket &rarr;
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Info Objek Fasilitas Terkait -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 bg-white mb-4 overflow-hidden">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-box-seam text-primary me-2"></i>Fasilitas Objek</h6>
                    <a href="<?= base_url('fasilitas/detail/' . $pemeliharaan['fasilitas_id']) ?>" class="small text-decoration-none">
                        Lihat Profil &rarr;
                    </a>
                </div>

                <div class="card-body p-4 text-center">
                    <?php if (!empty($pemeliharaan['foto_fasilitas'])): ?>
                        <img src="<?= base_url('uploads/fasilitas/' . htmlspecialchars($pemeliharaan['foto_fasilitas'])) ?>" 
                             alt="<?= htmlspecialchars($pemeliharaan['nama_fasilitas']) ?>" 
                             class="img-fluid rounded-4 shadow-sm mb-3 w-100" 
                             style="max-height: 180px; object-fit: cover;">
                    <?php else: ?>
                        <div class="bg-light rounded-4 d-flex flex-column align-items-center justify-content-center p-4 text-muted mb-3 border border-dashed" style="height: 140px;">
                            <i class="bi bi-box-seam text-secondary fs-1 mb-1"></i>
                            <span class="small">Foto Inventaris</span>
                        </div>
                    <?php endif; ?>

                    <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($pemeliharaan['nama_fasilitas']) ?></h5>
                    <div class="font-monospace text-primary small fw-semibold mb-2"><?= htmlspecialchars($pemeliharaan['kode_inventaris']) ?></div>

                    <div class="text-start bg-light p-3 rounded-3 small">
                        <div class="mb-2">
                            <span class="text-muted d-block" style="font-size: 0.72rem;">Kategori Sarpras:</span>
                            <strong class="text-dark"><?= htmlspecialchars($pemeliharaan['nama_kategori']) ?></strong>
                        </div>
                        <div class="mb-2">
                            <span class="text-muted d-block" style="font-size: 0.72rem;">Penempatan Ruangan:</span>
                            <strong class="text-dark"><?= htmlspecialchars($pemeliharaan['nama_ruangan']) ?></strong>
                            <small class="text-muted d-block"><?= htmlspecialchars($pemeliharaan['nama_gedung']) ?> (Lantai <?= htmlspecialchars($pemeliharaan['lantai']) ?>)</small>
                        </div>
                        <div>
                            <span class="text-muted d-block" style="font-size: 0.72rem;">Kondisi Terkini di Sistem:</span>
                            <?php 
                            $badgeKondisiNow = ($pemeliharaan['kondisi_fasilitas_sekarang'] === 'baik') ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning-emphasis';
                            ?>
                            <span class="badge <?= $badgeKondisiNow ?> rounded-pill px-2 py-1 mt-1">
                                <?= ucfirst(str_replace('_', ' ', $pemeliharaan['kondisi_fasilitas_sekarang'])) ?>
                            </span>
                        </div>
                    </div>

                    <div class="mt-3 d-grid gap-2">
                        <a href="<?= base_url('pemeliharaan?fasilitas_id=' . $pemeliharaan['fasilitas_id']) ?>" 
                           class="btn btn-outline-primary rounded-pill btn-sm">
                            <i class="bi bi-clock-history me-1"></i> Lihat Seluruh Histori Fasilitas Ini
                        </a>
                        <a href="<?= base_url('fasilitas/detail/' . $pemeliharaan['fasilitas_id']) ?>" 
                           class="btn btn-light border rounded-pill btn-sm">
                            <i class="bi bi-arrow-up-right-square me-1"></i> Buka Halaman Fasilitas
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
