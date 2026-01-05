<?php
function json_response($data, int $code = 200): void {
  http_response_code($code);
  header("Content-Type: application/json; charset=utf-8");
  header("Access-Control-Allow-Origin: *");
  header("Access-Control-Allow-Headers: Content-Type, Authorization");
  header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
  echo json_encode($data, JSON_UNESCAPED_UNICODE);
  exit;
}
