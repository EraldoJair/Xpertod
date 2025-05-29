<?php
// config/database.php - Configuración de la base de datos
class Database
{
    private $host;
    private $database_name;
    private $username;
    private $password;
    private $conn;

    public function __construct()
    {
        // Cargar variables de entorno
        $this->loadEnv();
        
        $this->host = $_ENV['DB_HOST'] ?? 'localhost';
        $this->database_name = $_ENV['DB_NAME'] ?? '';
        $this->username = $_ENV['DB_USER'] ?? '';
        $this->password = $_ENV['DB_PASS'] ?? '';
    }

    private function loadEnv()
    {
        if (file_exists(__DIR__ . '/../.env')) {
            $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    $_ENV[trim($key)] = trim($value);
                }
            }
        }
    }

    public function getConnection()
    {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->database_name . ";charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $exception) {
            error_log("Error de conexión DB: " . $exception->getMessage());
            return null;
        }
        return $this->conn;
    }
}