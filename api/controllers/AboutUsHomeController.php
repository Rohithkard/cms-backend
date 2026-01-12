<?php
require_once __DIR__."/../config/db.php";
require_once __DIR__."/../core/response.php";
require_once __DIR__."/../core/auth.php";
require_once __DIR__."/../core/upload.php";
require_once __DIR__."/../core/url.php";

class AboutUshHomeController
{
    /* =========================
       PUBLIC – LIST SECTIONS
    ========================= */
    public static function listPublic(): void
    {
        $sections = db()->query("
            SELECT id, section_key, heading, sub_heading, content
            FROM home_sections
            WHERE is_active = 1
            ORDER BY sort_order ASC
        ")->fetchAll();

        foreach ($sections as &$s) {
            $images = db()->prepare("
                SELECT id, image_url
                FROM home_section_images
                WHERE section_id = ? AND is_active = 1
                ORDER BY sort_order ASC
            ");
            $images->execute([$s["id"]]);

            $s["images"] = array_map(fn($i)=>[
                "id"=>$i["id"],
                "url"=>asset_url($i["image_url"])
            ], $images->fetchAll());
        }

        json_response(["success"=>true,"data"=>$sections]);
    }

    /* =========================
       ADMIN – LIST
    ========================= */
    public static function listAdmin(): void
    {
        require_admin();
       $sections = db()->query("
            SELECT id, section_key, heading, sub_heading, content
            FROM home_sections
            WHERE is_active = 1
            ORDER BY sort_order ASC
        ")->fetchAll();

        foreach ($sections as &$s) {
            $images = db()->prepare("
                SELECT id, image_url
                FROM home_section_images
                WHERE section_id = ? AND is_active = 1
                ORDER BY sort_order ASC
            ");
            $images->execute([$s["id"]]);

            $s["images"] = array_map(fn($i)=>[
                "id"=>$i["id"],
                "url"=>asset_url($i["image_url"])
            ], $images->fetchAll());
        }

        json_response(["success"=>true,"data"=>$sections]);
    }

    /* =========================
       ADMIN – CREATE SECTION
    ========================= */
    public static function create(): void
    {
        require_admin();
        $b = get_json_body();

        if (empty($b["section_key"]) || empty($b["heading"])) {
            json_response(["success"=>false,"message"=>"section_key & heading required"],400);
        }

        db()->prepare("
            INSERT INTO home_sections
            (section_key, heading, sub_heading, content, sort_order, is_active)
            VALUES (?,?,?,?,?,?)
        ")->execute([
            strtoupper($b["section_key"]),
            $b["heading"],
            $b["sub_heading"] ?? null,
            $b["content"] ?? null,
            (int)($b["sort_order"] ?? 0),
            (int)($b["is_active"] ?? 1)
        ]);

        json_response(["success"=>true,"message"=>"Section created"]);
    }

    /* =========================
       ADMIN – UPDATE SECTION
    ========================= */
    public static function update(): void
    {
        require_admin();
        $b = get_json_body();
        $id = (int)($b["id"] ?? 0);
        if (!$id) json_response(["success"=>false,"message"=>"id required"],400);

        $allowed = ["heading","sub_heading","content","sort_order","is_active"];
        $set=[];$params=[];

        foreach ($allowed as $f) {
            if (array_key_exists($f,$b)) {
                $set[]="$f=?";
                $params[]=$b[$f];
            }
        }
        if(!$set) json_response(["success"=>false,"message"=>"No fields"],400);

        $params[]=$id;
        db()->prepare("UPDATE home_sections SET ".implode(",",$set)." WHERE id=?")
            ->execute($params);

        json_response(["success"=>true,"message"=>"Section updated"]);
    }

    /* =========================
       ADMIN – DELETE SECTION
    ========================= */
    public static function delete(): void
    {
        require_admin();
        $id=(int)(get_json_body()["id"]??0);
        if(!$id) json_response(["success"=>false,"message"=>"id required"],400);

        db()->prepare("DELETE FROM home_sections WHERE id=?")->execute([$id]);
        json_response(["success"=>true,"message"=>"Section deleted"]);
    }

    /* =========================
       ADMIN – UPLOAD IMAGE
    ========================= */
    public static function uploadImage(): void
    {
        require_admin();

        $sectionId=(int)($_POST["section_id"]??0);
        if(!$sectionId) json_response(["success"=>false,"message"=>"section_id required"],400);

        $res=handle_image_upload("image");
        if(!$res["ok"]) json_response(["success"=>false,"message"=>$res["message"]],400);

        db()->prepare("
            INSERT INTO home_section_images
            (section_id,image_url,sort_order,is_active)
            VALUES (?,?,?,?)
        ")->execute([
            $sectionId,
            $res["url"],
            (int)($_POST["sort_order"]??0),
            1
        ]);

        json_response([
            "success"=>true,
            "image_url"=>asset_url($res["url"])
        ]);
    }

    /* =========================
       ADMIN – DELETE IMAGE
    ========================= */
    public static function deleteImage(): void
    {
        require_admin();
        $id=(int)(get_json_body()["id"]??0);
        if(!$id) json_response(["success"=>false,"message"=>"id required"],400);

        db()->prepare("DELETE FROM home_section_images WHERE id=?")->execute([$id]);
        json_response(["success"=>true,"message"=>"Image deleted"]);
    }
}
