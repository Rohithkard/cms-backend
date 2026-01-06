<?php
require_once __DIR__ . "/../core/response.php";

function current_path(): string
{
    $uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

    // remove trailing slash
    if ($uri !== "/" && str_ends_with($uri, "/")) {
        $uri = rtrim($uri, "/");
    }

    return $uri;
}

function route(string $method, string $path, callable $handler): void
{
    if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
        json_response(["success" => true]);
    }

    $reqMethod = $_SERVER["REQUEST_METHOD"];
    $reqPath   = current_path();

    if ($reqMethod === $method && $reqPath === $path) {
        $handler();
        exit;
    }
}

function not_found(): void
{
    json_response([
        "success" => false,
        "message" => "Route not found"
    ], 404);
}