<?php

// Load environment variables
require_once __DIR__ . '/env_loader.php';

/**
 * Database connection class for TiDB Cloud compatibility
 * Uses mysqli with SSL for secure connections
 * Works with both local .env and production environment variables
 */
class Database {
    
    private $host;
    private $username;
    private $password;
    private $db_name;
    private $port;
    private $ssl_ca;
    private $ssl_key;
    private $ssl_cert;
    private $ssl_verify;
    
    public $conn;

    public function __construct() {
        // Load from environment variables (prioritize server env over .env)
        $this->host = getenv('DB_HOST') ?: '127.0.0.1';
        $this->username = getenv('DB_USER') ?: 'root';
        $this->password = getenv('DB_PASS') ?: '';
        $this->db_name = getenv('DB_NAME') ?: 'blood_donation_db';
        $this->port = getenv('DB_PORT') ?: '3306';
        
        // TiDB Cloud SSL settings
        $this->ssl_ca = getenv('DB_SSL_CA') ?: null;
        $this->ssl_key = getenv('DB_SSL_KEY') ?: null;
        $this->ssl_cert = getenv('DB_SSL_CERT') ?: null;
        $this->ssl_verify = getenv('DB_SSL_VERIFY') !== 'false'; // Default to true
    }

    public function getConnection() {
        $this->conn = null;

        try {
            // Create mysqli connection with SSL
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            
            // Build connection string with SSL
            $connection_string = sprintf(
                "mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4;ssl_mode=REQUIRED",
                $this->host,
                $this->port,
                $this->db_name
            );
            
            // PDO options for SSL
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            // Add SSL options for TiDB Cloud
            if ($this->ssl_ca || $this->ssl_key || $this->ssl_cert) {
                $ssl_options = [];
                
                if ($this->ssl_ca) {
                    $ssl_options[PDO::MYSQL_ATTR_SSL_CA] = $this->ssl_ca;
                }
                
                if ($this->ssl_key) {
                    $ssl_options[PDO::MYSQL_ATTR_SSL_KEY] = $this->ssl_key;
                }
                
                if ($this->ssl_cert) {
                    $ssl_options[PDO::MYSQL_ATTR_SSL_CERT] = $this->ssl_cert;
                }
                
                $ssl_options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = $this->ssl_verify;
                
                $options = array_merge($options, $ssl_options);
            } else {
                // Basic SSL for TiDB Cloud when no specific certs provided
                // Use system CA bundle or disable verification for TiDB Cloud
                $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
            }
            
            $this->conn = new PDO($connection_string, $this->username, $this->password, $options);
            
            // Test connection
            $this->conn->query("SELECT 1");
            
        } catch(PDOException $exception) {
            // Log the error for debugging
            error_log("Database connection failed: " . $exception->getMessage());
            error_log("Connection details: Host={$this->host}, Port={$this->port}, DB={$this->db_name}");
            
            // Return JSON error response for API endpoints
            if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
                header('Content-Type: application/json');
                echo json_encode(array(
                    "status" => "error", 
                    "message" => "Database connection failed",
                    "debug" => $exception->getMessage()
                ));
            } else {
                echo "Database connection failed. Please check configuration.";
            }
            return null;
        }

        return $this->conn;
    }
    
    /**
     * Alternative mysqli connection for better SSL control
     */
    public function getMysqliConnection() {
        try {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            
            $conn = mysqli_init();
            
            // Configure SSL
            if ($this->ssl_ca || $this->ssl_key || $this->ssl_cert) {
                mysqli_ssl_set(
                    $conn,
                    $this->ssl_key,
                    $this->ssl_cert,
                    $this->ssl_ca,
                    null,
                    null
                );
            } else {
                // For TiDB Cloud, use SSL without verification
                mysqli_ssl_set(
                    $conn,
                    null,
                    null,
                    null,
                    null,
                    null
                );
            }
            
            // Connect with timeout
            mysqli_real_connect(
                $conn,
                $this->host,
                $this->username,
                $this->password,
                $this->db_name,
                $this->port,
                null,
                MYSQLI_CLIENT_SSL
            );
            
            // Set charset
            mysqli_set_charset($conn, 'utf8mb4');
            
            return $conn;
            
        } catch(mysqli_sql_exception $exception) {
            error_log("MySQLi connection failed: " . $exception->getMessage());
            return null;
        }
    }
}

?>
