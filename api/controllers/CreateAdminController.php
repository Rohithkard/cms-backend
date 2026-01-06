<?php

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../core/response.php";

class CreateAdminController
{
    public static function createAdmin(): void
    {
        $email = $_GET["email"] ?? "admin@example.com";
        $pass  = $_GET["pass"] ?? "Admin@123";
        $name  = $_GET["name"] ?? "Admin";

        if (!$email || !$pass) {
            json_response([
                "success" => false,
                "message" => "email and pass required"
            ], 400);
        }

        $hash = password_hash($pass, PASSWORD_BCRYPT);

        $pdo = db();

        // prevent duplicate admin
        $check = $pdo->prepare("SELECT id FROM admins WHERE email=? LIMIT 1");
        $check->execute([$email]);

        if ($check->fetch()) {
            json_response([
                "success" => false,
                "message" => "Admin already exists"
            ], 409);
        }

        $stmt = $pdo->prepare(
            "INSERT INTO admins (full_name, email, password_hash)
             VALUES (?, ?, ?)"
        );

        try {
            $stmt->execute([$name, $email, $hash]);

            json_response([
                "success" => true,
                "message" => "Admin created",
                "email" => $email,
                "password" => $pass   // ⚠️ show only for setup
            ]);
        } catch (Exception $e) {
            json_response([
                "success" => false,
                "message" => $e->getMessage()
            ], 500);
        }
    }
}
