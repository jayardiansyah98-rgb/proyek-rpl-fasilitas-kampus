<?php
/**
 * Generate authenticated session files for the 4 roles:
 * Admin, Petugas Sarpras, Dosen, Mahasiswa
 */
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/helpers/Auth.php';

session_save_path('C:/laragon/tmp');

$roles = [
    'admin' => ['email' => 'admin@ikmi.ac.id', 'sid' => 'testsessadmin00112233445566778899'],
    'petugas' => ['email' => 'petugas@ikmi.ac.id', 'sid' => 'testsesspetugas001122334455667788'],
    'dosen' => ['email' => 'dosen@ikmi.ac.id', 'sid' => 'testsessdosen00112233445566778899'],
    'mahasiswa' => ['email' => 'mahasiswa@ikmi.ac.id', 'sid' => 'testsessmhs0011223344556677889900']
];

$db = new Database();
$output = [];

foreach ($roles as $roleKey => $item) {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }
    session_id($item['sid']);
    session_start();

    $db->query("SELECT u.*, r.nama_role FROM users u JOIN roles r ON u.role_id = r.id WHERE u.email = :email");
    $db->bind(':email', $item['email']);
    $user = $db->single();

    if ($user) {
        $_SESSION['user'] = [
            'id' => (int)$user['id'],
            'role_id' => (int)$user['role_id'],
            'nama_role' => $user['nama_role'],
            'nomor_induk' => $user['nomor_induk'],
            'nama_lengkap' => $user['nama_lengkap'],
            'email' => $user['email'],
            'jenis_pengguna' => $user['jenis_pengguna'],
            'telepon' => $user['telepon'] ?? '',
            'logged_at' => time(),
            'last_activity' => time()
        ];
        session_write_close();
        $output[$roleKey] = [
            'session_id' => $item['sid'],
            'user_id' => $user['id'],
            'nama_lengkap' => $user['nama_lengkap'],
            'nama_role' => $user['nama_role'],
            'jenis_pengguna' => $user['jenis_pengguna']
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($output, JSON_PRETTY_PRINT);
