<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

requireAdmin();

$adminId = (int) $_SESSION['user_id'];

// ---------- Filtres GET (mêmes que ventes.php) ----------
$filterDateDebut = trim($_GET['date_debut'] ?? '');
$filterDateFin   = trim($_GET['date_fin'] ?? '');
$filterAcheteur  = trim($_GET['acheteur'] ?? '');

// ---------- Construction SQL dynamique ----------
$sql = 'SELECT o.id, o.montant_propose AS montant, o.date_offre AS date,
               u.nom AS acheteur, u.email, u.telephone,
               v.nom AS vache
        FROM offres o
        JOIN utilisateurs u ON o.id_utilisateur = u.id
        JOIN vaches v ON o.id_vache = v.id
        WHERE o.statut = \'acceptee\' AND v.id_admin = :id_admin';

$params = [':id_admin' => $adminId];

if ($filterDateDebut !== '') {
    $sql .= ' AND DATE(o.date_offre) >= :date_debut';
    $params[':date_debut'] = $filterDateDebut;
}
if ($filterDateFin !== '') {
    $sql .= ' AND DATE(o.date_offre) <= :date_fin';
    $params[':date_fin'] = $filterDateFin;
}
if ($filterAcheteur !== '') {
    $sql .= ' AND u.id = :id_acheteur';
    $params[':id_acheteur'] = (int) $filterAcheteur;
}

$sql .= ' ORDER BY o.date_offre DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$ventes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ---------- Création du fichier Excel ----------
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Ventes');

// En-têtes
$headers = ['Nom Vache', 'Acheteur (Nom)', 'Acheteur (Email)', 'Acheteur (Téléphone)', 'Montant HT (DH)', 'Montant TTC (DH)', 'Date', 'Statut'];
$col = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($col . '1', $header);
    $col++;
}

// Style des en-têtes
$headerRange = 'A1:H1';
$sheet->getStyle($headerRange)->applyFromArray([
    'font' => [
        'bold' => true,
        'color' => ['rgb' => 'FFFFFF'],
        'size' => 11,
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '1B3A2B'],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => 'CCCCCC'],
        ],
    ],
]);

// Hauteur de la ligne d'en-tête
$sheet->getRowDimension(1)->setRowHeight(28);

// Données
$row = 2;
foreach ($ventes as $vente) {
    $montantTTC = (float) $vente['montant'];
    $montantHT  = $montantTTC / 1.20;

    $sheet->setCellValue('A' . $row, $vente['vache']);
    $sheet->setCellValue('B' . $row, $vente['acheteur']);
    $sheet->setCellValue('C' . $row, $vente['email']);
    $sheet->setCellValue('D' . $row, $vente['telephone'] ?? '');
    $sheet->setCellValue('E' . $row, round($montantHT, 2));
    $sheet->setCellValue('F' . $row, round($montantTTC, 2));
    $sheet->setCellValue('G' . $row, date('d/m/Y H:i', strtotime($vente['date'])));
    $sheet->setCellValue('H' . $row, 'Vendue');

    $row++;
}

// Format des colonnes numériques
$lastRow = max($row - 1, 1);
$sheet->getStyle('E2:F' . $lastRow)->getNumberFormat()->setFormatCode('#,##0.00');

// Style des données (bordures + alignement)
if ($lastRow >= 2) {
    $dataRange = 'A2:H' . $lastRow;
    $sheet->getStyle($dataRange)->applyFromArray([
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => 'E3D9C2'],
            ],
        ],
        'alignment' => [
            'vertical' => Alignment::VERTICAL_CENTER,
        ],
    ]);

    // Lignes alternées
    for ($r = 2; $r <= $lastRow; $r++) {
        if ($r % 2 === 0) {
            $sheet->getStyle('A' . $r . ':H' . $r)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('F9F5EC');
        }
    }
}

// Largeur automatique des colonnes
foreach (range('A', 'H') as $c) {
    $sheet->getColumnDimension($c)->setAutoSize(true);
}

// ---------- Envoi du fichier ----------
$filename = 'ventes_ferme_tarmast';
if ($filterDateDebut) $filename .= '_du_' . $filterDateDebut;
if ($filterDateFin)   $filename .= '_au_' . $filterDateFin;
$filename .= '_' . date('Ymd_His') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');
header('Pragma: public');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
