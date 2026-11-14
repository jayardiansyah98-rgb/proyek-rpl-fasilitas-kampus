<div class="container-fluid p-0">
    <!-- Header Modul AI Maintenance Recommendation -->
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-4 bg-warning text-dark p-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 54px; height: 54px;">
                        <i class="bi bi-stars fs-3"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h4 class="fw-bold mb-0 text-dark">AI Maintenance Recommendation</h4>
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-1 rounded-pill small">
                                <i class="bi bi-shield-check me-1"></i> Asisten Rekomendasi Pemeliharaan
                            </span>
                        </div>
                        <p class="text-muted small mb-0 mt-1">
                            Sistem pendukung keputusan prioritas servis sarana prasarana kampus STMIK IKMI Cirebon berbasis tingkat keparahan, riwayat aduan, dan umur aset.
                        </p>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <a href="<?= base_url('ai/analysis') ?>" class="btn btn-outline-primary rounded-pill btn-sm px-3">
                        <i class="bi bi-graph-up-arrow me-1"></i> AI Facility Analysis
                    </a>
                    <a href="<?= base_url('pemeliharaan/tambah') ?>" class="btn btn-primary rounded-pill btn-sm px-3 shadow-sm">
                        <i class="bi bi-plus-lg me-1"></i> + Buat Jadwal Servis
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Peringatan Aturan Kebijakan AI (Kepatuhan Prinsip Non-Otomatisasi Status) -->
    <div class="alert alert-info border-0 rounded-4 shadow-sm p-3 mb-4 d-flex align-items-center gap-3">
        <i class="bi bi-info-circle-fill fs-3 text-primary flex-shrink-0"></i>
        <div class="small">
            <strong>Prinsip Verifikasi Petugas:</strong> Rekomendasi AI di bawah ini bersifat <em>decision support</em> (usulan pertimbangan) untuk mempermudah prioritas kerja tim Sarpras. 
            <strong>Sistem tidak akan mengubah status fasilitas secara otomatis.</strong> Petugas Sarpras berwenang memverifikasi kondisi lapangan sebelum menerbitkan Surat Perintah Kerja (SPK).
        </div>
    </div>

    <?php if (empty($recommendations['cukup_data'])): ?>
        <!-- Peringatan Jika Data Tidak Cukup -->
        <div class="alert alert-warning border-0 rounded-4 shadow-sm p-4 d-flex align-items-center gap-3 mb-4">
            <i class="bi bi-exclamation-triangle-fill fs-1 text-warning"></i>
            <div>
                <h5 class="fw-bold mb-1">Data Belum Mencukupi</h5>
                <p class="mb-0 small text-dark">
                    <?= htmlspecialchars($recommendations['pesan_data_kurang'] ?? 'Data fasilitas belum mencukupi untuk menyusun rekomendasi prioritas pemeliharaan.') ?>
                </p>
            </div>
        </div>
    <?php else: 
        $tinggi = $recommendations['prioritas_tinggi'];
        $sedang = $recommendations['prioritas_sedang'];
        $rendah = $recommendations['prioritas_rendah'];
    ?>

        <!-- Counter Kartu Ringkasan Prioritas -->
        <div class="row g-3 mb-4">
            <div class="col-sm-4">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-4 border-danger">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-danger small fw-bold text-uppercase">Prioritas Tinggi</div>
                            <div class="fs-4 fw-bold text-danger mb-0"><?= count($tinggi) ?> <span class="fs-6 fw-normal text-muted">Fasilitas</span></div>
                            <small class="text-muted">Mendesak diperbaiki</small>
                        </div>
                        <div class="rounded-circle bg-danger-subtle text-danger p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="bi bi-exclamation-octagon-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-4">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-4 border-warning">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-warning-emphasis small fw-bold text-uppercase">Prioritas Sedang</div>
                            <div class="fs-4 fw-bold text-warning-emphasis mb-0"><?= count($sedang) ?> <span class="fs-6 fw-normal text-muted">Fasilitas</span></div>
                            <small class="text-muted">Servis & kalibrasi berkala</small>
                        </div>
                        <div class="rounded-circle bg-warning-subtle text-warning-emphasis p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="bi bi-wrench-adjustable fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-4">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-4 border-success">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-success small fw-bold text-uppercase">Prioritas Rendah / Preventif</div>
                            <div class="fs-4 fw-bold text-success mb-0"><?= count($rendah) ?> <span class="fs-6 fw-normal text-muted">Fasilitas</span></div>
                            <small class="text-muted">Pencegahan rutin</small>
                        </div>
                        <div class="rounded-circle bg-success-subtle text-success p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="bi bi-shield-check fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 1. GRUP: PRIORITAS TINGGI -->
        <div class="card border-0 shadow-sm rounded-4 bg-white mb-4 overflow-hidden border-top border-4 border-danger">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-danger rounded-pill px-3 py-1 fw-bold text-uppercase">
                        <i class="bi bi-fire me-1"></i> Prioritas Tinggi
                    </span>
                    <span class="text-muted small">Tindakan Cepat / Urgent</span>
                </div>
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle small"><?= count($tinggi) ?> Aset Terjadwal</span>
            </div>
            <div class="card-body p-4">
                <?php if (empty($tinggi)): ?>
                    <div class="text-center py-4 text-muted small">
                        <i class="bi bi-check-circle fs-3 text-success d-block mb-1"></i>
                        Tidak ada fasilitas berkategori Prioritas Tinggi saat ini. Seluruh sarana utama operasional aman.
                    </div>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($tinggi as $item): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="p-3 rounded-4 bg-danger-subtle bg-opacity-10 border border-danger-subtle h-100 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="fw-bold text-dark mb-0"><?= htmlspecialchars($item['nama']) ?></h6>
                                        <span class="badge bg-danger rounded-pill small"><?= $item['label_kondisi'] ?></span>
                                    </div>
                                    <div class="font-monospace text-muted small mb-2"><?= htmlspecialchars($item['kode_inventaris']) ?></div>

                                    <!-- Poin-poin Kriteria Ringkas seperti Contoh Spesifikasi -->
                                    <div class="p-2 bg-white rounded-3 border mb-3 small">
                                        <ul class="mb-0 ps-3 text-dark">
                                            <li><strong class="text-danger"><?= $item['label_kondisi'] ?></strong></li>
                                            <li><?= ($item['total_laporan'] > 0) ? "Sudah dilaporkan {$item['total_laporan']} kali" : "Belum ada tiket laporan masuk" ?></li>
                                            <li>Usia <?= $item['umur_tahun'] ?> tahun (Pengadaan <?= $item['tahun_pengadaan'] ?>)</li>
                                            <li>Lokasi: <?= htmlspecialchars($item['lokasi']) ?></li>
                                        </ul>
                                    </div>

                                    <!-- Alasan Rekomendasi AI -->
                                    <div class="small mb-2">
                                        <strong class="text-danger small"><i class="bi bi-shield-exclamation me-1"></i>Alasan Verifikasi:</strong>
                                        <p class="text-muted small mb-1"><?= htmlspecialchars($item['alasan_ringkas']) ?></p>
                                    </div>

                                    <div class="small mb-3">
                                        <strong class="text-dark small"><i class="bi bi-gear-fill me-1 text-primary"></i>Saran AI:</strong>
                                        <p class="text-muted small mb-0"><?= htmlspecialchars($item['tindakan_disarankan']) ?></p>
                                    </div>
                                </div>

                                <div class="pt-2 border-top d-flex justify-content-between align-items-center">
                                    <small class="text-muted">Servis: <?= $item['terakhir_servis'] ?></small>
                                    <a href="<?= base_url('pemeliharaan/tambah?fasilitas_id=' . $item['id']) ?>" class="btn btn-sm btn-danger rounded-pill px-3 shadow-sm">
                                        <i class="bi bi-wrench me-1"></i> Tindak Lanjuti
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- 2. GRUP: PRIORITAS SEDANG -->
        <div class="card border-0 shadow-sm rounded-4 bg-white mb-4 overflow-hidden border-top border-4 border-warning">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-warning text-dark rounded-pill px-3 py-1 fw-bold text-uppercase">
                        <i class="bi bi-tools me-1"></i> Prioritas Sedang
                    </span>
                    <span class="text-muted small">Servis Ringan & Kalibrasi Berkala</span>
                </div>
                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle small"><?= count($sedang) ?> Aset Terjadwal</span>
            </div>
            <div class="card-body p-4">
                <?php if (empty($sedang)): ?>
                    <div class="text-center py-4 text-muted small">
                        <i class="bi bi-check2-circle fs-3 text-success d-block mb-1"></i>
                        Tidak ada fasilitas berkategori Prioritas Sedang.
                    </div>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($sedang as $item): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="p-3 rounded-4 bg-warning-subtle bg-opacity-10 border border-warning-subtle h-100 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="fw-bold text-dark mb-0"><?= htmlspecialchars($item['nama']) ?></h6>
                                        <span class="badge bg-warning text-dark rounded-pill small"><?= $item['label_kondisi'] ?></span>
                                    </div>
                                    <div class="font-monospace text-muted small mb-2"><?= htmlspecialchars($item['kode_inventaris']) ?></div>

                                    <!-- Poin-poin Kriteria Ringkas seperti Contoh Spesifikasi -->
                                    <div class="p-2 bg-white rounded-3 border mb-3 small">
                                        <ul class="mb-0 ps-3 text-dark">
                                            <li><strong class="text-warning-emphasis"><?= $item['label_kondisi'] ?></strong></li>
                                            <li><?= ($item['total_laporan'] > 0) ? "Dilaporkan {$item['total_laporan']} kali" : "Pencegahan dini" ?></li>
                                            <li>Usia <?= $item['umur_tahun'] ?> tahun (Pengadaan <?= $item['tahun_pengadaan'] ?>)</li>
                                            <li>Lokasi: <?= htmlspecialchars($item['lokasi']) ?></li>
                                        </ul>
                                    </div>

                                    <div class="small mb-2">
                                        <strong class="text-warning-emphasis small"><i class="bi bi-info-circle me-1"></i>Alasan Rekomendasi:</strong>
                                        <p class="text-muted small mb-1"><?= htmlspecialchars($item['alasan_ringkas']) ?></p>
                                    </div>

                                    <div class="small mb-3">
                                        <strong class="text-dark small"><i class="bi bi-gear me-1 text-primary"></i>Saran AI:</strong>
                                        <p class="text-muted small mb-0"><?= htmlspecialchars($item['tindakan_disarankan']) ?></p>
                                    </div>
                                </div>

                                <div class="pt-2 border-top d-flex justify-content-between align-items-center">
                                    <small class="text-muted">Servis: <?= $item['terakhir_servis'] ?></small>
                                    <a href="<?= base_url('pemeliharaan/tambah?fasilitas_id=' . $item['id']) ?>" class="btn btn-sm btn-outline-warning text-dark fw-semibold rounded-pill px-3">
                                        Jadwalkan Servis &rarr;
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- 3. GRUP: PRIORITAS RENDAH / PREVENTIF -->
        <div class="card border-0 shadow-sm rounded-4 bg-white mb-4 overflow-hidden border-top border-4 border-success">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success rounded-pill px-3 py-1 fw-bold text-uppercase">
                        <i class="bi bi-shield-check me-1"></i> Prioritas Rendah / Preventif
                    </span>
                    <span class="text-muted small">Inspeksi Rutin & Pemeliharaan Berkala</span>
                </div>
                <span class="badge bg-success-subtle text-success border border-success-subtle small"><?= count($rendah) ?> Aset Terjadwal</span>
            </div>
            <div class="card-body p-4">
                <?php if (empty($rendah)): ?>
                    <div class="text-center py-4 text-muted small">
                        Tidak ada aset dalam kategori preventif saat ini.
                    </div>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach (array_slice($rendah, 0, 6) as $item): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="p-3 rounded-4 bg-light border h-100 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <h6 class="fw-bold text-dark mb-0"><?= htmlspecialchars($item['nama']) ?></h6>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small">Baik</span>
                                    </div>
                                    <div class="font-monospace text-muted small mb-2"><?= htmlspecialchars($item['kode_inventaris']) ?></div>

                                    <div class="small mb-2">
                                        <span class="text-muted"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($item['lokasi']) ?></span><br>
                                        <span class="text-muted"><i class="bi bi-clock-history me-1"></i>Usia <?= $item['umur_tahun'] ?> thn &bull; <?= $item['terakhir_servis'] ?></span>
                                    </div>

                                    <div class="p-2 bg-white rounded-3 border small text-muted mb-2">
                                        <?= htmlspecialchars($item['tindakan_disarankan']) ?>
                                    </div>
                                </div>

                                <div class="pt-2 border-top text-end">
                                    <a href="<?= base_url('pemeliharaan/tambah?fasilitas_id=' . $item['id']) ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                        + Jadwal Rutin
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    <?php endif; ?>
</div>
