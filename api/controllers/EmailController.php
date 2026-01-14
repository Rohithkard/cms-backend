<?php
require_once __DIR__ . "/../core/response.php";
require_once __DIR__ . "/../core/mailer.php";
require_once __DIR__ . "/../config/config.php";

class EmailController
{
    
    public static function send(): void
    {
        $b = get_json_body();

        $required = ["to", "subject", "html"];
        foreach ($required as $f) {
            if (empty($b[$f])) {
                json_response([
                    "success" => false,
                    "message" => "$f required"
                ], 400);
            }
        }

        // 🔒 Basic email validation
        if (!filter_var($b["to"], FILTER_VALIDATE_EMAIL)) {
            json_response([
                "success" => false,
                "message" => "Invalid email address"
            ], 400);
        }

        $config = require __DIR__ . "/../config/config.php";

        $fromEmail = $config["mail"]["from_email"] ?? "no-reply@yourdomain.com";
        $fromName  = $config["mail"]["from_name"] ?? "Website";

        $sent = send_html_mail(
            $b["to"],
            $b["subject"],
            $b["html"],
            $fromEmail,
            $fromName
        );

        if (!$sent) {
            json_response([
                "success" => false,
                "message" => "Failed to send email"
            ], 500);
        }

        json_response([
            "success" => true,
            "message" => "Email sent successfully"
        ]);
    }
}
