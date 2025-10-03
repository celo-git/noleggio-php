<?php
// Pagina per modificare un noleggio esistente
require_once __DIR__ . '/../src/bootstrap.php';
session_start();

if (!isset($_SESSION['cliente_id'])) {
    header('Location: index.php');
    exit;
}

// Gestione logout
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit;
}
// Recupera tutte le tipologie dalla tabella tipologie_noleggio
$tipologie = $pdo->query('SELECT id, nome FROM tipologie_noleggio ORDER BY nome')->fetchAll();

// Filtra per cliente loggato
$where = '';
$params = [];
if (isset($_SESSION['cliente_id'])) {
    $where = 'WHERE n.cliente_id = ?';
    $params[] = $_SESSION['cliente_id'];
}
$sql = 'SELECT n.id, c.cognome AS cliente_cognome, c.nome AS cliente_nome, t.nome AS tipologia, n.data_inizio, n.data_fine, n.destinazione FROM noleggio n LEFT JOIN cliente c ON n.cliente_id = c.id LEFT JOIN tipologie_noleggio t ON n.tipologia_noleggio_id = t.id ' . $where . ' ORDER BY n.data_inizio DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$elenco_noleggi = $stmt->fetchAll();

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$azione = isset($_GET['azione']) && $_GET['azione'] === 'nuovo';
$noleggio = null;
$messaggio = 'inserisci i dati della prenotazione';


// Gestione inserimento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nuovo'])) {
    $cliente_id = isset($_SESSION['cliente_id']) ? (int)$_SESSION['cliente_id'] : (int)$_POST['cliente_id'];
    $tipologia_id = (int)$_POST['tipologia_id'];
    $data_inizio = $_POST['data_inizio'] ?? '';
    $data_fine = $_POST['data_fine'] ?? '';
    $destinazione = (($_POST['destinazione'] ?? '') !== '') ? trim($_POST['destinazione']) : null;
    $accompagnatore = (($_POST['accompagnatore'] ?? '') !== '') ? trim($_POST['accompagnatore']) : null;
    $preventivo = isset($_POST['preventivo']) ? 1 : 0;
    // Controllo date
    if (strtotime($data_inizio) > strtotime($data_fine)) {
        $messaggio = 'Errore: la data di inizio deve essere minore o uguale alla data di fine.';
            }
    elseif ($cliente_id && $tipologia_id && $data_inizio && $data_fine) {
        $stmt = $pdo->prepare('INSERT INTO noleggio (cliente_id, tipologia_noleggio_id, data_inizio, data_fine, destinazione, accompagnatore, preventivo) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$cliente_id, $tipologia_id, $data_inizio, $data_fine, $destinazione, $accompagnatore, $preventivo]);
        $_SESSION['messaggio'] = 'Noleggio inserito con successo.';
        $messaggio = 'Noleggio inserito con successo.';
        header('Location: modifica_noleggio.php');
    }
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Modifica Noleggio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; }
        .container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); padding: 2rem; }
        h1 { color: #2c3e50; }
        label { display: block; margin-top: 1em; }
        select, input { padding: 0.5em; width: 100%; margin-top: 0.5em; }
        button { background:#2980b9;color:#fff;padding:0.7em 1.5em;border-radius:5px;text-decoration:none;font-weight:bold;border:none;margin-top:1em; }
    </style>
</head>
<body>
<div class="container">
    <?php if (!empty($_SESSION['messaggio'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert" style="max-width:600px;margin:30px auto 0;">
            <strong><?= $_SESSION['messaggio'] ?></strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['messaggio']); ?>
    <?php endif; ?>
    <div class="d-flex justify-content-end" style="margin-bottom: 1em;">
        <a href="?logout=1" class="btn btn-danger">Logout</a>
    </div>
    <h1>inserisci la tua prenotazione</h1>
</body>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
</html>
<!-- popolo l'elenco -->
<div style="margin-bottom:2em;">
    <a href="modifica_noleggio.php?azione=nuovo" style="background:#27ae60;color:#fff;padding:0.4em 1em;border-radius:5px;text-decoration:none;font-size:0.95em;">Inserisci nuovo noleggio</a>
    <table style="width:100%;border-collapse:collapse;background:#fff;">
        <thead>
            <tr style="background:#eee;">
                <th>Tipologia</th>
                <th>Periodo</th>
                <th>Destinazione</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($elenco_noleggi as $n): ?>
            <tr>
                <td><?= htmlspecialchars($n['tipologia']) ?></td>
                <td><?= htmlspecialchars($n['data_inizio']) ?> - <?= htmlspecialchars($n['data_fine']) ?></td>
                <td><?= htmlspecialchars($n['destinazione'] ?? '') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<!-- gestisco l'azione -->
<?php if ($azione): ?>
    <?php if ($messaggio): ?>
        <div class="alert alert-danger text-center fw-bold" style="font-size:1.3em;max-width:600px;margin:30px auto 0;box-shadow:0 2px 8px rgba(200,0,0,0.18);border:2px solid #b71c1c;">
        <?= htmlspecialchars($messaggio) ?>
        </div>
    <?php endif; ?>
    <form method="post">
        <input type="hidden" name="nuovo" value="1">
<!-- rimosso id_cliente -->
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
        <input type="checkbox" name="preventivo" value="1"> Preventivo</label>
        <label style="display:flex;align-items:center;gap:0.5em;">
        <button type="submit">Salva</button>
    </form>

<?php endif; ?>
</div>
</body>
</html>
