<?php
require_once __DIR__ . "/../core/response.php";
require_once __DIR__ . "/../core/utils.php";
require_once __DIR__ . "/../config/db.php";

class AuthController {
  public static function login(): void {
    $body = get_json_body();
    $email = trim($body["email"] ?? "");
    $password = $body["password"] ?? "";

    if (!$email || !$password) {
      json_response(["success"=>false,"message"=>"Email & password required"], 400);
    }

    $pdo = db();
    $stmt = $pdo->prepare("SELECT id, email, full_name, password_hash, is_active FROM admins WHERE email=? LIMIT 1");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    if (!$admin || !$admin["is_active"] || !password_verify($password, $admin["password_hash"])) {
      json_response(["success"=>false,"message"=>"Invalid login"], 401);
    }

    $token = bin2hex(random_bytes(32));
    $hash = hash("sha256", $token);

    $cfg = (require __DIR__ . "/../config/config.php")["auth"];
    $days = (int)$cfg["token_days"];
    $expires = (new DateTime())->modify("+{$days} days")->format("Y-m-d H:i:s");

    $pdo->prepare("INSERT INTO admin_tokens (admin_id, token_hash, expires_at) VALUES (?,?,?)")
        ->execute([$admin["id"], $hash, $expires]);

    json_response([
      "success" => true,
      "token" => $token,
      "expires_at" => $expires,
      "admin" => [
        "id" => (int)$admin["id"],
        "email" => $admin["email"],
        "full_name" => $admin["full_name"]
      ]
    ]);
  }
}
