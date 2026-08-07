<?php

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require '../config/database.php';

$token = trim($_GET['token'] ?? '');

if ($token === '') {
    $_SESSION['auth_errors'] = ['Lien de validation invalide.'];
    header('Location: ../login.php');
    exit;
}

ensureColumnExists($pdo, 'utilisateurs', 'email_verified', 'TINYINT(1) NOT NULL DEFAULT 0');
ensureColumnExists($pdo, 'utilisateurs', 'verification_token', 'VARCHAR(64) NULL');

$stmt = $pdo->prepare('SELECT id, email_verified FROM utilisateurs WHERE verification_token = :token LIMIT 1');
$stmt->execute([':token' => $token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION['auth_errors'] = ['Lien de validation invalide ou déjà utilisé.'];
    header('Location: ../login.php');
    exit;
}

if ((int) ($user['email_verified'] ?? 0) === 1) {
    $_SESSION['auth_success'] = 'Votre adresse email est déjà validée.';
    header('Location: ../login.php');
    exit;
}

$pdo->prepare('UPDATE utilisateurs SET email_verified = 1, verification_token = NULL WHERE id = :id')->execute([':id' => $user['id']]);

$_SESSION['auth_success'] = 'Votre adresse email a bien été validée. Vous pouvez maintenant vous connecter.';
header('Location: ../login.php');
exit;
