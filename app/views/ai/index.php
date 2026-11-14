<div class="container-fluid p-0">
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
        <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <span class="badge bg-dark px-3 py-1 rounded-pill small mb-2 text-warning">
                    <i class="bi bi-cpu-fill me-1"></i> Audit Trail AI & Machine Learning
                </span>
                <h4 class="fw-bold mb-1">Log & Analitik Kecerdasan Buatan</h4>
                <p class="text-muted small mb-0">Catatan pemanggilan model AI dalam analisis foto kerusakan, prediksi jadwal pemeliharaan, dan interaksi AI Assistant.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="<?= base_url('ai/analysis') ?>" class="btn btn-outline-info rounded-pill px-3 fw-semibold">
                    <i class="bi bi-graph-up-arrow me-1"></i> Facility Analysis
                </a>
                <a href="<?= base_url('ai/recommendations') ?>" class="btn btn-outline-warning text-dark rounded-pill px-3 fw-semibold">
                    <i class="bi bi-stars me-1"></i> Rekomendasi Servis
                </a>
                <a href="<?= base_url('ai-assistant') ?>" class="btn btn-primary rounded-pill px-3 fw-semibold shadow-sm">
                    <i class="bi bi-robot me-1"></i> AI Assistant &rarr;
                </a>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Fitur AI</th>
                            <th>Input Prompt</th>
                            <th>Output Rekomendasi AI</th>
                            <th>Eksekusi</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($ai_logs)): ?>
                            <?php foreach ($ai_logs as $log): ?>
                            <tr>
                                <td class="ps-4 fw-bold font-monospace">#<?= $log['id'] ?></td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace">
                                        <?= htmlspecialchars($log['fitur']) ?>
                                    </span>
                                    <div class="text-muted small mt-1">Oleh: <?= htmlspecialchars($log['nama_lengkap'] ?? 'Sistem') ?></div>
                                </td>
                                <td>
                                    <div class="text-muted text-wrap" style="max-width: 250px;"><?= htmlspecialchars($log['prompt_input']) ?></div>
                                </td>
                                <td>
                                    <div class="text-dark fw-semibold text-wrap" style="max-width: 320px;"><?= htmlspecialchars($log['response_output']) ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i><?= ucfirst($log['status']) ?></span>
                                    <small class="text-muted d-block mt-1"><?= $log['waktu_eksekusi_ms'] ?> ms</small>
                                </td>
                                <td class="text-muted"><?= date('d M Y H:i', strtotime($log['created_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Belum ada riwayat inferensi AI.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
