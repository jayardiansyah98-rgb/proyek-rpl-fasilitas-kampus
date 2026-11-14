<?php
/**
 * CLI Tool: Manajemen Pengguna Aman
 * Sistem Informasi Fasilitas STMIK IKMI Cirebon
 * 
 * Penggunaan:
 *   1. Melihat daftar akun:
 *      php database/manage_user.php list
 * 
 *   2. Menambah akun mahasiswa/dosen/petugas/admin baru:
 *      php database/manage_user.php add --role=pengguna --jenis=mahasiswa --nama="Nama Lengkap" --nomor="NIM" --email="email@ikmi.ac.id" --pass="password"
 */

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/models/UserModel.php';

$userModel = new UserModel();

$action = $argv[1] ?? 'help';

switch ($action) {
    case 'list':
        $users = $userModel->getAllUsers();
        echo "\n=== DAFTAR PENGGUNA TERDAFTAR (STMIK IKMI CIREBON) ===\n";
        printf("%-4s | %-12s | %-32s | %-24s | %-16s | %-8s\n", "ID", "No. Induk", "Nama Lengkap", "Email", "Role", "Status");
        echo str_repeat("-", 108) . "\n";
        foreach ($users as $u) {
            printf("%-4d | %-12s | %-32s | %-24s | %-16s | %-8s\n", 
                $u['id'], 
                $u['nomor_induk'], 
                $u['nama_lengkap'], 
                $u['email'], 
                $u['nama_role'] . ' (' . $u['jenis_pengguna'] . ')', 
                $u['status']
            );
        }
        echo "\nCatatan: Kata sandi tersimpan dalam format hash Bcrypt yang aman.\n\n";
        break;

    case 'add':
        // Parsing CLI options
        $options = [];
        for ($i = 2; $i < count($argv); $i++) {
            if (preg_match('/^--([a-zA-Z0-9_]+)=(.*)$/', $argv[$i], $m)) {
                $options[$m[1]] = $m[2];
            }
        }

        $nama = trim($options['nama'] ?? '');
        $nomor = trim($options['nomor'] ?? '');
        $email = trim($options['email'] ?? '');
        $password = $options['pass'] ?? '';
        $jenis = strtolower(trim($options['jenis'] ?? 'mahasiswa'));
        $roleName = strtolower(trim($options['role'] ?? 'pengguna'));
        $telepon = trim($options['telepon'] ?? '');

        if (empty($nama) || empty($nomor) || empty($email) || empty($password)) {
            echo "\n[ERROR] Parameter tidak lengkap!\n";
            echo "Contoh penggunaan:\n";
            echo "  php database/manage_user.php add --role=pengguna --jenis=mahasiswa --nama=\"Leo Ardiansyah Jailani\" --nomor=\"4122099\" --email=\"leo@ikmi.ac.id\" --pass=\"rahasia123\"\n\n";
            exit(1);
        }

        // Tentukan role_id
        $roleMap = [
            'admin' => 1,
            'petugas_sarpras' => 2,
            'petugas' => 2,
            'pengguna' => 3
        ];
        $roleId = $roleMap[$roleName] ?? 3;

        // Cek duplikasi
        $existing = $userModel->isIdentifierTaken($email, $nomor);
        if ($existing) {
            echo "\n[ERROR] Email '{$email}' atau Nomor Induk '{$nomor}' sudah digunakan oleh user ID {$existing['id']}!\n\n";
            exit(1);
        }

        $newId = $userModel->createUser([
            'role_id' => $roleId,
            'nomor_induk' => $nomor,
            'nama_lengkap' => $nama,
            'email' => $email,
            'password' => $password,
            'telepon' => $telepon ?: null,
            'jenis_pengguna' => $jenis,
            'status' => 'aktif'
        ]);

        if ($newId) {
            echo "\n[SUKSES] Akun baru berhasil dibuat dengan ID: {$newId}\n";
            echo "  - Nama Lengkap : {$nama}\n";
            echo "  - Nomor Induk  : {$nomor}\n";
            echo "  - Email        : {$email}\n";
            echo "  - Role         : {$roleName} ({$jenis})\n";
            echo "  - Password     : Tersimpan dengan hash Bcrypt aman.\n\n";
        } else {
            echo "\n[ERROR] Gagal menyimpan pengguna ke database.\n\n";
            exit(1);
        }
        break;

    case 'update-name':
        $options = [];
        for ($i = 2; $i < count($argv); $i++) {
            if (preg_match('/^--([a-zA-Z0-9_]+)=(.*)$/', $argv[$i], $m)) {
                $options[$m[1]] = $m[2];
            }
        }

        $id = (int)($options['id'] ?? 0);
        $nama = trim($options['nama'] ?? '');

        if (!$id || empty($nama)) {
            echo "\n[ERROR] Parameter tidak lengkap!\n";
            echo "Contoh: php database/manage_user.php update-name --id=1 --nama=\"Asep Saepullah, M.Kom.\"\n\n";
            exit(1);
        }

        $user = $userModel->findById($id);
        if (!$user) {
            echo "\n[ERROR] Pengguna dengan ID {$id} tidak ditemukan.\n\n";
            exit(1);
        }

        $oldName = $user['nama_lengkap'];
        if ($userModel->updateNamaLengkap($id, $nama)) {
            echo "\n[SUKSES] Nama pengguna ID {$id} berhasil diperbarui:\n";
            echo "  - Nama Sebelumnya : {$oldName}\n";
            echo "  - Nama Baru       : {$nama}\n\n";
        } else {
            echo "\n[ERROR] Gagal memperbarui nama pengguna di database.\n\n";
            exit(1);
        }
        break;

    default:
        echo "\n=== BANTUAN MANAJEMEN PENGGUNA ===\n";
        echo "1. php database/manage_user.php list\n";
        echo "2. php database/manage_user.php add --role=pengguna --jenis=mahasiswa --nama=\"...\" --nomor=\"...\" --email=\"...\" --pass=\"...\"\n";
        echo "3. php database/manage_user.php update-name --id=1 --nama=\"...\"\n\n";
        break;
}
