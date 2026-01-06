<?php

function asset_url(?string $path): ?string
{
    if (!$path) return null;

    // Already absolute URL
    if (str_starts_with($path, "http://") || str_starts_with($path, "https://")) {
        return $path;
    }

    $cfg = require __DIR__ . "/../config/config.php";
    $base = rtrim($cfg["app"]["base_url"], "/");

    return $base . "/" . ltrim($path, "/");
}
