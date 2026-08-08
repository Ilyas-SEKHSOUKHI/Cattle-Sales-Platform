<?php

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/mail.php';
require '../config/database.php';

ensureColumnExists($pdo, 'utilisateurs', 'email_verified', 'TINYINT(1) NOT NULL DEFAULT 0');
ensureColumnExists($pdo, 'utilisateurs', 'verification_token', 'VARCHAR(64) NULL');
ensureColumnExists($pdo, 'utilisateurs', 'verification_sent_at', 'DATETIME NULL');

if (!isset($_POST['register'])) {
    header('Location: ../register.php');
    exit;
}

$nom = trim(filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_SPECIAL_CHARS) ?: '');
$emailInput = trim($_POST['email'] ?? '');
$email = filter_var($emailInput, FILTER_VALIDATE_EMAIL);
$telephone = trim($_POST['telephone'] ?? '');
$mot_de_passe = $_POST['mot_de_passe'] ?? '';

$errors = [];

if ($nom === '') {
    $errors[] = 'Le nom est obligatoire.';
}
if (!$email) {
    $errors[] = "L'email n'est pas valide.";
} else {
    $domain = substr(strrchr($emailInput, '@'), 1);
    if ($domain === '' || !checkdnsrr($domain, 'MX')) {
        $errors[] = "L'adresse email n'a pas de domaine valide ou n'est pas accessible.";
    }
}

$telephoneDigits = preg_replace('/\D/', '', $telephone);
if ($telephone === '' || strlen($telephoneDigits) < 8 || strlen($telephoneDigits) > 15) {
    $errors[] = "Le numéro de téléphone n'est pas valide.";
}
if (strlen($mot_de_passe) < 8) {
    $errors[] = 'Le mot de passe doit contenir au moins 8 caractères.';
}

if (!empty($errors)) {
    $_SESSION['auth_errors'] = $errors;
    $_SESSION['auth_old'] = [
        'nom' => $nom,
        'email' => $emailInput,
        'telephone' => $telephone,
    ];
    header('Location: ../register.php');
    exit;
}

$check = $pdo->prepare('SELECT id FROM utilisateurs WHERE email = :email');
$check->execute([':email' => $email]);

if ($check->fetch()) {
    $_SESSION['auth_errors'] = ['Cet email est déjà utilisé.'];
    $_SESSION['auth_old'] = [
        'nom' => $nom,
        'email' => $emailInput,
        'telephone' => $telephone,
    ];
    header('Location: ../register.php');
    exit;
}

$hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
$token = bin2hex(random_bytes(32));
$userId = null;

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        'INSERT INTO utilisateurs (nom, email, telephone, mot_de_passe, role, email_verified, verification_token, verification_sent_at)
         VALUES (:nom, :email, :telephone, :mot_de_passe, :role, 0, :token, NOW())'
    );

    $stmt->execute([
        ':nom' => $nom,
        ':email' => $email,
        ':telephone' => $telephone,
        ':mot_de_passe' => $hash,
        ':role' => 'acheteur',
        ':token' => $token,
    ]);

    $userId = (int) $pdo->lastInsertId();

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $basePath = dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '/')) ?: '';
    $verifyUrl = $scheme . '://' . $host . $basePath . '/actions/verify_email.php?token=' . urlencode($token);

    $subject = 'Validez votre compte Ferme Tarmast';
    $textMessage = "Bonjour $nom,\n\n";
    $textMessage .= "Merci pour votre inscription sur Ferme Tarmast.\n";
    $textMessage .= "Cliquez sur le lien ci-dessous pour valider votre compte :\n\n";
    $textMessage .= $verifyUrl . "\n\n";
    $textMessage .= "Si vous n'êtes pas à l'origine de cette inscription, vous pouvez ignorer cet email.";

    $htmlMessage = str_replace('{YEAR}', date('Y'), buildVerificationEmailHtml($nom, $verifyUrl));

    $headers = [
        'From' => 'no-reply@fermetarmast.local',
        'Reply-To' => 'no-reply@fermetarmast.local',
        'Content-Type' => 'text/plain; charset=UTF-8',
    ];

    $mailSent = sendMailWithFallback($email, $subject, $textMessage, $headers, $htmlMessage);


    $pdo->commit();

    if ($mailSent) {
        $_SESSION['auth_success'] = 'Votre compte a été créé. Un email de validation a été envoyé à votre adresse.';
    } else {
        $_SESSION['auth_success'] = 'Votre compte a été créé, mais l’email de validation n’a pas pu être envoyé automatiquement. Vérifiez votre boîte de réception et les spams, ou configurez un SMTP valide.';
        $_SESSION['auth_verify_url'] = $verifyUrl;
    }

    header('Location: ../login.php');
    exit;
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    if ($userId) {
        $pdo->prepare('DELETE FROM utilisateurs WHERE id = :id')->execute([':id' => $userId]);
    }

    $_SESSION['auth_errors'] = ['Impossible d’envoyer l’email de validation. Veuillez vérifier votre adresse email ou réessayer plus tard.'];
    $_SESSION['auth_old'] = [
        'nom' => $nom,
        'email' => $emailInput,
        'telephone' => $telephone,
    ];
    header('Location: ../register.php');
    exit;
}
