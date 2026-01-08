<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../core/response.php";
require_once __DIR__ . "/../core/utils.php";
require_once __DIR__ . "/../core/auth.php";

class ContactController
{
    /* =========================
       PUBLIC – CREATE CONTACT
       POST /contact
    ========================= */
    public static function create(): void
    {
        $b = get_json_body();

        $name = trim($b["name"] ?? "");
        $email = trim($b["email"] ?? "");
        $message = trim($b["message"] ?? "");

        if (!$name || !$email || !$message) {
            json_response([
                "success" => false,
                "message" => "name, email and message are required"
            ], 400);
        }

        db()->prepare("
            INSERT INTO contact_requests
            (name, email, phone, subject, message)
            VALUES (?, ?, ?, ?, ?)
        ")->execute([
            $name,
            $email,
            $b["phone"] ?? null,
            $b["subject"] ?? null,
            $message
        ]);

        json_response([
            "success" => true,
            "message" => "Thank you for contacting us. We will get back to you soon."
        ]);
    }

    /* =========================
       ADMIN – LIST CONTACTS
       GET /admin/contact
    ========================= */
    public static function list(): void
    {
        require_admin();

        $onlyNew = (int)($_GET["only_new"] ?? 0);

        $where = "";
        if ($onlyNew === 1) {
            $where = "WHERE is_contacted = 0";
        }

        $rows = db()->query("
            SELECT *
            FROM contact_requests
            $where
            ORDER BY created_at DESC
        ")->fetchAll();

        json_response([
            "success" => true,
            "data" => $rows
        ]);
    }

    /* =========================
       ADMIN – MARK AS CONTACTED
       POST /admin/contact/mark
    ========================= */
    public static function markContacted(): void
    {
        require_admin();
        $b = get_json_body();

        $id = (int)($b["id"] ?? 0);
        if (!$id) {
            json_response(["success" => false, "message" => "id required"], 400);
        }

        db()->prepare("
            UPDATE contact_requests
            SET is_contacted = 1,
                contacted_at = NOW()
            WHERE id = ?
        ")->execute([$id]);

        json_response([
            "success" => true,
            "message" => "Marked as contacted"
        ]);
    }

    /* =========================
       ADMIN – DELETE CONTACT
       POST /admin/contact/delete
    ========================= */
    public static function delete(): void
    {
        require_admin();
        $b = get_json_body();

        $id = (int)($b["id"] ?? 0);
        if (!$id) {
            json_response(["success" => false, "message" => "id required"], 400);
        }

        db()->prepare("DELETE FROM contact_requests WHERE id = ?")
            ->execute([$id]);

        json_response([
            "success" => true,
            "message" => "Contact request deleted"
        ]);
    }
}
