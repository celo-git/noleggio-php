<?php
require_once __DIR__ . '/../src/bootstrap.php';

// Recupera il fornitore da modificare
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$fornitore = $pdo->prepare('SELECT * FROM fornitore WHERE id = ?');
$fornitore->execute([$id]);
$fornitore = $fornitore->fetch();
if (!$fornitore) {
    header('Location: fornitori.php');
    exit;
}

// Aggiorna il fornitore
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare('UPDATE fornitore SET nome=?, cognome=?, telefono=?, email=?, indirizzo=?, note=? WHERE id=?');
    $stmt->execute([
        $_POST['nome'] ?? '',
        $_POST['cognome'] ?? '',
        $_POST['telefono'] ?? '',
        $_POST['email'] ?? '',
        $_POST['indirizzo'] ?? '',
        $_POST['note'] ?? '',
        $id
    ]);
    header('Location: fornitori.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Modifica Fornitore</title>
    <style>
        .form-box { background: #f4f4f4; padding: 1em; border-radius: 8px; margin-bottom: 2em; }
    </style>
</head>
<body>
    <h1>Modifica Fornitore</h1>
    <div class="form-box">
        <form method="post">
            <label>Nome: <input type="text" name="nome" value="<?= htmlspecialchars($fornitore['nome']) ?>" required></label>
            <label>Cognome: <input type="text" name="cognome" value="<?= htmlspecialchars($fornitore['cognome']) ?>" required></label>
            <label>Telefono: <input type="text" name="telefono" value="<?= htmlspecialchars($fornitore['telefono']) ?>"></label>
            <label>Email: <input type="email" name="email" value="<?= htmlspecialchars($fornitore['email']) ?>"></label>
            <label>Indirizzo: <input type="text" name="indirizzo" value="<?= htmlspecialchars($fornitore['indirizzo']) ?>"></label>
            <label>Note: <input type="text" name="note" value="<?= htmlspecialchars($fornitore['note']) ?>"></label>
            <button type="submit">Salva modifiche</button>
        </form>
    </div>
    <p><a href="fornitori.php">Torna all'elenco fornitori</a></p>
</body>
</html>
