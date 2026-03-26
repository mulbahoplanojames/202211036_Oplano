<?php
/**
 * Database Configuration
 * Curated Programming Tutorials Web Platform
 */

class Database {
    // private $host = "127.0.0.1:3307";
    // private $username = "root";
    // private $password = "newrootpassword";
    // private $db_name = "programming_tutorials";

    private $conn;



    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4", $this->username, $this->password);
            $this->conn->exec("set names utf8mb4");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>
