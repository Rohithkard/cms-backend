<?php

function handle_image_upload(string $field): array
{
    if (!isset($_FILES[$field])) {
        return ["ok" => false, "message" => "No file uploaded"];
    }

    $file = $_FILES[$field];

    if ($file["error"] !== UPLOAD_ERR_OK) {
        return ["ok" => false, "message" => "Upload error"];
    }

    // ✅ Allowed extensions
    $allowedExt = ["jpg", "jpeg", "png", "webp", "gif"];
    $allowedMime = [
        "image/jpeg",
        "image/png",
        "image/webp",
        "image/gif"
    ];

    $tmp  = $file["tmp_name"];
    $name = $file["name"];
    $size = $file["size"];
    $mime = $file["type"]; // browser-provided MIME

    // Extension check
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt)) {
        return ["ok" => false, "message" => "Invalid file extension"];
    }

    // MIME check
    if (!in_array($mime, $allowedMime)) {
        return ["ok" => false, "message" => "Invalid file type"];
    }

    // Size limit (5MB)
    if ($size > 5 * 1024 * 1024) {
        return ["ok" => false, "message" => "File too large (max 5MB)"];
    }

    // Upload directory
    $uploadDir = __DIR__ . "/../uploads";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    $filename = uniqid("img_", true) . "." . $ext;
    $target = $uploadDir . "/" . $filename;

    if (!move_uploaded_file($tmp, $target)) {
        return ["ok" => false, "message" => "Failed to save file"];
    }

    return [
        "ok" => true,
        "url" => "/uploads/" . $filename
    ];
}
