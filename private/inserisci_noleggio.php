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
// Pagina per inserire un nuovo noleggio

require_once __DIR__ . '/../src/bootstrap.php';

// Gestione eliminazione noleggio
if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    $stmt = $pdo->prepare('DELETE FROM noleggio WHERE id = ?');
    $stmt->execute([$delete_id]);
    header('Location: inserisci_noleggio.php?deleted=1');
    exit;
}

// Recupera clienti solo se non filtrato
$clienti = [];
if (!isset($_SESSION['cliente_id'])) {
    $clienti = $pdo->query('SELECT id, nome, cognome FROM cliente WHERE stato = 1 ORDER BY cognome, nome')->fetchAll();
}

// Recupera tipologie noleggio
$tipologie = $pdo->query('SELECT id, nome FROM tipologie_noleggio ORDER BY nome')->fetchAll();

// Recupera automezzi disponibili
$automezzi = $pdo->query('SELECT id, marca, modello, targa FROM automezzo WHERE stato = 1 ORDER BY marca, modello')->fetchAll();

// Recupera autisti disponibili
$autisti = $pdo->query('SELECT id, nome, cognome FROM autista WHERE stato = 1 ORDER BY cognome, nome')->fetchAll();

$messaggio = '';
// Se presente sessione cliente, forza il cliente_id
$edit_id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : null);
$cliente_fisso = isset($_SESSION['cliente_id']) ? (int)$_SESSION['cliente_id'] : null;
// Aggiungi autista1_id e autista2_id
$edit_data = [
    'cliente_id' => '', 'tipologia_id' => '', 'automezzo_id' => '', 'autista1_id' => '', 'autista2_id' => '', 'data_inizio' => '', 'data_fine' => '', 'importo' => '', 'destinazione' => '', 'accompagnatore' => '', 'preventivo' => 0, 'pagato' => 0, 'ivato' => 0
];
if ($edit_id) {
    $stmt = $pdo->prepare('SELECT * FROM noleggio WHERE id = ? order by data_inizio DESC');
    $stmt->execute([$edit_id]);
    $row = $stmt->fetch();
    if ($row) {
        $edit_data = [
            'cliente_id' => $row['cliente_id'],
            'tipologia_id' => $row['tipologia_noleggio_id'],
            'automezzo_id' => $row['automezzo_id'],
            'autista1_id' => $row['autista1_id'] ?? '',
            'autista2_id' => $row['autista2_id'] ?? '',
            'data_inizio' => $row['data_inizio'],
            'data_fine' => $row['data_fine'],
            'importo' => $row['importo'],
            'destinazione' => $row['destinazione'],
            'accompagnatore' => $row['accompagnatore'],
            'preventivo' => $row['preventivo'],
            'pagato' => $row['pagato'],
            'ivato' => $row['ivato']
        ];
    }
}
if ((isset($_POST['cliente_id']) || $cliente_fisso) && isset($_POST['tipologia_id'], $_POST['data_inizio'], $_POST['data_fine'], $_POST['importo'])) {
    $cliente_id = $cliente_fisso ?: (int)$_POST['cliente_id'];
    $tipologia_id = (int)$_POST['tipologia_id'];
    $automezzo_id = isset($_POST['automezzo_id']) && $_POST['automezzo_id'] !== '' ? (int)$_POST['automezzo_id'] : null;
    $autista1_id = isset($_POST['autista1_id']) && $_POST['autista1_id'] !== '' ? (int)$_POST['autista1_id'] : null;
    $autista2_id = isset($_POST['autista2_id']) && $_POST['autista2_id'] !== '' ? (int)$_POST['autista2_id'] : null;
    $data_inizio = $_POST['data_inizio'];
    $data_fine = $_POST['data_fine'];
    $importo = $_POST['importo'] !== '' ? str_replace(',', '.', $_POST['importo']) : null;
    $destinazione = isset($_POST['destinazione']) && $_POST['destinazione'] !== '' ? trim($_POST['destinazione']) : null;
    $accompagnatore = isset($_POST['accompagnatore']) && $_POST['accompagnatore'] !== '' ? trim($_POST['accompagnatore']) : null;
    $preventivo = isset($_POST['preventivo']) ? 1 : 0;
    $pagato = isset($_POST['pagato']) ? 1 : 0;
    $ivato = isset($_POST['ivato']) ? 1 : 0;
    if ($data_inizio && $data_fine && $data_inizio <= $data_fine) {
        $automezzo_occupato = false;
        if ($automezzo_id) {
            $sql = 'SELECT COUNT(*) FROM noleggio WHERE automezzo_id = ? AND data_fine >= ? AND data_inizio <= ?';
            $params = [$automezzo_id, $data_inizio, $data_fine];
            if (isset($_POST['edit_id']) && $_POST['edit_id']) {
                $sql .= ' AND id != ?';
                $params[] = (int)$_POST['edit_id'];
            }
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $automezzo_occupato = $stmt->fetchColumn() > 0;
        }
        if ($automezzo_occupato) {
            $messaggio = '<span style="color:red">Attenzione: l\'automezzo selezionato è già assegnato a un altro noleggio nello stesso periodo.</span>';
        } else if (isset($_POST['edit_id']) && $_POST['edit_id']) {
            // Modifica esistente
            $stmt = $pdo->prepare('UPDATE noleggio SET cliente_id=?, tipologia_noleggio_id=?, automezzo_id=?, autista1_id=?, autista2_id=?, data_inizio=?, data_fine=?, importo=?, destinazione=?, accompagnatore=?, preventivo=?, pagato=?, ivato=? WHERE id=?');
            $stmt->execute([$cliente_id, $tipologia_id, $automezzo_id, $autista1_id, $autista2_id, $data_inizio, $data_fine, $importo, $destinazione, $accompagnatore, $preventivo, $pagato, $ivato, (int)$_POST['edit_id']]);
            $messaggio = 'Noleggio modificato con successo!';
        } else {
            // Inserimento nuovo
            $stmt = $pdo->prepare('INSERT INTO noleggio (cliente_id, tipologia_noleggio_id, automezzo_id, autista1_id, autista2_id, data_inizio, data_fine, importo, destinazione, accompagnatore, preventivo, pagato, ivato) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$cliente_id, $tipologia_id, $automezzo_id, $autista1_id, $autista2_id, $data_inizio, $data_fine, $importo, $destinazione, $accompagnatore, $preventivo, $pagato, $ivato]);
            $messaggio = 'Noleggio inserito con successo!';
        }
    } else {
        $messaggio = 'Le date non sono valide.';
    }
    // Aggiorna i dati del form dopo submit
    $edit_data = [
        'cliente_id' => $cliente_id,
        'tipologia_id' => $tipologia_id,
        'automezzo_id' => $automezzo_id,
        'autista1_id' => $autista1_id,
        'autista2_id' => $autista2_id,
        'data_inizio' => $data_inizio,
        'data_fine' => $data_fine,
        'importo' => $importo,
        'destinazione' => $destinazione,
        'accompagnatore' => $accompagnatore,
        'preventivo' => $preventivo,
        'pagato' => $pagato,
        'ivato' => $ivato
    ];
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Inserisci Noleggio</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; }
        .container { max-width: 500px; margin: 40px auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); padding: 2rem; }
        h1 { color: #2c3e50; }
        label { display: block; margin-top: 1em; }
        select, button { padding: 0.5em; width: 100%; margin-top: 0.5em; }
        .msg { color: green; margin-bottom: 1em; }
        a { display:inline-block;margin-bottom:1.5em;background:#34495e;color:#fff;padding:0.5em 1.2em;border-radius:5px;text-decoration:none; }
    </style>
</head>
<body>
<div style="margin-bottom:2em;text-align:left;">
    <a href="gestione.php" style="background:#2980b9;color:#fff;padding:0.7em 1.5em;border-radius:5px;text-decoration:none;font-weight:bold;">Torna alla home</a>
</div>
<h1 style="text-align:left;">Inserisci Noleggio</h1>
<div class="form-box" style="text-align:left;width:100%;max-width:none;margin:0 auto;">
    <?php if ($messaggio): ?><div class="msg" style="text-align:left;"><?= $messaggio ?></div><?php endif; ?>
    <form method="post">
        <?php if ($edit_id): ?>
            <input type="hidden" name="edit_id" value="<?= htmlspecialchars($edit_id) ?>">
        <?php else: ?>
            <input type="hidden" name="add_noleggio" value="1">
        <?php endif; ?>
        <div style="display:flex;flex-wrap:wrap;gap:1em;align-items:flex-end;justify-content:flex-start;text-align:left;width:100%;">
            <?php if ($cliente_fisso): ?>
                <input type="hidden" name="cliente_id" value="<?= $cliente_fisso ?>">
                <div style="min-width:180px;margin-bottom:1em;font-weight:bold;">Cliente: <?= htmlspecialchars($pdo->query('SELECT CONCAT(cognome, " ", nome) FROM cliente WHERE id = ' . $cliente_fisso)->fetchColumn()) ?></div>
            <?php else: ?>
            <label style="display:flex;flex-direction:column;align-items:flex-start;min-width:180px;">Cliente:
                <select name="cliente_id" required style="min-width:180px;">
                    <option value="">-- Seleziona cliente --</option>
                    <?php foreach ($clienti as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $edit_data['cliente_id']==$c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['cognome'] . ' ' . $c['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <?php endif; ?>
            <label style="display:flex;flex-direction:column;align-items:flex-start;min-width:150px;">Tipologia:
                <select name="tipologia_id" required style="min-width:150px;">
                    <option value="">-- Seleziona tipologia --</option>
                    <?php foreach ($tipologie as $t): ?>
                        <option value="<?= $t['id'] ?>" <?= $edit_data['tipologia_id']==$t['id'] ? 'selected' : '' ?>><?= htmlspecialchars($t['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label style="display:flex;flex-direction:column;align-items:flex-start;min-width:180px;">Automezzo:
                <select name="automezzo_id" style="min-width:180px;">
                    <option value="">-- Nessun automezzo --</option>
                    <?php foreach ($automezzi as $a): ?>
                        <option value="<?= $a['id'] ?>" <?= $edit_data['automezzo_id']==$a['id'] ? 'selected' : '' ?>><?= htmlspecialchars($a['marca'] . ' ' . $a['modello'] . ' (' . $a['targa'] . ')') ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label style="display:flex;flex-direction:column;align-items:flex-start;min-width:150px;">Autista 1:
                <select name="autista1_id" style="min-width:150px;">
                    <option value="">-- Nessun autista --</option>
                    <?php foreach ($autisti as $au): ?>
                        <option value="<?= $au['id'] ?>" <?= $edit_data['autista1_id']==$au['id'] ? 'selected' : '' ?>><?= htmlspecialchars($au['cognome'] . ' ' . $au['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label style="display:flex;flex-direction:column;align-items:flex-start;min-width:150px;">Autista 2:
                <select name="autista2_id" style="min-width:150px;">
                    <option value="">-- Nessun autista --</option>
                    <?php foreach ($autisti as $au): ?>
                        <option value="<?= $au['id'] ?>" <?= $edit_data['autista2_id']==$au['id'] ? 'selected' : '' ?>><?= htmlspecialchars($au['cognome'] . ' ' . $au['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label style="display:flex;flex-direction:column;align-items:flex-start;min-width:130px;">Data Inizio:
                <input type="date" name="data_inizio" required value="<?= htmlspecialchars($edit_data['data_inizio']) ?>">
            </label>
            <label style="display:flex;flex-direction:column;align-items:flex-start;min-width:130px;">Data Fine:
                <input type="date" name="data_fine" required value="<?= htmlspecialchars($edit_data['data_fine']) ?>">
            </label>
            <label style="display:flex;flex-direction:column;align-items:flex-start;min-width:120px;">Importo (€):
                <input type="number" name="importo" step="0.01" min="0" placeholder="Importo" value="<?= htmlspecialchars($edit_data['importo']) ?>">
            </label>
            <label style="display:flex;flex-direction:column;align-items:flex-start;min-width:160px;">Destinazione:
                <input type="text" name="destinazione" maxlength="255" placeholder="Destinazione" value="<?= htmlspecialchars($edit_data['destinazione']) ?>">
            </label>
            <label style="display:flex;flex-direction:column;align-items:flex-start;min-width:160px;">Accompagnatore:
                <input type="text" name="accompagnatore" maxlength="100" placeholder="Accompagnatore" value="<?= htmlspecialchars($edit_data['accompagnatore']) ?>">
            </label>
            <label style="display:flex;flex-direction:row;align-items:center;gap:0.5em;margin-top:0.5em;min-width:120px;">
                <input type="checkbox" name="preventivo" value="1" <?= $edit_data['preventivo'] ? 'checked' : '' ?>> Preventivo
            </label>
            <label style="display:flex;flex-direction:row;align-items:center;gap:0.5em;margin-top:0.5em;min-width:120px;">
                <input type="checkbox" name="pagato" value="1" <?= $edit_data['pagato'] ? 'checked' : '' ?>> Pagato
            </label>
            <label style="display:flex;flex-direction:row;align-items:center;gap:0.5em;margin-top:0.5em;min-width:120px;">
                <input type="checkbox" name="ivato" value="1" <?= $edit_data['ivato'] ? 'checked' : '' ?>> Ivato
            </label>
            <button type="submit" style="align-self:flex-end;background:#2980b9;color:#fff;padding:0.7em 1.5em;border-radius:5px;text-decoration:none;font-weight:bold;border:none;margin-top:0.5em;">
                <?= $edit_id ? 'Salva Modifiche' : 'Inserisci Noleggio' ?>
            </button>

        </div>
    </form>

<?php
// Recupera tutti i noleggi per la tabella elenco
$noleggi = $pdo->query('SELECT n.id, c.cognome AS cliente_cognome, c.nome AS cliente_nome, t.nome AS tipologia, a.targa, n.data_inizio, n.data_fine, n.importo FROM noleggio n LEFT JOIN cliente c ON n.cliente_id = c.id LEFT JOIN tipologie_noleggio t ON n.tipologia_noleggio_id = t.id LEFT JOIN automezzo a ON n.automezzo_id = a.id ORDER BY n.data_inizio DESC')->fetchAll();
?>
<?php if (isset($_GET['deleted'])): ?>
    <div class="msg" style="color:green;margin-bottom:1em;">Noleggio eliminato con successo.</div>
<?php endif; ?>
<div style="margin-top:2.5em;">
    <h2 style="text-align:left;">Elenco Noleggi Inseriti</h2>
    <table style="width:100%;border-collapse:collapse;background:#fff;">
        <thead>
            <tr style="background:#eee;">
                <th>ID</th>
                <th>Cliente</th>
                <th>Tipologia</th>
                <th>Automezzo</th>
                <th>Periodo</th>
                <th>Importo</th>
                <th>Azioni</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($noleggi as $n): ?>
            <tr>
                <td><?= $n['id'] ?></td>
                <td><?= htmlspecialchars($n['cliente_cognome'] . ' ' . $n['cliente_nome']) ?></td>
                <td><?= htmlspecialchars($n['tipologia']) ?></td>
                <td><?= htmlspecialchars($n['targa']) ?></td>
                <td><?= htmlspecialchars($n['data_inizio']) ?> - <?= htmlspecialchars($n['data_fine']) ?></td>
                <td><?= number_format($n['importo'], 2, ',', '.') ?> €</td>
                <td>
                    <a href="?id=<?= $n['id'] ?>" class="btn btn-sm btn-warning" style="margin-right:4px;">Modifica</a>
                    <a href="?delete=<?= $n['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Sei sicuro di voler eliminare questo noleggio?');">Elimina</a>
                    <a href="stampa_noleggio.php?id=<?= $n['id'] ?>" target="_blank" style="background:#2980b9;color:#fff;padding:0.4em 1em;border-radius:5px;text-decoration:none;font-size:0.95em;margin-left:4px;">Stampa PDF</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
