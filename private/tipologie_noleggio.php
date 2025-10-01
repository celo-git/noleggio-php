<?php
session_start();
if (!isset($_SESSION['utente_id'])) {
    header('Location: ../public/accesso.php');
    exit;
}
?>
<?php
// Pagina per la gestione delle tipologie di noleggio
require_once __DIR__ . '/../src/bootstrap.php';

// Eliminazione tipologia
if (isset($_GET['elimina'])) {
    $id = (int)$_GET['elimina'];
    $stmt = $pdo->prepare('DELETE FROM tipologie_noleggio WHERE id = ?');
    $stmt->execute([$id]);
    header('Location: tipologie_noleggio.php');
    exit;
}

// Modifica tipologia
$edit_id = null;
$edit_nome = '';
if (isset($_GET['modifica'])) {
    $edit_id = (int)$_GET['modifica'];
    $stmt = $pdo->prepare('SELECT nome FROM tipologie_noleggio WHERE id = ?');
    $stmt->execute([$edit_id]);
    $row = $stmt->fetch();
    if ($row) {
        $edit_nome = $row['nome'];
    } else {
        $edit_id = null;
    }
}
if (isset($_POST['modifica_tipologia']) && isset($_POST['edit_id'])) {
    $id = (int)$_POST['edit_id'];
    $nuovo_nome = trim($_POST['modifica_tipologia']);
    if ($nuovo_nome !== '') {
        $stmt = $pdo->prepare('UPDATE tipologie_noleggio SET nome = ? WHERE id = ?');
        $stmt->execute([$nuovo_nome, $id]);
    }
    header('Location: tipologie_noleggio.php');
    exit;
}

// Inserimento nuova tipologia
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['nuova_tipologia'])) {
    $nuova = trim($_POST['nuova_tipologia']);
    if ($nuova !== '') {
        $stmt = $pdo->prepare('INSERT IGNORE INTO tipologie_noleggio (nome) VALUES (?)');
        $stmt->execute([$nuova]);
    }
    header('Location: tipologie_noleggio.php');
    exit;
}

// Recupero tipologie dal database
$tipologie = $pdo->query('SELECT * FROM tipologie_noleggio ORDER BY nome')->fetchAll();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Tipologie di Noleggio</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4 url('https://cdn.pixabay.com/photo/2013/07/13/12/46/vintage-car-146185_1280.png') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 0;
        }
        .container {
            background: rgba(255,255,255,0.92);
            /* ...existing code... */
        }
        .container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); padding: 2rem; }
        h1 { color: #2c3e50; }
        ul { padding-left: 1.2em; }
        li { margin-bottom: 0.5em; }
        .add-form { margin-top: 2em; }
        label { display: block; margin-bottom: 0.5em; }
        input[type="text"] { padding: 0.5em; width: 80%; }
        button { padding: 0.5em 1.2em; background: #2980b9; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #3498db; }
        a { text-decoration: none; }
        table { border-collapse: collapse; width: 100%; background: #fff; }
        th, td { border: 1px solid #ccc; padding: 0.5em 0.7em; }
        th { background: #eee; }
        tr:nth-child(even) { background: #f8f8f8; }
        .form-box { background: #f4f4f4; padding: 1em; border-radius: 8px; margin-bottom: 2em; }
    </style>
</head>
<body style="margin:0;font-family:sans-serif;background:#f4f4f4;">
    <div class="container">
        <div style="margin-bottom:2em;text-align:left;">
            <a href="index.php" style="background:#2980b9;color:#fff;padding:0.7em 1.5em;border-radius:5px;text-decoration:none;font-weight:bold;">Torna alla home</a>
        </div>
        <h1 style="text-align:left;">Tipologie di Noleggio</h1>
        <div class="form-box" style="text-align:left;max-width:600px;margin:0 auto;">
            <form method="post">
                <?php if ($edit_id): ?>
                    <input type="hidden" name="edit_id" value="<?= $edit_id ?>">
                    <div style="display:flex;gap:1em;flex-wrap:wrap;align-items:flex-end;justify-content:flex-start;">
                        <label style="display:flex;flex-direction:column;align-items:flex-start;min-width:200px;">Nome:
                            <input type="text" name="modifica_tipologia" value="<?= htmlspecialchars($edit_nome) ?>" required>
                        </label>
                        <button type="submit" style="align-self:flex-end;background:#2980b9;color:#fff;padding:0.7em 1.5em;border-radius:5px;text-decoration:none;font-weight:bold;border:none;">Salva</button>
                        <a href="tipologie_noleggio.php" style="align-self:flex-end;margin-left:1em;">Annulla</a>
                    </div>
                <?php else: ?>
                    <label for="nuova_tipologia">Aggiungi nuova tipologia:</label>
                    <div style="display:flex;gap:1em;flex-wrap:wrap;align-items:flex-end;justify-content:flex-start;">
                        <input type="text" id="nuova_tipologia" name="nuova_tipologia" required style="min-width:200px;">
                        <button type="submit" style="align-self:flex-end;background:#2980b9;color:#fff;padding:0.7em 1.5em;border-radius:5px;text-decoration:none;font-weight:bold;border:none;">Aggiungi</button>
                    </div>
                <?php endif; ?>
            </form>
        </div>
        <table style="text-align:left;">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($tipologie as $tipo): ?>
                <tr>
                    <td><?= htmlspecialchars($tipo['nome']) ?></td>
                    <td>
                        <a href="?modifica=<?= $tipo['id'] ?>">Modifica</a> |
                        <a href="?elimina=<?= $tipo['id'] ?>" onclick="return confirm('Eliminare questa tipologia?');">Elimina</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
