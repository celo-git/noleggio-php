<?php
session_start();
if (!isset($_SESSION['utente_id'])) {
    header('Location: ../public/accesso.php');
    exit;
}
?>
<?php
// Genera un PDF della fattura
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../vendor/autoload.php'; // Assumendo che dompdf sia installato via composer

use Dompdf\Dompdf;

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    die('ID fattura non specificato.');
}
$stmt = $pdo->prepare('SELECT f.*, n.id AS noleggio_id, n.data_inizio, n.data_fine, c.nome, c.cognome FROM fattura f JOIN noleggio n ON f.noleggio_id = n.id JOIN cliente c ON n.cliente_id = c.id WHERE f.id = ?');
$stmt->execute([$id]);
$fattura = $stmt->fetch();
if (!$fattura) {
    die('Fattura non trovata.');
}

$html = '<h2>Fattura n. ' . htmlspecialchars($fattura['numero']) . '</h2>';
$html .= '<p><b>Cliente:</b> ' . htmlspecialchars($fattura['cognome'] . ' ' . $fattura['nome']) . '</p>';
$html .= '<p><b>Periodo noleggio:</b> ' . htmlspecialchars($fattura['data_inizio']) . ' - ' . htmlspecialchars($fattura['data_fine']) . '</p>';
$html .= '<p><b>Data fattura:</b> ' . htmlspecialchars($fattura['data']) . '</p>';
$html .= '<p><b>Importo:</b> ' . number_format($fattura['importo'], 2, ',', '.') . ' €</p>';
$html .= '<p><b>IVA:</b> ' . ($fattura['iva'] !== null ? number_format($fattura['iva'], 2, ',', '.') . ' %' : '-') . '</p>';
$html .= '<p><b>IVA calcolata:</b> ' . ($fattura['iva_calcolata'] !== null ? number_format($fattura['iva_calcolata'], 2, ',', '.') . ' €' : '-') . '</p>';
$html .= '<p><b>Totale con IVA:</b> ' . ($fattura['totale_con_iva'] !== null ? number_format($fattura['totale_con_iva'], 2, ',', '.') . ' €' : '-') . '</p>';
$html .= '<p><b>Descrizione:</b> ' . htmlspecialchars($fattura['descrizione']) . '</p>';

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream('fattura_' . $fattura['numero'] . '.pdf', ['Attachment' => false]);
exit;
