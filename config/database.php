<?php
// config/database.php - Configuración de la base de datos
class Database
{
    private $host = "localhost";
    private $database_name = "xpertodc_xpertod_db";
    private $username = "xpertodc_contacto";
    private $password = "9YeagB{3%ErH";
    private $conn;

    public function getConnection()
    {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->database_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch (PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
