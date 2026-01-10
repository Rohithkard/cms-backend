<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../core/response.php";
require_once __DIR__ . "/../core/utils.php";
require_once __DIR__ . "/../core/auth.php";

class PackageRequestController
{
    /* =========================
       PUBLIC – CREATE PACKAGE REQUEST
       POST /package-request
    ========================= */
    public static function create(): void
    {
        $b = get_json_body();

        $required = ["first_name","email","phone","products"];
        foreach ($required as $f) {
            if (empty($b[$f])) {
                json_response(["success"=>false,"message"=>"$f required"],400);
            }
        }

        if (!is_array($b["products"]) || count($b["products"]) === 0 || count($b["products"]) > 3) {
            json_response([
                "success"=>false,
                "message"=>"products must be an array (1–3 items)"
            ],400);
        }

        $pdo = db();
        $pdo->beginTransaction();

        try {
            $pdo->prepare("
                INSERT INTO package_requests
                (first_name,last_name,email,phone,category)
                VALUES (?,?,?,?,?)
            ")->execute([
                $b["first_name"],
                $b["last_name"],
                $b["email"],
                $b["phone"],
                $b["category"] ?? null
            ]);

            $requestId = (int)$pdo->lastInsertId();

            $stmt = $pdo->prepare("
                INSERT INTO package_request_items
                (package_request_id, product_name, quantity)
                VALUES (?,?,?)
            ");

            foreach ($b["products"] as $p) {
                if (empty($p["product_name"]) || empty($p["quantity"])) {
                    throw new Exception("product_name and quantity required");
                }
                $stmt->execute([$requestId, $p["product_name"], (int)$p["quantity"]]);
            }

            $pdo->commit();

            json_response([
                "success"=>true,
                "message"=>"Package request submitted successfully"
            ]);

        } catch (Exception $e) {
            $pdo->rollBack();
            json_response(["success"=>false,"message"=>$e->getMessage()],400);
        }
    }

    /* =========================
       ADMIN – LIST PACKAGE REQUESTS
       GET /admin/package-requests
    ========================= */
    public static function list(): void
    {
        require_admin();

        $rows = db()->query("
            SELECT *
            FROM package_requests
            ORDER BY created_at DESC
        ")->fetchAll();

        foreach ($rows as &$r) {
            $items = db()->prepare("
                SELECT product_name, quantity
                FROM package_request_items
                WHERE package_request_id = ?
            ");
            $items->execute([$r["id"]]);
            $r["products"] = $items->fetchAll();
        }

        json_response(["success"=>true,"data"=>$rows]);
    }

    /* =========================
       ADMIN – MARK AS CONTACTED
       POST /admin/package-requests/mark
    ========================= */
    public static function markContacted(): void
    {
        require_admin();
        $b = get_json_body();
        $id = (int)($b["id"] ?? 0);

        if (!$id) json_response(["success"=>false,"message"=>"id required"],400);

        db()->prepare("
            UPDATE package_requests
            SET is_contacted = 1, contacted_at = NOW()
            WHERE id = ?
        ")->execute([$id]);

        json_response(["success"=>true,"message"=>"Marked as contacted"]);
    }

    /* =========================
       ADMIN – DELETE REQUEST
       POST /admin/package-requests/delete
    ========================= */
    public static function delete(): void
    {
        require_admin();
        $b = get_json_body();
        $id = (int)($b["id"] ?? 0);

        if (!$id) json_response(["success"=>false,"message"=>"id required"],400);

        db()->prepare("DELETE FROM package_requests WHERE id=?")->execute([$id]);
        json_response(["success"=>true,"message"=>"Package request deleted"]);
    }
}
