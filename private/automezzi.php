<?php
session_start();
if (!isset($_SESSION['utente_id'])) {
    header('Location: ../public/accesso.php');
    exit;
}
?>
<?php
// Pagina per la gestione degli automezzi
require_once __DIR__ . '/../src/bootstrap.php';

// Eliminazione automezzo
if (isset($_GET['elimina'])) {
    $id = (int)$_GET['elimina'];
    $stmt = $pdo->prepare('DELETE FROM automezzo WHERE id = ?');
    $stmt->execute([$id]);
    header('Location: automezzi.php');
    exit;
}

// Modifica automezzo
$edit_id = null;
$edit_data = [
    'modello' => '', 'marca' => '', 'targa' => '', 'anno' => '', 'colore' => '', 'stato' => 1
];
if (isset($_GET['modifica'])) {
    $edit_id = (int)$_GET['modifica'];
    $stmt = $pdo->prepare('SELECT modello, marca, targa, anno, colore, stato FROM automezzo WHERE id = ?');
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
    $fields = ['modello','marca','targa','anno','colore','stato'];
    $values = [];
    foreach ($fields as $f) {
        $values[$f] = isset($_POST[$f]) ? trim($_POST[$f]) : '';
    }
    if ($values['modello'] && $values['marca'] && $values['targa'] && $values['anno'] && $values['colore']) {
        $stmt = $pdo->prepare('UPDATE automezzo SET modello=?, marca=?, targa=?, anno=?, colore=?, stato=? WHERE id=?');
        $stmt->execute([
            $values['modello'], $values['marca'], $values['targa'], $values['anno'], $values['colore'], $values['stato'] ? 1 : 0, $id
        ]);
    }
    header('Location: automezzi.php');
    exit;
}

// Inserimento nuovo automezzo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_automezzo'])) {
    $fields = ['modello','marca','targa','anno','colore','stato'];
    $values = [];
    foreach ($fields as $f) {
        $values[$f] = isset($_POST[$f]) ? trim($_POST[$f]) : '';
    }
    if ($values['modello'] && $values['marca'] && $values['targa'] && $values['anno'] && $values['colore']) {
        $stmt = $pdo->prepare('INSERT INTO automezzo (modello, marca, targa, anno, colore, stato) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $values['modello'], $values['marca'], $values['targa'], $values['anno'], $values['colore'], $values['stato'] ? 1 : 0
        ]);
    }
    header('Location: automezzi.php');
    exit;
}

// Recupera tutti gli automezzi
$automezzi = $pdo->query('SELECT * FROM automezzo ORDER BY modello')->fetchAll();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Automezzi</title>
    <style>
        table { border-collapse: collapse; width: 100%; background: #fff; }
        th, td { border: 1px solid #ccc; padding: 0.5em 0.7em; }
        th { background: #eee; }
        tr:nth-child(even) { background: #f8f8f8; }
        .form-box { background: #f4f4f4; padding: 1em; border-radius: 8px; margin-bottom: 2em; }
    </style>
</head>
<body>
<div style="margin-bottom:2em;text-align:left;">
    <a href="index.php" style="background:#2980b9;color:#fff;padding:0.7em 1.5em;border-radius:5px;text-decoration:none;font-weight:bold;">Torna alla home</a>
</div>
<h1 style="text-align:left;">Automezzi</h1>
<div class="form-box" style="text-align:left;">
    <form method="post">
        <?php if ($edit_id): ?>
            <input type="hidden" name="edit_id" value="<?= $edit_id ?>">
        <?php else: ?>
            <input type="hidden" name="add_automezzo" value="1">
        <?php endif; ?>
        <div style="display:flex;gap:1em;flex-wrap:wrap;align-items:flex-start;justify-content:flex-start;">
            <label style="display:flex;flex-direction:column;align-items:flex-start;">Modello: <input type="text" name="modello" required value="<?= isset($edit_data['modello']) && $edit_id ? htmlspecialchars($edit_data['modello']) : '' ?>"></label>
            <label style="display:flex;flex-direction:column;align-items:flex-start;">Marca: <input type="text" name="marca" required value="<?= isset($edit_data['marca']) && $edit_id ? htmlspecialchars($edit_data['marca']) : '' ?>"></label>
            <label style="display:flex;flex-direction:column;align-items:flex-start;">Targa: <input type="text" name="targa" required value="<?= isset($edit_data['targa']) && $edit_id ? htmlspecialchars($edit_data['targa']) : '' ?>"></label>
            <label style="display:flex;flex-direction:column;align-items:flex-start;">Anno: <input type="number" name="anno" min="1900" max="2100" required style="width:80px;" value="<?= isset($edit_data['anno']) && $edit_id ? htmlspecialchars($edit_data['anno']) : '' ?>"></label>
            <label style="display:flex;flex-direction:column;align-items:flex-start;">Colore: <input type="text" name="colore" required value="<?= isset($edit_data['colore']) && $edit_id ? htmlspecialchars($edit_data['colore']) : '' ?>"></label>
            <label style="display:flex;flex-direction:column;align-items:flex-start;">Stato: <select name="stato">
                <option value="1" <?= (isset($edit_data['stato']) && $edit_id && $edit_data['stato']) ? 'selected' : '' ?>>Attivo</option>
                <option value="0" <?= (isset($edit_data['stato']) && $edit_id && !$edit_data['stato']) ? 'selected' : '' ?>>Non attivo</option>
            </select></label>
            <button type="submit" style="align-self:flex-end;background:#2980b9;color:#fff;padding:0.7em 1.5em;border-radius:5px;text-decoration:none;font-weight:bold;border:none;">
                <?= $edit_id ? 'Salva modifiche' : 'Aggiungi automezzo' ?>
            </button>
            <?php if ($edit_id): ?>
                <a href="automezzi.php" style="align-self:flex-end;margin-left:1em;">Annulla</a>
            <?php endif; ?>
        </div>
    </form>
</div>
<table style="text-align:left;">
    <thead>
        <tr>
            <th>Modello</th>
            <th>Marca</th>
            <th>Targa</th>
            <th>Anno</th>
            <th>Colore</th>
            <th>Stato</th>
            <th>Azioni</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($automezzi as $mezzo): ?>
        <tr>
            <td><?= htmlspecialchars($mezzo['modello']) ?></td>
            <td><?= htmlspecialchars($mezzo['marca']) ?></td>
            <td><?= htmlspecialchars($mezzo['targa']) ?></td>
            <td><?= htmlspecialchars($mezzo['anno']) ?></td>
            <td><?= htmlspecialchars($mezzo['colore']) ?></td>
            <td><?= $mezzo['stato'] ? 'Attivo' : 'Non attivo' ?></td>
            <td>
                <a href="?modifica=<?= $mezzo['id'] ?>">Modifica</a> |
                <a href="?elimina=<?= $mezzo['id'] ?>" onclick="return confirm('Eliminare questo automezzo?');">Elimina</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</body>
</html>
