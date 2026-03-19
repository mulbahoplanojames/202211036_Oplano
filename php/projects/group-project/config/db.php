<?php
/**
 * Database Connection for TiDB Cloud
 * Uses mysqli with SSL for TiDB Cloud compatibility
 */

// PDO compatibility constants
if (!defined('PDO::FETCH_ASSOC')) {
    define('PDO::FETCH_ASSOC', MYSQLI_ASSOC);
}
if (!defined('PDO::PARAM_INT')) {
    define('PDO::PARAM_INT', 'i');
}
if (!defined('PDO::PARAM_STR')) {
    define('PDO::PARAM_STR', 's');
}

// Load environment variables
require_once __DIR__ . '/env_loader.php';

/**
 * MysqliStatementWrapper - Handles named parameters for mysqli
 */
class MysqliStatementWrapper {
    private $stmt;
    private $params = [];
    private $param_order = [];
    
    public function __construct($stmt) {
        $this->stmt = $stmt;
    }
    
    public function bindValue($param, $value, $type = null) {
        $this->params[$param] = $value;
        if (!in_array($param, $this->param_order)) {
            $this->param_order[] = $param;
        }
    }
    
    public function bindParam($param, &$var, $type = null) {
        $this->params[$param] = &$var;
        if (!in_array($param, $this->param_order)) {
            $this->param_order[] = $param;
        }
    }
    
    public function execute() {
        if (!empty($this->params)) {
            $types = str_repeat('s', count($this->params));
            $values = [];
            foreach ($this->param_order as $param) {
                $values[] = &$this->params[$param];
            }
            $this->stmt->bind_param($types, ...$values);
        }
        return $this->stmt->execute();
    }
    
    public function get_result() {
        return $this->stmt->get_result();
    }
    
    public function fetch_assoc() {
        return $this->stmt->get_result()->fetch_assoc();
    }
    
    public function fetch_all($style = MYSQLI_ASSOC) {
        return $this->stmt->get_result()->fetch_all($style);
    }
    
    public function rowCount() {
        return $this->stmt->get_result()->num_rows;
    }
    
    public function fetch($style = null) {
        if ($style === null || $style === MYSQLI_ASSOC || $style === 'PDO::FETCH_ASSOC') {
            return $this->stmt->get_result()->fetch_assoc();
        }
        return $this->stmt->get_result()->fetch_assoc();
    }
    
    public function fetchAll($style = MYSQLI_ASSOC) {
        // Handle PDO::FETCH_COLUMN case
        if ($style === 7 || $style === 'PDO::FETCH_COLUMN') {
            $result = $this->stmt->get_result();
            $column = [];
            while ($row = $result->fetch_row()) {
                $column[] = $row[0];
            }
            return $column;
        }
        // Handle PDO::FETCH_ASSOC case
        if ($style === MYSQLI_ASSOC || $style === 'PDO::FETCH_ASSOC' || $style === null) {
            return $this->stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }
        return $this->stmt->get_result()->fetch_all($style);
    }
}

class Database {
    private $host;
    private $username;
    private $password;
    private $db_name;
    private $port;
    private $conn;

    public function __construct() {
        // Get database configuration from environment (server priority) or use defaults
        $this->host = getenv('DB_HOST') ?: '127.0.0.1';
        $this->username = getenv('DB_USER') ?: 'root';
        $this->password = getenv('DB_PASS') ?: '';
        $this->db_name = getenv('DB_NAME') ?: 'programming_tutorials';
        $this->port = getenv('DB_PORT') ?: '3306';
    }

    public function getConnection() {
        $this->conn = null;

        try {
            // Create mysqli connection
            $this->conn = new mysqli();
            
            // Configure SSL for TiDB Cloud
            $this->conn->ssl_set(
                null,   // key
                null,   // cert  
                null,   // ca
                null,   // ca_path
                null    // cipher
            );
            
            // Connect with SSL flags
            $this->conn->real_connect(
                $this->host,
                $this->username,
                $this->password,
                $this->db_name,
                $this->port,
                null,
                MYSQLI_CLIENT_SSL
            );
            
            if ($this->conn->connect_error) {
                throw new Exception("MySQLi connection failed: " . $this->conn->connect_error);
            }
            
            // Set charset
            $this->conn->set_charset('utf8mb4');
            
        } catch(Exception $exception) {
            // Output detailed error for debugging
            echo "Connection error: " . $exception->getMessage();
            echo "<br>Error code: " . $this->conn->connect_errno;
            echo "<br>Host: {$this->host}:{$this->port}";
            die(); // Stop execution to show the error
        }

        return $this->conn;
    }
    
    public function prepare($query) {
        if (!$this->conn) {
            $this->getConnection();
        }
        
        // Convert named parameters to positional
        if (strpos($query, ':') !== false) {
            $query = preg_replace('/:\w+/', '?', $query);
        }
        
        $stmt = $this->conn->prepare($query);
        return new MysqliStatementWrapper($stmt);
    }
    
    public function query($query) {
        if (!$this->conn) {
            $this->getConnection();
        }
        return $this->conn->query($query);
    }
    
    public function escape($string) {
        if (!$this->conn) {
            $this->getConnection();
        }
        return $this->conn->real_escape_string($string);
    }
    
    public function lastInsertId() {
        return $this->conn->insert_id;
    }
    
    public function beginTransaction() {
        $this->conn->begin_transaction();
    }
    
    public function commit() {
        $this->conn->commit();
    }
    
    public function rollBack() {
        $this->conn->rollback();
    }
}
?>
