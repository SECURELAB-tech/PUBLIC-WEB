<?php
// ============================================================
//  SecureLab - Servidor de archivos
//
//  ⚠️  VULNERABILIDAD: Local File Inclusion (LFI)
//  -----------------------------------------------
//  El parámetro ?file= NO está sanitizado.
//  Un atacante puede escapar del directorio uploads/
//  usando secuencias de "../" para leer cualquier
//  archivo del sistema:
//
//  USO NORMAL:
//    /php/file.php?file=uploads/1234_foto.jpg
//
//  EXPLOTACIÓN (LFI):
//    /php/file.php?file=../../../etc/passwd
//    /php/file.php?file=../php/config.php
//    /php/file.php?file=../../../etc/hosts
//
//  CAUSA: readfile() recibe la ruta sin validar.
//  SOLUCIÓN: usar realpath() + comprobar que la ruta
//            resultante empiece por UPLOADS_DIR.
// ============================================================
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.html');
    exit;
}

// -------------------------------------------------------
// CÓDIGO VULNERABLE — el parámetro llega directo a readfile
// -------------------------------------------------------
$file = $_GET['file'] ?? '';

if (empty($file)) {
    http_response_code(400);
    echo 'Error: no se especificó ningún archivo.';
    exit;
}

// Construir ruta absoluta desde la raíz del proyecto
$ruta = BASE_PATH . '/' . $file;   // ← esto es lo que cambia

if (!file_exists($ruta)) {
    http_response_code(404);
    echo 'Archivo no encontrado: ' . htmlspecialchars($file);
    exit;
}

$mime = mime_content_type($ruta) ?: 'application/octet-stream';
header('Content-Type: ' . $mime);
header('Content-Disposition: inline; filename="' . basename($ruta) . '"');

readfile($ruta);
exit;


// -------------------------------------------------------
// VERSIÓN CORREGIDA (descomenta para el fix en clase)
// -------------------------------------------------------
/*
$file         = $_GET['file'] ?? '';
$ruta_real    = realpath(BASE_PATH . '/' . $file);
$uploads_real = realpath(UPLOADS_DIR);

// Verificar que la ruta resuelta esté dentro de uploads/
if ($ruta_real === false || strpos($ruta_real, $uploads_real) !== 0) {
    http_response_code(403);
    echo 'Acceso denegado.';
    exit;
}

$mime = mime_content_type($ruta_real) ?: 'application/octet-stream';
header('Content-Type: ' . $mime);
header('Content-Disposition: inline; filename="' . basename($ruta_real) . '"');
readfile($ruta_real);
exit;
*/
