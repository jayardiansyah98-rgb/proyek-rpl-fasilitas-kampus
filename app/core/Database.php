<?php
/**
 * PDO Database Wrapper
 * Sistem Informasi Pendataan Fasilitas STMIK IKMI Cirebon
 */

class Database {
    private $host = DB_HOST;
    private $port = DB_PORT;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;
    private $charset = DB_CHARSET;

    private $dbh; // Database Handler
    private $stmt; // Statement
    private $error;

    public function __construct() {
        // Set DSN (Data Source Name)
        $dsn = 'mysql:host=' . $this->host . ';port=' . $this->port . ';dbname=' . $this->dbname . ';charset=' . $this->charset;
        
        $options = [
            PDO::ATTR_PERSISTENT => false,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ];

        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            die('Koneksi Database Gagal: ' . $this->error);
        }
    }

    /**
     * Mempersiapkan Query SQL
     */
    public function query($sql) {
        $this->stmt = $this->dbh->prepare($sql);
        return $this;
    }

    /**
     * Binding Parameter Prepared Statement
     */
    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
        return $this;
    }

    /**
     * Eksekusi Statement
     */
    public function execute() {
        return $this->stmt->execute();
    }

    /**
     * Mengambil Semua Baris Hasil Query (Array of Assoc)
     */
    public function resultSet() {
        $this->execute();
        return $this->stmt->fetchAll();
    }

    /**
     * Mengambil 1 Baris Hasil Query
     */
    public function single() {
        $this->execute();
        return $this->stmt->fetch();
    }

    /**
     * Menghitung Jumlah Baris Terdampak
     */
    public function rowCount() {
        return $this->stmt->rowCount();
    }

    /**
     * Mendapatkan ID Terakhir Hasil Auto Increment
     */
    public function lastInsertId() {
        return $this->dbh->lastInsertId();
    }

    /**
     * Memulai Transaksi Database
     */
    public function beginTransaction() {
        return $this->dbh->beginTransaction();
    }

    /**
     * Commit Transaksi
     */
    public function commit() {
        return $this->dbh->commit();
    }

    /**
     * Rollback Transaksi
     */
    public function rollBack() {
        return $this->dbh->rollBack();
    }

    /**
     * Mendapatkan PDO Instance langsung jika diperlukan query lanjutan
     */
    public function getPdo() {
        return $this->dbh;
    }
}
