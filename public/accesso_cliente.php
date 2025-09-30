<?php
// Pagina di accesso cliente
require_once __DIR__ . '/../src/bootstrap.php';
session_start();
$messaggio = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($email && $password) {
        $stmt = $pdo->prepare('SELECT id, password FROM cliente WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($cliente && password_verify($password, $cliente['password'])) {
            $_SESSION['cliente_id'] = $cliente['id'];
            header('Location: modifica_noleggio.php');
            exit;
        } else {
            $messaggio = 'Email o password non corretti.';
        }
    } else {
        $messaggio = 'Inserisci email e password.';
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Accesso Cliente</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; }
        .container { max-width: 400px; margin: 40px auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); padding: 2rem; }
        h1 { color: #2c3e50; }
        label { display: block; margin-top: 1em; }
        input { padding: 0.5em; width: 100%; margin-top: 0.5em; }
        .msg { color: red; margin-bottom: 1em; }
        button { background:#2980b9;color:#fff;padding:0.7em 1.5em;border-radius:5px;text-decoration:none;font-weight:bold;border:none;margin-top:1em; }
    </style>
</head>
<body>
<div class="container">
    <h1>Accesso Cliente</h1>
    <?php if ($messaggio): ?><div class="msg"><?= $messaggio ?></div><?php endif; ?>
    <form method="post">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
        <button type="submit">Accedi</button>
    </form>
    <div style="margin-top:1em;font-size:0.95em;">
    <a href="registrazione_cliente.php">Non hai un account? Registrati</a><br>
    <a href="reset_password_cliente.php">Password dimenticata? Reset</a>
    </div>
</div>
</body>
</html>
