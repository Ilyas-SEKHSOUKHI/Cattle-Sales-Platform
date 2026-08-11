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

requireAdmin();

$adminId = (int) $_SESSION['user_id'];

// ---------- Filtres GET ----------
$filterDateDebut = trim($_GET['date_debut'] ?? '');
$filterDateFin   = trim($_GET['date_fin'] ?? '');
$filterAcheteur  = trim($_GET['acheteur'] ?? '');

// ---------- Construction SQL ----------
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

// ============================================================
//  CRÉATION DU FICHIER EXCEL — DESIGN COMPACT & CENTRÉ
// ============================================================

$spreadsheet = new Spreadsheet();
$spreadsheet->getProperties()
    ->setCreator('Ferme Tarmast')
    ->setTitle('Ventes')
    ->setDescription('Export des ventes');

$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Ventes');

// ---------- Couleurs ----------
$colorForest   = '1B3A2B';
$colorCream    = 'FBF6EC';
$colorWhite    = 'FFFFFF';
$colorInk      = '2A2A25';
$colorLine     = 'E3D9C2';
$colorGreenDark= '2E7D32';

// ---------- EN-TÊTES (Sans #, orthographe corrigée, sans DH) ----------
$headers = [
    'A' => 'Numéro de série',
    'B' => 'Date',
    'C' => 'Nom & Prénom',
    'D' => 'Adresse mail',
    'E' => 'Téléphone',
    'F' => 'Produit',
    'G' => 'Quantité',
    'H' => 'Montant HT',
    'I' => 'Montant TTC',
];

$headerRow = 1;
foreach ($headers as $col => $label) {
    $sheet->setCellValue($col . $headerRow, $label);
}

// Style En-têtes (compact 9.5pt, vert forêt, centré, texte blanc gras)
$sheet->getStyle('A1:I1')->applyFromArray([
    'font' => [
        'bold' => true,
        'size' => 9.5,
        'color' => ['rgb' => $colorWhite],
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => $colorForest],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => '10231A'],
        ],
    ],
]);
$sheet->getRowDimension(1)->setRowHeight(24);

// ---------- DONNÉES (Ligne 2+) ----------
$dataStartRow = 2;
$row = $dataStartRow;

foreach ($ventes as $vente) {
    $montantTTC = (float) $vente['montant'];
    $montantHT  = $montantTTC; // TVA 0%
    $dateFormatted = !empty($vente['date']) ? date('d/m/Y', strtotime($vente['date'])) : '';

    $sheet->setCellValue('A' . $row, $vente['id']);
    $sheet->setCellValue('B' . $row, $dateFormatted);
    $sheet->setCellValue('C' . $row, $vente['acheteur']);
    $sheet->setCellValue('D' . $row, $vente['email']);
    $sheet->setCellValue('E' . $row, !empty($vente['telephone']) ? $vente['telephone'] : '—');
    $sheet->setCellValue('F' . $row, $vente['vache']);
    $sheet->setCellValue('G' . $row, 1);
    $sheet->setCellValue('H' . $row, round($montantHT, 2));
    $sheet->setCellValue('I' . $row, round($montantTTC, 2));

    $row++;
}

$lastDataRow = max($row - 1, $dataStartRow);

// ---------- Style des données (Taille 9pt compacte, tout centré) ----------
if ($lastDataRow >= $dataStartRow) {
    $dataRange = 'A' . $dataStartRow . ':I' . $lastDataRow;

    $sheet->getStyle($dataRange)->applyFromArray([
        'font' => [
            'size' => 9,
            'color' => ['rgb' => $colorInk],
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => $colorLine],
            ],
        ],
    ]);

    // Fond alterné pour la lisibilité
    for ($r = $dataStartRow; $r <= $lastDataRow; $r++) {
        $bgColor = ($r % 2 === 0) ? $colorCream : $colorWhite;
        $sheet->getStyle('A' . $r . ':I' . $r)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB($bgColor);
        $sheet->getRowDimension($r)->setRowHeight(19);
    }

    // Format des montants
    $sheet->getStyle('H' . $dataStartRow . ':I' . $lastDataRow)
        ->getNumberFormat()
        ->setFormatCode('#,##0.00');

    // Montants TTC en vert foncé gras
    $sheet->getStyle('I' . $dataStartRow . ':I' . $lastDataRow)->applyFromArray([
        'font' => [
            'bold' => true,
            'color' => ['rgb' => $colorGreenDark],
        ],
    ]);
}

// ---------- Largeurs des colonnes compactes ----------
$colWidths = [
    'A' => 14,   // Numéro de série
    'B' => 12,   // Date
    'C' => 20,   // Nom & Prénom
    'D' => 25,   // Adresse mail
    'E' => 15,   // Téléphone
    'F' => 18,   // Produit
    'G' => 10,   // Quantité
    'H' => 14,   // Montant HT
    'I' => 14,   // Montant TTC
];
foreach ($colWidths as $c => $w) {
    $sheet->getColumnDimension($c)->setWidth($w);
}

// ---------- Figer la ligne 1 ----------
$sheet->freezePane('A2');

// ---------- Filtre automatique sur en-tête ----------
$sheet->setAutoFilter('A1:I1');

// ============================================================
//  ENVOI DU FICHIER EXCEL
// ============================================================
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
