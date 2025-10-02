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
// Pagina per la gestione degli autisti
require_once __DIR__ . '/../src/bootstrap.php';

// Eliminazione autista
if (isset($_GET['elimina'])) {
    $id = (int)$_GET['elimina'];
    $stmt = $pdo->prepare('DELETE FROM autista WHERE id = ?');
    $stmt->execute([$id]);
    header('Location: autisti.php');
    exit;
}

// Modifica autista
$edit_id = null;
$edit_data = [
    'nome' => '', 'cognome' => '', 'telefono' => '', 'email' => '', 'patente' => '', 'tipo_patente' => '', 'stato' => 1
];
if (isset($_GET['modifica'])) {
    $edit_id = (int)$_GET['modifica'];
    $stmt = $pdo->prepare('SELECT nome, cognome, telefono, email, patente, tipo_patente, stato FROM autista WHERE id = ?');
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
    $fields = ['nome','cognome','telefono','email','patente','tipo_patente','stato'];
    $values = [];
    foreach ($fields as $f) {
        $values[$f] = isset($_POST[$f]) ? trim($_POST[$f]) : '';
    }
    if ($values['nome'] && $values['cognome']) {
        $stmt = $pdo->prepare('UPDATE autista SET nome=?, cognome=?, telefono=?, email=?, patente=?, tipo_patente=?, stato=? WHERE id=?');
        $stmt->execute([
            $values['nome'], $values['cognome'], $values['telefono'], $values['email'], $values['patente'], $values['tipo_patente'], $values['stato'] ? 1 : 0, $id
        ]);
    }
    header('Location: autisti.php');
    exit;
}

// Inserimento nuovo autista o modifica
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_autista'])) {
    $fields = ['nome','cognome','telefono','email','patente','tipo_patente','stato'];
    $values = [];
    foreach ($fields as $f) {
        $values[$f] = isset($_POST[$f]) ? trim($_POST[$f]) : '';
    }
    if ($values['nome'] && $values['cognome']) {
        $stmt = $pdo->prepare('INSERT INTO autista (nome, cognome, telefono, email, patente, tipo_patente, stato) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $values['nome'], $values['cognome'], $values['telefono'], $values['email'], $values['patente'], $values['tipo_patente'], $values['stato'] ? 1 : 0
        ]);
    }
    header('Location: autisti.php');
    exit;
}

// Recupera tutti gli autisti
$autisti = $pdo->query('SELECT * FROM autista ORDER BY cognome, nome')->fetchAll();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Autisti</title>
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
    <a href="gestione.php" style="background:#2980b9;color:#fff;padding:0.7em 1.5em;border-radius:5px;text-decoration:none;font-weight:bold;">Torna alla home</a>
</div>
<h1 style="text-align:left;">Autisti</h1>
<div class="form-box" style="text-align:left;">
    <form method="post">
        <?php if ($edit_id): ?>
            <input type="hidden" name="edit_id" value="<?= $edit_id ?>">
        <?php else: ?>
            <input type="hidden" name="add_autista" value="1">
        <?php endif; ?>
        <div style="display:flex;gap:1em;flex-wrap:wrap;align-items:flex-start;justify-content:flex-start;">
            <label style="display:flex;flex-direction:column;align-items:flex-start;">Nome: <input type="text" name="nome" required value="<?= isset($edit_data['nome']) && $edit_id ? htmlspecialchars($edit_data['nome']) : '' ?>"></label>
            <label style="display:flex;flex-direction:column;align-items:flex-start;">Cognome: <input type="text" name="cognome" required value="<?= isset($edit_data['cognome']) && $edit_id ? htmlspecialchars($edit_data['cognome']) : '' ?>"></label>
            <label style="display:flex;flex-direction:column;align-items:flex-start;">Telefono: <input type="text" name="telefono" value="<?= isset($edit_data['telefono']) && $edit_id ? htmlspecialchars($edit_data['telefono']) : '' ?>"></label>
            <label style="display:flex;flex-direction:column;align-items:flex-start;">Email: <input type="email" name="email" value="<?= isset($edit_data['email']) && $edit_id ? htmlspecialchars($edit_data['email']) : '' ?>"></label>
            <label style="display:flex;flex-direction:column;align-items:flex-start;">Patente: <input type="text" name="patente" value="<?= isset($edit_data['patente']) && $edit_id ? htmlspecialchars($edit_data['patente']) : '' ?>"></label>
            <label style="display:flex;flex-direction:column;align-items:flex-start;">Tipo Patente: <input type="text" name="tipo_patente" value="<?= isset($edit_data['tipo_patente']) && $edit_id ? htmlspecialchars($edit_data['tipo_patente']) : '' ?>"></label>
            <label style="display:flex;flex-direction:column;align-items:flex-start;">Stato: <select name="stato">
                <option value="1" <?= (isset($edit_data['stato']) && $edit_id && $edit_data['stato']) ? 'selected' : '' ?>>Attivo</option>
                <option value="0" <?= (isset($edit_data['stato']) && $edit_id && !$edit_data['stato']) ? 'selected' : '' ?>>Non attivo</option>
            </select></label>
            <button type="submit" style="align-self:flex-end;background:#2980b9;color:#fff;padding:0.7em 1.5em;border-radius:5px;text-decoration:none;font-weight:bold;border:none;">
                <?= $edit_id ? 'Salva modifiche' : 'Aggiungi autista' ?>
            </button>
            <?php if ($edit_id): ?>
                <a href="autisti.php" style="align-self:flex-end;margin-left:1em;">Annulla</a>
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
            <th>Patente</th>
            <th>Tipo Patente</th>
            <th>Stato</th>
            <th>Azioni</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($autisti as $a): ?>
        <tr>
            <td><?= htmlspecialchars($a['nome']) ?></td>
            <td><?= htmlspecialchars($a['cognome']) ?></td>
            <td><?= htmlspecialchars($a['telefono']) ?></td>
            <td><?= htmlspecialchars($a['email']) ?></td>
            <td><?= htmlspecialchars($a['patente']) ?></td>
            <td><?= htmlspecialchars($a['tipo_patente']) ?></td>
            <td><?= $a['stato'] ? 'Attivo' : 'Non attivo' ?></td>
            <td>
                <a href="?modifica=<?= $a['id'] ?>">Modifica</a> |
                <a href="?elimina=<?= $a['id'] ?>" onclick="return confirm('Eliminare questo autista?');">Elimina</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</body>
</html>
