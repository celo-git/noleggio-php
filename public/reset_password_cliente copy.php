<?php
// Pagina per il reset della password cliente
require_once __DIR__ . '/../src/bootstrap.php';
$messaggio = '';
$successo = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $nuova_password = $_POST['nuova_password'] ?? '';
    $nuova_password2 = $_POST['nuova_password2'] ?? '';
    // Controllo password: almeno una minuscola, una maiuscola, un numero e un carattere speciale
    $password_valid = (
        strlen($nuova_password) >= 8 &&
        preg_match('/[a-z]/', $nuova_password) &&
        preg_match('/[A-Z]/', $nuova_password) &&
        preg_match('/[0-9]/', $nuova_password) &&
        preg_match('/[^a-zA-Z0-9]/', $nuova_password)
    );
    if ($email && $nuova_password && $nuova_password2 && $password_valid && $nuova_password === $nuova_password2) {
        $stmt = $pdo->prepare('SELECT id FROM cliente WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $cliente_id = $stmt->fetchColumn();
        if ($cliente_id) {
            $password_hash = password_hash($nuova_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('UPDATE cliente SET password = ? WHERE id = ?');
            $stmt->execute([$password_hash, $cliente_id]);
            $successo = true;
            $messaggio = 'Password aggiornata con successo. Ora puoi accedere.';
        } else {
            $messaggio = 'Email non trovata.';
        }
    } else {
        if ($nuova_password !== $nuova_password2) {
            $messaggio = 'Le password non coincidono.';
        } else {
            $messaggio = 'Compila tutti i campi e scegli una password valida.';
        }
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
        <label for="nuova_password">Nuova Password</label>
        <input type="password" id="nuova_password" name="nuova_password" required minlength="8" placeholder="Almeno 8 caratteri, 1 maiuscola, 1 minuscola, 1 numero, 1 speciale">
        <label for="nuova_password2">Ripeti Nuova Password</label>
        <input type="password" id="nuova_password2" name="nuova_password2" required minlength="8" placeholder="Ripeti la password">
        <button type="submit">Reset Password</button>
    </form>
    <div style="margin-top:1em;font-size:0.95em;">
        <a href="accesso_cliente.php">Torna all'accesso</a>
    </div>
</div>
</body>
</html>
