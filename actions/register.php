<?php

require_once __DIR__ . '/../includes/session.php';
require '../config/database.php';

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

$stmt = $pdo->prepare(
    'INSERT INTO utilisateurs (nom, email, telephone, mot_de_passe) VALUES (:nom, :email, :telephone, :mot_de_passe)'
);

try {
    $stmt->execute([
        ':nom' => $nom,
        ':email' => $email,
        ':telephone' => $telephone,
        ':mot_de_passe' => $hash,
    ]);
    header('Location: ../login.php');
    exit;
} catch (PDOException $e) {
    $_SESSION['auth_errors'] = ["Erreur lors de l'inscription. Veuillez réessayer."];
    $_SESSION['auth_old'] = [
        'nom' => $nom,
        'email' => $emailInput,
        'telephone' => $telephone,
    ];
    header('Location: ../register.php');
    exit;
}
