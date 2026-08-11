<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

requireLogin('../login.php');

$userId = (int) $_SESSION['user_id'];
$message_succes = null;
$message_erreur = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['modifier_infos'])) {
        $nom = trim(filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_SPECIAL_CHARS) ?: '');
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $ice = trim(filter_input(INPUT_POST, 'ice', FILTER_SANITIZE_SPECIAL_CHARS) ?: '');

        if ($nom === '' || !$email) {
            $message_erreur = 'Veuillez renseigner un nom et un email valides.';
        } else {
            $check = $pdo->prepare('SELECT id FROM utilisateurs WHERE email = :email AND id != :id');
            $check->execute([':email' => $email, ':id' => $userId]);

            if ($check->fetch()) {
                $message_erreur = 'Cet email est déjà utilisé par un autre compte.';
            } else {
                ensureColumnExists($pdo, 'utilisateurs', 'ice', "VARCHAR(50) NULL DEFAULT ''");
                $pdo->prepare('UPDATE utilisateurs SET nom = :nom, email = :email, ice = :ice WHERE id = :id')
                    ->execute([':nom' => $nom, ':email' => $email, ':ice' => $ice, ':id' => $userId]);
                $_SESSION['nom'] = $nom;
                $message_succes = 'Profil mis à jour avec succès.';
            }
        }
    } elseif (isset($_POST['modifier_mot_de_passe'])) {
        $actuel = $_POST['mot_de_passe_actuel'] ?? '';
        $nouveau = $_POST['nouveau_mot_de_passe'] ?? '';
        $confirmer = $_POST['confirmer_mot_de_passe'] ?? '';

        $userStmt = $pdo->prepare('SELECT mot_de_passe FROM utilisateurs WHERE id = :id');
        $userStmt->execute([':id' => $userId]);
        $hash = $userStmt->fetchColumn();

        if (!$hash || !password_verify($actuel, $hash)) {
            $message_erreur = 'L\'ancien mot de passe est incorrect.';
        } elseif (strlen($nouveau) < 8) {
            $message_erreur = 'Le nouveau mot de passe doit contenir au moins 8 caractères.';
        } elseif ($nouveau !== $confirmer) {
            $message_erreur = 'Les mots de passe ne correspondent pas.';
        } else {
            $pdo->prepare('UPDATE utilisateurs SET mot_de_passe = :hash WHERE id = :id')
                ->execute([
                    ':hash' => password_hash($nouveau, PASSWORD_DEFAULT),
                    ':id' => $userId,
                ]);
            $message_succes = 'Mot de passe mis à jour avec succès.';
        }
    } elseif (isset($_POST['supprimer_compte'])) {
        $pdo->prepare('DELETE FROM offres WHERE id_utilisateur = :id')->execute([':id' => $userId]);
        $pdo->prepare('DELETE FROM utilisateurs WHERE id = :id')->execute([':id' => $userId]);
        $_SESSION = [];
        session_destroy();
        redirect('../login.php');
    }
}

ensureColumnExists($pdo, 'utilisateurs', 'ice', "VARCHAR(50) NULL DEFAULT ''");
$stmt = $pdo->prepare('SELECT id, nom, email, telephone, ice, role FROM utilisateurs WHERE id = :id');
$stmt->execute([':id' => $userId]);
$utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$utilisateur) {
    redirect('../login.php');
}

$utilisateur['date_inscription'] = null;
$nom_utilisateur = $utilisateur['nom'];

$statsStmt = $pdo->prepare(
    'SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN statut = \'acceptee\' THEN 1 ELSE 0 END) AS acceptees
     FROM offres
     WHERE id_utilisateur = :id'
);
$statsStmt->execute([':id' => $userId]);
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

$nb_offres_total = (int) ($stats['total'] ?? 0);
$nb_offres_acceptee = (int) ($stats['acceptees'] ?? 0);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mon profil — Ferme Tarmast</title>
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
  .btn-primary{ background: var(--green); color:#fff; }
  .btn-primary:hover{ background: var(--green-dark); }
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

  main{ padding-bottom: 5rem; }

  /* ---------- ALERTS ---------- */
  .alert{
    border-radius: 12px;
    padding: .9rem 1.2rem;
    font-size: .9rem;
    font-weight: 600;
    margin-bottom: 1.6rem;
    display:flex;
    align-items:center;
    gap:.6rem;
    border: 1px solid transparent;
  }
  .alert svg{ width:18px; height:18px; flex-shrink:0; }
  .alert-success{ background: rgba(76,175,80,.1); color: var(--green-dark); border-color: rgba(76,175,80,.25); }
  .alert-error{ background: rgba(166,81,46,.1); color: var(--rust); border-color: rgba(166,81,46,.25); }

  /* ---------- LAYOUT ---------- */
  .profile-layout{
    display:grid;
    grid-template-columns: .8fr 1.2fr;
    gap: 1.6rem;
    align-items: start;
  }

  /* ---------- IDENTITY CARD ---------- */
  .identity-card{
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 18px;
    padding: 2rem 1.6rem;
    text-align:center;
    box-shadow: 0 10px 26px rgba(27,58,43,.05);
  }
  .identity-avatar{
    width: 84px; height:84px; border-radius:50%;
    background: var(--forest);
    color:#fff;
    display:flex; align-items:center; justify-content:center;
    font-family: var(--display); font-weight:700; font-size: 2rem;
    margin: 0 auto 1rem;
  }
  .identity-card h2{ font-size: 1.2rem; margin-bottom: .2rem; }
  .identity-card .role-badge{
    display:inline-flex; align-items:center; gap:.35rem;
    padding: .28rem .7rem; border-radius: 999px; font-size:.74rem; font-weight:700;
    background: rgba(201,144,47,.14); color: var(--ochre);
    text-transform: uppercase; letter-spacing:.04em;
    margin-bottom: 1.4rem;
  }
  .identity-card .role-badge.admin{ background: rgba(27,58,43,.1); color: var(--forest); }

  .identity-meta{
    text-align:left;
    border-top: 1px solid var(--line);
    padding-top: 1.2rem;
    display:grid;
    gap: .8rem;
  }
  .identity-meta .row{ display:flex; justify-content:space-between; gap: .5rem; font-size: .85rem; }
  .identity-meta .row span:first-child{ color: var(--ink-soft); }
  .identity-meta .row span:last-child{ font-weight:600; color: var(--ink); text-align:right; word-break: break-word; }

  .identity-stats{
    display:grid;
    grid-template-columns: 1fr 1fr;
    gap: .8rem;
    margin-top: 1.4rem;
  }
  .identity-stats .stat{
    background: var(--cream-2);
    border-radius: 12px;
    padding: .8rem;
  }
  .identity-stats .num{ font-family: var(--display); font-size:1.3rem; font-weight:700; color: var(--forest); }
  .identity-stats .lbl{ font-size:.72rem; color: var(--ink-soft); text-transform: uppercase; letter-spacing:.03em; margin-top:.1rem; }

  /* ---------- PANELS (forms) ---------- */
  .panel{
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 10px 26px rgba(27,58,43,.05);
    margin-bottom: 1.6rem;
  }
  .panel-head{
    padding: 1.3rem 1.5rem;
    border-bottom: 1px solid var(--line);
  }
  .panel-head h2{ font-size: 1.05rem; }
  .panel-head p{ font-size: .82rem; color: var(--ink-soft); margin-top: .15rem; }
  .panel-body{ padding: 1.5rem; }

  .form-grid{
    display:grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.2rem;
  }
  .form-grid.single{ grid-template-columns: 1fr; }
  .field{ display:flex; flex-direction:column; gap:.4rem; }
  .field.full{ grid-column: 1 / -1; }
  .field label{
    font-size: .8rem; font-weight:700; color: var(--forest);
  }
  .field input{
    padding: .72rem .9rem;
    border: 1px solid var(--line);
    border-radius: 9px;
    font-family: var(--body);
    font-size: .92rem;
    color: var(--ink);
    background: var(--cream);
    transition: border-color .2s, box-shadow .2s;
  }
  .field input:focus{
    outline:none;
    border-color: var(--green);
    box-shadow: 0 0 0 3px rgba(76,175,80,.15);
    background: #fff;
  }
  .field .hint{ font-size: .76rem; color: var(--ink-soft); }

  .panel-footer{
    padding: 1.2rem 1.5rem;
    border-top: 1px solid var(--line);
    background: var(--cream-2);
    display:flex;
    justify-content:flex-end;
    gap: .8rem;
    flex-wrap: wrap;
  }

  /* ---------- DANGER ZONE ---------- */
  .danger-zone{
    border: 1px dashed rgba(166,81,46,.35);
    background: rgba(166,81,46,.04);
  }
  .danger-zone .panel-head h2{ color: var(--rust); }
  .danger-zone .panel-body{
    display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap: wrap;
  }
  .danger-zone .panel-body p{ color: var(--ink-soft); font-size: .88rem; max-width: 50ch; }
  .btn-danger{
    background: transparent;
    color: var(--rust);
    border: 1.5px solid rgba(166,81,46,.4);
  }
  .btn-danger:hover{ background: rgba(166,81,46,.08); }

  /* ---------- RESPONSIVE ---------- */
  @media (max-width: 900px){
    .profile-layout{ grid-template-columns: 1fr; }
  }
  @media (max-width: 760px){
    .nav-links{ display:none; }
    .nav-toggle{ display:flex; }
  }
  @media (max-width: 560px){
    .form-grid{ grid-template-columns: 1fr; }
    .danger-zone .panel-body{ flex-direction: column; align-items:flex-start; }
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
      <a href="mes_offres.php">Mes offres</a>
      <a href="profil.php" class="active">Mon profil</a>
    </nav>

    <div class="nav-actions">
      <div class="user-chip">
        <span class="avatar"><?php echo isset($nom_utilisateur) && $nom_utilisateur !== '' ? strtoupper(substr($nom_utilisateur, 0, 1)) : '?'; ?></span>
        <?php echo htmlspecialchars($nom_utilisateur !== '' ? $nom_utilisateur : 'Client'); ?>
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
    <h1>Mon profil</h1>
    <p>Gérez vos informations personnelles et votre mot de passe.</p>
  </div>
</section>

<main>
  <div class="container">

    <?php if ($message_succes): ?>
      <div class="alert alert-success">
        <svg viewBox="0 0 24 24" fill="none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
        <?php echo htmlspecialchars($message_succes); ?>
      </div>
    <?php endif; ?>

    <?php if ($message_erreur): ?>
      <div class="alert alert-error">
        <svg viewBox="0 0 24 24" fill="none"><path d="M12 9v4m0 4h.01M10.3 3.9L2.7 17a2 2 0 001.7 3h15.2a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        <?php echo htmlspecialchars($message_erreur); ?>
      </div>
    <?php endif; ?>

    <div class="profile-layout">

      <!-- ===== Colonne gauche : carte d'identité ===== -->
      <aside class="identity-card">
        <div class="identity-avatar">
          <?php echo isset($nom_utilisateur) && $nom_utilisateur !== '' ? strtoupper(substr($nom_utilisateur, 0, 1)) : '?'; ?>
        </div>
        <h2><?php echo htmlspecialchars($utilisateur['nom'] ?: 'Utilisateur'); ?></h2>
        <span class="role-badge<?php echo ($utilisateur['role'] ?? '') === 'admin' ? ' admin' : ''; ?>">
          <?php echo ($utilisateur['role'] ?? '') === 'admin' ? 'Administrateur · Jibal' : 'Acheteur'; ?>
        </span>

        <div class="identity-meta">
          <div class="row">
            <span>Email</span>
            <span><?php echo htmlspecialchars($utilisateur['email'] ?? ''); ?></span>
          </div>
          <div class="row">
            <span>Membre depuis</span>
            <span><?php echo !empty($utilisateur['date_inscription']) ? date('d/m/Y', strtotime($utilisateur['date_inscription'])) : '—'; ?></span>
          </div>
        </div>

        <div class="identity-stats">
          <div class="stat">
            <div class="num"><?php echo (int)$nb_offres_total; ?></div>
            <div class="lbl">Offres envoyées</div>
          </div>
          <div class="stat">
            <div class="num"><?php echo (int)$nb_offres_acceptee; ?></div>
            <div class="lbl">Acceptées</div>
          </div>
        </div>
      </aside>

      <!-- ===== Colonne droite : formulaires ===== -->
      <div>

        <!-- Informations personnelles -->
        <form class="panel" method="POST" action="profil.php">
          <div class="panel-head">
            <h2>Informations personnelles</h2>
            <p>Ces informations sont visibles par Jibal lors de vos échanges.</p>
          </div>
          <div class="panel-body">
            <div class="form-grid">
              <div class="field">
                <label for="nom">Nom complet</label>
                <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($utilisateur['nom'] ?? ''); ?>" required>
              </div>
              <div class="field">
                <label for="email">Adresse email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($utilisateur['email'] ?? ''); ?>" required>
              </div>
              <div class="field full">
                <label for="ice">Identifiant Commun de l'Entreprise (ICE)</label>
                <input type="text" id="ice" name="ice" placeholder="Ex: 001928374000089" value="<?php echo htmlspecialchars($utilisateur['ice'] ?? ''); ?>">
                <span class="hint">Optionnel — Utilisé sur vos factures d'achat.</span>
              </div>
            </div>
          </div>
          <div class="panel-footer">
            <button type="submit" name="modifier_infos" class="btn btn-primary">Enregistrer les modifications</button>
          </div>
        </form>

        <!-- Mot de passe -->
        <form class="panel" method="POST" action="profil.php">
          <div class="panel-head">
            <h2>Changer le mot de passe</h2>
            <p>Choisissez un mot de passe d'au moins 8 caractères.</p>
          </div>
          <div class="panel-body">
            <div class="form-grid single">
              <div class="field">
                <label for="mot_de_passe_actuel">Mot de passe actuel</label>
                <input type="password" id="mot_de_passe_actuel" name="mot_de_passe_actuel" required>
              </div>
            </div>
            <div class="form-grid" style="margin-top:1.2rem;">
              <div class="field">
                <label for="nouveau_mot_de_passe">Nouveau mot de passe</label>
                <input type="password" id="nouveau_mot_de_passe" name="nouveau_mot_de_passe" required>
                <span class="hint">Minimum 8 caractères.</span>
              </div>
              <div class="field">
                <label for="confirmer_mot_de_passe">Confirmer le nouveau mot de passe</label>
                <input type="password" id="confirmer_mot_de_passe" name="confirmer_mot_de_passe" required>
              </div>
            </div>
          </div>
          <div class="panel-footer">
            <button type="submit" name="modifier_mot_de_passe" class="btn btn-primary">Mettre à jour le mot de passe</button>
          </div>
        </form>

        <!-- Zone de danger -->
        <form class="panel danger-zone" method="POST" action="profil.php" onsubmit="return confirm('Supprimer définitivement votre compte ?');">
          <div class="panel-head">
            <h2>Supprimer mon compte</h2>
          </div>
          <div class="panel-body">
            <p>Cette action est définitive. Vos offres envoyées seront conservées dans l'historique de Jibal.</p>
            <button type="submit" name="supprimer_compte" class="btn btn-danger">Supprimer le compte</button>
          </div>
        </form>

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