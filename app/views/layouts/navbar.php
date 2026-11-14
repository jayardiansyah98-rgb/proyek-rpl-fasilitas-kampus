<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top shadow-sm py-3">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="<?= base_url() ?>">
            <div class="brand-icon bg-white text-primary rounded-3 d-flex align-items-center justify-content-center">
                <i class="bi bi-buildings-fill fs-5"></i>
            </div>
            <span>STMIK IKMI <span class="badge bg-warning text-dark fs-7 ms-1">Fasilitas AI</span></span>
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-query="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3">
                <li class="nav-item">
                    <a class="nav-link text-white active" href="<?= base_url() ?>"><i class="bi bi-house-door me-1"></i> Beranda</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white-50" href="#fitur"><i class="bi bi-grid me-1"></i> Modul Sistem</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white-50" href="#database"><i class="bi bi-database me-1"></i> Struktur Data</a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-3">
                <?php if (Auth::check()): ?>
                    <a href="<?= base_url('dashboard') ?>" class="btn btn-warning fw-semibold px-4 rounded-pill shadow-sm">
                        <i class="bi bi-speedometer2 me-1"></i> Dashboard (<?= htmlspecialchars(Auth::user()['nama_lengkap'] ?? '') ?>)
                    </a>
                <?php else: ?>
                    <a href="<?= base_url('auth/login') ?>" class="btn btn-warning fw-semibold px-4 rounded-pill shadow-sm">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Masuk Sistem
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
