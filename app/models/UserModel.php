<?php
/**
 * UserModel
 * Menangani interaksi data pengguna pada tabel users dan roles
 */

class UserModel extends Model {
    /**
     * Mencari pengguna berdasarkan Email atau Nomor Induk (NIM/NIDN/NIP)
     */
    public function findByIdentifier($identifier) {
        $sql = "SELECT u.*, r.nama_role, r.deskripsi as role_deskripsi 
                FROM users u 
                INNER JOIN roles r ON u.role_id = r.id 
                WHERE u.email = :email OR u.nomor_induk = :nomor_induk 
                LIMIT 1";

        $this->db->query($sql);
        $this->db->bind(':email', trim($identifier));
        $this->db->bind(':nomor_induk', trim($identifier));
        return $this->db->single();
    }

    /**
     * Mencari pengguna berdasarkan ID
     */
    public function findById($id) {
        $sql = "SELECT u.*, r.nama_role 
                FROM users u 
                INNER JOIN roles r ON u.role_id = r.id 
                WHERE u.id = :id 
                LIMIT 1";

        $this->db->query($sql);
        $this->db->bind(':id', $id, PDO::PARAM_INT);
        return $this->db->single();
    }

    /**
     * Memvalidasi kredensial login (identifier + password)
     * Mengembalikan data user jika valid, atau false dengan pesan error
     */
    public function validateCredentials($identifier, $password) {
        $user = $this->findByIdentifier($identifier);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'Email atau Nomor Induk tidak terdaftar.'
            ];
        }

        if ($user['status'] !== 'aktif') {
            return [
                'success' => false,
                'message' => 'Akun Anda sedang dinonaktifkan. Silakan hubungi Administrator Sarpras IKMI.'
            ];
        }

        // Verifikasi password hash Bcrypt
        if (!password_verify($password, $user['password'])) {
            return [
                'success' => false,
                'message' => 'Kata sandi yang Anda masukkan salah.'
            ];
        }

        return [
            'success' => true,
            'user' => $user
        ];
    }

    /**
     * Mengambil daftar semua pengguna (khusus role Admin)
     */
    public function getAllUsers() {
        $sql = "SELECT u.id, u.nomor_induk, u.nama_lengkap, u.email, u.telepon, 
                       u.jenis_pengguna, u.status, u.created_at, r.nama_role 
                FROM users u 
                INNER JOIN roles r ON u.role_id = r.id 
                ORDER BY u.id ASC";

        return $this->db->query($sql)->resultSet();
    }

    /**
     * Mengambil daftar staf/petugas sarpras dan admin yang aktif untuk penugasan
     */
    public function getStaffUsers() {
        $sql = "SELECT id, nama_lengkap, nomor_induk 
                FROM users 
                WHERE role_id IN (1, 2) AND status = 'aktif' 
                ORDER BY nama_lengkap ASC";
        return $this->db->query($sql)->resultSet();
    }

    /**
     * Memeriksa apakah email atau nomor induk sudah digunakan oleh user lain
     */
    public function isIdentifierTaken($email, $nomorInduk, $excludeId = null) {
        $sql = "SELECT id, email, nomor_induk FROM users WHERE (email = :email OR nomor_induk = :nomor_induk)";
        if ($excludeId !== null) {
            $sql .= " AND id != :exclude_id";
        }
        $sql .= " LIMIT 1";

        $this->db->query($sql);
        $this->db->bind(':email', trim($email));
        $this->db->bind(':nomor_induk', trim($nomorInduk));
        if ($excludeId !== null) {
            $this->db->bind(':exclude_id', (int)$excludeId, PDO::PARAM_INT);
        }
        return $this->db->single();
    }

    /**
     * Membuat akun pengguna baru secara aman dengan password hash Bcrypt
     */
    public function createUser(array $data) {
        $sql = "INSERT INTO users (role_id, nomor_induk, nama_lengkap, email, password, telepon, jenis_pengguna, status) 
                VALUES (:role_id, :nomor_induk, :nama_lengkap, :email, :password, :telepon, :jenis_pengguna, :status)";

        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);

        $this->db->query($sql);
        $this->db->bind(':role_id', (int)$data['role_id'], PDO::PARAM_INT);
        $this->db->bind(':nomor_induk', trim($data['nomor_induk']));
        $this->db->bind(':nama_lengkap', trim($data['nama_lengkap']));
        $this->db->bind(':email', trim($data['email']));
        $this->db->bind(':password', $hashedPassword);
        $this->db->bind(':telepon', $data['telepon'] ?? null);
        $this->db->bind(':jenis_pengguna', $data['jenis_pengguna'] ?? 'mahasiswa');
        $this->db->bind(':status', $data['status'] ?? 'aktif');

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Memperbarui nama lengkap pengguna
     */
    public function updateNamaLengkap($id, $namaLengkap) {
        $sql = "UPDATE users SET nama_lengkap = :nama_lengkap WHERE id = :id";
        $this->db->query($sql);
        $this->db->bind(':nama_lengkap', trim($namaLengkap));
        $this->db->bind(':id', (int)$id, PDO::PARAM_INT);
        return $this->db->execute();
    }
}
