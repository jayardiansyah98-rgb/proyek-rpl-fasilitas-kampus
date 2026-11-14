<div class="container-fluid p-0">
    <!-- Welcome Banner Admin -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-primary text-white overflow-hidden position-relative" 
         style="background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);">
        <div class="card-body p-4 p-lg-5 position-relative z-1">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <span class="badge bg-warning text-dark px-3 py-1 rounded-pill fw-semibold mb-2">
                        <i class="bi bi-shield-lock-fill me-1"></i> Dashboard Eksekutif Administrator
                    </span>
                    <h2 class="fw-bold mb-2">Selamat Datang, <?= htmlspecialchars($user['nama_lengkap']) ?>!</h2>
                    <p class="text-white-50 mb-0">
                        Pusat kendali sarana dan prasarana kampus STMIK IKMI Cirebon. Pantau kelaikan inventaris, sebaran aset gedung, penanganan tiket kerusakan, dan rekapitulasi operasional.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                    <a href="<?= base_url('report') ?>" class="btn btn-warning fw-semibold rounded-pill px-3 shadow-sm me-2">
                        <i class="bi bi-file-earmark-bar-graph me-1"></i> Pusat Laporan
                    </a>
                    <a href="<?= base_url('fasilitas') ?>" class="btn btn-light fw-semibold rounded-pill px-3 shadow-sm">
                        <i class="bi bi-box-seam me-1"></i> Master Sarpras
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- BAGIAN 1: SEPULUH (10) KARTU METRIK UTAMA DASHBOARD SARPRAS             -->
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
                        <small class="text-muted">Kelas, Laboratorium & Kantor</small>
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
                        <small class="text-muted">Inventaris Terdata</small>
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
                        <small class="text-muted">Siap & Layak Pakai</small>
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
                        <small class="text-muted">Perlu Servis / Kalibrasi</small>
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
                        <small class="text-muted">Tidak Beroperasi</small>
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
                        <small class="text-muted">Perlu Penghapusan / Ganti</small>
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
                        <small class="text-danger">Belum Diverifikasi</small>
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
                        <small class="text-muted">Dalam Tindakan Servis</small>
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
                        <small class="text-success">Tuntas Ditangani</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- BAGIAN 2: VISUALISASI GRAFIK JAVASCRIPT (CHART.JS)                         -->
    <!-- ========================================================================= -->
    <div class="row g-4 mb-4">
        <!-- 1. Distribusi Kondisi Fasilitas (Doughnut Chart) -->
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

        <!-- 2. Jumlah Fasilitas Per Gedung (Bar Chart) -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 bg-white h-100">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-bar-chart-fill text-primary me-2"></i>Jumlah Fasilitas per Gedung Kampus
                    </h6>
                    <a href="<?= base_url('report?type=gedung') ?>" class="small text-decoration-none">Detail Gedung &rarr;</a>
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
    <!-- BAGIAN 3: INFORMASI DASHBOARD                                             -->
    <!-- 3. Laporan Kerusakan Terbaru                                              -->
    <!-- 4. Fasilitas yang Sering Rusak                                            -->
    <!-- 5. Fasilitas yang Perlu Perhatian                                         -->
    <!-- ========================================================================= -->
    <div class="row g-4 mb-4">
        <!-- 3. Laporan Kerusakan Terbaru (Tabel) -->
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-ticket-detailed-fill text-danger me-2"></i>Laporan Kerusakan Terbaru
                        </h6>
                        <small class="text-muted">Tiket keluhan dan aduan sarana prasarana yang masuk dari civitas akademika.</small>
                    </div>
                    <a href="<?= base_url('laporan') ?>" class="btn btn-outline-primary rounded-pill btn-sm px-3">
                        Lihat Semua Tiket &rarr;
                    </a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($laporan_terbaru)): ?>
                        <div class="text-center py-4 text-muted small">
                            <i class="bi bi-check-circle fs-3 text-success d-block mb-1"></i>
                            Tidak ada tiket laporan kerusakan yang belum ditangani.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 small">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Kode Tiket</th>
                                        <th>Pelapor</th>
                                        <th>Fasilitas / Lokasi</th>
                                        <th>Deskripsi Kerusakan</th>
                                        <th>Prioritas</th>
                                        <th>Status</th>
                                        <th>Waktu Lapor</th>
                                        <th class="pe-4 text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($laporan_terbaru as $lap): 
                                        $badgePrio = ($lap['prioritas'] === 'darurat') ? 'bg-danger text-white' : (($lap['prioritas'] === 'tinggi') ? 'bg-warning text-dark' : 'bg-secondary text-white');
                                        $badgeStatus = ($lap['status'] === 'selesai') ? 'bg-success-subtle text-success border border-success-subtle' : (($lap['status'] === 'diproses') ? 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle');
                                    ?>
                                    <tr>
                                        <td class="ps-4 fw-bold font-monospace text-primary">
                                            <a href="<?= base_url('laporan/detail/' . $lap['id']) ?>" class="text-decoration-none">
                                                <?= htmlspecialchars($lap['kode_laporan']) ?>
                                            </a>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark"><?= htmlspecialchars($lap['pelapor']) ?></div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark"><?= htmlspecialchars($lap['nama_fasilitas']) ?></div>
                                            <small class="text-muted"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($lap['nama_ruangan']) ?> (<?= htmlspecialchars($lap['nama_gedung']) ?>)</small>
                                        </td>
                                        <td>
                                            <div class="text-dark text-truncate" style="max-width: 250px;" title="<?= htmlspecialchars($lap['deskripsi']) ?>">
                                                <?= htmlspecialchars($lap['deskripsi']) ?>
                                            </div>
                                        </td>
                                        <td><span class="badge <?= $badgePrio ?> px-2 py-1 rounded-pill"><?= ucfirst($lap['prioritas']) ?></span></td>
                                        <td><span class="badge <?= $badgeStatus ?> px-2 py-1 rounded-pill"><?= ucfirst($lap['status']) ?></span></td>
                                        <td class="text-muted"><?= date('d M Y H:i', strtotime($lap['created_at'])) ?></td>
                                        <td class="pe-4 text-end">
                                            <a href="<?= base_url('laporan/detail/' . $lap['id']) ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                                Verifikasi
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

        <!-- 4. Fasilitas yang Sering Rusak -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 bg-white h-100">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-fire text-danger me-2"></i>Fasilitas yang Sering Rusak
                        </h6>
                        <small class="text-muted">Top 5 sarpras dengan frekuensi kerusakan & servis terbanyak.</small>
                    </div>
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 small">Top Kasus</span>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($fasilitas_sering_rusak)): ?>
                        <div class="text-center py-4 text-muted small">
                            <i class="bi bi-shield-check fs-3 text-success d-block mb-1"></i>
                            Tidak ada fasilitas dengan catatan kerusakan berulang.
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($fasilitas_sering_rusak as $idx => $fs): 
                                $totalKasus = $fs['total_laporan'] + $fs['total_perbaikan'];
                            ?>
                            <div class="list-group-item px-4 py-3 d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle bg-danger-subtle text-danger fw-bold d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; font-size: 0.85rem;">
                                        #<?= $idx + 1 ?>
                                    </div>
                                    <div>
                                        <a href="<?= base_url('fasilitas/detail/' . $fs['id']) ?>" class="fw-bold text-dark text-decoration-none">
                                            <?= htmlspecialchars($fs['nama_fasilitas']) ?>
                                        </a>
                                        <div class="small text-muted font-monospace"><?= htmlspecialchars($fs['kode_inventaris']) ?></div>
                                        <small class="text-muted"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($fs['nama_ruangan']) ?> (<?= htmlspecialchars($fs['nama_gedung']) ?>)</small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-danger px-3 py-1 rounded-pill fw-bold">
                                        <?= $totalKasus ?> Insiden
                                    </span>
                                    <small class="text-muted d-block mt-1">
                                        <?= $fs['total_laporan'] ?> Laporan &bull; <?= $fs['total_perbaikan'] ?> Servis
                                    </small>
                                </div>
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
                        <small class="text-muted">Aset berkondisi rusak atau memiliki tiket pengaduan aktif.</small>
                    </div>
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 small">Prioritas</span>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($fasilitas_perlu_perhatian)): ?>
                        <div class="text-center py-4 text-muted small">
                            <i class="bi bi-check-all fs-3 text-success d-block mb-1"></i>
                            Seluruh fasilitas dalam kondisi baik & prima.
                        </div>
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
                                    <div class="small text-muted font-monospace"><?= htmlspecialchars($fp['kode_inventaris']) ?> &bull; <?= $fp['jumlah'] ?> Unit</div>
                                    <small class="text-muted"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($fp['nama_ruangan']) ?> (<?= htmlspecialchars($fp['nama_gedung']) ?>)</small>
                                </div>
                                <div class="text-end">
                                    <span class="badge <?= $badgeKond ?> px-3 py-1 rounded-pill small">
                                        <?= ucfirst(str_replace('_', ' ', $fp['kondisi'])) ?>
                                    </span>
                                    <?php if ($fp['active_reports'] > 0): ?>
                                        <div class="mt-1">
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle small">
                                                <?= $fp['active_reports'] ?> Tiket Aktif
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Library JavaScript Ringan: Chart.js untuk visualisasi grafik -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Inisialisasi Chart Kondisi Fasilitas (Doughnut Chart)
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
                    backgroundColor: [
                        '#10b981', // Emerald green
                        '#f59e0b', // Amber yellow
                        '#ef4444', // Red
                        '#475569', // Slate dark
                        '#8b5cf6'  // Purple
                    ],
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
                        labels: {
                            boxWidth: 12,
                            padding: 12,
                            font: { family: "'Plus Jakarta Sans', sans-serif", size: 11 }
                        }
                    }
                },
                cutout: '70%'
            }
        });
    }

    // 2. Inisialisasi Chart Fasilitas Per Gedung (Bar Chart)
    const ctxGedung = document.getElementById('chartGedung');
    if (ctxGedung) {
        <?php
        $gedungLabels = [];
        $gedungFasilitas = [];
        $gedungUnits = [];
        foreach ($fasilitas_per_gedung as $fg) {
            $gedungLabels[] = $fg['nama_gedung'] . ' (' . $fg['kode_gedung'] . ')';
            $gedungFasilitas[] = (int)$fg['total_fasilitas'];
            $gedungUnits[] = (int)$fg['total_unit'];
        }
        ?>
        new Chart(ctxGedung, {
            type: 'bar',
            data: {
                labels: <?= json_encode($gedungLabels) ?>,
                datasets: [
                    {
                        label: 'Total Jenis Sarpras',
                        data: <?= json_encode($gedungFasilitas) ?>,
                        backgroundColor: '#3b82f6',
                        borderRadius: 6
                    },
                    {
                        label: 'Total Unit Fisik',
                        data: <?= json_encode($gedungUnits) ?>,
                        backgroundColor: '#93c5fd',
                        borderRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            boxWidth: 14,
                            font: { family: "'Plus Jakarta Sans', sans-serif", size: 12 }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 5 }
                    }
                }
            }
        });
    }
});
</script>
