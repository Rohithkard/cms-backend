<?php

return [
  "db" => [
    "host" => "localhost",
    "name" => "simple_cms_api",
    "user" => "simple_cms_api",   // ✅ NOT root
    "pass" => "znGeW884ZkSj4wLX",
    "charset" => "utf8mb4",
  ],

   "app" => [
    "base_url" => "https://ecommerce.stokai.live"
  ],

  "upload" => [
    "dir" => __DIR__ . "/../uploads",
    "url_base" => "/uploads"
  ],

  "auth" => [
    "token_days" => 15
  ],
  "mail" => [
    "from_email" => "no-reply@ecommerce.stokai.live",
    "from_name"  => "Otp Service"
  ]
];
