<?php
$config = require __DIR__ . "/config.php";

function db(): PDO {
  static $pdo = null;
  if ($pdo) return $pdo;

  $cfg = (require __DIR__ . "/config.php")["db"];
  $dsn = "mysql:host={$cfg['host']};dbname={$cfg['name']};charset={$cfg['charset']}";
  $pdo = new PDO($dsn, $cfg["user"], $cfg["pass"], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
  ]);
  return $pdo;
}
