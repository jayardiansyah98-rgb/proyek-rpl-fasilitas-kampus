<div class="container-fluid p-0">
    <!-- Welcome Banner Civitas -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-primary text-white overflow-hidden position-relative" 
         style="background: linear-gradient(135deg, #0d47a1 0%, #1976d2 100%);">
        <div class="card-body p-4 p-lg-5 position-relative z-1">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <span class="badge bg-white text-primary px-3 py-1 rounded-pill fw-semibold mb-2">
                        <i class="bi bi-mortarboard-fill me-1"></i> Civitas STMIK IKMI Cirebon (<?= ucfirst($user['jenis_pengguna']) ?>)
                    </span>
                    <h2 class="fw-bold mb-2">Selamat Datang, <?= htmlspecialchars($user['nama_lengkap']) ?>!</h2>
                    <p class="text-white-50 mb-0">
                        Akses informasi ketersediaan ruangan dan inventaris kampus. Temukan fasilitas yang rusak atau tidak berfungsi? Laporkan secara langsung ke tim Sarpras untuk penanganan cepat.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                    <a href="<?= base_url('laporan/tambah') ?>" class="btn btn-warning fw-semibold rounded-pill px-4 shadow-sm me-2">
                        <i class="bi bi-plus-circle me-1"></i> Buat Laporan Kerusakan
                    </a>
                    <a href="<?= base_url('fasilitas') ?>" class="btn btn-outline-light rounded-pill px-3">
                        <i class="bi bi-box-seam me-1"></i> Katalog Sarpras
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 5 KARTU METRIK UTAMA DASHBOARD CIVITAS                                   -->
    <!-- ========================================================================= -->
    <div class="row g-3 mb-4">
        <!-- 1. Jumlah Ruangan -->
        <div class="col-sm-6 col-xl">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-4 bg-primary-subtle text-primary p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="bi bi-door-open fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Jumlah Ruangan</div>
                        <div class="fs-4 fw-bold text-dark"><?= $stats['jumlah_ruangan'] ?> <span class="fs-6 fw-normal text-muted">Ruang</span></div>
                        <small class="text-success"><i class="bi bi-check-circle me-1"></i>Siap Digunakan</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Jumlah Fasilitas yang Dapat Dilihat -->
        <div class="col-sm-6 col-xl">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-4 bg-info-subtle text-info p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="bi bi-box-seam fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Fasilitas Kampus</div>
                        <div class="fs-4 fw-bold text-dark"><?= $stats['jumlah_fasilitas'] ?> <span class="fs-6 fw-normal text-muted">Item</span></div>
                        <small class="text-muted"><?= $stats['fasilitas_layak'] ?> kondisi prima</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Laporan Saya -->
        <div class="col-sm-6 col-xl">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-4 bg-secondary-subtle text-dark p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="bi bi-journal-text fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Laporan Saya</div>
                        <div class="fs-4 fw-bold text-dark"><?= $stats['laporan_saya'] ?> <span class="fs-6 fw-normal text-muted">Tiket</span></div>
                        <small class="text-muted">Total aduan diajukan</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Laporan yang Sedang Diproses -->
        <div class="col-sm-6 col-xl">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-4 bg-warning-subtle text-warning-emphasis p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="bi bi-hourglass-split fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Sedang Diproses</div>
                        <div class="fs-4 fw-bold text-warning-emphasis"><?= $stats['laporan_diproses'] ?> <span class="fs-6 fw-normal text-muted">Tiket</span></div>
                        <small class="text-muted">Dalam penanganan Sarpras</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. Laporan Selesai -->
        <div class="col-sm-12 col-xl">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-4 bg-success-subtle text-success p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="bi bi-check2-circle fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Laporan Selesai</div>
                        <div class="fs-4 fw-bold text-success"><?= $stats['laporan_selesai'] ?> <span class="fs-6 fw-normal text-muted">Tiket</span></div>
                        <small class="text-success">Perbaikan tuntas</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Konten Informasi Pengguna: Riwayat Laporan Saya & Ruangan Kampus -->
    <div class="row g-4 mb-4">
        <!-- Riwayat Laporan Terakhir Pengguna -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 bg-white h-100">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-journal-check text-primary me-2"></i>Status Laporan Kerusakan Saya
                        </h6>
                        <small class="text-muted">Perkembangan tindak lanjut laporan yang Anda sampaikan.</small>
                    </div>
                    <a href="<?= base_url('laporan') ?>" class="small text-decoration-none">Lihat Semua &rarr;</a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($my_reports)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                            <h6 class="fw-semibold text-dark">Anda Belum Pernah Mengirim Laporan</h6>
                            <p class="small text-muted mb-3">Jika menemukan sarpras rusak di kelas atau lab, laporkan segera ke tim Sarpras.</p>
                            <a href="<?= base_url('laporan/tambah') ?>" class="btn btn-warning rounded-pill px-4 btn-sm fw-semibold">
                                <i class="bi bi-plus-circle me-1"></i> Buat Laporan Pertama
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 small">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Kode Tiket</th>
                                        <th>Fasilitas / Ruangan</th>
                                        <th>Prioritas</th>
                                        <th>Status Penanganan</th>
                                        <th class="pe-4 text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($my_reports as $lap): 
                                        $badgePrio = ($lap['prioritas'] === 'darurat') ? 'bg-danger text-white' : (($lap['prioritas'] === 'tinggi') ? 'bg-warning text-dark' : 'bg-secondary text-white');
                                        $badgeStat = ($lap['status'] === 'selesai') ? 'bg-success-subtle text-success border border-success-subtle' : (($lap['status'] === 'diproses') ? 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' : 'bg-secondary-subtle text-secondary');
                                    ?>
                                    <tr>
                                        <td class="ps-4 fw-bold font-monospace text-primary">
                                            <a href="<?= base_url('laporan/detail/' . $lap['id']) ?>" class="text-decoration-none">
                                                <?= htmlspecialchars($lap['kode_laporan']) ?>
                                            </a>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark"><?= htmlspecialchars($lap['nama_fasilitas']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($lap['nama_ruangan']) ?> (<?= htmlspecialchars($lap['nama_gedung']) ?>)</small>
                                        </td>
                                        <td><span class="badge <?= $badgePrio ?> px-2 py-1 rounded-pill"><?= ucfirst($lap['prioritas']) ?></span></td>
                                        <td><span class="badge <?= $badgeStat ?> px-2 py-1 rounded-pill"><?= ucfirst($lap['status']) ?></span></td>
                                        <td class="pe-4 text-end">
                                            <a href="<?= base_url('laporan/detail/' . $lap['id']) ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                                Detail
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Ruangan Kampus Tersedia & Info Servis -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-door-open-fill text-primary me-2"></i>Ruangan Kampus</h6>
                        <small class="text-muted">Ruang teori, laboratorium & seminar.</small>
                    </div>
                    <a href="<?= base_url('ruangan') ?>" class="small text-decoration-none">Semua Ruangan &rarr;</a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush small">
                        <?php foreach ($available_rooms as $rm): ?>
                        <div class="list-group-item px-4 py-3 d-flex align-items-center justify-content-between">
                            <div>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($rm['nama_ruangan']) ?></div>
                                <small class="text-muted font-monospace"><?= htmlspecialchars($rm['kode_ruangan']) ?> &bull; <?= htmlspecialchars($rm['nama_gedung']) ?> (Lt. <?= $rm['lantai'] ?>)</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-light text-primary border rounded-pill px-2 py-1">
                                    <?= $rm['total_fasilitas'] ?> Sarpras
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Pemeliharaan Publik Terkini -->
            <div class="card border-0 shadow-sm rounded-4 bg-white">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-tools text-success me-2"></i>Servis & Pemeliharaan Terkini</h6>
                    <a href="<?= base_url('pemeliharaan') ?>" class="small text-decoration-none">Lihat &rarr;</a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($public_maintenance)): ?>
                        <div class="text-center py-4 text-muted small">Belum ada pengumuman servis fasilitas.</div>
                    <?php else: ?>
                        <div class="list-group list-group-flush small">
                            <?php foreach ($public_maintenance as $pm): 
                                $tgl = !empty($pm['tanggal']) ? $pm['tanggal'] : $pm['tanggal_mulai'];
                            ?>
                            <div class="list-group-item px-4 py-2 d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($pm['nama_fasilitas']) ?></div>
                                    <small class="text-muted"><i class="bi bi-check-circle-fill text-success me-1"></i><?= htmlspecialchars($pm['jenis_tindakan']) ?> &bull; <?= date('d M Y', strtotime($tgl)) ?></small>
                                </div>
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill">Selesai</span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
