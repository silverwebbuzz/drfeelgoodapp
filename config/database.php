<?php
/**
 * Database Configuration
 * Connection settings for Dr. Feelgood App
 */

class Database {
    private $host = 'localhost';
    private $db_name = 'silverwebbuzz_in_drfeelgoodsapp';
    private $user = 'silverwebbuzz_in_drfeelgoodsapp';
    private $password = 'Drfeel@app123';
    private $charset = 'utf8mb4';

    private $conn;

    public function connect() {
        $this->conn = null;

        try {
            $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->db_name . ';charset=' . $this->charset;
            $this->conn = new PDO($dsn, $this->user, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo 'Connection Error: ' . $e->getMessage();
        }

        return $this->conn;
    }
}
?>
