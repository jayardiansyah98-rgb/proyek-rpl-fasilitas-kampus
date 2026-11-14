<div class="container-fluid p-0">
    <!-- Welcome Banner Petugas -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-dark text-white overflow-hidden position-relative" 
         style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
        <div class="card-body p-4 p-lg-5 position-relative z-1">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <span class="badge bg-warning text-dark px-3 py-1 rounded-pill fw-semibold mb-2">
                        <i class="bi bi-tools me-1"></i> Dashboard Tim Teknis Sarpras
                    </span>
                    <h2 class="fw-bold mb-2">Halo, <?= htmlspecialchars($user['nama_lengkap']) ?>!</h2>
                    <p class="text-white-50 mb-0">
                        Pantau jadwal servis berkala, tindak lanjuti laporan kerusakan sarana dari dosen & mahasiswa, dan perbarui kondisi inventaris kampus secara berkala.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                    <a href="<?= base_url('pemeliharaan/tambah') ?>" class="btn btn-warning fw-semibold rounded-pill px-3 shadow-sm me-2">
                        <i class="bi bi-plus-lg me-1"></i> + Catat Pemeliharaan
                    </a>
                    <a href="<?= base_url('report') ?>" class="btn btn-outline-light rounded-pill px-3">
                        <i class="bi bi-file-earmark-bar-graph me-1"></i> Rekapitulasi
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- SEPULUH (10) KARTU METRIK UTAMA DASHBOARD SARPRAS                         -->
    <!-- ========================================================================= -->

    <!-- Grup A: 3 Kartu Master Aset -->
    <div class="row g-3 mb-3">
        <!-- 1. Total Gedung -->
        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-4 bg-primary-subtle text-primary p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="bi bi-building fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Total Gedung</div>
                        <div class="fs-4 fw-bold text-dark"><?= number_format($stats['total_gedung']) ?> <span class="fs-6 fw-normal text-muted">Gedung</span></div>
                        <small class="text-success"><i class="bi bi-check-circle me-1"></i>Seluruhnya Aktif</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Total Ruangan -->
        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-4 bg-info-subtle text-info p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="bi bi-door-open fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Total Ruangan</div>
                        <div class="fs-4 fw-bold text-dark"><?= number_format($stats['total_ruangan']) ?> <span class="fs-6 fw-normal text-muted">Ruangan</span></div>
                        <small class="text-muted">Kelas & Laboratorium</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Total Fasilitas -->
        <div class="col-sm-12 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-4 bg-indigo-subtle text-primary p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px; background-color: #ede9fe; color: #6d28d9;">
                        <i class="bi bi-box-seam fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Total Fasilitas</div>
                        <div class="fs-4 fw-bold text-dark">
                            <?= number_format($stats['total_fasilitas']) ?> <span class="fs-6 fw-normal text-muted">Item (<?= number_format($stats['total_unit']) ?> Unit)</span>
                        </div>
                        <small class="text-muted">Master Inventaris</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Grup B: 4 Kartu Kondisi Fisik Fasilitas -->
    <div class="row g-3 mb-3">
        <!-- 4. Fasilitas Baik -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-4 border-success">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">Fasilitas Baik</div>
                        <div class="fs-4 fw-bold text-success mb-0"><?= number_format($stats['fasilitas_baik']) ?></div>
                        <small class="text-muted">Kondisi Normal</small>
                    </div>
                    <div class="rounded-circle bg-success-subtle text-success p-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <i class="bi bi-check-circle-fill fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. Rusak Ringan -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-4 border-warning">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">Rusak Ringan</div>
                        <div class="fs-4 fw-bold text-warning-emphasis mb-0"><?= number_format($stats['rusak_ringan']) ?></div>
                        <small class="text-muted">Perlu Servis Ringan</small>
                    </div>
                    <div class="rounded-circle bg-warning-subtle text-warning-emphasis p-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <i class="bi bi-exclamation-circle-fill fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- 6. Rusak Berat -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-4 border-danger">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">Rusak Berat</div>
                        <div class="fs-4 fw-bold text-danger mb-0"><?= number_format($stats['rusak_berat']) ?></div>
                        <small class="text-muted">Butuh Penanganan Khusus</small>
                    </div>
                    <div class="rounded-circle bg-danger-subtle text-danger p-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <i class="bi bi-x-octagon-fill fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- 7. Tidak Layak -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-4 border-secondary">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">Tidak Layak (Afkir)</div>
                        <div class="fs-4 fw-bold text-dark mb-0"><?= number_format($stats['tidak_layak']) ?></div>
                        <small class="text-muted">Perlu Penggantian</small>
                    </div>
                    <div class="rounded-circle bg-secondary-subtle text-secondary p-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <i class="bi bi-slash-circle-fill fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Grup C: 3 Kartu Tiket Laporan Kerusakan -->
    <div class="row g-3 mb-4">
        <!-- 8. Laporan Menunggu -->
        <div class="col-sm-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-4 bg-danger-subtle text-danger p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-hourglass-split fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Laporan Menunggu</div>
                        <div class="fs-4 fw-bold text-danger"><?= number_format($stats['laporan_menunggu']) ?></div>
                        <small class="text-danger">Perlu Verifikasi Fisik</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- 9. Laporan Diproses -->
        <div class="col-sm-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-4 bg-warning-subtle text-warning-emphasis p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-wrench-adjustable fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Laporan Diproses</div>
                        <div class="fs-4 fw-bold text-warning-emphasis"><?= number_format($stats['laporan_diproses']) ?></div>
                        <small class="text-muted">Sedang Diperbaiki</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- 10. Laporan Selesai -->
        <div class="col-sm-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-4 bg-success-subtle text-success p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-check2-circle fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Laporan Selesai</div>
                        <div class="fs-4 fw-bold text-success"><?= number_format($stats['laporan_selesai']) ?></div>
                        <small class="text-success">Sudah Tuntas</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- VISUALISASI GRAFIK JAVASCRIPT (CHART.JS)                                  -->
    <!-- ========================================================================= -->
    <div class="row g-4 mb-4">
        <!-- 1. Distribusi Kondisi Fasilitas -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 bg-white h-100">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-pie-chart-fill text-primary me-2"></i>Distribusi Kondisi Fasilitas
                    </h6>
                    <span class="badge bg-light text-muted border small">Persentase Kelaikan</span>
                </div>
                <div class="card-body p-4 d-flex flex-column justify-content-center align-items-center">
                    <div style="position: relative; width: 100%; max-width: 270px; height: 250px;">
                        <canvas id="chartKondisi"></canvas>
                    </div>
                    <div class="row w-100 g-2 mt-3 text-center small">
                        <div class="col-4">
                            <div class="p-2 rounded-3 bg-success-subtle text-success">
                                <strong><?= $distribusi_kondisi['baik'] ?></strong><br>Baik
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 rounded-3 bg-warning-subtle text-warning-emphasis">
                                <strong><?= $distribusi_kondisi['rusak_ringan'] ?></strong><br>R. Ringan
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 rounded-3 bg-danger-subtle text-danger">
                                <strong><?= $distribusi_kondisi['rusak_berat'] + $distribusi_kondisi['tidak_layak'] ?></strong><br>Berat/Afkir
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Jumlah Fasilitas Per Gedung -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 bg-white h-100">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-bar-chart-fill text-primary me-2"></i>Jumlah Fasilitas per Gedung
                    </h6>
                    <a href="<?= base_url('report?type=gedung') ?>" class="small text-decoration-none">Rekap Gedung &rarr;</a>
                </div>
                <div class="card-body p-4">
                    <div style="position: relative; height: 290px; width: 100%;">
                        <canvas id="chartGedung"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TUGAS PEMELIHARAAN SAYA & LAPORAN TERBARU                                 -->
    <!-- ========================================================================= -->
    <div class="row g-4 mb-4">
        <!-- Tugas Terjadwal Saya -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 bg-white h-100">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-calendar-check-fill text-primary me-2"></i>Tugas Pemeliharaan Saya
                        </h6>
                        <small class="text-muted">Aktivitas servis yang ditugaskan ke akun Anda.</small>
                    </div>
                    <a href="<?= base_url('pemeliharaan') ?>" class="small text-decoration-none">Lihat Semua &rarr;</a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($assigned_tasks)): ?>
                        <div class="text-center py-4 text-muted small">
                            <i class="bi bi-check-circle fs-3 text-success d-block mb-1"></i>
                            Tidak ada jadwal pemeliharaan aktif yang tertunda untuk Anda saat ini.
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($assigned_tasks as $task): 
                                $tgl = !empty($task['tanggal']) ? $task['tanggal'] : $task['tanggal_mulai'];
                            ?>
                            <div class="list-group-item px-4 py-3 d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="badge bg-secondary-subtle text-secondary small font-monospace"><?= htmlspecialchars($task['kode_pemeliharaan']) ?></span>
                                    <h6 class="fw-bold text-dark mb-0 mt-1"><?= htmlspecialchars($task['nama_fasilitas']) ?></h6>
                                    <small class="text-muted"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($task['nama_ruangan']) ?> (<?= htmlspecialchars($task['nama_gedung']) ?>)</small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-warning-subtle text-warning-emphasis px-2 py-1 rounded-pill small">
                                        <?= ucfirst(str_replace('_', ' ', $task['status'])) ?>
                                    </span>
                                    <small class="text-muted d-block mt-1"><?= date('d M Y', strtotime($tgl)) ?></small>
                                    <a href="<?= base_url('pemeliharaan/detail/' . $task['id']) ?>" class="btn btn-sm btn-link text-decoration-none p-0">Buka &rarr;</a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- 3. Laporan Kerusakan Terbaru -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 bg-white h-100">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-ticket-detailed-fill text-danger me-2"></i>Laporan Kerusakan Masuk
                        </h6>
                        <small class="text-muted">Aduan dari civitas yang perlu ditindaklanjuti.</small>
                    </div>
                    <a href="<?= base_url('laporan') ?>" class="small text-decoration-none">Kelola Tiket &rarr;</a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($laporan_terbaru)): ?>
                        <div class="text-center py-4 text-muted small">
                            <i class="bi bi-check-circle fs-3 text-success d-block mb-1"></i>
                            Tidak ada tiket laporan baru.
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($laporan_terbaru as $lap): ?>
                            <div class="list-group-item px-4 py-3 d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="badge bg-danger-subtle text-danger small font-monospace"><?= htmlspecialchars($lap['kode_laporan']) ?></span>
                                    <div class="fw-bold text-dark mt-1"><?= htmlspecialchars($lap['nama_fasilitas']) ?></div>
                                    <small class="text-muted"><i class="bi bi-person me-1"></i><?= htmlspecialchars($lap['pelapor']) ?> &bull; <?= htmlspecialchars($lap['nama_ruangan']) ?></small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-secondary px-2 py-1 rounded-pill small"><?= ucfirst($lap['status']) ?></span>
                                    <div class="mt-1">
                                        <a href="<?= base_url('laporan/detail/' . $lap['id']) ?>" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-0">Periksa</a>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- 4. Fasilitas yang Sering Rusak -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 bg-white h-100">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-fire text-danger me-2"></i>Fasilitas yang Sering Rusak
                        </h6>
                        <small class="text-muted">Top inventaris dengan riwayat kerusakan terbanyak.</small>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($fasilitas_sering_rusak)): ?>
                        <div class="text-center py-4 text-muted small">Tidak ada data.</div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($fasilitas_sering_rusak as $idx => $fs): 
                                $totalKasus = $fs['total_laporan'] + $fs['total_perbaikan'];
                            ?>
                            <div class="list-group-item px-4 py-3 d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle bg-danger-subtle text-danger fw-bold d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                        #<?= $idx + 1 ?>
                                    </div>
                                    <div>
                                        <a href="<?= base_url('fasilitas/detail/' . $fs['id']) ?>" class="fw-bold text-dark text-decoration-none">
                                            <?= htmlspecialchars($fs['nama_fasilitas']) ?>
                                        </a>
                                        <small class="text-muted d-block"><?= htmlspecialchars($fs['nama_ruangan']) ?> (<?= htmlspecialchars($fs['nama_gedung']) ?>)</small>
                                    </div>
                                </div>
                                <span class="badge bg-danger rounded-pill px-3 py-1"><?= $totalKasus ?> Kasus</span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- 5. Fasilitas yang Perlu Perhatian -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 bg-white h-100">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>Fasilitas Perlu Perhatian
                        </h6>
                        <small class="text-muted">Aset berkondisi rusak atau tiket aduan aktif.</small>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($fasilitas_perlu_perhatian)): ?>
                        <div class="text-center py-4 text-muted small">Seluruh fasilitas dalam kondisi baik.</div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($fasilitas_perlu_perhatian as $fp): 
                                $badgeKond = ($fp['kondisi'] === 'rusak_berat') ? 'bg-danger text-white' : (($fp['kondisi'] === 'tidak_layak') ? 'bg-dark text-white' : 'bg-warning text-dark');
                            ?>
                            <div class="list-group-item px-4 py-3 d-flex align-items-center justify-content-between">
                                <div>
                                    <a href="<?= base_url('fasilitas/detail/' . $fp['id']) ?>" class="fw-bold text-dark text-decoration-none">
                                        <?= htmlspecialchars($fp['nama_fasilitas']) ?>
                                    </a>
                                    <small class="text-muted d-block"><?= htmlspecialchars($fp['nama_ruangan']) ?> &bull; <?= $fp['jumlah'] ?> Unit</small>
                                </div>
                                <span class="badge <?= $badgeKond ?> rounded-pill px-3 py-1"><?= ucfirst(str_replace('_', ' ', $fp['kondisi'])) ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctxKondisi = document.getElementById('chartKondisi');
    if (ctxKondisi) {
        new Chart(ctxKondisi, {
            type: 'doughnut',
            data: {
                labels: ['Baik', 'Rusak Ringan', 'Rusak Berat', 'Tidak Layak', 'Hilang'],
                datasets: [{
                    data: [
                        <?= $distribusi_kondisi['baik'] ?>,
                        <?= $distribusi_kondisi['rusak_ringan'] ?>,
                        <?= $distribusi_kondisi['rusak_berat'] ?>,
                        <?= $distribusi_kondisi['tidak_layak'] ?>,
                        <?= $distribusi_kondisi['hilang'] ?>
                    ],
                    backgroundColor: ['#10b981', '#f59e0b', '#ef4444', '#475569', '#8b5cf6'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, padding: 12, font: { family: "'Plus Jakarta Sans', sans-serif", size: 11 } }
                    }
                },
                cutout: '70%'
            }
        });
    }

    const ctxGedung = document.getElementById('chartGedung');
    if (ctxGedung) {
        <?php
        $gLabels = [];
        $gFasilitas = [];
        $gUnits = [];
        foreach ($fasilitas_per_gedung as $fg) {
            $gLabels[] = $fg['nama_gedung'];
            $gFasilitas[] = (int)$fg['total_fasilitas'];
            $gUnits[] = (int)$fg['total_unit'];
        }
        ?>
        new Chart(ctxGedung, {
            type: 'bar',
            data: {
                labels: <?= json_encode($gLabels) ?>,
                datasets: [
                    { label: 'Jenis Sarpras', data: <?= json_encode($gFasilitas) ?>, backgroundColor: '#3b82f6', borderRadius: 6 },
                    { label: 'Total Unit', data: <?= json_encode($gUnits) ?>, backgroundColor: '#93c5fd', borderRadius: 6 }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', labels: { boxWidth: 14, font: { family: "'Plus Jakarta Sans', sans-serif", size: 12 } } }
                },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 5 } } }
            }
        });
    }
});
</script>
