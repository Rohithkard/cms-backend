<?php

function send_html_mail(
    string $to,
    string $subject,
    string $html,
    string $fromEmail,
    string $fromName = "Website"
): bool {

    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: {$fromName} <{$fromEmail}>\r\n";
    $headers .= "Reply-To: {$fromEmail}\r\n";

    return mail($to, $subject, $html, $headers);
}
