<?php
/**
 * View: Pengaturan Notifikasi (Admin Only)
 */
$settingsMap = [];
foreach ($settings as $s) {
    $settingsMap[$s['setting_key']] = $s;
}

function setting_val(array $map, string $key, mixed $default = ''): mixed {
    return $map[$key]['setting_value'] ?? $default;
}
function is_setting_on(array $map, string $key): bool {
    return in_array($map[$key]['setting_value'] ?? '0', ['1', 'true'], true);
}
?>

<div class="container-fluid px-0">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark"><i class="bi bi-gear-fill text-primary me-2"></i>Pengaturan Notifikasi</h4>
            <small class="text-muted">Konfigurasi WhatsApp (Fonnte), Email (SMTP), dan pengaturan notifikasi sistem.</small>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('notifikasi') ?>" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-bell me-1"></i>Pusat Notifikasi
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- ── KOLOM KIRI: Konfigurasi & Pengaturan ── -->
        <div class="col-lg-7">

            <!-- Status Integrasi -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4 fw-semibold text-dark">
                    <i class="bi bi-shield-check text-success me-2"></i>Status Integrasi WhatsApp
                </div>
                <div class="card-body px-4 py-3">
                    <div class="d-flex align-items-center gap-3 p-3 rounded-3 border <?= $wa_configured ? 'border-success-subtle bg-success-subtle' : 'border-warning-subtle bg-warning-subtle' ?>">
                        <i class="bi bi-whatsapp fs-3 <?= $wa_configured ? 'text-success' : 'text-warning' ?>"></i>
                        <div class="flex-grow-1">
                            <div class="fw-semibold">WhatsApp Gateway (Fonnte API)</div>
                            <div class="small <?= $wa_configured ? 'text-success' : 'text-warning' ?>">
                                <?= $wa_configured ? '✓ Token Fonnte terpasang dan siap digunakan' : '⚠ Token Fonnte belum diisi di file .env' ?>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-success" id="btnTestFonnte" <?= !$wa_configured ? 'disabled' : '' ?>>
                            <i class="bi bi-wifi me-1"></i>Uji Koneksi Fonnte
                        </button>
                    </div>

                    <div id="connectionTestResult" class="mt-2 small d-none"></div>

                    <div class="mt-3 p-3 bg-light rounded-3 small text-muted">
                        <strong>Petunjuk Token:</strong>
                        Masukkan API Token Fonnte pada file <code>.env</code> di baris <code>FONNTE_TOKEN=...</code>.<br>
                        Token tidak pernah ditampilkan di halaman ini demi keamanan akun Anda.
                    </div>
                </div>
            </div>

            <!-- Form Pengaturan -->
            <form method="POST" action="<?= base_url('notifikasi/savePengaturan') ?>" id="formSettings">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-header bg-white border-bottom py-3 px-4 fw-semibold">
                        <i class="bi bi-toggles2 me-2"></i>Aktifkan Kanal Notifikasi
                    </div>
                    <div class="card-body px-4 py-3">
                        <div class="d-flex flex-column gap-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="notif_enabled" name="notif_enabled" value="1"
                                    <?= is_setting_on($settingsMap, 'notif_enabled') ? 'checked' : '' ?>>
                                <label class="form-check-label" for="notif_enabled">
                                    <strong>Aktifkan Sistem Notifikasi</strong>
                                    <small class="d-block text-muted">Master switch — kendali utama seluruh notifikasi sistem.</small>
                                </label>
                            </div>
                            <hr class="my-1">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="whatsapp_enabled" name="whatsapp_enabled" value="1"
                                    <?= is_setting_on($settingsMap, 'whatsapp_enabled') ? 'checked' : '' ?> <?= !$wa_configured ? 'disabled' : '' ?>>
                                <label class="form-check-label" for="whatsapp_enabled">
                                    <i class="bi bi-whatsapp text-success me-1"></i><strong>Notifikasi WhatsApp Otomatis</strong>
                                    <?php if (!$wa_configured): ?>
                                        <span class="badge bg-warning text-dark ms-2 small">Token belum diisi</span>
                                    <?php endif; ?>
                                    <small class="d-block text-muted">Kirim pesan WhatsApp otomatis ke pelapor dan petugas saat ada pembaruan tiket fasilitas.</small>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-header bg-white border-bottom py-3 px-4 fw-semibold">
                        <i class="bi bi-calendar-event me-2"></i>Pemicu Notifikasi
                    </div>
                    <div class="card-body px-4 py-3">
                        <?php
                        $events = [
                            'notif_laporan_baru'   => ['Laporan Kerusakan Baru', 'Notifikasi saat laporan baru dibuat.'],
                            'notif_status_update'  => ['Perubahan Status Laporan', 'Notifikasi saat status laporan berubah.'],
                            'notif_selesai'        => ['Laporan Selesai', 'Notifikasi khusus saat laporan selesai ditangani.'],
                            'notif_ditolak'        => ['Laporan Ditolak', 'Notifikasi saat laporan ditolak petugas.'],
                        ];
                        ?>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($events as $key => [$label, $desc]): ?>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                           id="<?= $key ?>" name="<?= $key ?>" value="1"
                                        <?= is_setting_on($settingsMap, $key) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="<?= $key ?>">
                                        <strong><?= htmlspecialchars($label) ?></strong>
                                        <small class="d-block text-muted"><?= htmlspecialchars($desc) ?></small>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-header bg-white border-bottom py-3 px-4 fw-semibold">
                        <i class="bi bi-people me-2"></i>Nomor WhatsApp Tambahan (Opsional)
                    </div>
                    <div class="card-body px-4">
                        <?php
                        $rawWa = setting_val($settingsMap, 'wa_notif_tambahan', '[]');
                        $arrWa = json_decode($rawWa, true) ?: [];
                        $waText = implode("\n", $arrWa);
                        ?>
                        <div class="mb-1">
                            <label class="form-label small fw-semibold">Daftar Nomor WhatsApp Tambahan</label>
                            <textarea class="form-control form-control-sm" name="wa_notif_tambahan" rows="3"
                                      placeholder="Contoh: 6281234567890 (satu nomor per baris)"><?= htmlspecialchars($waText) ?></textarea>
                            <div class="form-text">Nomor darurat di luar akun pengguna yang juga akan menerima salinan notifikasi laporan baru via WhatsApp.</div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-header bg-white border-bottom py-3 px-4 fw-semibold">
                        <i class="bi bi-arrow-repeat me-2"></i>Pengaturan Retry
                    </div>
                    <div class="card-body px-4">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label small fw-semibold">Maks Percobaan Ulang</label>
                                <input type="number" class="form-control form-control-sm" name="notif_max_retry"
                                       value="<?= (int)(setting_val($settingsMap, 'notif_max_retry', 3)) ?>"
                                       min="1" max="10">
                                <div class="form-text">Pengiriman gagal akan dicoba ulang maksimal N kali.</div>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label small fw-semibold">Jeda Antar Retry (menit)</label>
                                <input type="number" class="form-control form-control-sm" name="notif_retry_delay_min"
                                       value="<?= (int)(setting_val($settingsMap, 'notif_retry_delay_min', 5)) ?>"
                                       min="1" max="60">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-1"></i>Simpan Pengaturan
                    </button>
                    <button type="submit" form="formRetryProcess" class="btn btn-outline-warning">
                        <i class="bi bi-arrow-repeat me-1"></i>Proses Retry Gagal
                    </button>
                </div>
            </form>

            <form id="formRetryProcess" method="POST" action="<?= base_url('notifikasi/retry') ?>" class="d-none">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            </form>
        </div>

        <!-- ── KOLOM KANAN: Test Kirim & Log ── -->
        <div class="col-lg-5">

            <!-- Test Kirim WhatsApp -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4 fw-semibold">
                    <i class="bi bi-whatsapp text-success me-2"></i>Kirim WhatsApp Uji
                </div>
                <div class="card-body px-4 py-3">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nomor WhatsApp Tujuan</label>
                        <input type="text" class="form-control form-control-sm" id="testWaNumber" placeholder="628xx..." required>
                        <div class="form-text">Format internasional (contoh: 6281234567890 atau 081234567890)</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Pesan Uji</label>
                        <textarea class="form-control form-control-sm" id="testWaMsg" rows="3">Ini adalah pesan uji coba dari Sistem Informasi Fasilitas STMIK IKMI Cirebon. Notifikasi WhatsApp berfungsi dengan baik.</textarea>
                    </div>
                    <button type="button" class="btn btn-sm btn-success w-100" id="btnSendWaTest" <?= !$wa_configured ? 'disabled title="Token Fonnte belum dikonfigurasi"' : '' ?>>
                        <i class="bi bi-send me-1"></i>Kirim Uji WhatsApp
                    </button>
                    <div id="waTestResult" class="mt-2 small d-none"></div>
                </div>
            </div>

            <!-- Statistik Log -->
            <?php if (!empty($log_stats)): ?>
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4 fw-semibold">
                    <i class="bi bi-bar-chart-fill me-2 text-info"></i>Statistik 30 Hari Terakhir
                </div>
                <div class="card-body px-4 py-3">
                    <?php foreach ($log_stats as $stat): ?>
                        <?php $kanIcon = $stat['kanal'] === 'whatsapp' ? '📱' : '📧'; ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small fw-semibold mb-1">
                                <span><?= $kanIcon ?> <?= ucfirst($stat['kanal']) ?></span>
                                <span class="text-muted"><?= $stat['total'] ?> total</span>
                            </div>
                            <div class="d-flex gap-2 small">
                                <span class="badge bg-success-subtle text-success">✓ <?= $stat['sent'] ?> terkirim</span>
                                <span class="badge bg-danger-subtle text-danger">✗ <?= $stat['failed'] ?> gagal</span>
                                <span class="badge bg-secondary-subtle text-secondary">⏳ <?= $stat['pending'] ?> pending</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Riwayat Log Pengiriman -->
    <div class="card border-0 shadow-sm rounded-3 mt-2">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
            <span class="fw-semibold"><i class="bi bi-clock-history me-2"></i>Riwayat Pengiriman</span>
            <div class="d-flex gap-2">
                <select class="form-select form-select-sm" id="filterKanal" style="width:130px;">
                    <option value="">Semua Kanal</option>
                    <option value="whatsapp" <?= ($kanal_filter ?? '') === 'whatsapp' ? 'selected' : '' ?>>WhatsApp</option>
                    <option value="email" <?= ($kanal_filter ?? '') === 'email' ? 'selected' : '' ?>>Email</option>
                </select>
                <select class="form-select form-select-sm" id="filterStatus" style="width:130px;">
                    <option value="">Semua Status</option>
                    <option value="sent"    <?= ($status_filter ?? '') === 'sent'    ? 'selected' : '' ?>>Terkirim</option>
                    <option value="failed"  <?= ($status_filter ?? '') === 'failed'  ? 'selected' : '' ?>>Gagal</option>
                    <option value="pending" <?= ($status_filter ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th class="px-4">Waktu</th>
                        <th>Kanal</th>
                        <th>Event</th>
                        <th>Penerima</th>
                        <th>Tiket</th>
                        <th>Status</th>
                        <th>Percobaan</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr><td colspan="8" class="text-center text-muted py-4">Belum ada riwayat pengiriman.</td></tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <?php
                            $statusBadge = match($log['status']) {
                                'sent'       => '<span class="badge bg-success-subtle text-success">Terkirim</span>',
                                'failed'     => '<span class="badge bg-danger-subtle text-danger">Gagal</span>',
                                'processing' => '<span class="badge bg-info-subtle text-info">Diproses</span>',
                                default      => '<span class="badge bg-secondary-subtle text-secondary">Pending</span>',
                            };
                            $kanBadge = $log['kanal'] === 'whatsapp'
                                ? '<span class="badge bg-success">📱 WA</span>'
                                : '<span class="badge bg-primary">📧 Email</span>';
                            ?>
                            <tr>
                                <td class="px-4 text-muted text-nowrap"><?= date('d/m H:i', strtotime($log['created_at'])) ?></td>
                                <td><?= $kanBadge ?></td>
                                <td class="text-truncate" style="max-width:140px;"><?= htmlspecialchars($log['event_type']) ?></td>
                                <td class="text-truncate" style="max-width:150px;"><?= htmlspecialchars($log['penerima']) ?></td>
                                <td><?= $log['kode_laporan'] ? '<a href="' . base_url('laporan/detail/' . $log['laporan_id']) . '" class="small">' . htmlspecialchars($log['kode_laporan']) . '</a>' : '-' ?></td>
                                <td><?= $statusBadge ?></td>
                                <td class="text-center"><?= (int)$log['percobaan'] ?>/<?= (int)$log['max_percobaan'] ?></td>
                                <td class="text-truncate text-danger" style="max-width:160px;" title="<?= htmlspecialchars($log['keterangan_gagal'] ?? '') ?>">
                                    <?= htmlspecialchars(mb_substr($log['keterangan_gagal'] ?? '', 0, 50)) ?: '<span class="text-muted">-</span>' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($log_pages > 1): ?>
            <div class="card-footer bg-white py-3">
                <nav><ul class="pagination pagination-sm justify-content-center mb-0">
                    <?php for ($p = 1; $p <= $log_pages; $p++): ?>
                        <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                            <a class="page-link" href="<?= base_url('notifikasi/pengaturan') ?>?page=<?= $p ?>&kanal=<?= htmlspecialchars($kanal_filter ?? '') ?>&status=<?= htmlspecialchars($status_filter ?? '') ?>"><?= $p ?></a>
                        </li>
                    <?php endfor; ?>
                </ul></nav>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
const CSRF = '<?= csrf_token() ?>';
const BASE = '<?= base_url() ?>/';

// Filter log dengan redirect
document.getElementById('filterKanal').addEventListener('change', applyFilter);
document.getElementById('filterStatus').addEventListener('change', applyFilter);
function applyFilter() {
    const k = document.getElementById('filterKanal').value;
    const s = document.getElementById('filterStatus').value;
    window.location.href = BASE + 'notifikasi/pengaturan?kanal=' + k + '&status=' + s;
}

// Helper untuk tampilkan hasil
function showResult(el, success, msg) {
    el.className = 'mt-2 small alert py-2 ' + (success ? 'alert-success' : 'alert-danger');
    el.textContent = msg;
    el.classList.remove('d-none');
}

// Uji koneksi Fonnte
document.getElementById('btnTestFonnte')?.addEventListener('click', async function() {
    this.disabled = true; this.textContent = 'Menguji...';
    const resultEl = document.getElementById('connectionTestResult');
    try {
        const res  = await fetch(BASE + 'notifikasi/testFonnteConnection', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'csrf_token=' + CSRF
        });
        const data = await res.json();
        showResult(resultEl, data.success, '📱 Fonnte: ' + (data.message || (data.success ? 'OK' : 'Gagal')));
    } catch (e) {
        showResult(resultEl, false, '⚠️ Gagal menghubungi server: ' + e.message);
    } finally {
        this.disabled = false; this.innerHTML = '<i class="bi bi-wifi me-1"></i>Uji Koneksi Fonnte';
    }
});

// Kirim WA Uji
document.getElementById('btnSendWaTest')?.addEventListener('click', async function() {
    const nomor = document.getElementById('testWaNumber').value.trim();
    const pesan = document.getElementById('testWaMsg').value.trim();
    if (!nomor) { alert('Masukkan nomor WhatsApp tujuan.'); return; }
    const resultEl = document.getElementById('waTestResult');
    this.disabled = true; this.textContent = 'Mengirim...';
    const fd = new FormData();
    fd.append('csrf_token', CSRF);
    fd.append('nomor', nomor);
    fd.append('pesan', pesan);
    try {
        const controller = new AbortController();
        const timeoutId  = setTimeout(() => controller.abort(), 12000); // max 12 detik
        const res  = await fetch(BASE + 'notifikasi/testWhatsapp', {
            method: 'POST',
            body: fd,
            signal: controller.signal
        });
        clearTimeout(timeoutId);
        const text = await res.text();
        let data;
        try { data = JSON.parse(text); }
        catch { data = { success: false, message: 'Respons server tidak valid: ' + text.substring(0, 100) }; }
        showResult(resultEl, data.success, data.message || (data.success ? '✅ Pesan berhasil dikirim!' : '❌ Gagal mengirim'));
    } catch (e) {
        const msg = e.name === 'AbortError'
            ? '⏱️ Timeout — server terlalu lama merespons. Cek apakah WA sudah masuk.'
            : '⚠️ Error jaringan: ' + e.message;
        showResult(resultEl, false, msg);
    } finally {
        this.disabled = false; this.innerHTML = '<i class="bi bi-send me-1"></i>Kirim Uji WhatsApp';
    }
});
</script>

