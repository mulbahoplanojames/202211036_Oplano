<?php
class Database {
    
    // private $host = "127.0.0.1:3307";
    // private $username = "root";
    // private $password = "newrootpassword";
    // private $db_name = "blood_donation_db";


     private $host = "sql302.infinityfree.com";
    private $username = "if0_41420494";
    private $password = "newrootpassword";
    private $db_name = "if0_41420494_blood_donation_db";

    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>
