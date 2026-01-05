<?php
require_once __DIR__ . "/../core/response.php";
require_once __DIR__ . "/../core/utils.php";
require_once __DIR__ . "/../core/auth.php";
require_once __DIR__ . "/../config/db.php";

class BannerController {
  public static function listPublic(): void {
    $type = $_GET["type"] ?? null;
    $pdo = db();

    $where = "is_active=1 AND (start_date IS NULL OR start_date <= CURDATE()) AND (end_date IS NULL OR end_date >= CURDATE())";
    $params = [];

    if ($type) { $where .= " AND banner_type = ?"; $params[] = $type; }

    $stmt = $pdo->prepare("SELECT * FROM banners WHERE $where ORDER BY sort_order ASC, id DESC");
    $stmt->execute($params);
    json_response(["success"=>true, "data"=>$stmt->fetchAll()]);
  }

  public static function listAdmin(): void {
    require_admin();
    $pdo = db();
    $rows = $pdo->query("SELECT * FROM banners ORDER BY id DESC")->fetchAll();
    json_response(["success"=>true, "data"=>$rows]);
  }

  public static function create(): void {
    require_admin();
    $b = get_json_body();

    $need = ["banner_type","image_url"];
    foreach ($need as $k) if (!($b[$k] ?? null)) json_response(["success"=>false,"message"=>"$k required"], 400);

    $pdo = db();
    $stmt = $pdo->prepare("
      INSERT INTO banners (banner_type,title,subtitle,image_url,link_url,sort_order,is_active,start_date,end_date)
      VALUES (?,?,?,?,?,?,?,?,?)
    ");
    $stmt->execute([
      $b["banner_type"],
      $b["title"] ?? null,
      $b["subtitle"] ?? null,
      $b["image_url"],
      $b["link_url"] ?? null,
      (int)($b["sort_order"] ?? 0),
      (int)($b["is_active"] ?? 1),
      $b["start_date"] ?? null,
      $b["end_date"] ?? null,
    ]);

    json_response(["success"=>true,"message"=>"Banner created","id"=>(int)$pdo->lastInsertId()]);
  }

  public static function update(): void {
    require_admin();
    $b = get_json_body();
    $id = (int)($b["id"] ?? 0);
    if (!$id) json_response(["success"=>false,"message"=>"id required"], 400);

    $allowed = ["banner_type","title","subtitle","image_url","link_url","sort_order","is_active","start_date","end_date"];
    $set = [];
    $params = [];

    foreach ($allowed as $k) {
      if (array_key_exists($k, $b)) { $set[] = "$k=?"; $params[] = $b[$k]; }
    }
    if (!$set) json_response(["success"=>false,"message"=>"No fields provided"], 400);

    $params[] = $id;
    db()->prepare("UPDATE banners SET ".implode(",", $set)." WHERE id=?")->execute($params);

    json_response(["success"=>true,"message"=>"Banner updated"]);
  }

  public static function delete(): void {
    require_admin();
    $b = get_json_body();
    $id = (int)($b["id"] ?? 0);
    if (!$id) json_response(["success"=>false,"message"=>"id required"], 400);

    db()->prepare("DELETE FROM banners WHERE id=?")->execute([$id]);
    json_response(["success"=>true,"message"=>"Banner deleted"]);
  }
}
