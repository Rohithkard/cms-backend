<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../core/response.php";

$email = $_GET["email"] ?? "admin@example.com";
$pass  = $_GET["pass"] ?? "Admin@123";
$name  = $_GET["name"] ?? "Admin";

$hash = password_hash($pass, PASSWORD_BCRYPT);

$pdo = db();
$stmt = $pdo->prepare("INSERT INTO admins (full_name,email,password_hash) VALUES (?,?,?)");
try {
  $stmt->execute([$name, $email, $hash]);
  json_response(["success"=>true,"message"=>"Admin created","email"=>$email,"password"=>$pass]);
} catch (Exception $e) {
  json_response(["success"=>false,"message"=>$e->getMessage()], 400);
}
