<?php
require_once __DIR__ . "/../core/response.php";
require_once __DIR__ . "/../core/utils.php";
require_once __DIR__ . "/../core/auth.php";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../core/url.php";

class ProductController
{
    /* =========================
       PUBLIC LIST
    ========================= */
  public static function viewPublic(): void
{
    $pdo = db();
    $id = (int)($_GET["id"] ?? 0);
    $slug = trim($_GET["slug"] ?? "");

    if (!$id && !$slug) {
        json_response(["success" => false, "message" => "id or slug required"], 400);
    }

    $sql = "
        SELECT
            p.*,
            c.name AS category_name,
            c.slug AS category_slug,

            CASE
                WHEN p.offer_price IS NOT NULL
                 AND (p.offer_start IS NULL OR p.offer_start <= NOW())
                 AND (p.offer_end IS NULL OR p.offer_end >= NOW())
                THEN p.offer_price
                ELSE p.price
            END AS final_price
        FROM products p
        LEFT JOIN categories c ON c.id = p.category_id
        WHERE p.is_active = 1
    ";

    if ($id) {
        $sql .= " AND p.id = ? LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
    } else {
        $sql .= " AND p.slug = ? LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$slug]);
    }

    $row = $stmt->fetch();
    if (!$row) {
        json_response(["success" => false, "message" => "Not found"], 404);
    }

    // ✅ ADD IMAGES
    $imgs = $pdo->prepare("
        SELECT id, image_url, is_primary
        FROM product_images
        WHERE product_id = ?
        ORDER BY is_primary DESC, sort_order ASC
    ");
    $imgs->execute([$row["id"]]);

    $images = $imgs->fetchAll();
    foreach ($images as &$img) {
        $img["image_url"] = asset_url($img["image_url"]);
    }

    $row["images"] = $images;

    json_response([
        "success" => true,
        "data" => $row
    ]);
}

public static function listPublic(): void
{
    $pdo = db();

    $q = trim($_GET["q"] ?? "");
    $categoryId = (int)($_GET["category_id"] ?? 0);
    $onlyOffer = (int)($_GET["only_offer"] ?? 0);

    $limit = min(100, max(1, (int)($_GET["limit"] ?? 30)));
    $offset = max(0, (int)($_GET["offset"] ?? 0));

    $where = "p.is_active = 1";
    $params = [];

    if ($q !== "") {
        $where .= " AND (p.name LIKE ? OR p.description LIKE ? OR p.sku LIKE ?)";
        $params[] = "%$q%";
        $params[] = "%$q%";
        $params[] = "%$q%";
    }

    if ($categoryId > 0) {
        $where .= " AND p.category_id = ?";
        $params[] = $categoryId;
    }

    if ($onlyOffer === 1) {
        $where .= " AND p.offer_price IS NOT NULL
                    AND (p.offer_start IS NULL OR p.offer_start <= NOW())
                    AND (p.offer_end IS NULL OR p.offer_end >= NOW())";
    }

    $sql = "
        SELECT
            p.*,
            c.name AS category_name,
            c.slug AS category_slug,

            CASE
                WHEN p.offer_price IS NOT NULL
                 AND (p.offer_start IS NULL OR p.offer_start <= NOW())
                 AND (p.offer_end IS NULL OR p.offer_end >= NOW())
                THEN p.offer_price
                ELSE p.price
            END AS final_price,

            CASE
                WHEN p.mrp IS NOT NULL
                 AND p.mrp > 0
                 AND (
                    p.offer_price IS NOT NULL
                    AND (p.offer_start IS NULL OR p.offer_start <= NOW())
                    AND (p.offer_end IS NULL OR p.offer_end >= NOW())
                 )
                THEN ROUND((p.mrp - p.offer_price) / p.mrp * 100)
                ELSE 0
            END AS discount_percent

        FROM products p
        LEFT JOIN categories c ON c.id = p.category_id
        WHERE $where
        ORDER BY p.id DESC
        LIMIT $limit OFFSET $offset
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    // ✅ ADD IMAGES
    foreach ($rows as &$row) {
        $imgs = $pdo->prepare("
            SELECT id, image_url, is_primary
            FROM product_images
            WHERE product_id = ?
            ORDER BY is_primary DESC, sort_order ASC
        ");
        $imgs->execute([$row["id"]]);

        $images = $imgs->fetchAll();
        foreach ($images as &$img) {
            $img["image_url"] = asset_url($img["image_url"]);
        }

        $row["images"] = $images;
    }

    json_response([
        "success" => true,
        "data" => $rows
    ]);
}



    public static function uploadImage(): void
{
    require_admin();

    $productId = (int)($_POST["product_id"] ?? 0);
    if (!$productId) {
        json_response(["success" => false, "message" => "product_id required"], 400);
    }

    // Upload image
    $res = handle_image_upload("image");
    if (!$res["ok"]) {
        json_response(["success" => false, "message" => $res["message"]], 400);
    }

    $imageUrl = $res["url"];
    $sort = (int)($_POST["sort_order"] ?? 0);
    $primary = (int)($_POST["is_primary"] ?? 0);

    // If primary → unset other primary images
    if ($primary === 1) {
        db()->prepare(
            "UPDATE product_images SET is_primary = 0 WHERE product_id = ?"
        )->execute([$productId]);
    }

    db()->prepare("
        INSERT INTO product_images (product_id, image_url, sort_order, is_primary)
        VALUES (?, ?, ?, ?)
    ")->execute([$productId, $imageUrl, $sort, $primary]);

    json_response([
        "success" => true,
        "message" => "Image uploaded",
        "image_url" => asset_url($imageUrl)
    ]);
}

public static function deleteImage(): void
{
    require_admin();
    $b = get_json_body();
    $id = (int)($b["id"] ?? 0);

    if (!$id) {
        json_response(["success" => false, "message" => "image id required"], 400);
    }

    db()->prepare("DELETE FROM product_images WHERE id = ?")->execute([$id]);

    json_response([
        "success" => true,
        "message" => "Image deleted"
    ]);
}

    /* =========================
       PUBLIC VIEW
    ========================= */
  

    /* =========================
       ADMIN LIST
    ========================= */
    public static function listAdmin(): void
    {
        require_admin();
        $rows = db()->query("
            SELECT p.*, c.name AS category_name
            FROM products p
            LEFT JOIN categories c ON c.id = p.category_id
            ORDER BY p.id DESC
        ")->fetchAll();

        json_response(["success" => true, "data" => $rows]);
    }

    /* =========================
       CREATE
    ========================= */
  public static function create(): void
{
    require_admin();
    $b = get_json_body();

    $name = trim($b["name"] ?? "");
    if (!$name) {
        json_response(["success" => false, "message" => "name required"], 400);
    }

    $slug = $b["slug"] ?? slugify($name);

    // ✅ VALIDATE CATEGORY (NEW)
    $categoryId = $b["category_id"] ?? null;
    if ($categoryId !== null) {
        $chk = db()->prepare("SELECT id FROM categories WHERE id = ? LIMIT 1");
        $chk->execute([$categoryId]);
        if (!$chk->fetch()) {
            json_response([
                "success" => false,
                "message" => "Invalid category_id"
            ], 400);
        }
    }

    $stmt = db()->prepare("
        INSERT INTO products (
          name, slug, image_url, description, short_description, sku,
          price, mrp, offer_price, offer_start, offer_end,
          stock, unit, category_id, brand, is_active
        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
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
        $b["offer_price"] ?? null,
        $b["offer_start"] ?? null,
        $b["offer_end"] ?? null,
        $b["stock"] ?? null,
        $b["unit"] ?? null,
        $categoryId, // ✅ SAFE
        $b["brand"] ?? null,
        (int)($b["is_active"] ?? 1)
    ]);

    json_response([
        "success" => true,
        "message" => "Product created",
        "id" => (int)db()->lastInsertId(),
        "slug" => $slug
    ]);
}


    /* =========================
       UPDATE
    ========================= */
   public static function update(): void
{
    require_admin();
    $b = get_json_body();

    $id = (int)($b["id"] ?? 0);
    if (!$id) {
        json_response(["success" => false, "message" => "id required"], 400);
    }

    // ✅ VALIDATE CATEGORY IF PROVIDED
    if (array_key_exists("category_id", $b) && $b["category_id"] !== null) {
        $chk = db()->prepare("SELECT id FROM categories WHERE id = ? LIMIT 1");
        $chk->execute([$b["category_id"]]);
        if (!$chk->fetch()) {
            json_response([
                "success" => false,
                "message" => "Invalid category_id"
            ], 400);
        }
    }

    $allowed = [
        "name","slug","image_url","description","short_description","sku",
        "price","mrp","offer_price","offer_start","offer_end",
        "stock","unit","category_id","brand","is_active"
    ];

    $set = [];
    $params = [];

    foreach ($allowed as $k) {
        if (array_key_exists($k, $b)) {
            $set[] = "$k = ?";
            $params[] = $b[$k];
        }
    }

    if (!$set) {
        json_response(["success" => false, "message" => "No fields provided"], 400);
    }

    $params[] = $id;
    db()->prepare(
        "UPDATE products SET " . implode(",", $set) . " WHERE id = ?"
    )->execute($params);

    json_response([
        "success" => true,
        "message" => "Product updated"
    ]);
}

    /* =========================
       DELETE
    ========================= */
    public static function delete(): void
    {
        require_admin();
        $b = get_json_body();
        $id = (int)($b["id"] ?? 0);

        if (!$id) {
            json_response(["success" => false, "message" => "id required"], 400);
        }

        db()->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
        json_response(["success" => true, "message" => "Product deleted"]);
    }
}
