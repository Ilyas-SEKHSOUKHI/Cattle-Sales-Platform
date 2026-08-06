<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

requireAdmin();

$adminNom = $_SESSION['nom'];
$adminId = (int) $_SESSION['user_id'];

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    redirect('liste_vaches.php');
}

$stmt = $pdo->prepare('SELECT id, nom, bovin, age, poids, description, image, statut FROM vaches WHERE id = :id AND id_admin = :id_admin');
$stmt->execute([':id' => $id, ':id_admin' => $adminId]);
$vache = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$vache) {
    redirect('liste_vaches.php');
}
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
<title>Modifier une vache — Ferme Tarmast</title>
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
  .topbar-back{
    font-size:.85rem;
    font-weight:700;
    color: var(--green-dark);
    background:#fff;
    border:1px solid var(--line);
    padding:.55rem 1rem;
    border-radius: 999px;
    display:inline-flex;
    align-items:center;
    gap:.4rem;
  }
  .topbar-back:hover{ background: var(--cream-2); }
  .topbar-back svg{ width:15px; height:15px; }

  /* ---------- PANEL ---------- */
  .panel{
    background:#fff;
    border: 1px solid var(--line);
    border-radius: 16px;
    overflow:hidden;
    max-width: 760px;
  }
  .panel-head{
    padding: 1.2rem 1.4rem;
    border-bottom: 1px solid var(--line);
  }
  .panel-head h2{ font-size: 1.05rem; }
  .panel-head p{ font-size:.85rem; color: var(--ink-soft); margin-top:.2rem; }
  .panel-body{ padding: 1.4rem; }

  .alert{
    display:flex;
    gap:.7rem;
    align-items:flex-start;
    background: rgba(76,175,80,.08);
    border: 1px solid rgba(76,175,80,.25);
    color: var(--green-dark);
    padding: .85rem 1rem;
    border-radius: 12px;
    font-size: .85rem;
    margin-bottom: 1.4rem;
  }
  .alert svg{ width:17px; height:17px; flex-shrink:0; margin-top:1px; }

  /* ---------- FORM ---------- */
  .form-grid{
    display:grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.2rem 1.4rem;
  }
  .form-group{ display:flex; flex-direction:column; gap:.45rem; }
  .form-group.full{ grid-column: 1 / -1; }
  .form-group label{
    font-size: .85rem;
    font-weight:700;
    color: var(--ink);
  }
  .form-group .hint{ font-size:.76rem; color: var(--ink-soft); font-weight:400; }

  input[type="text"], input[type="number"], input[type="file"], select, textarea{
    width:100%;
    padding: .75rem .9rem;
    border: 1px solid var(--line);
    border-radius: 10px;
    background: var(--cream);
    font-family: var(--body);
    font-size: .92rem;
    color: var(--ink);
    outline:none;
    transition: border-color .18s, box-shadow .18s, background .18s;
  }
  textarea{ resize:vertical; min-height: 110px; }
  input:focus, select:focus, textarea:focus, input[type="file"]:focus{
    border-color: var(--green);
    background:#fff;
    box-shadow: 0 0 0 3px rgba(76,175,80,.15);
  }

  .status-toggle{
    display:flex;
    gap:.6rem;
  }
  .status-toggle label{
    flex:1;
    display:flex;
    align-items:center;
    gap:.5rem;
    padding: .7rem .9rem;
    border: 1px solid var(--line);
    border-radius: 10px;
    font-size: .85rem;
    font-weight:600;
    color: var(--ink-soft);
    cursor:pointer;
    background: var(--cream);
  }
  .status-toggle input{ accent-color: var(--green); }
  .status-toggle input:checked + span{ color: var(--forest); }

  .form-actions{
    display:flex;
    justify-content:flex-end;
    gap:.8rem;
    margin-top: 1.8rem;
    padding-top: 1.2rem;
    border-top: 1px solid var(--line);
  }
  .btn{
    display:inline-flex;
    align-items:center;
    gap:.5rem;
    padding: .78rem 1.5rem;
    border-radius: 10px;
    font-size: .92rem;
    font-weight:700;
    font-family: var(--body);
    cursor:pointer;
    border: none;
    transition: background .18s;
  }
  .btn svg{ width:16px; height:16px; }
  .btn-primary{ background: var(--green); color:#fff; }
  .btn-primary:hover{ background: var(--green-dark); }
  .btn-cancel{ background: var(--cream-2); color: var(--ink-soft); border:1px solid var(--line); }
  .btn-cancel:hover{ background: #EADFC4; }

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
  @media (max-width: 640px){
    .main{ padding: 1.6rem 1.1rem 2.4rem; }
    .form-grid{ grid-template-columns: 1fr; }
    .panel{ max-width: 100%; }
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
      <a href="liste_vaches.php" class="active">
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
        <h1>Modifier une vache</h1>
        <p class="sub">Mettez à jour les informations de la fiche animal.</p>
      </div>
      <a href="liste_vaches.php" class="topbar-back">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
        Retour au cheptel
      </a>
    </div>

    <div class="alert">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 8h.01M11 12h1v5h1"/></svg>
      <span>Modifiez les champs ci-dessous puis enregistrez pour mettre à jour la fiche publique.</span>
    </div>

    <form action="../actions/modifier_vache.php" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="id" value="<?php echo (int) $vache['id']; ?>">
      <div class="panel">
        <div class="panel-head">
          <h2>Informations de l'animal</h2>
          <p>Champs correspondant à la table <code>vaches</code></p>
        </div>
        <div class="panel-body">
          <div class="form-grid">
            <div class="form-group">
              <label for="nom">Race</label>
              <select id="nom" name="nom" required>
                <option value="">— Choisir une race —</option>
                <?php foreach (getRaces() as $race): ?>
                  <option value="<?php echo htmlspecialchars($race); ?>" <?php echo $vache['nom'] === $race ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($race); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label for="bovin">Bovin</label>
              <select id="bovin" name="bovin" required>
                <?php foreach (getBovins() as $value => $label): ?>
                  <option value="<?php echo htmlspecialchars($value); ?>" <?php echo ($vache['bovin'] ?? 'vache') === $value ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($label); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label for="age">Âge (années)</label>
              <input type="number" id="age" name="age" min="0" step="1" placeholder="ex. 3" value="<?php echo $vache['age'] !== null ? (int) $vache['age'] : ''; ?>">
            </div>
            <div class="form-group">
              <label for="poids">Poids (kg)</label>
              <input type="number" id="poids" name="poids" min="0" step="0.01" placeholder="ex. 420" value="<?php echo $vache['poids'] !== null ? htmlspecialchars((string) $vache['poids']) : ''; ?>">
            </div>
            <div class="form-group">
              <label for="statut">Statut</label>
              <select id="statut" name="statut">
                <option value="disponible" <?php echo $vache['statut'] === 'disponible' ? 'selected' : ''; ?>>Disponible</option>
                <option value="vendue" <?php echo $vache['statut'] === 'vendue' ? 'selected' : ''; ?>>Vendue</option>
              </select>
            </div>
            <div class="form-group full">
              <label for="image">Photo de l'animal</label>
              <?php if (!empty($vache['image'])): ?>
                <img src="../<?php echo htmlspecialchars($vache['image']); ?>" alt="Photo actuelle" style="max-width:200px;border-radius:10px;border:1px solid var(--line);margin-bottom:.5rem;">
                <span class="hint">Photo actuelle — choisissez un fichier pour la remplacer.</span>
              <?php endif; ?>
              <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp,image/gif">
              <span class="hint">Formats acceptés : JPG, PNG, WEBP, GIF (optionnel).</span>
            </div>
            <div class="form-group full">
              <label for="description">Description</label>
              <textarea id="description" name="description" placeholder="Origine, alimentation, état de santé, remarques utiles à l'acheteur…"><?php echo htmlspecialchars($vache['description'] ?? ''); ?></textarea>
              <span class="hint">Ce texte s'affiche sur la fiche publique de l'animal.</span>
            </div>
          </div>

          <div class="form-actions">
            <a href="liste_vaches.php" class="btn btn-cancel">Annuler</a>
            <button type="submit" class="btn btn-primary">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
              Enregistrer les modifications
            </button>
          </div>
        </div>
      </div>
    </form>

  </main>
</div>

</body>
</html>