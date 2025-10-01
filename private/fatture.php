<?php
session_start();
if (!isset($_SESSION['utente_id'])) {
    header('Location: ../public/accesso.php');
    exit;
}
?>
<?php
// Pagina elenco tabellare delle fatture
require_once __DIR__ . '/../src/bootstrap.php';

// Filtri ricerca
$numero = isset($_GET['numero']) ? trim($_GET['numero']) : '';
$cliente = isset($_GET['cliente']) ? trim($_GET['cliente']) : '';
$dal = isset($_GET['dal']) ? $_GET['dal'] : '';
$al = isset($_GET['al']) ? $_GET['al'] : '';

$where = [];
$params = [];
if ($numero !== '') {
    $where[] = 'f.numero LIKE ?';
    $params[] = "%$numero%";
}
if ($cliente !== '') {
    $where[] = '(c.nome LIKE ? OR c.cognome LIKE ?)';
    $params[] = "%$cliente%";
    $params[] = "%$cliente%";
}
if ($dal !== '') {
    $where[] = 'f.data >= ?';
    $params[] = $dal;
}
if ($al !== '') {
    $where[] = 'f.data <= ?';
    $params[] = $al;
}
$sql = 'SELECT f.id, f.numero, f.data, f.importo, f.iva, f.iva_calcolata, f.totale_con_iva, f.descrizione, n.id AS noleggio_id, n.data_inizio, n.data_fine, c.nome, c.cognome FROM fattura f JOIN noleggio n ON f.noleggio_id = n.id JOIN cliente c ON n.cliente_id = c.id';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY f.data DESC, f.id DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$fatture = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Elenco Fatture</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; }
        .container { max-width: 1100px; margin: 40px auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); padding: 2rem; }
        h1 { color: #2c3e50; }
        table { border-collapse: collapse; width: 100%; margin-top: 2em; }
        th, td { border: 1px solid #ddd; padding: 0.7em 1em; text-align: left; }
        th { background: #2980b9; color: #fff; }
        tr:nth-child(even) { background: #f9f9f9; }
        a { color: #2980b9; text-decoration: underline; }
        .btn { display:inline-block;background:#2980b9;color:#fff;padding:0.5em 1.2em;border-radius:5px;text-decoration:none;margin-bottom:1.5em; }
    </style>
</head>
<body>
<div class="container">
    <a href="index.php" class="btn">Torna alla home</a>
    <a href="inserisci_fattura.php" class="btn" style="background:#c0392b;">Nuova Fattura</a>
    <h1>Elenco Fatture</h1>
    <form method="get" style="margin-bottom:2em;display:flex;gap:1em;flex-wrap:wrap;align-items:flex-end;">
        <div>
            <label>Numero fattura<br><input type="text" name="numero" value="<?= htmlspecialchars($numero) ?>" placeholder="Numero"></label>
        </div>
        <div>
            <label>Cliente<br><input type="text" name="cliente" value="<?= htmlspecialchars($cliente) ?>" placeholder="Nome o Cognome"></label>
        </div>
        <div>
            <label>Dal<br><input type="date" name="dal" value="<?= htmlspecialchars($dal) ?>"></label>
        </div>
        <div>
            <label>Al<br><input type="date" name="al" value="<?= htmlspecialchars($al) ?>"></label>
        </div>
        <div>
            <button type="submit" style="background:#2980b9;color:#fff;padding:0.5em 1.2em;border-radius:5px;border:none;">Cerca</button>
        </div>
    </form>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Numero</th>
                <th>Data</th>
                <th>Importo (€)</th>
                <th>IVA (%)</th>
                <th>IVA calcolata (€)</th>
                <th>Totale con IVA (€)</th>
                <th>Descrizione</th>
                <th>Noleggio (ID)</th>
                <th>Cliente</th>
                <th>Periodo Noleggio</th>
                <th>Azioni</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($fatture as $f): ?>
            <tr>
                <td><?= $f['id'] ?></td>
                <td><?= htmlspecialchars($f['numero']) ?></td>
                <td><?= htmlspecialchars($f['data']) ?></td>
                <td><?= number_format($f['importo'], 2, ',', '.') ?></td>
                <td><?= $f['iva'] !== null ? number_format($f['iva'], 2, ',', '.') : '-' ?></td>
                <td>
                    <?= $f['iva_calcolata'] !== null ? number_format($f['iva_calcolata'], 2, ',', '.') : '-' ?>
                </td>
                <td>
                    <?= $f['totale_con_iva'] !== null ? number_format($f['totale_con_iva'], 2, ',', '.') : '-' ?>
                </td>
                <td><?= htmlspecialchars($f['descrizione']) ?></td>
                <td><?= $f['noleggio_id'] ?></td>
                <td><?= htmlspecialchars($f['cognome'] . ' ' . $f['nome']) ?></td>
                <td><?= htmlspecialchars($f['data_inizio']) ?> - <?= htmlspecialchars($f['data_fine']) ?></td>
                <td>
                    <a href="stampa_fattura.php?id=<?= $f['id'] ?>" target="_blank" style="background:#2980b9;color:#fff;padding:0.4em 1em;border-radius:5px;text-decoration:none;font-size:0.95em;">Stampa PDF</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
