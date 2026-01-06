<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../core/response.php";
require_once __DIR__ . "/../core/auth.php";

class ThemeController
{
    /* =========================
       PUBLIC – GET THEME
    ========================= */
    public static function getPublic(): void
    {
        $row = db()->query("
            SELECT
              logo_url,
              favicon_url,
              primary_color,
              secondary_color,
              background_color,
              text_color,
              header_bg,
              footer_bg,
              font_family
            FROM website_theme
            LIMIT 1
        ")->fetch();

        if (!$row) {
            json_response([
                "success" => false,
                "message" => "Theme not found"
            ], 404);
        }

        json_response([
            "success" => true,
            "data" => $row
        ]);
    }

    /* =========================
       ADMIN – UPDATE THEME
    ========================= */
    public static function update(): void
    {
        require_admin();
        $b = get_json_body();

        $allowed = [
            "logo_url",
            "favicon_url",
            "primary_color",
            "secondary_color",
            "background_color",
            "text_color",
            "header_bg",
            "footer_bg",
            "font_family"
        ];

        $set = [];
        $params = [];

        foreach ($allowed as $f) {
            if (array_key_exists($f, $b)) {
                $set[] = "$f = ?";
                $params[] = $b[$f];
            }
        }

        if (!$set) {
            json_response([
                "success" => false,
                "message" => "No fields provided"
            ], 400);
        }

        db()->prepare(
            "UPDATE website_theme SET " . implode(",", $set) . " LIMIT 1"
        )->execute($params);

        json_response([
            "success" => true,
            "message" => "Theme updated"
        ]);
    }
}
