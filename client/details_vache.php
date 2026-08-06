<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

requireAcheteur('../login.php', '../admin/dashboard.php');

$nom_utilisateur = $_SESSION['nom'];

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    redirect('accueil.php');
}

$stmt = $pdo->prepare('SELECT id, nom, bovin, date_naissance, age, poids, description, image, statut FROM vaches WHERE id = :id');
$stmt->execute([':id' => $id]);
$vache = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$vache) {
    redirect('accueil.php');
}

$offreStmt = $pdo->prepare(
    'SELECT montant_propose, statut, date_offre
     FROM offres
     WHERE id_utilisateur = :id_utilisateur AND id_vache = :id_vache
     ORDER BY date_offre DESC
     LIMIT 1'
);
$offreStmt->execute([
    ':id_utilisateur' => (int) $_SESSION['user_id'],
    ':id_vache' => $id,
]);
$offre_existante = $offreStmt->fetch(PDO::FETCH_ASSOC) ?: null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($vache['nom']); ?> — Ferme Tarmast</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="icon" type="image/png" href="../assets/images/icon_vache.png">
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

  /* ---------- BREADCRUMB ---------- */
  .breadcrumb-bar{ padding: 1.6rem 0 0; }
  .breadcrumb{ font-size: .85rem; color: var(--ink-soft); display:flex; align-items:center; gap:.4rem; }
  .breadcrumb a{ color: var(--ink-soft); font-weight: 600; }
  .breadcrumb a:hover{ color: var(--forest); }
  .breadcrumb span.current{ color: var(--forest); font-weight: 700; }

  /* ---------- MAIN LAYOUT ---------- */
  main{ padding: 1.6rem 0 5rem; }
  .detail-grid{
    display:grid;
    grid-template-columns: 1fr 1fr;
    gap: 2.6rem;
    align-items: start;
  }

  /* ---------- MEDIA ---------- */
  .media-card{
    background: linear-gradient(135deg, #cfe7c9, #a9cf9f);
    border-radius: 22px;
    border: 1px solid var(--line);
    position: relative;
    overflow: hidden;
    height: 420px;
    display:flex; align-items:center; justify-content:center;
  }
  .media-card::before{
    content:"";
    position:absolute; inset:0;
    background: linear-gradient(180deg, rgba(27,58,43,0), rgba(27,58,43,.28));
  }
  .media-card .cow-fallback{ width: 62%; position: relative; z-index: 1; opacity: .92; }
  .media-card img{
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    z-index: 1;
  }
  .media-tag{
    position:absolute; top: 18px; left: 18px; z-index: 2;
    background: var(--forest); color:#fff;
    font-size:.78rem; font-weight:700; letter-spacing:.03em;
    padding: .45rem .85rem; border-radius: 999px;
  }
  .media-tag.statut-vendue{ background: var(--rust); }
  .media-ref{
    position:absolute; bottom: 18px; left: 18px; z-index: 2;
    font-family: var(--display); font-weight:700; color:#fff;
    font-size: .82rem; background: rgba(20,42,32,.55);
    padding: .4rem .8rem; border-radius: 8px;
    backdrop-filter: blur(3px);
  }

  /* ---------- INFO ---------- */
  .info-eyebrow{
    display:inline-flex; align-items:center; gap:.5rem;
    font-size: .78rem; letter-spacing: .1em; text-transform: uppercase; font-weight: 700;
    color: var(--rust); background: rgba(166,81,46,.09);
    padding: .4rem .8rem; border-radius: 999px; margin-bottom: 1rem;
  }
  .info-eyebrow .dot{ width:6px; height:6px; border-radius:50%; background: var(--rust); }
  h1.vache-nom{ font-size: clamp(1.9rem, 3vw, 2.5rem); margin-bottom: .5rem; }

  .meta-row{ display:flex; gap: 1.6rem; margin-bottom: 1.4rem; flex-wrap: wrap; }
  .meta-item{ display:flex; flex-direction:column; gap:.15rem; }
  .meta-item .val{ font-family: var(--display); font-size: 1.2rem; font-weight:700; color: var(--forest); }
  .meta-item .lbl{ font-size: .74rem; color: var(--ink-soft); text-transform: uppercase; letter-spacing: .04em; }

  .vache-desc{ color: var(--ink-soft); margin-bottom: 1.8rem; font-size: .96rem; }

  /* ---------- OFFER PANEL ---------- */
  .offer-panel{
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 18px;
    padding: 1.6rem 1.6rem 1.7rem;
    box-shadow: 0 14px 30px rgba(27,58,43,.06);
  }
  .offer-panel h2{ font-size: 1.15rem; margin-bottom: .35rem; }
  .offer-panel .hint{ font-size: .85rem; color: var(--ink-soft); margin-bottom: 1.3rem; }

  .amount-field{ position: relative; margin-bottom: 1.1rem; }
  .amount-field span{
    position:absolute; left: 14px; top:50%; transform: translateY(-50%);
    font-family: var(--display); font-weight:700; color: var(--ink-soft); font-size: .88rem;
  }
  .amount-field input{
    width:100%;
    padding: .85rem .9rem .85rem 52px;
    border: 1px solid var(--line);
    border-radius: 10px;
    font-family: var(--body);
    font-size: 1.05rem;
    font-weight: 600;
    color: var(--ink);
    background: var(--cream);
    outline: none;
    transition: border-color .2s, box-shadow .2s, background .2s;
  }
  .amount-field input:focus{ border-color: var(--green); background:#fff; box-shadow: 0 0 0 3px rgba(76,175,80,.15); }

  .offer-panel .btn-offer{
    width:100%;
    background: var(--green); color:#fff;
    padding: .9rem 1rem; border-radius: 10px;
    font-weight: 700; font-size: .96rem;
    box-shadow: 0 8px 18px rgba(76,175,80,.25);
  }
  .offer-panel .btn-offer:hover{ background: var(--green-dark); }
  .offer-panel .btn-offer:disabled{ background: var(--line); color: var(--ink-soft); box-shadow:none; cursor:not-allowed; }

  .offer-note{
    display:flex; gap:.6rem; align-items:flex-start;
    font-size: .82rem; color: var(--ink-soft);
    margin-top: 1rem;
  }
  .offer-note svg{ width:16px; height:16px; flex-shrink:0; margin-top: 2px; color: var(--ochre); }

  .status-box{
    display:flex; gap:.7rem; align-items:flex-start;
    padding: .9rem 1rem; border-radius: 12px; font-size: .87rem;
    margin-bottom: 1.2rem;
  }
  .status-box svg{ width:18px; height:18px; flex-shrink:0; margin-top:1px; }
  .status-box.en_attente{ background: rgba(201,144,47,.1); color: #8a5f18; border: 1px solid rgba(201,144,47,.25); }
  .status-box.acceptee{ background: rgba(76,175,80,.1); color: var(--green-dark); border: 1px solid rgba(76,175,80,.25); }
  .status-box.refusee{ background: rgba(166,81,46,.08); color: var(--rust); border: 1px solid rgba(166,81,46,.22); }
  .status-box.vendue{ background: var(--cream-2); color: var(--ink-soft); border: 1px solid var(--line); }
  .status-box b{ font-family: var(--display); }

  /* ---------- MINI STEPS ---------- */
  .mini-steps{ margin-top: 1.6rem; display:flex; flex-direction:column; gap: .8rem; }
  .mini-step{ display:flex; gap:.7rem; align-items:flex-start; font-size: .85rem; color: var(--ink-soft); }
  .mini-step .num{
    width:22px; height:22px; flex-shrink:0; border-radius:50%;
    background: var(--cream-2); color: var(--forest);
    display:flex; align-items:center; justify-content:center;
    font-family: var(--display); font-weight:700; font-size: .75rem;
  }

  .back-link{
    display:inline-flex; align-items:center; gap:.4rem;
    font-size: .88rem; font-weight:700; color: var(--forest);
    margin-top: 2rem;
  }
  .back-link svg{ width:15px; height:15px; }
  .back-link:hover{ text-decoration: underline; }

  /* ---------- RESPONSIVE ---------- */
  @media (max-width: 900px){
    .detail-grid{ grid-template-columns: 1fr; }
    .media-card{ height: 320px; }
  }
  @media (max-width: 760px){
    .nav-links{ display:none; }
    .nav-toggle{ display:flex; }
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

<!-- ================= BREADCRUMB ================= -->
<div class="breadcrumb-bar">
  <div class="container">
    <div class="breadcrumb">
      <a href="accueil.php">Le cheptel</a>
      <span>/</span>
      <span class="current"><?php echo htmlspecialchars($vache['nom']); ?></span>
    </div>
  </div>
</div>

<main>
  <div class="container">
    <div class="detail-grid">

      <!-- ================= MEDIA ================= -->
      <div>
        <div class="media-card">
          <?php $statut = $vache['statut'] ?? 'disponible'; ?>
          <span class="media-tag statut-<?php echo htmlspecialchars($statut); ?>">
            <?php echo $statut === 'vendue' ? 'Vendue' : 'Disponible'; ?>
          </span>
          <span class="media-ref">Fiche N°<?php echo (int)$vache['id']; ?></span>
          <?php if ($imageUrl = vacheImageUrl($vache['image'] ?? null)): ?>
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
        </div>

        <a href="accueil.php" class="back-link">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
          Retour au cheptel
        </a>
      </div>

      <!-- ================= INFOS + OFFRE ================= -->
      <div>
        <span class="info-eyebrow"><span class="dot"></span> Fiche animal</span>
        <h1 class="vache-nom"><?php echo htmlspecialchars($vache['nom']); ?></h1>

        <div class="meta-row">
          <div class="meta-item">
            <span class="val"><?php echo htmlspecialchars(labelBovin($vache['bovin'] ?? 'vache')); ?></span>
            <span class="lbl">Bovin</span>
          </div>
          <div class="meta-item">
            <span class="val"><?php echo (int) vacheAge($vache['date_naissance'] ?? null, $vache['age'] !== null ? (int) $vache['age'] : null); ?> ans</span>
            <span class="lbl">Âge</span>
          </div>
          <div class="meta-item">
            <span class="val"><?php echo number_format((float)$vache['poids'], 0, ',', ' '); ?> kg</span>
            <span class="lbl">Poids</span>
          </div>
          <div class="meta-item">
            <span class="val"><?php echo $statut === 'vendue' ? 'Vendue' : 'Disponible'; ?></span>
            <span class="lbl">Statut</span>
          </div>
        </div>

        <?php if (!empty($vache['description'])): ?>
          <p class="vache-desc"><?php echo nl2br(htmlspecialchars($vache['description'])); ?></p>
        <?php endif; ?>

        <!-- ================= PANEL OFFRE ================= -->
        <div class="offer-panel">

          <?php if ($statut === 'vendue'): ?>
            <div class="status-box vendue">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M9 12l2 2 4-4"/></svg>
              <span>Cette vache a déjà été vendue. Consultez le reste du <a href="accueil.php" style="color:var(--forest); font-weight:700; text-decoration:underline;">cheptel disponible</a>.</span>
            </div>

          <?php elseif ($offre_existante): ?>
            <div class="status-box <?php echo htmlspecialchars($offre_existante['statut']); ?>">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 8v5l3 2"/></svg>
              <span>
                Votre offre de <b><?php echo number_format((float)$offre_existante['montant_propose'], 0, ',', ' '); ?> MAD</b>
                est
                <?php
                  $labels = ['en_attente' => 'en attente de réponse', 'acceptee' => 'acceptée', 'refusee' => 'refusée'];
                  echo $labels[$offre_existante['statut']] ?? $offre_existante['statut'];
                ?>.
              </span>
            </div>
          <?php endif; ?>

          <h2>Proposer un prix</h2>
          <p class="hint">Envoyez le montant que vous êtes prêt à payer pour cette vache. La ferme vous répondra directement.</p>

          <form action="../actions/ajouter_offre.php" method="POST">
            <input type="hidden" name="id_vache" value="<?php echo (int)$vache['id']; ?>">
            <div class="amount-field">
              <span>MAD</span>
              <input type="number" name="montant_propose" min="1" step="1" placeholder="Votre offre"
                     <?php echo ($statut === 'vendue') ? 'disabled' : 'required'; ?>>
            </div>
            <button type="submit" class="btn-offer" <?php echo ($statut === 'vendue') ? 'disabled' : ''; ?>>
              Envoyer mon offre
            </button>
          </form>

          <div class="offer-note">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 8h.01M11 12h1v5h1"/></svg>
            <span>La ferme peut accepter, refuser ou vous recontacter pour ajuster votre proposition.</span>
          </div>

          <div class="mini-steps">
            <div class="mini-step"><span class="num">1</span> Vous envoyez votre prix pour cette vache.</div>
            <div class="mini-step"><span class="num">2</span> Ferme Tarmast examine l'offre reçue.</div>
            <div class="mini-step"><span class="num">3</span> En cas d'accord, vous êtes contacté pour finaliser.</div>
          </div>
        </div>
      </div>

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