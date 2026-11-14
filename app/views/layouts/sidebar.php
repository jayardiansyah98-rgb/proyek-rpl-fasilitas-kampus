<?php
$currentUrl = $_GET['url'] ?? 'dashboard';
$urlSegments = explode('/', trim($currentUrl, '/'));
$activeSegment = $urlSegments[0] ?? 'dashboard';
$subSegment = $urlSegments[1] ?? '';
$userRole = Auth::role();
?>

<!-- Sidebar Navigation -->
<aside class="sidebar d-flex flex-column py-3 px-2" id="mainSidebar">
    <!-- Brand Header -->
    <div class="px-3 py-2 mb-3 border-bottom border-secondary border-opacity-25 d-flex align-items-center justify-content-between">
        <a href="<?= base_url('dashboard') ?>" class="text-white text-decoration-none d-flex align-items-center gap-2">
            <div class="brand-icon bg-primary text-white rounded-3 d-flex align-items-center justify-content-center p-2 shadow-sm">
                <i class="bi bi-buildings-fill fs-5"></i>
            </div>
            <div>
                <div class="fw-bold fs-6 text-white lh-1">STMIK IKMI</div>
                <small class="text-secondary" style="font-size: 0.7rem;">Fasilitas Kampus</small>
            </div>
        </a>
        <button type="button" class="btn btn-sm btn-link text-white d-lg-none p-0" id="btnSidebarClose">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <!-- Navigation Links -->
    <div class="flex-grow-1 overflow-auto px-2">
        <ul class="nav flex-column">
            <!-- Menu Utama -->
            <li class="sidebar-heading">Menu Utama</li>
            <li class="nav-item">
                <a class="nav-link <?= ($activeSegment === 'dashboard') ? 'active' : '' ?>" href="<?= base_url('dashboard') ?>">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>

            <!-- MENU KHUSUS ADMIN & PETUGAS -->
            <?php if (Auth::hasRole(['admin', 'petugas_sarpras'])): ?>
                <li class="sidebar-heading">Manajemen Sarpras</li>

                <li class="nav-item">
                    <a class="nav-link <?= ($activeSegment === 'gedung') ? 'active' : '' ?>" href="<?= base_url('gedung') ?>">
                        <i class="bi bi-building"></i> Data Gedung
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($activeSegment === 'ruangan') ? 'active' : '' ?>" href="<?= base_url('ruangan') ?>">
                        <i class="bi bi-door-open"></i> Data Ruangan
                    </a>
                </li>

                <?php if (Auth::hasRole('admin')): ?>
                <li class="nav-item">
                    <a class="nav-link <?= ($activeSegment === 'kategori') ? 'active' : '' ?>" href="<?= base_url('kategori') ?>">
                        <i class="bi bi-tags"></i> Kategori Fasilitas
                    </a>
                </li>
                <?php endif; ?>

                <li class="nav-item">
                    <a class="nav-link <?= ($activeSegment === 'fasilitas') ? 'active' : '' ?>" href="<?= base_url('fasilitas') ?>">
                        <i class="bi bi-box-seam"></i> Data Fasilitas
                    </a>
                </li>

                <li class="sidebar-heading">Layanan & Pemeliharaan</li>
                <li class="nav-item">
                    <a class="nav-link <?= ($activeSegment === 'laporan') ? 'active' : '' ?>" href="<?= base_url('laporan') ?>">
                        <i class="bi bi-exclamation-octagon"></i> Laporan Kerusakan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($activeSegment === 'pemeliharaan') ? 'active' : '' ?>" href="<?= base_url('pemeliharaan') ?>">
                        <i class="bi bi-tools"></i> Pemeliharaan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($activeSegment === 'report' || $activeSegment === 'reporting') ? 'active' : '' ?>" href="<?= base_url('report') ?>">
                        <i class="bi bi-file-earmark-bar-graph"></i> Rekapitulasi & Laporan
                    </a>
                </li>

                <li class="sidebar-heading">Sistem & Kecerdasan</li>
                <li class="nav-item">
                    <a class="nav-link <?= ($activeSegment === 'ai-assistant' || $activeSegment === 'ai_assistant') ? 'active' : '' ?>" href="<?= base_url('ai-assistant') ?>">
                        <i class="bi bi-robot text-primary"></i> AI Assistant
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($activeSegment === 'ai' && $subSegment === 'analysis') ? 'active' : '' ?>" href="<?= base_url('ai/analysis') ?>">
                        <i class="bi bi-graph-up-arrow text-info"></i> AI Facility Analysis
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($activeSegment === 'ai' && $subSegment === 'recommendations') ? 'active' : '' ?>" href="<?= base_url('ai/recommendations') ?>">
                        <i class="bi bi-stars text-warning"></i> AI Rekomendasi Servis
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($activeSegment === 'ai' && ($subSegment === '' || $subSegment === 'index' || $subSegment === 'logs')) ? 'active' : '' ?>" href="<?= base_url('ai') ?>">
                        <i class="bi bi-cpu text-secondary"></i> Analitik AI & Logs
                    </a>
                </li>
                <?php if (Auth::hasRole('admin')): ?>
                <li class="nav-item">
                    <a class="nav-link <?= ($activeSegment === 'ai' && $subSegment === 'pengaturan') ? 'active' : '' ?>" href="<?= base_url('ai/pengaturan') ?>">
                        <i class="bi bi-sliders2-vertical text-warning"></i> Pengaturan AI Assistant
                    </a>
                </li>
                <?php endif; ?>

                <?php if (Auth::hasRole('admin')): ?>
                <li class="sidebar-heading">Administrasi Sistem</li>
                <li class="nav-item">
                    <a class="nav-link <?= ($activeSegment === 'user' || $activeSegment === 'users') ? 'active' : '' ?>" href="<?= base_url('user') ?>">
                        <i class="bi bi-people"></i> Manajemen Pengguna
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($activeSegment === 'ai' && $subSegment === 'pengaturan') ? 'active' : '' ?>" href="<?= base_url('ai/pengaturan') ?>">
                        <i class="bi bi-sliders2-vertical text-warning"></i> Pengaturan AI Assistant
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($activeSegment === 'notifikasi' && $subSegment === 'pengaturan') ? 'active' : '' ?>" href="<?= base_url('notifikasi/pengaturan') ?>">
                        <i class="bi bi-gear-wide-connected"></i> Pengaturan Notifikasi
                    </a>
                </li>
                <?php endif; ?>

                <!-- Link Notifikasi untuk admin & petugas -->
                <li class="nav-item">
                    <a class="nav-link <?= ($activeSegment === 'notifikasi' && $subSegment !== 'pengaturan') ? 'active' : '' ?>" href="<?= base_url('notifikasi') ?>">
                        <i class="bi bi-bell"></i> Pusat Notifikasi
                    </a>
                </li>

            <!-- MENU KHUSUS PENGGUNA (DOSEN & MAHASISWA) -->
            <?php else: ?>
                <li class="sidebar-heading">Fasilitas Kampus</li>
                <li class="nav-item">
                    <a class="nav-link <?= ($activeSegment === 'ruangan') ? 'active' : '' ?>" href="<?= base_url('ruangan') ?>">
                        <i class="bi bi-door-open"></i> Daftar Ruangan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($activeSegment === 'fasilitas') ? 'active' : '' ?>" href="<?= base_url('fasilitas') ?>">
                        <i class="bi bi-box-seam"></i> Katalog Fasilitas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($activeSegment === 'pemeliharaan') ? 'active' : '' ?>" href="<?= base_url('pemeliharaan') ?>">
                        <i class="bi bi-tools"></i> Info Pemeliharaan
                    </a>
                </li>

                <li class="sidebar-heading">Pengaduan</li>
                <li class="nav-item">
                    <a class="nav-link <?= ($activeSegment === 'laporan' && (!isset($urlSegments[1]) || $urlSegments[1] !== 'tambah')) ? 'active' : '' ?>" href="<?= base_url('laporan') ?>">
                        <i class="bi bi-journal-text"></i> Riwayat Laporan Saya
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($activeSegment === 'laporan' && isset($urlSegments[1]) && $urlSegments[1] === 'tambah') ? 'active' : '' ?>" href="<?= base_url('laporan/tambah') ?>">
                        <i class="bi bi-plus-circle text-warning"></i> Buat Laporan Baru
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($activeSegment === 'notifikasi') ? 'active' : '' ?>" href="<?= base_url('notifikasi') ?>">
                        <i class="bi bi-bell"></i> Notifikasi Saya
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </div>

    <!-- Quick User Profile Footer in Sidebar -->
    <div class="p-3 mt-auto border-top border-secondary border-opacity-25 bg-black bg-opacity-25 rounded-3 mx-2">
        <div class="d-flex align-items-center gap-2">
            <div class="user-avatar" style="width: 32px; height: 32px; font-size: 0.8rem;">
                <?= strtoupper(substr(Auth::user()['nama_lengkap'] ?? 'U', 0, 1)) ?>
            </div>
            <div class="overflow-hidden">
                <div class="text-white text-truncate small fw-bold"><?= htmlspecialchars(Auth::user()['nama_lengkap'] ?? '') ?></div>
                <div class="text-secondary small" style="font-size: 0.72rem;"><?= htmlspecialchars(Auth::user()['nomor_induk'] ?? '') ?></div>
            </div>
        </div>
    </div>
</aside>

<div class="sidebar-backdrop" id="sidebarBackdrop"></div>
