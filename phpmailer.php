Consigliato:
Usa PHPMailer con SMTP.
Installa PHPMailer con Composer:
composer require phpmailer/phpmailer



<?php
// ...existing code...
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php'; // Assicurati di aver installato PHPMailer con Composer

// ...dentro if ($cliente_id) { ... }
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // SMTP server
    $mail->SMTPAuth = true;
    $mail->Username = 'TUO_EMAIL@gmail.com'; // tua email
    $mail->Password = 'TUA_PASSWORD'; // tua password o app password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('TUO_EMAIL@gmail.com', 'Noleggio');
    $mail->addAddress($email);
    $mail->Subject = $subject;
    $mail->Body = $body;

    $mail->send();
    $successo = true;
    $messaggio = 'Ti abbiamo inviato una email con il link per reimpostare la password.';
} catch (Exception $e) {
    $messaggio = 'Errore invio email: ' . $mail->ErrorInfo;
}