<footer class="bg-dark text-white pt-5 pb-4 mt-auto border-top border-secondary">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-5">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="brand-icon bg-warning text-dark rounded-3 d-flex align-items-center justify-content-center p-2">
                        <i class="bi bi-buildings-fill fs-5"></i>
                    </div>
                    <h5 class="mb-0 fw-bold">STMIK IKMI Cirebon</h5>
                </div>
                <p class="text-secondary small mb-3">
                    Sistem Informasi Pendataan Fasilitas Setiap Ruangan di Kampus STMIK IKMI Cirebon Berbasis Web dengan AI. Mengakomodasi pendataan inventaris real-time, tiket laporan kerusakan cerdas, serta monitoring pemeliharaan sarana dan prasarana kampus.
                </p>
                <div class="text-secondary small">
                    <i class="bi bi-geo-alt-fill text-warning me-1"></i> Jl. Perjuangan No. 10B, Karyamulya, Kec. Kesambi, Kota Cirebon, Jawa Barat 45131
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <h6 class="fw-bold text-white text-uppercase mb-3">Role & Akses</h6>
                <ul class="list-unstyled text-secondary small d-flex flex-column gap-2">
                    <li><i class="bi bi-shield-check text-warning me-1"></i> <strong>Administrator</strong>: Master Data & Sistem</li>
                    <li><i class="bi bi-tools text-warning me-1"></i> <strong>Petugas Sarpras</strong>: Perbaikan & Perawatan</li>
                    <li><i class="bi bi-person-badge text-warning me-1"></i> <strong>Pengguna</strong>: Dosen & Mahasiswa Pelapor</li>
                </ul>
            </div>

            <div class="col-lg-4 col-md-6">
                <h6 class="fw-bold text-white text-uppercase mb-3">Status Environment</h6>
                <div class="p-3 bg-black bg-opacity-50 rounded-3 border border-secondary border-opacity-50 small">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-secondary">Arsitektur:</span>
                        <span class="badge bg-primary">PHP Native MVC</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-secondary">Database:</span>
                        <span class="badge bg-success">MySQL 8.4 (InnoDB)</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-secondary">Schema Normal:</span>
                        <span class="badge bg-info text-dark">3NF (10 Tabel Utama)</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-secondary">Koneksi:</span>
                        <span class="text-warning fw-semibold">PDO Prepared Statements</span>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4 border-secondary border-opacity-25">

        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center small text-secondary">
            <div>&copy; <?= date('Y') ?> STMIK IKMI Cirebon. All rights reserved.</div>
            <div class="mt-2 mt-sm-0">Fasilitas Kampus v<?= APP_VERSION ?></div>
        </div>
    </div>
</footer>

<!-- Bootstrap 5.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<!-- Custom JS -->
<script src="<?= base_url('assets/js/main.js') ?>"></script>
</body>
</html>
