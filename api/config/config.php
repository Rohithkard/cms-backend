<?php
return [
  "db" => [
    "host" => "localhost",
    "name" => "simple_cms_api",
    "user" => "root",
    "pass" => "znGeW884ZkSj4wLX",
    "charset" => "utf8mb4",
  ],
  "upload" => [
    "dir" => __DIR__ . "/../uploads",
    "url_base" => "/api/uploads" // adjust based on hosting path
  ],
  "auth" => [
    "token_days" => 15
  ]
];
