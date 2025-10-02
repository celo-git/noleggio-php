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
// Pagina per inserire una nuova fattura legata a un noleggio
require_once __DIR__ . '/../src/bootstrap.php';


// Recupera tutti i noleggi per la select
$noleggi = $pdo->query('SELECT n.id, c.cognome, c.nome, n.data_inizio, n.data_fine FROM noleggio n JOIN cliente c ON n.cliente_id = c.id ORDER BY n.data_inizio DESC')->fetchAll();

// Se viene passato un noleggio_id via GET, precompila e blocca la select
$noleggio_id_predef = isset($_GET['noleggio_id']) ? (int)$_GET['noleggio_id'] : null;

$messaggio = '';
if (isset($_POST['noleggio_id'], $_POST['numero'], $_POST['data'], $_POST['importo'])) {
    $noleggio_id = (int)$_POST['noleggio_id'];
    $numero = trim($_POST['numero']);
    $data = $_POST['data'];
    $importo = str_replace(',', '.', $_POST['importo']);
    $iva = isset($_POST['iva']) && $_POST['iva'] !== '' ? str_replace(',', '.', $_POST['iva']) : null;
    $descrizione = isset($_POST['descrizione']) ? trim($_POST['descrizione']) : null;
    if ($noleggio_id && $numero && $data && $importo !== '') {
        $importo_num = floatval($importo);
        $iva_num = ($iva !== null && $iva !== '') ? floatval($iva) : null;
        $iva_calcolata = ($iva_num !== null) ? $importo_num * $iva_num / 100 : null;
        $totale_con_iva = ($iva_calcolata !== null) ? $importo_num + $iva_calcolata : null;
        $stmt = $pdo->prepare('INSERT INTO fattura (noleggio_id, numero, data, importo, iva, iva_calcolata, totale_con_iva, descrizione) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$noleggio_id, $numero, $data, $importo, $iva, $iva_calcolata, $totale_con_iva, $descrizione]);
        $messaggio = 'Fattura inserita con successo!';
    } else {
        $messaggio = 'Compila tutti i campi obbligatori.';
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Inserisci Fattura</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; }
        .container { max-width: 500px; margin: 40px auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); padding: 2rem; }
        h1 { color: #2c3e50; }
        label { display: block; margin-top: 1em; }
        select, input, textarea, button { padding: 0.5em; width: 100%; margin-top: 0.5em; }
        .msg { color: green; margin-bottom: 1em; }
        a { display:inline-block;margin-bottom:1.5em;background:#34495e;color:#fff;padding:0.5em 1.2em;border-radius:5px;text-decoration:none; }
    </style>
</head>
<body>
<div class="container">
    <a href="gestione.php">Torna alla home</a>
    <h1>Inserisci Fattura</h1>
    <?php if ($messaggio): ?><div class="msg"><?= $messaggio ?></div><?php endif; ?>
    <?php
    // Mostra riepilogo calcolo IVA e totale dopo inserimento
    if (isset($_POST['importo']) && $_POST['importo'] !== '') {
        $importo = floatval(str_replace(',', '.', $_POST['importo']));
        $iva = isset($_POST['iva']) && $_POST['iva'] !== '' ? floatval(str_replace(',', '.', $_POST['iva'])) : null;
        if ($iva !== null) {
            $iva_calc = $importo * $iva / 100;
            $totale = $importo + $iva_calc;
            echo '<div style="margin-bottom:1em;padding:0.7em 1em;background:#f8f8f8;border-radius:6px;">';
            echo 'IVA calcolata: <b>' . number_format($iva_calc, 2, ',', '.') . ' €</b><br>';
            echo 'Totale con IVA: <b>' . number_format($totale, 2, ',', '.') . ' €</b>';
            echo '</div>';
        }
    }
    ?>
    <form method="post" id="fattura-form">
        <label>Noleggio associato:
            <select name="noleggio_id" required <?= $noleggio_id_predef ? 'readonly disabled' : '' ?>>
                <option value="">-- Seleziona noleggio --</option>
                <?php foreach ($noleggi as $n): ?>
                    <option value="<?= $n['id'] ?>" <?= ($noleggio_id_predef && $n['id'] == $noleggio_id_predef) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($n['cognome'] . ' ' . $n['nome']) ?> | <?= htmlspecialchars($n['data_inizio']) ?> - <?= htmlspecialchars($n['data_fine']) ?> (ID: <?= $n['id'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if ($noleggio_id_predef): ?>
                <input type="hidden" name="noleggio_id" value="<?= $noleggio_id_predef ?>">
            <?php endif; ?>
        </label>
        <label>Numero fattura:
            <input type="text" name="numero" required maxlength="50">
        </label>
        <label>Data fattura:
            <input type="date" name="data" required>
        </label>
        <label>Importo (€):
            <input type="number" name="importo" step="0.01" min="0" required>
        </label>
        <label>IVA (%):
            <input type="number" name="iva" step="0.01" min="0" max="100" placeholder="22">
        </label>
        <label>Descrizione:
            <textarea name="descrizione" maxlength="255" placeholder="Descrizione (opzionale)"></textarea>
        </label>
        <button type="submit" style="background:#2980b9;color:#fff;padding:0.7em 1.5em;border-radius:5px;text-decoration:none;font-weight:bold;border:none;margin-top:1em;">Inserisci Fattura</button>
    </form>
    <script>
    // Calcolo dinamico IVA e totale
    document.addEventListener('DOMContentLoaded', function() {
        const importoInput = document.querySelector('input[name="importo"]');
        const ivaInput = document.querySelector('input[name="iva"]');
        const form = document.getElementById('fattura-form');
        let infoBox = null;
        function updateIvaBox() {
            if (!infoBox) {
                infoBox = document.createElement('div');
                infoBox.style.marginBottom = '1em';
                infoBox.style.padding = '0.7em 1em';
                infoBox.style.background = '#f8f8f8';
                infoBox.style.borderRadius = '6px';
                form.parentNode.insertBefore(infoBox, form);
            }
            const importo = parseFloat(importoInput.value.replace(',', '.'));
            const iva = parseFloat(ivaInput.value.replace(',', '.'));
            if (!isNaN(importo) && !isNaN(iva)) {
                const ivaCalc = importo * iva / 100;
                const totale = importo + ivaCalc;
                infoBox.innerHTML = 'IVA calcolata: <b>' + ivaCalc.toLocaleString('it-IT', {minimumFractionDigits:2, maximumFractionDigits:2}) + ' €</b><br>' +
                    'Totale con IVA: <b>' + totale.toLocaleString('it-IT', {minimumFractionDigits:2, maximumFractionDigits:2}) + ' €</b>';
            } else {
                infoBox.innerHTML = '';
            }
        }
        importoInput.addEventListener('input', updateIvaBox);
        ivaInput.addEventListener('input', updateIvaBox);
    });
    </script>
    </form>
</div>
</body>
</html>
