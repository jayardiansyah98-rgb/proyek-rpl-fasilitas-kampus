<div class="container-fluid p-0">
    <!-- Header Modul AI Facility Analysis -->
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-4 bg-primary text-white p-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 54px; height: 54px;">
                        <i class="bi bi-cpu fs-3"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h4 class="fw-bold mb-0 text-dark">AI Facility Analysis</h4>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 rounded-pill small">
                                <i class="bi bi-graph-up-arrow me-1"></i> Analisis Multi-Dimensi Sarpras
                            </span>
                        </div>
                        <p class="text-muted small mb-0 mt-1">
                            Evaluasi kondisi fisik, umur ekonomis, frekuensi kerusakan, riwayat servis, serta deteksi pola anomali di kampus STMIK IKMI Cirebon.
                        </p>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <a href="<?= base_url('ai/recommendations') ?>" class="btn btn-warning text-dark fw-semibold rounded-pill btn-sm px-3 shadow-sm">
                        <i class="bi bi-stars me-1"></i> Rekomendasi Pemeliharaan &rarr;
                    </a>
                    <a href="<?= base_url('ai-assistant') ?>" class="btn btn-outline-primary rounded-pill btn-sm px-3">
                        <i class="bi bi-chat-dots me-1"></i> Tanya AI Assistant
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($analysis['cukup_data'])): ?>
        <!-- Notifikasi Jika Data Tidak Cukup -->
        <div class="alert alert-warning border-0 rounded-4 shadow-sm p-4 d-flex align-items-center gap-3 mb-4">
            <i class="bi bi-exclamation-triangle-fill fs-1 text-warning"></i>
            <div>
                <h5 class="fw-bold mb-1">Data Belum Mencukupi</h5>
                <p class="mb-0 small text-dark">
                    <?= htmlspecialchars($analysis['pesan_data_kurang'] ?? 'Data fasilitas belum mencukupi untuk melakukan analisis korelasi dan pola kerusakan.') ?>
                </p>
            </div>
        </div>
    <?php else: 
        $r = $analysis['ringkasan'];
        $fasilitasBermasalah = $analysis['fasilitas_bermasalah'];
        $gedungTop = $analysis['gedung_masalah_terbanyak'];
        $ruanganTop = $analysis['ruangan_masalah_terbanyak'];
        $pola = $analysis['pola_kerusakan'];
    ?>

        <!-- 1. KARTU METRIK RINGKASAN KONDISI FASILITAS -->
        <div class="row g-3 mb-4">
            <!-- Total Fasilitas -->
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-semibold">Total Fasilitas</div>
                            <div class="fs-4 fw-bold text-dark mb-0"><?= number_format($r['total_fasilitas']) ?> <span class="fs-6 fw-normal text-muted">Item</span></div>
                            <small class="text-muted"><?= number_format($r['total_unit']) ?> Total Unit</small>
                        </div>
                        <div class="rounded-4 bg-primary-subtle text-primary p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-box-seam fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kondisi Prima -->
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-4 border-success">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-semibold">Kondisi Baik</div>
                            <div class="fs-4 fw-bold text-success mb-0"><?= number_format($r['kondisi_counts']['baik']) ?> <span class="fs-6 fw-normal text-muted">(<?= $r['persentase_baik'] ?>%)</span></div>
                            <small class="text-success"><i class="bi bi-check-circle me-1"></i>Siap Operasional</small>
                        </div>
                        <div class="rounded-4 bg-success-subtle text-success p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-shield-check fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fasilitas Bermasalah -->
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-4 border-danger">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-semibold">Fasilitas Bermasalah</div>
                            <div class="fs-4 fw-bold text-danger mb-0"><?= number_format(count($fasilitasBermasalah)) ?> <span class="fs-6 fw-normal text-muted">(<?= $r['persentase_bermasalah'] ?>%)</span></div>
                            <small class="text-danger"><i class="bi bi-exclamation-octagon me-1"></i>Perlu Intervensi</small>
                        </div>
                        <div class="rounded-4 bg-danger-subtle text-danger p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-tools fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rata-rata Umur Fasilitas -->
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-4 border-info">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-semibold">Rata-rata Umur Aset</div>
                            <div class="fs-4 fw-bold text-info mb-0"><?= $r['rata_rata_usia_tahun'] ?> <span class="fs-6 fw-normal text-muted">Tahun</span></div>
                            <small class="text-muted">Berdasarkan tahun pengadaan</small>
                        </div>
                        <div class="rounded-4 bg-info-subtle text-info p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-hourglass-split fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. ANALISIS KONSENTRASI: GEDUNG & RUANGAN DENGAN MASALAH TERBANYAK -->
        <div class="row g-4 mb-4">
            <!-- Gedung dengan Masalah Terbanyak -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 bg-white h-100 overflow-hidden">
                    <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-building-exclamation text-danger me-2"></i>Gedung dengan Masalah Terbanyak
                        </h6>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle small">Fokus Prioritas</span>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($gedungTop): ?>
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="rounded-4 bg-danger-subtle text-danger p-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 52px; height: 52px;">
                                    <i class="bi bi-building fs-3"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($gedungTop['nama_gedung']) ?></h5>
                                    <span class="badge bg-dark font-monospace"><?= htmlspecialchars($gedungTop['kode_gedung']) ?></span>
                                </div>
                            </div>
                            <div class="row g-2 text-center small mb-3">
                                <div class="col-4">
                                    <div class="p-2 rounded-3 bg-light border">
                                        <div class="fw-bold text-danger fs-5"><?= $gedungTop['fasilitas_rusak'] ?></div>
                                        <div class="text-muted">Item Rusak</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-2 rounded-3 bg-light border">
                                        <div class="fw-bold text-dark fs-5"><?= $gedungTop['total_laporan'] ?></div>
                                        <div class="text-muted">Tiket Aduan</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-2 rounded-3 bg-light border">
                                        <div class="fw-bold text-primary fs-5"><?= $gedungTop['persentase_rusak_gedung'] ?>%</div>
                                        <div class="text-muted">Rasio Rusak</div>
                                    </div>
                                </div>
                            </div>
                            <p class="text-muted small mb-0">
                                <i class="bi bi-info-circle me-1"></i> Gedung ini mencatatkan akumulasi keluhan dan rasio kerusakan tertinggi di lingkungan kampus. Disarankan melakukan audit sarpras menyeluruh di seluruh lantai.
                            </p>
                        <?php else: ?>
                            <div class="text-center py-4 text-muted small">Tidak ada data anomali kerusakan gedung.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Ruangan dengan Masalah Terbanyak -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 bg-white h-100 overflow-hidden">
                    <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-door-closed-fill text-warning me-2"></i>Ruangan dengan Masalah Terbanyak
                        </h6>
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle small">Paling Sering Dilaporkan</span>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($ruanganTop): ?>
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="rounded-4 bg-warning-subtle text-warning-emphasis p-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 52px; height: 52px;">
                                    <i class="bi bi-door-open fs-3"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($ruanganTop['nama_ruangan']) ?></h5>
                                    <small class="text-muted">
                                        <?= htmlspecialchars($ruanganTop['nama_gedung']) ?> &bull; Lantai <?= $ruanganTop['lantai'] ?> (<?= htmlspecialchars($ruanganTop['jenis_ruangan']) ?>)
                                    </small>
                                </div>
                            </div>
                            <div class="row g-2 text-center small mb-3">
                                <div class="col-4">
                                    <div class="p-2 rounded-3 bg-light border">
                                        <div class="fw-bold text-danger fs-5"><?= $ruanganTop['fasilitas_rusak'] ?></div>
                                        <div class="text-muted">Item Rusak</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-2 rounded-3 bg-light border">
                                        <div class="fw-bold text-warning-emphasis fs-5"><?= $ruanganTop['total_laporan'] ?></div>
                                        <div class="text-muted">Total Laporan</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-2 rounded-3 bg-light border">
                                        <div class="fw-bold text-danger fs-5"><?= $ruanganTop['active_laporan'] ?></div>
                                        <div class="text-muted">Tiket Aktif</div>
                                    </div>
                                </div>
                            </div>
                            <p class="text-muted small mb-0">
                                <i class="bi bi-info-circle me-1"></i> Ruangan ini memiliki intensitas penggunaan tinggi dan tingkat komplain sarana tertinggi. Teknisi disarankan memprioritaskan inspeksi berkala di ruangan ini.
                            </p>
                        <?php else: ?>
                            <div class="text-center py-4 text-muted small">Tidak ada data ruangan dengan anomali kerusakan.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. FASILITAS BERMASALAH (DETAIL AUDIT) -->
        <div class="card border-0 shadow-sm rounded-4 bg-white mb-4 overflow-hidden">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>Daftar Fasilitas Bermasalah
                    </h6>
                    <small class="text-muted">Aset yang terdeteksi memiliki kerusakan fisik, riwayat komplain berulang, atau usia pakai kritis.</small>
                </div>
                <span class="badge bg-danger rounded-pill px-3 py-2 small fw-semibold">
                    <?= count($fasilitasBermasalah) ?> Fasilitas Terdeteksi
                </span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($fasilitasBermasalah)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-check-circle-fill text-success fs-1 d-block mb-2"></i>
                        <h6 class="fw-bold text-dark">Seluruh Fasilitas dalam Kondisi Baik</h6>
                        <small>Tidak ada sarana prasarana yang saat ini membutuhkan tindakan perbaikan mendesak.</small>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Fasilitas / Kode</th>
                                    <th>Lokasi</th>
                                    <th>Kondisi Fisik</th>
                                    <th>Umur Aset</th>
                                    <th>Frekuensi Kerusakan</th>
                                    <th>Alasan Terdeteksi AI</th>
                                    <th class="pe-4 text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($fasilitasBermasalah as $fb): 
                                    $badgeKondisi = ($fb['kondisi'] === 'rusak_berat' || $fb['kondisi'] === 'tidak_layak') 
                                        ? 'bg-danger text-white' 
                                        : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle';
                                ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($fb['nama']) ?></div>
                                        <span class="font-monospace text-muted small"><?= htmlspecialchars($fb['kode_inventaris']) ?> (<?= $fb['jumlah'] ?> Unit)</span>
                                    </td>
                                    <td>
                                        <div class="text-dark fw-semibold"><?= htmlspecialchars($fb['nama_ruangan']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($fb['nama_gedung']) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge <?= $badgeKondisi ?> px-3 py-1 rounded-pill small">
                                            <?= ucfirst(str_replace('_', ' ', $fb['kondisi'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= $fb['umur_tahun'] ?> Tahun</div>
                                        <small class="text-muted">Pengadaan <?= $fb['tahun_pengadaan'] ?></small>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-danger"><?= $fb['total_laporan'] ?> Kali Dilaporkan</div>
                                        <small class="text-muted"><?= $fb['total_pemeliharaan'] ?> Kali Diservis</small>
                                    </td>
                                    <td style="max-width: 320px;">
                                        <ul class="mb-0 ps-3 text-muted" style="font-size: 0.78rem;">
                                            <?php foreach ($fb['alasan_masalah'] as $als): ?>
                                                <li><?= htmlspecialchars($als) ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <a href="<?= base_url('fasilitas/detail/' . $fb['id']) ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                            Detail &rarr;
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

        <!-- 4. POLA KERUSAKAN YANG TERLIHAT (PATTERN RECOGNITION) -->
        <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-puzzle-fill text-primary me-2"></i>Pola Kerusakan yang Terlihat (AI Pattern Insights)
                </h6>
                <small class="text-muted">Korelasi kecerdasan buatan antara faktor usia, jenis perangkat, dan intensitas pemakaian ruangan.</small>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <?php foreach ($pola as $p): ?>
                    <div class="col-md-6">
                        <div class="p-3 rounded-4 bg-light border h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold text-dark"><i class="bi bi-check2-circle text-primary me-1"></i><?= htmlspecialchars($p['judul']) ?></span>
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill small"><?= $p['tingkat'] ?></span>
                                </div>
                                <p class="text-muted small mb-2">
                                    <?= htmlspecialchars($p['deskripsi']) ?>
                                </p>
                            </div>
                            <div class="p-2 rounded-3 bg-white border small text-dark mt-2">
                                <strong class="text-primary"><i class="bi bi-lightbulb me-1"></i>Saran AI:</strong> <?= htmlspecialchars($p['rekomendasi']) ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

    <?php endif; ?>
</div>
