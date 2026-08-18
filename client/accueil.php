<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

requireAcheteur('../login.php', '../admin/dashboard.php');

$nom_utilisateur = $_SESSION['nom'];

$filtres = [
    'recherche' => trim($_GET['recherche'] ?? ''),
    'tri' => $_GET['tri'] ?? 'recent',
];

$sql = "SELECT id, nom, bovin, date_naissance, age, poids, description, image, statut FROM vaches WHERE statut = 'disponible'";
$params = [];

if ($filtres['recherche'] !== '') {
    $sql .= ' AND nom LIKE :recherche';
    $params[':recherche'] = '%' . $filtres['recherche'] . '%';
}

switch ($filtres['tri']) {
    case 'age_asc':
        $sql .= ' ORDER BY date_naissance IS NULL, date_naissance DESC, age ASC';
        break;
    case 'age_desc':
        $sql .= ' ORDER BY date_naissance IS NULL, date_naissance ASC, age DESC';
        break;
    case 'poids_desc':
        $sql .= ' ORDER BY poids DESC';
        break;
    default:
        $sql .= ' ORDER BY id DESC';
        break;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$vaches = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Le cheptel disponible — Ferme Tarmast</title>
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

  /* ---------- FILTER BAR ---------- */
  .filter-bar{
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 16px;
    padding: 1.3rem 1.4rem;
    display:flex;
    flex-wrap: wrap;
    gap: 1rem;
    align-items: end;
    margin-bottom: 2.6rem;
    box-shadow: 0 10px 26px rgba(27,58,43,.05);
  }
  .field{ display:flex; flex-direction: column; gap: .4rem; flex: 1; min-width: 180px; }
  .field label{ font-size: .78rem; font-weight: 700; color: var(--ink-soft); text-transform: uppercase; letter-spacing: .04em; }
  .field select, .field input{
    border: 1px solid var(--line);
    border-radius: 8px;
    padding: .62rem .7rem;
    font-family: var(--body);
    font-size: .92rem;
    color: var(--ink);
    background: var(--cream);
  }
  .field select:focus, .field input:focus{ outline: none; border-color: var(--green); }
  .filter-bar .btn-submit{
    background: var(--green); color:#fff; box-shadow: 0 6px 16px rgba(76,175,80,.25);
    padding: .72rem 1.5rem; align-self: end;
  }
  .filter-bar .btn-submit:hover{ background: var(--green-dark); }
  .filter-bar .btn-reset{
    color: var(--ink-soft); font-size: .85rem; font-weight: 600;
    padding: .72rem .5rem; align-self:end;
  }
  .filter-bar .btn-reset:hover{ color: var(--rust); }

  .results-row{
    display:flex; justify-content: space-between; align-items:center;
    margin-bottom: 1.6rem; flex-wrap: wrap; gap: .8rem;
  }
  .results-count{ color: var(--ink-soft); font-size: .92rem; }
  .results-count strong{ color: var(--forest); }

  /* ---------- GRID DE VACHES ---------- */
  main{ padding-bottom: 5rem; }
  .cheptel-grid{
    display:grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.6rem;
  }

  .cow-card{
    background: #fff;
    border-radius: 18px;
    border: 1px solid var(--line);
    overflow: hidden;
    box-shadow: 0 10px 24px rgba(27,58,43,.06);
    transition: transform .2s, box-shadow .2s;
    display:flex;
    flex-direction: column;
  }
  .cow-card:hover{ transform: translateY(-4px); box-shadow: 0 18px 34px rgba(27,58,43,.12); }

  .cow-photo{
    height: 160px;
    position: relative;
    background:
      linear-gradient(180deg, rgba(27,58,43,0), rgba(27,58,43,.35)),
      linear-gradient(135deg, #cfe7c9, #a9cf9f);
  }
  .cow-photo .cow-fallback{ position:absolute; inset:0; margin:auto; width: 58%; opacity:.85; }
  .cow-photo img{
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }

  .cow-tag{
    position:absolute; top: 12px; left: 12px;
    background: var(--forest); color:#fff;
    font-size:.72rem; font-weight:700; letter-spacing:.03em;
    padding: .3rem .6rem; border-radius: 999px;
  }
  .cow-tag.statut-vendue{ background: var(--rust); }

  .cow-body{ padding: 1.15rem 1.25rem 1.3rem; display:flex; flex-direction:column; flex:1; }
  .cow-body h3{ font-size: 1.1rem; margin-bottom: .3rem; }
  .cow-meta{ font-size: .82rem; color: var(--ink-soft); margin-bottom: .7rem; }
  .cow-desc{
    font-size: .87rem;
    color: var(--ink-soft);
    margin-bottom: 1.1rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  .cow-actions{ display:flex; gap: .6rem; margin-top: auto; }
  .btn-detail{
    flex:1; text-align:center;
    background: var(--forest); color:#fff;
    padding: .62rem .9rem; border-radius: 8px;
    font-size: .87rem; font-weight: 700;
  }
  .btn-detail:hover{ background: var(--forest-2); }
  .btn-detail.disabled{
    background: var(--line); color: var(--ink-soft); pointer-events: none;
  }

  /* ---------- ETAT VIDE ---------- */
  .empty-state{
    text-align:center;
    padding: 4rem 1rem;
    background: #fff;
    border: 1px dashed var(--line);
    border-radius: 18px;
  }
  .empty-state svg{ width:56px; height:56px; margin: 0 auto 1.2rem; color: var(--line); }
  .empty-state h3{ margin-bottom: .5rem; }
  .empty-state p{ color: var(--ink-soft); font-size: .95rem; }

  /* ---------- PAGINATION ---------- */
  .pagination{
    display:flex; justify-content:center; align-items:center;
    gap: .5rem; margin-top: 3rem;
  }
  .pagination a, .pagination span{
    min-width: 38px; height: 38px;
    display:flex; align-items:center; justify-content:center;
    border-radius: 8px; font-size: .88rem; font-weight:600;
    color: var(--ink-soft); border: 1px solid var(--line);
    background:#fff;
  }
  .pagination a:hover{ border-color: var(--green); color: var(--forest); }
  .pagination .current{ background: var(--forest); color:#fff; border-color: var(--forest); }

  /* ---------- RESPONSIVE ---------- */
  @media (max-width: 980px){
    .cheptel-grid{ grid-template-columns: repeat(2, 1fr); }
  }
  @media (max-width: 760px){
    .nav-links{ display:none; }
    .nav-toggle{ display:flex; }
    .filter-bar{ flex-direction: column; align-items: stretch; }
    .filter-bar .btn-submit, .filter-bar .btn-reset{ width:100%; }
  }
  @media (max-width: 560px){
    .cheptel-grid{ grid-template-columns: 1fr; }
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
      <a href="accueil.php" class="active">Le cheptel</a>
      <a href="mes_offres.php">Mes offres</a>
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
    <span class="eyebrow"><span class="dot"></span> Cheptel · Ferme Tarmast</span>
    <h1>Le cheptel disponible</h1>
    <p>Parcourez les vaches actuellement proposées par Jibal et envoyez votre offre directement depuis la fiche qui vous intéresse.</p>
  </div>
</section>

<main>
  <div class="container">

    <!-- ================= FILTRES ================= -->
    <form class="filter-bar" method="get" action="accueil.php">
      <div class="field">
        <label for="recherche">Rechercher par nom</label>
        <input type="text" name="recherche" id="recherche"
               placeholder="Ex : Zahra"
               value="<?php echo htmlspecialchars($filtres['recherche'] ?? ''); ?>">
      </div>

      <div class="field">
        <label for="tri">Trier par</label>
        <select name="tri" id="tri">
          <option value="recent" <?php echo (($filtres['tri'] ?? 'recent') === 'recent') ? 'selected' : ''; ?>>Plus récentes</option>
          <option value="age_asc" <?php echo (($filtres['tri'] ?? '') === 'age_asc') ? 'selected' : ''; ?>>Âge croissant</option>
          <option value="age_desc" <?php echo (($filtres['tri'] ?? '') === 'age_desc') ? 'selected' : ''; ?>>Âge décroissant</option>
          <option value="poids_desc" <?php echo (($filtres['tri'] ?? '') === 'poids_desc') ? 'selected' : ''; ?>>Poids décroissant</option>
        </select>
      </div>

      <button type="submit" class="btn btn-submit">Filtrer</button>
      <a href="accueil.php" class="btn-reset">Réinitialiser</a>
    </form>

    <!-- ================= RESULTATS ================= -->
    <div class="results-row">
      <p class="results-count">
        <strong><?php echo isset($vaches) ? count($vaches) : 0; ?></strong> vache(s) trouvée(s)
      </p>
    </div>

    <?php if (!empty($vaches)): ?>
      <div class="cheptel-grid">
        <?php foreach ($vaches as $vache): ?>
          <?php
            $statut = $vache['statut'] ?? 'disponible';
            $labelStatut = ($statut === 'vendue') ? 'Vendue' : 'Disponible';
          ?>
          <article class="cow-card">
            <div class="cow-photo">
              <?php if ($imageUrl = vacheFirstImageUrl($vache['image'] ?? null)): ?>
                <img src="<?php echo htmlspecialchars($imageUrl); ?>" alt="<?php echo htmlspecialchars($vache['nom']); ?>">
              <?php else: ?>
              <svg class="cow-fallback" viewBox="0 0 200 140" fill="none" xmlns="http://www.w3.org/2000/svg">
                <ellipse cx="100" cy="90" rx="70" ry="34" fill="#F2EAD8"/>
                <ellipse cx="60" cy="70" rx="16" ry="20" fill="#F2EAD8"/>
                <circle cx="50" cy="58" r="4" fill="#1B3A2B"/>
                <path d="M46 50c-2-6 2-10 6-8M64 50c2-6-2-10-6-8" stroke="#1B3A2B" stroke-width="2" stroke-linecap="round"/>
                <ellipse cx="120" cy="95" rx="14" ry="9" fill="#A6512E" opacity=".7"/>
                <ellipse cx="90" cy="95" rx="10" ry="12" fill="#1B3A2B" opacity=".55"/>
                <path d="M40 118l0 14M70 122l0 14M110 122l0 14M140 118l0 14" stroke="#1B3A2B" stroke-width="6" stroke-linecap="round"/>
              </svg>
              <?php endif; ?>
              <span class="cow-tag statut-<?php echo htmlspecialchars($statut); ?>"><?php echo $labelStatut; ?></span>
            </div>

            <div class="cow-body">
              <h3><?php echo htmlspecialchars($vache['nom']); ?></h3>
              <p class="cow-meta">
                <?php echo htmlspecialchars(labelBovin($vache['bovin'] ?? 'vache')); ?> ·
                <?php echo htmlspecialchars(vacheAgeFormatted($vache['date_naissance'] ?? null, $vache['age'] !== null ? (int) $vache['age'] : null)); ?> ·
                <?php echo number_format((float)$vache['poids'], 0, ',', ' '); ?> kg
              </p>
              <?php if (!empty($vache['description'])): ?>
                <p class="cow-desc"><?php echo htmlspecialchars($vache['description']); ?></p>
              <?php endif; ?>

              <div class="cow-actions">
                <?php if ($statut === 'disponible'): ?>
                  <a href="details_vache.php?id=<?php echo (int)$vache['id']; ?>" class="btn-detail">Voir la fiche</a>
                <?php else: ?>
                  <span class="btn-detail disabled">Vendue</span>
                <?php endif; ?>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>

      <!-- ================= PAGINATION (optionnelle) ================= -->
      <?php if (!empty($pagination_total) && $pagination_total > 1): ?>
        <div class="pagination">
          <?php for ($p = 1; $p <= $pagination_total; $p++): ?>
            <?php if ($p == ($pagination_courante ?? 1)): ?>
              <span class="current"><?php echo $p; ?></span>
            <?php else: ?>
              <a href="?page=<?php echo $p; ?>"><?php echo $p; ?></a>
            <?php endif; ?>
          <?php endfor; ?>
        </div>
      <?php endif; ?>

    <?php else: ?>
      <!-- ================= ETAT VIDE ================= -->
      <div class="empty-state">
        <svg viewBox="0 0 24 24" fill="none"><path d="M12 8v5m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
        <h3>Aucune vache ne correspond à votre recherche</h3>
        <p>Essayez d'élargir vos filtres ou revenez un peu plus tard — le cheptel est mis à jour régulièrement.</p>
      </div>
    <?php endif; ?>

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