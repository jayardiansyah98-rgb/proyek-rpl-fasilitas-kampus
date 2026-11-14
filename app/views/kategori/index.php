<div class="container-fluid p-0">
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
        <div class="card-body p-4">
            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1 rounded-pill small mb-2">
                <i class="bi bi-shield-lock-fill me-1"></i> Khusus Administrator
            </span>
            <h4 class="fw-bold mb-1">Kategori Fasilitas</h4>
            <p class="text-muted small mb-0">Klasifikasi kelompok sarana dan prasarana kampus STMIK IKMI Cirebon.</p>
        </div>
    </div>

    <div class="row g-3">
        <?php foreach ($kategori_list as $k): ?>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                <span class="badge bg-light text-primary border font-monospace px-3 py-1 rounded-pill mb-2 align-self-start">
                    <?= htmlspecialchars($k['kode_kategori']) ?>
                </span>
                <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($k['nama_kategori']) ?></h5>
                <p class="text-muted small mb-3 flex-grow-1"><?= htmlspecialchars($k['deskripsi']) ?></p>
                <div class="pt-2 border-top small text-muted">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">
                        <?= $k['total_fasilitas'] ?> Item Terdaftar
                    </span>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
