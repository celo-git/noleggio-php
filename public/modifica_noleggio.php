<?php
// Pagina per modificare un noleggio esistente

require_once __DIR__ . '/../src/bootstrap.php';
session_start();


// Filtra per cliente loggato se presente
$where = '';
$params = [];
if (isset($_SESSION['cliente_id'])) {
    $where = 'WHERE n.cliente_id = ?';
    $params[] = $_SESSION['cliente_id'];
}
$sql = 'SELECT n.id, c.cognome AS cliente_cognome, c.nome AS cliente_nome, t.nome AS tipologia, n.data_inizio, n.data_fine FROM noleggio n LEFT JOIN cliente c ON n.cliente_id = c.id LEFT JOIN tipologie_noleggio t ON n.tipologia_noleggio_id = t.id ' . $where . ' ORDER BY n.data_inizio DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$elenco_noleggi = $stmt->fetchAll();


$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$azione = isset($_GET['azione']) && $_GET['azione'] === 'nuovo';
$noleggio = null;
if ($id) {
    // Recupera dati noleggio
    $stmt = $pdo->prepare('SELECT * FROM noleggio WHERE id = ?');
    $stmt->execute([$id]);
    $noleggio = $stmt->fetch();
    if (!$noleggio) {
        $id = null;
    }
}

// Gestione inserimento nuovo noleggio
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nuovo_noleggio'])) {
    $cliente_id = isset($_SESSION['cliente_id']) ? (int)$_SESSION['cliente_id'] : (int)$_POST['cliente_id'];
    $tipologia_id = (int)$_POST['tipologia_id'];
    $automezzo_id = $_POST['automezzo_id'] !== '' ? (int)$_POST['automezzo_id'] : null;
    $autista1_id = $_POST['autista1_id'] !== '' ? (int)$_POST['autista1_id'] : null;
    $autista2_id = $_POST['autista2_id'] !== '' ? (int)$_POST['autista2_id'] : null;
    $data_inizio = $_POST['data_inizio'];
    $data_fine = $_POST['data_fine'];
    $importo = $_POST['importo'] !== '' ? str_replace(',', '.', $_POST['importo']) : null;
    $destinazione = $_POST['destinazione'] !== '' ? trim($_POST['destinazione']) : null;
    $accompagnatore = $_POST['accompagnatore'] !== '' ? trim($_POST['accompagnatore']) : null;
    $preventivo = isset($_POST['preventivo']) ? 1 : 0;
    $pagato = isset($_POST['pagato']) ? 1 : 0;
    $ivato = isset($_POST['ivato']) ? 1 : 0;
    $stmt = $pdo->prepare('INSERT INTO noleggio (cliente_id, tipologia_noleggio_id, automezzo_id, autista1_id, autista2_id, data_inizio, data_fine, importo, destinazione, accompagnatore, preventivo, pagato, ivato) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$cliente_id, $tipologia_id, $automezzo_id, $autista1_id, $autista2_id, $data_inizio, $data_fine, $importo, $destinazione, $accompagnatore, $preventivo, $pagato, $ivato]);
    header('Location: modifica_noleggio.php');
    exit;
}

// Recupera dati per select
$clienti = $pdo->query('SELECT id, nome, cognome FROM cliente WHERE stato = 1 ORDER BY cognome, nome')->fetchAll();
$tipologie = $pdo->query('SELECT id, nome FROM tipologie_noleggio ORDER BY nome')->fetchAll();
$automezzi = $pdo->query('SELECT id, marca, modello, targa FROM automezzo WHERE stato = 1 ORDER BY marca, modello')->fetchAll();
$autisti = $pdo->query('SELECT id, nome, cognome FROM autista WHERE stato = 1 ORDER BY cognome, nome')->fetchAll();

$messaggio = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id) {
    $cliente_id = (int)$_POST['cliente_id'];
    $tipologia_id = (int)$_POST['tipologia_id'];
    $automezzo_id = $_POST['automezzo_id'] !== '' ? (int)$_POST['automezzo_id'] : null;
    $autista1_id = $_POST['autista1_id'] !== '' ? (int)$_POST['autista1_id'] : null;
    $autista2_id = $_POST['autista2_id'] !== '' ? (int)$_POST['autista2_id'] : null;
    $data_inizio = $_POST['data_inizio'];
    $data_fine = $_POST['data_fine'];
    $importo = $_POST['importo'] !== '' ? str_replace(',', '.', $_POST['importo']) : null;
    $destinazione = $_POST['destinazione'] !== '' ? trim($_POST['destinazione']) : null;
    $accompagnatore = $_POST['accompagnatore'] !== '' ? trim($_POST['accompagnatore']) : null;
    $preventivo = isset($_POST['preventivo']) ? 1 : 0;
    $pagato = isset($_POST['pagato']) ? 1 : 0;
    $ivato = isset($_POST['ivato']) ? 1 : 0;
    $stmt = $pdo->prepare('UPDATE noleggio SET cliente_id=?, tipologia_noleggio_id=?, automezzo_id=?, autista1_id=?, autista2_id=?, data_inizio=?, data_fine=?, importo=?, destinazione=?, accompagnatore=?, preventivo=?, pagato=?, ivato=? WHERE id=?');
    $stmt->execute([$cliente_id, $tipologia_id, $automezzo_id, $autista1_id, $autista2_id, $data_inizio, $data_fine, $importo, $destinazione, $accompagnatore, $preventivo, $pagato, $ivato, $id]);
    $messaggio = 'Noleggio modificato con successo!';
    // Aggiorna i dati visualizzati
    $stmt = $pdo->prepare('SELECT * FROM noleggio WHERE id = ?');
    $stmt->execute([$id]);
    $noleggio = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Modifica Noleggio</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; }
        .container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); padding: 2rem; }
        h1 { color: #2c3e50; }
        label { display: block; margin-top: 1em; }
        select, input { padding: 0.5em; width: 100%; margin-top: 0.5em; }
        .msg { color: green; margin-bottom: 1em; }
        button { background:#2980b9;color:#fff;padding:0.7em 1.5em;border-radius:5px;text-decoration:none;font-weight:bold;border:none;margin-top:1em; }
    </style>
</head>
<body>
<div class="container">
    <h1>Modifica Noleggio</h1>
    <div style="margin-bottom:2em;">
    <a href="modifica_noleggio.php?azione=nuovo" style="background:#27ae60;color:#fff;padding:0.4em 1em;border-radius:5px;text-decoration:none;font-size:0.95em;">Inserisci nuovo noleggio</a>
        <table style="width:100%;border-collapse:collapse;background:#fff;">
            <thead>
                <tr style="background:#eee;">
                    <!-- <th>ID</th> -->
                    <!-- <th>Cliente</th> -->
                        <th>Tipologia</th>
                        <th>Periodo</th>
                        <th>Destinazione</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($elenco_noleggi as $n): ?>
                <tr>
                        <!-- <td><?= $n['id'] ?></td> -->
                        <!-- <td><?= htmlspecialchars($n['cliente_cognome'] . ' ' . $n['cliente_nome']) ?></td> -->
                        <td><?= htmlspecialchars($n['tipologia']) ?></td>
                        <td><?= htmlspecialchars($n['data_inizio']) ?> - <?= htmlspecialchars($n['data_fine']) ?></td>
                        <td><?= htmlspecialchars($n['destinazione'] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if ($azione): ?>
    <?php if ($messaggio): ?><div class="msg"><?= $messaggio ?></div><?php endif; ?>
    <form method="post">
        <input type="hidden" name="nuovo_noleggio" value="1">
        <?php if (isset($_SESSION['cliente_id'])): ?>
            <input type="hidden" name="cliente_id" value="<?= (int)$_SESSION['cliente_id'] ?>">
            <div style="margin-bottom:1em;font-weight:bold;">Cliente: <?= htmlspecialchars($pdo->query('SELECT CONCAT(cognome, " ", nome) FROM cliente WHERE id = ' . (int)$_SESSION['cliente_id'])->fetchColumn()) ?></div>
        <?php else: ?>
        <label for="cliente_id">Cliente</label>
        <select name="cliente_id" id="cliente_id" required>
            <option value="">-- Seleziona cliente --</option>
            <?php foreach ($clienti as $c): ?>
                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['cognome'] . ' ' . $c['nome']) ?></option>
            <?php endforeach; ?>
        </select>
        <?php endif; ?>
        <label for="tipologia_id">Tipologia</label>
        <select name="tipologia_id" id="tipologia_id" required>
            <option value="">-- Seleziona tipologia --</option>
            <?php foreach ($tipologie as $t): ?>
                <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nome']) ?></option>
            <?php endforeach; ?>
        </select>
        <label for="data_inizio">Data Inizio</label>
        <input type="date" name="data_inizio" id="data_inizio" required>
        <label for="data_fine">Data Fine</label>
    <input type="date" name="data_fine" id="data_fine" required>
        <label for="destinazione">Destinazione</label>
        <input type="text" name="destinazione" id="destinazione" maxlength="255">
        <label for="accompagnatore">Accompagnatore</label>
        <input type="text" name="accompagnatore" id="accompagnatore" maxlength="100">
        <label style="display:flex;align-items:center;gap:0.5em;margin-top:1em;">
            <input type="checkbox" name="preventivo" value="1"> Preventivo
        </label>
        <label style="display:flex;align-items:center;gap:0.5em;">
        <!-- Flag pagato e ivato rimossi -->
        <button type="submit">Inserisci Noleggio</button>
    </form>
    <?php elseif ($id && $noleggio): ?>
    <?php if ($messaggio): ?><div class="msg"><?= $messaggio ?></div><?php endif; ?>
    <form method="post">
        <label for="cliente_id">Cliente</label>
        <select name="cliente_id" id="cliente_id" required>
            <option value="">-- Seleziona cliente --</option>
            <?php foreach ($clienti as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $noleggio['cliente_id']==$c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['cognome'] . ' ' . $c['nome']) ?></option>
            <?php endforeach; ?>
        </select>
        <label for="tipologia_id">Tipologia</label>
        <select name="tipologia_id" id="tipologia_id" required>
            <option value="">-- Seleziona tipologia --</option>
            <?php foreach ($tipologie as $t): ?>
                <option value="<?= $t['id'] ?>" <?= $noleggio['tipologia_noleggio_id']==$t['id'] ? 'selected' : '' ?>><?= htmlspecialchars($t['nome']) ?></option>
            <?php endforeach; ?>
        </select>
        <label for="data_inizio">Data Inizio</label>
        <input type="date" name="data_inizio" id="data_inizio" required value="<?= htmlspecialchars($noleggio['data_inizio']) ?>">
        <label for="data_fine">Data Fine</label>
    <input type="date" name="data_fine" id="data_fine" required value="<?= htmlspecialchars($noleggio['data_fine']) ?>">
        <label for="destinazione">Destinazione</label>
        <input type="text" name="destinazione" id="destinazione" maxlength="255" value="<?= htmlspecialchars($noleggio['destinazione']) ?>">
        <label for="accompagnatore">Accompagnatore</label>
        <input type="text" name="accompagnatore" id="accompagnatore" maxlength="100" value="<?= htmlspecialchars($noleggio['accompagnatore']) ?>">
        <label style="display:flex;align-items:center;gap:0.5em;margin-top:1em;">
            <input type="checkbox" name="preventivo" value="1" <?= $noleggio['preventivo'] ? 'checked' : '' ?>> Preventivo
        </label>
        <label style="display:flex;align-items:center;gap:0.5em;">
        <!-- Flag pagato e ivato rimossi -->
        <button type="submit">Salva Modifiche</button>
    </form>
    <?php endif; ?>
</div>
</body>
</html>
