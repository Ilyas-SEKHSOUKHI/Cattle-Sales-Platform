<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

requireAdmin();

$adminId = (int) $_SESSION['user_id'];

ensureColumnExists($pdo, 'utilisateurs', 'ice', "VARCHAR(50) NULL DEFAULT ''");
ensureFacturesTableExists($pdo);
syncAllFactures($pdo);

$recup = trim($_GET['recup'] ?? '');
$recupMois = trim($_GET['recup_mois'] ?? '');
$recupAnnee = trim($_GET['recup_annee'] ?? '');
$idAcheteur = filter_input(INPUT_GET, 'acheteur', FILTER_VALIDATE_INT);
$factureIdsRaw = $_GET['facture_ids'] ?? [];

$factures = [];
$titlePeriode = '';

// Traitement selon le mode de récupération
if (!empty($factureIdsRaw)) {
    if (is_string($factureIdsRaw)) {
        $factureIds = array_map('intval', explode(',', $factureIdsRaw));
    } else {
        $factureIds = array_map('intval', (array)$factureIdsRaw);
    }
    $factureIds = array_filter($factureIds);

    if (!empty($factureIds)) {
        $inClause = implode(',', array_fill(0, count($factureIds), '?'));
        $sql = "SELECT f.*, 
                       u.id AS acheteur_id, u.nom AS acheteur_nom, u.email AS acheteur_email, u.telephone AS acheteur_tel, u.ice AS acheteur_ice,
                       v.id AS id_vache, v.nom AS vache_nom, v.bovin, v.poids,
                       o.date_reprise, o.date_offre
                FROM factures f
                JOIN utilisateurs u ON f.id_utilisateur = u.id
                JOIN vaches v ON f.id_vache = v.id
                JOIN offres o ON f.id_offre = o.id
                WHERE f.id IN ($inClause) AND v.id_admin = ?
                ORDER BY f.date_facture ASC";
        $params = array_merge($factureIds, [$adminId]);
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $factures = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $titlePeriode = 'Sélection par lot (' . count($factures) . ' factures)';
    }
} elseif ($recup === 'mois' && $idAcheteur && $recupMois !== '') {
    $stmt = $pdo->prepare(
        "SELECT f.*, 
                u.id AS acheteur_id, u.nom AS acheteur_nom, u.email AS acheteur_email, u.telephone AS acheteur_tel, u.ice AS acheteur_ice,
                v.id AS id_vache, v.nom AS vache_nom, v.bovin, v.poids,
                o.date_reprise, o.date_offre
         FROM factures f
         JOIN utilisateurs u ON f.id_utilisateur = u.id
         JOIN vaches v ON f.id_vache = v.id
         JOIN offres o ON f.id_offre = o.id
         WHERE f.id_utilisateur = :id_acheteur 
           AND DATE_FORMAT(f.date_facture, '%Y-%m') = :recup_mois 
           AND v.id_admin = :id_admin
         ORDER BY f.date_facture ASC"
    );
    $stmt->execute([
        ':id_acheteur' => $idAcheteur,
        ':recup_mois' => $recupMois,
        ':id_admin' => $adminId
    ]);
    $factures = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $dateObj = DateTime::createFromFormat('Y-m', $recupMois);
    $titlePeriode = 'Mois de ' . ($dateObj ? $dateObj->format('m/Y') : $recupMois);
} elseif ($recup === 'annee' && $idAcheteur && $recupAnnee !== '') {
    $stmt = $pdo->prepare(
        "SELECT f.*, 
                u.id AS acheteur_id, u.nom AS acheteur_nom, u.email AS acheteur_email, u.telephone AS acheteur_tel, u.ice AS acheteur_ice,
                v.id AS id_vache, v.nom AS vache_nom, v.bovin, v.poids,
                o.date_reprise, o.date_offre
         FROM factures f
         JOIN utilisateurs u ON f.id_utilisateur = u.id
         JOIN vaches v ON f.id_vache = v.id
         JOIN offres o ON f.id_offre = o.id
         WHERE f.id_utilisateur = :id_acheteur 
           AND YEAR(f.date_facture) = :recup_annee 
           AND v.id_admin = :id_admin
         ORDER BY f.date_facture ASC"
    );
    $stmt->execute([
        ':id_acheteur' => $idAcheteur,
        ':recup_annee' => $recupAnnee,
        ':id_admin' => $adminId
    ]);
    $factures = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $titlePeriode = 'Année ' . $recupAnnee;
} elseif ($idAcheteur) {
    // Tous les achats d'un acheteur spécifique
    $stmt = $pdo->prepare(
        "SELECT f.*, 
                u.id AS acheteur_id, u.nom AS acheteur_nom, u.email AS acheteur_email, u.telephone AS acheteur_tel, u.ice AS acheteur_ice,
                v.id AS id_vache, v.nom AS vache_nom, v.bovin, v.poids,
                o.date_reprise, o.date_offre
         FROM factures f
         JOIN utilisateurs u ON f.id_utilisateur = u.id
         JOIN vaches v ON f.id_vache = v.id
         JOIN offres o ON f.id_offre = o.id
         WHERE f.id_utilisateur = :id_acheteur 
           AND v.id_admin = :id_admin
         ORDER BY f.date_facture ASC"
    );
    $stmt->execute([
        ':id_acheteur' => $idAcheteur,
        ':id_admin' => $adminId
    ]);
    $factures = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $titlePeriode = 'Historique des achats';
}

if (empty($factures)) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Aucune facture correspondant aux critères sélectionnés.'];
    redirect('factures.php');
}

$distinctBuyers = array_unique(array_column($factures, 'acheteur_id'));
if (count($distinctBuyers) > 1) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Une facture récapitulative ne peut concerner qu\'un seul et même acheteur.'];
    redirect('factures.php?recup=lot');
}

$premier = $factures[0];
$clientIce = !empty($premier['acheteur_ice'])
    ? $premier['acheteur_ice']
    : '00' . sprintf('%013d', abs(crc32('client_ice_' . $premier['acheteur_id'])) % 10000000000000);

$totalHT = 0;
$totalTTC = 0;
foreach ($factures as $f) {
    $totalTTC += (float) $f['montant_ttc'];
}
$totalHT = $totalTTC; // TVA 0%
$tvaTotale = 0;
$montantEnLettres = nombreEnLettres($totalTTC);

$numFactureRecap = 'RECAP-' . date('Ym') . '-' . str_pad((string)$premier['acheteur_id'], 4, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;600;700&family=Work+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="icon" type="image/png" href="../assets/images/iconVache.png">
<title>Facture Récapitulative <?php echo htmlspecialchars($numFactureRecap); ?> — Ferme Tarmast</title>
<style>
  :root {
    --forest: #1B3A2B;
    --forest-2: #142A20;
    --cream: #FBF6EC;
    --cream-2: #F2EAD8;
    --green: #4CAF50;
    --green-dark: #2E7D32;
    --ink: #2A2A25;
    --ink-soft: #5C5B52;
    --line: #E3D9C2;
    --display: "Fraunces", Georgia, serif;
    --body: "Work Sans", Arial, sans-serif;
  }

  * { margin:0; padding:0; box-sizing:border-box; }

  body {
    font-family: var(--body);
    background: var(--cream);
    color: var(--ink);
    line-height: 1.5;
    padding: 2rem 1rem;
  }

  .action-bar {
    max-width: 850px;
    margin: 0 auto 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .btn {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    padding: .6rem 1.2rem;
    border-radius: 8px;
    font-size: .88rem;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: background .18s;
  }
  .btn-secondary {
    background: #fff;
    color: var(--ink);
    border: 1px solid var(--line);
  }
  .btn-secondary:hover { background: var(--cream-2); }
  .btn-primary {
    background: var(--forest);
    color: #fff;
    border: none;
  }
  .btn-primary:hover { background: var(--forest-2); }
  .btn svg { width: 18px; height: 18px; }

  .invoice-card {
    max-width: 850px;
    margin: 0 auto;
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 12px;
    padding: 2.5rem 3rem;
    box-shadow: 0 8px 24px rgba(0,0,0,.06);
  }

  .invoice-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding-bottom: 2rem;
    border-bottom: 2px solid var(--forest);
    margin-bottom: 2rem;
  }

  .brand-logo {
    display: flex;
    align-items: center;
    gap: 1rem;
  }
  .brand-logo img {
    width: 64px;
    height: 64px;
    object-fit: contain;
  }
  .brand-text h1 {
    font-family: var(--display);
    font-size: 1.5rem;
    color: var(--forest);
    line-height: 1.2;
  }
  .brand-text p {
    font-size: .82rem;
    color: var(--ink-soft);
  }

  .invoice-meta {
    text-align: right;
  }
  .invoice-title {
    font-family: var(--display);
    font-size: 1.35rem;
    color: var(--forest);
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
  }
  .invoice-num {
    font-size: .95rem;
    font-weight: 700;
    color: var(--green-dark);
    margin-top: .2rem;
  }
  .invoice-date {
    font-size: .84rem;
    color: var(--ink-soft);
    margin-top: .2rem;
  }

  .invoice-details {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    margin-bottom: 2rem;
  }

  .box-detail {
    background: var(--cream);
    border: 1px solid var(--line);
    border-radius: 8px;
    padding: 1.2rem;
  }
  .box-detail p {
    font-size: .88rem;
    color: var(--ink);
    line-height: 1.6;
  }
  .box-detail strong {
    color: var(--forest);
  }

  .invoice-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 2rem;
  }
  .invoice-table th {
    background: var(--forest);
    color: #fff;
    text-align: left;
    padding: .75rem .9rem;
    font-size: .78rem;
    text-transform: uppercase;
    letter-spacing: .05em;
    font-weight: 600;
  }
  .invoice-table th.text-right,
  .invoice-table td.text-right {
    text-align: right;
  }
  .invoice-table th.text-center,
  .invoice-table td.text-center {
    text-align: center;
  }
  .invoice-table td {
    padding: .85rem .9rem;
    border-bottom: 1px solid var(--line);
    font-size: .88rem;
  }
  .invoice-table tbody tr:nth-child(even) {
    background: var(--cream);
  }

  .item-name {
    font-weight: 700;
    color: var(--forest);
  }

  .totals-section {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 2rem;
    margin-bottom: 2rem;
  }

  .words-box {
    flex: 1;
    background: #F4EFE2;
    border: 1px solid var(--line);
    border-left: 4px solid var(--forest);
    border-radius: 6px;
    padding: 1rem 1.2rem;
  }
  .words-box h4 {
    font-size: .78rem;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: var(--ink-soft);
    margin-bottom: .3rem;
  }
  .words-box p {
    font-family: var(--display);
    font-size: .95rem;
    font-weight: 600;
    color: var(--forest);
    line-height: 1.4;
  }

  .totals-table {
    width: 280px;
    border-collapse: collapse;
  }
  .totals-table td {
    padding: .45rem 0;
    font-size: .9rem;
  }
  .totals-table td.lbl {
    color: var(--ink-soft);
  }
  .totals-table td.val {
    text-align: right;
    font-weight: 600;
  }
  .totals-table tr.grand-total td {
    border-top: 2px solid var(--forest);
    padding-top: .7rem;
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--green-dark);
  }

  .legal-footer {
    text-align: center;
    margin-top: 2.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--line);
    font-size: .76rem;
    color: var(--ink-soft);
    line-height: 1.4;
  }

  @media print {
    @page {
      size: auto;
      margin: 10mm 15mm;
    }
    body {
      background: #fff;
      padding: 0;
      color: #000;
    }
    .action-bar { display: none !important; }
    .invoice-card {
      border: none;
      box-shadow: none;
      padding: 0;
      max-width: 100%;
    }
    .box-detail, .words-box {
      background: #FBF6EC !important;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }
    .invoice-table th {
      background: #1B3A2B !important;
      color: #fff !important;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }
  }
</style>
</head>
<body>

<div class="action-bar">
  <a href="factures.php" class="btn btn-secondary">
    <svg viewBox="0 0 24 24" fill="none"><path d="M19 12H5M12 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
    Retour aux factures
  </a>
  <button onclick="window.print()" class="btn btn-primary">
    <svg viewBox="0 0 24 24" fill="none"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
    Imprimer / Télécharger (PDF)
  </button>
</div>

<div class="invoice-card">

  <!-- EN-TÊTE -->
  <div class="invoice-header">
    <div class="brand-logo">
      <img src="../assets/images/iconVache.png" alt="Logo Ferme Tarmast">
      <div class="brand-text">
        <h1>Ferme Tarmast</h1>
        <p>Élevage bovin & Production laitière</p>
        <p style="margin-top:.2rem;">Région de Tarmast — Maroc</p>
      </div>
    </div>
    <div class="invoice-meta">
      <div class="invoice-title">FACTURE RÉCAPITULATIVE</div>
      <div class="invoice-num"><?php echo htmlspecialchars($numFactureRecap); ?></div>
      <div class="invoice-date">Date d'émission : <?php echo date('d/m/Y'); ?></div>
      <div class="invoice-date"><strong>Période :</strong> <?php echo htmlspecialchars($titlePeriode); ?></div>
    </div>
  </div>

  <!-- DÉTAILS ÉMETTEUR & CLIENT -->
  <div class="invoice-details">
    <div class="box-detail">
      <p><strong>Ferme Tarmast S.A.R.L</strong></p>
      <p>Service Ventes & Cheptel</p>
      <p>Email : contact@tarmast.ma</p>
      <p>Tél : +212 5 22 00 00 00</p>
      <p>ICE : 001928374000089</p>
    </div>
    <div class="box-detail">
      <p><strong><?php echo htmlspecialchars($premier['acheteur_nom']); ?></strong></p>
      <p>Email : <?php echo htmlspecialchars($premier['acheteur_email']); ?></p>
      <p>Tél : <?php echo htmlspecialchars($premier['acheteur_tel'] ?? 'Non renseigné'); ?></p>
      <p>ICE : <?php echo htmlspecialchars($clientIce); ?></p>
      <p style="margin-top:.3rem; font-size:.82rem; color: var(--green-dark);">
        <strong>Nombre d'achats :</strong> <?php echo count($factures); ?> animal(aux)
      </p>
    </div>
  </div>

  <!-- TABLEAU RÉCAPITULATIF DES PRESTATIONS -->
  <table class="invoice-table">
    <thead>
      <tr>
        <th class="text-center">SÉRIE</th>
        <th>PRODUIT</th>
        <th>RACE</th>
        <th class="text-center">N° FACTURE</th>
        <th class="text-center">DATE</th>
        <th class="text-right">PRIX HT</th>
        <th class="text-center">TVA</th>
        <th class="text-right">MONTANT HT</th>
        <th class="text-right">MONTANT TTC</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($factures as $fact): 
        $ttc = (float)$fact['montant_ttc'];
        $ht = $ttc; // TVA 0%
      ?>
      <tr>
        <td class="text-center" style="font-weight:700; color:var(--forest); font-family:monospace; font-size:.85rem;">
          <?php echo 'BOV-' . str_pad((string)$fact['id_vache'], 4, '0', STR_PAD_LEFT); ?>
        </td>
        <td><?php echo htmlspecialchars(labelBovin($fact['bovin'])); ?></td>
        <td>
          <div class="item-name"><?php echo htmlspecialchars($fact['vache_nom']); ?></div>
        </td>
        <td class="text-center" style="font-size:.82rem; font-weight:600; color:var(--forest);">
          <?php echo htmlspecialchars($fact['numero_facture']); ?>
        </td>
        <td class="text-center" style="font-size:.82rem;">
          <?php echo date('d/m/Y', strtotime($fact['date_facture'])); ?>
        </td>
        <td class="text-right"><?php echo number_format($ht, 2, ',', ' '); ?></td>
        <td class="text-center">0%</td>
        <td class="text-right"><?php echo number_format($ht, 2, ',', ' '); ?></td>
        <td class="text-right" style="font-weight:700; color:var(--green-dark);"><?php echo number_format($ttc, 2, ',', ' '); ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <!-- SECTION TOTAUX ET MONTANT EN LETTRES -->
  <div class="totals-section">
    <div class="words-box">
      <h4>Montant total en toutes lettres</h4>
      <p>« Arrêtée la présente facture récapitulative à la somme de : <strong><?php echo htmlspecialchars($montantEnLettres); ?></strong>. »</p>
    </div>

    <table class="totals-table">
      <tr>
        <td class="lbl">Total HT :</td>
        <td class="val"><?php echo number_format($totalHT, 2, ',', ' '); ?> DH</td>
      </tr>
      <tr>
        <td class="lbl">TVA (0%) :</td>
        <td class="val">0,00 DH</td>
      </tr>
      <tr class="grand-total">
        <td class="lbl">Total TTC :</td>
        <td class="val"><?php echo number_format($totalTTC, 2, ',', ' '); ?> DH</td>
      </tr>
    </table>
  </div>

  <div class="legal-footer">
    Ferme Tarmast S.A.R.L — Vente de Bovins & Produits Laitiers — Patente : 3482910 — RC : 10293<br>
    Facture Récapitulative valant justificatif global de paiement — Merci pour votre confiance !
  </div>

</div>

</body>
</html>
