<?php
/**
 * Base Model
 * Menyediakan instance PDO Database untuk semua Model entitas
 */

class Model {
    protected $db;

    public function __construct() {
        $this->db = new Database();
    }
}
