<?php
require_once __DIR__ . "/../config/db.php";

function bearer_token(): ?string {
  $headers = getallheaders();
  $auth = $headers["Authorization"] ?? $headers["authorization"] ?? null;
  if (!$auth) return null;
  if (preg_match('/Bearer\s(\S+)/', $auth, $m)) return $m[1];
  return null;
}

function require_admin(): array {
  $token = bearer_token();
  if (!$token) json_response(["success"=>false,"message"=>"Missing token"], 401);

  $hash = hash("sha256", $token);
  $pdo = db();

  $stmt = $pdo->prepare("
    SELECT a.id, a.email, a.full_name
    FROM admin_tokens t
    JOIN admins a ON a.id = t.admin_id
    WHERE t.token_hash = ? AND t.expires_at > NOW() AND a.is_active = 1
    LIMIT 1
  ");
  $stmt->execute([$hash]);
  $admin = $stmt->fetch();
  if (!$admin) json_response(["success"=>false,"message"=>"Invalid/expired token"], 401);

  return $admin;
}
