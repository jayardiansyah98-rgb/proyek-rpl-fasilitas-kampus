    </main>

    <!-- Footer Status -->
    <footer class="bg-white border-top py-3 px-4 mt-auto small text-muted">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center">
            <div>
                &copy; <?= date('Y') ?> <strong>STMIK IKMI Cirebon</strong> &bull; Sistem Informasi Fasilitas Ruangan Kampus
            </div>
            <div class="mt-1 mt-sm-0">
                <span class="badge bg-light text-dark border">Role: <?= htmlspecialchars(Auth::role() ?? 'Guest') ?></span>
                <span class="badge bg-primary ms-1">v<?= APP_VERSION ?></span>
            </div>
        </div>
    </footer>
</div><!-- /.content-area -->
</div><!-- /.dashboard-wrapper -->

<!-- Modal Konfirmasi Logout -->
<div class="modal fade" id="modalLogout" tabindex="-1" aria-labelledby="modalLogoutLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
            <div class="modal-body text-center p-4">
                <div class="mb-3">
                    <span class="badge bg-danger bg-opacity-10 text-danger p-3 rounded-circle fs-3">
                        <i class="bi bi-box-arrow-right"></i>
                    </span>
                </div>
                <h5 class="fw-bold mb-2" id="modalLogoutLabel">Konfirmasi Keluar</h5>
                <p class="text-muted small mb-4">Apakah Anda yakin ingin mengakhiri sesi dan keluar dari sistem?</p>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-light px-3 rounded-pill small" data-bs-dismiss="modal">Batal</button>
                    <a href="<?= base_url('auth/logout') ?>" class="btn btn-danger px-4 rounded-pill small fw-semibold">
                        Ya, Keluar
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Notifikasi JS (Bell Polling) -->
<?php if (Auth::check()): ?>
<script src="<?= base_url('assets/js/notifikasi.js?v=' . (file_exists(ROOT_PATH . '/public/assets/js/notifikasi.js') ? filemtime(ROOT_PATH . '/public/assets/js/notifikasi.js') : '1.0')) ?>"></script>
<?php endif; ?>

<!-- Bootstrap 5.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<!-- Sidebar Toggle JS -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('mainSidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    const btnToggle = document.getElementById('btnSidebarToggle');
    const btnClose = document.getElementById('btnSidebarClose');

    function openSidebar() {
        sidebar.classList.add('show');
        backdrop.classList.add('show');
    }

    function closeSidebar() {
        sidebar.classList.remove('show');
        backdrop.classList.remove('show');
    }

    if (btnToggle) btnToggle.addEventListener('click', openSidebar);
    if (btnClose) btnClose.addEventListener('click', closeSidebar);
    if (backdrop) backdrop.addEventListener('click', closeSidebar);
});
</script>

<?php
/**
 * Chatbot Widget Asisten Fasilitas IKMI - Dashboard (Hanya pengguna yang sudah login)
 * Dimuat satu kali di dashboard_footer.php agar berlaku untuk semua role dashboard.
 */
if (Auth::check()):
?>
<?php require_once ROOT_PATH . '/app/views/home/chatbot_widget.php'; ?>
<!-- Chatbot Client Script Dashboard -->
<script src="<?= base_url('assets/js/chatbot.js?v=' . (file_exists(ROOT_PATH . '/public/assets/js/chatbot.js') ? filemtime(ROOT_PATH . '/public/assets/js/chatbot.js') : '2.1')) ?>"></script>
<?php endif; ?>
</body>
</html>
