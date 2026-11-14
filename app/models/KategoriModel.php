<?php
/**
 * KategoriModel
 * Menangani transaksi data master kategori fasilitas kampus STMIK IKMI Cirebon
 */

class KategoriModel extends Model {
    /**
     * Mengambil seluruh kategori fasilitas
     */
    public function getAll() {
        $sql = "SELECT k.*, (SELECT COUNT(*) FROM fasilitas WHERE kategori_id = k.id) as total_fasilitas 
                FROM kategori_fasilitas k 
                ORDER BY k.nama_kategori ASC";
        return $this->db->query($sql)->resultSet();
    }

    /**
     * Mengambil data kategori berdasarkan ID
     */
    public function findById($id) {
        $sql = "SELECT * FROM kategori_fasilitas WHERE id = :id LIMIT 1";
        $this->db->query($sql);
        $this->db->bind(':id', (int)$id, PDO::PARAM_INT);
        return $this->db->single();
    }
}
