
<?php
require_once __DIR__ . '/../src/bootstrap.php';

$messaggio = '';
$successo = false;
$token = $_GET['token'] ?? '';

if (!$token) {
    $messaggio = 'Token non valido.';
} else {
    // Verifica token e scadenza
    $stmt = $pdo->prepare('SELECT id, email, reset_expires FROM cliente WHERE reset_token = ? LIMIT 1');
    $stmt->execute([$token]);
    $cliente = $stmt->fetch();

    if (!$cliente) {
        $messaggio = 'Token non valido o già usato.';
    } elseif (strtotime($cliente['reset_expires']) < time()) {
        $messaggio = 'Il link di reset è scaduto.';
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $password = $_POST['password'] ?? '';
        $password2 = $_POST['password2'] ?? '';
        if (strlen($password) < 6) {
            $messaggio = 'La password deve essere di almeno 6 caratteri.';
        } elseif ($password !== $password2) {
            $messaggio = 'Le password non coincidono.';
        } else {
            // Aggiorna la password e cancella il token
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('UPDATE cliente SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?');
            $stmt->execute([$hash, $cliente['id']]);
            $successo = true;
            $messaggio = 'Password aggiornata con successo! Ora puoi accedere.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Conferma Reset Password</title>
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
    <h1>Imposta nuova password</h1>
    <?php if ($messaggio): ?>
        <div class="<?= $successo ? 'success' : 'msg' ?>"><?= $messaggio ?></div>
    <?php endif; ?>
    <?php if (!$successo && $token && isset($cliente) && $cliente && strtotime($cliente['reset_expires']) >= time()): ?>
    <form method="post">
        <label for="password">Nuova password</label>
        <input type="password" id="password" name="password" required minlength="6">
        <label for="password2">Ripeti password</label>
        <input type="password" id="password2" name="password2" required minlength="6">
        <button type="submit">Salva nuova password</button>
    </form>
    <?php endif; ?>
    <div style="margin-top:1em;font-size:0.95em;">
        <a href="accesso_cliente.php">Torna all'accesso</a>
    </div>
</div>
</body>
</html>