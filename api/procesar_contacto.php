<?php

// api/procesar_contacto.php - Endpoint para procesar el formulario
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    include_once '../config/database.php';
    include_once '../models/Contacto.php';

    $database = new Database();
    $db = $database->getConnection();

    if ($db) {
        $contacto = new Contacto($db);

        // Obtener datos del POST
        $contacto->nombre_empresa = $_POST['nombre'] ?? '';
        $contacto->persona_contacto = $_POST['contacto'] ?? '';
        $contacto->email = $_POST['email'] ?? '';
        $contacto->telefono = $_POST['telefono'] ?? '';
        $contacto->sector = $_POST['sector'] ?? '';
        $contacto->mensaje = $_POST['mensaje'] ?? '';
        $contacto->fecha_registro = date('Y-m-d H:i:s');

        // Validar campos requeridos
        if (
            empty($contacto->nombre_empresa) || empty($contacto->persona_contacto) ||
            empty($contacto->email) || empty($contacto->mensaje)
        ) {
            echo json_encode(['success' => false, 'message' => 'Todos los campos obligatorios deben estar completos']);
            exit;
        }

        // Validar email
        if (!filter_var($contacto->email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'El formato del email no es válido']);
            exit;
        }

        // Intentar crear el contacto
        if ($contacto->crear()) {
            echo json_encode(['success' => true, 'message' => 'Consulta enviada exitosamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al procesar la consulta']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>
?>