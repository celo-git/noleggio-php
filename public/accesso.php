<?php
session_start();
require_once __DIR__ . '/../src/bootstrap.php';

$errore = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT id, password FROM utenti WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $utente = $stmt->fetch();

    if ($utente && password_verify($password, $utente['password'])) {
        $_SESSION['utente_id'] = $utente['id'];
        header('Location: ../private/index.php');
        exit;
    } else {
        $errore = 'Credenziali non valide';
    }
}
?>
<?php
echo password_hash('admin123', PASSWORD_DEFAULT);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <title>Accesso Area Privata</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>Accesso Area Privata</h2>
    <?php if ($errore): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($errore) ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" name="username" id="username" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Accedi</button>
    </form>
</div>
</body>
</html>