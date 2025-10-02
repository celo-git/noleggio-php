
<?php
// Pagina per invio email di reset password cliente
require_once __DIR__ . '/../src/bootstrap.php';
$messaggio = '';
$successo = false;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php'; // Assicurati di aver installato PHPMailer con Composer

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if ($email) {
        $stmt = $pdo->prepare('SELECT id FROM cliente WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $cliente_id = $stmt->fetchColumn();
        if ($cliente_id) {
            // Genera token sicuro
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + 3600); // valido 1 ora

            // Salva token e scadenza nel database (aggiungi i campi se non esistono)
            $stmt = $pdo->prepare('UPDATE cliente SET reset_token = ?, reset_expires = ? WHERE id = ?');
            $stmt->execute([$token, $expires, $cliente_id]);
//Se usi Gmail, non puoi usare la password normale. Devi generare una "password per app" dalle impostazioni di sicurezza di Google.
//Vai su Google Account > Sicurezza > Password per le app e crea una password per l’applicazione.
            // Prepara email
            $link = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/conferma_reset_password_cliente.php?token=' . $token;
            $subject = 'Reset password noleggio';
            $body = "Clicca sul link per reimpostare la tua password:\n$link\nIl link scade tra 1 ora.";

            // Invia email (usa mail() o una libreria, qui esempio base)
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // SMTP server
    $mail->SMTPAuth = true;
    $mail->Username = 'mattemartivince@gmail.com'; // tua email
    $mail->Password = 'ofxnyhagbbfmkxfu'; // tua password o app password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('mattemartivince@gmail.com', 'Noleggio');
    $mail->addAddress($email);
    $mail->Subject = $subject;
    $mail->Body = $body;

    $mail->send();
    $successo = true;
    $messaggio = 'Ti abbiamo inviato una email con il link per reimpostare la password.';
} catch (Exception $e) {
    $messaggio = 'Errore invio email: ' . $mail->ErrorInfo;
}

            $successo = true;
            $messaggio = 'Ti abbiamo inviato una email con il link per reimpostare la password.';
        } else {
            $messaggio = 'Email non trovata.';
        }
    } else {
        $messaggio = 'Inserisci la tua email.';
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Reset Password Cliente</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; }
        .container { max-width: 400px; margin: 40px auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); padding: 2rem; }
        h1 { color: #2c3e50; }
        label { display: block; margin-top: 1em; }
        input { padding: 0.5em; width: 100%; margin-top: 0.5em; }
        .msg { color: red; margin-bottom: 1em; }
        .success { color: green; margin-bottom: 1em; }
        button { background:#2980b9;color:#fff;padding:0.7em 1.5em;border-radius:5px;text-decoration:none;font-weight:bold;border:none;margin-top:1em; }
    </style>
</head>
<body>
<div class="container">
    <h1>Reset Password Cliente</h1>
    <?php if ($messaggio): ?>
        <div class="<?= $successo ? 'success' : 'msg' ?>"><?= $messaggio ?></div>
    <?php endif; ?>
    <form method="post">
        <label for="email">Email registrata</label>
        <input type="email" id="email" name="email" required>
        <button type="submit">Invia link di reset</button>
    </form>
    <div style="margin-top:1em;font-size:0.95em;">
        <a href="accesso_cliente.php">Torna all'accesso</a>
    </div>
</div>
</body>
</html>

