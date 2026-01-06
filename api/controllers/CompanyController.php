<?php
require_once __DIR__ . "/../core/response.php";
require_once __DIR__ . "/../core/utils.php";
require_once __DIR__ . "/../core/auth.php";
require_once __DIR__ . "/../core/upload.php";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../core/url.php";


class CompanyController
{
    /* =========================
       PUBLIC – GET COMPANY DATA
    ========================= */
  public static function getPublic(): void
{
    $row = db()->query("
        SELECT
            id,
          company_name,
          logo_url,
          phone,
          email,
          address,
          website,
          map_url,
          whatsapp,
          facebook,
          instagram,
          youtube,
          linkedin
        FROM company_info
        LIMIT 1
    ")->fetch();

    if (!$row) {
        json_response([
            "success" => false,
            "message" => "Company info not found"
        ], 404);
    }

        $row["logo_url"] = asset_url($row["logo_url"]);


    json_response([
        "success" => true,
        "data" => $row
    ]);
}


    /* =========================
       ADMIN – UPDATE COMPANY
    ========================= */
    public static function update(): void
    {
        require_admin();
        $body = get_json_body();

        $allowed = [
            "company_name",
            "logo_url",
            "favicon_url",
            "phone",
            "email",
            "address",
            "website",
            "map_url",
            "whatsapp",
            "facebook",
            "instagram",
            "youtube",
            "linkedin",
            "primary_color",
            "secondary_color",
            "background_color",
            "text_color",
            "footer_text"
        ];

        $set = [];
        $params = [];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $body)) {
                $set[] = "$field = ?";
                $params[] = $body[$field];
            }
        }

        if (!$set) {
            json_response([
                "success" => false,
                "message" => "No fields provided"
            ], 400);
        }

        $sql = "UPDATE company_info SET " . implode(",", $set) . " LIMIT 1";
        db()->prepare($sql)->execute($params);

        json_response([
            "success" => true,
            "message" => "Company information updated"
        ]);
    }

    /* =========================
       ADMIN – IMAGE UPLOAD
    ========================= */
public static function uploadImage(): void
{
    require_admin();

    // company_info id
    $id = (int)($_POST["id"] ?? 0);
    if (!$id) {
        json_response([
            "success" => false,
            "message" => "id required"
        ], 400);
    }

    // Upload file
    $res = handle_image_upload("image");
    if (!$res["ok"]) {
        json_response([
            "success" => false,
            "message" => $res["message"]
        ], 400);
    }

    $url = $res["url"];

    // Save image URL to DB
    db()->prepare(
        "UPDATE company_info SET logo_url = ? WHERE id = ? LIMIT 1"
    )->execute([$url, $id]);

    json_response([
        "success" => true,
        "message" => "Image uploaded and saved",
        "url" => $url,
        "id" => $id
    ]);
}
}
