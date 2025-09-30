<?php
// Pagina di registrazione cliente e accesso a inserisci_noleggio.php
require_once __DIR__ . '/../src/bootstrap.php';

session_start();

$messaggio = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $cognome = trim($_POST['cognome'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $indirizzo = trim($_POST['indirizzo'] ?? '');
    $password = $_POST['password'] ?? '';
    $privacy_marketing = isset($_POST['privacy_marketing']) ? 1 : 0;
    $privacy_terzi = isset($_POST['privacy_terzi']) ? 1 : 0;
    $privacy_consenso = isset($_POST['privacy_consenso']) ? 1 : 0;
    $password2 = $_POST['password2'] ?? '';
    // Controllo password: almeno una minuscola, una maiuscola, un numero e un carattere speciale
    $password_valid = (
        strlen($password) >= 8 &&
        preg_match('/[a-z]/', $password) &&
        preg_match('/[A-Z]/', $password) &&
        preg_match('/[0-9]/', $password) &&
        preg_match('/[^a-zA-Z0-9]/', $password)
    );
    // Controllo se email già registrata
    $stmt_check = $pdo->prepare('SELECT id FROM cliente WHERE email = ? LIMIT 1');
    $stmt_check->execute([$email]);
    $email_esiste = $stmt_check->fetchColumn();
    if ($email_esiste) {
        $messaggio = 'Indirizzo email già registrato. Usa un altro indirizzo o accedi.';
    } else if ($nome && $cognome && $email && $password && $password_valid && $password === $password2) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        // Crea sempre un nuovo cliente
        $stmt = $pdo->prepare('INSERT INTO cliente (nome, cognome, telefono, email, indirizzo, password, stato, privacy_marketing, privacy_terzi, privacy_consenso) VALUES (?, ?, ?, ?, ?, ?, 1, ?, ?, ?)');
        $stmt->execute([$nome, $cognome, $telefono, $email, $indirizzo, $password_hash, $privacy_marketing, $privacy_terzi, $privacy_consenso]);
        $id = $pdo->lastInsertId();
        $_SESSION['cliente_id'] = $id;
        header('Location: modifica_noleggio.php');
        exit;
    } else {
        if ($password !== $password2) {
            $messaggio = 'Le password non coincidono.';
        } else {
            $messaggio = 'Compila tutti i campi obbligatori e scegli una password di almeno 8 caratteri, con almeno una minuscola, una maiuscola, un numero e un carattere speciale.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Registrazione Cliente</title>
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
    <h1>Registrati come Cliente</h1>
    <?php if ($messaggio): ?><div class="msg"><?= $messaggio ?></div><?php endif; ?>
    <form method="post">
        <label for="nome">Nome*</label>
        <input type="text" id="nome" name="nome" required>
        <label for="cognome">Cognome*</label>
        <input type="text" id="cognome" name="cognome" required>
        <label for="telefono">Telefono</label>
        <input type="text" id="telefono" name="telefono">
        <label for="email">Email*</label>
        <input type="email" id="email" name="email" required>
    <label for="indirizzo">Indirizzo</label>
    <input type="text" id="indirizzo" name="indirizzo">
    <label for="password">Password*</label>
    <input type="password" id="password" name="password" required minlength="8" placeholder="Almeno 8 caratteri, 1 maiuscola, 1 minuscola, 1 numero, 1 speciale">
    <label for="password2">Ripeti Password*</label>
    <input type="password" id="password2" name="password2" required minlength="8" placeholder="Ripeti la password">
        <div style="margin-top:1em;">
            <label style="display:flex;align-items:center;gap:0.5em;font-size:0.97em;">
                <input type="checkbox" name="privacy_marketing" value="1"> Acconsento a ricevere comunicazioni marketing
            </label>
            <label style="display:flex;align-items:center;gap:0.5em;font-size:0.97em;">
                <input type="checkbox" name="privacy_terzi" value="1"> Acconsento alla comunicazione dei dati a terzi
            </label>
            <label style="display:flex;align-items:center;gap:0.5em;font-size:0.97em;">
                <input type="checkbox" name="privacy_consenso" value="1" required> Acconsento al trattamento dei dati personali (obbligatorio)
            </label>
        </div>
        <button type="submit">Registrati e accedi</button>
    </form>
    <div style="margin-top:1em;font-size:0.95em;">
        <a href="accesso_cliente.php">Hai già un account? Accedi</a>
    </div>
</div>
</body>
</html>
