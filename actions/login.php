<?php

require_once __DIR__ . '/../includes/session.php';
require '../config/database.php';

if (!isset($_POST['login'])) {
    header('Location: ../login.php');
    exit;
}

$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$mot_de_passe = $_POST['mot_de_passe'] ?? '';

if (!$email || $mot_de_passe === '') {
    $_SESSION['auth_errors'] = ['Email ou mot de passe invalide.'];
    header('Location: ../login.php');
    exit;
}

$stmt = $pdo->prepare('SELECT id, nom, email, mot_de_passe, role FROM utilisateurs WHERE email = :email');
$stmt->execute([':email' => $email]);
$utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

if ($utilisateur && password_verify($mot_de_passe, $utilisateur['mot_de_passe'])) {
    $_SESSION['user_id'] = $utilisateur['id'];
    $_SESSION['nom'] = $utilisateur['nom'];
    $_SESSION['role'] = $utilisateur['role'];

    if ($utilisateur['role'] === 'admin') {
        header('Location: ../admin/dashboard.php');
    } else {
        header('Location: ../client/accueil.php');
    }
    exit;
}

$_SESSION['auth_errors'] = ['Email ou mot de passe incorrect.'];
header('Location: ../login.php');
exit;
