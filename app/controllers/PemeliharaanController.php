<?php
/**
 * PemeliharaanController
 * Modul Tindakan Pemeliharaan, Pemeriksaan & Riwayat Fasilitas
 * Kampus STMIK IKMI Cirebon.
 * 
 * RBAC Rules:
 * - Admin: Memiliki akses penuh (Lihat semua riwayat internal & publik, tambah, edit, hapus)
 * - Petugas Sarpras: Catat pemeliharaan baru, revisi riwayat, ubah kondisi fasilitas, filter per fasilitas
 * - Pengguna (Dosen/Mahasiswa): Hanya melihat informasi pemeliharaan bertanda publik (tampil_publik = 1)
 */

class PemeliharaanController extends Controller {
    protected $pemeliharaanModel;
    protected $fasilitasModel;
    protected $ruanganModel;
    protected $laporanModel;
    protected $userModel;

    public function __construct() {
        // Seluruh civitas kampus yang sudah terautentikasi dapat mengakses modul ini dengan data scoping
        Auth::requireLogin();
        $this->pemeliharaanModel = $this->model('PemeliharaanModel');
        $this->fasilitasModel = $this->model('FasilitasModel');
        $this->ruanganModel = $this->model('RuanganModel');
        $this->laporanModel = $this->model('LaporanModel');
        $this->userModel = $this->model('UserModel');
    }

    /**
     * Halaman Utama Daftar Riwayat Pemeliharaan
     * Mendukung multi-filter (pencarian, fasilitas_id, jenis tindakan, status, tanggal)
     * serta kalkulasi statistik sederhana (total pemeliharaan, total biaya, jumlah kerusakan)
     */
    public function index() {
        $user = Auth::user();
        $role = Auth::role();
        $isPengguna = ($role === 'pengguna');

        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $fasilitasId = isset($_GET['fasilitas_id']) && $_GET['fasilitas_id'] !== '' ? (int)$_GET['fasilitas_id'] : null;
        $ruanganId = isset($_GET['ruangan_id']) && $_GET['ruangan_id'] !== '' ? (int)$_GET['ruangan_id'] : null;
        $jenisTindakan = isset($_GET['jenis_tindakan']) && $_GET['jenis_tindakan'] !== '' ? trim($_GET['jenis_tindakan']) : null;
        $status = isset($_GET['status']) && $_GET['status'] !== '' ? trim($_GET['status']) : null;
        $tanggalDari = isset($_GET['tanggal_dari']) && $_GET['tanggal_dari'] !== '' ? trim($_GET['tanggal_dari']) : null;
        $tanggalSampai = isset($_GET['tanggal_sampai']) && $_GET['tanggal_sampai'] !== '' ? trim($_GET['tanggal_sampai']) : null;

        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 10;

        // Data scoping: Pengguna hanya melihat yang tampil_publik
        $totalRows = $this->pemeliharaanModel->countAll(
            $search, $fasilitasId, $ruanganId, $jenisTindakan, $status, $tanggalDari, $tanggalSampai, $isPengguna
        );

        $totalPages = ($totalRows > 0) ? (int)ceil($totalRows / $perPage) : 1;
        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $list = $this->pemeliharaanModel->getAll(
            $search, $fasilitasId, $ruanganId, $jenisTindakan, $status, $tanggalDari, $tanggalSampai, $isPengguna, $page, $perPage
        );

        // Perhitungan Sederhana Statistik
        $stats = $this->pemeliharaanModel->getStats($fasilitasId, $isPengguna);

        // Data master untuk filter dropdown
        $fasilitasList = $this->fasilitasModel->getAll('', null, null, null, null, 1, 1000);
        $ruanganList = $this->ruanganModel->getAll();

        // Info fasilitas aktif jika difilter berdasarkan fasilitas
        $selectedFasilitas = null;
        if (!empty($fasilitasId)) {
            $selectedFasilitas = $this->fasilitasModel->findById($fasilitasId);
        }

        $data = [
            'title' => 'Pemeliharaan & Riwayat Fasilitas - ' . APP_NAME,
            'user' => $user,
            'role' => $role,
            'is_pengguna' => $isPengguna,
            'pemeliharaan_list' => $list,
            'fasilitas_list' => $fasilitasList,
            'ruangan_list' => $ruanganList,
            'selected_fasilitas' => $selectedFasilitas,
            'stats' => $stats,
            'search' => $search,
            'fasilitas_id' => $fasilitasId,
            'ruangan_id' => $ruanganId,
            'jenis_tindakan' => $jenisTindakan,
            'status' => $status,
            'tanggal_dari' => $tanggalDari,
            'tanggal_sampai' => $tanggalSampai,
            'page' => $page,
            'per_page' => $perPage,
            'total_rows' => $totalRows,
            'total_pages' => $totalPages
        ];

        $this->view('layouts/dashboard_header', $data);
        $this->view('layouts/sidebar', $data);
        $this->view('layouts/topbar', $data);
        $this->view('pemeliharaan/index', $data);
        $this->view('layouts/dashboard_footer', $data);
    }

    /**
     * Shortcut rute melihat riwayat histori berdasarkan fasilitas tertentu
     */
    public function fasilitas($fasilitasId = null) {
        if (!$fasilitasId) {
            redirect('pemeliharaan');
        }
        redirect('pemeliharaan?fasilitas_id=' . (int)$fasilitasId);
    }

    /**
     * Rincian Lengkap Dokumen Pemeliharaan
     */
    public function detail($id = null) {
        if (!$id) {
            $_SESSION['flash_error'] = 'ID Pemeliharaan tidak valid.';
            redirect('pemeliharaan');
        }

        $isPengguna = Auth::hasRole('pengguna');
        $pemeliharaan = $this->pemeliharaanModel->findById($id, $isPengguna);

        if (!$pemeliharaan) {
            $_SESSION['flash_error'] = 'Catatan pemeliharaan tidak ditemukan atau dibatasi untuk internal sarpras.';
            redirect('pemeliharaan');
        }

        $data = [
            'title' => 'Rincian ' . htmlspecialchars($pemeliharaan['kode_pemeliharaan']) . ' - ' . APP_NAME,
            'user' => Auth::user(),
            'role' => Auth::role(),
            'is_pengguna' => $isPengguna,
            'pemeliharaan' => $pemeliharaan
        ];

        $this->view('layouts/dashboard_header', $data);
        $this->view('layouts/sidebar', $data);
        $this->view('layouts/topbar', $data);
        $this->view('pemeliharaan/detail', $data);
        $this->view('layouts/dashboard_footer', $data);
    }

    /**
     * Tambah Catatan Riwayat Pemeliharaan Baru (Khusus Admin & Petugas Sarpras)
     */
    public function tambah() {
        Auth::requireRole(['admin', 'petugas_sarpras']);

        $preSelectedFasilitasId = isset($_GET['fasilitas_id']) ? (int)$_GET['fasilitas_id'] : '';
        $preSelectedLaporanId = isset($_GET['laporan_id']) ? (int)$_GET['laporan_id'] : '';

        // Default form state
        $old = [
            'fasilitas_id' => $preSelectedFasilitasId,
            'laporan_id' => $preSelectedLaporanId,
            'petugas_id' => Auth::id(),
            'tanggal' => date('Y-m-d'),
            'jenis_tindakan' => 'Pemeliharaan Rutin',
            'kondisi_sebelum' => 'baik',
            'kondisi_sesudah' => 'baik',
            'ubah_kondisi_fasilitas' => '1',
            'deskripsi' => '',
            'biaya' => '0',
            'keterangan' => '',
            'status' => 'selesai',
            'tampil_publik' => '1'
        ];

        // Jika fasilitas sudah dipilih, sesuaikan kondisi_sebelum otomatis
        if (!empty($preSelectedFasilitasId)) {
            $fInfo = $this->fasilitasModel->findById($preSelectedFasilitasId);
            if ($fInfo) {
                $old['kondisi_sebelum'] = $fInfo['kondisi'];
                $old['kondisi_sesudah'] = ($fInfo['kondisi'] === 'baik') ? 'baik' : 'baik';
            }
        }

        // Jika terhubung dari tiket laporan kerusakan
        if (!empty($preSelectedLaporanId)) {
            $lInfo = $this->laporanModel->findById($preSelectedLaporanId);
            if ($lInfo) {
                $old['fasilitas_id'] = $lInfo['fasilitas_id'];
                $old['kondisi_sebelum'] = $lInfo['kondisi_fasilitas'] ?? 'rusak_ringan';
                $old['jenis_tindakan'] = 'Perbaikan';
                $old['deskripsi'] = 'Penanganan tiket laporan ' . $lInfo['kode_laporan'] . ': ' . $lInfo['deskripsi'];
            }
        }

        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!csrf_validate()) {
                $errors['general'] = 'Sesi formulir telah kedaluwarsa atau token tidak valid (CSRF). Silakan muat ulang halaman.';
            } else {
                $old['fasilitas_id'] = (int)($_POST['fasilitas_id'] ?? 0);
            $old['laporan_id'] = !empty($_POST['laporan_id']) ? (int)$_POST['laporan_id'] : null;
            $old['petugas_id'] = (Auth::role() === 'admin' && !empty($_POST['petugas_id'])) ? (int)$_POST['petugas_id'] : Auth::id();
            $old['tanggal'] = trim($_POST['tanggal'] ?? date('Y-m-d'));
            $old['jenis_tindakan'] = trim($_POST['jenis_tindakan'] ?? 'Pemeliharaan Rutin');
            $old['kondisi_sebelum'] = trim($_POST['kondisi_sebelum'] ?? 'baik');
            $old['kondisi_sesudah'] = trim($_POST['kondisi_sesudah'] ?? 'baik');
            $old['ubah_kondisi_fasilitas'] = isset($_POST['ubah_kondisi_fasilitas']) ? '1' : '0';
            $old['deskripsi'] = trim($_POST['deskripsi'] ?? '');
            
            // Format biaya dari input angka atau format rupiah
            $rawBiaya = str_replace(['.', ',', 'Rp', ' '], '', $_POST['biaya'] ?? '0');
            $old['biaya'] = is_numeric($rawBiaya) ? (float)$rawBiaya : 0;

            $old['keterangan'] = trim($_POST['keterangan'] ?? '');
            $old['status'] = trim($_POST['status'] ?? 'selesai');
            $old['tampil_publik'] = isset($_POST['tampil_publik']) ? 1 : 0;

            // 1. Validasi Fasilitas
            if (empty($old['fasilitas_id']) || !$this->fasilitasModel->findById($old['fasilitas_id'])) {
                $errors['fasilitas_id'] = 'Pilih fasilitas sarpras yang valid.';
            }

            // 2. Validasi Tanggal
            if (empty($old['tanggal']) || !strtotime($old['tanggal'])) {
                $errors['tanggal'] = 'Tanggal tindakan pemeliharaan wajib diisi dengan format tanggal valid.';
            }

            // 3. Validasi Jenis Tindakan
            $validTindakan = ['Pemeriksaan', 'Perbaikan', 'Penggantian', 'Pemeliharaan Rutin'];
            if (!in_array($old['jenis_tindakan'], $validTindakan, true)) {
                $errors['jenis_tindakan'] = 'Pilih jenis tindakan pemeliharaan yang valid.';
            }

            // 4. Validasi Kondisi
            $validKondisi = ['baik', 'rusak_ringan', 'rusak_berat', 'tidak_layak', 'hilang'];
            if (!in_array($old['kondisi_sebelum'], $validKondisi, true)) {
                $errors['kondisi_sebelum'] = 'Kondisi sebelum tidak valid.';
            }
            if (!in_array($old['kondisi_sesudah'], $validKondisi, true)) {
                $errors['kondisi_sesudah'] = 'Kondisi sesudah tidak valid.';
            }

            // 5. Validasi Deskripsi
            if (empty($old['deskripsi'])) {
                $errors['deskripsi'] = 'Deskripsi tindakan pemeliharaan wajib diisi.';
            } elseif (strlen($old['deskripsi']) < 5) {
                $errors['deskripsi'] = 'Deskripsi minimal 5 karakter.';
            }

            // 6. Validasi Biaya
            if ($old['biaya'] < 0) {
                $errors['biaya'] = 'Biaya pemeliharaan tidak boleh bernilai negatif.';
            }

            // 7. Validasi Status
            $validStatus = ['dijadwalkan', 'sedang_dikerjakan', 'selesai', 'batal'];
            if (!in_array($old['status'], $validStatus, true)) {
                $errors['status'] = 'Pilih status pemeliharaan yang valid.';
            }

            if (empty($errors)) {
                try {
                    $insertData = [
                        'fasilitas_id' => $old['fasilitas_id'],
                        'laporan_id' => $old['laporan_id'],
                        'petugas_id' => $old['petugas_id'],
                        'tanggal' => $old['tanggal'],
                        'jenis_tindakan' => $old['jenis_tindakan'],
                        'kondisi_sebelum' => $old['kondisi_sebelum'],
                        'kondisi_sesudah' => $old['kondisi_sesudah'],
                        'deskripsi' => $old['deskripsi'],
                        'biaya' => $old['biaya'],
                        'keterangan' => $old['keterangan'],
                        'status' => $old['status'],
                        'tampil_publik' => $old['tampil_publik'],
                        'update_fasilitas_kondisi' => ($old['ubah_kondisi_fasilitas'] === '1')
                    ];

                    $newId = $this->pemeliharaanModel->create($insertData);
                    $_SESSION['flash_success'] = 'Catatan pemeliharaan berhasil disimpan dan status fasilitas telah diperbarui.';

                    if (isset($_POST['redirect_target']) && $_POST['redirect_target'] === 'fasilitas') {
                        redirect('fasilitas/detail/' . $old['fasilitas_id']);
                    } else {
                        redirect('pemeliharaan');
                    }
                } catch (Exception $e) {
                    $errors['general'] = 'Gagal menyimpan catatan pemeliharaan: ' . $e->getMessage();
                }
            }
            }
        }

        // Ambil daftar fasilitas dan petugas untuk form
        $fasilitasList = $this->fasilitasModel->getAll('', null, null, null, null, 1, 1000);
        $petugasList = $this->userModel->getStaffUsers();

        $data = [
            'title' => 'Catat Tindakan Pemeliharaan Baru - ' . APP_NAME,
            'user' => Auth::user(),
            'role' => Auth::role(),
            'fasilitas_list' => $fasilitasList,
            'petugas_list' => $petugasList,
            'errors' => $errors,
            'old' => $old
        ];

        $this->view('layouts/dashboard_header', $data);
        $this->view('layouts/sidebar', $data);
        $this->view('layouts/topbar', $data);
        $this->view('pemeliharaan/create', $data);
        $this->view('layouts/dashboard_footer', $data);
    }

    /**
     * Edit Catatan Pemeliharaan (Khusus Admin & Petugas Sarpras)
     */
    public function edit($id = null) {
        Auth::requireRole(['admin', 'petugas_sarpras']);

        if (!$id) {
            $_SESSION['flash_error'] = 'ID Pemeliharaan tidak valid.';
            redirect('pemeliharaan');
        }

        $pemeliharaan = $this->pemeliharaanModel->findById($id);
        if (!$pemeliharaan) {
            $_SESSION['flash_error'] = 'Catatan pemeliharaan tidak ditemukan.';
            redirect('pemeliharaan');
        }

        $old = [
            'fasilitas_id' => $pemeliharaan['fasilitas_id'],
            'laporan_id' => $pemeliharaan['laporan_id'],
            'petugas_id' => $pemeliharaan['petugas_id'],
            'tanggal' => !empty($pemeliharaan['tanggal']) ? $pemeliharaan['tanggal'] : $pemeliharaan['tanggal_mulai'],
            'jenis_tindakan' => $pemeliharaan['jenis_tindakan'],
            'kondisi_sebelum' => $pemeliharaan['kondisi_sebelum'],
            'kondisi_sesudah' => $pemeliharaan['kondisi_sesudah'],
            'ubah_kondisi_fasilitas' => '1',
            'deskripsi' => $pemeliharaan['deskripsi'] ?? $pemeliharaan['keterangan'] ?? '',
            'biaya' => $pemeliharaan['biaya'],
            'keterangan' => $pemeliharaan['keterangan'] ?? '',
            'status' => $pemeliharaan['status'],
            'tampil_publik' => $pemeliharaan['tampil_publik']
        ];

        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!csrf_validate()) {
                $errors['general'] = 'Sesi formulir telah kedaluwarsa atau token tidak valid (CSRF). Silakan muat ulang halaman.';
            } else {
                $old['fasilitas_id'] = (int)($_POST['fasilitas_id'] ?? 0);
            $old['laporan_id'] = !empty($_POST['laporan_id']) ? (int)$_POST['laporan_id'] : null;
            $old['petugas_id'] = (Auth::role() === 'admin' && !empty($_POST['petugas_id'])) ? (int)$_POST['petugas_id'] : $pemeliharaan['petugas_id'];
            $old['tanggal'] = trim($_POST['tanggal'] ?? date('Y-m-d'));
            $old['jenis_tindakan'] = trim($_POST['jenis_tindakan'] ?? 'Pemeliharaan Rutin');
            $old['kondisi_sebelum'] = trim($_POST['kondisi_sebelum'] ?? 'baik');
            $old['kondisi_sesudah'] = trim($_POST['kondisi_sesudah'] ?? 'baik');
            $old['ubah_kondisi_fasilitas'] = isset($_POST['ubah_kondisi_fasilitas']) ? '1' : '0';
            $old['deskripsi'] = trim($_POST['deskripsi'] ?? '');

            $rawBiaya = str_replace(['.', ',', 'Rp', ' '], '', $_POST['biaya'] ?? '0');
            $old['biaya'] = is_numeric($rawBiaya) ? (float)$rawBiaya : 0;

            $old['keterangan'] = trim($_POST['keterangan'] ?? '');
            $old['status'] = trim($_POST['status'] ?? 'selesai');
            $old['tampil_publik'] = isset($_POST['tampil_publik']) ? 1 : 0;

            // Validasi
            if (empty($old['fasilitas_id']) || !$this->fasilitasModel->findById($old['fasilitas_id'])) {
                $errors['fasilitas_id'] = 'Pilih fasilitas sarpras yang valid.';
            }

            if (empty($old['tanggal']) || !strtotime($old['tanggal'])) {
                $errors['tanggal'] = 'Tanggal wajib diisi dengan format tanggal valid.';
            }

            $validTindakan = ['Pemeriksaan', 'Perbaikan', 'Penggantian', 'Pemeliharaan Rutin'];
            if (!in_array($old['jenis_tindakan'], $validTindakan, true)) {
                $errors['jenis_tindakan'] = 'Pilih jenis tindakan pemeliharaan yang valid.';
            }

            $validKondisi = ['baik', 'rusak_ringan', 'rusak_berat', 'tidak_layak', 'hilang'];
            if (!in_array($old['kondisi_sebelum'], $validKondisi, true)) {
                $errors['kondisi_sebelum'] = 'Kondisi sebelum tidak valid.';
            }
            if (!in_array($old['kondisi_sesudah'], $validKondisi, true)) {
                $errors['kondisi_sesudah'] = 'Kondisi sesudah tidak valid.';
            }

            if (empty($old['deskripsi'])) {
                $errors['deskripsi'] = 'Deskripsi tindakan pemeliharaan wajib diisi.';
            }

            if ($old['biaya'] < 0) {
                $errors['biaya'] = 'Biaya tidak boleh bernilai negatif.';
            }

            if (empty($errors)) {
                try {
                    $updateData = [
                        'fasilitas_id' => $old['fasilitas_id'],
                        'laporan_id' => $old['laporan_id'],
                        'petugas_id' => $old['petugas_id'],
                        'tanggal' => $old['tanggal'],
                        'jenis_tindakan' => $old['jenis_tindakan'],
                        'kondisi_sebelum' => $old['kondisi_sebelum'],
                        'kondisi_sesudah' => $old['kondisi_sesudah'],
                        'deskripsi' => $old['deskripsi'],
                        'biaya' => $old['biaya'],
                        'keterangan' => $old['keterangan'],
                        'status' => $old['status'],
                        'tampil_publik' => $old['tampil_publik'],
                        'update_fasilitas_kondisi' => ($old['ubah_kondisi_fasilitas'] === '1')
                    ];

                    $this->pemeliharaanModel->update($id, $updateData);
                    $_SESSION['flash_success'] = 'Catatan pemeliharaan ' . htmlspecialchars($pemeliharaan['kode_pemeliharaan']) . ' berhasil diperbarui.';
                    redirect('pemeliharaan/detail/' . $id);
                } catch (Exception $e) {
                    $errors['general'] = 'Gagal memperbarui catatan: ' . $e->getMessage();
                }
            }
            }
        }

        $fasilitasList = $this->fasilitasModel->getAll('', null, null, null, null, 1, 1000);
        $petugasList = $this->userModel->getStaffUsers();

        $data = [
            'title' => 'Edit ' . htmlspecialchars($pemeliharaan['kode_pemeliharaan']) . ' - ' . APP_NAME,
            'user' => Auth::user(),
            'role' => Auth::role(),
            'pemeliharaan' => $pemeliharaan,
            'fasilitas_list' => $fasilitasList,
            'petugas_list' => $petugasList,
            'errors' => $errors,
            'old' => $old
        ];

        $this->view('layouts/dashboard_header', $data);
        $this->view('layouts/sidebar', $data);
        $this->view('layouts/topbar', $data);
        $this->view('pemeliharaan/edit', $data);
        $this->view('layouts/dashboard_footer', $data);
    }

    /**
     * Hapus Catatan Pemeliharaan (Khusus Role Admin)
     */
    public function hapus($id = null) {
        Auth::requireRole('admin');

        if (!csrf_validate()) {
            $_SESSION['flash_error'] = 'Token keamanan (CSRF) tidak valid.';
            redirect('pemeliharaan');
            return;
        }

        if (!$id) {
            $_SESSION['flash_error'] = 'ID Pemeliharaan tidak valid.';
            redirect('pemeliharaan');
        }

        $pemeliharaan = $this->pemeliharaanModel->findById($id);
        if (!$pemeliharaan) {
            $_SESSION['flash_error'] = 'Data pemeliharaan tidak ditemukan.';
            redirect('pemeliharaan');
        }

        try {
            $this->pemeliharaanModel->delete($id);
            $_SESSION['flash_success'] = 'Catatan pemeliharaan "' . htmlspecialchars($pemeliharaan['kode_pemeliharaan']) . '" berhasil dihapus.';
        } catch (Exception $e) {
            $_SESSION['flash_error'] = 'Gagal menghapus catatan pemeliharaan: ' . $e->getMessage();
        }

        redirect('pemeliharaan');
    }

    /**
     * Update Cepat Kondisi Fisik Fasilitas (Petugas & Admin)
     */
    public function updateKondisi($fasilitasId = null) {
        Auth::requireRole(['admin', 'petugas_sarpras']);

        if (!$fasilitasId || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['flash_error'] = 'Permintaan tidak valid.';
            redirect('pemeliharaan');
        }

        if (!csrf_validate()) {
            $_SESSION['flash_error'] = 'Token keamanan (CSRF) tidak valid.';
            redirect('pemeliharaan?fasilitas_id=' . $fasilitasId);
            return;
        }

        $kondisi = trim($_POST['kondisi'] ?? '');
        $catatan = trim($_POST['catatan'] ?? '');

        $result = $this->pemeliharaanModel->updateKondisiFasilitas($fasilitasId, $kondisi, $catatan);
        if ($result) {
            $_SESSION['flash_success'] = 'Kondisi fisik fasilitas berhasil diperbarui menjadi ' . ucfirst(str_replace('_', ' ', $kondisi)) . '.';
        } else {
            $_SESSION['flash_error'] = 'Gagal memperbarui kondisi fasilitas.';
        }

        $redirectBack = $_POST['redirect_back'] ?? 'pemeliharaan';
        if ($redirectBack === 'detail_fasilitas') {
            redirect('fasilitas/detail/' . $fasilitasId);
        } else {
            redirect('pemeliharaan?fasilitas_id=' . $fasilitasId);
        }
    }
}
