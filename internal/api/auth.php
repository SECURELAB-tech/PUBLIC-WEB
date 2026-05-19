<?php
// api/auth.php — Login
// ⚠️ VULN 1: SQL Injection (extracción de datos, NO bypass de login)
//
// El campo username se concatena directamente en la query sin sanitizar.
// El login en sí compara el password en PHP, así que no sirve para saltarse la auth,
// pero sí permite extraer datos de la BD vía UNION.
//
// Payload de extracción (UNION-based):
//   username: ' UNION SELECT id, username, password, role FROM users-- -
//   password: (cualquier cosa)
//
// La respuesta de error devuelve el mensaje de MySQL con los datos inyectados,
// o bien se puede usar con sqlmap: sqlmap -u http://target/internal/api/auth.php
//   --data="username=test&password=test" -p username --dbs
//
// El atacante obtiene los hashes SHA-256 de todos los usuarios,
// los crackea offline (ej: hashcat, CrackStation) y entra con credenciales reales
// como employee1 → acceso legítimo como usuario normal.
 
session_start();
header("Content-Type: application/json");
require "db.php";
 
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit();
}
 
$username = $_POST["username"] ?? "";
$password = $_POST["password"] ?? "";
 
// ⚠️ VULNERABILIDAD: $username sin escapar → SQLi
// El error de MySQL se expone directamente → error-based SQLi también funciona
$query = "SELECT id, username, password, role FROM users WHERE username = '$username'";
 
$result = $conn->query($query);
 
if (!$result) {
    // El mensaje de error de MySQL se filtra — útil para error-based SQLi
    http_response_code(500);
    echo json_encode(["error" => "DB error: " . $conn->error]);
    exit();
}
 
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
 
    // La comparación de contraseña se hace en PHP, NO en SQL
    // → Un UNION o -- no sirve para saltarse este check
    if ($user["password"] === hash("sha256", $password)) {
        $_SESSION["user_id"]   = $user["id"];
        $_SESSION["username"]  = $user["username"];
        $_SESSION["full_name"] = $user["full_name"] ?? $user["username"];
        $_SESSION["role"]      = $user["role"];
 
        echo json_encode([
            "success"  => true,
            "role"     => $user["role"],
            "redirect" => $user["role"] === "administrator" ? "/internal/admin/" : "/internal/dashboard.html"
        ]);
    } else {
        http_response_code(401);
        echo json_encode(["success" => false, "error" => "Credenciales incorrectas"]);
    }
} else {
    http_response_code(401);
    echo json_encode(["success" => false, "error" => "Credenciales incorrectas"]);
}
 
$conn->close();
 
