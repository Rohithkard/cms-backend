<?php
require_once __DIR__ . "/../core/response.php";
require_once __DIR__ . "/../core/utils.php";
require_once __DIR__ . "/../core/auth.php";
require_once __DIR__ . "/../core/upload.php";
require_once __DIR__ . "/../core/url.php";
require_once __DIR__ . "/../config/db.php";

class BannerController
{
    /* =========================
       PUBLIC – LIST BANNERS
    ========================= */
    public static function listPublic(): void
    {
        $type = $_GET["type"] ?? null;
        $pdo = db();

        $where = "
            is_active = 1
            AND (start_date IS NULL OR start_date <= CURDATE())
            AND (end_date IS NULL OR end_date >= CURDATE())
        ";
        $params = [];

        if ($type) {
            $where .= " AND banner_type = ?";
            $params[] = $type;
        }

        $stmt = $pdo->prepare("
            SELECT *
            FROM banners
            WHERE $where
            ORDER BY sort_order ASC, id DESC
        ");
        $stmt->execute($params);

        $rows = $stmt->fetchAll();

        // ✅ ADD BASE URL
        foreach ($rows as &$row) {
            $row["image_url"] = asset_url($row["image_url"]);
        }

        json_response([
            "success" => true,
            "data" => $rows
        ]);
    }

    /* =========================
       ADMIN – LIST
    ========================= */
    public static function listAdmin(): void
    {
        require_admin();
        $rows = db()->query("SELECT * FROM banners ORDER BY id DESC")->fetchAll();

        foreach ($rows as &$row) {
            $row["image_url"] = asset_url($row["image_url"]);
        }

        json_response([
            "success" => true,
            "data" => $rows
        ]);
    }

    /* =========================
       ADMIN – CREATE (FILE UPLOAD)
    ========================= */
    public static function create(): void
    {
        require_admin();

        $bannerType = $_POST["banner_type"] ?? null;
        if (!$bannerType) {
            json_response(["success" => false, "message" => "banner_type required"], 400);
        }

        // 📷 Upload image (required)
        if (empty($_FILES["image"])) {
            json_response(["success" => false, "message" => "image required"], 400);
        }

        $res = handle_image_upload("image");
        if (!$res["ok"]) {
            json_response(["success" => false, "message" => $res["message"]], 400);
        }

        $imageUrl = $res["url"];

        db()->prepare("
            INSERT INTO banners (
              banner_type, title, subtitle, image_url,
              link_url, sort_order, is_active,
              start_date, end_date
            ) VALUES (?,?,?,?,?,?,?,?,?)
        ")->execute([
            $bannerType,
            $_POST["title"] ?? null,
            $_POST["subtitle"] ?? null,
            $imageUrl,
            $_POST["link_url"] ?? null,
            (int)($_POST["sort_order"] ?? 0),
            (int)($_POST["is_active"] ?? 1),
            $_POST["start_date"] ?? null,
            $_POST["end_date"] ?? null
        ]);

        json_response([
            "success" => true,
            "message" => "Banner created",
            "id" => (int)db()->lastInsertId(),
            "image_url" => asset_url($imageUrl)
        ]);
    }

    /* =========================
       ADMIN – UPDATE
    ========================= */
    public static function update(): void
    {
        require_admin();

        $id = (int)($_POST["id"] ?? 0);
        if (!$id) {
            json_response(["success" => false, "message" => "id required"], 400);
        }

        // ✅ VERIFY BANNER EXISTS
        $chk = db()->prepare("SELECT id FROM banners WHERE id = ? LIMIT 1");
        $chk->execute([$id]);
        if (!$chk->fetch()) {
            json_response(["success" => false, "message" => "Invalid banner id"], 400);
        }

        $allowed = [
            "banner_type","title","subtitle",
            "link_url","sort_order","is_active",
            "start_date","end_date"
        ];

        $set = [];
        $params = [];

        foreach ($allowed as $f) {
            if (isset($_POST[$f])) {
                $set[] = "$f = ?";
                $params[] = $_POST[$f];
            }
        }

        // 📷 Optional image update
        if (!empty($_FILES["image"])) {
            $res = handle_image_upload("image");
            if (!$res["ok"]) {
                json_response(["success" => false, "message" => $res["message"]], 400);
            }
            $set[] = "image_url = ?";
            $params[] = $res["url"];
        }

        if (!$set) {
            json_response(["success" => false, "message" => "No fields provided"], 400);
        }

        $params[] = $id;
        db()->prepare(
            "UPDATE banners SET " . implode(",", $set) . " WHERE id = ?"
        )->execute($params);

        json_response([
            "success" => true,
            "message" => "Banner updated"
        ]);
    }

    /* =========================
       ADMIN – DELETE
    ========================= */
    public static function delete(): void
    {
        require_admin();
        $b = get_json_body();
        $id = (int)($b["id"] ?? 0);

        if (!$id) {
            json_response(["success" => false, "message" => "id required"], 400);
        }

        // ✅ VERIFY EXISTS
        $chk = db()->prepare("SELECT id FROM banners WHERE id = ? LIMIT 1");
        $chk->execute([$id]);
        if (!$chk->fetch()) {
            json_response(["success" => false, "message" => "Invalid banner id"], 400);
        }

        db()->prepare("DELETE FROM banners WHERE id = ?")->execute([$id]);

        json_response([
            "success" => true,
            "message" => "Banner deleted"
        ]);
    }
}
