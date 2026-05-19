<?php
// ============================================================
//  SecureLab - Registro de usuario
// ============================================================
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../register.html');
    exit;
}

$nombre   = trim($_POST['nombre']   ?? '');
$email    = trim($_POST['email']    ?? '');
$password = trim($_POST['password'] ?? '');

if (empty($nombre) || empty($email) || empty($password)) {
    header('Location: ../register.html?error=campos_vacios');
    exit;
}

if (strlen($password) < 6) {
    header('Location: ../register.html?error=password_corta');
    exit;
}

$conn = conectarDB();

// Comprobar si el email ya existe
$stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->close();
    $conn->close();
    header('Location: ../register.html?error=email_existe');
    exit;
}
$stmt->close();

// Insertar nuevo usuario con contraseña hasheada
$hash = password_hash($password, PASSWORD_BCRYPT);
$stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)");
$stmt->bind_param('sss', $nombre, $email, $hash);
$stmt->execute();
$nuevo_id = $stmt->insert_id;
$stmt->close();
$conn->close();

// Autologin tras el registro
$_SESSION['user_id']   = $nuevo_id;
$_SESSION['user_name'] = $nombre;

header('Location: ../dashboard.php');
exit;
