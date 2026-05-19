<?php
// ============================================================
//  SecureLab - Configuración de base de datos
// ============================================================

define('DB_HOST', '10.2.0.5');
define('DB_USER', 'app_user');
define('DB_PASS', 'Password12345');
define('DB_NAME', 'securelab');

function conectarDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die(json_encode(['error' => 'No se pudo conectar a la base de datos.']));
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}

// Inicia sesión si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ruta base del proyecto (ajusta si cambias de carpeta)
define('BASE_PATH', dirname(__DIR__));
define('UPLOADS_DIR', BASE_PATH . '/uploads/');
