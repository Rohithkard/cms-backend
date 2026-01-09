<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../core/response.php";
require_once __DIR__ . "/../core/utils.php";
require_once __DIR__ . "/../core/auth.php";
require_once __DIR__ . "/../core/upload.php";
require_once __DIR__ . "/../core/url.php";

class CategoryController
{
    /* =========================
       PUBLIC – LIST CATEGORIES
    ========================= */
    public static function listPublic(): void
    {
        $rows = db()->query("
            SELECT id, name, slug, image_url,description
            FROM categories
            WHERE is_active = 1
            ORDER BY sort_order ASC, name ASC
        ")->fetchAll();

        // 🔗 Add base URL to image
        foreach ($rows as &$row) {
            $row["image_url"] = asset_url($row["image_url"]);
        }

        json_response([
            "success" => true,
            "data" => $rows
        ]);
    }

    /* =========================
       ADMIN – CREATE CATEGORY
       (multipart/form-data)
    ========================= */
    public static function create(): void
    {
        require_admin();

        $name = trim($_POST["name"] ?? "");
        if (!$name) {
            json_response(["success" => false, "message" => "name required"], 400);
        }

        $slug = slugify($_POST["slug"] ?? $name);
        $sort = (int) ($_POST["sort_order"] ?? 0);
        $active = (int) ($_POST["is_active"] ?? 1);
        $description = trim($_POST["description"] ?? "");

        // 📷 Image upload
        $imageUrl = null;
        if (!empty($_FILES["image"])) {
            $res = handle_image_upload("image");
            if (!$res["ok"]) {
                json_response(["success" => false, "message" => $res["message"]], 400);
            }
            $imageUrl = $res["url"];
        }

        $stmt = db()->prepare("
        INSERT INTO categories
        (name, slug, description, image_url, sort_order, is_active)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

        $stmt->execute([
            $name,
            $slug,
            $description,
            $imageUrl,
            $sort,
            $active
        ]);

        json_response([
            "success" => true,
            "message" => "Category created",
            "id" => db()->lastInsertId(),
            "image_url" => asset_url($imageUrl)
        ]);
    }


    /* =========================
       ADMIN – UPDATE CATEGORY
       (multipart/form-data)
    ========================= */
    public static function update(): void
{
    require_admin();

    $id = (int) ($_POST["id"] ?? 0);
    if ($id <= 0) {
        json_response([
            "success" => false,
            "message" => "Valid id is required"
        ], 400);
    }

    // Allowed updatable fields
    $allowed = ["name", "slug", "description", "sort_order", "is_active"];

    $set = [];
    $params = [];

    foreach ($allowed as $field) {
        if (array_key_exists($field, $_POST)) {
            $set[] = "{$field} = ?";
            $params[] = $_POST[$field];
        }
    }

    /* ================= IMAGE UPLOAD ================= */
    if (!empty($_FILES["image"]) && $_FILES["image"]["error"] === UPLOAD_ERR_OK) {

        $res = handle_image_upload("image");

        if (!$res["ok"]) {
            json_response([
                "success" => false,
                "message" => $res["message"]
            ], 400);
        }

        $set[] = "image_url = ?";
        $params[] = $res["url"];
    }

    if (empty($set)) {
        json_response([
            "success" => false,
            "message" => "No fields provided to update"
        ], 400);
    }

    $params[] = $id;

    $stmt = db()->prepare(
        "UPDATE categories SET " . implode(", ", $set) . " WHERE id = ?"
    );

    $stmt->execute($params);

    if ($stmt->rowCount() === 0) {
        json_response([
            "success" => false,
            "message" => "No changes made or category not found"
        ], 404);
    }

    json_response([
        "success" => true,
        "message" => "Category updated successfully"
    ]);
}


    /* =========================
       ADMIN – DELETE CATEGORY
    ========================= */
    public static function delete(): void
    {
        require_admin();
        $b = get_json_body();
        $id = (int) ($b["id"] ?? 0);

        if (!$id) {
            json_response(["success" => false, "message" => "id required"], 400);
        }

        db()->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);

        json_response([
            "success" => true,
            "message" => "Category deleted"
        ]);
    }
}
