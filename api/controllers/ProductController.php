<?php
require_once __DIR__ . "/../core/response.php";
require_once __DIR__ . "/../core/utils.php";
require_once __DIR__ . "/../core/auth.php";
require_once __DIR__ . "/../config/db.php";

class ProductController {
  public static function listPublic(): void {
    $pdo = db();
    $q = trim($_GET["q"] ?? "");
    $category = trim($_GET["category"] ?? "");
    $limit = min(100, max(1, (int)($_GET["limit"] ?? 30)));
    $offset = max(0, (int)($_GET["offset"] ?? 0));

    $where = "is_active=1";
    $params = [];

    if ($q !== "") { $where .= " AND (name LIKE ? OR description LIKE ? OR sku LIKE ?)"; $params[]="%$q%"; $params[]="%$q%"; $params[]="%$q%"; }
    if ($category !== "") { $where .= " AND category = ?"; $params[]=$category; }

    $stmt = $pdo->prepare("SELECT * FROM products WHERE $where ORDER BY id DESC LIMIT $limit OFFSET $offset");
    $stmt->execute($params);
    json_response(["success"=>true, "data"=>$stmt->fetchAll()]);
  }

  public static function viewPublic(): void {
    $pdo = db();
    $id = (int)($_GET["id"] ?? 0);
    $slug = trim($_GET["slug"] ?? "");

    if (!$id && !$slug) json_response(["success"=>false,"message"=>"id or slug required"], 400);

    if ($id) {
      $stmt = $pdo->prepare("SELECT * FROM products WHERE id=? AND is_active=1 LIMIT 1");
      $stmt->execute([$id]);
    } else {
      $stmt = $pdo->prepare("SELECT * FROM products WHERE slug=? AND is_active=1 LIMIT 1");
      $stmt->execute([$slug]);
    }

    $row = $stmt->fetch();
    if (!$row) json_response(["success"=>false,"message"=>"Not found"], 404);
    json_response(["success"=>true, "data"=>$row]);
  }

  public static function listAdmin(): void {
    require_admin();
    $rows = db()->query("SELECT * FROM products ORDER BY id DESC")->fetchAll();
    json_response(["success"=>true, "data"=>$rows]);
  }

  public static function create(): void {
    require_admin();
    $b = get_json_body();
    $name = trim($b["name"] ?? "");
    if (!$name) json_response(["success"=>false,"message"=>"name required"], 400);

    $slug = $b["slug"] ?? slugify($name);

    $pdo = db();
    $stmt = $pdo->prepare("
      INSERT INTO products (name, slug, image_url, description, short_description, sku, price, mrp, stock, unit, category, brand, is_active)
      VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)
    ");
    $stmt->execute([
      $name,
      $slug,
      $b["image_url"] ?? null,
      $b["description"] ?? null,
      $b["short_description"] ?? null,
      $b["sku"] ?? null,
      $b["price"] ?? null,
      $b["mrp"] ?? null,
      $b["stock"] ?? null,
      $b["unit"] ?? null,
      $b["category"] ?? null,
      $b["brand"] ?? null,
      (int)($b["is_active"] ?? 1)
    ]);

    json_response(["success"=>true,"message"=>"Product created","id"=>(int)$pdo->lastInsertId(),"slug"=>$slug]);
  }

  public static function update(): void {
    require_admin();
    $b = get_json_body();
    $id = (int)($b["id"] ?? 0);
    if (!$id) json_response(["success"=>false,"message"=>"id required"], 400);

    $allowed = ["name","slug","image_url","description","short_description","sku","price","mrp","stock","unit","category","brand","is_active"];
    $set = [];
    $params = [];

    foreach ($allowed as $k) {
      if (array_key_exists($k, $b)) { $set[] = "$k=?"; $params[] = $b[$k]; }
    }
    if (!$set) json_response(["success"=>false,"message"=>"No fields provided"], 400);

    $params[] = $id;
    db()->prepare("UPDATE products SET ".implode(",", $set)." WHERE id=?")->execute($params);

    json_response(["success"=>true,"message"=>"Product updated"]);
  }

  public static function delete(): void {
    require_admin();
    $b = get_json_body();
    $id = (int)($b["id"] ?? 0);
    if (!$id) json_response(["success"=>false,"message"=>"id required"], 400);

    db()->prepare("DELETE FROM products WHERE id=?")->execute([$id]);
    json_response(["success"=>true,"message"=>"Product deleted"]);
  }
}
