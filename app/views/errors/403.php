<main class="flex-grow-1 d-flex align-items-center py-5 bg-light">
    <div class="container text-center">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 p-5 bg-white">
                    <div class="mb-3">
                        <span class="badge bg-danger bg-opacity-10 text-danger p-3 rounded-circle fs-1">
                            <i class="bi bi-shield-x"></i>
                        </span>
                    </div>
                    <div class="display-4 fw-extrabold text-danger mb-2">403</div>
                    <h4 class="fw-bold mb-2">Akses Ditolak (Forbidden)</h4>
                    <p class="text-muted small mb-4">
                        Maaf, akun Anda dengan peran <span class="badge bg-secondary"><?= htmlspecialchars($current_role ?? 'Pengguna') ?></span> tidak memiliki izin otorisasi (*Role-Based Access Control*) untuk membuka halaman ini.
                    </p>

                    <?php if (!empty($required_roles)): ?>
                    <div class="alert alert-warning py-2 px-3 small border-0 bg-warning-subtle text-dark rounded-3 mb-4 text-start">
                        <i class="bi bi-info-circle-fill text-warning me-1"></i>
                        <strong>Hak akses yang diizinkan:</strong> 
                        <span class="badge bg-dark ms-1"><?= implode(', ', array_map('htmlspecialchars', (array)$required_roles)) ?></span>
                    </div>
                    <?php endif; ?>

                    <div class="d-flex justify-content-center gap-2">
                        <?php if (Auth::check()): ?>
                            <a href="<?= base_url('dashboard') ?>" class="btn btn-primary px-4 py-2 rounded-pill shadow-sm">
                                <i class="bi bi-speedometer2 me-1"></i> Kembali ke Dashboard
                            </a>
                        <?php else: ?>
                            <a href="<?= base_url('auth/login') ?>" class="btn btn-primary px-4 py-2 rounded-pill shadow-sm">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Masuk Sistem
                            </a>
                        <?php endif; ?>
                        <a href="<?= base_url() ?>" class="btn btn-outline-secondary px-4 py-2 rounded-pill">
                            <i class="bi bi-house-door me-1"></i> Beranda
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
