<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../core/response.php";
require_once __DIR__ . "/../core/auth.php";
require_once __DIR__ . "/../core/upload.php";
require_once __DIR__ . "/../core/url.php";

class HomeController
{
    /* =====================================================
       PUBLIC HOME PAGE API
       GET /home
    ===================================================== */
   public static function get(): void
{
    $pdo = db();

    /* ================= HOME CONTENT ================= */
    $content = $pdo->query("
        SELECT about_title, about_text, why_title, map_embed_url
        FROM home_content
        LIMIT 1
    ")->fetch() ?: [];

    /* ================= WHY CHOOSE US ================= */
    $why = $pdo->query("
        SELECT id, title, description, icon
        FROM home_why_points
        WHERE is_active = 1
        ORDER BY sort_order ASC, id ASC
    ")->fetchAll();

    foreach ($why as &$w) {
        $w["icon"] = asset_url($w["icon"]);

        // 🔽 FETCH SUB POINTS
        $subStmt = $pdo->prepare("
            SELECT id, question, answer
            FROM home_why_subpoints
            WHERE why_point_id = ?
              AND is_active = 1
            ORDER BY sort_order ASC, id ASC
        ");
        $subStmt->execute([$w["id"]]);
        $w["sub_points"] = $subStmt->fetchAll();
    }

    /* ================= TESTIMONIALS ================= */
    $testimonials = $pdo->query("
        SELECT id, name, role, message, image_url, rating
        FROM home_testimonials
        WHERE is_active = 1
        ORDER BY sort_order ASC
    ")->fetchAll();

    foreach ($testimonials as &$t) {
        $t["image_url"] = asset_url($t["image_url"]);
    }

    /* ================= VIDEOS ================= */
    $videos = $pdo->query("
        SELECT id, title, video_url, thumbnail_url
        FROM home_videos
        WHERE is_active = 1
        ORDER BY sort_order ASC
    ")->fetchAll();

    foreach ($videos as &$v) {
        $v["thumbnail_url"] = asset_url($v["thumbnail_url"]);
    }

    /* ================= FAQ ================= */
    $faqs = $pdo->query("
        SELECT id, question, answer
        FROM home_faqs
        WHERE is_active = 1
        ORDER BY sort_order ASC
    ")->fetchAll();

    /* ================= FINAL RESPONSE ================= */
    json_response([
        "success" => true,
        "data" => [
            "about" => [
                "title" => $content["about_title"] ?? "",
                "text"  => $content["about_text"] ?? ""
            ],
            "why_choose_us" => [
                "title"  => $content["why_title"] ?? "",
                "points" => $why
            ],
            "testimonials" => $testimonials,
            "videos" => $videos,
            "map" => [
                "embed_url" => $content["map_embed_url"] ?? ""
            ],
            "faqs" => $faqs
        ]
    ]);
}


    /* =====================================================
       ADMIN – HOME CONTENT (LIST + UPDATE)
    ===================================================== */
    public static function listContent(): void
    {
        require_admin();
        $row = db()->query("SELECT * FROM home_content LIMIT 1")->fetch();
        json_response(["success" => true, "data" => $row]);
    }

    public static function updateContent(): void
    {
        require_admin();
        $b = get_json_body();

        $allowed = ["about_title", "about_text", "why_title", "map_embed_url"];
        $set = [];
        $params = [];

        foreach ($allowed as $f) {
            if (array_key_exists($f, $b)) {
                $set[] = "$f=?";
                $params[] = $b[$f];
            }
        }

        if (!$set)
            json_response(["success" => false, "message" => "No fields"], 400);

        db()->prepare("UPDATE home_content SET " . implode(",", $set) . " LIMIT 1")
            ->execute($params);

        json_response(["success" => true, "message" => "Home content updated"]);
    }

    /* =====================================================
       ADMIN – WHY CHOOSE US (LIST / CREATE / UPDATE / DELETE)
    ===================================================== */
    public static function listWhy(): void
    {
        $pdo = db();

        $points = $pdo->query("
        SELECT *
        FROM home_why_points
        WHERE is_active = 1
        ORDER BY sort_order ASC, id ASC
    ")->fetchAll();

        foreach ($points as &$p) {
            $p["icon"] = asset_url($p["icon"]);

            $subs = $pdo->prepare("
            SELECT id, question, answer, sort_order
            FROM home_why_subpoints
            WHERE why_point_id = ?
              AND is_active = 1
            ORDER BY sort_order ASC, id ASC
        ");
            $subs->execute([$p["id"]]);
            $p["sub_points"] = $subs->fetchAll();
        }

        json_response(["success" => true, "data" => $points]);
    }
    public static function createWhy(): void
    {
        require_admin();
        $title = trim($_POST["title"] ?? "");
        if (!$title)
            json_response(["success" => false, "message" => "title required"], 400);

        $icon = null;
        if (!empty($_FILES["icon"])) {
            $res = handle_image_upload("icon");
            if (!$res["ok"])
                json_response(["success" => false, "message" => $res["message"]], 400);
            $icon = $res["url"];
        }

        db()->prepare("
            INSERT INTO home_why_points (title,description,icon,sort_order,is_active)
            VALUES (?,?,?,?,?)
        ")->execute([
                    $title,
                    $_POST["description"] ?? null,
                    $icon,
                    (int) ($_POST["sort_order"] ?? 0),
                    (int) ($_POST["is_active"] ?? 1)
                ]);

        json_response(["success" => true, "message" => "Why point created"]);
    }

    public static function updateWhy(): void
    {
        require_admin();
        $id = (int) ($_POST["id"] ?? 0);
        if (!$id)
            json_response(["success" => false, "message" => "id required"], 400);

        $set = [];
        $params = [];
        foreach (["title", "description", "sort_order", "is_active"] as $f) {
            if (isset($_POST[$f])) {
                $set[] = "$f=?";
                $params[] = $_POST[$f];
            }
        }

        if (!empty($_FILES["icon"])) {
            $res = handle_image_upload("icon");
            if (!$res["ok"])
                json_response(["success" => false, "message" => $res["message"]], 400);
            $set[] = "icon=?";
            $params[] = $res["url"];
        }

        if (!$set)
            json_response(["success" => false, "message" => "No fields"], 400);

        $params[] = $id;
        db()->prepare("UPDATE home_why_points SET " . implode(",", $set) . " WHERE id=?")
            ->execute($params);

        json_response(["success" => true, "message" => "Why point updated"]);
    }

    public static function deleteWhy(): void
    {
        require_admin();
        $b = get_json_body();
        $id = (int) ($b["id"] ?? 0);
        if (!$id)
            json_response(["success" => false, "message" => "id required"], 400);

        db()->prepare("DELETE FROM home_why_points WHERE id=?")->execute([$id]);
        json_response(["success" => true, "message" => "Why point deleted"]);
    }

    public static function createWhySub(): void
    {
        require_admin();
        $b = get_json_body();

        if (!($b["why_point_id"] ?? null) || !($b["question"] ?? null)) {
            json_response(["success" => false, "message" => "why_point_id & question required"], 400);
        }

        db()->prepare("
        INSERT INTO home_why_subpoints
        (why_point_id, question, answer, sort_order, is_active)
        VALUES (?,?,?,?,?)
    ")->execute([
                    $b["why_point_id"],
                    $b["question"],
                    $b["answer"] ?? null,
                    (int) ($b["sort_order"] ?? 0),
                    (int) ($b["is_active"] ?? 1)
                ]);

        json_response(["success" => true, "message" => "Sub point created"]);
    }

    /* =====================================================
       SUB POINTS – UPDATE
       POST /admin/why-subpoints/update
    ===================================================== */
    public static function updateWhySub(): void
    {
        require_admin();
        $b = get_json_body();
        $id = (int) ($b["id"] ?? 0);
        if (!$id)
            json_response(["success" => false, "message" => "id required"], 400);

        $fields = ["question", "answer", "sort_order", "is_active"];
        $set = [];
        $params = [];

        foreach ($fields as $f) {
            if (array_key_exists($f, $b)) {
                $set[] = "$f=?";
                $params[] = $b[$f];
            }
        }

        if (!$set)
            json_response(["success" => false, "message" => "no fields"], 400);

        $params[] = $id;
        db()->prepare("UPDATE home_why_subpoints SET " . implode(",", $set) . " WHERE id=?")
            ->execute($params);

        json_response(["success" => true, "message" => "Sub point updated"]);
    }

    public static function listWhySub(): void
{
    require_admin();
    $whyId = (int)($_GET["why_point_id"] ?? 0);
    if (!$whyId) json_response(["success"=>false,"message"=>"why_point_id required"],400);

    $rows = db()->prepare("
        SELECT *
        FROM home_why_subpoints
        WHERE why_point_id = ?
        ORDER BY sort_order ASC, id ASC
    ");
    $rows->execute([$whyId]);

    json_response(["success"=>true,"data"=>$rows->fetchAll()]);
}

    public static function deleteWhySub(): void
    {
        require_admin();
        $b = get_json_body();
        $id = (int) ($b["id"] ?? 0);
        if (!$id)
            json_response(["success" => false, "message" => "id required"], 400);

        db()->prepare("DELETE FROM home_why_subpoints WHERE id=?")->execute([$id]);
        json_response(["success" => true, "message" => "Sub point deleted"]);
    }

    /* =====================================================
       ADMIN – TESTIMONIALS (LIST / CREATE / UPDATE / DELETE)
    ===================================================== */
    public static function listTestimonials(): void
    {
        require_admin();
        $rows = db()->query("SELECT * FROM home_testimonials ORDER BY sort_order ASC")->fetchAll();

        foreach ($rows as &$r) {
            $r["image_url"] = asset_url($r["image_url"]);
        }

        json_response(["success" => true, "data" => $rows]);
    }

    public static function createTestimonial(): void
    {
        require_admin();
        $name = trim($_POST["name"] ?? "");
        $message = trim($_POST["message"] ?? "");
        if (!$name || !$message)
            json_response(["success" => false, "message" => "name & message required"], 400);

        $img = null;
        if (!empty($_FILES["image"])) {
            $res = handle_image_upload("image");
            if (!$res["ok"])
                json_response(["success" => false, "message" => $res["message"]], 400);
            $img = $res["url"];
        }

        db()->prepare("
            INSERT INTO home_testimonials
            (name,role,message,image_url,rating,sort_order,is_active)
            VALUES (?,?,?,?,?,?,?)
        ")->execute([
                    $name,
                    $_POST["role"] ?? null,
                    $message,
                    $img,
                    (int) ($_POST["rating"] ?? 5),
                    (int) ($_POST["sort_order"] ?? 0),
                    (int) ($_POST["is_active"] ?? 1)
                ]);

        json_response(["success" => true, "message" => "Testimonial created"]);
    }

    public static function updateTestimonial(): void
    {
        require_admin();
        $id = (int) ($_POST["id"] ?? 0);
        if (!$id)
            json_response(["success" => false, "message" => "id required"], 400);

        $set = [];
        $params = [];
        foreach (["name", "role", "message", "rating", "sort_order", "is_active"] as $f) {
            if (isset($_POST[$f])) {
                $set[] = "$f=?";
                $params[] = $_POST[$f];
            }
        }

        if (!empty($_FILES["image"])) {
            $res = handle_image_upload("image");
            if (!$res["ok"])
                json_response(["success" => false, "message" => $res["message"]], 400);
            $set[] = "image_url=?";
            $params[] = $res["url"];
        }

        if (!$set)
            json_response(["success" => false, "message" => "No fields"], 400);

        $params[] = $id;
        db()->prepare("UPDATE home_testimonials SET " . implode(",", $set) . " WHERE id=?")
            ->execute($params);

        json_response(["success" => true, "message" => "Testimonial updated"]);
    }

    public static function deleteTestimonial(): void
    {
        require_admin();
        $b = get_json_body();
        $id = (int) ($b["id"] ?? 0);
        if (!$id)
            json_response(["success" => false, "message" => "id required"], 400);

        db()->prepare("DELETE FROM home_testimonials WHERE id=?")->execute([$id]);
        json_response(["success" => true, "message" => "Testimonial deleted"]);
    }

    public static function listVideos(): void
    {
        require_admin();

        $rows = db()->query("
        SELECT *
        FROM home_videos
        ORDER BY sort_order ASC
    ")->fetchAll();

        foreach ($rows as &$r) {
            $r["thumbnail_url"] = asset_url($r["thumbnail_url"]);
        }

        json_response([
            "success" => true,
            "data" => $rows
        ]);
    }

    public static function createVideo(): void
    {
        require_admin();

        $videoUrl = trim($_POST["video_url"] ?? "");
        if (!$videoUrl) {
            json_response(["success" => false, "message" => "video_url required"], 400);
        }

        $thumb = null;
        if (!empty($_FILES["thumbnail"])) {
            $res = handle_image_upload("thumbnail");
            if (!$res["ok"]) {
                json_response(["success" => false, "message" => $res["message"]], 400);
            }
            $thumb = $res["url"];
        }

        db()->prepare("
        INSERT INTO home_videos
        (title, video_url, thumbnail_url, sort_order, is_active)
        VALUES (?,?,?,?,?)
    ")->execute([
                    $_POST["title"] ?? null,
                    $videoUrl,
                    $thumb,
                    (int) ($_POST["sort_order"] ?? 0),
                    (int) ($_POST["is_active"] ?? 1)
                ]);

        json_response([
            "success" => true,
            "message" => "Video created"
        ]);
    }
    public static function updateVideo(): void
    {
        require_admin();

        $id = (int) ($_POST["id"] ?? 0);
        if (!$id) {
            json_response(["success" => false, "message" => "id required"], 400);
        }

        $set = [];
        $params = [];

        foreach (["title", "video_url", "sort_order", "is_active"] as $f) {
            if (isset($_POST[$f])) {
                $set[] = "$f = ?";
                $params[] = $_POST[$f];
            }
        }

        if (!empty($_FILES["thumbnail"])) {
            $res = handle_image_upload("thumbnail");
            if (!$res["ok"]) {
                json_response(["success" => false, "message" => $res["message"]], 400);
            }
            $set[] = "thumbnail_url = ?";
            $params[] = $res["url"];
        }

        if (!$set) {
            json_response(["success" => false, "message" => "No fields"], 400);
        }

        $params[] = $id;

        db()->prepare("
        UPDATE home_videos
        SET " . implode(",", $set) . "
        WHERE id = ?
    ")->execute($params);

        json_response([
            "success" => true,
            "message" => "Video updated"
        ]);
    }
    public static function deleteVideo(): void
    {
        require_admin();
        $b = get_json_body();

        $id = (int) ($b["id"] ?? 0);
        if (!$id) {
            json_response(["success" => false, "message" => "id required"], 400);
        }

        db()->prepare("DELETE FROM home_videos WHERE id = ?")->execute([$id]);

        json_response([
            "success" => true,
            "message" => "Video deleted"
        ]);
    }
    public static function listFaqs(): void
    {
        require_admin();

        $rows = db()->query("
        SELECT *
        FROM home_faqs
        ORDER BY sort_order ASC
    ")->fetchAll();

        json_response([
            "success" => true,
            "data" => $rows
        ]);
    }
    public static function createFaq(): void
    {
        require_admin();

        $q = trim($_POST["question"] ?? "");
        $a = trim($_POST["answer"] ?? "");

        if (!$q || !$a) {
            json_response(["success" => false, "message" => "question & answer required"], 400);
        }

        db()->prepare("
        INSERT INTO home_faqs
        (question, answer, sort_order, is_active)
        VALUES (?,?,?,?)
    ")->execute([
                    $q,
                    $a,
                    (int) ($_POST["sort_order"] ?? 0),
                    (int) ($_POST["is_active"] ?? 1)
                ]);

        json_response([
            "success" => true,
            "message" => "FAQ created"
        ]);
    }
    public static function updateFaq(): void
    {
        require_admin();

        $id = (int) ($_POST["id"] ?? 0);
        if (!$id) {
            json_response(["success" => false, "message" => "id required"], 400);
        }

        $set = [];
        $params = [];

        foreach (["question", "answer", "sort_order", "is_active"] as $f) {
            if (isset($_POST[$f])) {
                $set[] = "$f = ?";
                $params[] = $_POST[$f];
            }
        }

        if (!$set) {
            json_response(["success" => false, "message" => "No fields"], 400);
        }

        $params[] = $id;

        db()->prepare("
        UPDATE home_faqs
        SET " . implode(",", $set) . "
        WHERE id = ?
    ")->execute($params);

        json_response([
            "success" => true,
            "message" => "FAQ updated"
        ]);
    }
    public static function deleteFaq(): void
    {
        require_admin();
        $b = get_json_body();

        $id = (int) ($b["id"] ?? 0);
        if (!$id) {
            json_response(["success" => false, "message" => "id required"], 400);
        }

        db()->prepare("DELETE FROM home_faqs WHERE id = ?")->execute([$id]);

        json_response([
            "success" => true,
            "message" => "FAQ deleted"
        ]);
    }

     /* =========================
       PUBLIC – INTRO
    ========================= */
    public static function intro(): void
    {
        $row = db()->query("
            SELECT heading, sub_heading, description, points, closing_text
            FROM home_intro
            LIMIT 1
        ")->fetch();

        if ($row && $row["points"]) {
            $row["points"] = json_decode($row["points"], true);
        }

        json_response([
            "success" => true,
            "data" => $row ?: []
        ]);
    }

    /* =========================
       ADMIN – UPDATE INTRO
    ========================= */
    public static function updateIntro(): void
    {
        require_admin();
        $b = get_json_body();

        $allowed = [
            "heading",
            "sub_heading",
            "description",
            "points",
            "closing_text"
        ];

        $set = [];
        $params = [];

        foreach ($allowed as $f) {
            if (array_key_exists($f, $b)) {
                if ($f === "points") {
                    $set[] = "points = ?";
                    $params[] = json_encode($b["points"]);
                } else {
                    $set[] = "$f = ?";
                    $params[] = $b[$f];
                }
            }
        }

        if (!$set) {
            json_response([
                "success" => false,
                "message" => "No fields provided"
            ], 400);
        }

        db()->prepare(
            "UPDATE home_intro SET " . implode(",", $set) . " LIMIT 1"
        )->execute($params);

        json_response([
            "success" => true,
            "message" => "Home intro updated"
        ]);
    }

}
