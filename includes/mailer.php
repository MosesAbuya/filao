<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/db.php';

function sendSiteEmail($toEmail, $toName, $subject, $body, $isHtml = true) {
    $pdo = getPDO();
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
    $settings_raw = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    $host = $settings_raw['smtp_host'] ?? '';
    $port = $settings_raw['smtp_port'] ?? '587';
    $user = $settings_raw['smtp_username'] ?? '';
    $pass = $settings_raw['smtp_password'] ?? '';
    $fromEmail = $settings_raw['smtp_from_email'] ?? 'info@filaoadventures.com';
    $fromName = $settings_raw['smtp_from_name'] ?? 'Filao Adventures';

    // Ensure we don't crash the server if settings are missing, throw our own exception.
    if (empty($host) || empty($user) || empty($pass)) {
        throw new Exception("SMTP settings are not fully configured in the admin dashboard.");
    }

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = $host;
        $mail->SMTPAuth   = true;
        $mail->Username   = $user;
        $mail->Password   = $pass;
        $mail->SMTPSecure = ($port == 465) ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $port;

        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML($isHtml);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>'], "\n", $body));

        $mail->send();
        return true;
    } catch (Exception $e) {
        throw new Exception("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }
}
