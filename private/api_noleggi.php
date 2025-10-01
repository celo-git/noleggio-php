<?php
session_start();
if (!isset($_SESSION['utente_id'])) {
    header('Location: ../public/accesso.php');
    exit;
}
?>
<?php
// API per fornire i noleggi in formato JSON per il calendario
require_once __DIR__ . '/../src/bootstrap.php';
header('Content-Type: application/json');


if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare('SELECT n.*, c.nome AS cliente_nome, c.cognome AS cliente_cognome, a.targa, t.nome AS tipologia, a.marca, a.modello,
        au1.nome AS autista1_nome, au1.cognome AS autista1_cognome, au2.nome AS autista2_nome, au2.cognome AS autista2_cognome
        FROM noleggio n
        LEFT JOIN cliente c ON n.cliente_id = c.id
        LEFT JOIN automezzo a ON n.automezzo_id = a.id
        LEFT JOIN tipologie_noleggio t ON n.tipologia_noleggio_id = t.id
        LEFT JOIN autista au1 ON n.autista1_id = au1.id
        LEFT JOIN autista au2 ON n.autista2_id = au2.id
        WHERE n.id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if ($row) {
        // Recupera la fattura associata (se esiste)
        $fattura = null;
        $stmtF = $pdo->prepare('SELECT * FROM fattura WHERE noleggio_id = ? LIMIT 1');
        $stmtF->execute([$row['id']]);
        $fatturaRow = $stmtF->fetch();
        if ($fatturaRow) {
            $fattura = [
                'Numero fattura' => $fatturaRow['numero'],
                'Data fattura' => $fatturaRow['data'],
                'Importo fattura' => $fatturaRow['importo'],
                'IVA %' => $fatturaRow['iva'],
                'IVA calcolata' => $fatturaRow['iva_calcolata'],
                'Totale con IVA' => $fatturaRow['totale_con_iva'],
                'Descrizione fattura' => $fatturaRow['descrizione'],
            ];
        }
        $result = [
            'id' => $row['id'],
            'Cliente' => $row['cliente_cognome'] . ' ' . $row['cliente_nome'],
            'Tipologia' => $row['tipologia'],
            'Targa' => $row['targa'],
            'Automezzo' => ($row['marca'] ?? '') . ' ' . ($row['modello'] ?? ''),
            'Autista 1' => ($row['autista1_cognome'] || $row['autista1_nome']) ? trim(($row['autista1_cognome'] ?? '') . ' ' . ($row['autista1_nome'] ?? '')) : null,
            'Autista 2' => ($row['autista2_cognome'] || $row['autista2_nome']) ? trim(($row['autista2_cognome'] ?? '') . ' ' . ($row['autista2_nome'] ?? '')) : null,
            'Data inizio' => $row['data_inizio'],
            'Data fine' => $row['data_fine'],
            'Importo' => $row['importo'],
            'Destinazione' => $row['destinazione'],
            'Accompagnatore' => $row['accompagnatore'],
            'Preventivo' => $row['preventivo'] ? 'Sì' : 'No',
            'Pagato' => $row['pagato'] ? 'Sì' : 'No',
            'Ivato' => $row['ivato'] ? 'Sì' : 'No',
        ];
        if ($fattura) {
            $result['Fattura'] = $fattura;
        }
        echo json_encode($result);
    } else {
        echo json_encode(['error' => 'Noleggio non trovato']);
    }
    exit;
}


// Recupera tutti gli automezzi
$mezzi = [];
$stmt = $pdo->query('SELECT id, targa, marca, modello FROM automezzo ORDER BY modello');
while ($row = $stmt->fetch()) {
    $mezzi[$row['id']] = [
        'id' => $row['id'],
        'targa' => $row['targa'],
        'modello' => $row['modello'],
        'marca' => $row['marca']
    ];
}

// Recupera tutti i noleggi

$where = [];
$params = [];
if (isset($_GET['pagato']) && ($_GET['pagato'] === '0' || $_GET['pagato'] === '1')) {
    $where[] = 'n.pagato = ?';
    $params[] = (int)$_GET['pagato'];
}
$sql = 'SELECT n.id, n.data_inizio, n.data_fine, c.nome AS cliente_nome, 
c.cognome AS cliente_cognome, a.targa, t.nome AS tipologia, n.automezzo_id, n.autista1_id, n.autista2_id
FROM noleggio n
LEFT JOIN cliente c ON n.cliente_id = c.id
LEFT JOIN automezzo a ON n.automezzo_id = a.id
LEFT JOIN tipologie_noleggio t ON n.tipologia_noleggio_id = t.id';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$noleggi = [];
while ($row = $stmt->fetch()) {
    $noleggi[] = [
        'id' => $row['id'],
        'targa' => $row['targa'],
        'title' => $row['cliente_cognome'] . ' ' . $row['cliente_nome'] ,
        'start' => $row['data_inizio'],
        'end' => date('Y-m-d', strtotime($row['data_fine'] . ' +1 day')),
        'automezzo_id' => $row['automezzo_id'],
        'autista1_id' => $row['autista1_id'],
        'autista2_id' => $row['autista2_id']
    ];
}




// Righe per ogni noleggio senza automezzo (automezzo_id nullo o vuoto)
$results = [];
foreach ($noleggi as $noleggio) {
    if (empty($noleggio['automezzo_id'])) {
        $results[] = [
            'id' => $noleggio['id'],
            'targa' => '',
            'title' => $noleggio['title'],
            'start' => $noleggio['start'],
            'end' => $noleggio['end'],
            'mezzo_label' => $noleggio['title'],
            'automezzo_id' => null
        ];
    }
}
// Righe raggruppate per automezzo per i noleggi con automezzo
foreach ($mezzi as $mezzo_id => $mezzo) {
    $trovato = false;
    foreach ($noleggi as $noleggio) {
        if ($noleggio['automezzo_id'] == $mezzo_id) {
            $results[] = $noleggio + [
                'mezzo_label' => ($mezzo['marca'] ? $mezzo['marca'].' ' : '') . $mezzo['modello'],
            ];
            $trovato = true;
        }
    }
    if (!$trovato) {
        $results[] = [
            'id' => null,
            'targa' => $mezzo['targa'],
            'title' => '',
            'start' => null,
            'end' => null,
            'mezzo_label' => ($mezzo['marca'] ? $mezzo['marca'].' ' : '') . $mezzo['modello'],
            'automezzo_id' => $mezzo_id
        ];
    }
}
echo json_encode($results);
