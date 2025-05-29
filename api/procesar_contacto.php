<?php
// api/procesar_contacto.php - Endpoint para procesar el formulario
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://xpertod.com');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Rate limiting básico
session_start();
$now = time();
$window = 60; // 1 minuto
$max_requests = 3;

if (!isset($_SESSION['requests'])) {
    $_SESSION['requests'] = [];
}

// Limpiar requests antiguos
$_SESSION['requests'] = array_filter($_SESSION['requests'], function($time) use ($now, $window) {
    return ($now - $time) < $window;
});

if (count($_SESSION['requests']) >= $max_requests) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Demasiadas solicitudes. Intenta en un minuto.']);
    exit;
}

$_SESSION['requests'][] = $now;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include_once '../config/database.php';
    include_once '../models/Contacto.php';

    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        error_log("Fallo de conexión a BD en " . date('Y-m-d H:i:s'));
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
        exit;
    }

    $contacto = new Contacto($db);

    // Obtener y validar datos del POST
    $contacto->nombre_empresa = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING) ?? '';
    $contacto->persona_contacto = filter_input(INPUT_POST, 'contacto', FILTER_SANITIZE_STRING) ?? '';
    $contacto->email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?? '';
    $contacto->telefono = filter_input(INPUT_POST, 'telefono', FILTER_SANITIZE_STRING) ?? '';
    $contacto->sector = filter_input(INPUT_POST, 'sector', FILTER_SANITIZE_STRING) ?? '';
    $contacto->mensaje = filter_input(INPUT_POST, 'mensaje', FILTER_SANITIZE_STRING) ?? '';
    $contacto->fecha_registro = date('Y-m-d H:i:s');

    // Validaciones mejoradas
    $errors = [];

    if (empty($contacto->nombre_empresa) || strlen($contacto->nombre_empresa) < 2) {
        $errors[] = 'Nombre de empresa inválido';
    }

    if (empty($contacto->persona_contacto) || strlen($contacto->persona_contacto) < 2) {
        $errors[] = 'Persona de contacto inválida';
    }

    if (empty($contacto->email) || !filter_var($contacto->email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email inválido';
    }

    if (empty($contacto->mensaje) || strlen($contacto->mensaje) < 10) {
        $errors[] = 'Mensaje muy corto (mínimo 10 caracteres)';
    }

    if (!empty($contacto->telefono) && !preg_match('/^[\+]?[0-9\s\-\(\)]{7,15}$/', $contacto->telefono)) {
        $errors[] = 'Formato de teléfono inválido';
    }

    if (!empty($errors)) {
        echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
        exit;
    }

    // Intentar crear el contacto
    try {
        if ($contacto->crear()) {
            // Log exitoso
            error_log("Contacto creado: " . $contacto->email . " - " . date('Y-m-d H:i:s'));
            echo json_encode(['success' => true, 'message' => 'Consulta enviada exitosamente']);
        } else {
            error_log("Error al crear contacto: " . $contacto->email . " - " . date('Y-m-d H:i:s'));
            echo json_encode(['success' => false, 'message' => 'Error al procesar la consulta']);
        }
    } catch (Exception $e) {
        error_log("Excepción al crear contacto: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}