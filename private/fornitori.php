<?php
session_start();
if (!isset($_SESSION['utente_id'])) {
    header('Location: ../public/accesso.php');
    exit;
}
?>
<?php
require_once __DIR__ . '/../src/bootstrap.php';

// Inserimento nuovo fornitore
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare('INSERT INTO fornitore (nome, cognome, telefono, email, indirizzo, note) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([
        $_POST['nome'] ?? '',
        $_POST['cognome'] ?? '',
        $_POST['telefono'] ?? '',
        $_POST['email'] ?? '',
        $_POST['indirizzo'] ?? '',
        $_POST['note'] ?? ''
    ]);
    header('Location: fornitori.php');
    exit;
}

// Recupera tutti i fornitori
$fornitori = $pdo->query('SELECT * FROM fornitore ORDER BY cognome, nome')->fetchAll();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Fornitori</title>
    <style>
        table { border-collapse: collapse; width: 100%; background: #fff; }
        th, td { border: 1px solid #ccc; padding: 0.5em 0.7em; }
        th { background: #eee; }
        tr:nth-child(even) { background: #f8f8f8; }
        .form-box { background: #f4f4f4; padding: 1em; border-radius: 8px; margin-bottom: 2em; }
    </style>
</head>
<body>
    <div style="margin-bottom:2em;">
        <a href="index.php" style="background:#2980b9;color:#fff;padding:0.7em 1.5em;border-radius:5px;text-decoration:none;font-weight:bold;">Torna alla home</a>
    </div>
    <h1>Fornitori</h1>
    <div class="form-box">
        <form method="post" style="display:flex;flex-wrap:wrap;gap:1em;">
            <div style="display:flex;flex-direction:column;min-width:180px;">
                <label for="nome">Nome:</label>
                <input type="text" id="nome" name="nome" required>
            </div>
            <div style="display:flex;flex-direction:column;min-width:180px;">
                <label for="cognome">Cognome:</label>
                <input type="text" id="cognome" name="cognome" required>
            </div>
            <div style="display:flex;flex-direction:column;min-width:180px;">
                <label for="telefono">Telefono:</label>
                <input type="text" id="telefono" name="telefono">
            </div>
            <div style="display:flex;flex-direction:column;min-width:180px;">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email">
            </div>
            <div style="display:flex;flex-direction:column;min-width:180px;">
                <label for="indirizzo">Indirizzo:</label>
                <input type="text" id="indirizzo" name="indirizzo">
            </div>
            <div style="display:flex;flex-direction:column;min-width:180px;">
                <label for="note">Note:</label>
                <input type="text" id="note" name="note">
            </div>
            <div style="align-self:flex-end;">
                <button type="submit" style="background:#2980b9;color:#fff;padding:0.7em 1.5em;border-radius:5px;text-decoration:none;font-weight:bold;border:none;">Aggiungi fornitore</button>
            </div>
        </form>
    </div>
    <table>
        <thead>
            <tr>
                <th>Nome</th>
                <th>Cognome</th>
                <th>Telefono</th>
                <th>Email</th>
                <th>Indirizzo</th>
                <th>Note</th>
                <th>Azioni</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($fornitori as $f): ?>
            <tr>
                <td><?= htmlspecialchars($f['nome']) ?></td>
                <td><?= htmlspecialchars($f['cognome']) ?></td>
                <td><?= htmlspecialchars($f['telefono']) ?></td>
                <td><?= htmlspecialchars($f['email']) ?></td>
                <td><?= htmlspecialchars($f['indirizzo']) ?></td>
                <td><?= htmlspecialchars($f['note']) ?></td>
                <td><a href="modifica_fornitore.php?id=<?= $f['id'] ?>">Modifica</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
