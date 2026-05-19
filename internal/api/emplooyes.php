<?php
// api/employees.php — Listado de empleados (solo administradores)
session_start();
header("Content-Type: application/json");
require "db.php";
 
if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(["error" => "No autenticado"]);
    exit();
}
 
if ($_SESSION["role"] !== "administrator") {
    http_response_code(403);
    echo json_encode(["error" => "Acceso denegado"]);
    exit();
}
 
$result = $conn->query("SELECT id, username, full_name, email, department, phone, role FROM users ORDER BY id ASC");
$users  = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}
 
echo json_encode($users);
$conn->close();
