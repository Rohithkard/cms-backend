<?php
require_once __DIR__ . "/../core/response.php";

function route(string $method, string $path, callable $handler): void {
  $reqMethod = $_SERVER["REQUEST_METHOD"];
  $reqPath = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

  // find "/api/public/index.php/..."
  $base = $_SERVER["SCRIPT_NAME"]; // /api/public/index.php
  $subPath = "/";
  if (strpos($reqPath, $base) === 0) {
    $subPath = substr($reqPath, strlen($base));
    if ($subPath === "") $subPath = "/";
  }

  if ($reqMethod === "OPTIONS") {
    json_response(["success"=>true], 200);
  }

  if ($reqMethod === $method && $subPath === $path) {
    $handler();
  }
}

function not_found(): void {
  json_response(["success"=>false,"message"=>"Route not found"], 404);
}
