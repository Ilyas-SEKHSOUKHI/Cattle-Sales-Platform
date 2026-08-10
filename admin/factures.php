<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

requireAdmin();

$adminNom = $_SESSION['nom'];
$adminId = (int) $_SESSION['user_id'];

// Assurer la création de la table et la rétro-synchro de toutes les factures
ensureFacturesTableExists($pdo);
syncAllFactures($pdo);

// ---------- Filtres GET ----------
$search          = trim($_GET['search'] ?? '');
$filterDateDebut = trim($_GET['date_debut'] ?? '');
$filterDateFin   = trim($_GET['date_fin'] ?? '');

// ---------- Requete SQL Factures ----------
$sql = "SELECT f.*,
               u.nom AS acheteur_nom, u.email AS acheteur_email, u.telephone AS acheteur_tel,
               v.nom AS vache_nom, v.bovin,
               o.date_reprise
        FROM factures f
        JOIN utilisateurs u ON f.id_utilisateur = u.id
        JOIN vaches v ON f.id_vache = v.id
        JOIN offres o ON f.id_offre = o.id
        WHERE v.id_admin = :id_admin";

$params = [':id_admin' => $adminId];

if ($search !== '') {
    $sql .= " AND (f.numero_facture LIKE :search OR u.nom LIKE :search OR v.nom LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}
if ($filterDateDebut !== '') {
    $sql .= " AND DATE(f.date_facture) >= :date_debut";
    $params[':date_debut'] = $filterDateDebut;
}
if ($filterDateFin !== '') {
    $sql .= " AND DATE(f.date_facture) <= :date_fin";
    $params[':date_fin'] = $filterDateFin;
}

$sql .= " ORDER BY f.date_facture DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$factures = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalFacturesCount = count($factures);
$totalMontantTTC   = array_sum(array_column($factures, 'montant_ttc'));
$totalMontantHT    = array_sum(array_column($factures, 'montant_ht'));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;500;600;700&family=Work+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="icon" type="image/png" href="../assets/images/iconVache.png">
<title>Historique des Factures — Ferme Tarmast</title>
<style>
  :root{
    --forest: #1B3A2B;
    --forest-2: #142A20;
    --cream: #FBF6EC;
    --cream-2: #F2EAD8;
    --green: #4CAF50;
    --green-dark: #3d9140;
    --ochre: #C9902F;
    --rust: #A6512E;
    --ink: #2A2A25;
    --ink-soft: #5C5B52;
    --line: #E3D9C2;
    --display: "Fraunces", Georgia, serif;
    --body: "Work Sans", Arial, Helvetica, sans-serif;
  }

  *{ margin:0; padding:0; box-sizing:border-box; }

  body{
    font-family: var(--body);
    background: var(--cream);
    color: var(--ink);
    line-height: 1.5;
  }

  a{ color:inherit; text-decoration:none; }

  h1,h2,h3{ font-family: var(--display); color: var(--forest); font-weight:600; }

  :focus-visible{ outline: 3px solid var(--ochre); outline-offset: 2px; }

  .layout{
    display:grid;
    grid-template-columns: 250px 1fr;
    min-height: 100vh;
  }

  /* ---------- SIDEBAR ---------- */
  .sidebar{
    background: var(--forest-2);
    color: #C7D6CB;
    display:flex;
    flex-direction:column;
    padding: 1.6rem 1.2rem;
    position: sticky;
    top:0;
    height: 100vh;
  }
  .sidebar-brand{
    display:flex;
    align-items:center;
    gap:.6rem;
    font-family: var(--display);
    font-size: 1.15rem;
    font-weight:700;
    color:#fff;
    padding: .4rem .3rem 1.6rem;
    border-bottom: 1px solid rgba(255,255,255,.08);
    margin-bottom: 1.4rem;
  }
  .sidebar-brand svg{ width:32px; height:32px; flex-shrink:0; }

  .sidebar-label{
    font-size: .72rem;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: #7F9585;
    font-weight:700;
    padding: 0 .5rem;
    margin-bottom: .6rem;
  }

  .sidebar-nav{ display:flex; flex-direction:column; gap:.25rem; margin-bottom: 1.6rem; }
  .sidebar-nav a{
    display:flex;
    align-items:center;
    gap: .75rem;
    padding: .68rem .8rem;
    border-radius: 999px;
    font-size: .92rem;
    font-weight: 500;
    color: #C7D6CB;
    transition: background .18s, color .18s;
  }
  .sidebar-nav a svg{ width:18px; height:18px; flex-shrink:0; opacity:.85; }
  .sidebar-nav a:hover{ background: rgba(255,255,255,.06); color:#fff; }
  .sidebar-nav a.active{
    background: var(--green);
    color:#fff;
  }
  .sidebar-nav a.active svg{ opacity:1; }

  .sidebar-footer{
    margin-top:auto;
    border-top: 1px solid rgba(255,255,255,.08);
    padding-top: 1.1rem;
    display:flex;
    flex-direction:column;
    gap:.3rem;
  }
  .sidebar-user{
    display:flex;
    align-items:center;
    gap:.7rem;
    padding: .4rem .5rem .9rem;
  }
  .sidebar-avatar{
    width:34px; height:34px;
    border-radius:50%;
    background: var(--green);
    color:#fff;
    display:flex; align-items:center; justify-content:center;
    font-weight:700;
    font-size:.85rem;
    flex-shrink:0;
  }
  .sidebar-user .who{ display:flex; flex-direction:column; }
  .sidebar-user .who strong{ color:#fff; font-size:.88rem; }
  .sidebar-user .who span{ font-size:.74rem; color:#8FA595; }
  .sidebar-nav a.logout{ color:#E3B6A8; }
  .sidebar-nav a.logout:hover{ background: rgba(166,81,46,.16); color:#fff; }

  /* ---------- MAIN ---------- */
  .main{ padding: 2.2rem 2.4rem 3rem; }

  .topbar{
    display:flex;
    align-items:center;
    justify-content:space-between;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
  }
  .topbar h1{ font-size: 1.6rem; }
  .topbar .sub{ color: var(--ink-soft); font-size: .92rem; margin-top:.2rem; }

  /* ---------- STAT CARDS ---------- */
  .stats-grid{
    display:grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.2rem;
    margin-bottom: 2rem;
  }
  .stat-card{
    background: linear-gradient(135deg, #3f8f42 0%, #173425 100%);
    border:1px solid var(--line);
    border-radius: 14px;
    padding: 1rem 1rem;
    display:flex;
    flex-direction:column;
    gap: .55rem;
    box-shadow: 0 6px 14px rgba(0,0,0,.12);
  }
  .stat-top{ display:flex; align-items:center; justify-content:space-between; }
  .stat-icon{
    width: 34px; height:34px;
    border-radius: 8px;
    display:flex; align-items:center; justify-content:center;
    flex-shrink:0;
    background: rgba(255,255,255,.22); color: #fff;
  }
  .stat-icon svg{ width:20px; height:20px; }
  .stat-num{ font-family: var(--display); font-size: 1.35rem; font-weight:700; color: #fff; }
  .stat-lbl{ font-size:.78rem; color: #fff; opacity: .95; }

  /* ---------- FILTER BAR ---------- */
  .filter-bar{
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 14px;
    padding: 1.2rem 1.4rem;
    margin-bottom: 1.4rem;
    display:flex;
    align-items:flex-end;
    gap: 1rem;
    flex-wrap: wrap;
  }
  .filter-group{
    display:flex;
    flex-direction:column;
    gap:.3rem;
    flex: 1;
    min-width: 160px;
  }
  .filter-group label{
    font-size: .76rem;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: var(--ink-soft);
    font-weight: 700;
  }
  .filter-group input{
    font-family: var(--body);
    font-size: .88rem;
    padding: .55rem .75rem;
    border: 1px solid var(--line);
    border-radius: 8px;
    background: var(--cream);
    color: var(--ink);
  }
  .filter-actions{
    display:flex;
    gap: .5rem;
    align-items:flex-end;
  }
  .btn-filter{
    font-family: var(--body);
    font-size: .85rem;
    font-weight: 600;
    padding: .55rem 1.1rem;
    border-radius: 8px;
    border: none;
    cursor: pointer;
  }
  .btn-filter.primary{ background: var(--green); color: #fff; }
  .btn-filter.secondary{ background: var(--cream-2); color: var(--ink); border: 1px solid var(--line); }

  /* ---------- PANEL & TABLE ---------- */
  .panel{
    background:#fff;
    border: 1px solid var(--line);
    border-radius: 16px;
    overflow:hidden;
  }
  .panel-head{
    padding: 1.2rem 1.4rem;
    border-bottom: 1px solid var(--line);
  }
  .panel-head h2{ font-size: 1.05rem; }
  .panel-head p{ font-size:.82rem; color: var(--ink-soft); margin-top:.15rem; }

  table{ width:100%; border-collapse: collapse; }
  thead th{
    text-align:left;
    font-size: .74rem;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: var(--ink-soft);
    padding: .8rem 1.4rem;
    background: var(--cream-2);
    font-weight:700;
  }
  tbody td{
    padding: .95rem 1.4rem;
    border-top: 1px solid var(--line);
    font-size: .9rem;
    vertical-align: middle;
  }
  tbody tr:hover{ background: rgba(76,175,80,.04); }

  .num-facture{
    font-family: var(--display);
    font-weight: 700;
    color: var(--forest);
    display: flex;
    align-items: center;
    gap: .4rem;
  }
  .num-facture svg{ width: 18px; height: 18px; color: var(--green-dark); }

  .badge-payee{
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    padding: .25rem .6rem;
    border-radius: 999px;
    font-size: .75rem;
    font-weight: 700;
    background: rgba(76,175,80,.15);
    color: var(--green-dark);
  }

  .btn-action {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .4rem .8rem;
    border-radius: 6px;
    font-size: .82rem;
    font-weight: 600;
    background: var(--cream-2);
    color: var(--forest);
    border: 1px solid var(--line);
    transition: background .18s;
  }
  .btn-action:hover { background: #EAE1CB; }
  .btn-action svg { width: 14px; height: 14px; }

  .empty-state{
    padding: 3rem 1.4rem;
    text-align:center;
    color: var(--ink-soft);
    font-size: .92rem;
  }
</style>
</head>
<body>

<div class="layout">

  <!-- ================= SIDEBAR ================= -->
  <aside class="sidebar">
    <a href="../index.php" class="sidebar-brand">
      <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="24" cy="24" r="23" fill="#4CAF50" opacity="0.18"/>
        <path d="M14 22c0-2 1.5-3.5 3.5-3.5 1 0 1.8.4 2.5 1 1-1.3 2.5-2 4-2s3 .7 4 2c.7-.6 1.5-1 2.5-1 2 0 3.5 1.5 3.5 3.5 0 1-.4 2-1 2.6.6.5 1 1.3 1 2.2 0 2-1.7 3.7-3.7 3.7H18.7C16.7 30.5 15 28.8 15 26.8c0-.9.4-1.7 1-2.2-.6-.6-1-1.6-1-2.6z" fill="#fff"/>
      </svg>
      Ferme Tarmast
    </a>

    <span class="sidebar-label">Gestion</span>
    <nav class="sidebar-nav">
      <a href="dashboard.php">
        <svg viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="8" height="8" rx="1.5" stroke="currentColor" stroke-width="1.8"/><rect x="13" y="3" width="8" height="5" rx="1.5" stroke="currentColor" stroke-width="1.8"/><rect x="13" y="12" width="8" height="9" rx="1.5" stroke="currentColor" stroke-width="1.8"/><rect x="3" y="15" width="8" height="6" rx="1.5" stroke="currentColor" stroke-width="1.8"/></svg>
        Tableau de bord
      </a>
      <a href="liste_vaches.php">
        <svg viewBox="0 0 24 24" fill="none"><path d="M4 20c0-4.4 3.6-8 8-8s8 3.6 8 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="1.8"/></svg>
        Le cheptel
      </a>
      <a href="ajouter_vache.php">
        <svg viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
        Ajouter une vache
      </a>
      <a href="offres.php">
        <svg viewBox="0 0 24 24" fill="none"><path d="M3 12l3-8h12l3 8-9 9-9-9z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>
        Offres
      </a>
      <a href="ventes.php">
        <svg viewBox="0 0 24 24" fill="none"><path d="M3 3v18h18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M7 15l4-4 3 3 5-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Ventes
      </a>
      <a href="factures.php" class="active">
        <svg viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Factures
      </a>
    </nav>

    <div class="sidebar-footer">
      <div class="sidebar-user">
        <div class="sidebar-avatar"><?php echo strtoupper(substr($adminNom, 0, 1)); ?></div>
        <div class="who">
          <strong><?php echo htmlspecialchars($adminNom); ?></strong>
          <span>Administrateur</span>
        </div>
      </div>
      <nav class="sidebar-nav">
        <a href="../actions/logout.php" class="logout">
          <svg viewBox="0 0 24 24" fill="none"><path d="M15 17l5-5-5-5M20 12H9M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
          Déconnexion
        </a>
      </nav>
    </div>
  </aside>

  <!-- ================= MAIN ================= -->
  <main class="main">
    <div class="topbar">
      <div>
        <h1>Historique des Factures</h1>
        <p class="sub">Consultez, recherchez et imprimez l'ensemble des factures d'achat émises.</p>
      </div>
    </div>

    <!-- STATS -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-top">
          <span class="stat-icon">
            <svg viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6z" stroke="currentColor" stroke-width="1.8"/></svg>
          </span>
        </div>
        <div class="stat-num"><?php echo $totalFacturesCount; ?></div>
        <div class="stat-lbl">Factures émises</div>
      </div>

      <div class="stat-card">
        <div class="stat-top">
          <span class="stat-icon">
            <svg viewBox="0 0 24 24" fill="none"><path d="M12 2v20M17 7c0-2.2-2.2-4-5-4s-5 1.8-5 4 2.2 3.2 5 4 5 1.8 5 4-2.2 4-5 4-5-1.8-5-4" stroke="currentColor" stroke-width="1.8"/></svg>
          </span>
        </div>
        <div class="stat-num"><?php echo number_format($totalMontantHT, 2, ',', ' '); ?> DH</div>
        <div class="stat-lbl">Total HT</div>
      </div>

      <div class="stat-card">
        <div class="stat-top">
          <span class="stat-icon">
            <svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/><path d="M8 12h8M12 8v8" stroke="currentColor" stroke-width="1.8"/></svg>
          </span>
        </div>
        <div class="stat-num"><?php echo number_format($totalMontantTTC, 2, ',', ' '); ?> DH</div>
        <div class="stat-lbl">Total TTC encaissement</div>
      </div>
    </div>

    <!-- FILTRES -->
    <form class="filter-bar" method="GET" action="factures.php">
      <div class="filter-group">
        <label for="search">Rechercher</label>
        <input type="text" id="search" name="search" placeholder="N° Facture, Acheteur, Bovin..." value="<?php echo htmlspecialchars($search); ?>">
      </div>
      <div class="filter-group">
        <label for="date_debut">Date début</label>
        <input type="date" id="date_debut" name="date_debut" value="<?php echo htmlspecialchars($filterDateDebut); ?>">
      </div>
      <div class="filter-group">
        <label for="date_fin">Date fin</label>
        <input type="date" id="date_fin" name="date_fin" value="<?php echo htmlspecialchars($filterDateFin); ?>">
      </div>
      <div class="filter-actions">
        <button type="submit" class="btn-filter primary">Filtrer</button>
        <a href="factures.php" class="btn-filter secondary">Réinitialiser</a>
      </div>
    </form>

    <!-- LISTE FACTURES -->
    <div class="panel">
      <div class="panel-head">
        <h2>Registre complet des factures</h2>
        <p>Toutes les factures sont archivées et conservées indéfiniment.</p>
      </div>

      <?php if (!empty($factures)): ?>
      <table>
        <thead>
          <tr>
            <th>N° Facture</th>
            <th>Acheteur</th>
            <th>Produit (Bovin)</th>
            <th>Montant HT</th>
            <th>Montant TTC</th>
            <th>Date Facture</th>
            <th>Statut</th>
            <th style="text-align:right;">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($factures as $fact): ?>
          <tr>
            <td>
              <div class="num-facture">
                <svg viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6z" stroke="currentColor" stroke-width="1.8"/><path d="M14 2v6h6" stroke="currentColor" stroke-width="1.8"/></svg>
                <?php echo htmlspecialchars($fact['numero_facture']); ?>
              </div>
            </td>
            <td>
              <strong><?php echo htmlspecialchars($fact['acheteur_nom']); ?></strong><br>
              <span style="font-size:.78rem; color:var(--ink-soft);"><?php echo htmlspecialchars($fact['acheteur_email']); ?></span>
            </td>
            <td>
              <strong><?php echo htmlspecialchars($fact['vache_nom']); ?></strong>
              <span style="font-size:.78rem; color:var(--ink-soft);">(<?php echo htmlspecialchars(labelBovin($fact['bovin'])); ?>)</span>
            </td>
            <td style="font-weight:600; color:var(--ink-soft);"><?php echo number_format((float)$fact['montant_ht'], 2, ',', ' '); ?> DH</td>
            <td style="font-weight:700; color:var(--green-dark);"><?php echo number_format((float)$fact['montant_ttc'], 2, ',', ' '); ?> DH</td>
            <td><?php echo date('d/m/Y H:i', strtotime($fact['date_facture'])); ?></td>
            <td><span class="badge-payee">Payée</span></td>
            <td style="text-align:right;">
              <a href="voir_facture.php?id=<?php echo (int)$fact['id']; ?>" class="btn-action">
                <svg viewBox="0 0 24 24" fill="none"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/></svg>
                Voir / Imprimer
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?>
        <div class="empty-state">Aucune facture trouvée.</div>
      <?php endif; ?>
    </div>

  </main>
</div>

</body>
</html>
