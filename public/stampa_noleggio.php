<?php
// stampa_noleggio.php: genera PDF per un noleggio
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/bootstrap.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_GET['id'])) {
    die('ID noleggio mancante');
}
$id = (int)$_GET['id'];

// Recupera dati noleggio e relativi (cliente, automezzo, autisti, tipologia)
$stmt = $pdo->prepare('
    SELECT n.*, c.nome AS cliente_nome, c.cognome AS cliente_cognome, c.indirizzo AS cliente_indirizzo, c.telefono AS cliente_telefono,
           a.targa AS automezzo_targa, a.modello AS automezzo_modello, a.marca AS automezzo_marca,
           t.nome AS tipologia_nome,
           au1.nome AS autista1_nome, au1.cognome AS autista1_cognome,
           au2.nome AS autista2_nome, au2.cognome AS autista2_cognome
    FROM noleggio n
    LEFT JOIN cliente c ON n.cliente_id = c.id
    LEFT JOIN automezzo a ON n.automezzo_id = a.id
    LEFT JOIN tipologie_noleggio t ON n.tipologia_noleggio_id = t.id
    LEFT JOIN autista au1 ON n.autista1_id = au1.id
    LEFT JOIN autista au2 ON n.autista2_id = au2.id
    WHERE n.id = ?
');
$stmt->execute([$id]);
$noleggio = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$noleggio) {
    die('Noleggio non trovato');
}

// HTML per il PDF
$html = '<h2 style="text-align:center;">Dettaglio Noleggio</h2>';
$html .= '<table style="width:100%;border-collapse:collapse;">';
$html .= '<tr><td><b>Cliente:</b></td><td>' . htmlspecialchars($noleggio['cliente_nome'] . ' ' . $noleggio['cliente_cognome']) . '</td></tr>';
$html .= '<tr><td><b>Indirizzo:</b></td><td>' . htmlspecialchars($noleggio['cliente_indirizzo']) . '</td></tr>';
$html .= '<tr><td><b>Telefono:</b></td><td>' . htmlspecialchars($noleggio['cliente_telefono']) . '</td></tr>';
$html .= '<tr><td><b>Automezzo:</b></td><td>' . htmlspecialchars($noleggio['automezzo_marca'] . ' ' . $noleggio['automezzo_modello'] . ' (' . $noleggio['automezzo_targa'] . ')') . '</td></tr>';
$html .= '<tr><td><b>Tipologia:</b></td><td>' . htmlspecialchars($noleggio['tipologia_nome']) . '</td></tr>';
$html .= '<tr><td><b>Periodo:</b></td><td>' . htmlspecialchars($noleggio['data_inizio']) . ' - ' . htmlspecialchars($noleggio['data_fine']) . '</td></tr>';
$html .= '<tr><td><b>Importo:</b></td><td>' . number_format($noleggio['importo'], 2, ',', '.') . ' €</td></tr>';
if (!empty($noleggio['accompagnatore'])) {
    $html .= '<tr><td><b>Accompagnatore:</b></td><td>' . htmlspecialchars($noleggio['accompagnatore']) . '</td></tr>';
}
if (!empty($noleggio['autista1_nome'])) {
    $html .= '<tr><td><b>Autista 1:</b></td><td>' . htmlspecialchars($noleggio['autista1_nome'] . ' ' . $noleggio['autista1_cognome']) . '</td></tr>';
}
if (!empty($noleggio['autista2_nome'])) {
    $html .= '<tr><td><b>Autista 2:</b></td><td>' . htmlspecialchars($noleggio['autista2_nome'] . ' ' . $noleggio['autista2_cognome']) . '</td></tr>';
}
$html .= '<tr><td><b>Destinazione:</b></td><td>' . htmlspecialchars($noleggio['destinazione']) . '</td></tr>';
$html .= '<tr><td><b>Preventivo:</b></td><td>' . ($noleggio['preventivo'] ? 'Sì' : 'No') . '</td></tr>';
$html .= '<tr><td><b>Pagato:</b></td><td>' . ($noleggio['pagato'] ? 'Sì' : 'No') . '</td></tr>';
$html .= '</table>';

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream('noleggio_' . $id . '.pdf', ['Attachment' => false]);
exit;
