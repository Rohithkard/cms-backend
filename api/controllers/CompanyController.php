<?php
require_once __DIR__ . "/../core/response.php";
require_once __DIR__ . "/../core/utils.php";
require_once __DIR__ . "/../core/auth.php";
require_once __DIR__ . "/../core/upload.php";
require_once __DIR__ . "/../config/db.php";

class CompanyController {
  public static function get(): void {
    $pdo = db();
    $row = $pdo->query("SELECT * FROM company_info LIMIT 1")->fetch();
    json_response(["success"=>true, "data"=>$row]);
  }

  public static function update(): void {
    require_admin();
    $body = get_json_body();

    $allowed = [
      "company_name","logo_url","phone","email","address","website","map_url",
      "whatsapp","facebook","instagram","youtube","linkedin"
    ];

    $pdo = db();
    $current = $pdo->query("SELECT * FROM company_info LIMIT 1")->fetch();
    if (!$current) json_response(["success"=>false,"message"=>"company_info missing"], 500);

    $updates = [];
    $params = [];

    foreach ($allowed as $k) {
      if (array_key_exists($k, $body)) {
        $updates[] = "$k = ?";
        $params[] = $body[$k];
      }
    }

    if (!$updates) json_response(["success"=>false,"message"=>"No fields provided"], 400);

    $sql = "UPDATE company_info SET ".implode(",", $updates)." WHERE id = ?";
    $params[] = $current["id"];
    $pdo->prepare($sql)->execute($params);

    $row = $pdo->query("SELECT * FROM company_info LIMIT 1")->fetch();
    json_response(["success"=>true, "message"=>"Updated", "data"=>$row]);
  }

  public static function uploadImage(): void {
    require_admin();
    $res = handle_image_upload("image");
    if (!$res["ok"]) json_response(["success"=>false,"message"=>$res["message"]], 400);
    json_response(["success"=>true,"url"=>$res["url"]]);
  }
}
