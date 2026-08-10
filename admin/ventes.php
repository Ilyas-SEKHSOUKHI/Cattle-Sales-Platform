<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

requireAdmin();

$adminNom = $_SESSION['nom'];
$adminId = (int) $_SESSION['user_id'];

// ---------- Filtres GET ----------
$filterDateDebut = trim($_GET['date_debut'] ?? '');
$filterDateFin   = trim($_GET['date_fin'] ?? '');
$filterAcheteur  = trim($_GET['acheteur'] ?? '');

// ---------- Construction SQL dynamique ----------
$sql = 'SELECT o.id, o.montant_propose AS montant, o.date_offre AS date,
               u.nom AS acheteur, u.email, u.telephone, u.id AS id_acheteur,
               v.nom AS vache, v.id AS id_vache
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

$ventesStmt = $pdo->prepare($sql);
$ventesStmt->execute($params);
$ventes = $ventesStmt->fetchAll(PDO::FETCH_ASSOC);

$revenuTotal = array_sum(array_column($ventes, 'montant'));
$nbVentes = count($ventes);
$panierMoyen = $nbVentes > 0 ? $revenuTotal / $nbVentes : 0;

// ---------- Liste des acheteurs (pour le filtre) ----------
$buyersStmt = $pdo->prepare(
    'SELECT DISTINCT u.id, u.nom
     FROM utilisateurs u
     JOIN offres o ON o.id_utilisateur = u.id
     JOIN vaches v ON o.id_vache = v.id
     WHERE o.statut = \'acceptee\' AND v.id_admin = :id_admin
     ORDER BY u.nom ASC'
);
$buyersStmt->execute([':id_admin' => $adminId]);
$acheteurs = $buyersStmt->fetchAll(PDO::FETCH_ASSOC);

// ---------- Construction de la query string pour l'export ----------
$exportParams = [];
if ($filterDateDebut !== '') $exportParams['date_debut'] = $filterDateDebut;
if ($filterDateFin !== '')   $exportParams['date_fin']   = $filterDateFin;
if ($filterAcheteur !== '')  $exportParams['acheteur']   = $filterAcheteur;
$exportQuery = http_build_query($exportParams);
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
<title>Ventes — Ferme Tarmast</title>
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
  ul{ list-style:none; }

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
    border-radius: 9px;
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
    margin-bottom: 2.4rem;
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
  }
  .stat-icon svg{ width:20px; height:20px; }
  .stat-icon.green{ background: rgba(255,255,255,.22); color: #fff; }
  .stat-icon.ochre{ background: rgba(255,255,255,.22); color: #fff; }
  .stat-icon.forest{ background: rgba(255,255,255,.22); color: #fff; }
  .stat-num{ font-family: var(--display); font-size: 1.35rem; font-weight:700; color: #fff; }
  .stat-lbl{ font-size:.78rem; color: #fff; opacity: .95; }

  /* ---------- PANEL / TABLE ---------- */
  .panel{
    background:#fff;
    border: 1px solid var(--line);
    border-radius: 16px;
    overflow:hidden;
  }
  .panel-head{
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding: 1.2rem 1.4rem;
    border-bottom: 1px solid var(--line);
    flex-wrap:wrap;
    gap: .8rem;
  }
  .panel-head h2{ font-size: 1.05rem; }
  .panel-head p{ font-size:.82rem; color: var(--ink-soft); margin-top:.15rem; }
  .panel-head a{
    font-size: .85rem;
    font-weight:700;
    color: var(--green-dark);
    background:#fff;
    border:1px solid var(--line);
    padding:.5rem .9rem;
    border-radius: 999px;
  }
  .panel-head a:hover{ background: var(--cream-2); }

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

  .vache-cell .name{ font-weight:700; color: var(--forest); }
  .buyer-cell .name{ font-weight:600; }
  .buyer-cell .contact{
    font-size:.78rem;
    color: var(--ink-soft);
    margin-top:.15rem;
    display:flex;
    flex-direction:column;
    gap:.1rem;
  }
  .buyer-cell .contact a{ color: var(--green-dark); font-weight:600; }
  .buyer-cell .contact a:hover{ text-decoration:underline; }
  .montant{ font-family: var(--display); font-weight:600; font-size: 1rem; color: var(--green-dark); }
  .montant-ht{ font-family: var(--display); font-weight:600; font-size: .95rem; color: var(--ink-soft); }

  .badge{
    display:inline-flex;
    align-items:center;
    gap:.35rem;
    padding: .28rem .65rem;
    border-radius: 999px;
    font-size: .76rem;
    font-weight: 700;
    background: rgba(76,175,80,.14);
    color: var(--green-dark);
  }

  .empty-state{
    padding: 2.6rem 1.4rem;
    text-align:center;
    color: var(--ink-soft);
    font-size: .92rem;
  }

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
  .filter-group input,
  .filter-group select{
    font-family: var(--body);
    font-size: .88rem;
    padding: .55rem .75rem;
    border: 1px solid var(--line);
    border-radius: 8px;
    background: var(--cream);
    color: var(--ink);
    transition: border-color .18s;
  }
  .filter-group input:focus,
  .filter-group select:focus{
    border-color: var(--green);
    outline: none;
  }
  .filter-actions{
    display:flex;
    gap: .5rem;
    align-items:flex-end;
    flex-shrink:0;
  }
  .btn-filter{
    font-family: var(--body);
    font-size: .85rem;
    font-weight: 600;
    padding: .55rem 1.1rem;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    transition: background .18s, transform .1s;
  }
  .btn-filter:active{ transform: scale(.97); }
  .btn-filter.primary{
    background: var(--green);
    color: #fff;
  }
  .btn-filter.primary:hover{ background: var(--green-dark); }
  .btn-filter.secondary{
    background: var(--cream-2);
    color: var(--ink);
    border: 1px solid var(--line);
  }
  .btn-filter.secondary:hover{ background: #EAE1CB; }
  .btn-export{
    display:inline-flex;
    align-items:center;
    gap:.45rem;
    font-family: var(--body);
    font-size: .85rem;
    font-weight: 700;
    padding: .55rem 1.1rem;
    border-radius: 8px;
    border: 1px solid var(--line);
    background: #fff;
    color: var(--forest);
    cursor: pointer;
    text-decoration: none;
    transition: background .18s, transform .1s;
  }
  .btn-export:hover{ background: var(--cream-2); }
  .btn-export:active{ transform: scale(.97); }
  .btn-export svg{ width: 16px; height: 16px; flex-shrink:0; }

  /* ---------- RESPONSIVE ---------- */
  @media (max-width: 980px){
    .layout{ grid-template-columns: 1fr; }
    .sidebar{ position: static; height: auto; flex-direction: row; align-items:center; overflow-x:auto; }
    .sidebar-brand{ border:none; padding:0; margin:0; margin-right: 1.2rem; }
    .sidebar-label{ display:none; }
    .sidebar-nav{ flex-direction:row; margin:0; }
    .sidebar-footer{ margin-left:auto; border:none; padding:0; flex-direction:row; align-items:center; }
    .sidebar-user{ padding:0; }
    .stats-grid{ grid-template-columns: repeat(3,1fr); }
    .filter-bar{ padding: 1rem; }
  }
  @media (max-width: 720px){
    .stats-grid{ grid-template-columns: 1fr 1fr; }
  }
  @media (max-width: 560px){
    .main{ padding: 1.6rem 1.1rem 2.4rem; }
    .stats-grid{ grid-template-columns: 1fr; }
    .filter-bar{ flex-direction: column; align-items: stretch; }
    .filter-group{ min-width: unset; }
    thead{ display:none; }
    table, tbody, tr, td{ display:block; width:100%; }
    tbody tr{ border-top: 1px solid var(--line); padding: .9rem 1.1rem; }
    tbody td{ border:none; padding: .3rem 0; }
    tbody td::before{ content: attr(data-label); display:block; font-size:.72rem; color:var(--ink-soft); text-transform:uppercase; letter-spacing:.04em; }
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
      <a href="ventes.php" class="active">
        <svg viewBox="0 0 24 24" fill="none"><path d="M3 3v18h18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M7 15l4-4 3 3 5-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Ventes
      </a>
      <a href="factures.php">
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
        <h1>Ventes</h1>
        <p class="sub">Historique des transactions conclues sur la ferme.</p>
      </div>
    </div>

    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-top">
          <span class="stat-icon forest">
            <svg viewBox="0 0 24 24" fill="none"><path d="M3 3v18h18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M7 15l4-4 3 3 5-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </span>
        </div>
        <div class="stat-num"><?php echo (int)$nbVentes; ?></div>
        <div class="stat-lbl">Ventes conclues</div>
      </div>

      <div class="stat-card">
        <div class="stat-top">
          <span class="stat-icon green">
            <svg viewBox="0 0 24 24" fill="none"><path d="M12 2v20M17 7c0-2.2-2.2-4-5-4s-5 1.8-5 4 2.2 3.2 5 4 5 1.8 5 4-2.2 4-5 4-5-1.8-5-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
          </span>
        </div>
        <div class="stat-num"><?php echo number_format($revenuTotal, 0, ',', ' '); ?> DH</div>
        <div class="stat-lbl">Revenu total</div>
      </div>

      <div class="stat-card">
        <div class="stat-top">
          <span class="stat-icon ochre">
            <svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/><path d="M8 12h8M12 8v8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
          </span>
        </div>
        <div class="stat-num"><?php echo number_format($panierMoyen, 0, ',', ' '); ?> DH</div>
        <div class="stat-lbl">Panier moyen</div>
      </div>
    </div>

    <!-- ================= FILTER BAR ================= -->
    <form class="filter-bar" method="GET" action="ventes.php" id="filter-form">
      <div class="filter-group">
        <label for="date_debut">Date début</label>
        <input type="date" id="date_debut" name="date_debut" value="<?php echo htmlspecialchars($filterDateDebut); ?>">
      </div>
      <div class="filter-group">
        <label for="date_fin">Date fin</label>
        <input type="date" id="date_fin" name="date_fin" value="<?php echo htmlspecialchars($filterDateFin); ?>">
      </div>
      <div class="filter-group">
        <label for="acheteur">Acheteur</label>
        <select id="acheteur" name="acheteur">
          <option value="">Tous les acheteurs</option>
          <?php foreach ($acheteurs as $ach): ?>
            <option value="<?php echo (int)$ach['id']; ?>" <?php echo ($filterAcheteur == $ach['id']) ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($ach['nom']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="filter-actions">
        <button type="submit" class="btn-filter primary">Filtrer</button>
        <a href="ventes.php" class="btn-filter secondary">Réinitialiser</a>
      </div>
      <div class="filter-actions">
        <a href="export_ventes_excel.php<?php echo $exportQuery ? '?' . htmlspecialchars($exportQuery) : ''; ?>" class="btn-export">
          <svg viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 2v6h6M8 13h8M8 17h8M8 9h2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
          Télécharger Excel
        </a>
      </div>
    </form>

    <div class="panel">
      <div class="panel-head">
        <div>
          <h2>Historique des ventes</h2>
          <p>Offres acceptées, du plus récent au plus ancien<?php
            $filterInfo = [];
            if ($filterDateDebut) $filterInfo[] = 'depuis le ' . date('d/m/Y', strtotime($filterDateDebut));
            if ($filterDateFin) $filterInfo[] = 'jusqu\'au ' . date('d/m/Y', strtotime($filterDateFin));
            if ($filterAcheteur) {
              foreach ($acheteurs as $a) {
                if ($a['id'] == $filterAcheteur) { $filterInfo[] = 'acheteur : ' . htmlspecialchars($a['nom']); break; }
              }
            }
            if ($filterInfo) echo ' — ' . implode(', ', $filterInfo);
          ?></p>
        </div>
        <a href="offres.php">Voir les offres en attente →</a>
      </div>

      <?php if (!empty($ventes)): ?>
      <table>
        <thead>
          <tr>
            <th>Vache</th>
            <th>Acheteur</th>
            <th>Montant HT</th>
            <th>Montant TTC</th>
            <th>Date</th>
            <th>Statut</th>
            <th style="text-align:right;">Facture</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($ventes as $vente):
            $montantTTC = (float) $vente['montant'];
            $montantHT  = $montantTTC / 1.20;
          ?>
          <tr>
            <td data-label="Vache">
              <div class="vache-cell">
                <div class="name"><?php echo htmlspecialchars($vente['vache']); ?></div>
              </div>
            </td>
            <td data-label="Acheteur">
              <div class="buyer-cell">
                <div class="name"><?php echo htmlspecialchars($vente['acheteur']); ?></div>
                <div class="contact">
                  <a href="mailto:<?php echo htmlspecialchars($vente['email']); ?>"><?php echo htmlspecialchars($vente['email']); ?></a>
                  <?php if (!empty($vente['telephone'])): ?>
                    <a href="tel:<?php echo htmlspecialchars(telephoneDigits($vente['telephone'])); ?>"><?php echo htmlspecialchars($vente['telephone']); ?></a>
                  <?php else: ?>
                    <span>Téléphone non renseigné</span>
                  <?php endif; ?>
                </div>
              </div>
            </td>
            <td data-label="Montant HT"><span class="montant-ht"><?php echo number_format($montantHT, 2, ',', ' '); ?> DH</span></td>
            <td data-label="Montant TTC"><span class="montant"><?php echo number_format($montantTTC, 2, ',', ' '); ?> DH</span></td>
            <td data-label="Date"><?php echo date('d/m/Y H:i', strtotime($vente['date'])); ?></td>
            <td data-label="Statut"><span class="badge">Vendue</span></td>
            <td data-label="Facture" style="text-align:right;">
              <a href="voir_facture.php?offre=<?php echo (int)$vente['id']; ?>" style="display:inline-flex; align-items:center; gap:.35rem; padding:.35rem .75rem; border-radius:6px; font-size:.8rem; font-weight:600; background:var(--cream-2); border:1px solid var(--line); color:var(--forest);">
                📄 Voir Facture
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?>
        <div class="empty-state">Aucune vente conclue pour le moment.</div>
      <?php endif; ?>
    </div>

  </main>
</div>

</body>
</html>