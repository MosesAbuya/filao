<?php
/**
 * mailer.php — Gracefully loads PHPMailer if available.
 * If PHPMailer files are missing, sendSiteEmail() will throw
 * an exception that callers can catch without crashing the page.
 */

$phpmailerAvailable = false;
$phpmailerBase = __DIR__ . '/PHPMailer/src/';

if (file_exists($phpmailerBase . 'Exception.php') &&
    file_exists($phpmailerBase . 'PHPMailer.php') &&
    file_exists($phpmailerBase . 'SMTP.php')) {
    require_once $phpmailerBase . 'Exception.php';
    require_once $phpmailerBase . 'PHPMailer.php';
    require_once $phpmailerBase . 'SMTP.php';
    $phpmailerAvailable = true;
}

require_once __DIR__ . '/db.php';

function sendSiteEmail($toEmail, $toName, $subject, $body, $isHtml = true) {
    global $phpmailerAvailable;

    if (!$phpmailerAvailable) {
        throw new \Exception("PHPMailer library is not installed on this server. Please upload the PHPMailer folder to includes/PHPMailer/.");
    }

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
        throw new \Exception("SMTP settings are not fully configured in the admin dashboard.");
    }

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();
        $mail->Host       = $host;
        $mail->SMTPAuth   = true;
        $mail->Username   = $user;
        $mail->Password   = $pass;
        $mail->SMTPSecure = ($port == 465) ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $port;

        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML($isHtml);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>'], "\n", $body));

        $mail->send();
        return true;
    } catch (\Exception $e) {
        throw new \Exception("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }
}
