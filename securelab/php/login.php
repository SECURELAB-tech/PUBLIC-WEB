<?php
// ============================================================
//  SecureLab - Login handler
// ============================================================
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.html');
    exit;
}

$email    = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if (empty($email) || empty($password)) {
    header('Location: ../login.html?error=campos_vacios');
    exit;
}

$conn = conectarDB();

$stmt = $conn->prepare("SELECT id, nombre, password FROM usuarios WHERE email = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario   = $resultado->fetch_assoc();
$stmt->close();
$conn->close();

if (!$usuario || !password_verify($password, $usuario['password'])) {
    header('Location: ../login.html?error=credenciales');
    exit;
}

// Guardar sesión
$_SESSION['user_id']   = $usuario['id'];
$_SESSION['user_name'] = $usuario['nombre'];

header('Location: ../dashboard.php');
exit;
