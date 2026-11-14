<?php
/**
 * View: Pusat Notifikasi
 */

// Helper timeAgo untuk view
function notif_time_ago(string $datetime): string {
    $time = time() - strtotime($datetime);
    if ($time < 60)     return 'Baru saja';
    if ($time < 3600)   return floor($time / 60) . ' mnt lalu';
    if ($time < 86400)  return floor($time / 3600) . ' jam lalu';
    if ($time < 604800) return floor($time / 86400) . ' hari lalu';
    return date('d M Y', strtotime($datetime));
}
$tipeOptions = [
    ''            => 'Semua',
    'laporan'     => 'Laporan Kerusakan',
    'pemeliharaan'=> 'Pemeliharaan',
    'sistem'      => 'Sistem',
    'info'        => 'Info',
];
$baseUrl = base_url('notifikasi');
?>

<div class="container-fluid px-0">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="bi bi-bell-fill text-primary me-2"></i>Pusat Notifikasi
            </h4>
            <small class="text-muted">
                <?= number_format($total) ?> notifikasi
                <?php if ($unread > 0): ?>
                    — <span class="text-danger fw-semibold"><?= $unread ?> belum dibaca</span>
                <?php endif; ?>
            </small>
        </div>
        <?php if ($unread > 0): ?>
            <button type="button" id="btnMarkAllRead" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-check2-all me-1"></i>Tandai Semua Dibaca
            </button>
        <?php endif; ?>
    </div>

    <!-- Filter Tipe -->
    <div class="d-flex gap-2 flex-wrap mb-4">
        <?php foreach ($tipeOptions as $val => $label): ?>
            <a href="<?= $baseUrl ?>?tipe=<?= $val ?><?= ($val === ($tipe_filter ?? '')) ? '' : '' ?>"
               class="btn btn-sm <?= ($tipe_filter ?? '') === $val ? 'btn-primary' : 'btn-outline-secondary' ?> rounded-pill">
                <?= htmlspecialchars($label) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Daftar Notifikasi -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="list-group list-group-flush" id="notifList">
            <?php if (empty($notifs)): ?>
                <div class="list-group-item text-center py-5 text-muted">
                    <i class="bi bi-bell-slash fs-1 d-block mb-2 opacity-25"></i>
                    <div class="fw-semibold">Tidak ada notifikasi</div>
                    <?php if ($tipe_filter): ?>
                        <small><a href="<?= $baseUrl ?>">Lihat semua notifikasi</a></small>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <?php foreach ($notifs as $n): ?>
                    <?php
                    $isRead  = (int)$n['is_read'] === 1;
                    $tipeIcon = match($n['tipe']) {
                        'laporan'      => '<i class="bi bi-wrench-adjustable text-danger"></i>',
                        'pemeliharaan' => '<i class="bi bi-tools text-warning"></i>',
                        'sistem'       => '<i class="bi bi-gear text-secondary"></i>',
                        default        => '<i class="bi bi-info-circle text-primary"></i>',
                    };
                    $tautan  = !empty($n['tautan']) ? base_url($n['tautan']) : null;
                    ?>
                    <div class="list-group-item list-group-item-action d-flex gap-3 align-items-start py-3 px-4 notif-item <?= $isRead ? '' : 'bg-primary-subtle' ?>"
                         data-id="<?= (int)$n['id'] ?>" data-read="<?= $isRead ? '1' : '0' ?>">

                        <div class="notif-icon mt-1" style="min-width:36px; height:36px; display:flex; align-items:center; justify-content:center; background:#f0f4ff; border-radius:50%; font-size:1.1rem;">
                            <?= $tipeIcon ?>
                        </div>

                        <div class="flex-grow-1 overflow-hidden">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="fw-semibold <?= $isRead ? 'text-secondary' : 'text-dark' ?> text-truncate pe-3" style="max-width:70%;">
                                    <?= htmlspecialchars($n['judul']) ?>
                                </div>
                                <small class="text-muted text-nowrap">
                                    <?= notif_time_ago($n['created_at']) ?>
                                </small>
                            </div>
                            <p class="mb-1 text-muted small text-truncate-2"><?= htmlspecialchars($n['pesan']) ?></p>
                            <div class="d-flex gap-2 align-items-center mt-1">
                                <?php if ($tautan): ?>
                                    <a href="<?= htmlspecialchars($tautan) ?>" class="btn btn-xs btn-outline-primary py-0 px-2 small rounded-pill">
                                        <i class="bi bi-arrow-right-circle me-1"></i>Lihat Detail
                                    </a>
                                <?php endif; ?>
                                <?php if (!$isRead): ?>
                                    <button class="btn btn-xs btn-link text-muted p-0 small btn-mark-read" data-id="<?= (int)$n['id'] ?>">
                                        Tandai Dibaca
                                    </button>
                                <?php endif; ?>
                                <button class="btn btn-xs btn-link text-danger p-0 small btn-delete-notif ms-auto" data-id="<?= (int)$n['id'] ?>">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="card-footer bg-white border-top-0 py-3">
                <nav>
                    <ul class="pagination pagination-sm justify-content-center mb-0">
                        <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                            <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                                <a class="page-link" href="<?= $baseUrl ?>?tipe=<?= htmlspecialchars($tipe_filter ?? '') ?>&page=<?= $p ?>">
                                    <?= $p ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.text-truncate-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.btn-xs { font-size: 0.72rem; padding: 2px 8px; }
</style>

<script>
const CSRF_TOKEN = '<?= csrf_token() ?>';
const BASE_URL   = '<?= base_url() ?>/';

// Tandai satu sebagai dibaca
document.querySelectorAll('.btn-mark-read').forEach(btn => {
    btn.addEventListener('click', async function(e) {
        e.preventDefault();
        const id = this.dataset.id;
        const res = await fetch(BASE_URL + 'notifikasi/markRead/' + id, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'csrf_token=' + CSRF_TOKEN
        });
        const data = await res.json();
        if (data.success) {
            const item = document.querySelector(`.notif-item[data-id="${id}"]`);
            if (item) {
                item.classList.remove('bg-primary-subtle');
                this.remove();
            }
            updateBadge(data.unread);
        }
    });
});

// Tandai semua dibaca
document.getElementById('btnMarkAllRead')?.addEventListener('click', async function() {
    const res = await fetch(BASE_URL + 'notifikasi/markAllRead', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'csrf_token=' + CSRF_TOKEN
    });
    const data = await res.json();
    if (data.success) {
        document.querySelectorAll('.notif-item').forEach(el => el.classList.remove('bg-primary-subtle'));
        document.querySelectorAll('.btn-mark-read').forEach(el => el.remove());
        this.remove();
        updateBadge(0);
    }
});

// Hapus notifikasi
document.querySelectorAll('.btn-delete-notif').forEach(btn => {
    btn.addEventListener('click', async function() {
        if (!confirm('Hapus notifikasi ini?')) return;
        const id = this.dataset.id;
        const res = await fetch(BASE_URL + 'notifikasi/hapus/' + id, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'csrf_token=' + CSRF_TOKEN
        });
        const data = await res.json();
        if (data.success) {
            const item = document.querySelector(`.notif-item[data-id="${id}"]`);
            if (item) item.remove();
        }
    });
});

function updateBadge(count) {
    const badge = document.querySelector('#notif-badge');
    if (badge) {
        badge.textContent = count;
        badge.style.display = count > 0 ? 'inline-flex' : 'none';
    }
}
</script>