<?php
/**
 * Test Matrix Lengkap: Autentikasi 4 Role via HTTP Apache & PHP Native
 * Sistem Informasi Pendataan Fasilitas STMIK IKMI Cirebon
 */

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/models/UserModel.php';
require_once __DIR__ . '/../app/helpers/Auth.php';

echo "========================================================================\n";
echo "   PENGUJIAN LENGKAP MATRIKS AUTENTIKASI 4 ROLE (STMIK IKMI CIREBON)    \n";
echo "========================================================================\n\n";

$rolesToTest = [
    [
        'label' => 'Admin (Rizky Ramadhan)',
        'email' => 'admin@ikmi.ac.id',
        'nomor_induk' => 'ADM-001',
        'password' => 'Admin!IKMI2026',
        'expected_role' => 'admin',
        'expected_jenis' => 'staff',
        'expected_name' => 'Rizky Ramadhan',
        'expected_dashboard_view' => 'dashboard/admin',
        'expected_dashboard_keyword' => 'Dashboard Eksekutif Administrator'
    ],
    [
        'label' => 'Petugas Sarpras (Budi Santoso)',
        'email' => 'petugas@ikmi.ac.id',
        'nomor_induk' => 'PET-001',
        'password' => 'Petugas!IKMI2026',
        'expected_role' => 'petugas_sarpras',
        'expected_jenis' => 'staff',
        'expected_name' => 'Budi Santoso',
        'expected_dashboard_view' => 'dashboard/petugas',
        'expected_dashboard_keyword' => 'Dashboard Tim Teknis Sarpras'
    ],
    [
        'label' => 'Dosen (Dr. Dadang Sudrajat, M.Kom.)',
        'email' => 'dosen@ikmi.ac.id',
        'nomor_induk' => '0412088501',
        'password' => 'Dadang!IKMI2026',
        'expected_role' => 'pengguna',
        'expected_jenis' => 'dosen',
        'expected_name' => 'Dr. Dadang Sudrajat, M.Kom.',
        'expected_dashboard_view' => 'dashboard/pengguna',
        'expected_dashboard_keyword' => 'Civitas STMIK IKMI Cirebon (Dosen)'
    ],
    [
        'label' => 'Mahasiswa (Ahmad Fauzi)',
        'email' => 'mahasiswa@ikmi.ac.id',
        'nomor_induk' => '4122001',
        'password' => 'Ahmad!IKMI2026',
        'expected_role' => 'pengguna',
        'expected_jenis' => 'mahasiswa',
        'expected_name' => 'Ahmad Fauzi',
        'expected_dashboard_view' => 'dashboard/pengguna',
        'expected_dashboard_keyword' => 'Civitas STMIK IKMI Cirebon (Mahasiswa)'
    ]
];

$baseUrl = 'http://localhost/Fasilitas%20Kampus/public';
$userModel = new UserModel();

$totalTests = 0;
$totalPassed = 0;
$totalFailed = 0;

function reportTest($ok, $desc) {
    global $totalTests, $totalPassed, $totalFailed;
    $totalTests++;
    if ($ok) {
        $totalPassed++;
        echo "   [✓ PASS] {$desc}\n";
    } else {
        $totalFailed++;
        echo "   [✗ FAIL] {$desc}\n";
    }
}

function httpLoginRequest($baseUrl, $identifier, $password) {
    $cookieJar = tempnam(sys_get_temp_dir(), 'ikmi_cookie_');
    
    // 1. GET Login Page to retrieve CSRF token and session cookie
    $ch = curl_init($baseUrl . '/auth/login');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    $html = curl_exec($ch);
    curl_close($ch);

    preg_match('/name="csrf_token"\s+value="([^"]+)"/', $html, $m);
    $csrf = $m[1] ?? '';

    // 2. POST login credentials
    $ch = curl_init($baseUrl . '/auth/login');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'csrf_token' => $csrf,
        'identifier' => $identifier,
        'password' => $password
    ]));
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_HEADER, true);
    $postResponse = curl_exec($ch);
    $postHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // 3. GET Dashboard with established session
    $ch = curl_init($baseUrl . '/dashboard');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $dashBody = curl_exec($ch);
    $dashHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // 4. GET Logout
    $ch = curl_init($baseUrl . '/auth/logout');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    $logoutResponse = curl_exec($ch);
    $logoutHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // 5. GET Dashboard after logout (should redirect to login 302 or return 401/403/redirect)
    $ch = curl_init($baseUrl . '/dashboard');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    $afterLogoutResp = curl_exec($ch);
    $afterLogoutCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    @unlink($cookieJar);

    return [
        'post_code' => $postHttpCode,
        'post_response' => $postResponse,
        'dash_code' => $dashHttpCode,
        'dash_body' => $dashBody,
        'logout_code' => $logoutHttpCode,
        'after_logout_code' => $afterLogoutCode
    ];
}

foreach ($rolesToTest as $idx => $r) {
    echo ($idx + 1) . ". Pengujian Role: {$r['label']}\n";

    // --- Uji Database & Model Direct ---
    // A. Login via Email di Model
    $valEmail = $userModel->validateCredentials($r['email'], $r['password']);
    reportTest($valEmail['success'], "Model validateCredentials via Email ({$r['email']})");
    reportTest($valEmail['user']['nama_role'] === $r['expected_role'], "Role sesuai: '{$r['expected_role']}'");
    reportTest($valEmail['user']['nama_lengkap'] === $r['expected_name'], "Nama sesuai: '{$r['expected_name']}'");
    reportTest($valEmail['user']['jenis_pengguna'] === $r['expected_jenis'], "Jenis pengguna sesuai: '{$r['expected_jenis']}'");

    // B. Login via Nomor Induk di Model
    $valNip = $userModel->validateCredentials($r['nomor_induk'], $r['password']);
    reportTest($valNip['success'], "Model validateCredentials via Nomor Induk ({$r['nomor_induk']})");

    // C. Password salah di Model
    $valWrong = $userModel->validateCredentials($r['email'], 'SalahPassword!2026');
    reportTest(!$valWrong['success'] && strpos($valWrong['message'], 'salah') !== false, "Penolakan kata sandi salah menghasilkan pesan: '{$valWrong['message']}'");

    // --- Uji HTTP End-to-End via Apache Web Server ---
    // D. HTTP Login via Email
    $httpEmail = httpLoginRequest($baseUrl, $r['email'], $r['password']);
    reportTest($httpEmail['post_code'] === 302, "HTTP POST Login via Email redirect (302 Found)");
    reportTest($httpEmail['dash_code'] === 200, "HTTP GET Dashboard berhasil (200 OK)");
    reportTest(strpos($httpEmail['dash_body'], $r['expected_name']) !== false, "Dashboard memuat nama profil '{$r['expected_name']}'");
    reportTest(strpos($httpEmail['dash_body'], $r['expected_dashboard_keyword']) !== false, "Dashboard memuat konten view yang benar ('{$r['expected_dashboard_keyword']}')");

    // E. HTTP Login via Nomor Induk
    $httpNip = httpLoginRequest($baseUrl, $r['nomor_induk'], $r['password']);
    reportTest($httpNip['post_code'] === 302, "HTTP POST Login via Nomor Induk redirect (302 Found)");
    reportTest($httpNip['dash_code'] === 200, "HTTP GET Dashboard via Nomor Induk (200 OK)");
    reportTest(strpos($httpNip['dash_body'], $r['expected_name']) !== false, "Dashboard memuat nama profil pengguna");

    // F. HTTP Logout & Session Invalidation
    reportTest($httpEmail['logout_code'] === 302, "HTTP Logout merespons redirect (302 Found)");
    reportTest($httpEmail['after_logout_code'] === 302, "Akses dashboard pasca logout dialihkan kembali ke login (302)");

    // G. HTTP Login dengan Password Salah
    $httpWrong = httpLoginRequest($baseUrl, $r['email'], 'WrongPass123!');
    reportTest($httpWrong['post_code'] === 200 && strpos($httpWrong['post_response'], 'Kata sandi yang Anda masukkan salah.') !== false, "HTTP Login kata sandi salah tetap di halaman login dengan pesan error");

    echo "\n";
}

echo "========================================================================\n";
echo "HASIL AKHIR: {$totalPassed} PASS, {$totalFailed} FAIL (Total: {$totalTests} tes)\n";
if ($totalFailed === 0) {
    echo "STATUS: SELURUH PENGUJIAN MATRIKS AUTENTIKASI 4 ROLE SUKSES 100%!\n";
} else {
    echo "STATUS: ADA PENGUJIAN YANG GAGAL!\n";
}
echo "========================================================================\n";
