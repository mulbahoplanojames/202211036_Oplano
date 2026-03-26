<?php
class Database {
    
    // private $host = "127.0.0.1:3307";
    // private $username = "root";
    // private $password = "newrootpassword";
    // private $db_name = "blood_donation_db";

    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            // Log the error for debugging
            error_log("Database connection failed: " . $exception->getMessage());
            echo json_encode(array(
                "status" => "error", 
                "message" => "Database connection failed: " . $exception->getMessage()
            ));
            return null;
        }

        return $this->conn;
    }
}
?>
