<?php
// api/profile.php — Ver y actualizar perfil propio
// ⚠️ VULN 2: Mass Assignment
// El UPDATE usa los campos del POST sin filtrar.
// Un empleado puede añadir role=administrator al request y escalar privilegios.
// Exploit: POST /api/profile.php con body: phone=123&role=administrator
 
session_start();
header("Content-Type: application/json");
require "db.php";
 
if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(["error" => "No autenticado"]);
    exit();
}
 
$id = $_SESSION["user_id"];
 
// GET → devuelve perfil del usuario actual
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $result = $conn->query("SELECT id, username, full_name, email, department, phone, role FROM users WHERE id = $id");
    echo json_encode($result->fetch_assoc());
    exit();
}
 
// POST → actualiza perfil
// ⚠️ VULNERABILIDAD: los campos del POST se insertan directamente en la query
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $allowed = ["full_name", "email", "phone", "role"]; // "role" no debería estar aquí
    $sets = [];
 
    foreach ($allowed as $field) {
        if (isset($_POST[$field])) {
            $val = $conn->real_escape_string($_POST[$field]);
            $sets[] = "$field = '$val'";
        }
    }
 
    if (empty($sets)) {
        echo json_encode(["error" => "Nada que actualizar"]);
        exit();
    }
 
    $conn->query("UPDATE users SET " . implode(", ", $sets) . " WHERE id = $id");
    echo json_encode(["success" => true, "message" => "Perfil actualizado"]);
    exit();
}
 
$conn->close();
