<?php
$user = Auth::user();
$roleName = $user['nama_role'] ?? 'pengguna';

$roleBadgeClass = 'bg-success-subtle text-success border border-success-subtle';
$roleLabel = 'Civitas Akademika';

if ($roleName === 'admin') {
    $roleBadgeClass = 'bg-danger-subtle text-danger border border-danger-subtle';
    $roleLabel = 'Administrator';
} elseif ($roleName === 'petugas_sarpras') {
    $roleBadgeClass = 'bg-warning-subtle text-warning border border-warning-subtle';
    $roleLabel = 'Petugas Sarpras';
} elseif ($user['jenis_pengguna'] === 'dosen') {
    $roleLabel = 'Dosen Pengguna';
} elseif ($user['jenis_pengguna'] === 'mahasiswa') {
    $roleLabel = 'Mahasiswa Pengguna';
}
?>

<div class="content-area">
    <!-- Topbar Navigation -->
    <header class="bg-white border-bottom py-2 px-3 px-lg-4 sticky-top shadow-sm">
        <div class="d-flex align-items-center justify-content-between">
            <!-- Left Side: Hamburger & Breadcrumb -->
            <div class="d-flex align-items-center gap-3">
                <button type="button" class="btn btn-outline-secondary btn-sm d-lg-none" id="btnSidebarToggle">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <div class="d-none d-sm-block">
                    <div class="text-muted small">STMIK IKMI Cirebon &bull; Fasilitas Kampus</div>
                    <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($title ?? 'Dashboard') ?></div>
                </div>
            </div>

            <!-- Right Side: User Profile & Actions -->
            <div class="d-flex align-items-center gap-3">
                <!-- 🔔 Notification Bell -->
                <?php
                // Hitung unread in-app notification untuk bell badge
                try {
                    $_notifDb    = new Database();
                    $_unreadCount = (int)$_notifDb->query("SELECT COUNT(*) as c FROM notifikasi WHERE user_id = :uid AND is_read = 0")
                        ->bind(':uid', (int)($user['id'] ?? 0), PDO::PARAM_INT)
                        ->single()['c'];
                } catch (Exception $e) { $_unreadCount = 0; }
                ?>
                <div class="dropdown" id="notifBellDropdown">
                    <button class="btn btn-light btn-sm rounded-circle position-relative border p-2"
                            type="button" data-bs-toggle="dropdown" aria-expanded="false"
                            title="Notifikasi" id="notifBellBtn">
                        <i class="bi bi-bell-fill fs-6 text-secondary"></i>
                        <?php if ($_unreadCount > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                  id="notif-badge" style="font-size:0.65rem; min-width:18px;">
                                <?= $_unreadCount > 99 ? '99+' : $_unreadCount ?>
                            </span>
                        <?php else: ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none"
                                  id="notif-badge" style="font-size:0.65rem; min-width:18px;">0</span>
                        <?php endif; ?>
                    </button>

                    <div class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 p-0 mt-2"
                         style="min-width:340px; max-width:380px;">
                        <!-- Bell Header -->
                        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                            <span class="fw-semibold text-dark small"><i class="bi bi-bell me-1"></i>Notifikasi</span>
                            <div class="d-flex gap-2 align-items-center">
                                <button class="btn btn-xs btn-link text-muted p-0 small" id="bellMarkAll" style="font-size:.75rem;">Tandai Semua Dibaca</button>
                                <a href="<?= base_url('notifikasi') ?>" class="btn btn-xs btn-link p-0 small" style="font-size:.75rem;">Lihat Semua</a>
                            </div>
                        </div>

                        <!-- Bell Items -->
                        <div id="bellItems" style="max-height:360px; overflow-y:auto;">
                            <div class="text-center text-muted py-4 small" id="bellLoading">
                                <div class="spinner-border spinner-border-sm me-1"></div> Memuat...
                            </div>
                        </div>

                        <div class="border-top text-center py-2">
                            <a href="<?= base_url('notifikasi') ?>" class="small text-primary text-decoration-none">Lihat semua notifikasi →</a>
                        </div>
                    </div>
                </div>
                <!-- /Notification Bell -->

                <!-- Role Badge -->
                <span class="badge <?= $roleBadgeClass ?> px-3 py-2 rounded-pill fw-semibold small">
                    <i class="bi bi-shield-fill-check me-1"></i> <?= $roleLabel ?>
                </span>

                <!-- User Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-light bg-light rounded-pill p-1 pe-3 d-flex align-items-center gap-2 border" 
                            type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="user-avatar" style="width: 32px; height: 32px; font-size: 0.85rem;">
                            <?= strtoupper(substr($user['nama_lengkap'] ?? 'U', 0, 1)) ?>
                        </div>
                        <div class="text-start d-none d-md-block" style="line-height: 1.2;">
                            <div class="small fw-bold text-dark text-truncate" style="max-width: 140px;">
                                <?= htmlspecialchars($user['nama_lengkap'] ?? '') ?>
                            </div>
                            <div class="text-muted" style="font-size: 0.7rem;">
                                <?= htmlspecialchars($user['nomor_induk'] ?? '') ?>
                            </div>
                        </div>
                        <i class="bi bi-chevron-down text-muted small ms-1"></i>
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 p-2 mt-2" style="min-width: 240px;">
                        <li class="px-3 py-2 border-bottom mb-2">
                            <div class="fw-bold text-dark text-truncate"><?= htmlspecialchars($user['nama_lengkap'] ?? '') ?></div>
                            <div class="text-muted small text-truncate"><?= htmlspecialchars($user['email'] ?? '') ?></div>
                            <div class="badge bg-secondary-subtle text-secondary small mt-1">
                                <?= htmlspecialchars($user['nomor_induk'] ?? '') ?> (<?= ucfirst($user['jenis_pengguna'] ?? 'civitas') ?>)
                            </div>
                        </li>
                        <li>
                            <a class="dropdown-item rounded-2 small py-2" href="<?= base_url() ?>" target="_blank">
                                <i class="bi bi-globe me-2 text-primary"></i> Halaman Landing Depan
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item rounded-2 small py-2" href="<?= base_url('dashboard') ?>">
                                <i class="bi bi-speedometer2 me-2 text-primary"></i> Beranda Dashboard
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <button type="button" class="dropdown-item rounded-2 small py-2 text-danger" 
                                    data-bs-toggle="modal" data-bs-target="#modalLogout">
                                <i class="bi bi-box-arrow-right me-2"></i> Keluar (Logout)
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="p-3 p-lg-4 flex-grow-1">
        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="alert alert-success alert-dismissible fade show border-0 bg-success-subtle text-success small py-2 px-3 mb-4 rounded-3" role="alert">
                <i class="bi bi-check-circle-fill me-1"></i> <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?>
                <button type="button" class="btn-close small" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show border-0 bg-danger-subtle text-danger small py-2 px-3 mb-4 rounded-3" role="alert">
                <i class="bi bi-exclamation-circle-fill me-1"></i> <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
                <button type="button" class="btn-close small" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
