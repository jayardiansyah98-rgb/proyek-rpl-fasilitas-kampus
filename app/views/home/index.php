<main class="flex-grow-1">
    <!-- Hero Section -->
    <section class="hero-section text-white py-5 position-relative overflow-hidden">
        <div class="container py-4 position-relative z-1">
            <div class="row align-items-center g-5">
                <div class="col-lg-7">
                    <span class="badge bg-light text-primary px-3 py-2 rounded-pill fw-semibold mb-3 shadow-sm">
                        <i class="bi bi-cpu-fill text-warning me-1"></i> Sistem Terintegrasi AI
                    </span>
                    <h1 class="display-5 fw-extrabold lh-tight mb-3">
                        Sistem Informasi Pendataan Fasilitas Ruangan Kampus
                    </h1>
                    <p class="lead text-white-50 mb-4">
                        Platform manajemen sarana dan prasarana kampus STMIK IKMI Cirebon. Memudahkan pendataan inventaris setiap ruangan, pelaporan kerusakan secara cepat, dan penjadwalan pemeliharaan preventif.
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="<?= base_url('auth/login') ?>" class="btn btn-warning btn-lg px-4 fw-bold rounded-pill shadow">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Masuk Sistem
                        </a>
                        <a href="#database" class="btn btn-outline-light btn-lg px-4 fw-semibold rounded-pill">
                            <i class="bi bi-database me-1"></i> Lihat Data Master
                        </a>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card border-0 shadow-lg rounded-4 overflow-hidden bg-white text-dark p-4">
                        <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                            <i class="bi bi-pie-chart-fill text-primary"></i> Status Database Real-Time
                        </h5>
                        <div class="row g-3 text-center">
                            <div class="col-6">
                                <div class="p-3 bg-light rounded-3 border">
                                    <div class="fs-2 fw-extrabold text-primary"><?= $stats['gedung'] ?></div>
                                    <div class="small text-muted fw-semibold">Gedung Kampus</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 bg-light rounded-3 border">
                                    <div class="fs-2 fw-extrabold text-success"><?= $stats['ruangan'] ?></div>
                                    <div class="small text-muted fw-semibold">Ruangan Aktif</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 bg-light rounded-3 border">
                                    <div class="fs-2 fw-extrabold text-info"><?= $stats['fasilitas_unit'] ?></div>
                                    <div class="small text-muted fw-semibold">Total Unit Fasilitas</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 bg-light rounded-3 border">
                                    <div class="fs-2 fw-extrabold text-warning"><?= $stats['laporan'] ?></div>
                                    <div class="small text-muted fw-semibold">Laporan Kerusakan</div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top d-flex align-items-center justify-content-between small">
                            <span class="text-muted"><i class="bi bi-check-circle-fill text-success me-1"></i> Koneksi MySQL PDO</span>
                            <span class="badge bg-success-subtle text-success border border-success-subtle">Aktif & Normal</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Role Section -->
    <section id="fitur" class="py-5 bg-light">
        <div class="container py-3">
            <div class="text-center mb-5">
                <span class="text-primary fw-bold text-uppercase small tracking-wide">Tiga Peran Pengguna</span>
                <h2 class="fw-bold mt-1">Struktur Akses Sistem</h2>
                <p class="text-muted">Setiap role memiliki tanggung jawab dan wewenang yang terintegrasi di database</p>
            </div>

            <div class="row g-4">
                <!-- Role 1 -->
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm rounded-4 p-4 hover-lift">
                        <div class="feature-icon bg-primary bg-opacity-10 text-primary rounded-3 d-inline-flex align-items-center justify-content-center mb-3">
                            <i class="bi bi-shield-lock-fill fs-3"></i>
                        </div>
                        <h4 class="fw-bold mb-2">1. Administrator</h4>
                        <p class="text-muted small mb-4">
                            Pengelolaan penuh terhadap master data gedung, ruangan, kategori, inventaris barang fasilitas, manajemen akun pengguna, serta log sistem AI.
                        </p>
                        <div class="mt-auto">
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill">
                                Akses: admin@ikmi.ac.id
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Role 2 -->
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm rounded-4 p-4 hover-lift">
                        <div class="feature-icon bg-warning bg-opacity-10 text-warning rounded-3 d-inline-flex align-items-center justify-content-center mb-3">
                            <i class="bi bi-tools fs-3"></i>
                        </div>
                        <h4 class="fw-bold mb-2">2. Petugas Sarpras</h4>
                        <p class="text-muted small mb-4">
                            Menerima tiket laporan kerusakan, melakukan verifikasi fisik fasilitas, memperbarui status kondisi, dan menjadwalkan tindakan pemeliharaan teknis.
                        </p>
                        <div class="mt-auto">
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-2 rounded-pill">
                                Akses: petugas@ikmi.ac.id
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Role 3 -->
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm rounded-4 p-4 hover-lift">
                        <div class="feature-icon bg-success bg-opacity-10 text-success rounded-3 d-inline-flex align-items-center justify-content-center mb-3">
                            <i class="bi bi-mortarboard-fill fs-3"></i>
                        </div>
                        <h4 class="fw-bold mb-2">3. Civitas (Dosen / Mahasiswa)</h4>
                        <p class="text-muted small mb-4">
                            Melihat informasi ketersediaan sarana ruangan perkuliahan & laboratorium, serta membuat tiket pelaporan kerusakan jika menemukan fasilitas rusak.
                        </p>
                        <div class="mt-auto">
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill">
                                Akses: dosen@ikmi.ac.id / mahasiswa@ikmi.ac.id
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Database Structure Section -->
    <section id="database" class="py-5 bg-white">
        <div class="container py-3">
            <div class="text-center mb-5">
                <span class="text-primary fw-bold text-uppercase small tracking-wide">Relational Database 3NF</span>
                <h2 class="fw-bold mt-1">10 Tabel Utama Database MySQL</h2>
                <p class="text-muted">Desain skema normal dengan integritas referensial (Foreign Key) dan indexing performa</p>
            </div>

            <div class="row g-3">
                <div class="col-lg-6">
                    <div class="list-group shadow-sm rounded-4 border-0">
                        <div class="list-group-item list-group-item-action p-3 border-bottom">
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <h6 class="mb-1 fw-bold"><i class="bi bi-table text-primary me-2"></i>1. roles</h6>
                                <span class="badge bg-primary rounded-pill">Master Role</span>
                            </div>
                            <small class="text-muted">Menyimpan hak akses (admin, petugas_sarpras, pengguna).</small>
                        </div>
                        <div class="list-group-item list-group-item-action p-3 border-bottom">
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <h6 class="mb-1 fw-bold"><i class="bi bi-table text-primary me-2"></i>2. users</h6>
                                <span class="badge bg-primary rounded-pill">Entitas Pengguna</span>
                            </div>
                            <small class="text-muted">Data akun civitas kampus (NIM/NIDN, email, password bcrypt, status).</small>
                        </div>
                        <div class="list-group-item list-group-item-action p-3 border-bottom">
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <h6 class="mb-1 fw-bold"><i class="bi bi-table text-primary me-2"></i>3. gedung</h6>
                                <span class="badge bg-secondary rounded-pill">Lokasi Fisik</span>
                            </div>
                            <small class="text-muted">Daftar gedung kampus (Gedung Rektorat, ICT & Lab Terpadu).</small>
                        </div>
                        <div class="list-group-item list-group-item-action p-3 border-bottom">
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <h6 class="mb-1 fw-bold"><i class="bi bi-table text-primary me-2"></i>4. ruangan</h6>
                                <span class="badge bg-secondary rounded-pill">Ruang & Lab</span>
                            </div>
                            <small class="text-muted">Relasi Gedung 1:N Ruangan (Lab AI, Lab Jaringan, Ruang Kuliah 201).</small>
                        </div>
                        <div class="list-group-item list-group-item-action p-3">
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <h6 class="mb-1 fw-bold"><i class="bi bi-table text-primary me-2"></i>5. kategori_fasilitas</h6>
                                <span class="badge bg-info text-dark rounded-pill">Klasifikasi</span>
                            </div>
                            <small class="text-muted">Pengelompokan barang (Komputer, Jaringan, Multimedia, AC, Furnitur).</small>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="list-group shadow-sm rounded-4 border-0">
                        <div class="list-group-item list-group-item-action p-3 border-bottom">
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <h6 class="mb-1 fw-bold"><i class="bi bi-table text-success me-2"></i>6. fasilitas</h6>
                                <span class="badge bg-success rounded-pill">Inventaris Utama</span>
                            </div>
                            <small class="text-muted">Relasi Ruangan 1:N Fasilitas & Kategori 1:N Fasilitas (Kondisi, Jumlah, Tahun, Foto).</small>
                        </div>
                        <div class="list-group-item list-group-item-action p-3 border-bottom">
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <h6 class="mb-1 fw-bold"><i class="bi bi-table text-warning me-2"></i>7. laporan_kerusakan</h6>
                                <span class="badge bg-warning text-dark rounded-pill">Tiket Kerusakan</span>
                            </div>
                            <small class="text-muted">Relasi User, Fasilitas & Ruangan (Prioritas, Status, Catatan Petugas).</small>
                        </div>
                        <div class="list-group-item list-group-item-action p-3 border-bottom">
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <h6 class="mb-1 fw-bold"><i class="bi bi-table text-warning me-2"></i>8. pemeliharaan</h6>
                                <span class="badge bg-warning text-dark rounded-pill">Perawatan & Reparasi</span>
                            </div>
                            <small class="text-muted">Relasi Fasilitas 1:N Pemeliharaan & Petugas (Biaya, Tanggal, Jenis Tindakan).</small>
                        </div>
                        <div class="list-group-item list-group-item-action p-3 border-bottom">
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <h6 class="mb-1 fw-bold"><i class="bi bi-table text-danger me-2"></i>9. notifikasi</h6>
                                <span class="badge bg-danger rounded-pill">Broadcast & Alert</span>
                            </div>
                            <small class="text-muted">Notifikasi langsung kepada user pelapor & petugas teknisi sarpras.</small>
                        </div>
                        <div class="list-group-item list-group-item-action p-3">
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <h6 class="mb-1 fw-bold"><i class="bi bi-table text-purple me-2"></i>10. ai_logs</h6>
                                <span class="badge bg-dark rounded-pill">Audit Trail AI</span>
                            </div>
                            <small class="text-muted">Log analitik AI: deteksi kerusakan gambar, estimasi biaya, rekomendasi.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

</main>

