<?php
function handle_image_upload(string $fieldName = "image"): array {
  $cfg = (require __DIR__ . "/../config/config.php")["upload"];

  if (!isset($_FILES[$fieldName])) {
    return ["ok"=>false, "message"=>"No file uploaded"];
  }

  $file = $_FILES[$fieldName];
  if ($file["error"] !== UPLOAD_ERR_OK) {
    return ["ok"=>false, "message"=>"Upload error"];
  }

  $tmp = $file["tmp_name"];
  $mime = mime_content_type($tmp);
  $allowed = ["image/jpeg","image/png","image/webp"];
  if (!in_array($mime, $allowed, true)) {
    return ["ok"=>false, "message"=>"Only JPG/PNG/WEBP allowed"];
  }

  if (!is_dir($cfg["dir"])) mkdir($cfg["dir"], 0775, true);

  $ext = match($mime) {
    "image/jpeg" => "jpg",
    "image/png" => "png",
    "image/webp" => "webp",
    default => "bin"
  };

  $name = date("Ymd_His") . "_" . bin2hex(random_bytes(6)) . "." . $ext;
  $dest = rtrim($cfg["dir"], "/") . "/" . $name;

  if (!move_uploaded_file($tmp, $dest)) {
    return ["ok"=>false, "message"=>"Failed to save file"];
  }

  $url = rtrim($cfg["url_base"], "/") . "/" . $name;
  return ["ok"=>true, "url"=>$url];
}
