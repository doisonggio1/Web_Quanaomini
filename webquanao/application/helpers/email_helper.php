<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function send_email($to, $to_name, $subject, $body, $altBody)
{
    require_once APPPATH . 'third_party/PHPMailer/src/Exception.php';
    require_once APPPATH . 'third_party/PHPMailer/src/PHPMailer.php';
    require_once APPPATH . 'third_party/PHPMailer/src/SMTP.php';

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username = getenv('MAIL_USERNAME');
        $mail->Password = getenv('MAIL_PASSWORD');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom(getenv('MAIL_USERNAME'), 'Shop quần áo mini');
        $mail->addAddress($to, $to_name);
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = $altBody;
        return $mail->send();
    } catch (Exception $e) {
        return false;
    }
}

