<?php
// api/session.php — Devuelve datos de la sesión activa
session_start();
header("Content-Type: application/json");
 
if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(["authenticated" => false]);
    exit();
}
 
echo json_encode([
    "authenticated" => true,
    "user_id"       => $_SESSION["user_id"],
    "username"      => $_SESSION["username"],
    "full_name"     => $_SESSION["full_name"],
    "role"          => $_SESSION["role"]
]);
 
