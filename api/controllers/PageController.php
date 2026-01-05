<?php
require_once __DIR__ . "/../core/response.php";
require_once __DIR__ . "/../core/utils.php";
require_once __DIR__ . "/../core/auth.php";
require_once __DIR__ . "/../config/db.php";

class PageController {
  public static function listPublic(): void {
    $rows = db()->query("SELECT page_key, title, content, updated_at FROM pages ORDER BY id ASC")->fetchAll();
    json_response(["success"=>true,"data"=>$rows]);
  }

  public static function getByKey(string $key): void {
    $stmt = db()->prepare("SELECT page_key, title, content, updated_at FROM pages WHERE page_key=? LIMIT 1");
    $stmt->execute([$key]);
    $row = $stmt->fetch();
    if (!$row) json_response(["success"=>false,"message"=>"Not found"], 404);
    json_response(["success"=>true,"data"=>$row]);
  }

  public static function update(): void {
    require_admin();
    $b = get_json_body();
    $key = trim($b["page_key"] ?? "");
    if (!$key) json_response(["success"=>false,"message"=>"page_key required"], 400);

    $title = $b["title"] ?? null;
    $content = $b["content"] ?? null;

    $pdo = db();
    $stmt = $pdo->prepare("UPDATE pages SET title=COALESCE(?,title), content=COALESCE(?,content) WHERE page_key=?");
    $stmt->execute([$title, $content, $key]);

    json_response(["success"=>true,"message"=>"Page updated"]);
  }
}
