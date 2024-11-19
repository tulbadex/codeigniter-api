<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!function_exists('sendEmail')) {
    function sendEmail($to, $subject, $message)
    {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = getenv('email.SMTPHost');
            $mail->SMTPAuth   = true;
            $mail->Username   = getenv('email.SMTPUser');
            $mail->Password   = getenv('email.SMTPPass');
            $mail->SMTPSecure = getenv('email.SMTPCrypto');
            $mail->Port       = getenv('email.SMTPPort');

            // Recipients
            $mail->setFrom(getenv('email.fromEmail'), getenv('email.fromName'));
            $mail->addAddress($to);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $message;

            $mail->send();
            return true;
        } catch (Exception $e) {
            log_message('error', "Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }
}
