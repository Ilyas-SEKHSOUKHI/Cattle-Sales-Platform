<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

requireAdmin();

$adminNom = $_SESSION['nom'];
$adminId = (int) $_SESSION['user_id'];

$filtre = $_GET['filtre'] ?? 'tous';
$statutsOffres = ['en_attente', 'acceptee', 'refusee'];

if (!in_array($filtre, array_merge(['tous'], $statutsOffres), true)) {
    $filtre = 'tous';
}

$hasDateReprise = tableHasColumn($pdo, 'offres', 'date_reprise');

$sql = 'SELECT o.id, o.montant_propose AS montant, o.date_offre AS date, o.statut,
               u.nom AS acheteur, u.email, u.telephone,
               v.id AS id_vache, v.nom AS vache, v.bovin, v.date_naissance, v.age AS vache_age,
               v.poids, v.description AS vache_description, v.image AS vache_image, v.statut AS vache_statut';

if ($hasDateReprise) {
    $sql .= ', o.date_reprise';
} else {
    $sql .= ', NULL AS date_reprise';
}

$sql .= ' FROM offres o
        JOIN utilisateurs u ON o.id_utilisateur = u.id
        JOIN vaches v ON o.id_vache = v.id
        WHERE v.id_admin = :id_admin';

if ($filtre !== 'tous') {
    $sql .= ' AND o.statut = :statut';
}

$sql .= ' ORDER BY o.date_offre DESC';

$offresStmt = $pdo->prepare($sql);
$params = [':id_admin' => $adminId];

if ($filtre !== 'tous') {
    $params[':statut'] = $filtre;
}

$offresStmt->execute($params);
$offres = $offresStmt->fetchAll(PDO::FETCH_ASSOC);
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
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
<title>Offres — Ferme Tarmast</title>
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

  .topbar-stats{ display:flex; gap:.6rem; }
  .mini-stat{
    background:#fff;
    border: 1px solid var(--line);
    border-radius: 12px;
    padding: .55rem 1rem;
    text-align:center;
    min-width: 92px;
  }
  .mini-stat b{ display:block; font-family: var(--display); font-size: 1.1rem; color: var(--forest); }
  .mini-stat span{ font-size:.68rem; color: var(--ink-soft); text-transform:uppercase; letter-spacing:.04em; }

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

  .filters{ display:flex; gap:.5rem; }
  .filter-chip{
    font-size: .8rem;
    font-weight:700;
    color: var(--ink-soft);
    background: var(--cream-2);
    border: 1px solid var(--line);
    padding: .4rem .85rem;
    border-radius: 999px;
  }
  .filter-chip:hover{ background:#EADFC4; }
  .filter-chip.active{ background: var(--forest); color:#fff; border-color: var(--forest); }

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

  .vache-cell{ display:flex; align-items:center; gap:.7rem; }
  .vache-thumb{
    width:40px; height:40px;
    border-radius:10px;
    background: rgba(76,175,80,.1);
    display:flex; align-items:center; justify-content:center;
    flex-shrink:0;
    overflow:hidden;
  }
  .vache-thumb img{ width:100%; height:100%; object-fit:cover; }
  .vache-thumb svg{ width:20px; height:20px; color: var(--green-dark); }
  .vache-cell .name{ font-weight:700; color: var(--forest); }
  .vache-cell .sub{ font-size:.78rem; color: var(--ink-soft); }
  .buyer-cell .name{ font-weight:600; color: var(--forest); }
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
  .montant{ font-family: var(--display); font-weight:600; font-size: 1rem; color: var(--ink); }

  /* ---------- FICHE VACHE MODAL ---------- */
  .fiche-overlay{
    position:fixed; inset:0;
    background: rgba(20,42,32,.55);
    display:grid; place-items:center;
    padding:1rem; z-index:250;
    animation: fadeIn .2s ease;
  }
  .fiche-overlay[hidden]{ display:none; }
  @keyframes fadeIn{ from{ opacity:0; } to{ opacity:1; } }
  @keyframes slideUp{ from{ transform:translateY(24px); opacity:0; } to{ transform:translateY(0); opacity:1; } }
  .fiche-dialog{
    width: min(520px, 100%);
    max-height: 90vh;
    overflow-y:auto;
    background:#fff;
    border-radius:18px;
    border:1px solid var(--line);
    box-shadow: 0 22px 48px rgba(20,42,32,.25);
    animation: slideUp .25s ease;
  }
  .fiche-hero{
    position:relative;
    height:200px;
    background: linear-gradient(135deg, #3f8f42 0%, #173425 100%);
    border-radius:18px 18px 0 0;
    overflow:hidden;
    display:flex; align-items:center; justify-content:center;
  }
  .fiche-hero img{ width:100%; height:100%; object-fit:cover; }
  .fiche-hero .no-img{ color:rgba(255,255,255,.3); }
  .fiche-hero .no-img svg{ width:64px; height:64px; }
  .fiche-close{
    position:absolute; top:.7rem; right:.7rem;
    width:34px; height:34px;
    border-radius:50%;
    background: rgba(0,0,0,.35);
    backdrop-filter:blur(4px);
    border:none; cursor:pointer;
    display:flex; align-items:center; justify-content:center;
    color:#fff;
    transition: background .18s;
  }
  .fiche-close:hover{ background: rgba(0,0,0,.55); }
  .fiche-close svg{ width:18px; height:18px; }
  .fiche-badge-status{
    position:absolute; bottom:.7rem; left:.7rem;
    padding:.3rem .75rem;
    border-radius:999px;
    font-size:.74rem;
    font-weight:700;
    backdrop-filter:blur(6px);
  }
  .fiche-badge-status.disponible{ background:rgba(76,175,80,.85); color:#fff; }
  .fiche-badge-status.vendue{ background:rgba(201,144,47,.85); color:#fff; }
  .fiche-body{ padding:1.4rem 1.5rem 1.6rem; }
  .fiche-title{
    font-family: var(--display);
    font-size:1.25rem;
    font-weight:700;
    color: var(--forest);
    margin-bottom:.15rem;
  }
  .fiche-type{
    font-size:.82rem;
    color: var(--ink-soft);
    margin-bottom:1.1rem;
  }
  .fiche-grid{
    display:grid;
    grid-template-columns: 1fr 1fr;
    gap:.7rem;
    margin-bottom:1.1rem;
  }
  .fiche-stat{
    background: var(--cream);
    border:1px solid var(--line);
    border-radius:10px;
    padding:.65rem .8rem;
  }
  .fiche-stat-label{
    font-size:.68rem;
    text-transform:uppercase;
    letter-spacing:.05em;
    color: var(--ink-soft);
    font-weight:700;
    margin-bottom:.15rem;
  }
  .fiche-stat-value{
    font-family: var(--display);
    font-size:1rem;
    font-weight:600;
    color: var(--forest);
  }
  .fiche-desc-title{
    font-size:.78rem;
    font-weight:700;
    color: var(--ink-soft);
    text-transform:uppercase;
    letter-spacing:.04em;
    margin-bottom:.35rem;
  }
  .fiche-desc-text{
    font-size:.88rem;
    color: var(--ink);
    line-height:1.55;
  }
  .btn-voir-fiche{
    border:none;
    border-radius:7px;
    padding:.4rem .7rem;
    font-size:.76rem;
    font-weight:700;
    cursor:pointer;
    font-family: var(--body);
    background: var(--cream-2);
    color: var(--forest);
    border:1px solid var(--line);
    transition: background .18s, border-color .18s;
    display:inline-flex;
    align-items:center;
    gap:.3rem;
  }
  .btn-voir-fiche:hover{ background:#EADFC4; border-color:#D0C4A8; }
  .btn-voir-fiche svg{ width:14px; height:14px; }

  .badge{
    display:inline-flex;
    align-items:center;
    gap:.35rem;
    padding: .28rem .65rem;
    border-radius: 999px;
    font-size: .76rem;
    font-weight: 700;
  }
  .badge.en_attente{ background: rgba(201,144,47,.14); color: var(--ochre); }
  .badge.acceptee{ background: rgba(76,175,80,.14); color: var(--green-dark); }
  .badge.refusee{ background: rgba(166,81,46,.12); color: var(--rust); }

  .row-actions{ display:flex; gap:.5rem; flex-wrap:wrap; align-items:center; }
  .modal-overlay{
    position: fixed;
    inset: 0;
    background: rgba(20, 42, 32, .55);
    display: grid;
    place-items: center;
    padding: 1rem;
    z-index: 200;
  }
  .modal-overlay[hidden]{ display: none; }
  .modal-dialog{
    width: min(460px, 100%);
    background: #fff;
    border-radius: 16px;
    border: 1px solid var(--line);
    box-shadow: 0 18px 42px rgba(20, 42, 32, .22);
    padding: 1.3rem;
  }
  .modal-dialog h3{ font-size: 1.1rem; margin-bottom: .4rem; }
  .modal-dialog p{ color: var(--ink-soft); font-size: .9rem; margin-bottom: 1rem; }
  .recovery-form{ display:flex; flex-direction:column; gap:.7rem; }
  .recovery-form label{ font-size:.85rem; font-weight:700; color: var(--forest); }
  .recovery-form input[type="date"]{
    border: 1px solid var(--line);
    border-radius: 8px;
    padding: .62rem .7rem;
    font-size: .9rem;
    color: var(--ink);
    background: #fff;
  }
  .modal-actions{ display:flex; justify-content:flex-end; gap:.6rem; margin-top:.3rem; }
  .btn-mini{
    border:none;
    border-radius: 7px;
    padding: .4rem .75rem;
    font-size: .78rem;
    font-weight:700;
    cursor:pointer;
    font-family: var(--body);
    transition: background .18s;
  }
  .btn-mini.accept{ background: var(--green); color:#fff; }
  .btn-mini.accept:hover{ background: var(--green-dark); }
  .btn-mini.refuse{ background: var(--cream-2); color: var(--rust); border:1px solid var(--line); }
  .btn-mini.refuse:hover{ background: #F3E4DC; }

  .empty-state{
    padding: 2.6rem 1.4rem;
    text-align:center;
    color: var(--ink-soft);
    font-size: .92rem;
  }

  /* ---------- RESPONSIVE ---------- */
  @media (max-width: 980px){
    .layout{ grid-template-columns: 1fr; }
    .sidebar{ position: static; height: auto; flex-direction: row; align-items:center; overflow-x:auto; }
    .sidebar-brand{ border:none; padding:0; margin:0; margin-right: 1.2rem; }
    .sidebar-label{ display:none; }
    .sidebar-nav{ flex-direction:row; margin:0; }
    .sidebar-footer{ margin-left:auto; border:none; padding:0; flex-direction:row; align-items:center; }
    .sidebar-user{ padding:0; }
    .topbar-stats{ display:none; }
  }
  @media (max-width: 560px){
    .main{ padding: 1.6rem 1.1rem 2.4rem; }
    thead{ display:none; }
    table, tbody, tr, td{ display:block; width:100%; }
    tbody tr{ border-top: 1px solid var(--line); padding: .9rem 1.1rem; }
    tbody td{ border:none; padding: .3rem 0; }
    tbody td::before{ content: attr(data-label); display:block; font-size:.72rem; color:var(--ink-soft); text-transform:uppercase; letter-spacing:.04em; }
    .row-actions{ padding-top:.4rem; }
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
      <a href="offres.php" class="active">
        <svg viewBox="0 0 24 24" fill="none"><path d="M3 12l3-8h12l3 8-9 9-9-9z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>
        Offres
      </a>
      <a href="ventes.php">
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
        <h1>Offres reçues</h1>
        <p class="sub">Propositions de prix envoyées par les acheteurs.</p>
      </div>
      <div class="topbar-stats">
        <div class="mini-stat">
          <b><?php echo count(array_filter($offres, fn($o) => $o['statut'] === 'en_attente')); ?></b>
          <span>En attente</span>
        </div>
        <div class="mini-stat">
          <b><?php echo count(array_filter($offres, fn($o) => $o['statut'] === 'acceptee')); ?></b>
          <span>Acceptées</span>
        </div>
      </div>
    </div>

    <?php if (!empty($flash)): ?>
      <div style="margin-bottom:1rem; padding:.85rem 1rem; border-radius:10px; background: <?php echo $flash['type'] === 'error' ? '#fbecec' : '#eaf7eb'; ?>; color: <?php echo $flash['type'] === 'error' ? '#8c2f2f' : '#2f6b3d'; ?>; border: 1px solid <?php echo $flash['type'] === 'error' ? '#e7b7b7' : '#b9d9bc'; ?>;">
        <?php echo htmlspecialchars($flash['message']); ?>
      </div>
    <?php endif; ?>

    <div class="panel">
      <div class="panel-head">
        <div>
          <h2>Toutes les offres</h2>
          <p><?php echo count($offres); ?> offre(s) au total</p>
        </div>
        <div class="filters">
          <a href="?filtre=tous" class="filter-chip <?php echo $filtre === 'tous' ? 'active' : ''; ?>">Toutes</a>
          <a href="?filtre=en_attente" class="filter-chip <?php echo $filtre === 'en_attente' ? 'active' : ''; ?>">En attente</a>
          <a href="?filtre=acceptee" class="filter-chip <?php echo $filtre === 'acceptee' ? 'active' : ''; ?>">Acceptées</a>
          <a href="?filtre=refusee" class="filter-chip <?php echo $filtre === 'refusee' ? 'active' : ''; ?>">Refusées</a>
        </div>
      </div>

      <?php if (!empty($offres)): ?>
      <table>
        <thead>
          <tr>
            <th>Vache</th>
            <th>Acheteur</th>
            <th>Montant proposé</th>
            <th>Date</th>
            <th>Statut</th>
            <th style="text-align:right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($offres as $offre): ?>
          <tr>
            <td data-label="Vache">
              <div class="vache-cell">
                <div class="vache-thumb">
                  <?php if ($thumbUrl = vacheFirstImageUrl($offre['vache_image'] ?? null)): ?>
                    <img src="<?php echo htmlspecialchars($thumbUrl); ?>" alt="">
                  <?php else: ?>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 20c0-4.4 3.6-8 8-8s8 3.6 8 8" stroke-linecap="round"/><circle cx="12" cy="8" r="4"/></svg>
                  <?php endif; ?>
                </div>
                <div>
                  <div class="name"><?php echo htmlspecialchars($offre['vache']); ?></div>
                  <div class="sub"><?php echo htmlspecialchars(labelBovin($offre['bovin'] ?? 'vache')); ?> · N°<?php echo (int)$offre['id_vache']; ?></div>
                </div>
              </div>
            </td>
            <td data-label="Acheteur">
              <div class="buyer-cell">
                <div class="name"><?php echo htmlspecialchars($offre['acheteur']); ?></div>
                <div class="contact">
                  <a href="mailto:<?php echo htmlspecialchars($offre['email']); ?>"><?php echo htmlspecialchars($offre['email']); ?></a>
                  <?php if (!empty($offre['telephone'])): ?>
                    <a href="tel:<?php echo htmlspecialchars(telephoneDigits($offre['telephone'])); ?>"><?php echo htmlspecialchars($offre['telephone']); ?></a>
                  <?php else: ?>
                    <span>Téléphone non renseigné</span>
                  <?php endif; ?>
                </div>
              </div>
            </td>
            <td data-label="Montant"><span class="montant"><?php echo number_format((float)$offre['montant'], 0, ',', ' '); ?> DH</span></td>
            <td data-label="Date"><?php echo date('d/m/Y H:i', strtotime($offre['date'])); ?></td>
            <td data-label="Statut">
              <span class="badge <?php echo $offre['statut']; ?>">
                <?php
                  $labels = ['en_attente' => 'En attente', 'acceptee' => 'Acceptée', 'refusee' => 'Refusée'];
                  echo $labels[$offre['statut']] ?? $offre['statut'];
                ?>
              </span>
            </td>
            <td data-label="Actions">
              <div class="row-actions">
                <button type="button" class="btn-voir-fiche open-fiche-modal"
                  data-vache-nom="<?php echo htmlspecialchars($offre['vache']); ?>"
                  data-vache-bovin="<?php echo htmlspecialchars(labelBovin($offre['bovin'] ?? 'vache')); ?>"
                  data-vache-id="<?php echo (int)$offre['id_vache']; ?>"
                  data-vache-age="<?php echo htmlspecialchars(vacheAgeFormatted($offre['date_naissance'] ?? null, $offre['vache_age'] !== null ? (int)$offre['vache_age'] : null)); ?>"
                  data-vache-poids="<?php echo number_format((float)($offre['poids'] ?? 0), 0, ',', ' '); ?>"
                  data-vache-desc="<?php echo htmlspecialchars($offre['vache_description'] ?? ''); ?>"
                  data-vache-img="<?php echo htmlspecialchars(vacheFirstImageUrl($offre['vache_image'] ?? null) ?? ''); ?>"
                  data-vache-statut="<?php echo htmlspecialchars($offre['vache_statut'] ?? 'disponible'); ?>"
                >
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                  Fiche
                </button>
                <?php if ($offre['statut'] === 'en_attente'): ?>
                <button type="button" class="btn-mini accept open-recovery-modal" data-offre-id="<?php echo (int)$offre['id']; ?>">Accepter</button>
                <form action="../actions/refuser_offre.php" method="POST">
                  <input type="hidden" name="id_offre" value="<?php echo (int)$offre['id']; ?>">
                  <button type="submit" class="btn-mini refuse">Refuser</button>
                </form>
                <?php else: ?>
                <span style="color:var(--ink-soft); font-size:.85rem;">
                  <?php if (!empty($offre['date_reprise'])): echo 'Récup: ' . date('d/m/Y', strtotime($offre['date_reprise'])); else: echo '—'; endif; ?>
                </span>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?>
        <div class="empty-state">Aucune offre reçue pour le moment.</div>
      <?php endif; ?>
    </div>

  </main>
</div>

<div id="recoveryModal" class="modal-overlay" hidden>
  <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="recoveryModalTitle">
    <h3 id="recoveryModalTitle">Date de récupération</h3>
    <p>Choisissez la date à laquelle l’acheteur viendra récupérer son bovin.</p>
    <form action="../actions/accepter_offre.php" method="POST" class="recovery-form">
      <input type="hidden" name="id_offre" id="modalOfferId">
      <label for="modalDateReprise">Date de récupération</label>
      <input type="date" id="modalDateReprise" name="date_reprise" required>
      <div class="modal-actions">
        <button type="button" class="btn-mini refuse modal-cancel">Annuler</button>
        <button type="submit" class="btn-mini accept">Accepter</button>
      </div>
    </form>
  </div>
</div>

<!-- ================= FICHE VACHE MODAL ================= -->
<div id="ficheVacheModal" class="fiche-overlay" hidden>
  <div class="fiche-dialog" role="dialog" aria-modal="true" aria-labelledby="ficheVacheTitle">
    <div class="fiche-hero">
      <div id="ficheHeroContent"></div>
      <button type="button" class="fiche-close" id="ficheCloseBtn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
      </button>
      <span id="ficheBadgeStatus" class="fiche-badge-status"></span>
    </div>
    <div class="fiche-body">
      <div class="fiche-title" id="ficheVacheTitle"></div>
      <div class="fiche-type" id="ficheVacheType"></div>
      <div class="fiche-grid">
        <div class="fiche-stat">
          <div class="fiche-stat-label">Âge</div>
          <div class="fiche-stat-value" id="ficheAge"></div>
        </div>
        <div class="fiche-stat">
          <div class="fiche-stat-label">Poids</div>
          <div class="fiche-stat-value" id="fichePoids"></div>
        </div>
        <div class="fiche-stat">
          <div class="fiche-stat-label">Fiche N°</div>
          <div class="fiche-stat-value" id="ficheNumero"></div>
        </div>
        <div class="fiche-stat">
          <div class="fiche-stat-label">Statut</div>
          <div class="fiche-stat-value" id="ficheStatut"></div>
        </div>
      </div>
      <div id="ficheDescBlock" style="display:none;">
        <div class="fiche-desc-title">Description</div>
        <div class="fiche-desc-text" id="ficheDesc"></div>
      </div>
    </div>
  </div>
</div>

<script>
  /* ---- Recovery modal ---- */
  const modal = document.getElementById('recoveryModal');
  const offerIdInput = document.getElementById('modalOfferId');
  const dateInput = document.getElementById('modalDateReprise');
  const openButtons = document.querySelectorAll('.open-recovery-modal');
  const cancelButtons = document.querySelectorAll('.modal-cancel');

  const closeModal = () => {
    modal.hidden = true;
    offerIdInput.value = '';
    dateInput.value = '';
  };

  openButtons.forEach((button) => {
    button.addEventListener('click', () => {
      offerIdInput.value = button.dataset.offreId || '';
      modal.hidden = false;
      dateInput.focus();
    });
  });

  cancelButtons.forEach((button) => {
    button.addEventListener('click', closeModal);
  });

  modal.addEventListener('click', (event) => {
    if (event.target === modal) {
      closeModal();
    }
  });

  /* ---- Fiche vache modal ---- */
  const ficheModal = document.getElementById('ficheVacheModal');
  const ficheCloseBtn = document.getElementById('ficheCloseBtn');
  const ficheHero = document.getElementById('ficheHeroContent');
  const ficheBadge = document.getElementById('ficheBadgeStatus');
  const ficheTitle = document.getElementById('ficheVacheTitle');
  const ficheType = document.getElementById('ficheVacheType');
  const ficheAge = document.getElementById('ficheAge');
  const fichePoids = document.getElementById('fichePoids');
  const ficheNumero = document.getElementById('ficheNumero');
  const ficheStatut = document.getElementById('ficheStatut');
  const ficheDescBlock = document.getElementById('ficheDescBlock');
  const ficheDesc = document.getElementById('ficheDesc');

  const openFicheModal = (btn) => {
    const d = btn.dataset;
    // Hero image
    if (d.vacheImg) {
      ficheHero.innerHTML = '<img src="' + d.vacheImg + '" alt="">';
    } else {
      ficheHero.innerHTML = '<div class="no-img"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><path d="M4 20c0-4.4 3.6-8 8-8s8 3.6 8 8" stroke-linecap="round"/><circle cx="12" cy="8" r="4"/></svg></div>';
    }
    // Badge status
    ficheBadge.textContent = d.vacheStatut === 'vendue' ? 'Vendue' : 'Disponible';
    ficheBadge.className = 'fiche-badge-status ' + (d.vacheStatut || 'disponible');
    // Info
    ficheTitle.textContent = d.vacheNom;
    ficheType.textContent = d.vacheBovin + ' · Fiche N°' + d.vacheId;
    ficheAge.textContent = d.vacheAge || 'Non renseigné';
    fichePoids.textContent = (d.vachePoids || '0') + ' kg';
    ficheNumero.textContent = d.vacheId;
    ficheStatut.textContent = d.vacheStatut === 'vendue' ? 'Vendue' : 'Disponible';
    // Description
    if (d.vacheDesc && d.vacheDesc.trim()) {
      ficheDescBlock.style.display = 'block';
      ficheDesc.textContent = d.vacheDesc;
    } else {
      ficheDescBlock.style.display = 'none';
    }
    ficheModal.hidden = false;
  };

  const closeFicheModal = () => { ficheModal.hidden = true; };

  document.querySelectorAll('.open-fiche-modal').forEach(btn => {
    btn.addEventListener('click', () => openFicheModal(btn));
  });

  ficheCloseBtn.addEventListener('click', closeFicheModal);
  ficheModal.addEventListener('click', (e) => { if (e.target === ficheModal) closeFicheModal(); });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      if (!ficheModal.hidden) closeFicheModal();
      else if (!modal.hidden) closeModal();
    }
  });
</script>

</body>
</html>