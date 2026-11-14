<?php
/**
 * FasilitasController
 * Modul Utama Manajemen Fasilitas & Inventaris Ruangan STMIK IKMI Cirebon
 * Mendukung Full CRUD (Admin), Tambah/Edit/Update Kondisi (Petugas Sarpras), Read-Only (Civitas),
 * Filter Multi-Dimensi, Upload Berkas Gambar Aman, dan Validasi Relasi Database.
 */

class FasilitasController extends Controller {
    protected $fasilitasModel;
    protected $kategoriModel;
    protected $gedungModel;
    protected $ruanganModel;

    public function __construct() {
        // Halaman fasilitas dapat diakses seluruh civitas terautentikasi
        Auth::requireLogin();
        $this->fasilitasModel = $this->model('FasilitasModel');
        $this->kategoriModel = $this->model('KategoriModel');
        $this->gedungModel = $this->model('GedungModel');
        $this->ruanganModel = $this->model('RuanganModel');
    }

    /**
     * Daftar fasilitas dengan pencarian, filter gedung, ruangan, kategori, kondisi, dan pagination
     */
    public function index() {
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $gedungId = isset($_GET['gedung_id']) && $_GET['gedung_id'] !== '' ? (int)$_GET['gedung_id'] : null;
        $ruanganId = isset($_GET['ruangan_id']) && $_GET['ruangan_id'] !== '' ? (int)$_GET['ruangan_id'] : null;
        $kategoriId = isset($_GET['kategori_id']) && $_GET['kategori_id'] !== '' ? (int)$_GET['kategori_id'] : null;
        $kondisi = isset($_GET['kondisi']) && $_GET['kondisi'] !== '' ? trim($_GET['kondisi']) : null;
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 10;

        $totalRows = $this->fasilitasModel->countAll($search, $gedungId, $ruanganId, $kategoriId, $kondisi);
        $totalPages = ($totalRows > 0) ? (int)ceil($totalRows / $perPage) : 1;
        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $fasilitasList = $this->fasilitasModel->getAll($search, $gedungId, $ruanganId, $kategoriId, $kondisi, $page, $perPage);
        $kategoriList = $this->kategoriModel->getAll();
        $gedungList = $this->gedungModel->getAllActive();
        $ruanganList = $this->fasilitasModel->getRuanganByGedung($gedungId);

        $data = [
            'title' => 'Manajemen Fasilitas Ruangan - ' . APP_NAME,
            'user' => Auth::user(),
            'role' => Auth::role(),
            'fasilitas_list' => $fasilitasList,
            'kategori_list' => $kategoriList,
            'gedung_list' => $gedungList,
            'ruangan_list' => $ruanganList,
            'search' => $search,
            'gedung_id' => $gedungId,
            'ruangan_id' => $ruanganId,
            'kategori_id' => $kategoriId,
            'kondisi' => $kondisi,
            'page' => $page,
            'per_page' => $perPage,
            'total_rows' => $totalRows,
            'total_pages' => $totalPages
        ];

        $this->view('layouts/dashboard_header', $data);
        $this->view('layouts/sidebar', $data);
        $this->view('layouts/topbar', $data);
        $this->view('fasilitas/index', $data);
        $this->view('layouts/dashboard_footer', $data);
    }

    /**
     * Menampilkan informasi detail fasilitas beserta riwayat pemeliharaan dan laporan
     */
    public function detail($id = null) {
        if (!$id) {
            $_SESSION['flash_error'] = 'ID Fasilitas tidak valid.';
            redirect('fasilitas');
        }

        $fasilitas = $this->fasilitasModel->findById($id);
        if (!$fasilitas) {
            $_SESSION['flash_error'] = 'Data fasilitas tidak ditemukan.';
            redirect('fasilitas');
        }

        $onlyPublic = Auth::hasRole('pengguna');
        $riwayatPemeliharaan = $this->fasilitasModel->getRiwayatPemeliharaan($id, $onlyPublic);
        $riwayatLaporan = $this->fasilitasModel->getRiwayatLaporan($id);
        $stats = $this->fasilitasModel->getStatistikFasilitas($id, $onlyPublic);

        $data = [
            'title' => 'Detail ' . htmlspecialchars($fasilitas['nama_fasilitas']) . ' (' . htmlspecialchars($fasilitas['kode_inventaris']) . ') - ' . APP_NAME,
            'user' => Auth::user(),
            'role' => Auth::role(),
            'fasilitas' => $fasilitas,
            'riwayat_pemeliharaan' => $riwayatPemeliharaan,
            'riwayat_laporan' => $riwayatLaporan,
            'stats' => $stats
        ];

        $this->view('layouts/dashboard_header', $data);
        $this->view('layouts/sidebar', $data);
        $this->view('layouts/topbar', $data);
        $this->view('fasilitas/detail', $data);
        $this->view('layouts/dashboard_footer', $data);
    }

    /**
     * Menambah fasilitas baru (Khusus Admin dan Petugas Sarpras)
     */
    public function tambah() {
        Auth::requireRole(['admin', 'petugas_sarpras']);

        $errors = [];
        $old = [
            'kode_inventaris' => '',
            'nama_fasilitas' => '',
            'kategori_id' => '',
            'gedung_id' => '',
            'ruangan_id' => '',
            'jumlah' => 1,
            'kondisi' => 'baik',
            'tahun_pengadaan' => date('Y'),
            'keterangan' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!csrf_validate()) {
                $errors['general'] = 'Sesi formulir telah kedaluwarsa atau token tidak valid (CSRF). Silakan muat ulang halaman.';
            } else {
                $old['kode_inventaris'] = strtoupper(trim($_POST['kode_inventaris'] ?? ''));
            $old['nama_fasilitas'] = trim($_POST['nama_fasilitas'] ?? '');
            $old['kategori_id'] = trim($_POST['kategori_id'] ?? '');
            $old['gedung_id'] = trim($_POST['gedung_id'] ?? '');
            $old['ruangan_id'] = trim($_POST['ruangan_id'] ?? '');
            $old['jumlah'] = max(1, (int)($_POST['jumlah'] ?? 1));
            $old['kondisi'] = trim($_POST['kondisi'] ?? 'baik');
            $old['tahun_pengadaan'] = (int)($_POST['tahun_pengadaan'] ?? date('Y'));
            $old['keterangan'] = trim($_POST['keterangan'] ?? '');

            // 1. Validasi Kode Inventaris
            if (empty($old['kode_inventaris'])) {
                $errors['kode_inventaris'] = 'Kode inventaris wajib diisi.';
            } elseif (strlen($old['kode_inventaris']) > 50) {
                $errors['kode_inventaris'] = 'Kode inventaris maksimal 50 karakter.';
            } elseif ($this->fasilitasModel->findByKode($old['kode_inventaris'])) {
                $errors['kode_inventaris'] = 'Kode inventaris "' . htmlspecialchars($old['kode_inventaris']) . '" sudah terdaftar.';
            }

            // 2. Validasi Nama Fasilitas
            if (empty($old['nama_fasilitas'])) {
                $errors['nama_fasilitas'] = 'Nama fasilitas wajib diisi.';
            } elseif (strlen($old['nama_fasilitas']) > 150) {
                $errors['nama_fasilitas'] = 'Nama fasilitas maksimal 150 karakter.';
            }

            // 3. Validasi Kategori
            if (empty($old['kategori_id']) || !$this->kategoriModel->findById($old['kategori_id'])) {
                $errors['kategori_id'] = 'Pilih kategori fasilitas yang valid.';
            }

            // 4. Validasi Ruangan
            if (empty($old['ruangan_id']) || !$this->ruanganModel->findById($old['ruangan_id'])) {
                $errors['ruangan_id'] = 'Pilih ruangan penempatan fasilitas yang valid.';
            }

            // 5. Validasi Kondisi
            $validKondisi = ['baik', 'rusak_ringan', 'rusak_berat', 'tidak_layak', 'hilang'];
            if (!in_array($old['kondisi'], $validKondisi)) {
                $errors['kondisi'] = 'Status kondisi tidak valid.';
            }

            // 6. Validasi Tahun Pengadaan
            $currentYear = (int)date('Y');
            if ($old['tahun_pengadaan'] < 1990 || $old['tahun_pengadaan'] > ($currentYear + 1)) {
                $errors['tahun_pengadaan'] = 'Tahun pengadaan harus di antara 1990 dan ' . ($currentYear + 1) . '.';
            }

            // 7. Penanganan Upload Foto Aman
            $fotoFileName = null;
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
                $uploadResult = $this->handleFileUpload($_FILES['foto']);
                if ($uploadResult['success']) {
                    $fotoFileName = $uploadResult['filename'];
                } else {
                    $errors['foto'] = $uploadResult['message'];
                }
            }

            if (empty($errors)) {
                try {
                    $saveData = $old;
                    $saveData['foto'] = $fotoFileName;
                    $insertId = $this->fasilitasModel->create($saveData);
                    $_SESSION['flash_success'] = 'Fasilitas ' . htmlspecialchars($old['nama_fasilitas']) . ' (' . htmlspecialchars($old['kode_inventaris']) . ') berhasil ditambahkan.';
                    redirect('fasilitas');
                } catch (Exception $e) {
                    $errors['general'] = 'Gagal menyimpan fasilitas: ' . $e->getMessage();
                }
            }
            }
        }

        $kategoriList = $this->kategoriModel->getAll();
        $gedungList = $this->gedungModel->getAllActive();
        $ruanganList = $this->fasilitasModel->getRuanganByGedung(!empty($old['gedung_id']) ? (int)$old['gedung_id'] : null);

        $data = [
            'title' => 'Tambah Fasilitas Baru - ' . APP_NAME,
            'user' => Auth::user(),
            'role' => Auth::role(),
            'kategori_list' => $kategoriList,
            'gedung_list' => $gedungList,
            'ruangan_list' => $ruanganList,
            'errors' => $errors,
            'old' => $old
        ];

        $this->view('layouts/dashboard_header', $data);
        $this->view('layouts/sidebar', $data);
        $this->view('layouts/topbar', $data);
        $this->view('fasilitas/create', $data);
        $this->view('layouts/dashboard_footer', $data);
    }

    /**
     * Mengubah fasilitas (Khusus Admin dan Petugas Sarpras)
     */
    public function edit($id = null) {
        Auth::requireRole(['admin', 'petugas_sarpras']);

        if (!$id) {
            $_SESSION['flash_error'] = 'ID Fasilitas tidak valid.';
            redirect('fasilitas');
        }

        $fasilitas = $this->fasilitasModel->findById($id);
        if (!$fasilitas) {
            $_SESSION['flash_error'] = 'Data fasilitas tidak ditemukan.';
            redirect('fasilitas');
        }

        $errors = [];
        $old = [
            'kode_inventaris' => $fasilitas['kode_inventaris'],
            'nama_fasilitas' => $fasilitas['nama_fasilitas'],
            'kategori_id' => $fasilitas['kategori_id'],
            'gedung_id' => $fasilitas['gedung_id'],
            'ruangan_id' => $fasilitas['ruangan_id'],
            'jumlah' => $fasilitas['jumlah'],
            'kondisi' => $fasilitas['kondisi'],
            'tahun_pengadaan' => $fasilitas['tahun_pengadaan'],
            'keterangan' => $fasilitas['keterangan'] ?? '',
            'current_foto' => $fasilitas['foto']
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!csrf_validate()) {
                $errors['general'] = 'Sesi formulir telah kedaluwarsa atau token tidak valid (CSRF). Silakan muat ulang halaman.';
            } else {
                $old['kode_inventaris'] = strtoupper(trim($_POST['kode_inventaris'] ?? ''));
            $old['nama_fasilitas'] = trim($_POST['nama_fasilitas'] ?? '');
            $old['kategori_id'] = trim($_POST['kategori_id'] ?? '');
            $old['gedung_id'] = trim($_POST['gedung_id'] ?? '');
            $old['ruangan_id'] = trim($_POST['ruangan_id'] ?? '');
            $old['jumlah'] = max(1, (int)($_POST['jumlah'] ?? 1));
            $old['kondisi'] = trim($_POST['kondisi'] ?? 'baik');
            $old['tahun_pengadaan'] = (int)($_POST['tahun_pengadaan'] ?? date('Y'));
            $old['keterangan'] = trim($_POST['keterangan'] ?? '');

            // Validasi Kode
            if (empty($old['kode_inventaris'])) {
                $errors['kode_inventaris'] = 'Kode inventaris wajib diisi.';
            } elseif (strlen($old['kode_inventaris']) > 50) {
                $errors['kode_inventaris'] = 'Kode inventaris maksimal 50 karakter.';
            } elseif ($this->fasilitasModel->findByKode($old['kode_inventaris'], $id)) {
                $errors['kode_inventaris'] = 'Kode inventaris "' . htmlspecialchars($old['kode_inventaris']) . '" sudah digunakan fasilitas lain.';
            }

            // Validasi Nama
            if (empty($old['nama_fasilitas'])) {
                $errors['nama_fasilitas'] = 'Nama fasilitas wajib diisi.';
            } elseif (strlen($old['nama_fasilitas']) > 150) {
                $errors['nama_fasilitas'] = 'Nama fasilitas maksimal 150 karakter.';
            }

            // Validasi Kategori & Ruangan
            if (empty($old['kategori_id']) || !$this->kategoriModel->findById($old['kategori_id'])) {
                $errors['kategori_id'] = 'Pilih kategori fasilitas yang valid.';
            }

            if (empty($old['ruangan_id']) || !$this->ruanganModel->findById($old['ruangan_id'])) {
                $errors['ruangan_id'] = 'Pilih ruangan penempatan fasilitas yang valid.';
            }

            // Validasi Kondisi
            $validKondisi = ['baik', 'rusak_ringan', 'rusak_berat', 'tidak_layak', 'hilang'];
            if (!in_array($old['kondisi'], $validKondisi)) {
                $errors['kondisi'] = 'Status kondisi tidak valid.';
            }

            // Validasi Tahun
            $currentYear = (int)date('Y');
            if ($old['tahun_pengadaan'] < 1990 || $old['tahun_pengadaan'] > ($currentYear + 1)) {
                $errors['tahun_pengadaan'] = 'Tahun pengadaan harus di antara 1990 dan ' . ($currentYear + 1) . '.';
            }

            // Pergantian Foto Aman (Opsional)
            $fotoFileName = $fasilitas['foto'];
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
                $uploadResult = $this->handleFileUpload($_FILES['foto']);
                if ($uploadResult['success']) {
                    // Hapus foto lama jika ada
                    $this->removeOldPhoto($fasilitas['foto']);
                    $fotoFileName = $uploadResult['filename'];
                } else {
                    $errors['foto'] = $uploadResult['message'];
                }
            }

            if (empty($errors)) {
                try {
                    $saveData = $old;
                    $saveData['foto'] = $fotoFileName;
                    $this->fasilitasModel->update($id, $saveData);
                    $_SESSION['flash_success'] = 'Data fasilitas ' . htmlspecialchars($old['nama_fasilitas']) . ' berhasil diperbarui.';
                    redirect('fasilitas');
                } catch (Exception $e) {
                    $errors['general'] = 'Gagal memperbarui fasilitas: ' . $e->getMessage();
                }
            }
            }
        }

        $kategoriList = $this->kategoriModel->getAll();
        $gedungList = $this->gedungModel->getAllActive();
        $ruanganList = $this->fasilitasModel->getRuanganByGedung(!empty($old['gedung_id']) ? (int)$old['gedung_id'] : null);

        $data = [
            'title' => 'Edit Fasilitas ' . htmlspecialchars($fasilitas['nama_fasilitas']) . ' - ' . APP_NAME,
            'user' => Auth::user(),
            'role' => Auth::role(),
            'fasilitas' => $fasilitas,
            'kategori_list' => $kategoriList,
            'gedung_list' => $gedungList,
            'ruangan_list' => $ruanganList,
            'errors' => $errors,
            'old' => $old
        ];

        $this->view('layouts/dashboard_header', $data);
        $this->view('layouts/sidebar', $data);
        $this->view('layouts/topbar', $data);
        $this->view('fasilitas/edit', $data);
        $this->view('layouts/dashboard_footer', $data);
    }

    /**
     * Update kondisi fasilitas secara cepat (Petugas Sarpras & Admin)
     */
    public function updateKondisi($id = null) {
        Auth::requireRole(['admin', 'petugas_sarpras']);

        if (!$id || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['flash_error'] = 'Permintaan tidak valid.';
            redirect('fasilitas');
            return;
        }

        if (!csrf_validate()) {
            $_SESSION['flash_error'] = 'Token keamanan (CSRF) tidak valid.';
            redirect('fasilitas/detail/' . $id);
            return;
        }

        $fasilitas = $this->fasilitasModel->findById($id);
        if (!$fasilitas) {
            $_SESSION['flash_error'] = 'Data fasilitas tidak ditemukan.';
            redirect('fasilitas');
        }

        $kondisi = trim($_POST['kondisi'] ?? '');
        $catatan = trim($_POST['catatan'] ?? '');
        $validKondisi = ['baik', 'rusak_ringan', 'rusak_berat', 'tidak_layak', 'hilang'];

        if (!in_array($kondisi, $validKondisi)) {
            $_SESSION['flash_error'] = 'Status kondisi yang dipilih tidak valid.';
            redirect('fasilitas/detail/' . $id);
        }

        $this->fasilitasModel->updateKondisi($id, $kondisi, $catatan);
        $_SESSION['flash_success'] = 'Kondisi fasilitas "' . htmlspecialchars($fasilitas['nama_fasilitas']) . '" berhasil diperbarui menjadi ' . ucfirst(str_replace('_', ' ', $kondisi)) . '.';
        
        $redirectUrl = isset($_POST['redirect_back']) && $_POST['redirect_back'] === 'detail' ? 'fasilitas/detail/' . $id : 'fasilitas';
        redirect($redirectUrl);
    }

    /**
     * Menghapus fasilitas dengan validasi relasi data (Khusus Role Admin)
     */
    public function hapus($id = null) {
        Auth::requireRole('admin');

        if (!csrf_validate()) {
            $_SESSION['flash_error'] = 'Token keamanan (CSRF) tidak valid.';
            redirect('fasilitas');
            return;
        }

        if (!$id) {
            $_SESSION['flash_error'] = 'ID Fasilitas tidak valid.';
            redirect('fasilitas');
            return;
        }

        $fasilitas = $this->fasilitasModel->findById($id);
        if (!$fasilitas) {
            $_SESSION['flash_error'] = 'Data fasilitas tidak ditemukan.';
            redirect('fasilitas');
        }

        // 1. Validasi Relasi Pemeliharaan
        $pemeliharaanCount = $this->fasilitasModel->countPemeliharaan($id);
        if ($pemeliharaanCount > 0) {
            $_SESSION['flash_error'] = 'Fasilitas "' . htmlspecialchars($fasilitas['nama_fasilitas']) . '" tidak dapat dihapus karena memiliki ' . $pemeliharaanCount . ' riwayat tindakan pemeliharaan yang tercatat.';
            redirect('fasilitas');
        }

        // 2. Validasi Relasi Laporan Kerusakan
        $laporanCount = $this->fasilitasModel->countLaporan($id);
        if ($laporanCount > 0) {
            $_SESSION['flash_error'] = 'Fasilitas "' . htmlspecialchars($fasilitas['nama_fasilitas']) . '" tidak dapat dihapus karena masih terhubung dengan ' . $laporanCount . ' laporan kerusakan.';
            redirect('fasilitas');
        }

        // Hapus file foto dari server jika ada
        $this->removeOldPhoto($fasilitas['foto']);

        try {
            $this->fasilitasModel->delete($id);
            $_SESSION['flash_success'] = 'Fasilitas "' . htmlspecialchars($fasilitas['nama_fasilitas']) . '" (' . htmlspecialchars($fasilitas['kode_inventaris']) . ') berhasil dihapus.';
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = 'Gagal menghapus fasilitas karena batasan integritas database: ' . $e->getMessage();
        }

        redirect('fasilitas');
    }

    /**
     * Helper AJAX Endpoint: Mengambil daftar ruangan berdasarkan gedung (JSON)
     */
    public function ajaxRuangan() {
        header('Content-Type: application/json; charset=utf-8');
        $gedungId = isset($_GET['gedung_id']) ? (int)$_GET['gedung_id'] : null;
        $ruangan = $this->fasilitasModel->getRuanganByGedung($gedungId);
        echo json_encode($ruangan);
        exit;
    }

    /**
     * Helper Keamanan: Validasi & Upload Berkas Gambar
     * - Whitelist Extension: jpg, jpeg, png, webp
     * - Whitelist MIME Type: image/jpeg, image/png, image/webp
     * - Check getimagesize()
     * - Max size 2MB
     * - Random safe filename
     */
    protected function handleFileUpload($file) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Terjadi kesalahan saat mengunggah berkas foto (Error Code: ' . $file['error'] . ').'];
        }

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

        // 5. Nama Berkas Acak & Aman
        $uploadDir = (defined('UPLOAD_PATH') ? UPLOAD_PATH : (__DIR__ . '/../../public/uploads')) . '/fasilitas/';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        $newFileName = 'fasilitas_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $targetFilePath = $uploadDir . $newFileName;

        if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
            return ['success' => true, 'filename' => $newFileName];
        }

        return ['success' => false, 'message' => 'Gagal memindahkan berkas ke folder penyimpanan server.'];
    }

    /**
     * Menghapus foto lama dari penyimpanan server
     */
    protected function removeOldPhoto($filename) {
        if (!empty($filename)) {
            $uploadDir = (defined('UPLOAD_PATH') ? UPLOAD_PATH : (__DIR__ . '/../../public/uploads')) . '/fasilitas/';
            $path = $uploadDir . basename($filename);
            if (file_exists($path) && is_file($path)) {
                @unlink($path);
            }
        }
    }
}
