<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

requireAdmin();

$adminNom = $_SESSION['nom'];
$adminId = (int) $_SESSION['user_id'];

$filtre = $_GET['filtre'] ?? 'tous';
$statutsVaches = ['disponible', 'vendue'];

if (!in_array($filtre, array_merge(['tous'], $statutsVaches), true)) {
    $filtre = 'tous';
}

$sql = 'SELECT id, nom, bovin, date_naissance, age, poids, image, statut FROM vaches WHERE id_admin = :id_admin';

if ($filtre !== 'tous') {
    $sql .= ' AND statut = :statut';
}

$sql .= ' ORDER BY id DESC';

$stmt = $pdo->prepare($sql);
$params = [':id_admin' => $adminId];

if ($filtre !== 'tous') {
    $params[':statut'] = $filtre;
}

$stmt->execute($params);
$vaches = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Work+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="icon" type="image/x-icon" href="">
<title>Le cheptel — Ferme Tarmast</title>
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
  .topbar-add{
    display:inline-flex;
    align-items:center;
    gap:.5rem;
    background: var(--green);
    color:#fff;
    font-size:.88rem;
    font-weight:700;
    padding:.7rem 1.2rem;
    border-radius: 999px;
  }
  .topbar-add:hover{ background: var(--green-dark); }
  .topbar-add svg{ width:16px; height:16px; }

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

  .animal-cell{ display:flex; align-items:center; gap:.8rem; }
  .animal-thumb{
    width: 42px; height:42px;
    border-radius: 10px;
    background: rgba(76,175,80,.12);
    display:flex; align-items:center; justify-content:center;
    flex-shrink:0;
  }
  .animal-thumb svg{ width:22px; height:22px; color: var(--green-dark); }
  .animal-name{ font-weight:700; color: var(--forest); }
  .animal-sub{ font-size: .78rem; color: var(--ink-soft); }

  .badge{
    display:inline-flex;
    align-items:center;
    gap:.35rem;
    padding: .28rem .65rem;
    border-radius: 999px;
    font-size: .76rem;
    font-weight: 700;
  }
  .badge.disponible{ background: rgba(76,175,80,.14); color: var(--green-dark); }
  .badge.vendue{ background: rgba(201,144,47,.14); color: var(--ochre); }

  .row-actions{ display:flex; gap:.5rem; justify-content:flex-end; }
  .icon-btn{
    width: 34px; height:34px;
    border-radius: 8px;
    display:flex; align-items:center; justify-content:center;
    border: 1px solid var(--line);
    background:#fff;
    cursor:pointer;
    color: var(--forest);
    transition: background .18s, border-color .18s, color .18s;
  }
  .icon-btn svg{ width:16px; height:16px; }
  .icon-btn:hover{ background: var(--cream-2); }
  .icon-btn.danger{ color: var(--rust); }
  .icon-btn.danger:hover{ background: rgba(166,81,46,.1); border-color: var(--rust); }

  .empty-state{
    padding: 2.6rem 1.4rem;
    text-align:center;
    color: var(--ink-soft);
    font-size: .92rem;
  }
  .empty-state a{ color: var(--green-dark); font-weight:700; }

  /* ---------- RESPONSIVE ---------- */
  @media (max-width: 980px){
    .layout{ grid-template-columns: 1fr; }
    .sidebar{ position: static; height: auto; flex-direction: row; align-items:center; overflow-x:auto; }
    .sidebar-brand{ border:none; padding:0; margin:0; margin-right: 1.2rem; }
    .sidebar-label{ display:none; }
    .sidebar-nav{ flex-direction:row; margin:0; }
    .sidebar-footer{ margin-left:auto; border:none; padding:0; flex-direction:row; align-items:center; }
    .sidebar-user{ padding:0; }
  }
  @media (max-width: 560px){
    .main{ padding: 1.6rem 1.1rem 2.4rem; }
    thead{ display:none; }
    table, tbody, tr, td{ display:block; width:100%; }
    tbody tr{ border-top: 1px solid var(--line); padding: .9rem 1.1rem; }
    tbody td{ border:none; padding: .3rem 0; }
    tbody td::before{ content: attr(data-label); display:block; font-size:.72rem; color:var(--ink-soft); text-transform:uppercase; letter-spacing:.04em; }
    .row-actions{ justify-content:flex-start; padding-top:.4rem; }
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
      <a href="liste_vaches.php" class="active">
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
        <h1>Le cheptel</h1>
        <p class="sub">Toutes les vaches enregistrées par la ferme.</p>
      </div>
      <a href="ajouter_vache.php" class="topbar-add">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
        Ajouter une vache
      </a>
    </div>

    <div class="panel">
      <div class="panel-head">
        <div>
          <h2>Liste des animaux</h2>
          <p><?php echo count($vaches); ?> fiche(s) enregistrée(s)</p>
        </div>
        <div class="filters">
          <a href="?filtre=tous" class="filter-chip <?php echo $filtre === 'tous' ? 'active' : ''; ?>">Tous</a>
          <a href="?filtre=disponible" class="filter-chip <?php echo $filtre === 'disponible' ? 'active' : ''; ?>">Disponibles</a>
          <a href="?filtre=vendue" class="filter-chip <?php echo $filtre === 'vendue' ? 'active' : ''; ?>">Vendues</a>
        </div>
      </div>

      <?php if (!empty($vaches)): ?>
      <table>
        <thead>
          <tr>
            <th>Animal</th>
            <th>Âge</th>
            <th>Poids</th>
            <th>Statut</th>
            <th style="text-align:right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($vaches as $v): ?>
          <tr>
            <td data-label="Animal">
              <div class="animal-cell">
                <div class="animal-thumb">
                  <?php if ($imageUrl = vacheImageUrl($v['image'] ?? null)): ?>
                    <img src="<?php echo htmlspecialchars($imageUrl); ?>" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:8px;">
                  <?php else: ?>
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 20c0-4.4 3.6-8 8-8s8 3.6 8 8" stroke-linecap="round"/><circle cx="12" cy="8" r="4"/></svg>
                  <?php endif; ?>
                </div>
                <div>
                  <div class="animal-name"><?php echo htmlspecialchars($v['nom']); ?></div>
                  <div class="animal-sub"><?php echo htmlspecialchars(labelBovin($v['bovin'] ?? 'vache')); ?> · Fiche N°<?php echo (int)$v['id']; ?></div>
                </div>
              </div>
            </td>
            <td data-label="Âge"><?php echo (int) vacheAge($v['date_naissance'] ?? null, $v['age'] !== null ? (int) $v['age'] : null); ?> ans</td>
            <td data-label="Poids"><?php echo number_format((float)$v['poids'], 0, ',', ' '); ?> kg</td>
            <td data-label="Statut">
              <span class="badge <?php echo $v['statut']; ?>">
                <?php echo $v['statut'] === 'disponible' ? 'Disponible' : 'Vendue'; ?>
              </span>
            </td>
            <td data-label="Actions">
              <div class="row-actions">
                <a href="modifier_vache.php?id=<?php echo (int)$v['id']; ?>" class="icon-btn" title="Modifier">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                </a>
                <form action="../actions/supprimer_vache.php" method="POST" onsubmit="return confirm('Supprimer cette fiche ?');">
                  <input type="hidden" name="id" value="<?php echo (int)$v['id']; ?>">
                  <button type="submit" class="icon-btn danger" title="Supprimer">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/></svg>
                  </button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?>
        <div class="empty-state">
          Aucune vache enregistrée pour le moment.<br>
          <a href="ajouter_vache.php">Ajouter la première fiche →</a>
        </div>
      <?php endif; ?>
    </div>

  </main>
</div>

</body>
</html>