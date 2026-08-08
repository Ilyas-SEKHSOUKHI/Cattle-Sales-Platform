<?php
require_once __DIR__ . '/includes/session.php';

$authErrors = $_SESSION['auth_errors'] ?? [];
$authSuccess = $_SESSION['auth_success'] ?? '';
$oldInput = $_SESSION['auth_old'] ?? [];
unset($_SESSION['auth_errors'], $_SESSION['auth_old'], $_SESSION['auth_success']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="icon" type="image/png" href="assets/images/iconVache.png">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Work+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="icon" type="image/x-icon" href="">
<title>Inscription — Ferme Tarmast</title>
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

  html, body{ height: auto; min-height: 100%; }

  body{
    font-family: var(--body);
    background: var(--cream);
    color: var(--ink);
    min-height: 100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    padding: 2rem 1rem;
    position: relative;
  }

  body::before{
    content:"";
    position:fixed;
    inset:0;
    z-index: -1;
    background:
      radial-gradient(600px 300px at 85% 10%, rgba(201,144,47,.14), transparent 70%),
      radial-gradient(500px 260px at 10% 90%, rgba(76,175,80,.10), transparent 70%);
    pointer-events:none;
  }

  a{ color: inherit; text-decoration:none; }

  .auth-wrap{
    width: 100%;
    max-width: 420px;
    position: relative;
    z-index: 1;
  }

  .brand{
    display:flex;
    align-items:center;
    justify-content:center;
    gap:.6rem;
    font-family: var(--display);
    font-size: 1.35rem;
    font-weight: 700;
    color: var(--forest);
    margin-bottom: 2rem;
  }
  .brand-mark{ width: 38px; height: 38px; flex-shrink: 0; }

  .auth-card{
    background: linear-gradient(145deg, #1B3A2B 0%, #142A20 100%);
    border: 1px solid rgba(27,58,43,.3);
    border-radius: 16px;
    box-shadow: 0 20px 45px rgba(27,58,43,.25);
    padding: 1.9rem 1.8rem 1.7rem;
    color: #fff;
  }

  .auth-card h2{
    font-family: var(--display);
    color: #fff;
    font-size: 1.4rem;
    font-weight: 600;
    margin-bottom: .3rem;
    text-align:center;
  }
  .auth-sub{
    text-align:center;
    color: #C7D6CB;
    font-size: .87rem;
    margin-bottom: 1.5rem;
  }

  .auth-alert{
    display:flex;
    align-items:flex-start;
    gap:.65rem;
    padding: .85rem 1rem;
    border-radius: 12px;
    font-size: .88rem;
    font-weight: 600;
    line-height: 1.45;
    margin-bottom: 1.2rem;
    border: 1px solid rgba(255,255,255,.2);
    background: rgba(0,0,0,.25);
    color: #ffd8d0;
  }
  .auth-alert[role="status"]{
    background: rgba(255,255,255,.15) !important;
    border-color: rgba(255,255,255,.3) !important;
    color: #fff !important;
  }
  .auth-alert[role="status"] a{
    color: #81C784 !important;
  }
  .auth-alert svg{ width:18px; height:18px; flex-shrink:0; margin-top:1px; }
  .auth-alert ul{
    margin: .35rem 0 0 1.1rem;
    padding: 0;
    list-style: disc;
  }
  .auth-alert li{ font-weight: 500; }

  .field{ margin-bottom: 1rem; }
  .field label{
    display:block;
    font-size: .8rem;
    font-weight: 600;
    color: #EFE9DA;
    margin-bottom: .35rem;
  }
  .field input{
    width: 100%;
    padding: .65rem .85rem;
    border: 1.5px solid rgba(255,255,255,.2);
    border-radius: 10px;
    background: #fff;
    font-family: var(--body);
    font-size: .95rem;
    color: var(--ink);
    transition: border-color .2s, background .2s, box-shadow .2s;
  }
  .field input:focus{
    outline: none;
    border-color: var(--green);
    box-shadow: 0 0 0 3px rgba(76,175,80,.35);
  }

  .auth-submit{
    width: 100%;
    padding: .85rem 1.2rem;
    border: none;
    border-radius: 10px;
    background: var(--green);
    color: #fff;
    font-family: var(--body);
    font-weight: 700;
    font-size: .96rem;
    cursor: pointer;
    box-shadow: 0 6px 16px rgba(0,0,0,.25);
    transition: background .25s, transform .15s;
    margin-top: .4rem;
  }
  .auth-submit:hover{ background: var(--green-dark); transform: translateY(-1px); }

  .auth-switch{
    text-align:center;
    margin-top: 1.6rem;
    font-size: .9rem;
    color: #C7D6CB;
  }
  .auth-switch a{
    color: #81C784;
    font-weight: 700;
  }
  .auth-switch a:hover{ text-decoration: underline; color: #a5d6a7; }

  .back-home{
    display:flex;
    justify-content:center;
    margin-top: 1.6rem;
    font-size: .85rem;
    color: var(--ink-soft);
  }
  .back-home a:hover{ color: var(--forest); }

  :focus-visible{ outline: 3px solid var(--ochre); outline-offset: 3px; }
</style>
</head>
<body>

<div class="auth-wrap">
  <a href="index.php" class="brand">
    <svg class="brand-mark" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
      <circle cx="24" cy="24" r="23" fill="#4CAF50" opacity="0.12"/>
      <path d="M14 22c0-2 1.5-3.5 3.5-3.5 1 0 1.8.4 2.5 1 1-1.3 2.5-2 4-2s3 .7 4 2c.7-.6 1.5-1 2.5-1 2 0 3.5 1.5 3.5 3.5 0 1-.4 2-1 2.6.6.5 1 1.3 1 2.2 0 2-1.7 3.7-3.7 3.7H18.7C16.7 30.5 15 28.8 15 26.8c0-.9.4-1.7 1-2.2-.6-.6-1-1.6-1-2.6z" fill="#1B3A2B"/>
      <circle cx="20" cy="25" r="1.4" fill="#fff"/>
      <circle cx="28" cy="25" r="1.4" fill="#fff"/>
      <path d="M22.5 27.5c.5.6 1.5.6 2 0" stroke="#fff" stroke-width="1" stroke-linecap="round"/>
    </svg>
    Ferme Tarmast
  </a>

  <div class="auth-card">
    <h2>Inscription</h2>
    <p class="auth-sub">Créez votre compte pour proposer un prix sur le cheptel.</p>

    <?php if (!empty($authErrors)): ?>
      <div class="auth-alert" role="alert">
        <svg viewBox="0 0 24 24" fill="none"><path d="M12 9v4m0 4h.01M10.3 3.9L2.7 17a2 2 0 001.7 3h15.2a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        <?php if (count($authErrors) === 1): ?>
          <span><?php echo htmlspecialchars($authErrors[0]); ?></span>
        <?php else: ?>
          <div>
            <span>Veuillez corriger les erreurs suivantes :</span>
            <ul>
              <?php foreach ($authErrors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($authSuccess)): ?>
      <div class="auth-alert" role="status" style="background: rgba(76,175,80,.12); border-color: rgba(76,175,80,.26); color: var(--forest);">
        <svg viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        <span><?php echo htmlspecialchars($authSuccess); ?></span>
      </div>
    <?php endif; ?>

    <form action="actions/register.php" method="POST">
        <div class="field">
            <label for="inputNomRegister">Nom</label>
            <input type="text" name="nom" id="inputNomRegister" value="<?php echo htmlspecialchars($oldInput['nom'] ?? ''); ?>" required>
        </div>
        <div class="field">
            <label for="inputEmailRegister">Email</label>
            <input type="email" name="email" id="inputEmailRegister" value="<?php echo htmlspecialchars($oldInput['email'] ?? ''); ?>" required>
        </div>
        <div class="field">
            <label for="inputTelephoneRegister">Numéro de téléphone</label>
            <input type="tel" name="telephone" id="inputTelephoneRegister" placeholder="ex. 06 12 34 56 78" value="<?php echo htmlspecialchars($oldInput['telephone'] ?? ''); ?>" required>
        </div>
        <div class="field">
            <label for="inputPasswordRegister">Mot de passe</label>
            <input type="password" name="mot_de_passe" id="inputPasswordRegister" required>
        </div>
        <input type="submit" name="register" id="registerSubmit" class="auth-submit" value="S'inscrire">
    </form>

    <p class="auth-switch">Déjà un compte ? <a href="login.php">Connexion</a></p>
  </div>

  <div class="back-home">
    <a href="index.php">&larr; Retour à l'accueil</a>
  </div>
</div>

</body>
</html>