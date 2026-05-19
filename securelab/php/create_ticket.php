<?php
// ============================================================
//  SecureLab - Crear ticket + subida de archivo
// ============================================================
require_once 'config.php';

// Proteger la ruta: solo usuarios autenticados
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.html');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../dashboard.php');
    exit;
}

$titulo      = trim($_POST['titulo']      ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$usuario_id  = $_SESSION['user_id'];

if (empty($titulo) || empty($descripcion)) {
    header('Location: ../dashboard.php?error=campos_vacios');
    exit;
}

$archivo_nombre = null;
$archivo_ruta   = null;

// --- Manejo del archivo adjunto ---
if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {

    $nombre_original = basename($_FILES['archivo']['name']);
    $nombre_seguro   = time() . '_' . $nombre_original;   // prefijo temporal único
    $destino         = UPLOADS_DIR . $nombre_seguro;

    if (move_uploaded_file($_FILES['archivo']['tmp_name'], $destino)) {
        $archivo_nombre = $nombre_original;
        $archivo_ruta   = 'uploads/' . $nombre_seguro;    // ruta relativa guardada en BD
    }
}

// --- Guardar ticket en base de datos ---
$conn = conectarDB();
$stmt = $conn->prepare(
    "INSERT INTO tickets (usuario_id, titulo, descripcion, archivo_nombre, archivo_ruta)
     VALUES (?, ?, ?, ?, ?)"
);
$stmt->bind_param('issss', $usuario_id, $titulo, $descripcion, $archivo_nombre, $archivo_ruta);
$stmt->execute();
$stmt->close();
$conn->close();

header('Location: ../dashboard.php?ok=ticket_creado');
exit;
