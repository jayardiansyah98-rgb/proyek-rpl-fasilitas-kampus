<?php
/**
 * LaporanController
 * Modul Tiket Pelaporan Kerusakan Fasilitas Kampus STMIK IKMI Cirebon
 * Mendukung RBAC (Pengguna Dosen/Mahasiswa, Petugas Sarpras, dan Admin)
 */

require_once ROOT_PATH . '/app/services/NotificationService.php';
require_once ROOT_PATH . '/app/services/WhatsAppService.php';
require_once ROOT_PATH . '/app/services/EmailService.php';
require_once ROOT_PATH . '/app/services/WhatsAppTemplates.php';
require_once ROOT_PATH . '/app/services/EmailTemplates.php';

class LaporanController extends Controller {
    protected $laporanModel;
    protected $ruanganModel;
    protected $fasilitasModel;
    protected $notifService;

    public function __construct() {
        Auth::requireLogin();
        $this->laporanModel  = $this->model('LaporanModel');
        $this->ruanganModel  = $this->model('RuanganModel');
        $this->fasilitasModel= $this->model('FasilitasModel');
        // NotificationService — dibuat lazy (hanya dibuat saat diperlukan)
        $this->notifService  = null;
    }

    /**
     * Lazy-init NotificationService agar tidak membebani setiap request
     */
    protected function getNotifService(): NotificationService {
        if ($this->notifService === null) {
            $db = new Database();
            $this->notifService = new NotificationService($db);
        }
        return $this->notifService;
    }

    /**
     * Menampilkan daftar tiket laporan kerusakan
     * Pengguna: Hanya melihat laporan miliknya sendiri
     * Petugas/Admin: Melihat seluruh laporan kampus
     */
    public function index() {
        $user = Auth::user();
        $role = Auth::role();

        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $status = isset($_GET['status']) && $_GET['status'] !== '' ? trim($_GET['status']) : null;
        $prioritas = isset($_GET['prioritas']) && $_GET['prioritas'] !== '' ? trim($_GET['prioritas']) : null;
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 10;

        // Data scoping
        $userId = ($role === 'pengguna') ? (int)$user['id'] : null;

        $totalRows = $this->laporanModel->countAll($userId, $status, $prioritas, $search);
        $totalPages = ($totalRows > 0) ? (int)ceil($totalRows / $perPage) : 1;
        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $laporanList = $this->laporanModel->getAll($userId, $status, $prioritas, $search, $page, $perPage);
        $summary = $this->laporanModel->getStatusSummary($userId);

        $data = [
            'title' => ($role === 'pengguna' ? 'Riwayat Laporan Saya' : 'Daftar Tiket Laporan Kerusakan') . ' - ' . APP_NAME,
            'user' => $user,
            'role' => $role,
            'laporan_list' => $laporanList,
            'summary' => $summary,
            'search' => $search,
            'status' => $status,
            'prioritas' => $prioritas,
            'page' => $page,
            'per_page' => $perPage,
            'total_rows' => $totalRows,
            'total_pages' => $totalPages
        ];

        $this->view('layouts/dashboard_header', $data);
        $this->view('layouts/sidebar', $data);
        $this->view('layouts/topbar', $data);
        $this->view('laporan/index', $data);
        $this->view('layouts/dashboard_footer', $data);
    }

    /**
     * Menampilkan form dan memproses pembuatan tiket laporan kerusakan baru
     */
    public function tambah() {
        $user = Auth::user();
        $role = Auth::role();

        $errors = [];
        $old = [
            'ruangan_id' => isset($_GET['ruangan_id']) ? (int)$_GET['ruangan_id'] : '',
            'fasilitas_id' => isset($_GET['fasilitas_id']) ? (int)$_GET['fasilitas_id'] : '',
            'prioritas' => 'sedang',
            'deskripsi' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!csrf_validate()) {
                $errors['general'] = 'Token keamanan (CSRF) tidak valid atau sesi formulir telah kadaluarsa. Silakan muat ulang halaman.';
            } else {
                $old['ruangan_id'] = trim($_POST['ruangan_id'] ?? '');
                $old['fasilitas_id'] = trim($_POST['fasilitas_id'] ?? '');
                $old['prioritas'] = trim($_POST['prioritas'] ?? 'sedang');
                $old['deskripsi'] = trim($_POST['deskripsi'] ?? '');

            // 1. Validasi Ruangan
            if (empty($old['ruangan_id']) || !is_numeric($old['ruangan_id'])) {
                $errors['ruangan_id'] = 'Ruangan lokasi kerusakan wajib dipilih.';
            }

            // 2. Validasi Fasilitas
            if (empty($old['fasilitas_id']) || !is_numeric($old['fasilitas_id'])) {
                $errors['fasilitas_id'] = 'Fasilitas yang mengalami kerusakan wajib dipilih.';
            }

            // 3. Validasi Prioritas
            $validPrioritas = ['rendah', 'sedang', 'tinggi', 'darurat'];
            if (!in_array($old['prioritas'], $validPrioritas, true)) {
                $errors['prioritas'] = 'Tingkat prioritas tidak valid.';
            }

            // 4. Validasi Deskripsi
            if (empty($old['deskripsi'])) {
                $errors['deskripsi'] = 'Deskripsi rincian kerusakan wajib diisi.';
            } elseif (mb_strlen($old['deskripsi']) < 10) {
                $errors['deskripsi'] = 'Deskripsi kerusakan minimal 10 karakter agar mudah dipahami teknisi.';
            } elseif (mb_strlen($old['deskripsi']) > 2000) {
                $errors['deskripsi'] = 'Deskripsi kerusakan maksimal 2000 karakter.';
            }

            // 5. Validasi Upload Foto Bukti Kerusakan
            $fotoFileName = null;
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
                $uploadResult = $this->handleFileUpload($_FILES['foto']);
                if ($uploadResult['success']) {
                    $fotoFileName = $uploadResult['filename'];
                } else {
                    $errors['foto'] = $uploadResult['message'];
                }
            }

            // 6. Eksekusi Simpan
            if (empty($errors)) {
                try {
                    $kodeLaporan = $this->laporanModel->generateKodeLaporan();

                    $saveData = [
                        'kode_laporan' => $kodeLaporan,
                        'user_id' => $user['id'],
                        'fasilitas_id' => (int)$old['fasilitas_id'],
                        'ruangan_id' => (int)$old['ruangan_id'],
                        'deskripsi' => $old['deskripsi'],
                        'foto' => $fotoFileName,
                        'prioritas' => $old['prioritas'],
                        'status' => 'menunggu',
                        'catatan_petugas' => null
                    ];

                    $newId = $this->laporanModel->create($saveData);

                    // ── Trigger Notifikasi ──────────────────────────────
                    // Notifikasi dikirim SETELAH laporan tersimpan.
                    // Kegagalan notifikasi TIDAK membatalkan laporan.
                    try {
                        $laporanData = $this->laporanModel->findById($newId);
                        if ($laporanData) {
                            $this->getNotifService()->onLaporanBaru($newId, $laporanData);
                        }
                    } catch (Exception $notifErr) {
                        // Abaikan — laporan sudah tersimpan, notifikasi bisa diretry
                        error_log('[Notifikasi] onLaporanBaru gagal: ' . $notifErr->getMessage());
                    }
                    // ────────────────────────────────────────────────────

                    $_SESSION['flash_success'] = 'Laporan kerusakan berhasil dikirim dengan kode tiket <strong>' . htmlspecialchars($kodeLaporan) . '</strong>. Petugas Sarpras akan segera menindaklanjuti.';
                    redirect('laporan/detail/' . $newId);
                    return;
                } catch (Exception $e) {
                    $errors['general'] = 'Gagal menyimpan laporan kerusakan: ' . $e->getMessage();
                }
            }
        }
    }

        $ruanganList = $this->laporanModel->getRuanganList();
        $fasilitasRuangan = [];
        if (!empty($old['ruangan_id'])) {
            $fasilitasRuangan = $this->laporanModel->getFasilitasByRuangan((int)$old['ruangan_id']);
        }

        $data = [
            'title' => 'Buat Laporan Kerusakan Baru - ' . APP_NAME,
            'user' => $user,
            'role' => $role,
            'ruangan_list' => $ruanganList,
            'fasilitas_ruangan' => $fasilitasRuangan,
            'errors' => $errors,
            'old' => $old
        ];

        $this->view('layouts/dashboard_header', $data);
        $this->view('layouts/sidebar', $data);
        $this->view('layouts/topbar', $data);
        $this->view('laporan/create', $data);
        $this->view('layouts/dashboard_footer', $data);
    }

    /**
     * Menampilkan detail lengkap tiket laporan kerusakan beserta visual timeline
     * Disertai verifikasi otorisasi kepemilikan untuk role pengguna
     */
    public function detail($id = null) {
        if (!$id) {
            $_SESSION['flash_error'] = 'ID tiket laporan tidak valid.';
            redirect('laporan');
            return;
        }

        $user = Auth::user();
        $role = Auth::role();

        // Otorisasi: Pengguna hanya dapat mengakses laporannya sendiri
        $userId = ($role === 'pengguna') ? (int)$user['id'] : null;
        $laporan = $this->laporanModel->findById($id, $userId);

        if (!$laporan) {
            // Cek apakah tiket sebenarnya ada tetapi milik pengguna lain
            if ($role === 'pengguna') {
                $existsForOther = $this->laporanModel->findById($id);
                if ($existsForOther) {
                    $_SESSION['flash_error'] = 'Akses ditolak: Anda hanya berwenang melihat laporan kerusakan yang Anda ajukan sendiri.';
                    redirect('laporan');
                    return;
                }
            }
            $_SESSION['flash_error'] = 'Tiket laporan kerusakan tidak ditemukan.';
            redirect('laporan');
            return;
        }

        $data = [
            'title' => 'Tiket #' . htmlspecialchars($laporan['kode_laporan']) . ' - ' . APP_NAME,
            'user' => $user,
            'role' => $role,
            'laporan' => $laporan
        ];

        $this->view('layouts/dashboard_header', $data);
        $this->view('layouts/sidebar', $data);
        $this->view('layouts/topbar', $data);
        $this->view('laporan/detail', $data);
        $this->view('layouts/dashboard_footer', $data);
    }

    /**
     * Memperbarui status, prioritas, dan catatan petugas (Khusus Petugas Sarpras dan Admin)
     */
    public function updateStatus($id = null) {
        Auth::requireRole(['admin', 'petugas_sarpras']);

        if (!$id || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('laporan');
            return;
        }

        if (!csrf_validate()) {
            $_SESSION['flash_error'] = 'Token keamanan (CSRF) tidak valid atau sesi telah kadaluarsa.';
            redirect('laporan/detail/' . $id);
            return;
        }

        $laporan = $this->laporanModel->findById($id);
        if (!$laporan) {
            $_SESSION['flash_error'] = 'Data laporan tidak ditemukan.';
            redirect('laporan');
            return;
        }

        $status = trim($_POST['status'] ?? '');
        $prioritas = trim($_POST['prioritas'] ?? '');
        $catatanPetugas = trim($_POST['catatan_petugas'] ?? '');

        $validStatus = ['menunggu', 'diverifikasi', 'diproses', 'selesai', 'ditolak'];
        $validPrioritas = ['rendah', 'sedang', 'tinggi', 'darurat'];

        if (!in_array($status, $validStatus, true)) {
            $_SESSION['flash_error'] = 'Status laporan yang dipilih tidak valid.';
            redirect('laporan/detail/' . $id);
            return;
        }

        if (!in_array($prioritas, $validPrioritas, true)) {
            $_SESSION['flash_error'] = 'Tingkat prioritas yang dipilih tidak valid.';
            redirect('laporan/detail/' . $id);
            return;
        }

        try {
            $this->laporanModel->updateStatus($id, $status, $prioritas, $catatanPetugas);

            // ── Trigger Notifikasi ──────────────────────────────
            // Dikirim SETELAH update DB berhasil.
            try {
                $laporanUpdated = $this->laporanModel->findById($id);
                if ($laporanUpdated) {
                    if ($status === 'selesai') {
                        $this->getNotifService()->onLaporanSelesai($id, $laporanUpdated, $catatanPetugas);
                    } else {
                        $this->getNotifService()->onStatusUpdate($id, $laporanUpdated, $status, $catatanPetugas);
                    }
                }
            } catch (Exception $notifErr) {
                error_log('[Notifikasi] onStatusUpdate gagal: ' . $notifErr->getMessage());
            }
            // ────────────────────────────────────────────────────

            $statusLabels = [
                'menunggu' => 'Menunggu',
                'diverifikasi' => 'Diverifikasi',
                'diproses' => 'Diproses',
                'selesai' => 'Selesai',
                'ditolak' => 'Ditolak'
            ];

            $_SESSION['flash_success'] = 'Status tiket <strong>' . htmlspecialchars($laporan['kode_laporan']) . '</strong> berhasil diperbarui menjadi <strong>' . ($statusLabels[$status] ?? ucfirst($status)) . '</strong>.';
            redirect('laporan/detail/' . $id);
        } catch (Exception $e) {
            $_SESSION['flash_error'] = 'Gagal memperbarui status laporan: ' . $e->getMessage();
            redirect('laporan/detail/' . $id);
        }
    }

    /**
     * Menghapus tiket laporan kerusakan (Khusus Admin)
     */
    public function hapus($id = null) {
        Auth::requireRole('admin');

        if (!csrf_validate()) {
            $_SESSION['flash_error'] = 'Token keamanan (CSRF) tidak valid.';
            redirect('laporan');
            return;
        }

        if (!$id) {
            $_SESSION['flash_error'] = 'ID laporan tidak valid.';
            redirect('laporan');
            return;
        }

        $laporan = $this->laporanModel->findById($id);
        if (!$laporan) {
            $_SESSION['flash_error'] = 'Data laporan tidak ditemukan.';
            redirect('laporan');
            return;
        }

        try {
            $this->laporanModel->delete($id);
            $_SESSION['flash_success'] = 'Tiket laporan <strong>' . htmlspecialchars($laporan['kode_laporan']) . '</strong> beserta lampirannya berhasil dihapus.';
            redirect('laporan');
        } catch (Exception $e) {
            $_SESSION['flash_error'] = 'Gagal menghapus laporan: ' . $e->getMessage();
            redirect('laporan/detail/' . $id);
        }
    }

    /**
     * Endpoint API JSON untuk mengambil daftar fasilitas berdasarkan ID ruangan (AJAX Chained Select)
     */
    public function ajaxFasilitas() {
        $ruanganId = isset($_GET['ruangan_id']) ? (int)$_GET['ruangan_id'] : 0;
        $fasilitas = [];

        if ($ruanganId > 0) {
            $fasilitas = $this->laporanModel->getFasilitasByRuangan($ruanganId);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($fasilitas);
        exit;
    }

    /**
     * Penanganan upload berkas foto bukti kerusakan secara aman
     * Memeriksa ukuran, ekstensi, MIME type biner, dan struktur berkas grafis
     */
    protected function handleFileUpload($file) {
        // 1. Batas ukuran 2MB
        $maxSize = 2 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'message' => 'Ukuran foto maksimal adalah 2 MB.'];
        }

        // 2. Validasi Ekstensi Berkas
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($ext, $allowedExts, true)) {
            return ['success' => false, 'message' => 'Format file tidak diizinkan. Hanya format JPG, JPEG, PNG, dan WEBP yang diperbolehkan.'];
        }

        // 3. Validasi MIME Type Biner via finfo
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($mime, $allowedMimes, true)) {
            return ['success' => false, 'message' => 'Tipe konten berkas tidak valid sebagai gambar (' . htmlspecialchars($mime) . ').'];
        }

        // 4. Validasi Struktur Gambar Asli
        $imageInfo = @getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            return ['success' => false, 'message' => 'Berkas yang diunggah bukan gambar asli yang valid.'];
        }

        // 5. Direktori & Penamaan Berkas Acak Aman
        $uploadDir = (defined('UPLOAD_PATH') ? UPLOAD_PATH : (__DIR__ . '/../../public/uploads')) . '/laporan/';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        $newFileName = 'lap_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $targetFilePath = $uploadDir . $newFileName;

        if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
            return ['success' => true, 'filename' => $newFileName];
        }

        return ['success' => false, 'message' => 'Gagal memindahkan berkas foto ke folder penyimpanan server.'];
    }
}
