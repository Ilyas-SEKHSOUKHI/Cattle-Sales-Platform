<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

requireAcheteur('../login.php', '../admin/dashboard.php');

$nom_utilisateur = $_SESSION['nom'];
$userId = (int) $_SESSION['user_id'];

$hasDateReprise = tableHasColumn($pdo, 'offres', 'date_reprise');

$sql = 'SELECT o.id, o.montant_propose, o.date_offre, o.statut';

if ($hasDateReprise) {
    $sql .= ', o.date_reprise';
} else {
    $sql .= ', NULL AS date_reprise';
}

$sql .= ', v.id AS id_vache, v.nom AS vache
         FROM offres o
         JOIN vaches v ON o.id_vache = v.id
         WHERE o.id_utilisateur = :id_utilisateur
         ORDER BY o.date_offre DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute([':id_utilisateur' => $userId]);
$toutesLesOffres = $stmt->fetchAll(PDO::FETCH_ASSOC);

$filtre = $_GET['filtre'] ?? 'tous';
$statutsOffres = ['en_attente', 'acceptee', 'refusee'];

if (!in_array($filtre, array_merge(['tous'], $statutsOffres), true)) {
    $filtre = 'tous';
}

$mes_offres = $filtre === 'tous'
    ? $toutesLesOffres
    : array_values(array_filter($toutesLesOffres, fn($o) => $o['statut'] === $filtre));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mes offres — Ferme Tarmast</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="icon" type="image/png" href="../assets/images/iconVache.png">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Work+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
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
  html{ scroll-behavior: smooth; }
  body{
    font-family: var(--body);
    background: var(--cream);
    color: var(--ink);
    line-height: 1.55;
    -webkit-font-smoothing: antialiased;
  }
  img, svg{ display:block; max-width:100%; }
  a{ color: inherit; text-decoration: none; }
  ul{ list-style:none; }
  h1,h2,h3{ font-family: var(--display); color: var(--forest); font-weight: 600; }
  .container{ width: min(1160px, 92%); margin-inline: auto; }
  :focus-visible{ outline: 3px solid var(--ochre); outline-offset: 3px; }

  .fence-divider{
    height: 22px;
    background-image: repeating-linear-gradient(to right, var(--line) 0 2px, transparent 2px 26px);
    background-position: bottom;
    background-repeat: repeat-x;
    background-size: auto 100%;
    opacity: .9;
  }

  /* ---------- NAVBAR ---------- */
  .navbar{
    position: sticky; top: 0; z-index: 100;
    background: rgba(251,246,236,.92);
    backdrop-filter: blur(6px);
    border-bottom: 1px solid var(--line);
  }
  .navbar .container{ display:flex; align-items:center; justify-content: space-between; height: 74px; }
  .brand{ display:flex; align-items:center; gap:.6rem; font-family: var(--display); font-size: 1.35rem; font-weight: 700; color: var(--forest); }
  .brand-mark{ width: 38px; height: 38px; flex-shrink: 0; }
  .nav-links{ display:flex; gap: 2rem; align-items:center; }
  .nav-links a{ font-size: .95rem; font-weight: 500; color: var(--ink-soft); transition: color .2s; }
  .nav-links a.active{ color: var(--forest); font-weight: 700; }
  .nav-links a:hover{ color: var(--forest); }
  .nav-actions{ display:flex; align-items:center; gap: 1rem; }
  .user-chip{
    display:flex; align-items:center; gap:.6rem;
    padding: .4rem .8rem .4rem .4rem;
    border-radius: 999px;
    border: 1px solid var(--line);
    background:#fff;
    font-size: .88rem;
    font-weight: 600;
    color: var(--forest);
  }
  .user-chip .avatar{
    width: 30px; height:30px; border-radius:50%;
    background: var(--green); color:#fff;
    display:flex; align-items:center; justify-content:center;
    font-family: var(--display); font-weight:700; font-size:.85rem;
  }
  .btn{
    display:inline-flex; align-items:center; justify-content:center; gap: .5rem;
    padding: .68rem 1.3rem; border-radius: 8px; font-weight: 700; font-size: .92rem;
    border: none; cursor: pointer; transition: background .25s, color .25s, box-shadow .25s, transform .15s;
    font-family: var(--body);
  }
  .btn-ghost{ background: transparent; color: var(--forest); border: 1.5px solid var(--line); }
  .btn-ghost:hover{ border-color: var(--forest); background: rgba(27,58,43,.04); }
  .nav-toggle{ display:none; background:none; border:none; cursor:pointer; flex-direction:column; gap:5px; padding: 8px; }
  .nav-toggle span{ width: 24px; height: 2px; background: var(--forest); display:block; border-radius: 2px; }

  /* ---------- PAGE HEAD ---------- */
  .page-head{ padding: 3.2rem 0 2.2rem; position: relative; overflow:hidden; }
  .page-head::before{
    content:"";
    position:absolute; inset: 0;
    background: radial-gradient(600px 260px at 90% 0%, rgba(201,144,47,.12), transparent 70%);
    pointer-events: none;
  }
  .page-head .eyebrow{
    display:inline-flex; align-items:center; gap:.5rem;
    font-size: .78rem; letter-spacing: .12em; text-transform: uppercase; font-weight: 700;
    color: var(--rust); background: rgba(166,81,46,.09);
    padding: .4rem .8rem; border-radius: 999px; margin-bottom: 1rem;
  }
  .eyebrow .dot{ width:6px; height:6px; border-radius:50%; background: var(--rust); }
  .page-head h1{ font-size: clamp(1.8rem, 3vw, 2.5rem); margin-bottom: .6rem; }
  .page-head p{ color: var(--ink-soft); max-width: 56ch; }

  /* ---------- STAT STRIP ---------- */
  .stat-strip{ display:flex; gap: 1rem; margin-bottom: 2.4rem; flex-wrap: wrap; }
  .stat-pill{
    background:#fff; border:1px solid var(--line); border-radius: 14px;
    padding: 1rem 1.3rem; flex:1; min-width: 150px;
  }
  .stat-pill .num{ font-family: var(--display); font-size:1.5rem; font-weight:700; color: var(--forest); }
  .stat-pill .lbl{ font-size:.78rem; color: var(--ink-soft); text-transform: uppercase; letter-spacing:.04em; margin-top:.15rem; }

  main{ padding-bottom: 5rem; }

  /* ---------- PANEL / TABLE ---------- */
  .panel{
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 10px 26px rgba(27,58,43,.05);
  }
  .panel-head{
    display:flex; align-items:center; justify-content:space-between;
    padding: 1.3rem 1.5rem; border-bottom: 1px solid var(--line);
    flex-wrap: wrap; gap: .8rem;
  }
  .panel-head h2{ font-size: 1.1rem; }
  .panel-head p{ font-size: .82rem; color: var(--ink-soft); margin-top: .15rem; }

  .filters{ display:flex; gap:.5rem; flex-wrap: wrap; }
  .filter-chip{
    font-size: .8rem; font-weight:700; color: var(--ink-soft);
    background: var(--cream-2); border: 1px solid var(--line);
    padding: .4rem .85rem; border-radius: 999px;
  }
  .filter-chip:hover{ background:#EADFC4; }
  .filter-chip.active{ background: var(--forest); color:#fff; border-color: var(--forest); }

  table{ width:100%; border-collapse: collapse; }
  thead th{
    text-align:left; font-size:.74rem; text-transform: uppercase; letter-spacing:.05em;
    color: var(--ink-soft); padding: .8rem 1.5rem; background: var(--cream-2); font-weight:700;
  }
  tbody td{ padding: 1rem 1.5rem; border-top: 1px solid var(--line); font-size:.9rem; vertical-align: middle; }
  tbody tr:hover{ background: rgba(76,175,80,.04); }

  .vache-cell{ display:flex; align-items:center; gap:.8rem; }
  .vache-thumb{
    width: 42px; height:42px; border-radius: 10px;
    background: rgba(76,175,80,.12);
    display:flex; align-items:center; justify-content:center; flex-shrink:0;
  }
  .vache-thumb svg{ width:22px; height:22px; color: var(--green-dark); }
  .vache-name{ font-weight:700; color: var(--forest); }

  .montant{ font-family: var(--display); font-weight:600; font-size: 1rem; }

  .badge{
    display:inline-flex; align-items:center; gap:.35rem;
    padding: .28rem .65rem; border-radius: 999px; font-size:.76rem; font-weight:700;
  }
  .badge.en_attente{ background: rgba(201,144,47,.14); color: var(--ochre); }
  .badge.acceptee{ background: rgba(76,175,80,.14); color: var(--green-dark); }
  .badge.refusee{ background: rgba(166,81,46,.12); color: var(--rust); }

  .view-link{
    font-size: .82rem; font-weight:700; color: var(--forest);
    border: 1px solid var(--line); border-radius: 8px; padding: .45rem .9rem;
    display:inline-block; white-space:nowrap;
  }
  .view-link:hover{ background: var(--cream-2); }

  /* ---------- ETAT VIDE ---------- */
  .empty-state{
    text-align:center; padding: 4rem 1rem;
    background: #fff; border: 1px dashed var(--line); border-radius: 18px;
  }
  .empty-state svg{ width:56px; height:56px; margin: 0 auto 1.2rem; color: var(--line); }
  .empty-state h3{ margin-bottom: .5rem; }
  .empty-state p{ color: var(--ink-soft); font-size: .95rem; margin-bottom: 1.4rem; }
  .empty-state .btn-cta{
    display:inline-block; background: var(--green); color:#fff;
    padding: .7rem 1.4rem; border-radius: 8px; font-weight:700; font-size:.9rem;
  }
  .empty-state .btn-cta:hover{ background: var(--green-dark); }

  /* ---------- RESPONSIVE ---------- */
  @media (max-width: 760px){
    .nav-links{ display:none; }
    .nav-toggle{ display:flex; }
  }
  @media (max-width: 620px){
    thead{ display:none; }
    table, tbody, tr, td{ display:block; width:100%; }
    tbody tr{ border-top: 1px solid var(--line); padding: 1rem 1.2rem; }
    tbody td{ border:none; padding: .35rem 0; }
    tbody td::before{ content: attr(data-label); display:block; font-size:.72rem; color:var(--ink-soft); text-transform:uppercase; letter-spacing:.04em; }
    .view-link{ margin-top:.3rem; }
  }
</style>
</head>
<body>

<!-- ================= NAVBAR ================= -->
<header class="navbar">
  <div class="container">
    <a href="accueil.php" class="brand">
      <svg class="brand-mark" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="24" cy="24" r="23" fill="#4CAF50" opacity="0.12"/>
        <path d="M14 22c0-2 1.5-3.5 3.5-3.5 1 0 1.8.4 2.5 1 1-1.3 2.5-2 4-2s3 .7 4 2c.7-.6 1.5-1 2.5-1 2 0 3.5 1.5 3.5 3.5 0 1-.4 2-1 2.6.6.5 1 1.3 1 2.2 0 2-1.7 3.7-3.7 3.7H18.7C16.7 30.5 15 28.8 15 26.8c0-.9.4-1.7 1-2.2-.6-.6-1-1.6-1-2.6z" fill="#1B3A2B"/>
        <circle cx="20" cy="25" r="1.4" fill="#fff"/>
        <circle cx="28" cy="25" r="1.4" fill="#fff"/>
        <path d="M22.5 27.5c.5.6 1.5.6 2 0" stroke="#fff" stroke-width="1" stroke-linecap="round"/>
      </svg>
      Ferme Tarmast
    </a>

    <nav class="nav-links">
      <a href="accueil.php">Le cheptel</a>
      <a href="mes_offres.php" class="active">Mes offres</a>
      <a href="profil.php">Mon profil</a>
    </nav>

    <div class="nav-actions">
      <div class="user-chip">
        <span class="avatar"><?php echo isset($nom_utilisateur) ? strtoupper(substr($nom_utilisateur, 0, 1)) : '?'; ?></span>
        <?php echo htmlspecialchars($nom_utilisateur ?? 'Client'); ?>
      </div>
      <a href="../actions/logout.php" class="btn btn-ghost">Déconnexion</a>
    </div>

    <button class="nav-toggle" aria-label="Ouvrir le menu">
      <span></span><span></span><span></span>
    </button>
  </div>
</header>

<!-- ================= PAGE HEAD ================= -->
<section class="page-head">
  <div class="container">
    <span class="eyebrow"><span class="dot"></span> Mon compte · Ferme Tarmast</span>
    <h1>Mes offres</h1>
    <p>Suivez ici l'état de toutes les propositions de prix que vous avez envoyées à la ferme.</p>
  </div>
</section>

<main>
  <div class="container">

    <?php
      $nbAttente  = count(array_filter($toutesLesOffres, fn($o) => $o['statut'] === 'en_attente'));
      $nbAcceptee = count(array_filter($toutesLesOffres, fn($o) => $o['statut'] === 'acceptee'));
      $nbRefusee  = count(array_filter($toutesLesOffres, fn($o) => $o['statut'] === 'refusee'));
    ?>
    <div class="stat-strip">
      <div class="stat-pill">
        <div class="num"><?php echo count($toutesLesOffres); ?></div>
        <div class="lbl">Offres envoyées</div>
      </div>
      <div class="stat-pill">
        <div class="num"><?php echo $nbAttente; ?></div>
        <div class="lbl">En attente</div>
      </div>
      <div class="stat-pill">
        <div class="num"><?php echo $nbAcceptee; ?></div>
        <div class="lbl">Acceptées</div>
      </div>
      <div class="stat-pill">
        <div class="num"><?php echo $nbRefusee; ?></div>
        <div class="lbl">Refusées</div>
      </div>
    </div>

    <div class="panel">
      <div class="panel-head">
        <div>
          <h2>Historique des offres</h2>
          <p><?php echo count($mes_offres); ?> offre(s) affichée(s)</p>
        </div>
        <div class="filters">
          <a href="?filtre=tous" class="filter-chip <?php echo $filtre === 'tous' ? 'active' : ''; ?>">Toutes</a>
          <a href="?filtre=en_attente" class="filter-chip <?php echo $filtre === 'en_attente' ? 'active' : ''; ?>">En attente</a>
          <a href="?filtre=acceptee" class="filter-chip <?php echo $filtre === 'acceptee' ? 'active' : ''; ?>">Acceptées</a>
          <a href="?filtre=refusee" class="filter-chip <?php echo $filtre === 'refusee' ? 'active' : ''; ?>">Refusées</a>
        </div>
      </div>

      <?php if (!empty($mes_offres)): ?>
      <table>
        <thead>
          <tr>
            <th>Vache</th>
            <th>Offre proposée</th>
            <th>Statut</th>
            <th>Date</th>
            <th style="text-align:right;">Fiche</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($mes_offres as $offre): ?>
          <tr>
            <td data-label="Vache">
              <div class="vache-cell">
                <div class="vache-thumb">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 20c0-4.4 3.6-8 8-8s8 3.6 8 8" stroke-linecap="round"/><circle cx="12" cy="8" r="4"/></svg>
                </div>
                <span class="vache-name"><?php echo htmlspecialchars($offre['vache']); ?></span>
              </div>
            </td>
            <td data-label="Offre"><span class="montant"><?php echo number_format((float)$offre['montant_propose'], 0, ',', ' '); ?> MAD</span></td>
            <td data-label="Statut">
              <span class="badge <?php echo $offre['statut']; ?>">
                <?php
                  $labels = ['en_attente' => 'En attente', 'acceptee' => 'Acceptée', 'refusee' => 'Refusée'];
                  echo $labels[$offre['statut']] ?? $offre['statut'];
                ?>
              </span>
            </td>
            <td data-label="Date">
              <?php echo date('d/m/Y H:i', strtotime($offre['date_offre'])); ?>
              <?php if (!empty($offre['date_reprise'])): ?>
                <div style="margin-top:.3rem; font-size:.78rem; color:var(--green-dark); font-weight:600;">Récupération : <?php echo date('d/m/Y', strtotime($offre['date_reprise'])); ?></div>
              <?php endif; ?>
            </td>
            <td data-label="Fiche" style="text-align:right;">
              <a href="details_vache.php?id=<?php echo (int)$offre['id_vache']; ?>" class="view-link">Voir la fiche</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php elseif (!empty($toutesLesOffres)): ?>
        <div class="empty-state" style="margin: 1.5rem; border-radius: 12px;">
          <h3>Aucune offre pour ce filtre</h3>
          <p>Essayez un autre filtre ou consultez toutes vos offres.</p>
          <a href="?filtre=tous" class="btn-cta">Voir toutes les offres</a>
        </div>
      <?php else: ?>
        <div class="empty-state">
          <svg viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
          <h3>Vous n'avez encore envoyé aucune offre</h3>
          <p>Parcourez le cheptel disponible et proposez un prix sur la vache qui vous intéresse.</p>
          <a href="accueil.php" class="btn-cta">Voir le cheptel</a>
        </div>
      <?php endif; ?>
    </div>

  </div>
</main>

<div class="fence-divider"></div>

<script>
  const toggle = document.querySelector('.nav-toggle');
  const links = document.querySelector('.nav-links');
  toggle.addEventListener('click', () => {
    const open = links.style.display === 'flex';
    links.style.display = open ? 'none' : 'flex';
    links.style.cssText += open ? '' : 'position:absolute;top:74px;left:0;right:0;background:#FBF6EC;flex-direction:column;padding:1.2rem 6%;border-bottom:1px solid #E3D9C2;gap:1rem;';
  });
</script>

</body>
</html>