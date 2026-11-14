# Sistem Informasi Pendataan Fasilitas Setiap Ruangan di Kampus STMIK IKMI Cirebon Berbasis Web Dilengkapi AI

Aplikasi web manajemen sarana dan prasarana kampus berbasis **PHP Native (Arsitektur MVC)**, **MySQL (Normalisasi 3NF)**, **Bootstrap 5**, dan dilengkapi modul **Kecerdasan Buatan (AI)** untuk analisis kerusakan serta rekomendasi pemeliharaan prioritas.

## 👥Identitas Kelompok

- Kelompok : Kere Hore
- Kelas : TI-1D-REGULER
- Dosen Pengampu : Yudhistira Arie Wijaya, M.Kom

## 👥👥Anggota Kelompok

- Ketua : Fajar Ma'ruf (41257191)
- Anggota 1 : Eggi Alivia Rohmaht (41257187)
- Anggota 2 : Leo Ardiansyah Jailani (41257201)
- Anggota 3 : Daryell Zhafranabil Saren (41257184)
- Anggota 4 : Fakhri Fadlurrohman Rahmadani (41257192)

---

## 📋 Daftar Isi

1. [Fitur Utama Sistem](#-fitur-utama-sistem)
2. [Arsitektur & Struktur Direktori](#-arsitektur--struktur-direktori)
3. [Daftar Rute Utama (Routing Table)](#-daftar-rute-utama-routing-table)
4. [Matriks Role & Hak Akses (RBAC)](#-matriks-role--hak-akses-rbac)
5. [Hardening Keamanan Sistem](#-hardening-keamanan-sistem)
6. [Kredensial Akun Pengujian (Testing Accounts)](#-kredensial-akun-pengujian-testing-accounts)
7. [Hasil Pengujian Otomatis (Test Suite Checklist)](#-hasil-pengujian-otomatis-test-suite-checklist)
8. [Panduan Instalasi & Menjalankan di Laragon](#-panduan-instalasi--menjalankan-di-laragon)

---

## 🚀 Fitur Utama Sistem

1. **Autentikasi & RBAC Multi-Role**:
   - Dukungan 4 peran pengguna: **Admin**, **Petugas Sarpras**, **Dosen**, dan **Mahasiswa**.
   - Dilengkapi proteksi sesi anti-fixation (`session_regenerate_id`), `cookie_httponly`, dan auto-logout jika inaktif selama 2 jam.
   - Quick Fill 1-Klik akun pengujian di halaman login untuk efisiensi pengujian.
2. **Master Gedung & Ruangan**:
   - Pengelolaan data fisik gedung kampus dan ruangan per lantai.
   - Validasi integritas relasi: pencegahan penghapusan gedung yang memiliki ruangan, atau ruangan yang memiliki fasilitas.
3. **Inventaris Fasilitas Kampus**:
   - Pencatatan aset per ruangan dengan kode inventaris unik, kategori barang, tahun pengadaan, jumlah unit, dan kondisi fisik.
   - Fitur upload foto inventaris dengan validasi MIME biner dan proteksi nama berkas acak.
   - Update cepat status kondisi fisik inventaris.
4. **Pelaporan Kerusakan Fasilitas**:
   - Civitas kampus (Dosen & Mahasiswa) dapat melaporkan kerusakan sarpras dengan melampirkan foto bukti.
   - Alur verifikasi status tiket komprehensif: `menunggu` ➔ `diverifikasi` ➔ `diproses` ➔ `selesai` / `ditolak`.
   - Data scoping: Dosen & Mahasiswa hanya dapat melihat dan mengelola tiket laporan milik mereka sendiri.
5. **Pemeliharaan & Riwayat Servis**:
   - Pencatatan log perbaikan, perawatan berkala, dan penggantian suku cadang oleh Petugas Sarpras.
   - Terintegrasi langsung dengan tiket pengaduan kerusakan dan auto-update kondisi fisik fasilitas.
   - Pemisahan visibilitas catatan (publik vs internal sarpras).
6. **Dashboard Analitik & Visualisasi Grafik**:
   - Dashboard Admin & Petugas Sarpras: 10 kartu metrik, grafik distribusi kondisi (Chart.js Doughnut), jumlah fasilitas per gedung (Chart.js Bar), dan tabel inventaris bermasalah.
   - Dashboard Civitas Pengguna: ringkasan ruangan tersedia, fasilitas publik, dan status tindak lanjut tiket pribadi.
7. **Cetak & Ekspor Dokumen Laporan**:
   - Laporan multi-kriteria (inventaris fasilitas, ruangan, rekapitulasi kerusakan, dan histori pemeliharaan).
   - Format cetak ramah printer (_print-optimized view_).
8. **Modul Kecerdasan Buatan (AI)**:
   - **AI Assistant Chatbot**: Konsultasi berbasis bahasa natural dengan whitelist parameterized query (anti-SQL Injection dan anti-halusinasi).
   - **AI Facility Analyzer**: Deteksi anomali umur aset, gedung/ruangan dengan frekuensi kerusakan tertinggi, dan identifikasi pola kerusakan.
   - **AI Maintenance Recommender**: Rekomendasi prioritas servis berkala (Tinggi, Sedang, Rendah) untuk mendukung keputusan teknisi.
   - **Audit Trail Log AI**: Pencatatan riwayat inferensi prompt dan waktu eksekusi.

---

## 📁 Arsitektur & Struktur Direktori

Aplikasi dibangun murni menggunakan pendekatan **MVC (Model-View-Controller)** tanpa dependensi framework eksternal:

```text
Fasilitas Kampus/
├── .htaccess                         # Apache rewrite root ke public/
├── README.md                         # Dokumentasi sistem & panduan instalasi
├── app/
│   ├── config/
│   │   └── config.php                # Konfigurasi PDO MySQL, session timeout, BASE_URL dinamis
│   ├── controllers/                  # Controller penangan alur logika sistem
│   │   ├── AiAssistantController.php # Chatbot asisten virtual sarpras
│   │   ├── AiController.php          # Analisis fasilitas & rekomendasi servis
│   │   ├── AuthController.php        # Alur login, logout, dan CSRF verification
│   │   ├── DashboardController.php   # Agregasi metrik & visualisasi Chart.js
│   │   ├── ErrorController.php       # Tampilan halaman 404 dan 403 Forbidden
│   │   ├── FasilitasController.php   # Manajemen inventaris dan upload foto
│   │   ├── GedungController.php      # Manajemen master data gedung
│   │   ├── HomeController.php        # Landing page & ringkasan publik
│   │   ├── KategoriController.php    # Master kategori sarana & prasarana
│   │   ├── LaporanController.php     # Tiket pengaduan kerusakan & alur status
│   │   ├── PemeliharaanController.php# Rekam jejak pemeliharaan & perbaikan fisik
│   │   ├── ReportController.php      # Filter pelaporan & export/print dokumen
│   │   ├── RuanganController.php     # Manajemen master data ruangan
│   │   └── UserController.php        # Manajemen akun pengguna (Admin only)
│   ├── core/
│   │   ├── App.php                   # Front Controller Router & URL Parser
│   │   ├── Controller.php            # Base Controller (view renderer & model loader)
│   │   ├── Database.php              # PDO Wrapper dengan prepared statements
│   │   └── Model.php                 # Base Model mewarisi koneksi database
│   ├── helpers/
│   │   ├── Auth.php                  # Guard autentikasi, RBAC middleware, proteksi session
│   │   └── helper.php                # Helper base_url, sanitize, flash, dan CSRF generator/validator
│   ├── models/                       # Lapisan akses data (0 query SQL di Controller)
│   │   ├── AiLogModel.php            # Audit trail inferensi kecerdasan buatan
│   │   ├── DashboardModel.php        # Agregasi data metrik & grafik
│   │   ├── FasilitasModel.php        # Query entitas fasilitas & stok inventaris
│   │   ├── GedungModel.php           # Query entitas gedung kampus
│   │   ├── KategoriModel.php         # Query klasifikasi kategori fasilitas
│   │   ├── LaporanModel.php          # Query tiket kerusakan & alur verifikasi
│   │   ├── PemeliharaanModel.php     # Query histori servis fisik inventaris
│   │   ├── ReportModel.php           # Query pelaporan multi-dimensi
│   │   ├── RuanganModel.php          # Query entitas ruangan kelas & lab
│   │   └── UserModel.php             # Kredensial pengguna & enkripsi Bcrypt
│   ├── services/
│   │   ├── AIService.php             # Lapisan orkestrasi layanan kecerdasan buatan
│   │   └── ai/                       # Modul reasoning & machine intelligence
│   │       ├── AIFacilityAnalyzer.php       # Analitik anomali fasilitas
│   │       ├── AIMaintenanceRecommender.php # Kalkulasi prioritas servis
│   │       ├── AIProviderInterface.php     # Abstraksi driver AI
│   │       ├── FacilityDataRetriever.php    # Whitelist parameterized database reader
│   │       ├── GeminiProvider.php           # Driver integrasi Google Gemini API
│   │       ├── IntentResolver.php           # Parser intent bahasa natural
│   │       └── RuleBasedEngine.php          # Engine inferensi cerdas bawaan offline
│   └── views/                        # Tampilan antarmuka Bootstrap 5 responsif
│       ├── ai/                       # Chatbot, analisis, rekomendasi, log AI
│       ├── auth/                     # Formulir login & quick fill
│       ├── dashboard/                # Tampilan dashboard Admin, Petugas, Pengguna
│       ├── errors/                   # Tampilan 403 Forbidden & 404 Not Found
│       ├── fasilitas/                # CRUD, upload foto, & detail fasilitas
│       ├── gedung/                   # CRUD master gedung
│       ├── home/                     # Landing page utama
│       ├── kategori/                 # Master kategori
│       ├── laporan/                  # Buat laporan, verifikasi, timeline status
│       ├── layouts/                  # Layout modular (navbar, sidebar, topbar, footer)
│       ├── pemeliharaan/             # Log pemeliharaan & form tindakan
│       ├── report/                   # Filter laporan & lembar cetak
│       ├── ruangan/                  # CRUD master ruangan
│       └── users/                    # Daftar pengguna sistem
├── database/
│   ├── schema.sql                    # Skema 3NF MySQL + Seeder data awal
│   ├── test_db.php                   # Script verifikasi database & koneksi PDO
│   └── test_auth.php                 # Script verifikasi kredensial & RBAC
├── migration/
│   └── schema.sql                    # File migrasi terstandarisasi
├── public/
│   ├── .htaccess                     # Apache clean URL rewriting
│   ├── index.php                     # Entry point Front Controller
│   ├── assets/                       # File CSS kustom, JS, font icon
│   └── uploads/                      # Direktori upload foto (terlindungi .htaccess)
│       ├── fasilitas/
│       └── laporan/
└── tests/                            # Test suite otomatis (197 Pengujian PASS)
```

---

## 🗺️ Daftar Rute Utama (Routing Table)

Aplikasi menggunakan URL bersih (_Clean URL_) yang diuraikan oleh `App.php`:

| URL Route                       | Controller & Method                  | Metode HTTP | Hak Akses (Role) | Keterangan                                        |
| ------------------------------- | ------------------------------------ | ----------- | ---------------- | ------------------------------------------------- |
| `/` atau `/home`                | `HomeController::index`              | GET         | Publik           | Landing page sistem & statistik ringkas           |
| `/auth/login`                   | `AuthController::login`              | GET, POST   | Publik           | Form login & proses verifikasi kredensial         |
| `/auth/logout`                  | `AuthController::logout`             | GET, POST   | Terautentikasi   | Mengakhiri sesi dan regenerasi session ID         |
| `/dashboard`                    | `DashboardController::index`         | GET         | Semua Role       | Dashboard analitik sesuai peran pengguna          |
| `/gedung`                       | `GedungController::index`            | GET         | Semua Role       | Daftar seluruh gedung kampus                      |
| `/gedung/detail/{id}`           | `GedungController::detail`           | GET         | Semua Role       | Rincian gedung & ruangan di dalamnya              |
| `/gedung/tambah`                | `GedungController::tambah`           | GET, POST   | Admin            | Tambah master data gedung baru                    |
| `/gedung/edit/{id}`             | `GedungController::edit`             | GET, POST   | Admin            | Edit informasi gedung                             |
| `/gedung/hapus/{id}`            | `GedungController::hapus`            | GET (CSRF)  | Admin            | Hapus gedung (dilindungi relasi ruangan)          |
| `/ruangan`                      | `RuanganController::index`           | GET         | Semua Role       | Daftar inventaris ruangan kelas & laboratorium    |
| `/ruangan/detail/{id}`          | `RuanganController::detail`          | GET         | Semua Role       | Rincian fasilitas yang ada di ruangan             |
| `/ruangan/tambah`               | `RuanganController::tambah`          | GET, POST   | Admin, Petugas   | Tambah ruangan baru                               |
| `/ruangan/edit/{id}`            | `RuanganController::edit`            | GET, POST   | Admin, Petugas   | Ubah kapasitas, status, atau gedung ruangan       |
| `/ruangan/hapus/{id}`           | `RuanganController::hapus`           | GET (CSRF)  | Admin            | Hapus ruangan (dilindungi relasi fasilitas)       |
| `/fasilitas`                    | `FasilitasController::index`         | GET         | Semua Role       | Pencarian, filter multi-dimensi & pagination aset |
| `/fasilitas/detail/{id}`        | `FasilitasController::detail`        | GET         | Semua Role       | Spesifikasi fasilitas, histori servis, & tiket    |
| `/fasilitas/tambah`             | `FasilitasController::tambah`        | GET, POST   | Admin, Petugas   | Input inventaris baru + upload gambar aman        |
| `/fasilitas/edit/{id}`          | `FasilitasController::edit`          | GET, POST   | Admin, Petugas   | Perbarui spesifikasi atau foto inventaris         |
| `/fasilitas/hapus/{id}`         | `FasilitasController::hapus`         | GET (CSRF)  | Admin            | Hapus inventaris (dilindungi relasi laporan)      |
| `/fasilitas/updateKondisi/{id}` | `FasilitasController::updateKondisi` | POST        | Admin, Petugas   | Pembaruan cepat status kondisi fisik              |
| `/laporan`                      | `LaporanController::index`           | GET         | Semua Role       | Daftar tiket pengaduan kerusakan                  |
| `/laporan/detail/{id}`          | `LaporanController::detail`          | GET         | Semua Role       | Timeline histori tiket & rincian pelapor          |
| `/laporan/tambah`               | `LaporanController::tambah`          | GET, POST   | Semua Role       | Buat laporan kerusakan baru + upload bukti        |
| `/laporan/updateStatus/{id}`    | `LaporanController::updateStatus`    | POST        | Admin, Petugas   | Verifikasi status tiket (`diverifikasi`, dll.)    |
| `/laporan/hapus/{id}`           | `LaporanController::hapus`           | GET (CSRF)  | Admin            | Hapus tiket laporan pengujian/usang               |
| `/pemeliharaan`                 | `PemeliharaanController::index`      | GET         | Admin, Petugas   | Daftar seluruh tindakan pemeliharaan              |
| `/pemeliharaan/detail/{id}`     | `PemeliharaanController::detail`     | GET         | Semua Role       | Lembar rincian tindakan servis (scoped)           |
| `/pemeliharaan/tambah`          | `PemeliharaanController::tambah`     | GET, POST   | Admin, Petugas   | Catat pemeliharaan & sinkronisasi status tiket    |
| `/pemeliharaan/edit/{id}`       | `PemeliharaanController::edit`       | GET, POST   | Admin, Petugas   | Koreksi catatan tindakan atau biaya perbaikan     |
| `/pemeliharaan/hapus/{id}`      | `PemeliharaanController::hapus`      | GET (CSRF)  | Admin            | Hapus catatan pemeliharaan                        |
| `/report`                       | `ReportController::index`            | GET         | Admin, Petugas   | Filter laporan dan rekapitulasi data              |
| `/report/print`                 | `ReportController::print`            | GET         | Admin, Petugas   | Tampilan siap cetak (print preview)               |
| `/ai`                           | `AiController::index`                | GET         | Admin, Petugas   | Audit trail inferensi log AI                      |
| `/ai/analysis`                  | `AiController::analysis`             | GET         | Admin, Petugas   | Analisis anomali frekuensi kerusakan aset         |
| `/ai/recommendations`           | `AiController::recommendations`      | GET         | Admin, Petugas   | Rekomendasi prioritas servis preventif            |
| `/ai-assistant`                 | `AiAssistantController::index`       | GET         | Admin, Petugas   | Interface konsultasi asisten virtual sarpras      |
| `/ai-assistant/ask`             | `AiAssistantController::ask`         | POST (AJAX) | Admin, Petugas   | Endpoint API inferensi jawaban bahasa natural     |
| `/users`                        | `UserController::index`              | GET         | Admin            | Daftar seluruh pengguna civitas sistem            |

---

## 🛡️ Matriks Role & Hak Akses (RBAC)

| Modul / Tindakan                              |    Administrator     |    Petugas Sarpras     |    Dosen (Pengguna)    |  Mahasiswa (Pengguna)  |
| --------------------------------------------- | :------------------: | :--------------------: | :--------------------: | :--------------------: |
| **Login & Dashboard Role**                    | ✅ (Admin Dashboard) | ✅ (Petugas Dashboard) | ✅ (Civitas Dashboard) | ✅ (Civitas Dashboard) |
| **Manajemen User (`/users`)**                 |    ✅ Full Akses     |    ❌ 403 Forbidden    |    ❌ 403 Forbidden    |    ❌ 403 Forbidden    |
| **Master Gedung: Tambah / Edit / Hapus**      |    ✅ Full Akses     |      ❌ Read Only      |      ❌ Read Only      |      ❌ Read Only      |
| **Master Ruangan: Tambah / Edit**             |    ✅ Full Akses     |      ✅ Diizinkan      |      ❌ Read Only      |      ❌ Read Only      |
| **Master Ruangan: Hapus**                     |    ✅ Full Akses     |    ❌ Khusus Admin     |      ❌ Read Only      |      ❌ Read Only      |
| **Fasilitas: Tambah / Edit / Update Kondisi** |    ✅ Full Akses     |      ✅ Diizinkan      |      ❌ Read Only      |      ❌ Read Only      |
| **Fasilitas: Hapus**                          |    ✅ Full Akses     |    ❌ Khusus Admin     |      ❌ Read Only      |      ❌ Read Only      |
| **Pelaporan Kerusakan: Buat Laporan**         |     ✅ Diizinkan     |      ✅ Diizinkan      |      ✅ Diizinkan      |      ✅ Diizinkan      |
| **Pelaporan Kerusakan: Verifikasi Status**    |     ✅ Diizinkan     |      ✅ Diizinkan      |      ❌ Read Only      |      ❌ Read Only      |
| **Pelaporan Kerusakan: Data Scoping**         |    Melihat Semua     |     Melihat Semua      |  Hanya Milik Sendiri   |  Hanya Milik Sendiri   |
| **Pemeliharaan: Tambah / Edit**               |    ✅ Full Akses     |      ✅ Diizinkan      |    ❌ 403 Forbidden    |    ❌ 403 Forbidden    |
| **Pemeliharaan: Hapus**                       |    ✅ Full Akses     |    ❌ Khusus Admin     |    ❌ 403 Forbidden    |    ❌ 403 Forbidden    |
| **Export & Cetak Laporan (`/report`)**        |    ✅ Full Akses     |      ✅ Diizinkan      |    ❌ 403 Forbidden    |    ❌ 403 Forbidden    |
| **AI Assistant & Rekomendasi (`/ai`)**        |    ✅ Full Akses     |      ✅ Diizinkan      |    ❌ 403 Forbidden    |    ❌ 403 Forbidden    |

---

## 🔒 Hardening Keamanan Sistem

Sesuai hasil audit final pada tahap Prompt 11, sistem telah dilengkapi perlindungan keamanan:

1. **CSRF (Cross-Site Request Forgery)**:
   - Dilindungi helper `csrf_token()`, `csrf_field()`, dan `csrf_validate()`.
   - Seluruh form POST dan aksi link konfirmasi penghapusan divalidasi token CSRF berbasis sesi menggunakan `hash_equals()`.
2. **SQL Injection**:
   - Seluruh lapisan Model memanfaatkan PDO Prepared Statements dengan parameter binding terpisah (`PDO::PARAM_STR`, `PDO::PARAM_INT`).
   - Tidak ada string SQL concatenation pada input dinamis pengguna.
3. **XSS (Cross-Site Scripting)**:
   - Seluruh output dinamis dieksekusi melalui `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.
4. **Session Fixation & Hijacking**:
   - Fungsi `Auth::login()` selalu meregenerasi ID sesi melalui `session_regenerate_id(true)`.
   - Cookie sesi disetel `httponly=1`, `use_only_cookies=1`, dan otomatis `secure=1` pada koneksi HTTPS.
   - Sesi kadaluarsa secara otomatis jika tidak ada aktivitas pengguna selama 2 jam (`SESSION_TIMEOUT`).
5. **Upload File Vulnerability**:
   - Folder `public/uploads/` dan `public/uploads/laporan/` dilengkapi berkas `.htaccess` yang mematikan eksekusi script PHP/CGI (`php_flag engine off` & `SetHandler default-handler`).
   - Validasi file mencakup ekstensi whitelist (`jpg`, `jpeg`, `png`, `webp`), MIME type biner asli via `finfo`, ukuran maksimal berkas, serta nama file acak menggunakan `bin2hex(random_bytes(6))`.
6. **Error Disclosure**:
   - Pengaturan `APP_ENV` di `config.php`: saat mode `production`, `display_errors` dimatikan secara total sehingga struktur internal tidak bocor ke publik.

---

## 👥 Kredensial Akun Pengujian (Testing Accounts)

Semua kata sandi di-hash menggunakan algoritma standar aman PHP `password_hash(..., PASSWORD_BCRYPT)`. Pengguna juga dapat memanfaatkan tombol **Quick Fill** 1-klik pada halaman login:

| Peran (Role)             | Nomor Induk  | Nama Lengkap                | Email Akun             | Kata Sandi Awal    |
| ------------------------ | ------------ | --------------------------- | ---------------------- | ------------------ |
| **Administrator**        | `ADM-001`    | Rizky Ramadhan              | `admin@ikmi.ac.id`     | `Admin!IKMI2026`   |
| **Petugas Sarpras**      | `PET-001`    | Budi Santoso                | `petugas@ikmi.ac.id`   | `Petugas!IKMI2026` |
| **Dosen (Pengguna)**     | `0412088501` | Dr. Dadang Sudrajat, M.Kom. | `dosen@ikmi.ac.id`     | `Dadang!IKMI2026`  |
| **Mahasiswa (Pengguna)** | `4122001`    | Ahmad Fauzi                 | `mahasiswa@ikmi.ac.id` | `Ahmad!IKMI2026`   |

---

## 🧪 Hasil Pengujian Otomatis (Test Suite Checklist)

Pengujian komprehensif telah dijalankan secara otomatis melalui PHP CLI environment:

- [x] **Database & Skema Relasional (`database/test_db.php`)**:
  - Validasi koneksi PDO MySQL `db_fasilitas_ikmi` (OK).
  - Keberadaan 10 tabel utama (roles, users, gedung, ruangan, kategori_fasilitas, fasilitas, laporan_kerusakan, pemeliharaan, notifikasi, ai_logs).
  - Integritas Foreign Key relasi 1:N (Gedung-Ruangan, Ruangan-Fasilitas, Laporan, Pemeliharaan).
  - Status: **100% SUKSES**.
- [x] **Autentikasi & RBAC (`database/test_auth.php`)**:
  - Pengujian login Email & Nomor Induk, penolakan password salah, penolakan akun nonaktif.
  - Verifikasi hak akses server-side Admin, Petugas Sarpras, dan Pengguna (403 Forbidden Guard).
  - Status: **27 PASS / 0 FAIL (100% LULUS)**.
- [x] **Master Gedung & Ruangan (`tests/test_gedung_ruangan.php`)**:
  - Validasi CRUD gedung & ruangan, deteksi keunikan kode inventaris, filter gedung/lantai.
  - Pembatasan hapus gedung berpenghuni ruangan dan ruangan berfasilitas.
  - Status: **35 PASS / 0 FAIL (100% LULUS)**.
- [x] **Pelaporan Kerusakan Fasilitas (`tests/test_laporan_kerusakan.php`)**:
  - Penomoran otomatis kode tiket (`LAP-YYYYMM-XXX`).
  - Alur verifikasi status tiket (`menunggu` ➔ `diverifikasi` ➔ `diproses` ➔ `selesai` / `ditolak`).
  - Pembatasan akses tiket antar civitas (Data Scoping).
  - Status: **34 PASS / 0 FAIL (100% LULUS)**.
- [x] **Pemeliharaan & Riwayat Fasilitas (`tests/test_pemeliharaan.php`)**:
  - Penomoran kode pemeliharaan (`MT-YYYYMM-XXX`), insert log perbaikan, update otomatis kondisi fisik fasilitas.
  - Pemisahan rekam data publik vs internal sarpras.
  - Status: **24 PASS / 0 FAIL (100% LULUS)**.
- [x] **Dashboard & Reporting (`tests/test_dashboard_reporting.php`)**:
  - Kalkulasi akurat 10 kartu metrik dashboard Admin dan 5 kartu metrik Civitas.
  - Pengujian query agregasi grafik Chart.js dan 6 varian laporan pada `ReportModel`.
  - Status: **29 PASS / 0 FAIL (100% LULUS)**.
- [x] **AI Assistant Interaktif (`tests/test_ai_assistant.php`)**:
  - Intent resolution pada 6 variasi pertanyaan spesifikasi bahasa natural.
  - Whitelist parameterized queries pada `FacilityDataRetriever` (bebas SQL Injection & halusinasi).
  - Pencatatan log inferensi audit trail ke tabel `ai_logs`.
  - Status: **42 PASS / 0 FAIL (100% LULUS)**.
- [x] **AI Facility Analysis & Recommendations (`tests/test_ai_analysis_recommendations.php`)**:
  - Analisis anomali umur inventaris dan identifikasi ruangan rawan kerusakan.
  - Rekomendasi jadwal servis terstruktur dengan prioritas Tinggi/Sedang/Rendah.
  - Kepatuhan status: AI tidak mengubah data database secara sepihak tanpa verifikasi teknisi.
  - Status: **36 PASS / 0 FAIL (100% LULUS)**.

**Total Pengujian Unit & Integrasi:** **197 PENGUJIAN LULUS (0 GAGAL / 100% PASS RATE)**.

---

## ⚡ Panduan Instalasi & Menjalankan di Laragon

### 1. Prasyarat Sistem

- **Web Server**: Apache 2.4+ (Bawaan Laragon).
- **PHP**: Versi 8.1 / 8.2 / 8.3 dengan ekstensi aktif: `pdo_mysql`, `fileinfo`, `gd`, `openssl`, `mbstring`.
- **Database**: MySQL 8.0+ / MariaDB 10.4+.

### 2. Langkah Pemasangan

1. **Pastikan Folder Berada di Laragon**:
   Folder proyek ditempatkan pada direktori root Laragon:
   ```text
   C:\laragon\www\Fasilitas Kampus
   ```
2. **Jalankan Layanan Laragon**:
   Buka aplikasi Laragon, lalu klik tombol **Start All** untuk menyalakan Apache dan MySQL.
3. **Import Skema & Data Seeder**:
   Buka Terminal Laragon atau CMD, lalu jalankan:
   ```bash
   mysql -u root -e "source C:/laragon/www/Fasilitas Kampus/database/schema.sql"
   ```
4. **Verifikasi Integritas Database**:
   Jalankan script verifikasi bawaan melalui terminal:
   ```bash
   php "C:/laragon/www/Fasilitas Kampus/database/test_db.php"
   ```
5. **Akses Aplikasi Melalui Browser**:
   Buka browser web dan kunjungi salah satu URL berikut:
   - URL Folder Laragon: `http://localhost/Fasilitas%20Kampus/public/`
   - URL Halaman Login: `http://localhost/Fasilitas%20Kampus/public/auth/login`
   - Virtual Host (jika aktif): `http://fasilitas-kampus.test`

Aplikasi kini siap digunakan secara utuh sebagai solusi MVP Sistem Informasi Pendataan Fasilitas Kampus STMIK IKMI Cirebon.
