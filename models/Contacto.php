<?php

// models/Contacto.php - Modelo para manejar contactos
class Contacto
{
    private $conn;
    private $table_name = "contactos";

    public $id;
    public $nombre_empresa;
    public $persona_contacto;
    public $email;
    public $telefono;
    public $sector;
    public $mensaje;
    public $fecha_registro;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function crear()
    {
        $query = "INSERT INTO " . $this->table_name . "
                SET nombre_empresa=:nombre_empresa,
                    persona_contacto=:persona_contacto,
                    email=:email,
                    telefono=:telefono,
                    sector=:sector,
                    mensaje=:mensaje,
                    fecha_registro=:fecha_registro";

        $stmt = $this->conn->prepare($query);

        // Limpiar datos
        $this->nombre_empresa = htmlspecialchars(strip_tags($this->nombre_empresa));
        $this->persona_contacto = htmlspecialchars(strip_tags($this->persona_contacto));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->telefono = htmlspecialchars(strip_tags($this->telefono));
        $this->sector = htmlspecialchars(strip_tags($this->sector));
        $this->mensaje = htmlspecialchars(strip_tags($this->mensaje));

        // Bind valores
        $stmt->bindParam(":nombre_empresa", $this->nombre_empresa);
        $stmt->bindParam(":persona_contacto", $this->persona_contacto);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":telefono", $this->telefono);
        $stmt->bindParam(":sector", $this->sector);
        $stmt->bindParam(":mensaje", $this->mensaje);
        $stmt->bindParam(":fecha_registro", $this->fecha_registro);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
