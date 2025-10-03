<?php
session_start();
if (!isset($_SESSION['utente_id'])) {
    header('Location: ../public/accesso.php');
    exit;
}
// Timeout di 5 minuti (300 secondi)
$timeout = 300;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    session_unset();
    session_destroy();
    header('Location: ../public/accesso.php?timeout=1');
    exit;
}
$_SESSION['last_activity'] = time();
echo "<script>
    setTimeout(function() {
        window.location.href = '../public/accesso.php?timeout=1';
    }, " . ($timeout * 1000) . ");
</script>";
?>

<?php
// Pagina per la gestione dei fornitori
require_once __DIR__ . '/../src/bootstrap.php';

// Eliminazione fornitore
if (isset($_GET['elimina'])) {
    $id = (int)$_GET['elimina'];
    $stmt = $pdo->prepare('DELETE FROM fornitore WHERE id = ?');
    $stmt->execute([$id]);
    header('Location: fornitori.php');
    exit;
}

// Modifica fornitore
$edit_id = null;
// Campi fornitore
$edit_data = [
    'nome' => '', 'cognome' => '', 'telefono' => '', 'email' => '', 'indirizzo' => '', 'codice_fiscale' => '', 'partita_iva' => '', 'stato' => 1
];
if (isset($_GET['modifica'])) {
    $edit_id = (int)$_GET['modifica'];
    $stmt = $pdo->prepare('SELECT nome, cognome, telefono, email, indirizzo, codice_fiscale, partita_iva, stato FROM fornitore WHERE id = ?');
    $stmt->execute([$edit_id]);
    $row = $stmt->fetch();
    if ($row) {
        $edit_data = $row;
    } else {
        $edit_id = null;
    }
}
if (isset($_POST['edit_id'])) {
    $id = (int)$_POST['edit_id'];
    $fields = ['nome','cognome','telefono','email','indirizzo','codice_fiscale','partita_iva','stato'];
    $values = [];
    foreach ($fields as $f) {
        $values[$f] = isset($_POST[$f]) ? trim($_POST[$f]) : '';
    }
    if ($values['nome'] && $values['cognome']) {
        $stmt = $pdo->prepare('UPDATE fornitore SET nome=?, cognome=?, telefono=?, email=?, indirizzo=?, codice_fiscale=?, partita_iva=?, stato=? WHERE id=?');
        $stmt->execute([
            $values['nome'], $values['cognome'], $values['telefono'], $values['email'], $values['indirizzo'], $values['codice_fiscale'], $values['partita_iva'], $values['stato'] ? 1 : 0, $id
        ]);
    }
    header('Location: fornitori.php');
    exit;
}

// Inserimento nuovo fornitore
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_fornitore'])) {
    $fields = ['nome','cognome','telefono','email','indirizzo','codice_fiscale','partita_iva','stato'];
    $values = [];
    foreach ($fields as $f) {
        $values[$f] = isset($_POST[$f]) ? trim($_POST[$f]) : '';
    }
    if ($values['nome'] && $values['cognome']) {
        $stmt = $pdo->prepare('INSERT INTO fornitore (nome, cognome, telefono, email, indirizzo, codice_fiscale, partita_iva, stato) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $values['nome'], $values['cognome'], $values['telefono'], $values['email'], $values['indirizzo'], $values['codice_fiscale'], $values['partita_iva'], $values['stato'] ? 1 : 0
        ]);
    }
    header('Location: fornitori.php');
    exit;
}

// Recupera tutti i fornitore
$fornitori = $pdo->query('SELECT * FROM fornitore ORDER BY cognome, nome')->fetchAll();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Fornitori</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4 url('https://cdn.pixabay.com/photo/2013/07/13/12/46/vintage-car-146185_1280.png') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 700px;
            margin: 40px auto;
            background: rgba(255,255,255,0.92);
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            padding: 2rem;
        }
        h1 { color: #2c3e50; }
        table { width:100%; border-collapse:collapse; margin-bottom:2em; }
        th, td { padding: 0.5em 0.7em; border-bottom: 1px solid #ddd; }
        th { background: #eee; }
        .add-form { margin-top: 2em; }
        label { display: block; margin-bottom: 0.5em; }
        input[type="text"], input[type="email"] { padding: 0.5em; width: 90%; }
        button { padding: 0.5em 1.2em; background: #2980b9; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #3498db; }
        a { text-decoration: none; }
        .form-box { background: #f4f4f4; padding: 1em; border-radius: 8px; margin-bottom: 2em; }
    </style>
</head>
<body>
<div style="margin-bottom:2em;text-align:left;">
    <a href="gestione.php" style="background:#2980b9;color:#fff;padding:0.7em 1.5em;border-radius:5px;text-decoration:none;font-weight:bold;">Torna alla home</a>
</div>
<h1 style="text-align:left;">Fornitori</h1>
<div class="form-box" style="text-align:left;">
    <form method="post">
        <?php if ($edit_id): ?>
            <input type="hidden" name="edit_id" value="<?= $edit_id ?>">
        <?php else: ?>
            <input type="hidden" name="add_fornitore" value="1">
        <?php endif; ?>
        <div style="display:flex;gap:1em;flex-wrap:wrap;align-items:flex-start;justify-content:flex-start;">
            <label style="display:flex;flex-direction:column;align-items:flex-start;">Nome: <input type="text" name="nome" required value="<?= isset($edit_data['nome']) && $edit_id ? htmlspecialchars($edit_data['nome']) : '' ?>"></label>
            <label style="display:flex;flex-direction:column;align-items:flex-start;">Cognome: <input type="text" name="cognome" required value="<?= isset($edit_data['cognome']) && $edit_id ? htmlspecialchars($edit_data['cognome']) : '' ?>"></label>
            <label style="display:flex;flex-direction:column;align-items:flex-start;">Telefono: <input type="text" name="telefono" value="<?= isset($edit_data['telefono']) && $edit_id ? htmlspecialchars($edit_data['telefono']) : '' ?>"></label>
            <label style="display:flex;flex-direction:column;align-items:flex-start;">Email: <input type="email" name="email" value="<?= isset($edit_data['email']) && $edit_id ? htmlspecialchars($edit_data['email']) : '' ?>"></label>
            <label style="display:flex;flex-direction:column;align-items:flex-start;">Indirizzo: <input type="text" name="indirizzo" value="<?= isset($edit_data['indirizzo']) && $edit_id ? htmlspecialchars($edit_data['indirizzo']) : '' ?>"></label>
            <label style="display:flex;flex-direction:column;align-items:flex-start;">Codice Fiscale: <input type="text" name="codice_fiscale" value="<?= isset($edit_data['codice_fiscale']) && $edit_id ? htmlspecialchars($edit_data['codice_fiscale']) : '' ?>"></label>
            <label style="display:flex;flex-direction:column;align-items:flex-start;">Partita IVA: <input type="text" name="partita_iva" value="<?= isset($edit_data['partita_iva']) && $edit_id ? htmlspecialchars($edit_data['partita_iva']) : '' ?>"></label>
            <label style="display:flex;flex-direction:column;align-items:flex-start;">Stato: <select name="stato">
                <option value="1" <?= (isset($edit_data['stato']) && $edit_id && $edit_data['stato']) ? 'selected' : '' ?>>Attivo</option>
                <option value="0" <?= (isset($edit_data['stato']) && $edit_id && !$edit_data['stato']) ? 'selected' : '' ?>>Non attivo</option>
            </select></label>
            <button type="submit" style="align-self:flex-end;">
                <?= $edit_id ? 'Salva modifiche' : 'Aggiungi fornitore' ?>
            </button>
            <?php if ($edit_id): ?>
                <a href="fornitori.php" style="align-self:flex-end;margin-left:1em;">Annulla</a>
            <?php endif; ?>
        </div>
    </form>
</div>
<table style="text-align:left;">
    <thead>
        <tr>
            <th>Nome</th>
            <th>Cognome</th>
            <th>Telefono</th>
            <th>Email</th>
            <th>Indirizzo</th>
            <th>Codice Fiscale</th>
            <th>Partita IVA</th>
            <th>Stato</th>
            <th>Azioni</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($fornitori as $c): ?>
        <tr>
            <td><?= htmlspecialchars($c['nome']) ?></td>
            <td><?= htmlspecialchars($c['cognome']) ?></td>
            <td><?= htmlspecialchars($c['telefono']) ?></td>
            <td><?= htmlspecialchars($c['email']) ?></td>
            <td><?= htmlspecialchars($c['indirizzo']) ?></td>
            <td><?= htmlspecialchars($c['codice_fiscale']) ?></td>
            <td><?= htmlspecialchars($c['partita_iva']) ?></td>
            <td><?= $c['stato'] ? 'Attivo' : 'Non attivo' ?></td>
            <td>
                <a href="?modifica=<?= $c['id'] ?>">Modifica</a> |
                <a href="?elimina=<?= $c['id'] ?>" onclick="return confirm('Eliminare questo fornitore?');">Elimina</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</body>
</html>
