<?php
$db_host = "10.2.0.5";
$db_user = "app_user";
$db_pass = "Password12345";
$db_name = "securelab";
 
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "DB connection failed"]);
    exit();
}
