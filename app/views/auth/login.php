<main class="flex-grow-1 d-flex align-items-center py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 p-4 p-sm-5 bg-white">
                    <div class="text-center mb-4">
                        <div class="brand-icon bg-primary text-white rounded-3 d-inline-flex align-items-center justify-content-center p-3 mb-2 shadow-sm">
                            <i class="bi bi-buildings-fill fs-3"></i>
                        </div>
                        <h4 class="fw-bold mb-1">Masuk Sistem</h4>
                        <p class="text-muted small">Pendataan Fasilitas Ruangan STMIK IKMI Cirebon</p>
                    </div>

                    <!-- Flash Message Success -->
                    <?php if (!empty($_SESSION['flash_success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show small py-2 px-3 mb-3 border-0 bg-success-subtle text-success" role="alert">
                            <i class="bi bi-check-circle-fill me-1"></i> <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?>
                            <button type="button" class="btn-close small" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Flash Message Error -->
                    <?php if (!empty($_SESSION['flash_error']) || !empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show small py-2 px-3 mb-3 border-0 bg-danger-subtle text-danger" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> 
                            <?= !empty($error) ? $error : $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
                            <button type="button" class="btn-close small" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('auth/login') ?>" method="POST" id="loginForm">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label for="identifier" class="form-label small fw-semibold">Email atau Nomor Induk (NIM / NIDN / NIP)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-person-badge"></i></span>
                                <input type="text" name="identifier" class="form-control border-start-0" id="identifier" 
                                       placeholder="Email kampus atau Nomor Induk (NIM / NIDN / NIP)" 
                                       value="<?= htmlspecialchars($old_identifier ?? '') ?>" required autofocus>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="password" class="form-label small fw-semibold mb-0">Kata Sandi</label>
                                <button type="button" class="btn btn-link p-0 text-decoration-none small text-muted" id="btnTogglePassword">
                                    <i class="bi bi-eye" id="toggleIcon"></i> Tampilkan
                                </button>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                                <input type="password" name="password" class="form-control border-start-0" id="password" 
                                       placeholder="••••••••" required>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-4 small">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember">
                                <label class="form-check-label text-muted" for="remember">Ingat Saya</label>
                            </div>
                            <span class="text-muted">Lupa sandi? Hubungi Sarpras</span>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold rounded-3 shadow-sm">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Masuk Sekarang
                        </button>
                    </form>
                </div>

                <div class="text-center mt-3 small">
                    <a href="<?= base_url() ?>" class="text-decoration-none text-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Kembali ke Halaman Utama
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Toggle Password Visibility
    const btnToggle = document.getElementById('btnTogglePassword');
    const inputPass = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');

    if (btnToggle && inputPass) {
        btnToggle.addEventListener('click', () => {
            const isPassword = inputPass.getAttribute('type') === 'password';
            inputPass.setAttribute('type', isPassword ? 'text' : 'password');
            toggleIcon.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
            btnToggle.innerHTML = isPassword ? '<i class="bi bi-eye-slash"></i> Sembunyikan' : '<i class="bi bi-eye"></i> Tampilkan';
        });
    }
});
</script>
