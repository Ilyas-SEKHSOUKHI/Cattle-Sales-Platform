<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

requireAdmin();

$idFacture = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$idOffre   = filter_input(INPUT_GET, 'offre', FILTER_VALIDATE_INT);

ensureColumnExists($pdo, 'utilisateurs', 'ice', "VARCHAR(50) NULL DEFAULT ''");
ensureFacturesTableExists($pdo);
syncAllFactures($pdo);

$facture = null;

if ($idFacture) {
    $stmt = $pdo->prepare(
        "SELECT f.*, 
                u.nom AS acheteur_nom, u.email AS acheteur_email, u.telephone AS acheteur_tel, u.ice AS acheteur_ice,
                v.id AS id_vache, v.nom AS vache_nom, v.bovin, v.poids, v.description,
                o.date_reprise, o.date_offre
         FROM factures f
         JOIN utilisateurs u ON f.id_utilisateur = u.id
         JOIN vaches v ON f.id_vache = v.id
         JOIN offres o ON f.id_offre = o.id
         WHERE f.id = :id AND v.id_admin = :id_admin"
    );
    $stmt->execute([':id' => $idFacture, ':id_admin' => $_SESSION['user_id']]);
    $facture = $stmt->fetch(PDO::FETCH_ASSOC);
} elseif ($idOffre) {
    $stmt = $pdo->prepare(
        "SELECT f.*, 
                u.nom AS acheteur_nom, u.email AS acheteur_email, u.telephone AS acheteur_tel, u.ice AS acheteur_ice,
                v.id AS id_vache, v.nom AS vache_nom, v.bovin, v.poids, v.description,
                o.date_reprise, o.date_offre
         FROM factures f
         JOIN utilisateurs u ON f.id_utilisateur = u.id
         JOIN vaches v ON f.id_vache = v.id
         JOIN offres o ON f.id_offre = o.id
         WHERE f.id_offre = :id_offre AND v.id_admin = :id_admin"
    );
    $stmt->execute([':id_offre' => $idOffre, ':id_admin' => $_SESSION['user_id']]);
    $facture = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$facture) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Facture introuvable.'];
    redirect('factures.php');
}

$montantTTC = (float) $facture['montant_ttc'];
$montantHT  = $montantTTC; // TVA 0%
$tvaMontant = 0;
$montantEnLettres = nombreEnLettres($montantTTC);
$typeBovinLabel = labelBovin($facture['bovin']);

// ICE client : s'il est vide, générer un numéro ICE à 15 chiffres réaliste et cohérent
$clientIce = !empty($facture['acheteur_ice'])
    ? $facture['acheteur_ice']
    : '00' . sprintf('%013d', abs(crc32('client_ice_' . $facture['id_utilisateur'])) % 10000000000000);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Work+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="icon" type="image/png" href="../assets/images/iconVache.png">
<title>Facture <?php echo htmlspecialchars($facture['numero_facture']); ?> — Ferme Tarmast</title>
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
    --body: "Work Sans", Arial, Helvetica, sans-serif;
  }

  * { margin:0; padding:0; box-sizing:border-box; }

  body {
    font-family: var(--body);
    background: var(--cream);
    color: var(--ink);
    line-height: 1.5;
    padding: 2rem 1rem;
  }

  /* BARRE D'ACTIONS FLOOTANTE EN HAUT */
  .action-bar {
    max-width: 800px;
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

  /* FEUILLE FACTURE */
  .invoice-card {
    max-width: 800px;
    margin: 0 auto;
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 12px;
    padding: 2.5rem 3rem;
    box-shadow: 0 8px 24px rgba(0,0,0,.06);
  }

  /* EN-TÊTE FACTURE */
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
    font-size: 1.6rem;
    color: var(--forest);
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }
  .invoice-num {
    font-size: 1rem;
    font-weight: 700;
    color: var(--green-dark);
    margin-top: .2rem;
  }
  .invoice-date {
    font-size: .84rem;
    color: var(--ink-soft);
    margin-top: .2rem;
  }

  /* BLOCS ADRESSES */
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
  .box-detail h3 {
    font-family: var(--display);
    font-size: .9rem;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: var(--forest);
    margin-bottom: .6rem;
    border-bottom: 1px dashed var(--line);
    padding-bottom: .4rem;
  }
  .box-detail p {
    font-size: .88rem;
    color: var(--ink);
    line-height: 1.6;
  }
  .box-detail strong {
    color: var(--forest);
  }

  /* TABLEAU PRESTATIONS */
  .invoice-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 2rem;
  }
  .invoice-table th {
    background: var(--forest);
    color: #fff;
    text-align: left;
    padding: .75rem 1rem;
    font-size: .8rem;
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
    padding: .9rem 1rem;
    border-bottom: 1px solid var(--line);
    font-size: .9rem;
  }
  .invoice-table tbody tr:nth-child(even) {
    background: var(--cream);
  }

  .item-name {
    font-weight: 700;
    color: var(--forest);
  }
  .item-desc {
    font-size: .8rem;
    color: var(--ink-soft);
  }

  /* TOTAUX ET MENTION EN LETTRES */
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
    font-size: .98rem;
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

  /* IMPRESSION CLEAN */
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
      <div class="invoice-title">FACTURE</div>
      <div class="invoice-num"><?php echo htmlspecialchars($facture['numero_facture']); ?></div>
      <div class="invoice-date">Date : <?php echo date('d/m/Y', strtotime($facture['date_facture'])); ?></div>
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
      <p><strong><?php echo htmlspecialchars($facture['acheteur_nom']); ?></strong></p>
      <p>Email : <?php echo htmlspecialchars($facture['acheteur_email']); ?></p>
      <p>Tél : <?php echo htmlspecialchars($facture['acheteur_tel'] ?? 'Non renseigné'); ?></p>
      <p>ICE : <?php echo htmlspecialchars($clientIce); ?></p>
      <?php if (!empty($facture['date_reprise'])): ?>
        <p style="margin-top:.3rem; font-size:.82rem; color: var(--green-dark);">
          <strong>Date de récupération :</strong> <?php echo date('d/m/Y', strtotime($facture['date_reprise'])); ?>
        </p>
      <?php endif; ?>
    </div>
  </div>

  <!-- TABLEAU DE FACTURATION -->
  <table class="invoice-table">
    <thead>
      <tr>
        <th class="text-center">SÉRIE</th>
        <th>PRODUIT</th>
        <th>RACE</th>
        <th class="text-center">QUANTITÉ</th>
        <th class="text-right">PRIX HT</th>
        <th class="text-center">TVA</th>
        <th class="text-right">MONTANT HT</th>
        <th class="text-right">MONTANT TTC</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td class="text-center" style="font-weight:700; color:var(--forest); font-size:.85rem;">
          <?php echo 'BOV-' . str_pad((string)$facture['id_vache'], 4, '0', STR_PAD_LEFT); ?>
        </td>
        <td><?php echo htmlspecialchars($typeBovinLabel); ?></td>
        <td>
          <div class="item-name"><?php echo htmlspecialchars($facture['vache_nom']); ?></div>
        </td>
        <td class="text-center">1</td>
        <td class="text-right"><?php echo number_format($montantHT, 2, ',', ' '); ?></td>
        <td class="text-center">0%</td>
        <td class="text-right"><?php echo number_format($montantHT, 2, ',', ' '); ?></td>
        <td class="text-right" style="font-weight:700; color:var(--green-dark);"><?php echo number_format($montantTTC, 2, ',', ' '); ?></td>
      </tr>
    </tbody>
  </table>

  <!-- SECTION TOTAUX ET MONTANT EN LETTRES -->
  <div class="totals-section">
    <div class="words-box">
      <h4>Montant total en toutes lettres</h4>
      <p>« Arrêtée la présente facture à la somme de : <strong><?php echo htmlspecialchars($montantEnLettres); ?></strong>. »</p>
    </div>

    <table class="totals-table">
      <tr>
        <td class="lbl">Total HT :</td>
        <td class="val"><?php echo number_format($montantHT, 2, ',', ' '); ?> DH</td>
      </tr>
      <tr>
        <td class="lbl">TVA (0%) :</td>
        <td class="val">0,00 DH</td>
      </tr>
      <tr class="grand-total">
        <td class="lbl">Total TTC :</td>
        <td class="val"><?php echo number_format($montantTTC, 2, ',', ' '); ?> DH</td>
      </tr>
    </table>
  </div>

  <div class="legal-footer">
    Ferme Tarmast S.A.R.L — Vente de Bovins & Produits Laitiers — Patente : 3482910 — RC : 10293<br>
    Merci pour votre confiance !
  </div>

</div>

</body>
</html>
