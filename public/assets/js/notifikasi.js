/**
 * notifikasi.js — Bell Notification Widget
 * Sistem Informasi Fasilitas STMIK IKMI Cirebon
 *
 * Polling AJAX untuk memperbarui jumlah notifikasi belum dibaca
 * dan menampilkan daftar notifikasi di dropdown bell topbar.
 */
(function () {
    'use strict';

    const BASE_URL    = document.querySelector('meta[name="base-url"]')?.content?.replace(/\/$/, '') || '';
    const POLL_INTERVAL = 30000; // 30 detik
    let   pollTimer   = null;

    // ─────────────────────────────────────────────────────────
    // Bell Dropdown — Load Items via AJAX
    // ─────────────────────────────────────────────────────────

    const bellBtn      = document.getElementById('notifBellBtn');
    const bellItems    = document.getElementById('bellItems');
    const bellLoading  = document.getElementById('bellLoading');
    const notifBadge   = document.getElementById('notif-badge');

    /**
     * Render ikon sesuai tipe notifikasi
     */
    function tipeIcon(tipe) {
        const map = {
            laporan:      '<i class="bi bi-wrench-adjustable text-danger"></i>',
            pemeliharaan: '<i class="bi bi-tools text-warning"></i>',
            sistem:       '<i class="bi bi-gear text-secondary"></i>',
            default:      '<i class="bi bi-info-circle text-primary"></i>',
        };
        return map[tipe] || map.default;
    }

    /**
     * Render daftar notifikasi di dropdown bell
     */
    function renderBellItems(items) {
        if (!bellItems) return;
        if (!items || items.length === 0) {
            bellItems.innerHTML = '<div class="text-center text-muted py-4 small"><i class="bi bi-bell-slash d-block fs-3 mb-1 opacity-25"></i>Tidak ada notifikasi</div>';
            return;
        }

        bellItems.innerHTML = items.map(n => {
            const isUnread = n.is_read === '0' || n.is_read === 0;
            const link     = n.tautan ? (BASE_URL + '/' + n.tautan) : '#';
            const icon     = tipeIcon(n.tipe);
            return `
            <a href="${link}" class="d-flex gap-3 align-items-start text-decoration-none px-3 py-2 border-bottom ${isUnread ? 'bg-primary-subtle' : ''} notif-bell-item"
               data-id="${n.id}" style="transition:background .15s;">
                <div class="pt-1" style="min-width:28px; font-size:.95rem;">${icon}</div>
                <div class="flex-grow-1 overflow-hidden">
                    <div class="small fw-semibold ${isUnread ? 'text-dark' : 'text-secondary'} text-truncate">${escHtml(n.judul)}</div>
                    <div class="text-muted" style="font-size:.75rem; display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">${escHtml(n.pesan)}</div>
                    <div class="text-muted" style="font-size:.7rem;">${escHtml(n.waktu_relatif || '')}</div>
                </div>
                ${isUnread ? '<span class="mt-2 ms-1 d-block bg-primary rounded-circle flex-shrink-0" style="width:8px;height:8px;"></span>' : ''}
            </a>`;
        }).join('');

        // Tandai otomatis saat diklik
        bellItems.querySelectorAll('.notif-bell-item').forEach(el => {
            el.addEventListener('click', function () {
                const id = this.dataset.id;
                const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
                if (this.classList.contains('bg-primary-subtle')) {
                    fetch(BASE_URL + '/notifikasi/markRead/' + id, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
                        body: 'csrf_token=' + encodeURIComponent(csrf)
                    }).then(r => r.json()).then(d => { if (d.unread !== undefined) updateBadge(d.unread); });
                }
            });
        });
    }

    /**
     * Polling: ambil data terbaru dari endpoint /notifikasi/poll
     */
    async function poll() {
        try {
            const res  = await fetch(BASE_URL + '/notifikasi/poll?ajax=1', { credentials: 'same-origin' });
            if (!res.ok) return;
            const data = await res.json();
            updateBadge(data.unread ?? 0);
            renderBellItems(data.items ?? []);
        } catch (e) {
            // Abaikan error jaringan
        }
    }

    /**
     * Update badge angka di bell icon
     */
    function updateBadge(count) {
        if (!notifBadge) return;
        count = parseInt(count, 10) || 0;
        notifBadge.textContent = count > 99 ? '99+' : (count || '');
        notifBadge.style.display = count > 0 ? 'inline-flex' : 'none';
        notifBadge.style.alignItems = 'center';
        notifBadge.style.justifyContent = 'center';
    }

    function escHtml(str) {
        return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // ─────────────────────────────────────────────────────────
    // Init
    // ─────────────────────────────────────────────────────────

    // Load saat dropdown dibuka
    bellBtn?.addEventListener('click', function () {
        if (bellLoading) bellLoading.style.display = 'block';
        poll();
    });

    // Tandai semua via bell
    document.getElementById('bellMarkAll')?.addEventListener('click', async function (e) {
        e.preventDefault();
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const res  = await fetch(BASE_URL + '/notifikasi/markAllRead', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
            body: 'csrf_token=' + encodeURIComponent(csrf)
        });
        const data = await res.json();
        if (data.success) {
            updateBadge(0);
            bellItems?.querySelectorAll('.bg-primary-subtle').forEach(el => el.classList.remove('bg-primary-subtle'));
            bellItems?.querySelectorAll('.rounded-circle.bg-primary').forEach(el => el.remove());
        }
    });

    // Polling otomatis setiap 30 detik
    if (bellBtn) {
        poll(); // load pertama
        pollTimer = setInterval(poll, POLL_INTERVAL);
    }

    // Expose updateBadge globally (untuk view notifikasi/index.php)
    window.updateBadge = updateBadge;
})();

