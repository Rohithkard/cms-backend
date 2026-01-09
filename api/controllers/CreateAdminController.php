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

    public static function install(): void
    {
        $config = require __DIR__ . "/../config/config.php";

        $dbHost = $config["db"]["host"];
        $dbUser = $config["db"]["user"];
        $dbPass = $config["db"]["pass"];
        $dbName = $config["db"]["name"];

        try {
            // 🔗 Connect WITHOUT database first
            $pdo = new PDO(
                "mysql:host=$dbHost;charset=utf8mb4",
                $dbUser,
                $dbPass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            $sqlFile = __DIR__ . "/../sql/database.sql";

            if (!file_exists($sqlFile)) {
                json_response([
                    "success" => false,
                    "message" => "database.sql file not found"
                ], 500);
            }

            $sql = file_get_contents($sqlFile);

            // 🧨 Execute full SQL
            $pdo->exec($sql);

            json_response([
                "success" => true,
                "message" => "Database & tables created successfully"
            ]);

        } catch (PDOException $e) {
            json_response([
                "success" => false,
                "message" => $e->getMessage()
            ], 500);
        }
    }


}
